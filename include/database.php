<?php

if (!class_exists('QuipInvoicesDatabase'))
{
    class QuipInvoicesDatabase
    {
        private $invoiceTable, $lineItemTable, $clientTable, $paymentTable, $templateTable;
        const CARD_PAYMENT = 1;
        const MAIL_PAYMENT = 2;
        const PHONE_PAYMENT = 3;
        const IN_PERSON_PAYMENT = 4;
        const PAYPAL_PAYMENT = 5;

        public function __construct()
        {
            global $wpdb;
            $this->invoiceTable = $wpdb->prefix . 'qi_invoices';
            $this->lineItemTable = $wpdb->prefix . 'qi_line_items';
            $this->clientTable = $wpdb->prefix . 'qi_clients';
            $this->paymentTable = $wpdb->prefix . 'qi_payments';
            $this->templateTable = $wpdb->prefix . 'qi_templates';
        }

        public static function setup_db()
        {
            //require for dbDelta()
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

            global $wpdb;

            $table = $wpdb->prefix . 'qi_invoices';

            $sql = "CREATE TABLE " . $table . " (
            invoiceID INT NOT NULL AUTO_INCREMENT,
            invoiceNumber VARCHAR(100) NOT NULL,
            type VARCHAR(100) NOT NULL DEFAULT 'invoice',
            companyDetails TEXT,
            invoiceDate DATE,
            dueDate DATE,
            notes TEXT,
            clientID INT NOT NULL,
            hash VARCHAR(255) NOT NULL,
            subTotal DECIMAL(10,2),
            tax DECIMAL(10,2),
            total DECIMAL(10,2),
            owed DECIMAL(10,2),
            sent DATETIME,
            viewed DATETIME,
            reminderSent DATETIME,
            paymentTypes VARCHAR(100) DEFAULT '1',
            paymentInstructions TEXT,
            allowPartialPayment TINYINT(1) DEFAULT 1,
            pdfURL VARCHAR(1000),
            pdfPath VARCHAR(1000),
            recurring TINYINT(1) DEFAULT 0,
            baseInvoiceID INT,
            active TINYINT(1) DEFAULT 1,
            created DATETIME NOT NULL,
            UNIQUE KEY invoiceID (invoiceID)
            );";

            //database write/update
            dbDelta($sql);

            /////////////////

            $table = $wpdb->prefix . 'qi_line_items';

            $sql = "CREATE TABLE " . $table . " (
            id INT NOT NULL AUTO_INCREMENT,
            invoiceID INT NOT NULL,
            title VARCHAR(100) NOT NULL,
            description TEXT,
            quantity DECIMAL(10,2),
            rate DECIMAL(10,2),
            total DECIMAL(10,2),
            adjustment DECIMAL(10,2),
            created DATETIME NOT NULL,
            UNIQUE KEY id (id)
            );";

            //database write/update
            dbDelta($sql);

            /////////////////

            $table = $wpdb->prefix . 'qi_clients';

            $sql = "CREATE TABLE " . $table . " (
            id INT NOT NULL AUTO_INCREMENT,
            clientName VARCHAR(255) NOT NULL,
            clientContactName VARCHAR(255),
            clientEmail VARCHAR(255) NOT NULL,
            clientAltEmails TEXT,
            clientPhone VARCHAR(50),
            addressLine1 VARCHAR(500),
            addressLine2 VARCHAR(500),
            addressCity VARCHAR(500),
            addressState VARCHAR(255),
            addressZip VARCHAR(100),
            addressCountry VARCHAR(100),
            stripeCustomerID VARCHAR(100),
            active TINYINT(1) DEFAULT 1,
            created DATETIME NOT NULL,
            wpUserID INT,
            UNIQUE KEY id (id)
            );";

            //database write/update
            dbDelta($sql);

            /////////////////

            $table = $wpdb->prefix . 'qi_payments';

            $sql = "CREATE TABLE " . $table . " (
            id INT NOT NULL AUTO_INCREMENT,
            invoiceID INT NOT NULL,
            stripePaymentID VARCHAR(100),
            paypalTransactionID VARCHAR(100),
            livemode TINYINT(1) DEFAULT 1,
            amount INT NOT NULL,
            paymentType INT NOT NULL,
            paymentDate DATE,
            created DATETIME NOT NULL,
            UNIQUE KEY id (id)
            );";

            //database write/update
            dbDelta($sql);

            /////////////////

            $table = $wpdb->prefix . 'qi_templates';

            $sql = "CREATE TABLE " . $table . " (
            id INT NOT NULL AUTO_INCREMENT,
            name VARCHAR(255),
            template TEXT NOT NULL,
            created DATETIME NOT NULL,
            UNIQUE KEY id (id)
            );";

            //database write/update
            dbDelta($sql);

        }

        public function insert_invoice($invoice)
        {
            global $wpdb;
            $wpdb->insert($this->invoiceTable, $invoice);
            return $wpdb->insert_id;
        }

        public function update_invoice($id, $invoice)
        {
            global $wpdb;
            $wpdb->update($this->invoiceTable, $invoice, array('invoiceID' => $id));
        }

        /**
         * Soft deletes an invoice
         *
         * @param $id
         */
        public function delete_invoice($id)
        {
            $invoice = $this->get_invoice($id);
            if ($invoice)
            {
                //change the invoice number to allow user to reuse the number
                $invoiceNumber = "(DELETED)_" . $invoice->invoiceNumber;
                global $wpdb;
                $wpdb->update($this->invoiceTable, array('active' => 0, 'invoiceNumber' => $invoiceNumber), array('invoiceID' => $id));

                // if recurring, hard delete all the child invoices
                if ($invoice->recurring)
                {
                    $this->delete_child_invoices($id);
                }
            }
        }

        /**
         * Delete all children invoices in the recurring series, keeping just the parent
         *
         * @param $id int parent invoice id
         */
        public function delete_child_invoices($id)
        {
            global $wpdb;
            $childInvoices = $wpdb->get_results("SELECT * FROM $this->invoiceTable WHERE baseInvoiceID = $id");
            foreach($childInvoices as $ci)
            {
                if ($ci->invoiceID != $id)
                {
                    $this->hard_remove_invoice($ci->invoiceID);
                }
            }
        }

        /**
         * Hard delete (i.e. remove from DB) an invoice and associated line items.
         * Warning: If any payments, reports or other records refer to the deleted
         * invoice there may be errors.
         *
         * @param $id
         */
        public function hard_remove_invoice($id)
        {
            $this->hard_remove_invoice_line_items($id);

            global $wpdb;
            $wpdb->delete($this->invoiceTable, array('invoiceID' => $id));
        }

        public function hard_remove_invoice_line_items($id)
        {
            global $wpdb;
            $wpdb->delete($this->lineItemTable, array('invoiceID' => $id));
        }

        public function get_invoice($id)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM " . $this->invoiceTable . " WHERE invoiceId='" . $id . "';");
        }

        public function get_invoice_by_invoice_number($invoiceNumber)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM " . $this->invoiceTable . " WHERE invoiceNumber='" . $invoiceNumber . "';");
        }

        public function get_full_invoice($id)
        {
            $invoice = $this->get_invoice($id);
            if ($invoice)
            {
                $invoice->lineItems = $this->get_line_items($id);
            }
            return $invoice;
        }

        public function get_full_invoice_by_hash($hash)
        {
            $sql = "SELECT * FROM  $this->invoiceTable WHERE hash=%s";

            global $wpdb;
            $invoice = $wpdb->get_row($wpdb->prepare($sql, $hash));
            if ($invoice)
            {
                $invoice->lineItems = $this->get_line_items($invoice->invoiceID);
            }
            return $invoice;
        }

        public function get_invoices()
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM $this->invoiceTable WHERE active=1 and type='invoice'");
        }

        /**
         * Get invoices that haven't been sent to clients yet, dated before $date.
         * This is used by the client notifications check.
         *
         * @param $date
         * @return array|null|object
         */
        public function get_unsent_invoices_before_date($date)
        {
            global $wpdb;

            $sql = "SELECT * FROM $this->invoiceTable WHERE active=1 and type='invoice' ";
            $sql .= "and invoiceDate <= '$date' and (sent is null or reminderSent is null)";

            return $wpdb->get_results($sql);
        }

        /**
         * Calculate amount outstanding by adding all up payments received for invoice.
         * Normally we use the $invoice->owed column but sometimes need to recalculate
         * from all the payments instead.
         *
         * @param $invoiceID
         * @return mixed
         */
        public function get_invoice_amount_outstanding($invoiceID)
        {
            $invoice = $this->get_invoice($invoiceID);
            $payments = $this->get_payments($invoiceID);
            $total = $invoice->total;

            foreach ($payments as $p)
            {
                //payment amounts are stored in cents (easier for Stripe)
                $decAmount = floatval($p->amount / 100);
                $total = $total - $decAmount;
            }

            return round($total, 2);
        }

        /**
         * Have to force NULL in date fields, wpdb->update wraps them in quotes which then
         * inserts the 0000-00-00 date which is not what we want as we check against NULL
         *
         * @param $id
         */
        public function nullify_invoice_date_fields($id)
        {
            $sql = "UPDATE $this->invoiceTable SET sent=NULL, viewed=NULL WHERE invoiceID=%d";
            global $wpdb;
            $wpdb->query($wpdb->prepare($sql, $id));
        }

        /**
         * Get all template data except line item id's to make a template from this invoice
         * @param $id
         * @return array|null|object
         */
        public function get_invoice_data_for_template($id)
        {
            $invoice = $this->get_invoice($id);
            if ($invoice)
            {
                global $wpdb;
                $invoice->lineItems = $wpdb->get_results("SELECT invoiceID,title,description,quantity,rate,total,adjustment FROM $this->lineItemTable WHERE invoiceID=$id");
            }
            return $invoice;
        }

        //////////

        public function insert_line_item($item)
        {
            global $wpdb;
            $wpdb->insert($this->lineItemTable, $item);
            return $wpdb->insert_id;
        }

        public function update_line_item($id, $item)
        {
            global $wpdb;
            $wpdb->update($this->lineItemTable, $item, array('id' => $id));
        }

        public function delete_line_item($id)
        {
            global $wpdb;
            $wpdb->delete($this->lineItemTable, array('id' => $id));
        }

        public function get_line_items($invoiceID)
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM $this->lineItemTable WHERE invoiceID=$invoiceID");
        }

        /////////
        public function insert_client($item)
        {
            global $wpdb;
            $wpdb->insert($this->clientTable, $item);
            return $wpdb->insert_id;
        }

        public function update_client($id, $item)
        {
            global $wpdb;
            $wpdb->update($this->clientTable, $item, array('id' => $id));
        }

        public function hard_remove_client($id)
        {
            global $wpdb;
            $wpdb->delete($this->clientTable, array('id' => $id));
        }

        /**
         * Soft deletes a client
         *
         * @param $id
         */
        public function delete_client($id)
        {
            global $wpdb;
            $wpdb->update($this->clientTable, array('active' => 0), array('id' => $id));
        }

        public function get_clients()
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM $this->clientTable where active=1");
        }

        public function get_client($id)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM " . $this->clientTable . " WHERE id='" . $id . "';");
        }

        public function get_total_owed_for_client($id)
        {
            $sql = "SELECT sum(owed) as total_owed FROM $this->invoiceTable";
            $sql .= " WHERE clientID=$id AND type='invoice' and active=1";
            global $wpdb;
            $row = $wpdb->get_row($sql);
            if ($row)
            {
                return $row->total_owed;
            }

            return 0.0;
        }

        public function get_total_paid_by_client($id)
        {
            $sql = "SELECT sum(p.amount) as total_paid FROM $this->paymentTable p";
            $sql .= " JOIN $this->invoiceTable i ON p.invoiceID=i.invoiceID";
            $sql .= " WHERE i.clientID=$id";
            global $wpdb;
            $row = $wpdb->get_row($sql);
            if ($row)
            {
                return $row->total_paid / 100;  //amount is stored in cents
            }

            return 0.0;
        }

        public function get_invoices_for_client($id)
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM " . $this->invoiceTable . " WHERE clientID='" . $id . "' AND type='invoice' AND active=1");
        }

        public function get_quotes_for_client($id)
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM " . $this->invoiceTable . " WHERE clientID='" . $id . "' AND type='quote' AND active=1");
        }

        public function search_clients($term)
        {
            global $wpdb;
            $sql = "and (clientName LIKE '%{$term}%' or clientContactName LIKE '%{$term}%' or clientEmail LIKE '%{$term}%' or clientPhone LIKE '%{$term}%')";
            return $wpdb->get_results("SELECT * FROM $this->clientTable where active=1 $sql");
        }

        //////////

        public function insert_payment($item)
        {
            global $wpdb;
            $wpdb->insert($this->paymentTable, $item);
            return $wpdb->insert_id;
        }

        public function update_payment($id, $item)
        {
            global $wpdb;
            $wpdb->update($this->paymentTable, $item, array('id' => $id));
        }

        public function delete_payment($id)
        {
            global $wpdb;
            $wpdb->delete($this->paymentTable, array('id' => $id));
        }

        public function get_payment($id)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM " . $this->paymentTable . " WHERE id='" . $id . "';");
        }

        public function get_payments($invoiceID)
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM $this->paymentTable WHERE invoiceID=$invoiceID");
        }

        public function get_payments_for_export()
        {
            $sql = "SELECT i.invoiceNumber, c.clientName, p.* from $this->paymentTable p";
            $sql .= " JOIN $this->invoiceTable i ON p.invoiceID=i.invoiceID";
            $sql .= " JOIN $this->clientTable c ON i.clientID=c.id";

            global $wpdb;
            return $wpdb->get_results($sql);
        }

        ////////////
        public function get_quotes()
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM $this->invoiceTable WHERE active=1 and type='quote'");
        }

        ////////////

        public function insert_template($item)
        {
            global $wpdb;
            $wpdb->insert($this->templateTable, $item);
            return $wpdb->insert_id;
        }

        public function update_template($id, $item)
        {
            global $wpdb;
            $wpdb->update($this->templateTable, $item, array('id' => $id));
        }

        /**
         * Note: This is a hard delete
         * @param $id
         */
        public function delete_template($id)
        {
            global $wpdb;
            $wpdb->delete($this->templateTable, array('id' => $id));
        }

        public function get_templates()
        {
            global $wpdb;
            return $wpdb->get_results("SELECT * FROM $this->templateTable ORDER BY created ASC");
        }

        public function get_template($id)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT * FROM " . $this->templateTable . " WHERE id='" . $id . "';");
        }

        /////////////////////////////////////////////////

        /**
         * get total of all invoice payments received
         */
        public function get_invoice_total_received()
        {
            global $wpdb;
            return $wpdb->get_row("SELECT sum(amount) as total FROM " . $this->paymentTable . " where livemode=1;");
        }

        public function get_invoice_total_received_year($year)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT sum(amount) as total FROM " . $this->paymentTable . " where livemode=1 and year(paymentDate) = $year;");
        }

        /**
         * Get total of all invoices billed
         */
        public function get_invoice_total_billed()
        {
            global $wpdb;
            return $wpdb->get_row("SELECT sum(total) as total FROM " . $this->invoiceTable . " where active=1;");
        }

        /**
         * Get total of all invoices owed amount
         */
        public function get_invoice_total_owed()
        {
            global $wpdb;
            return $wpdb->get_row("SELECT sum(owed) as total FROM " . $this->invoiceTable . " where active=1;");
        }

        public function get_invoice_total_owed_year($year)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT sum(owed) as total FROM " . $this->invoiceTable . " where active=1 and year(dueDate) = $year;");
        }

        /**
         * Get monthly grouped amounts
         */
        public function get_invoice_total_received_by_month($year)
        {
            global $wpdb;
            return $wpdb->get_results("select month(paymentDate) as 'month', sum(amount) as total from " . $this->paymentTable . " where liveMode=1 and year(paymentDate) = $year group by month(paymentDate)");
        }

        public function get_total_billed_by_month($year)
        {
            global $wpdb;
            return $wpdb->get_results("select month(invoiceDate) as 'month', sum(total) as total from " . $this->invoiceTable . " where active=1 and year(invoiceDate) = $year group by month(invoiceDate)");
        }

        /**
         * Get amount due between dates
         */
        public function get_due_between_dates($start, $end)
        {
            global $wpdb;
            return $wpdb->get_row("SELECT sum(owed) as due from " . $this->invoiceTable . " where active=1 and dueDate between '$start' and '$end';");
        }


    }//end class QuipInvoicesDatabase

}