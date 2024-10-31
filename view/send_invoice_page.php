<?php
$options = get_option('quip_invoices_options');
$localeStrings = QuipInvoices::getInstance()->get_locale_strings();
$client = QuipInvoices::getInstance()->db->get_client($invoice->clientID);
if (!$invoice->sent)
{
    $subject = $options['emailDefaultSubject'];
    $message = stripslashes(base64_decode($options['emailDefaultMessage']));
}
else
{
    $subject = $options['emailDefaultReminderSubject'];
    $message = stripslashes(base64_decode($options['emailDefaultReminderMessage']));
}
//calculate to address(es)
$toAddress = $client->clientEmail;
if ($client->clientAltEmails) $toAddress .= ',' . $client->clientAltEmails;
$demo = defined('QUIP_INVOICES_DEMO_MODE') ? 'disabled="disabled"' : '';

?>
<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>
    <h2><?php _e('Send Invoice', 'quip-invoices'); ?></h2>
    <?php if ($invoice->active == 0): ?>
        <div class="error"><p><?php  _e('DELETED INVOICE.  Data shown here for record keeping purposes.','quip-invoices'); ?></p></div>
        <script type="text/javascript">
            jQuery(document).ready(function ($)
            {
                $("form :input").prop("disabled", true);
            });
        </script>
    <?php endif; ?>
    <p><?php _e('Use this form to send the invoice directly to your customer via email. The customer will receive an email containing a link allowing them to view the invoice online.', 'quip-invoices'); ?>
        <br/>
        <?php _e('Please make sure your web server supports sending email', 'quip-invoices'); ?> (<code>php mail() / sendmail / postfix etc.</code>) <?php _e('otherwise this will not work.', 'quip-invoices'); ?>
    </p>
    <?php if ($invoice->sent): ?>
        <div class="updated"><p><?php echo __('You sent this invoice on: ' . date('d F Y \a\t g:ia', strtotime($invoice->sent)) ,'quip-invoices'); ?></p></div>
        <?php if ($invoice->reminderSent): ?>
            <div class="updated"><p><?php echo __('You last sent a reminder on: ' . date('d F Y \a\t g:ia', strtotime($invoice->reminderSent)) ,'quip-invoices'); ?></p></div>
        <?php endif; ?>
    <?php endif; ?>
    <form action="" method="post" id="quip-invoices-send-invoice-form">
        <input type="hidden" name="action" value="quip_invoices_send_invoice"/>
        <input type="hidden" name="invoiceID" value="<?php echo $invoice->invoiceID; ?>"/>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label><?php _e('Invoice Details', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <p>
                        <strong><?php _e('Invoice Number', 'quip-invoices'); ?>: </strong>
                        <span><?php echo $invoice->invoiceNumber; ?></span><br/>
                        <strong><?php _e('Invoice Date', 'quip-invoices'); ?>: </strong>
                        <span><?php echo date('l, d F, Y', strtotime($invoice->invoiceDate)); ?></span><br/>
                        <strong><?php _e('Due Date', 'quip-invoices'); ?>: </strong>
                        <span><?php echo date('l, d F, Y', strtotime($invoice->dueDate)); ?></span><br/>
                        <strong><?php _e('Amount Due', 'quip-invoices'); ?>: </strong>
                        <span><?php echo $localeStrings['symbol'] . $invoice->total; ?></span><br/>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="toAddress"><?php _e('To Address', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="toAddress" id="toAddress" class="regular-text code" value="<?php echo $toAddress; ?>">
                    <p class="description"><?php _e('You can add multiple email addresses using a comma to separate. i.e. joe@example.com,sally@example.com', 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="subject"><?php _e('Email Subject', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="subject" id="subject" class="regular-text" value="<?php echo $subject; ?>">
                    <p class="description"><?php _e('The  email subject line', 'quip-invoices'); ?>. <?php _e('Defaults to your', 'quip-invoices'); ?>
                        <a href="<?php echo admin_url('admin.php?page=quip-invoices-settings&tab=email'); ?>"><?php _e('settings', 'quip-invoices'); ?></a>.
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="message"><?php _e('Email Message', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <?php wp_editor($message, 'message', array('media_buttons' => false, 'teeny' => true, 'wpautop' => false)); ?>
                    <p class="description"><?php _e('The email message body', 'quip-invoices'); ?>. <?php _e('Defaults to your', 'quip-invoices'); ?>
                        <a href="<?php echo admin_url('admin.php?page=quip-invoices-settings&tab=email'); ?>"><?php _e('settings', 'quip-invoices'); ?></a>.
                        <?php _e('You can use the following dynamic tags', 'quip-invoices'); ?>: <br />
                        <code>%%INVOICE_AMOUNT%%</code> - <?php _e('The invoice total amount value', 'quip-invoices'); ?><br/>
                        <code>%%INVOICE_DUE_DATE%%</code> - <?php _e('The invoice due date', 'quip-invoices'); ?><br/>
                        <code>%%INVOICE_LINK%%</code> - <?php _e('A link to view the invoice online', 'quip-invoices'); ?><br/>
                        <code>%%COMPANY_DETAILS%%</code> - <?php _e('Your company details for this invoice (Name, Address, Phone, Email)', 'quip-invoices'); ?><br/>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="sendAdminCopy"><?php _e('Send Copy To Admin', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <label class="radio">
                        <input type="radio" name="sendAdminCopy" value="1" > <?php _e('Yes', 'quip-invoices'); ?>
                    </label> <label class="radio">
                        <input type="radio" name="sendAdminCopy" value="0" checked="checked"> <?php _e('No', 'quip-invoices'); ?>
                    </label>
                    <p class="description"><?php _e('Send a copy of this email to yourself as well.', 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="sendAdminCopy"><?php _e('Attach Invoice PDF', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <label class="radio">
                        <input type="radio" name="sendPDF" value="1" disabled> <?php _e('Yes', 'quip-invoices'); ?>
                    </label> <label class="radio">
                        <input type="radio" name="sendPDF" value="0" checked="checked"> <?php _e('No', 'quip-invoices'); ?>
                    </label>
                    <p class="description"><?php _e('Attach a copy of the invoice as a PDF to the email.', 'quip-invoices'); ?></p>
                    <p><a href="https://bit.ly/3fCpzU4" target="_blank">PDF's are available in our PRO veriosn - for more information click here</a></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="attachment"><?php _e('Attachment', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input id="attachment" type="text" name="attachment" />
                    <button id="uploadAttachmentButton" class="button" type="button" value="Select Attachment" <?php echo $demo; ?> ><?php _e('Select Attachment', 'quip-invoices'); ?></button>
                    <p class="description"><?php _e('Add an attachment to the email.  Leave blank to not send an attachment.', 'quip-invoices'); ?></p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary" <?php echo $demo; ?> ><?php _e('Send Invoice', 'quip-invoices'); ?></button>
            <a href="<?php echo admin_url('admin.php?page=quip-invoices-invoices'); ?>" class="button"><?php _e('Cancel', 'quip-invoices'); ?></a>
            <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
        </p>
    </form>
</div>