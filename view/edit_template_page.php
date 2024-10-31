<?php
$options = get_option('quip_invoices_options');
$localeStrings = QuipInvoices::getInstance()->get_locale_strings();
$invoice = json_decode($template->template);
?>
<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>


    <h2><?php _e('Edit Template', 'quip-invoices'); ?></h2>

    <form action="" method="post" id="quip-invoices-edit-template-form">
        <input type="hidden" name="action" value="quip_invoices_edit_template"/>
        <input type="hidden" name="id" value="<?php echo $template->id; ?>"/>
        <input type="hidden" name="allowPartialPayment" value="0" />
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="name"><?php _e('Template Name', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="name" id="name" class="regular-text code" value="<?php echo $template->name; ?>">
                    <p class="description"><?php _e('An identifier for your template', 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceNotes"><?php _e('Notes', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <textarea cols="50" rows="5" name="invoiceNotes" id="invoiceNotes" class="regular-text"><?php echo $invoice->notes; ?></textarea>
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
                            <a class='button deleteLineItemButton' href='delete'><?php _e('Delete', 'quip-invoices'); ?></a>
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
                    <button id="companyDetailsChangeSave" class="button button-primary" style="display:none;"><?php _e('Save Changes', 'quip-invoices'); ?></button>
                    <a href="cancel" id="companyDetailsChangeCancel" style="display:none;" class="button button-secondary"><?php _e('Cancel', 'quip-invoices'); ?></a>
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
            <button type="submit" class="button button-primary"><?php _e('Update Template', 'quip-invoices'); ?></button>
            <a href="<?php echo admin_url('admin.php?page=quip-invoices-invoices&tab=templates'); ?>" class="button button-secondary"><?php _e('Cancel', 'quip-invoices'); ?></a>
            <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
        </p>
    </form>
</div>