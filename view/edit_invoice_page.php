<?php
$options = get_option('quip_invoices_options');
$localeStrings = QuipInvoices::getInstance()->get_locale_strings();
?>
<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>

    <?php
    $status = QuipInvoices::getInstance()->invoice->calculate_invoice_status($invoice->invoiceID);
    $disabled = (!$status->isNew()) ? 'disabled="disabled"' : '';
    ?>
    <h2><?php _e('Edit Invoice', 'quip-invoices'); ?></h2>
    <?php if ($invoice->active == 0): ?>
        <div class="error">
            <p><?php _e('DELETED INVOICE.  Data shown here for record keeping purposes.', 'quip-invoices'); ?></p>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($)
            {
                $("form :input").prop("disabled", true);
            });
        </script>
    <?php endif; ?>
    <form action="" method="post" id="quip-invoices-create-invoice-form">
        <input type="hidden" name="action" value="quip_invoices_edit_invoice"/>
        <input type="hidden" name="invoiceID" value="<?php echo $invoice->invoiceID; ?>"/>
        <input type="hidden" name="invoiceType" value="invoice"/>
        <input type="hidden" name="allowPartialPayment" value="0" />
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceNumber"><?php _e('Invoice Number', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="invoiceNumber" id="invoiceNumber" class="regular-text code" value="<?php echo $invoice->invoiceNumber; ?>">
                    <p class="description"><?php _e('An identifier for your invoice', 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceCreateDate"><?php _e('Invoice Date', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="invoiceCreateDate" id="invoiceCreateDate" class="regular-text datepicker" value="<?php echo date('l, d F, Y', strtotime($invoice->invoiceDate)); ?>">
                    <input type="hidden" name="invoiceCreateDateDB" id="invoiceCreateDateDB" value="<?php echo $invoice->invoiceDate; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceDueDate"><?php _e('Invoice Due Date', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="invoiceDueDate" id="invoiceDueDate" class="regular-text datepicker" value="<?php echo date('l, d F, Y', strtotime($invoice->dueDate)); ?>">
                    <input type="hidden" name="invoiceDueDateDB" id="invoiceDueDateDB" value="<?php echo $invoice->dueDate; ?>">
                </td>
            </tr>
            <?php if ($invoice->recurring): ?>
                <tr valign="top">
                    <th scope="row">
                        <label for="invoiceRepeat"><?php _e('Recurring Invoice', 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <?php if ($invoice->invoiceID == $invoice->baseInvoiceID): ?>
                            <input type="checkbox" name="invoiceRepeat" id="invoiceRepeat" value="1" checked="checked">
                            <p class="description">* <?php _e('Disabling recurring invoice will DELETE all related future invoices because this is the parent invoice for the series.', 'quip-invoices'); ?></p>
                        <?php else: ?>
                            <input type="checkbox" name="invoiceRepeat" id="invoiceRepeat" value="1" checked="checked" disabled="disabled">
                            <p class="description">* <?php _e('Editing this invoice will remove it from the related recurring invoice series to become stand-alone.', 'quip-invoices'); ?></p>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php else: ?>
                <tr valign="top">
                    <th scope="row">
                        <label for="invoiceRepeat"><?php _e('Recurring Invoice', 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <input type="checkbox" name="invoiceRepeat" id="invoiceRepeat" value="1" disabled>
                        <p class="description"><?php _e('Automatically create future repeating invoices based on this one', 'quip-invoices'); ?></p>
                    </td>
                </tr>
                <tr valign="top" style="display: none;" id="invoiceRepeatSection">
                    <th scope="row">
                        <label for="invoiceRepeatFrequency"><?php _e('Recurring Frequency', 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <select name="invoiceRepeatFrequency" id="invoiceRepeatFrequency">
                            <option value="weekly" ><?php _e('Weekly', 'quip-invoices'); ?></option>
                            <option value="biweekly"><?php _e('Bi-weekly', 'quip-invoices'); ?></option>
                            <option value="monthly"><?php _e('Monthly', 'quip-invoices'); ?></option>
                            <option value="quarterly"><?php _e('Quarterly', 'quip-invoices'); ?></option>
                            <option value="yearly"><?php _e('Yearly', 'quip-invoices'); ?></option>
                        </select>
                    </td>
                </tr>
            <?php endif; ?>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceNotes"><?php _e('Notes', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <textarea cols="50" rows="5" name="invoiceNotes" id="invoiceNotes" class="regular-text"><?php echo $invoice->notes; ?></textarea>
                    <p class="description"><?php _e('Add notes for the client on the invoice', 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceClient"><?php _e('Client', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <?php $clients = QuipInvoices::getInstance()->db->get_clients(); ?>
                    <select id="invoiceClient" name="invoiceClient" <?php echo count($clients) == 0 ? 'style="display:none;"' : '' ?> <?php echo $disabled; ?> >
                        <?php foreach ($clients as $c): ?>
                            <option value="<?php echo $c->id; ?>" <?php if ($invoice->clientID == $c->id) echo 'selected="selected"'; ?>> <?php echo stripslashes($c->clientName); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <a href="new" id="createNewClient" <?php echo !$status->isNew() ? 'style="display:none;"' : '' ?> class="button button-secondary"><?php _e('Add New', 'quip-invoices'); ?></a>
                    <?php if (!$status->isNew()): ?>
                        <p class="description">* <?php _e('Changing clients is disabled after the invoice has been sent to or viewed by the client.', 'quip-invoices'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <h3><?php _e('Line Items', 'quip-invoices'); ?></h3>
        <div id="lineItemSection">
            <table class="widefat" id="lineItemsTable">
                <thead>
                <tr>
                    <th><?php _e('Item', 'quip-invoices'); ?></th>
                    <th><?php _e('Rate', 'quip-invoices'); ?></th>
                    <th><?php _e('Quantity', 'quip-invoices'); ?></th>
                    <th>% <?php _e('Adjustment', 'quip-invoices'); ?></th>
                    <th><?php _e('Amount', 'quip-invoices'); ?></th>
                    <th>&nbsp;</th>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($invoice->lineItems as $li): ?>
                    <tr data-json="<?php echo base64_encode(json_encode($li)); ?>" data-amount="<?php echo $li->total; ?>">
                        <td><?php echo $li->title; ?></td>
                        <td><?php echo $localeStrings['symbol'] . $li->rate; ?></td>
                        <td><?php echo $li->quantity; ?></td>
                        <td><?php echo $li->adjustment; ?></td>
                        <td><?php echo $localeStrings['symbol'] . $li->total; ?></td>
                        <td>
                            <a class='button deleteLineItemButton' href='delete' data-id="<?php echo $li->id; ?>"><?php _e('Delete', 'quip-invoices'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr class="qi-new-line-item">
                    <td>
                        <input type="text" name="liTitle" id="liTitle" class="regular-text">
                    </td>
                    <td>
                        <input type="text" name="liRate" id="liRate" class="qi-input-mini" value="0">
                    </td>
                    <td>
                        <input type="text" name="liQty" id="liQty" class="qi-input-mini" value="0">
                    </td>
                    <td>
                        <input type="text" name="liAdj" id="liAdj" class="qi-input-mini" value="0">
                    </td>
                    <td>
                        <span id="liAmount"></span>
                    </td>
                    <td>
                        <button id="addLineItemButton" class="button button-primary"><?php _e('Add', 'quip-invoices'); ?></button>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div id="line-item-totals" class="clearfix">
            <p style="line-height: 2.2em;">
                <strong><?php _e('Sub Total', 'quip-invoices'); ?>: </strong><span id="liSubTotal"></span><br/>
                <strong><?php _e('Tax', 'quip-invoices'); ?> %: </strong><input type="text" class="qi-input-mini" name="invoiceTaxRate" id="invoiceTaxRate" value="<?php echo $invoice->tax; ?>"><br/>
                <strong><?php _e('Total', 'quip-invoices'); ?>: </strong><span id="liTotal"></span><br/>
            </p>
        </div>
        <!-- clear the float -->
        <div style="clear: both;"></div>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="companyDetails"><?php _e('Your details', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <textarea rows="6" cols="50" id="companyDetails" name="companyDetails" style="display:none;"><?php echo str_replace("<br />", "", stripslashes($invoice->companyDetails)); ?></textarea>
                    <address id="companyDetailsDisplay">
                        <?php echo stripslashes($invoice->companyDetails); ?>
                    </address>
                    <br/><a href="set-details" id="companyDetailsChange" class="button button-secondary"><?php _e('Change Details', 'quip-invoices'); ?></a>
                    <button id="companyDetailsChangeSave" style="display:none;" class="button button-primary"><?php _e('Save Changes', 'quip-invoices'); ?></button>
                    <a href="cancel" id="companyDetailsChangeCancel" style="display:none;" class="button button-secondary"><?php _e('Cancel', 'quip-invoices'); ?></a>
                    <p class="description"><?php _e('Your details are added to the invoice.', 'quip-invoices'); ?>
                        <?php
                        $url = admin_url('admin.php?page=quip-invoices-settings');
                        $link = sprintf(
                            wp_kses(__('Defaults to your <a href="%s">settings</a>, but you can customize them per invoice.', 'quip-invoices'),
                                array('a' => array('href' => array()))), esc_url($url));
                        echo $link;
                        ?>
                    </p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="paymentTypes"><?php _e('Payment Types Allowed', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <?php $paymentTypes = explode(',', $invoice->paymentTypes); ?>
                    <?php if ($options['paymentProcessor'] == 'stripe'): ?>
                    <label>
                        <input type="checkbox" name="paymentTypes[]" id="paymentTypeCC" value="1" <?php echo in_array('1', $paymentTypes) ? 'checked="checked"' : ''; ?> > <?php _e('Credit Card', 'quip-invoices'); ?>
                    </label>
                    <br/><br/>
                    <?php endif; ?>
                    <?php if ($options['paymentProcessor'] == 'paypal'): ?>
                        <label>
                            <input type="checkbox" name="paymentTypes[]" id="paymentTypePaypal" value="5" <?php echo in_array('5', $paymentTypes) ? 'checked="checked"' : ''; ?>> Paypal
                        </label>
                        <br/><br/>
                    <?php endif; ?>
                    <label>
                        <input type="checkbox" name="paymentTypes[]" id="paymentTypeMail" value="2" <?php echo in_array('2', $paymentTypes) ? 'checked="checked"' : ''; ?> > <?php _e('Pay via Mail', 'quip-invoices'); ?>
                    </label>
                    <br/><br/>
                    <label>
                        <input type="checkbox" name="paymentTypes[]" id="paymentTypePhone" value="3" <?php echo in_array('3', $paymentTypes) ? 'checked="checked"' : ''; ?> > <?php _e('Pay via Phone', 'quip-invoices'); ?>
                    </label>
                </td>
            </tr>
        </table>
        <div id="paymentInstructionsSection" <?php echo (in_array('2', $paymentTypes) || in_array('3', $paymentTypes)) ? '' : 'style="display:none;"'; ?> >
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label for="paymentInstructions"><?php _e('Payment Instructions', 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <textarea rows="4" cols="50" name="paymentInstructions" id="paymentInstructions" class="regular-text"><?php echo stripslashes($invoice->paymentInstructions); ?></textarea>
                        <p class="description"><?php _e('Extra instructions for payment via phone or mail. Leave blank to not show on invoice.', 'quip-invoices'); ?></p>
                    </td>
                </tr>
            </table>
        </div>
        <p class="submit">
            <button type="submit" class="button button-primary"><?php _e('Update Invoice', 'quip-invoices'); ?></button>
            <a href="<?php echo admin_url('admin.php?page=quip-invoices-invoices'); ?>" class="button button-secondary"><?php _e('Cancel', 'quip-invoices'); ?></a>
            <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
        </p>
    </form>
</div>

<div id="createClientDialog" title="Create New Client" style="display:none;">
    <p>
        <label for="clientName"><?php _e('Client Name', 'quip-invoices'); ?>:</label>
        <input type="text" name="clientName" id="clientName" class="regular-text">
    </p>
    <p>
        <label for="clientEmail"><?php _e('Client Email', 'quip-invoices'); ?>:</label>
        <input type="text" name="clientEmail" id="clientEmail" class="regular-text">
    </p>
</div>