<?php
include_once 'base.controller.php';
include_once 'models/invoice_status.php';

if (!class_exists('QuipInvoicesInvoice'))
{
    class QuipInvoicesInvoice extends QuipInvoicesController
    {
        public function __construct()
        {
            parent::__construct();

            //ajax hook for create invoice form
            add_action('wp_ajax_quip_invoices_create_invoice', array($this, 'create_invoice'));
            //ajax hook for edit invoice form
            add_action('wp_ajax_quip_invoices_edit_invoice', array($this, 'edit_invoice'));
            //copy an invoice
            add_action('wp_ajax_quip_invoices_copy_invoice', array($this, 'copy_invoice'));
            //send an invoice
            add_action('wp_ajax_quip_invoices_send_invoice', array($this, 'send_invoice'));
            //delete an invoice
            add_action('wp_ajax_quip_invoices_delete_invoice', array($this, 'delete_invoice'));
            //delete multiple invoices
            add_action('wp_ajax_quip_invoices_delete_invoices', array($this, 'delete_invoices'));
            //get pdf invoice
            add_action('wp_ajax_quip_invoices_pdf_invoice', array($this, 'pdf_invoice'));
            //ajax hook for edit template form
            add_action('wp_ajax_quip_invoices_edit_template', array($this, 'edit_template'));
            //ajax hook for delete template
            add_action('wp_ajax_quip_invoices_delete_template', array($this, 'delete_template'));
        }

        /**
         * Handle create invoice form submission
         */
        public function create_invoice()
        {
            // we must have line items defined
            $lineItems = json_decode(base64_decode($_POST['lineItems']));
            if (!count($lineItems)) $this->json_exit(false, __("Please enter at least one line item.", 'quip-invoices'), '');

            // we must have at least one payment type defined
            if (!isset($_POST['paymentTypes'])) $this->json_exit(false, __("Please choose at least one payment type.", 'quip-invoices'), '');

            // check that we've not used this invoice number before
            $invoiceNumber = filter_var(trim($_POST['invoiceNumber']), FILTER_SANITIZE_STRING);
            $invoice = $this->find_invoice($invoiceNumber);
            if ($invoice) $this->json_exit(false, __("This invoice number has already been used.", 'quip-invoices'), '');

            $taxRate = sanitize_text_field($_POST['invoiceTaxRate']);

            $recurring = isset($_POST['invoiceRepeat']);

            $invoiceData = array(
                'invoiceNumber' => $invoiceNumber,
                'invoiceDate' => date("Y-m-d", strtotime($_POST['invoiceCreateDateDB'])),
                'dueDate' => date("Y-m-d", strtotime($_POST['invoiceDueDateDB'])),
                'clientID' => $_POST['invoiceClient'],
                'notes' => sanitize_text_field($_POST['invoiceNotes']),
                'hash' => $this->generate_invoice_hash($invoiceNumber, $_POST['invoiceClient']),
                'companyDetails' => $this->format_company_details($_POST['companyDetails']),
                'tax' => $taxRate,
                'paymentTypes' => $this->format_payment_types($_POST['paymentTypes']),
                'paymentInstructions' => sanitize_text_field($_POST['paymentInstructions']),
                'allowPartialPayment' => $_POST['allowPartialPayment'],
                'recurring' => $recurring,
                'type' => $_POST['invoiceType'],
                'created' => date('Y-m-d H:i:s')
            );

            //create invoice
            $id = $this->db->insert_invoice($invoiceData);
            //create line items
            $invoiceSubTotal = 0.0;
            foreach ($lineItems as $li)
            {
                $lineItemData = json_decode($li, true);
                $lineItemData['created'] = date('Y-m-d H:i:s');
                $lineItemData['invoiceID'] = $id;
                $this->db->insert_line_item($lineItemData);
                $invoiceSubTotal = $invoiceSubTotal + floatval($lineItemData['total']);
            }

            //update totals
            $total = $invoiceSubTotal * ((100 + floatval($taxRate)) / 100);
            $this->db->update_invoice($id, array(
                'subTotal' => $invoiceSubTotal,
                'total' => $total,
                'owed' => $total
            ));

            //update the counter
            QuipInvoices::getInstance()->increase_invoice_number();

            //if save as template is checked, serialize and save
            if (isset($_POST['saveAsTemplate']))
            {
                $this->db->insert_template(
                    array(
                        'name' => "template_$invoiceNumber",
                        'template' => json_encode($this->db->get_invoice_data_for_template($id)),
                        'created' => date('Y-m-d H:i:s')
                    ));
            }


            // automatic notifications
            $options = get_option('quip_invoices_options');
            if ($options['sendClientNotifications'] == 1 && $options['sendInvoiceEmailDelay'] == 0 )
            {
                $this->send_invoice_notificaton($id, $options);
            }

            $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-invoices&tab=view'));
        }

        /**
         * Handle edit invoice form submission.
         */
        public function edit_invoice()
        {
            $invoiceID = $_POST['invoiceID'];
            // we must have line items defined
            $lineItems = json_decode(base64_decode($_POST['lineItems']));
            if (!count($lineItems)) $this->json_exit(false, __("Please enter at least one line item.", 'quip-invoices'), '');

            // we must have at least one payment type defined
            if (!isset($_POST['paymentTypes'])) $this->json_exit(false, __("Please choose at least one payment type.", 'quip-invoices'), '');

            // check that we've not used this invoice number on a different invoice
            $invoiceNumber = filter_var(trim($_POST['invoiceNumber']), FILTER_SANITIZE_STRING);
            $invoice = $this->find_invoice($invoiceNumber);
            if ($invoice && $invoice->invoiceID != $invoiceID)
                $this->json_exit(false, __("This invoice number is used by another invoice.", 'quip-invoices'), '');

            // handle any recurring changes
            $createRecurring = false;
            $thisInvoice = $this->db->get_invoice($invoiceID);
            $recurring = $thisInvoice->recurring;
            $baseInvoiceID = $thisInvoice->baseInvoiceID;
            if ($thisInvoice->recurring)
            {
                if ($thisInvoice->invoiceID == $thisInvoice->baseInvoiceID)
                {
                    if (!isset($_POST['invoiceRepeat']))
                    {
                        // they've turned off recurring on the parent invoice
                        $recurring = 0;
                        $baseInvoiceID = null;
                        $this->db->delete_child_invoices($invoiceID);
                    }
                }
                else
                {
                    // they're editing one of the recurring series, remove it from series
                    $recurring = 0;
                    $baseInvoiceID = null;
                }
            }
            else if (isset($_POST['invoiceRepeat']))
            {
                // they've turned on recurring for this non-recurring invoice
                $createRecurring = true;
                $recurring = 1;
                $baseInvoiceID = $invoiceID;
            }

            //delete and re-create line items
            $this->db->hard_remove_invoice_line_items($invoiceID);
            //create line items
            $invoiceSubTotal = 0.0;
            foreach ($lineItems as $li)
            {
                $lineItemData = json_decode($li, true);
                $lineItemData['created'] = date('Y-m-d H:i:s');
                $lineItemData['invoiceID'] = $invoiceID;
                $this->db->insert_line_item($lineItemData);
                $invoiceSubTotal = $invoiceSubTotal + floatval($lineItemData['total']);
            }

            //invoice data
            $taxRate = sanitize_text_field($_POST['invoiceTaxRate']);
            $total = $invoiceSubTotal * ((100 + floatval($taxRate)) / 100);
            $invoiceData = array(
                'invoiceNumber' => $invoiceNumber,
                'invoiceDate' => date("Y-m-d", strtotime($_POST['invoiceCreateDateDB'])),
                'dueDate' => date("Y-m-d", strtotime($_POST['invoiceDueDateDB'])),
                'notes' => sanitize_text_field($_POST['invoiceNotes']),
                'companyDetails' => $this->format_company_details($_POST['companyDetails']),
                'tax' => $taxRate,
                'paymentTypes' => $this->format_payment_types($_POST['paymentTypes']),
                'paymentInstructions' => sanitize_text_field($_POST['paymentInstructions']),
                'allowPartialPayment' => $_POST['allowPartialPayment'],
                'type' => $_POST['invoiceType'],
                'subTotal' => $invoiceSubTotal,
                'total' => $total,
                'recurring' => $recurring,
                'baseInvoiceID' => $baseInvoiceID
            );

            //can't change client for certain invoice statuses
            if (isset($_POST['invoiceClient'])) $invoiceData['clientID'] = $_POST['invoiceClient'];

            //update invoice
            $this->db->update_invoice($invoiceID, $invoiceData);

            //now recalculate owed just in case payments were received before this invoice was edited
            $this->db->update_invoice($invoiceID, array('owed' => $this->db->get_invoice_amount_outstanding($invoiceID)));

            //now create recurring invoices based on updated data if needed
            if ($createRecurring)
            {
                $this->create_recurring_invoices($invoiceID, $_POST['invoiceRepeatFrequency']);
            }

            $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-invoices&tab=view'));

        }

        /**
         * Handle submission from copy invoice link.  Copy an existing invoice
         */
        public function copy_invoice()
        {
            $id = $_POST['id'];
            $newID = $this->create_invoice_copy($id);
            $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-edit&type=invoice&id=' . $newID));
        }

        /**
         * Handle submission from send_invoice form.  Sends invoice via email.
         */
        public function send_invoice()
        {
            $to = sanitize_text_field($_POST['toAddress']);
            $subject = sanitize_text_field($_POST['subject']);
            $message = stripslashes(($_POST['message']));
            $invoice = $this->db->get_invoice($_POST['invoiceID']);
            $localeStrings = QuipInvoices::getInstance()->get_locale_strings();
            $sendAdminCopy = $_POST['sendAdminCopy'];
            $attachment = $this->format_email_attachment(sanitize_text_field($_POST['attachment']));
            $sendPDF = $_POST['sendPDF'];
            if ($sendPDF == 1)
            {
                $pdf = $this->create_pdf($invoice->invoiceID);
                $attachment[] = $pdf['file'];
            }
            $client = $this->db->get_client($invoice->clientID);

            //replace our dynamic tags
            $message = str_replace(
                array(
                    "%%INVOICE_AMOUNT%%",
                    "%%INVOICE_DUE_DATE%%",
                    "%%INVOICE_LINK%%",
                    "%%COMPANY_DETAILS%%",
                    "%%CLIENT_NAME%%",
                    "%%CLIENT_CONTACT_NAME%%",
                    "%%CLIENT_EMAIL%%"
                ),
                array(
                    $localeStrings['symbol'] . $invoice->total,
                    date('l, d F, Y', strtotime($invoice->dueDate)),
                    '<a href="' . site_url() . '?qinvoice=' . $invoice->hash . '">' . __("Click here to view your invoice", 'quip-invoices') . '</a>',
                    $invoice->companyDetails,
                    $client->clientName,
                    $client->clientContactName,
                    $client->clientEmail
                ),
                $message);

            $success = QuipInvoices::getInstance()->send_email($to, $subject, $message, array(), $attachment);

            if (!$success)
            {
                $this->json_exit(false, __('Unable to send email.  Please check your server is configured correctly and the to address(es) are valid.', 'quip-invoices'), '');
            }
            else
            {
                if (!$invoice->sent)
                    $this->db->update_invoice($invoice->invoiceID, array('sent' => date('Y-m-d H:i:s')));
                else
                    $this->db->update_invoice($invoice->invoiceID, array('reminderSent' => date('Y-m-d H:i:s')));

                if ($sendAdminCopy == 1) QuipInvoices::getInstance()->send_email(get_bloginfo('admin_email'), "(COPY) " . $subject, $message);

                $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-invoices&tab=view'));
            }
        }

        /**
         * Handle user request to generate a PDF of an invoice
         */
        public function pdf_invoice()
        {
            $invoiceID = $_POST['id'];
            $pdf = $this->create_pdf($invoiceID);

            if (empty($pdf['error']))
            {
                // return with redirect to PDF
                $this->json_exit(true, "PDF Generated. Redirecting to view...", $pdf['url']);
            }
            else
            {
                $this->json_exit(false, "There has been an error creating the PDF: " . $pdf['error'], '');
            }
        }

        /**
         * Handle user request delete invoice
         */
        public function delete_invoice()
        {
            $this->db->delete_invoice(sanitize_text_field($_POST['id']));
            $this->json_exit(true, '', '');
        }

        public function delete_invoices()
        {
            $ids = $_POST['ids'];
            $count = 0;
            foreach($ids as $id)
            {
                if ($id)
                {
                    $this->db->delete_invoice($id);
                    $count++;
                }
            }

            $this->json_exit(true, $count . " " . __("Invoices Deleted Successfully"), '');
        }

        /**
         * Calculate status of invoice
         * @param $invoiceID
         * @return null|QuipInvoicesInvoiceStatus#
         */
        public function calculate_invoice_status($invoiceID)
        {
            $status = null;
            $invoice = $this->db->get_invoice($invoiceID);
            if ($invoice)
            {
                $payments = $this->db->get_payments($invoiceID);
                $status = new QuipInvoicesInvoiceStatus($invoice, $payments);
            }

            return $status;
        }

        /**
         * Handle edit template form submission.
         */
        public function edit_template()
        {
            $templateID = $_POST['id'];
            // we must have line items defined
            $lineItems = json_decode(base64_decode($_POST['lineItems']));
            if (!count($lineItems)) $this->json_exit(false, __("Please enter at least one line item.", 'quip-invoices'), '');

            // we must have at least one payment type defined
            if (!isset($_POST['paymentTypes'])) $this->json_exit(false, __("Please choose at least one payment type.", 'quip-invoices'), '');

            //create line items
            $invoiceSubTotal = 0.0;
            $lineItemStore = [];
            foreach ($lineItems as $li)
            {
                $lineItemData = json_decode($li, true);
                $lineItemData['created'] = date('Y-m-d H:i:s');
                $lineItemData['invoiceID'] = $templateID;
                $lineItemStore[] = $lineItemData;
                $invoiceSubTotal = $invoiceSubTotal + floatval($lineItemData['total']);
            }

            //invoice data
            $taxRate = sanitize_text_field($_POST['invoiceTaxRate']);
            $total = $invoiceSubTotal * ((100 + floatval($taxRate)) / 100);
            $templateData = array(
                'notes' => sanitize_text_field($_POST['invoiceNotes']),
                'companyDetails' => $this->format_company_details($_POST['companyDetails']),
                'tax' => $taxRate,
                'paymentTypes' => $this->format_payment_types($_POST['paymentTypes']),
                'paymentInstructions' => sanitize_text_field($_POST['paymentInstructions']),
                'allowPartialPayment' => $_POST['allowPartialPayment'],
                'subTotal' => $invoiceSubTotal,
                'total' => $total,
                'lineItems' => $lineItemStore
            );


            $this->db->update_template($templateID, array(
                'name' => sanitize_text_field($_POST['name']),
                'template' => json_encode($templateData)
                ));


            $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-invoices&tab=templates'));

        }

        /**
         * Handle user request delete template
         */
        public function delete_template()
        {
            $this->db->delete_template(sanitize_text_field($_POST['id']));
            $this->json_exit(true, '', '');
        }



        /**
         * Generate a unique hash for this invoice
         *
         * @param $invoiceNumber
         * @param $clientID
         * @return string Unique Hash for invoice
         */
        private function generate_invoice_hash($invoiceNumber, $clientID)
        {
            return hash('md5', "quip_invoices_{$invoiceNumber}_{$clientID}_" . date('Y-m-d_H:i:s'));
        }

        /**
         * Format incoming textarea company details ready for database and display
         *
         * @param $details
         * @return string
         */
        private function format_company_details($details)
        {
            $companyDetails = nl2br(htmlentities($details, ENT_QUOTES, 'UTF-8'));

            return $companyDetails;
        }

        /**
         * Currently just store payment types as a comma separated string
         *
         * @param $paymentTypes array
         * @return string
         */
        private function format_payment_types($paymentTypes)
        {
            return implode(',', $paymentTypes);
        }

        /**
         * Find the invoice by invoice number
         *
         * @param $invoiceNumber
         * @return mixed
         */
        private function find_invoice($invoiceNumber)
        {
            return $this->db->get_invoice_by_invoice_number($invoiceNumber);
        }

        /**
         * Convert a url style attachment from the media library into a file path
         * ready to be included in wp_mail attachment parameter
         *
         * @param $attachment string
         * @return string
         */
        private function format_email_attachment($attachment)
        {
            $attachmentPath = array();

            if ($attachment !== '')
            {
                // If uploaded to the media library it will be placed in a location such as
                // http://example.com/wp-content/uploads/attachment.zip.
                // We need to give back: WP_CONTENT_DIR . '/uploads/attachment.zip'

                list ($url, $path) = explode('wp-content', $attachment);
                $path = WP_CONTENT_DIR . $path;
                if (file_exists($path))
                    $attachmentPath = array($path);
            }

            return $attachmentPath;
        }

        /**
         * Make a full copy of an invoice and its line items
         *
         * @param $id
         * @return int
         */
        private function create_invoice_copy($id)
        {
            $invoice = $this->db->get_full_invoice($id);
            $invoiceNumber = "(COPY)_" . $invoice->invoiceNumber;

            $invoiceData = array(
                'invoiceNumber' => $invoiceNumber,
                'invoiceDate' => $invoice->invoiceDate,
                'dueDate' => $invoice->dueDate,
                'clientID' => $invoice->clientID,
                'notes' => $invoice->notes,
                'hash' => $this->generate_invoice_hash($invoiceNumber, $invoice->clientID),
                'companyDetails' => $invoice->companyDetails,
                'tax' => $invoice->tax,
                'subTotal' => $invoice->subTotal,
                'total' => $invoice->total,
                'owed' => $invoice->total,
                'paymentTypes' => $invoice->paymentTypes,
                'paymentInstructions' => $invoice->paymentInstructions,
                'allowPartialPayment' => $invoice->allowPartialPayment,
                'type' => $invoice->type,
                'created' => date('Y-m-d H:i:s')
            );

            $newID = $this->db->insert_invoice($invoiceData);

            //now line items
            foreach ($invoice->lineItems as $li)
            {
                $lineItemData = array(
                    'title' => $li->title,
                    'rate' => $li->rate,
                    'quantity' => $li->quantity,
                    'adjustment' => $li->adjustment,
                    'total' => $li->total,
                    'created' => date('Y-m-d H:i:s'),
                    'invoiceID' => $newID
                );

                $this->db->insert_line_item($lineItemData);
            }

            //update the counter
            QuipInvoices::getInstance()->increase_invoice_number();

            return $newID;
        }


        /**
         * PHP datetime add 1 month is broken because it adds 1 to the month and then rounds up days if that date doesnt
         * actually exist, i.e. end of the month or say 31st jan +1 month gives 31st Feb so PHP goes to 3rd March...
         * @param $date DateTime
         * @return DateTime
         */
        private function fixed_add_month($date, $months)
        {
            // go to next month and then fix the days to match
            $day = $date->format('j');
            $date->modify('first day of +' . $months . ' month');
            $date->modify('+' . (min($day, $date->format('t')) - 1) . ' days');
            return $date;
        }

        /**
         * Trigger email notification for new invoice creation to client.
         * @param $invoiceID
         * @param $options
         */
        private function send_invoice_notificaton($invoiceID, $options)
        {
            $invoice = $this->db->get_invoice($invoiceID);
            $client = $this->db->get_client($invoice->clientID);

            wp_schedule_single_event(
                time()+60,
                'quip_notification_hook',
                array(
                    $client->clientEmail, //$to
                    $options['emailDefaultSubject'], //$subject
                    QuipInvoices::getInstance()->construct_invoice_notification_message($invoice, $client, false) //$message
                )
            );

            $this->db->update_invoice($invoiceID, array('sent' => date('Y-m-d H:i:s')));
        }
    }
}
