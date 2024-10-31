<?php
include_once 'base.controller.php';
include_once 'models/invoice_status.php';

if (!class_exists('QuipInvoicesQuote'))
{
    /**
     * Class QuipInvoicesQuote - Quotes controllers
     *
     * @notes: Currently very similar to QuipInvoicesInvoice due to being mostly
     * based off of it, so we will look into refactoring in a future update.
     */
    class QuipInvoicesQuote extends QuipInvoicesController
    {
        public function __construct()
        {
            parent::__construct();

            //ajax hook for create quote form
            add_action('wp_ajax_quip_invoices_create_quote', array($this, 'create_quote'));
            //ajax hook for edit quote form
            add_action('wp_ajax_quip_invoices_edit_quote', array($this, 'edit_quote'));
            //copy an quote
            add_action('wp_ajax_quip_invoices_copy_quote', array($this, 'copy_quote'));
            //send an quote
            add_action('wp_ajax_quip_invoices_send_quote', array($this, 'send_quote'));
            //delete an quote
            add_action('wp_ajax_quip_invoices_delete_quote', array($this, 'delete_quote'));
            //delete quotes
            add_action('wp_ajax_quip_invoices_delete_quotes', array($this, 'delete_quotes'));
            //convert quote to invoice
            add_action('wp_ajax_quip_invoices_convert_quote', array($this, 'convert_quote'));
        }

        /**
         * Handle create quote form submission
         */
        public function create_quote()
        {
            // we must have line items defined
            $lineItems = json_decode(base64_decode($_POST['lineItems']));
            if (!count($lineItems)) $this->json_exit(false, __("Please enter at least one line item.", 'quip-invoices'), '');

            // check that we've not used this invoice number before
            $invoiceNumber = filter_var(trim($_POST['invoiceNumber']), FILTER_SANITIZE_STRING);
            $invoice = $this->find_invoice($invoiceNumber);
            if ($invoice) $this->json_exit(false, __("This quote number has already been used.", 'quip-invoices'), '');

            $taxRate = sanitize_text_field($_POST['invoiceTaxRate']);

            $invoiceData = array(
                'invoiceNumber' => $invoiceNumber,
                'invoiceDate' => date("Y-m-d", strtotime($_POST['invoiceCreateDateDB'])),
                'clientID' => $_POST['invoiceClient'],
                'notes' => trim(filter_var($_POST['invoiceNotes']), FILTER_SANITIZE_STRING),
                'hash' => $this->generate_invoice_hash($_POST['invoiceNumber'], $_POST['invoiceClient']),
                'companyDetails' => $this->format_company_details($_POST['companyDetails']),
                'tax' => $taxRate,
                'type' => $_POST['invoiceType'],
                'created' => date('Y-m-d H:i:s')
            );

            //check expiry date
            if (isset($_POST['invoiceDueDate']) && $_POST['invoiceDueDate'] != '')
                $invoiceData['dueDate'] = date("Y-m-d", strtotime($_POST['invoiceDueDateDB']));

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

            $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-quotes&tab=view'));
        }

        /**
         * Handle edit quote form submission.
         */
        public function edit_quote()
        {
            $invoiceID = $_POST['invoiceID'];
            // we must have line items defined
            $lineItems = json_decode(base64_decode($_POST['lineItems']));
            if (!count($lineItems)) $this->json_exit(false, __("Please enter at least one line item.", 'quip-invoices'), '');

            // check that we've not used this invoice number on a different invoice
            $invoiceNumber = filter_var(trim($_POST['invoiceNumber']), FILTER_SANITIZE_STRING);
            $invoice = $this->find_invoice($invoiceNumber);
            if ($invoice && $invoice->invoiceID != $invoiceID)
                $this->json_exit(false, __("This quote number is used by another invoice/quote.", 'quip-invoices'), '');

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

            //update invoice
            $taxRate = sanitize_text_field($_POST['invoiceTaxRate']);
            $total = $invoiceSubTotal * ((100 + floatval($taxRate)) / 100);

            $this->db->update_invoice($invoiceID, array(
                'invoiceNumber' => $invoiceNumber,
                'invoiceDate' => date("Y-m-d", strtotime($_POST['invoiceCreateDateDB'])),
                'dueDate' => date("Y-m-d", strtotime($_POST['invoiceDueDateDB'])),
                'clientID' => $_POST['invoiceClient'],
                'notes' => trim(filter_var($_POST['invoiceNotes']), FILTER_SANITIZE_STRING),
                'companyDetails' => $this->format_company_details($_POST['companyDetails']),
                'tax' => $taxRate,
                'type' => $_POST['invoiceType'],
                'subTotal' => $invoiceSubTotal,
                'total' => $total
            ));

            //now recalculate owed just in case payments were received before this invoice was edited
            $this->db->update_invoice($invoiceID, array('owed' => $this->db->get_invoice_amount_outstanding($invoiceID)));

            $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-quotes&tab=view'));

        }

        /**
         * Handle submission from copy link.  Copy an existing quote
         */
        public function copy_quote()
        {
            $id = $_POST['id'];
            $invoice = $this->db->get_full_invoice($id);
            $invoiceNumber = "(COPY)_" . $invoice->invoiceNumber;

            $invoiceData = array(
                'invoiceNumber' => $invoiceNumber,
                'invoiceDate' => $invoice->invoiceDate,
                'clientID' => $invoice->clientID,
                'notes' => $invoice->notes,
                'hash' => $this->generate_invoice_hash($invoiceNumber, $invoice->clientID),
                'companyDetails' => $invoice->companyDetails,
                'tax' => $invoice->tax,
                'subTotal' => $invoice->subTotal,
                'total' => $invoice->total,
                'owed' => $invoice->total,
                'type' => $invoice->type,
                'created' => date('Y-m-d H:i:s')
            );

            if ($invoice->dueDate)
                $invoiceData['dueDate'] = $invoice->dueDate;

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

            $this->json_exit(true, '',  admin_url('admin.php?page=quip-invoices-edit&type=quote&id=' . $newID));
        }

        /**
         * Handle conversion to an invoice.  Because we already treat quotes as invoices
         * internally all this involves is changing the type and some minor tidy up.
         */
        public function convert_quote()
        {
            $id = $_POST['id'];
            $invoice = $this->db->get_full_invoice($id);

            $invoiceData = array(
                'type' => 'invoice',
            );

            if (!$invoice->dueDate)
                $invoiceData['dueDate'] = date('Y-m-d', strtotime("+1 week"));

            $this->db->update_invoice($id, $invoiceData);
            // Make sure to reset the tracking dates
            $this->db->nullify_invoice_date_fields($id);

            $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-edit&type=invoice&id=' . $id));
        }

        /**
         * Handle submission from send form.  Sends quote via email.
         */
        public function send_quote()
        {
            $to = sanitize_text_field($_POST['toAddress']);
            $subject = sanitize_text_field($_POST['subject']);
            $message = stripslashes(($_POST['message']));
            $invoice = $this->db->get_invoice($_POST['invoiceID']);
            $localeStrings = QuipInvoices::getInstance()->get_locale_strings();
            $sendAdminCopy = $_POST['sendAdminCopy'];
            $attachment = $this->format_email_attachment(sanitize_text_field($_POST['attachment']));

            $client = $this->db->get_client($invoice->clientID);

            //replace our dynamic tags
            $message = str_replace(
                array(
                    "%%INVOICE_AMOUNT%%",
                    "%%INVOICE_LINK%%",
                    "%%COMPANY_DETAILS%%",
                    "%%CLIENT_NAME%%",
                    "%%CLIENT_CONTACT_NAME%%",
                    "%%CLIENT_EMAIL%%"
                ),
                array(
                    $localeStrings['symbol'] . $invoice->total,
                    '<a href="' . site_url() . '?qinvoice=' . $invoice->hash . '">' . __("Click here to view your quote", 'quip-invoices') . '</a>',
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
                $this->db->update_invoice($invoice->invoiceID, array('sent' => date('Y-m-d H:i:s')));
                if ($sendAdminCopy == 1) QuipInvoices::getInstance()->send_email(get_bloginfo('admin_email'), "(COPY) " . $subject, $message);

                $this->json_exit(true, '', admin_url('admin.php?page=quip-invoices-quotes&tab=view'));
            }
        }


        /**
         * Handle user request delete quote
         */
        public function delete_quote()
        {
            $this->db->delete_invoice(sanitize_text_field($_POST['id']));
            $this->json_exit(true, '', '');
        }

        public function delete_quotes()
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

            $this->json_exit(true, $count . " " . __("Quotes Deleted Successfully"), '');
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

    }
}
