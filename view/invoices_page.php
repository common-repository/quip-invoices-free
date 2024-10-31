<?php
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'view';
$options = get_option('quip_invoices_options');
$localeStrings = QuipInvoices::getInstance()->get_locale_strings();
$useTemplate = false;
if (isset($t))
{
    $useTemplate = true;
    $template = json_decode($t->template);
}
?>
<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>
    <h2 class="nav-tab-wrapper">
        <a href="?page=quip-invoices-invoices&tab=view" class="nav-tab <?php echo $active_tab == 'view' ? 'nav-tab-active' : ''; ?>"><?php _e('Invoices', 'quip-invoices'); ?></a>
        <a href="?page=quip-invoices-invoices&tab=create" class="nav-tab <?php echo $active_tab == 'create' ? 'nav-tab-active' : ''; ?>"><?php _e('Create New Invoice', 'quip-invoices'); ?></a>
        <a href="?page=quip-invoices-invoices&tab=templates" class="nav-tab <?php echo $active_tab == 'templates' ? 'nav-tab-active' : ''; ?>"><?php _e('Templates', 'quip-invoices'); ?></a>
        <?php do_action('quip_invoices_invoices_page_tabs', $active_tab); ?>
    </h2>
    <div class="tab-content">
        <?php if ($active_tab == 'view'): ?>
            <form id="searchInvoicesForm" method="get" action="" style="padding-top: 10px;">
                <p class="search-box">
                    <input type="hidden" name="page" value="quip-invoices-invoices"/>
                    <input type="hidden" name="tab" value="<?php echo $active_tab; ?>"/>
                    <label class="screen-reader-text" for="post-search-input">Search Invoices:</label>
                    <input type="search" id="post-search-input" name="s" value="">
                    <input type="submit" id="search-submit" class="button" value="Search Invoices">
                </p>
                <?php if (isset($_GET['s']) && !empty($_GET['s'])): ?>
                    <h4>Search results for "<?php echo sanitize_text_field($_GET['s']); ?>"</h4>
                <?php endif; ?>
            </form>
            <form method="post" id="bulk-action-form">
                <input type="hidden" name="type" value="invoices" />
                <div class="qu-list-table">
                    <?php $table->display(); ?>
                </div>
            </form>
        <?php elseif ($active_tab == 'create'): ?>
            <?php if ($useTemplate): ?>
                <h4>Using template: <a href="<?php echo admin_url('admin.php?page=quip-invoices-edit&type=template&id=' . $t->id) ?>"><?php echo $t->name; ?></a></h4>
            <?php endif; ?>
            <form action="" method="post" id="quip-invoices-create-invoice-form">
                <input type="hidden" name="action" value="quip_invoices_create_invoice"/>
                <input type="hidden" name="invoiceType" value="invoice"/>
                <input type="hidden" name="allowPartialPayment" value="0" />
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceNumber"><?php _e('Invoice Number', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceNumber" id="invoiceNumber" class="regular-text code"
                                   value="<?php echo $options['nextInvoiceNumber']; ?>">
                            <p class="description"><?php _e('An identifier for your invoice', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceCreateDate"><?php _e('Invoice Date', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceCreateDate" id="invoiceCreateDate"
                                   class="regular-text datepicker">
                            <input type="hidden" name="invoiceCreateDateDB" id="invoiceCreateDateDB" value="">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceDueDate"><?php _e('Invoice Due Date', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceDueDate" id="invoiceDueDate"
                                   class="regular-text datepicker" autocomplete="off">
                            <input type="hidden" name="invoiceDueDateDB" id="invoiceDueDateDB" value="">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceRepeat"><?php _e('Recurring Invoice', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="checkbox" style="border-color: #000000;" name="invoiceRepeat" id="invoiceRepeat" value="1" disabled>
                            Recurring invoices are a PRO feature - find out more here.... TODO INSERT LINK
                            <p class="description"><?php _e('Automatically create future repeating invoices based on this one', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top" style="display: none;" id="invoiceRepeatSection">
                        <th scope="row">
                            <label for="invoiceRepeatFrequency"><?php _e('Recurring Frequency', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <select name="invoiceRepeatFrequency" id="invoiceRepeatFrequency">
                                <option value="weekly"><?php _e('Weekly', 'quip-invoices'); ?></option>
                                <option value="biweekly"><?php _e('Bi-weekly', 'quip-invoices'); ?></option>
                                <option value="monthly"><?php _e('Monthly', 'quip-invoices'); ?></option>
                                <option value="quarterly"><?php _e('Quarterly', 'quip-invoices'); ?></option>
                                <option value="yearly"><?php _e('Yearly', 'quip-invoices'); ?></option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceNotes"><?php _e('Notes', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <textarea cols="50" rows="5" name="invoiceNotes" id="invoiceNotes"
                                      class="regular-text"><?php echo ($useTemplate ? $template->notes : "") ?></textarea>
                            <p class="description"><?php _e('Add notes for the client on the invoice', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceClient"><?php _e('Client', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceClientText" id="invoiceClientText" class="regular-text"/>
                            <input type="hidden" name="invoiceClient" id="invoiceClient"/>
                            <a href="new" id="createNewClient" class="button button-secondary"><?php _e('Add New', 'quip-invoices'); ?></a>
                            <p class="description"><?php _e('Start typing to select the client. Will search by name, email or phone.', 'quip-invoices'); ?></p>
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
                        <?php if ($useTemplate): ?>
                            <?php foreach ($template->lineItems as $li): ?>
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
                        <?php endif; ?>
                        <tr class="qi-new-line-item">
                            <td>
                                <input type="text" name="liTitle" id="liTitle" class="regular-text">
                            </td>
                            <td>
                                <input type="text" name="liRate" id="liRate" class="qi-input-mini" value="0">
                            </td>
                            <td>
                                <input type="text" name="liQty" id="liQty" class="qi-input-mini" value="1">
                            </td>
                            <td>
                                <input type="text" name="liAdj" id="liAdj" class="qi-input-mini" value="0">
                            </td>
                            <td>
                                <span id="liAmount"></span>
                            </td>
                            <td>
                                <button id="addLineItemButton"
                                        class="button button-primary"><?php _e('Add', 'quip-invoices'); ?></button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div id="line-item-totals" class="clearfix">
                    <p style="line-height: 2.2em;">
                        <strong><?php _e('Sub Total', 'quip-invoices'); ?>: </strong><span id="liSubTotal"></span><br/>
                        <strong><?php _e('Tax', 'quip-invoices'); ?> %: </strong><input type="text"
                                                                                        class="qi-input-mini"
                                                                                        name="invoiceTaxRate"
                                                                                        id="invoiceTaxRate"
                                                                                        value="<?php echo ($useTemplate ? $template->tax : 0) ?>"><br/>
                        <strong><?php _e('Total', 'quip-invoices'); ?>: </strong><span id="liTotal"></span><br/>
                    </p>
                </div>
                <div style="clear: both;"></div>
                <!-- clear the float -->
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyDetails"><?php _e('Your details', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <textarea rows="6" cols="50" id="companyDetails" name="companyDetails" style="display:none;"><?php echo $useTemplate ? str_replace("<br />", "", stripslashes($template->companyDetails)) : QuipInvoices::getInstance()->get_formatted_company_details(false); ?></textarea>
                            <?php $companyName = $options['companyName']; ?>
                            <address id="companyDetailsDisplay">
                                <?php if ($useTemplate): ?>
                                    <?php echo stripslashes($template->companyDetails); ?>
                                <?php else: ?>
                                    <?php if ($companyName !== ''): ?>
                                        <?php echo QuipInvoices::getInstance()->get_formatted_company_details(); ?>
                                    <?php else: ?>
                                        <strong><?php _e('No details set!', 'quip-invoices'); ?></strong>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </address>
                            <br/><a href="set-details" id="companyDetailsChange" class="button button-secondary"><?php _e('Change details', 'quip-invoices'); ?></a>
                            <button id="companyDetailsChangeSave" class="button button-primary" style="display:none;"><?php _e('Save Changes', 'quip-invoices'); ?></button>
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
                            <?php if ($useTemplate) { $paymentTypes = explode(',', $template->paymentTypes); } ?>
                            <?php if ($options['paymentProcessor'] == 'stripe'): ?>
                                <label>
                                    <input type="checkbox" name="paymentTypes[]" id="paymentTypeCC" value="1" <?php echo $useTemplate ? (in_array('1', $paymentTypes) ? 'checked="checked"' : '') : 'checked="checked"'  ?> ><?php _e('Credit Card', 'quip-invoices'); ?>
                                </label>
                                <br/><br/>
                            <?php endif; ?>
                            <?php if ($options['paymentProcessor'] == 'paypal'): ?>
                                <label>
                                    <input type="checkbox" name="paymentTypes[]" id="paymentTypePaypal" value="5" <?php echo $useTemplate ? (in_array('5', $paymentTypes) ? 'checked="checked"' : '') : 'checked="checked"'  ?> > Paypal
                                </label>
                                <br/><br/>
                            <?php endif; ?>
                            <label>
                                <input type="checkbox" name="paymentTypes[]" id="paymentTypeMail" value="2" <?php echo $useTemplate ? (in_array('2', $paymentTypes) ? 'checked="checked"' : '') : '' ?> ><?php _e('Pay via Mail', 'quip-invoices'); ?>
                            </label>
                            <br/><br/>
                            <label>
                                <input type="checkbox" name="paymentTypes[]" id="paymentTypePhone" value="3" <?php echo $useTemplate ? (in_array('3', $paymentTypes) ? 'checked="checked"' : '') : '' ?> ><?php _e('Pay via Phone', 'quip-invoices'); ?>
                            </label>
                        </td>
                    </tr>
                </table>
                <div id="paymentInstructionsSection" style="display:none;">
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="paymentInstructions"><?php _e('Payment Instructions', 'quip-invoices'); ?>
                                    :</label>
                            </th>
                            <td>
                                <textarea rows="4" cols="50" name="paymentInstructions" id="paymentInstructions" class="regular-text"><?php echo $useTemplate ? $template->paymentInstructions : "" ?></textarea>
                                <p class="description"><?php _e('Extra instructions for payment via phone or mail. Leave blank to not show on invoice.', 'quip-invoices'); ?></p>
                            </td>
                        </tr>
                    </table>
                </div>
                <p class="submit">
                    <button type="submit"
                            class="button button-primary"><?php _e('Create Invoice', 'quip-invoices'); ?></button>
                    <label style="padding-left: 10px;">
                        <input type="checkbox" name="saveAsTemplate" value="1" ><?php _e('Save as template', 'quip-invoices'); ?>
                    </label>
                    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                </p>
            </form>
        <?php elseif ($active_tab == 'templates'): ?>
            <div class="qu-list-table">
                <?php $templatesTable->display(); ?>
            </div>
        <?php endif; ?>

        <?php do_action('quip_invoices_invoices_page_tab_content', $active_tab); ?>
    </div>
</div>
<!-- dialog -->
<div id="deleteInvoiceDialog" title="Delete Invoice?" style="display:none;">
    <p><?php _e('This will delete this invoice and all related items. Are you sure?', 'quip-invoices'); ?></p>
</div>

<div id="deleteTemplateDialog" title="Delete Invoice?" style="display:none;">
    <p><?php _e('This will delete this template and cannot be undone. Are you sure?', 'quip-invoices'); ?></p>
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

<div id="searchClientDialog" title="Search Clients" style="display:none;">
    <div>
        <label for="searchText"><?php _e('Search Text', 'quip-invoices'); ?>:</label><br/>
        <input type="text" name="searchText" id="searchText" class="regular-text"><br/>
        <p class="description"><?php _e('You can search clients by name, email & phone number', 'quip-invoices'); ?></p>
    </div>
</div>