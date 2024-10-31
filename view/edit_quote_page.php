<?php
$localeStrings = QuipInvoices::getInstance()->get_locale_strings();
?>
<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>
    <h2><?php _e('Edit Quote', 'quip-invoices'); ?></h2>
    <?php if ($invoice->active == 0): ?>
        <div class="error">
            <p><?php _e('DELETED QUOTE.  Data shown here for record keeping purposes.', 'quip-invoices'); ?></p>
        </div>
        <script type="text/javascript">
            jQuery(document).ready(function ($)
            {
                $("form :input").prop("disabled", true);
            });
        </script>
    <?php endif; ?>
    <form action="" method="post" id="quip-invoices-create-invoice-form">
        <input type="hidden" name="action" value="quip_invoices_edit_quote"/>
        <input type="hidden" name="invoiceID" value="<?php echo $invoice->invoiceID; ?>"/>
        <input type="hidden" name="invoiceType" value="quote"/>
        <table class="form-table">
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceNumber"><?php _e('Quote Number', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="invoiceNumber" id="invoiceNumber" class="regular-text code" value="<?php echo $invoice->invoiceNumber; ?>">
                    <p class="description"><?php _e('An identifier for your quote', 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceCreateDate"><?php _e('Quote Date', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="invoiceCreateDate" id="invoiceCreateDate" class="regular-text datepicker" value="<?php echo date('l, d F, Y', strtotime($invoice->invoiceDate)); ?>">
                    <input type="hidden" name="invoiceCreateDateDB" id="invoiceCreateDateDB" value="<?php echo $invoice->invoiceDate; ?>">
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceDueDate"><?php _e('Quote Expiry Date', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <input type="text" name="invoiceDueDate" id="invoiceDueDate" class="regular-text datepicker"
                           value="<?php echo $invoice->dueDate ? date('l, d F, Y', strtotime($invoice->dueDate)) : ''; ?>">
                    <input type="hidden" name="invoiceDueDateDB" id="invoiceDueDateDB" value="<?php echo $invoice->dueDate ? $invoice->dueDate: ''; ?>">
                    <p class="description"><?php _e("The date this quote expires. Leave blank for no expiry.", 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceNotes"><?php _e('Notes', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <textarea cols="50" rows="5" name="invoiceNotes" id="invoiceNotes" class="regular-text"><?php echo $invoice->notes; ?></textarea>
                    <p class="description"><?php _e('Add notes for the client on the quote', 'quip-invoices'); ?></p>
                </td>
            </tr>
            <tr valign="top">
                <th scope="row">
                    <label for="invoiceClient"><?php _e('Client', 'quip-invoices'); ?>:</label>
                </th>
                <td>
                    <?php $clients = QuipInvoices::getInstance()->db->get_clients(); ?>
                    <select id="invoiceClient" name="invoiceClient" <?php echo count($clients) == 0 ? 'style="display:none;"' : '' ?> >
                        <?php foreach ($clients as $c): ?>
                            <option value="<?php echo $c->id; ?>" <?php if ($invoice->clientID == $c->id) echo 'selected="selected"'; ?>> <?php echo stripslashes($c->clientName); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <a href="new" id="createNewClient" class="button button-secondary"><?php _e('Add New', 'quip-invoices'); ?></a>
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
                    <textarea rows="6" cols="50" id="companyDetails" name="companyDetails" style="display:none;"><?php echo str_replace("<br />", "", $invoice->companyDetails); ?></textarea>
                    <address id="companyDetailsDisplay">
                        <?php echo $invoice->companyDetails; ?>
                    </address>
                    <br/><a href="set-details" id="companyDetailsChange" class="button button-secondary"><?php _e('Change Details', 'quip-invoices'); ?></a>
                    <button id="companyDetailsChangeSave" class="button button-primary" style="display:none;"><?php _e('Save Changes', 'quip-invoices'); ?></button>
                    <a href="cancel" id="companyDetailsChangeCancel" style="display:none;" class="button button-secondary"><?php _e('Cancel', 'quip-invoices'); ?></a>
                    <p class="description"><?php _e('Your details are added to the invoice.', 'quip-invoices'); ?>
                        <?php
                        $url = admin_url('admin.php?page=quip-invoices-settings');
                        $link = sprintf(
                            wp_kses(__('Defaults to your <a href="%s">settings</a>, but you can customize them per quote.', 'quip-invoices'),
                                array('a' => array('href' => array()))), esc_url($url));
                        echo $link;
                        ?>
                    </p>
                </td>
            </tr>
        </table>
        <p class="submit">
            <button type="submit" class="button button-primary"><?php _e('Update Quote', 'quip-invoices'); ?></button>
            <a href="<?php echo admin_url('admin.php?page=quip-invoices-quotes'); ?>" class="button button-secondary"><?php _e('Cancel', 'quip-invoices'); ?></a>
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