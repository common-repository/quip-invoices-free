<?php
include_once 'base.controller.php';

if (!class_exists('QuipInvoicesPayment'))
{
    class QuipInvoicesPayment extends QuipInvoicesController
    {
        public function __construct()
        {
            parent::__construct();

            // hooks
            add_action('wp_ajax_quip_invoices_create_manual_payment', array($this, 'create_manual_payment'));
            add_action('wp_ajax_quip_invoices_delete_payment', array($this, 'delete_payment'));
            add_action('wp_ajax_quip_invoices_delete_payments', array($this, 'delete_payments'));
            add_action('wp_ajax_quip_invoices_pay_invoice', array($this, 'pay_invoice'));
            add_action('wp_ajax_nopriv_quip_invoices_pay_invoice', array($this, 'pay_invoice'));

            // includes
            include_once 'processors/paypal.php';

            //catch paypal requests
            add_action('quip_invoices_listener', array('\quip_invoices\processors\PaypalProcessor', 'paypal_listener'));

            do_action('quip_invoices_include_processors');
        }

        /**
         * Handle add payment form submission to manually add a new payment
         */
        public function create_manual_payment()
        {
            //create payment
            $amount = sanitize_text_field($_POST['paymentAmount']);
            $paymentData = array(
                'invoiceID' => $_POST['invoiceID'],
                'amount' => $amount,
                'paymentType' => $_POST['paymentType'],
                'paymentDate' => $_POST['paymentDateDB'],
                'created' => date('Y-m-d H:i:s')
            );

            $this->db->insert_payment($paymentData);

            //update invoice owed
            $invoice = $this->db->get_invoice($_POST['invoiceID']);
            $owed = $invoice->owed - floatval($amount / 100);
            $this->db->update_invoice($invoice->invoiceID, array('owed' => $owed));

            $this->json_exit(true, '', '');

        }

        /**
         * Handle submission of payment directly from HTML invoice template page.
         */
        public function pay_invoice()
        {
            $invoice = $this->db->get_full_invoice($_POST['invoiceID']);

            if ($invoice)
            {
                $options = get_option('quip_invoices_options');

                if ($options['paymentProcessor'] == 'stripe')
                {
                }
                else
                {
                    // perhaps an extension is processing this payment
                    do_action('quip_invoices_pay_invoice', $invoice);
                }

            }

            // Success exit, just refresh the page so payment appears on invoice.
            header("Content-Type: application/json");
            echo json_encode(array('success' => true, 'redirectURL' => site_url() . "?qinvoice=$invoice->hash"));
            exit;
        }


        /**
         * Permanently delete a payment and recalculate the invoice owed amount
         * after deletion.
         */
        public function delete_payment()
        {
            $id = sanitize_text_field($_POST['id']);
            $payment = $this->db->get_payment($id);
            if ($payment)
            {
                //update invoice owed
                $invoice = $this->db->get_invoice($payment->invoiceID);
                // make sure to delete payment before recalculating amount owed
                $this->db->delete_payment($id);
                $this->db->update_invoice(
                    $invoice->invoiceID,
                    array('owed' => $this->db->get_invoice_amount_outstanding($invoice->invoiceID)));

            }

            $this->json_exit(true, '', '');
        }

        public function delete_payments()
        {
            $ids = $_POST['ids'];
            $count = 0;
            foreach($ids as $id)
            {
                if ($id)
                {
                    $payment = $this->db->get_payment($id);
                    if ($payment)
                    {
                        //update invoice owed
                        $invoice = $this->db->get_invoice($payment->invoiceID);
                        // make sure to delete payment before recalculating amount owed
                        $this->db->delete_payment($id);
                        $this->db->update_invoice(
                            $invoice->invoiceID,
                            array('owed' => $this->db->get_invoice_amount_outstanding($invoice->invoiceID)));

                        $count++;
                    }
                }
            }

            $this->json_exit(true, $count . " " . __("Payments Deleted Successfully"), '');
        }



    } //end class
}
