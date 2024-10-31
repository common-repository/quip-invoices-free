<?php

namespace quip_invoices\processors;

include_once QUIP_INVOICES_DIR . '/lib/paypal/IpnListener.php';

class PaypalProcessor
{
    const ACTION_IPN = 'paypal_ipn';
    const SANDBOX_URL = 'https://www.sandbox.paypal.com/cgi-bin/webscr';
    const LIVE_URL = 'https://www.paypal.com/cgi-bin/webscr';

    /**
     * This listens for incoming Paypal IPN messages and validates specific payment
     * IPN messages related to invoice payments through Paypal.
     */
    public static function paypal_listener()
    {
        if (!isset($_GET[self::ACTION_IPN]))
            return;

        $options = get_option('quip_invoices_options');

        //validate the IPN
        $ipnListener = new \IpnListener();
        if ($options['apiMode'] == 'test') $ipnListener->use_sandbox = true;

        try
        {
            $verified = $ipnListener->processIpn();
        }
        catch (\Exception $e)
        {
            error_log("Error with PayPal IPN: " . $e->getMessage());
            exit;
        }

        if ($verified)
        {
            //only update if completed payment
            $paymentStatus = $_POST['payment_status'];

            if ($paymentStatus == 'Completed' || $paymentStatus == 'Processed')
            {
                //try and match the IPN to the hash to know we've got the right invoice IPN
                $ipnHash = sanitize_text_field($_POST['custom']);
                $getHash = sanitize_text_field($_GET['hash']);

                if ($ipnHash == $getHash)
                {
                    $invoice = \QuipInvoices::getInstance()->db->get_full_invoice_by_hash($ipnHash);
                    $amount = sanitize_text_field($_POST['mc_gross']);

                    $paymentData = array(
                        'invoiceID' => $invoice->invoiceID,
                        'amount' => $amount * 100, //always store payment amounts as cents
                        'paymentType' => 5, //Paypal
                        'paymentDate' => date('Y-m-d'),  // Paypal passes back badly formatted date... //strtotime($_POST['payment_date'])
                        'paypalTransactionID' => sanitize_text_field($_POST['txn_id']),
                        'livemode' => isset($_POST['test_ipn']),
                        'created' => date('Y-m-d H:i:s')
                    );
                    \QuipInvoices::getInstance()->db->insert_payment($paymentData);

                    //update invoice owed
                    $owed = $invoice->owed - floatval($amount);
                    \QuipInvoices::getInstance()->db->update_invoice($invoice->invoiceID, array('owed' => $owed));

                    //TODO: send buyer confirmation email
                    //...

                    //send notification
                    if ($options['sendNotifications'] == 1)
                        \QuipInvoices::getInstance()->send_notification(__('Payment Received', 'quip-invoices'), $invoice);

                    //PAYMENT SUCCESS!
                }
            }
        }
    }

    /**
     * Output the Paypal button markup using Paypal options found:
     * http://paypal.github.io/JavaScriptButtons/
     *
     * @param $invoice
     */
    public static function construct_paypal_button_for_invoice($invoice)
    {
        $options = get_option('quip_invoices_options');
        $client = \QuipInvoices::getInstance()->db->get_client($invoice->clientID);
        $buttonJS = QUIP_INVOICES_JS_DIR . 'paypal-button.min.js?merchant=' . $options['merchantID'];

        ?>
        <script src="<?php echo $buttonJS ?>"
                data-button="buynow"
                data-size="large"
                data-style="primary"
                data-no_shipping="1"
                data-no_note="1"
                data-name="<?php echo sprintf(__('%s - Invoice: %s', 'quip-invoices'), get_bloginfo(), $invoice->invoiceNumber); ?>"
                data-quantity="1"
                data-amount="<?php echo $invoice->owed; ?>"
                data-currency="<?php echo mb_strtoupper($options['currency']); ?>"
                data-shipping="0"
                data-tax="0"
                data-callback="<?php echo site_url() . '?' . self::ACTION_IPN . '=1&hash=' . $invoice->hash; ?>"
                data-return="<?php echo site_url() . '?qinvoice=' . $invoice->hash . '&pending=1'; ?>"
                data-cancel_return="<?php echo site_url() . '?qinvoice=' . $invoice->hash; ?>"
                data-custom="<?php echo $invoice->hash; ?>"
                data-cbt="<?php echo sprintf(__('Return to %s store', 'quip-invoices'), get_bloginfo()); ?>"
                <?php echo $options['apiMode'] == 'test' ? 'data-env="sandbox"' : '' ?>
                data-first_name="<?php echo stripslashes($client->clientName); ?>"
                data-email="<?php echo $client->clientEmail; ?>">
        </script>
    <?php

    }
}