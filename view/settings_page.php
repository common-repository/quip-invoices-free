<?php
$options = get_option('quip_invoices_options');
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'basics';
$localeStrings = QuipInvoices::getInstance()->get_locale_strings();
$demo = defined('QUIP_INVOICES_DEMO_MODE') ? 'disabled="disabled"' : '';

?>
<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>
    <h2 class="nav-tab-wrapper">
        <a href="?page=quip-invoices-settings&tab=basics" class="nav-tab <?php echo $active_tab == 'basics' ? 'nav-tab-active' : ''; ?>"><?php _e('Basic', 'quip-invoices'); ?></a>
        <a href="?page=quip-invoices-settings&tab=payment" class="nav-tab <?php echo $active_tab == 'payment' ? 'nav-tab-active' : ''; ?>"><?php _e('Payment', 'quip-invoices'); ?></a>
        <a href="?page=quip-invoices-settings&tab=email" class="nav-tab <?php echo $active_tab == 'email' ? 'nav-tab-active' : ''; ?>"><?php _e('Email', 'quip-invoices'); ?></a>
        <a href="?page=quip-invoices-settings&tab=notifications" class="nav-tab <?php echo $active_tab == 'notifications' ? 'nav-tab-active' : ''; ?>"><?php _e('Notifications', 'quip-invoices'); ?></a>
        <a href="?page=quip-invoices-settings&tab=export" class="nav-tab <?php echo $active_tab == 'export' ? 'nav-tab-active' : ''; ?>"><?php _e('Export', 'quip-invoices'); ?></a>
        <?php do_action('quip_invoices_settings_page_tabs', $active_tab); ?>
    </h2>
    <div class="tab-content">
        <?php if ($active_tab == 'basics'): ?>
            <form action="" method="post" id="quip-invoices-settings-form">
                <input type="hidden" name="action" value="quip_invoice_update_settings"/>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyName"><?php _e('Company Name', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyName" id="companyName" class="regular-text" value="<?php echo stripslashes(htmlspecialchars($options['companyName'])); ?>">
                            <p class="description"><?php _e('Your default company name to be shown on invoices (can be customized per invoice)', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyEmail"><?php _e('Company Email', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyEmail" id="companyEmail" class="regular-text" value="<?php echo $options['companyEmail']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyPhone"><?php _e('Company Phone', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyPhone" id="companyPhone" class="regular-text" value="<?php echo $options['companyPhone']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyAddress1"><?php _e('Address Line 1', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyAddress1" id="companyAddress1" class="regular-text" value="<?php echo $options['companyAddress1']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyAddress2"><?php _e('Address Line 2', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyAddress2" id="companyAddress2" class="regular-text" value="<?php echo $options['companyAddress2']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyCity"><?php _e('City', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyCity" id="companyCity" class="regular-text" value="<?php echo $options['companyCity']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyState"><?php echo $localeStrings['state']; ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyState" id="companyState" class="regular-text" value="<?php echo $options['companyState']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyZip"><?php echo $localeStrings['zip']; ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyZip" id="companyZip" class="regular-text" value="<?php echo $options['companyZip']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyCountry"><?php _e('Country', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyCountry" id="companyCountry" class="regular-text" value="<?php echo $options['companyCountry']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="companyTaxID"><?php _e('Tax ID', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="companyTaxID" id="companyTaxID" class="regular-text" value="<?php echo $options['companyTaxID']; ?>">
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label><?php _e('Company Logo', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <div id="companyLogoImage" style="display:none;">
                                <img id="companyLogoSrc" src="<?php echo $options['companyLogo']; ?>"/>
                            </div>
                            <input id="companyLogo" type="text" name="companyLogo" value="<?php echo $options['companyLogo']; ?>"/>
                            <button id="uploadImageButton" class="button" type="button" value="Upload Image"><?php _e('Upload Image', 'quip-invoices'); ?></button>
                            <a href="clear" id="clearLogo"><?php _e('Clear Logo', 'quip-invoices'); ?></a>
                            <p class="description"><?php _e('Your logo to be included on invoices and quotes, max 300px wide.  Leave blank to use Company Name instead.', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoicePageBackgroundColor"><?php _e('Page Background Color', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoicePageBackgroundColor" id="invoicePageBackgroundColor" data-default-color="#cccccc" class="color-field" value="<?php echo $options['invoicePageBackgroundColor']; ?>">
                            <p class="description"><?php _e('The color of the page background behind the invoice.', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceBackgroundColor"><?php _e('Invoice Background Color', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="invoiceBackgroundColor" id="invoiceBackgroundColor" data-default-color="#ffffff" class="color-field" value="<?php echo $options['invoiceBackgroundColor']; ?>">
                            <p class="description"><?php _e('The background color of the invoice.', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceFont"><?php _e("Invoice Font: ", 'quip-invoices'); ?></label>
                        </th>
                        <td>
                            <select id="invoiceFont" name="invoiceFont">
                                <option value="Arial" <?php echo ($options['invoiceFont'] == 'Arial') ? 'selected="selected"' : '' ?>><?php _e('Arial', 'quip-invoices'); ?></option>
                                <option value="Verdana" <?php echo ($options['invoiceFont'] == 'Verdana') ? 'selected="selected"' : '' ?>><?php _e('Verdana', 'quip-invoices'); ?></option>
                                <option value="serif" <?php echo ($options['invoiceFont'] == 'serif') ? 'selected="selected"' : '' ?>><?php _e('serif', 'quip-invoices'); ?></option>
                                <option value="sans-serif" <?php echo ($options['invoiceFont'] == 'sans-serif') ? 'selected="selected"' : '' ?>><?php _e('sans-serif', 'quip-invoices'); ?></option>
                                <option value="monospace" <?php echo ($options['invoiceFont'] == 'monospace') ? 'selected="selected"' : '' ?>><?php _e('monospace', 'quip-invoices'); ?></option>
                                <option value="cursive" <?php echo ($options['invoiceFont'] == 'cursive') ? 'selected="selected"' : '' ?>><?php _e('cursive', 'quip-invoices'); ?></option>
                                <option value="fantasy" <?php echo ($options['invoiceFont'] == 'fantasy') ? 'selected="selected"' : '' ?>><?php _e('fantasy', 'quip-invoices'); ?></option>
                            </select>
                            <p class="description"><?php _e('The font to use on the invoice (where possible).', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="invoiceLineItemsStyle"><?php _e("Invoice Line Items Style: ", 'quip-invoices'); ?></label>
                        </th>
                        <td>
                            <select id="invoiceLineItemsStyle" name="invoiceLineItemsStyle">
                                <option value="table-plain" <?php echo ($options['invoiceLineItemsStyle'] == 'table-plain') ? 'selected="selected"' : '' ?>><?php _e('Plain', 'quip-invoices'); ?></option>
                                <option value="table-bordered" <?php echo ($options['invoiceLineItemsStyle'] == 'table-bordered') ? 'selected="selected"' : '' ?>><?php _e('Bordered', 'quip-invoices'); ?></option>
                                <option value="table-striped" <?php echo ($options['invoiceLineItemsStyle'] == 'table-striped') ? 'selected="selected"' : '' ?>><?php _e('Striped', 'quip-invoices'); ?></option>
                                <option value="table-bordered table-striped" <?php echo ($options['invoiceLineItemsStyle'] == 'table-bordered table-striped') ? 'selected="selected"' : '' ?>><?php _e('Bordered & Striped', 'quip-invoices'); ?></option>
                                <option value="table-condensed" <?php echo ($options['invoiceLineItemsStyle'] == 'table-condensed') ? 'selected="selected"' : '' ?>><?php _e('Condensed', 'quip-invoices'); ?></option>
                                <option value="table-bordered table-condensed" <?php echo ($options['invoiceLineItemsStyle'] == 'table-bordered table-condensed') ? 'selected="selected"' : '' ?>><?php _e('Bordered & Condensed', 'quip-invoices'); ?></option>
                                <option value="table-condensed table-striped" <?php echo ($options['invoiceLineItemsStyle'] == 'table-condensed table-striped') ? 'selected="selected"' : '' ?>><?php _e('Striped & Condensed', 'quip-invoices'); ?></option>
                                <option value="table-bordered table-condensed table-striped" <?php echo ($options['invoiceLineItemsStyle'] == 'table-bordered table-condensed table-striped') ? 'selected="selected"' : '' ?>><?php _e('Bordered, Striped & Condensed', 'quip-invoices'); ?></option>
                            </select>
                            <p class="description"><?php _e('The font to use on the invoice (where possible).', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" <?php echo $demo; ?>><?php _e('Save Settings', 'quip-invoices'); ?></button>
                    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                </p>
            </form>
        <?php elseif ($active_tab == 'email'): ?>
            <form action="" method="post" id="quip-invoices-email-settings-form">
                <input type="hidden" name="action" value="quip_invoice_update_email_settings"/>
                <h3 class="title"><?php _e('Invoice Email', 'quip-invoices'); ?></h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="emailDefaultSubject"><?php _e('Default Subject', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="emailDefaultSubject" id="emailDefaultSubject" class="regular-text" value="<?php echo $options['emailDefaultSubject']; ?>">
                            <p class="description"><?php _e('Default email subject line. Can be customized per invoice.', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="emailDefaultMessage"><?php _e('Email Message', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <?php wp_editor(stripslashes(base64_decode($options['emailDefaultMessage'])), 'emailDefaultMessage', array('media_buttons' => false, 'teeny' => true)); ?>
                            <p class="description"><?php _e('Default HTML email when sending an invoice to a client for the first time. You can use the following dynamic tags', 'quip-invoices'); ?>:
                                <br/>
                                <code>%%INVOICE_AMOUNT%%</code> - <?php _e('The invoice total amount value', 'quip-invoices'); ?>
                                <br/>
                                <code>%%INVOICE_DUE_DATE%%</code> - <?php _e('The invoice due date', 'quip-invoices'); ?>
                                <br/>
                                <code>%%INVOICE_LINK%%</code> - <?php _e('A link to view the invoice online', 'quip-invoices'); ?>
                                <br/>
                                <code>%%COMPANY_DETAILS%%</code> - <?php _e('Your company details for this invoice (Name, Address, Phone, Email)', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_NAME%%</code> - <?php _e('The client name for this invoice', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_CONTACT_NAME%%</code> - <?php _e('The client contact name', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_EMAIL%%</code> - <?php _e('The client email address', 'quip-invoices'); ?>
                                <br/>
                            </p>
                        </td>
                    </tr>
                </table>
                <h3 class="title"><?php _e('Invoice Reminder Email', 'quip-invoices'); ?></h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="emailDefaultReminderSubject"><?php _e('Default Subject', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="emailDefaultReminderSubject" id="emailDefaultReminderSubject" class="regular-text" value="<?php echo $options['emailDefaultReminderSubject']; ?>">
                            <p class="description"><?php _e('Default email subject line', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="emailDefaultReminderMessage"><?php _e('Reminder Email Message', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <?php wp_editor(stripslashes(base64_decode($options['emailDefaultReminderMessage'])), 'emailDefaultReminderMessage', array('media_buttons' => false, 'teeny' => true)); ?>
                            <p class="description"><?php _e('Default HTML email when sending an invoice reminder email. You can use the following dynamic tags', 'quip-invoices'); ?>:
                                <br/>
                                <code>%%INVOICE_AMOUNT%%</code> - <?php _e('The invoice total amount value', 'quip-invoices'); ?>
                                <br/>
                                <code>%%INVOICE_DUE_DATE%%</code> - <?php _e('The invoice due date', 'quip-invoices'); ?>
                                <br/>
                                <code>%%INVOICE_LINK%%</code> - <?php _e('A link to view the invoice online', 'quip-invoices'); ?>
                                <br/>
                                <code>%%COMPANY_DETAILS%%</code> - <?php _e('Your company details for this invoice (Name, Address, Phone, Email)', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_NAME%%</code> - <?php _e('The client name for this invoice', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_CONTACT_NAME%%</code> - <?php _e('The client contact name', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_EMAIL%%</code> - <?php _e('The client email address', 'quip-invoices'); ?>
                                <br/>
                            </p>
                        </td>
                    </tr>
                </table>
                <h3 class="title"><?php _e('Quote Email', 'quip-invoices'); ?></h3>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="emailDefaultQuoteSubject"><?php _e('Default Subject', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <input type="text" name="emailDefaultQuoteSubject" id="emailDefaultQuoteSubject" class="regular-text" value="<?php echo $options['emailDefaultQuoteSubject']; ?>">
                            <p class="description"><?php _e('Default email subject line for quotes', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="emailDefaultQuoteMessage"><?php _e('Email Message', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <?php wp_editor(stripslashes(base64_decode($options['emailDefaultQuoteMessage'])), 'emailDefaultQuoteMessage', array('media_buttons' => false, 'teeny' => true)); ?>
                            <p class="description"><?php _e('Default HTML email when sending a quote. You can use the following dynamic tags', 'quip-invoices'); ?>:
                                <br/>
                                <code>%%INVOICE_AMOUNT%%</code> - <?php _e('The quote grand total amount', 'quip-invoices'); ?>
                                <br/>
                                <code>%%INVOICE_LINK%%</code> - <?php _e('A link to view the quote online', 'quip-invoices'); ?>
                                <br/>
                                <code>%%COMPANY_DETAILS%%</code> - <?php _e('Your company details for this quote (Name, Address, Phone, Email)', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_NAME%%</code> - <?php _e('The client name for this quote', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_CONTACT_NAME%%</code> - <?php _e('The client contact name', 'quip-invoices'); ?>
                                <br/>
                                <code>%%CLIENT_EMAIL%%</code> - <?php _e('The client email address', 'quip-invoices'); ?>
                                <br/>
                            </p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" <?php echo $demo; ?> ><?php _e('Save Settings', 'quip-invoices'); ?></button>
                    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                </p>
            </form>
        <?php elseif ($active_tab == 'payment'): ?>
            <form action="" method="post" id="quip-invoices-payment-settings-form">
                <input type="hidden" name="action" value="quip_invoice_update_payment_settings"/>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label><?php _e("Payment Processor: ", 'quip-invoices'); ?> </label>
                        </th>
                        <td>
                            <label class="radio">
                                <input type="radio" name="paymentProcessor" id="processorPaypal" value="paypal" <?php echo ($options['paymentProcessor'] == 'paypal') ? 'checked' : '' ?>> Paypal
                            </label>
                            <label class="radio">
                                <input type="radio" name="paymentProcessor" id="processorStripe" value="stripe" <?php echo ($options['paymentProcessor'] == 'stripe') ? 'checked' : '' ?> > Stripe
                            </label>
                        </td>
                    </tr>
                </table>
                <div id="paypalPaymentSettings" class="paymentSettingsSection" <?php echo ($options['paymentProcessor'] !== 'paypal') ? 'style="display:none;"' : '' ?>>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="merchantID">Paypal <?php _e("Merchant ID: ", 'quip-invoices'); ?> </label>
                            </th>
                            <td>
                                <input type="text" name="merchantID" id="merchantID" value="<?php echo $options['merchantID']; ?>" class="regular-text code">
                                <p class="description"><?php _e('Your Paypal merchant ID is usually the email address used to sign into Paypal.','quip-invoices'); ?></p>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label><?php _e("API mode: ", 'quip-invoices'); ?> </label>
                            </th>
                            <td>
                                <label class="radio">
                                    <input type="radio" name="apiModePaypal" value="test" <?php echo ($options['apiMode'] == 'test') ? 'checked' : '' ?> > <?php _e('Sandbox', 'quip-invoices'); ?>
                                </label> <label class="radio">
                                    <input type="radio" name="apiModePaypal" value="live" <?php echo ($options['apiMode'] == 'live') ? 'checked' : '' ?>> <?php _e('Live', 'quip-invoices'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                <div id="stripePaymentSettings" class="paymentSettingsSection" <?php echo ($options['paymentProcessor'] !== 'stripe') ? 'style="display:none;"' : '' ?> >
                    <h2>
                        <div class="qi-banner-header">
                            Stripe is available in our PRO version
                        </div>
                        <div class="qi-banner-content">
                            Stripe payments and MUCC MUCH more are available in our Pro version<BR><BR><BR>
                            <a class="qi-banner-button" href="https://bit.ly/3fCpzU4" target="_blank">
                            More Info Here
                            </a><BR><BR>
                        </div>
                    </h2>
                    <table class="form-table">
                        <tr valign="top">
                            <th scope="row">
                                <label for="secretKey_test">Stripe <?php _e("Test Secret Key: ", 'quip-invoices'); ?> </label>
                            </th>
                            <td>
                                <input type="text" name="secretKey_test" id="secretKey_test" value="<?php echo $options['secretKey_test']; ?>" class="regular-text code" disabled>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="publishKey_test">Stripe <?php _e("Test Publishable Key: ", 'quip-invoices'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="publishKey_test" name="publishKey_test" value="<?php echo $options['publishKey_test']; ?>" class="regular-text code" disabled>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="secretKey_live">Stripe <?php _e("Live Secret Key: ", 'quip-invoices'); ?> </label>
                            </th>
                            <td>
                                <input type="text" name="secretKey_live" id="secretKey_live" value="<?php echo $options['secretKey_live']; ?>" class="regular-text code" disabled>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label for="publishKey_live">Stripe <?php _e("Live Publishable Key: ", 'quip-invoices'); ?></label>
                            </th>
                            <td>
                                <input type="text" id="publishKey_live" name="publishKey_live" value="<?php echo $options['publishKey_live']; ?>" class="regular-text code" disabled>
                            </td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">
                                <label>Stripe <?php _e("API mode: ", 'quip-invoices'); ?> </label>
                            </th>
                            <td>
                                <label class="radio">
                                    <input type="radio" name="apiMode" id="modeTest" value="test" <?php echo ($options['apiMode'] == 'test') ? 'checked' : '' ?>  disabled> <?php _e('Test', 'quip-invoices'); ?>
                                </label> <label class="radio">
                                    <input type="radio" name="apiMode" id="modeLive" value="live" <?php echo ($options['apiMode'] == 'live') ? 'checked' : '' ?>  disabled> <?php _e('Live', 'quip-invoices'); ?>
                                </label>
                            </td>
                        </tr>
                    </table>
                </div>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="currency"><?php _e("Payment Currency: ", 'quip-invoices'); ?></label>
                        </th>
                        <td>
                            <select id="currency" name="currency">
                                <option value="usd" <?php echo ($options['currency'] == 'usd') ? 'selected="selected"' : '' ?>><?php _e('United States Dollar', 'quip-invoices'); ?></option>
                                <option value="cad" <?php echo ($options['currency'] == 'cad') ? 'selected="selected"' : '' ?>><?php _e('Canadian Dollar', 'quip-invoices'); ?></option>
                                <option value="eur" <?php echo ($options['currency'] == 'eur') ? 'selected="selected"' : '' ?>><?php _e('Euro', 'quip-invoices'); ?></option>
                                <option value="gbp" <?php echo ($options['currency'] == 'gbp') ? 'selected="selected"' : '' ?>><?php _e('British Pound Sterling', 'quip-invoices'); ?></option>
                                <option value="aud" <?php echo ($options['currency'] == 'aud') ? 'selected="selected"' : '' ?>><?php _e('Australian Dollar', 'quip-invoices'); ?></option>
                            </select>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" <?php echo $demo; ?> ><?php echo __('Save Settings', 'quip-invoices') ?></button>
                    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                </p>
            </form>
        <?php elseif ($active_tab == 'notifications'): ?>
            <p class="alert alert-info">NOTE: Please read the <strong><a href="?page=quip-invoices-help">help page</a></strong> about setting up your web server for automatic notifications.</p>
            <form action="" method="post" id="quip-invoices-notification-settings-form">
                <input type="hidden" name="action" value="quip_invoice_update_notification_settings"/>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="sendNotifications"><?php _e('Admin Notifications', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <label class="radio">
                                <input type="radio" name="sendNotifications" value="1" <?php echo ($options['sendNotifications'] == 1) ? 'checked="checked"' : '' ?> > <?php _e('Yes', 'quip-invoices'); ?>
                            </label> <label class="radio">
                                <input type="radio" name="sendNotifications" value="0" <?php echo ($options['sendNotifications'] == 0) ? 'checked="checked"' : '' ?>> <?php _e('No', 'quip-invoices'); ?>
                            </label>
                            <p class="description"><?php _e('Send email notifications to the Admin when clients view and pay invoices', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="sendClientNotifications"><?php _e('Client Notifications', 'quip-invoices'); ?>:</label>
                        </th>
                        <td>
                            <label class="radio">
                                <input type="radio" name="sendClientNotifications" value="1" <?php echo ($options['sendClientNotifications'] == 1) ? 'checked="checked"' : '' ?> > <?php _e('Yes', 'quip-invoices'); ?>
                            </label> <label class="radio">
                                <input type="radio" name="sendClientNotifications" value="0" <?php echo ($options['sendClientNotifications'] == 0) ? 'checked="checked"' : '' ?>> <?php _e('No', 'quip-invoices'); ?>
                            </label>
                            <p class="description"><?php _e('Automatically send email notifications to your clients when invoices are due', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="sendInvoiceEmailDelay"><?php _e("Invoice Email Delay ", 'quip-invoices'); ?></label>
                        </th>
                        <td>
                            <select id="sendInvoiceEmailDelay" name="sendInvoiceEmailDelay">
                                <option value="0" <?php echo ($options['sendInvoiceEmailDelay'] == '0') ? 'selected="selected"' : '' ?>><?php _e('On Creation', 'quip-invoices'); ?></option>
                                <option value="3" <?php echo ($options['sendInvoiceEmailDelay'] == '3') ? 'selected="selected"' : '' ?>><?php _e('3 Days Later', 'quip-invoices'); ?></option>
                                <option value="7" <?php echo ($options['sendInvoiceEmailDelay'] == '7') ? 'selected="selected"' : '' ?>><?php _e('7 Days Later', 'quip-invoices'); ?></option>
                                <option value="14" <?php echo ($options['sendInvoiceEmailDelay'] == '14') ? 'selected="selected"' : '' ?>><?php _e('2 Weeks Later', 'quip-invoices'); ?></option>
                                <option value="31" <?php echo ($options['sendInvoiceEmailDelay'] == '31') ? 'selected="selected"' : '' ?>><?php _e('1 Month Later', 'quip-invoices'); ?></option>
                            </select>
                            <p class="description"><?php _e('How long to wait until sending the initial invoice email after the invoice is created', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="sendReminderEmailDelay"><?php _e("Reminder Email Delay ", 'quip-invoices'); ?></label>
                        </th>
                        <td>
                            <select id="sendReminderEmailDelay" name="sendReminderEmailDelay">
                                <option value="-7" <?php echo ($options['sendReminderEmailDelay'] == '-7') ? 'selected="selected"' : '' ?>><?php _e('7 Days Before', 'quip-invoices'); ?></option>
                                <option value="-3" <?php echo ($options['sendReminderEmailDelay'] == '-3') ? 'selected="selected"' : '' ?>><?php _e('3 Days Before', 'quip-invoices'); ?></option>
                                <option value="0" <?php echo ($options['sendReminderEmailDelay'] == '0') ? 'selected="selected"' : '' ?>><?php _e('On Due Date', 'quip-invoices'); ?></option>
                                <option value="3" <?php echo ($options['sendReminderEmailDelay'] == '3') ? 'selected="selected"' : '' ?>><?php _e('3 Days After', 'quip-invoices'); ?></option>
                                <option value="7" <?php echo ($options['sendReminderEmailDelay'] == '7') ? 'selected="selected"' : '' ?>><?php _e('7 Days After', 'quip-invoices'); ?></option>
                                <option value="14" <?php echo ($options['sendReminderEmailDelay'] == '14') ? 'selected="selected"' : '' ?>><?php _e('2 Weeks After', 'quip-invoices'); ?></option>
                                <option value="31" <?php echo ($options['sendReminderEmailDelay'] == '31') ? 'selected="selected"' : '' ?>><?php _e('1 Month After', 'quip-invoices'); ?></option>
                            </select>
                            <p class="description"><?php _e('How long to wait until sending the reminder email after the invoice due date', 'quip-invoices'); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <button type="submit" class="button button-primary" <?php echo $demo; ?> ><?php echo __('Save Settings', 'quip-invoices') ?></button>
                    <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                </p>
            </form>
        <?php elseif ($active_tab == 'export'): ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">
                        <label><?php _e("Invoices", 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <a href="<?php echo admin_url("admin.php?page=quip-invoices-export&noheader=true&type=invoices") ?>" class="button button-primary" <?php echo $demo; ?> ><?php _e("Export Invoices", 'quip-invoices'); ?></a>
                        <p class="description"><?php _e("Download a CSV file containing all invoices", 'quip-invoices'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php _e("Quotes", 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <a href="<?php echo admin_url("admin.php?page=quip-invoices-export&noheader=true&type=quotes") ?>" class="button button-primary" <?php echo $demo; ?> ><?php _e("Export Quotes", 'quip-invoices'); ?></a>
                        <p class="description"><?php _e("Download a CSV file containing all quotes", 'quip-invoices'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php _e("Clients", 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <a href="<?php echo admin_url("admin.php?page=quip-invoices-export&noheader=true&type=clients") ?>" class="button button-primary" <?php echo $demo; ?> ><?php _e("Export Clients", 'quip-invoices'); ?></a>
                        <p class="description"><?php _e("Download a CSV file containing all clients", 'quip-invoices'); ?></p>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row">
                        <label><?php _e("Payments", 'quip-invoices'); ?>:</label>
                    </th>
                    <td>
                        <a href="<?php echo admin_url("admin.php?page=quip-invoices-export&noheader=true&type=payments") ?>" class="button button-primary" <?php echo $demo; ?> ><?php _e("Export Payments", 'quip-invoices'); ?></a>
                        <p class="description"><?php _e("Download a CSV file containing all payments", 'quip-invoices'); ?></p>
                    </td>
                </tr>
            </table>

        <?php endif; ?>

        <?php do_action('quip_invoices_settings_page_tab_content', $active_tab); ?>
    </div>
</div>