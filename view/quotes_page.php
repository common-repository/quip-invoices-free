<?php
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'view';
$options = get_option('quip_invoices_options');
$localeStrings = QuipInvoices::getInstance()->get_locale_strings();
?>
<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>
    <h2 class="nav-tab-wrapper">
        <a href="?page=quip-invoices-quotes&tab=view" class="nav-tab <?php echo $active_tab == 'view' ? 'nav-tab-active' : ''; ?>"><?php _e('Quotes', 'quip-invoices'); ?></a>
        <a href="?page=quip-invoices-quotes&tab=create" class="nav-tab <?php echo $active_tab == 'create' ? 'nav-tab-active' : ''; ?>"><?php _e('Create New Quote', 'quip-invoices'); ?></a>
        <?php do_action('quip_invoices_quotes_page_tabs', $active_tab); ?>
    </h2>
    <div class="tab-content">
        <?php if ($active_tab == 'view'): ?>
            <form id="searchQuotesForm" method="get" action="" style="padding-top: 10px;">
                <p class="search-box">
                    <input type="hidden" name="page" value="quip-invoices-quotes"/>
                    <input type="hidden" name="tab" value="<?php echo $active_tab; ?>"/>
                    <label class="screen-reader-text" for="post-search-input">Search Quotes:</label>
                    <input type="search" id="post-search-input" name="s" value="">
                    <input type="submit" id="search-submit" class="button" value="Search Quotes">
                </p>
                <?php if (isset($_GET['s']) && !empty($_GET['s'])): ?>
                    <h4>Search results for "<?php echo sanitize_text_field($_GET['s']); ?>"</h4>
                <?php endif; ?>
            </form>
        <form method="post" id="bulk-action-form">
            <input type="hidden" name="type" value="quotes" />
            <div class="qu-list-table">
                <?php $table->display(); ?>
            </div>
        </form>
        <?php elseif ($active_tab == 'create'): ?>
            <form action="" method="post" id="quip-invoices-create-invoice-form">
                <input type="hidden" name="action" value="quip_invoices_create_quote"/>
                <input type="hidden" name="invoiceType" value="quote"/>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceNumber"><?php _e('Quote Number', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceNumber" id="invoiceNumber" class="regular-text code" value="<?php echo $options['nextInvoiceNumber'];?>">
                            <p class="description"><?php _e('An identifier for your quote', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceCreateDate"><?php _e('Quote Date', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceCreateDate" id="invoiceCreateDate" class="regular-text datepicker">
                            <input type="hidden" name="invoiceCreateDateDB" id="invoiceCreateDateDB" value="">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceDueDate"><?php _e('Quote Expiry Date', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceDueDate" id="invoiceDueDate" class="regular-text datepicker" autocomplete="off">
                            <input type="hidden" name="invoiceDueDateDB" id="invoiceDueDateDB" value="">
                            <p class="description">The date this quote expires. Leave blank for no expiry.</p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceNotes"><?php _e('Notes', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <textarea cols="50" rows="5" name="invoiceNotes" id="invoiceNotes" class="regular-text"></textarea>
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
                                    <option value="<?php echo $c->id; ?>"><?php echo stripslashes($c->clientName); ?></option>
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
                                <button id="addLineItemButton" class="button button-primary"><?php _e('Add', 'quip-invoices'); ?></button>
                            </td>
                        </tr>
                        </tbody>
                    </table>
                </div>
                <div id="line-item-totals" class="clearfix">
                    <p style="line-height: 2.2em;">
                        <strong><?php _e('Sub Total', 'quip-invoices'); ?>: </strong><span id="liSubTotal"></span><br/>
                        <strong><?php _e('Tax', 'quip-invoices'); ?> %: </strong><input type="text" class="qi-input-mini" name="invoiceTaxRate" id="invoiceTaxRate" value="0"><br/>
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
                            <textarea rows="6" cols="50" id="companyDetails" name="companyDetails" style="display:none;"><?php echo QuipInvoices::getInstance()->get_formatted_company_details(false); ?></textarea>
                            <?php $companyName = $options['companyName']; ?>
                            <address id="companyDetailsDisplay">
                                <?php if ($companyName !== ''): ?>
                                    <?php echo QuipInvoices::getInstance()->get_formatted_company_details(); ?>
                                <?php else: ?>
                                    <strong><?php _e('No details set!', 'quip-invoices'); ?></strong>
                                <?php endif; ?>
                            </address>
                            <br/><a href="set-details" id="companyDetailsChange" class="button button-secondary"><?php _e('Change details', 'quip-invoices'); ?></a>
                            <button id="companyDetailsChangeSave" class="button button-primary" style="display:none;"><?php _e('Save Changes', 'quip-invoices'); ?></button>
                            <a href="cancel" id="companyDetailsChangeCancel" style="display:none;" class="button button-secondary"><?php _e('Cancel', 'quip-invoices'); ?></a>
                            <p class="description"><?php _e('Your details are added to the quote.', 'quip-invoices'); ?>
                                <?php
                                $url = admin_url('admin.php?page=quip-invoices-settings');
                                $link = sprintf(
                                    wp_kses( __( 'Defaults to your <a href="%s">settings</a>, but you can customize them per quote.', 'quip-invoices' ),
                                        array(  'a' => array( 'href' => array() ) ) ), esc_url( $url ) );
                                echo $link;
                                ?>
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary"><?php _e('Create Quote', 'quip-invoices'); ?></button>
                    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                </p>
            </form>
        <?php endif; ?>

        <?php do_action('quip_invoices_quotes_page_tab_content', $active_tab); ?>
    </div>
</div>
<!-- dialog -->
<div id="deleteInvoiceDialog" title="Delete Quote?" style="display:none;">
    <p><?php _e('This will delete this quote and all related items. Are you sure?', 'quip-invoices'); ?></p>
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