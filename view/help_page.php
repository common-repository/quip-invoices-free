<div class="wrap about-wrap">
    <h1><?php _e('Quip Invoices Help', 'quip-invoices'); ?></h1>
    <div class="about-text"><?php _e("We're here to help.", 'quip-invoices'); ?> <?php _e('This section contains all you need to know to get started using Quip Invoices.', 'quip-invoices'); ?>
        <?php _e("If you need more assistance, please click the support button below to get in touch.", 'quip-invoices'); ?>
    </div>
    <p>
        <a href="http://quipcode.com/support-policy" class="button button-primary"><?php _e("Support", 'quip-invoices'); ?></a>
        <a href="http://quipcode.com" class="button button-primary"><?php _e("Visit our website", 'quip-invoices'); ?></a>
    </p>
    <div style="padding-top: 20px;"></div>
    <div id="contextual-help-wrap" tabindex="-1">

        <div id="contextual-help-columns">
            <div class="contextual-help-tabs">
                <ul>
                    <li id="tab-link-quick_start" class="active">
                        <a href="#tab-panel-quick_start" aria-controls="tab-panel-quick_start"> <?php _e("Quick Start", 'quip-invoices'); ?></a>
                    </li>
                    <li id="tab-link-invoices" class="">
                        <a href="#tab-panel-invoices" aria-controls="tab-panel-glossary"> <?php _e("Invoices & Quotes", 'quip-invoices'); ?></a>
                    </li>
                    <li id="tab-link-clients" class="">
                        <a href="#tab-panel-clients" aria-controls="tab-panel-glossary"> <?php _e("Clients", 'quip-invoices'); ?></a>
                    </li>
                    <li id="tab-link-payments" class="">
                        <a href="#tab-panel-payments" aria-controls="tab-panel-glossary"> <?php _e("Payments", 'quip-invoices'); ?></a>
                    </li>
                    <li id="tab-link-settings" class="">
                        <a href="#tab-panel-settings" aria-controls="tab-panel-glossary"> <?php _e("Settings", 'quip-invoices'); ?></a>
                    </li>
                    <li id="tab-link-notifications" class="">
                        <a href="#tab-panel-notifications" aria-controls="tab-panel-glossary"> <?php _e("Notifications", 'quip-invoices'); ?></a>
                    </li>
                    <li id="tab-link-reports" class="">
                        <a href="#tab-panel-reports" aria-controls="tab-panel-glossary"> <?php _e("Reports", 'quip-invoices'); ?></a>
                    </li>
                </ul>
            </div>
            <div class="contextual-help-tabs-wrap" style="background-color: #f6fbfd">
                <div id="tab-panel-quick_start" class="help-tab-content active" >
                    <h3 class="title"><?php _e("Quick Start", 'quip-invoices'); ?></h3>
                    <p><?php _e("The following steps are the minimum you need to get started using Quip Invoices", 'quip-invoices'); ?>:</p>
                    <ul>
                        <li><?php _e("Setup your company name and email address from the settings page.", 'quip-invoices'); ?>  <a href="<?php echo admin_url("admin.php?page=quip-invoices-settings"); ?>"><?php _e("View settings", 'quip-invoices'); ?></a></li>
                        <li><?php _e("If you want to accept credit cards, fill in your Stripe API details (Stripe is available in the <a href='https://quipcode.com' target='_blank'>Pro version</a>).", 'quip-invoices'); ?>  <a href="<?php echo admin_url("admin.php?page=quip-invoices-settings&tab=payment"); ?>"><?php _e("Payment settings", 'quip-invoices'); ?></a></li>
                        <li><?php _e("To accept Paypal, change the payment settings to Paypal and enter your Merchant ID.", 'quip-invoices'); ?></li>
                        <li><?php _e("Also on the payment settings page, select the currency you wish to use.", 'quip-invoices'); ?></li>
                        <li><?php _e("Check the email default settings to make sure you're happy with them. ", 'quip-invoices'); ?>  <a href="<?php echo admin_url("admin.php?page=quip-invoices-settings&tab=email"); ?>"><?php _e("Email settings", 'quip-invoices'); ?></a></li>
                        <li><?php _e("Remember, clients are associated to WordPress users so they can login and see their invoices too.", 'quip-invoices'); ?></li>
                        <li><?php _e("Now you can create your first invoice from the create invoice page! ", 'quip-invoices'); ?>  <a href="<?php echo admin_url("admin.php?page=quip-invoices-invoices&tab=create"); ?>"><?php _e("Create Invoice", 'quip-invoices'); ?></a></li>
                        <li><?php _e("Happy Invoicing!  Remember, you can always ask us for help if you need it. ", 'quip-invoices'); ?></li>
                    </ul>
                </div>
                <div id="tab-panel-invoices" class="help-tab-content">
                    <h3 class="title"><?php _e("Invoices & Quotes", 'quip-invoices'); ?></h3>
                    <p><?php _e("Invoices and Quotes are central to Quip Invoices and allow you to quickly and easily create and send detailed invoices and quotes to your customers.", 'quip-invoices'); ?></p>
                    <h4><?php _e("Invoices", 'quip-invoices'); ?></h4>
                    <p><?php _e("Invoices in Quip Invoices consist of the following data:", 'quip-invoices'); ?></p>
                    <ul>
                        <li><strong><?php _e("Invoice Number", 'quip-invoices'); ?>:</strong> <?php _e("A unique identifier for your invoice, can be any combination of text, symbols and numbers and should not be the same as another invoice or quote.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Invoice Date", 'quip-invoices'); ?>:</strong> <?php _e("The date the invoice was created.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Due Date", 'quip-invoices'); ?>:</strong> <?php _e("The deadline for payment of this invoice.  After this date the invoice will be marked as PAST DUE.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Repeating", 'quip-invoices'); ?>:</strong> <?php _e("This marks the invoice as a parent of a recurring series of invoices which will be created automatically on saving this invoice.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Notes", 'quip-invoices'); ?>:</strong> <?php _e("You can add notes to the invoice for your customer.  These can be about anything you like and are added to the bottom of the invoice.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Client", 'quip-invoices'); ?>:</strong> <?php _e("This is the client/customer the invoice is for.  All invoices must be for a specific client and you can quickly create new clients when creating your invoices.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Your Details", 'quip-invoices'); ?>:</strong> <?php _e("These are your company/personal details added to the invoice to show who is issuing the invoice.  They default to your company settings but you can customize per invoice too.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Partial Payments", 'quip-invoices'); ?>:</strong> <?php _e("You can choose to allow or disallow partial payment of invoices.  If allowed, when viewing the invoice your customer is able to select a custom payment amount when paying by credit card.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Payment Types", 'quip-invoices'); ?>:</strong> <?php _e("Choose which payment types you accept.  These will be shown on the invoice the customer sees.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Payment Instructions", 'quip-invoices'); ?>:</strong> <?php _e("If you choose mail or phone payments you can give extra instructions on how to make payment, such as bank details or times to call.", 'quip-invoices'); ?></li>
                        <li>
                            <strong><?php _e("Line Items", 'quip-invoices'); ?>:</strong>
                            <?php _e("These are the products or services you are invoicing your customer for.", 'quip-invoices'); ?>  <?php _e("Line items have the following data", 'quip-invoices'); ?>:
                            <ul>
                                <li><strong><?php _e("Item", 'quip-invoices'); ?>:</strong><?php _e("This is the name of the product or service you are billing for", 'quip-invoices'); ?></li>
                                <li><strong><?php _e("Rate", 'quip-invoices'); ?>:</strong><?php _e("The base rate you charge for this product or service", 'quip-invoices'); ?></li>
                                <li><strong><?php _e("Quantity", 'quip-invoices'); ?>:</strong><?php _e("The quantity of the product or service", 'quip-invoices'); ?></li>
                                <li><strong><?php _e("Adjustment", 'quip-invoices'); ?>:</strong><?php _e("A percentage adjustment of the line item amount.  For example a discount or per-item tax.", 'quip-invoices'); ?></li>
                            </ul>
                        </li>
                        <li><strong><?php _e("Tax Rate", 'quip-invoices'); ?>:</strong> <?php _e("You can set the tax rate (as a percentage) for the invoice which is calculated automatically.", 'quip-invoices'); ?></li>
                    </ul>
                    <h4><?php _e("Invoice Actions", 'quip-invoices'); ?></h4>
                    <p><?php _e("Once created, you can easily view the invoice from the Invoice page where it will be displayed in a sortable table.", 'quip-invoices'); ?>
                        <?php _e("From this table you may perform the following actions ", 'quip-invoices'); ?>:</p>
                    <ul>
                        <li><strong><?php _e("Edit", 'quip-invoices'); ?></strong>: <?php _e("Edit the invoice by clicking the invoice number.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("View", 'quip-invoices'); ?></strong>: <?php _e("View the invoice as the customer will see it.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Details", 'quip-invoices'); ?></strong>: <?php _e("View details about the invoice including emails sent and payments received.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Send", 'quip-invoices'); ?></strong>: <?php _e("Send the invoice via email.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Copy", 'quip-invoices'); ?></strong>: <?php _e("Make an exact copy of this invoice.  Useful for quickly creating new invoices.", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Delete", 'quip-invoices'); ?></strong>: <?php _e("This will delete the invoice and all line items.", 'quip-invoices'); ?></li>
                    </ul>
                    <h4><?php _e("Sending Invoices", 'quip-invoices'); ?></h4>
                    <p><?php _e("Once created, you can send invoices via email by clicking the Send link for the invoice from the Invoices page.", 'quip-invoices'); ?>
                        <?php _e("This will take you to a page where you can decide on the email content as well as choose to send to multiple email addresses.  The client data is pre-filled for your convenience, as are the email defaults taken from your settings.", 'quip-invoices'); ?></p>
                    <?php _e("Once sent, the invoice status will be updated to Sent and Quip Invoices will track when the client views the invoice.", 'quip-invoices'); ?>
                    <h4><?php _e("Repeating/Recurring Invoices", 'quip-invoices'); ?></h4>
                    <p>
                        <?php _e("Making an invoice repeating means we will automatically create future invoices based on the frequency you select.", 'quip-invoices'); ?>
                        <?php _e("For example, if you choose a 'Yearly' frequency, Quip Invoices will create yearly future invoices based on the invoice you choose to make repeating.", 'quip-invoices'); ?>
                        <?php _e("You can choose to make an invoice repeat weekly, bi-weekly (every 2 weeks), monthly, quarterly (every 3 months) and yearly.", 'quip-invoices'); ?>
                    </p>
                    <p>
                        <?php _e("When you choose to make an invoice repeating, that invoice will be considered the 'Parent' invoice and to view all the related invoices in the series you must click the 'view invoices' link in the invoices table.", 'quip-invoices'); ?>
                        <?php _e("Repeating invoices operate exactly the same as normal invoices with regards to sending, viewing, paying etc. Repeating invoices are simply a convenience to create multiple recurring invoices at the same time.", 'quip-invoices'); ?>
                    </p>
                    <p><?php _e("Note that all repeating invoices in the series are created at the time of creating a repeating invoice so this operation may take slightly longer than usual.", 'quip-invoices'); ?></p>
                    <h5><?php _e("Deleting Repeating Invoices", 'quip-invoices'); ?></h5>
                    <p><?php _e("To delete all child invoices in a repeating invoice series, you can edit the parent invoice to unselect the 'Repeating' checkbox. This will delete all related invoices and mark the parent as non-repeating.", 'quip-invoices'); ?></p>
                    <p><?php _e("If you delete a child invoice, only that invoice will be removed and no others in the repeating series will be affected.", 'quip-invoices'); ?></p>
                    <h4><?php _e("Quotes", 'quip-invoices'); ?></h4>
                    <p><?php _e("Quotes are very similar to invoices (internally they are treated almost the same) except they are only used to give prices to clients, not accept payments.", 'quip-invoices'); ?>
                        <?php _e("This means that all the data and actions you can do with Invoices you can do with Quotes except choosing payment options.", 'quip-invoices'); ?>
                        <?php _e("Also, Quotes will not be able to accept payments from customers, simply showing the grand total amount for the line items on the quote.", 'quip-invoices'); ?></p>
                    <h4><?php _e("Quote Actions", 'quip-invoices'); ?></h4>
                    <p><?php _e("Quotes have the same actions as Invoices, plus", 'quip-invoices'); ?>: </p>
                    <ul>
                        <li><strong><?php _e("Convert to Invoice", 'quip-invoices'); ?></strong>: <?php _e("This will convert a Quote to an Invoice, adding the ability to accept payment.", 'quip-invoices'); ?></li>
                    </ul>
                </div>
                <div id="tab-panel-clients" class="help-tab-content">
                    <h3 class="title"><?php _e("Clients", 'quip-invoices'); ?></h3>
                    <p><?php _e("Quip Invoices lets you store client details to make it easier to create invoices and track what is owed.", 'quip-invoices'); ?>
                        <?php _e("Creating a new client is straightforward, simply click the Create New Client tab from the Clients page and fill out information such as name, email and address.", 'quip-invoices'); ?>
                        <?php _e("You can also create new clients during creation of an invoice by clicking the 'Add New' link and adding the client name and email.", 'quip-invoices'); ?><br/>
                        <?php _e("When clients are created they will be associated with the WordPress user with the same email address.  If no WordPress user exists, a new one will be created using the client name and email you provide and a password will be automatically generated for them.", 'quip-invoices'); ?><br/>
                        <?php _e("Clients can login to your website using the standard WordPress login process and will be only be able to see the 'My Invoices' section in Quip Invoices.", 'quip-invoices'); ?></p>
                    <h4><?php _e("Client Details", 'quip-invoices'); ?></h4>
                    <p><?php _e("Clients have a details page, accessed from the Clients list action 'Details', which shows all the client invoices and any outstanding balances. ", 'quip-invoices'); ?></p>
                </div>
                <div id="tab-panel-payments" class="help-tab-content">
                    <h3 class="title"><?php _e("Payments", 'quip-invoices'); ?></h3>
                    <p><?php _e("The payments page keeps track of all received payments.", 'quip-invoices'); ?>  <?php _e("When a client pays an invoice via credit card directly, the payment data is stored and available from the Payments page.", 'quip-invoices'); ?>
                        <?php _e("Manually added payments are also stored here and you can easily view and sort payments as well as view the related invoice details.", 'quip-invoices'); ?></p>
                    <h4><?php _e("Manual Payments", 'quip-invoices'); ?></h4>
                    <p><?php _e("For invoice payments you receive via mail, phone or in person you can add a manual payment by clicking the 'Add Payment' tab.", 'quip-invoices'); ?>
                        <?php _e("Simply fill out the payment amount (in cents), the date and the type of payment as well as selecting the invoice to apply the payment too.  The invoice will be updated to show the payment received.", 'quip-invoices'); ?></p>
                    <h4><?php _e("Paypal", 'quip-invoices'); ?></h4>
                    <p><?php _e("For Paypal payments, simply enter your merchant ID and a Paypal button will be added to invoices you select to accept Paypal payments.", 'quip-invoices'); ?>
                        <?php _e("Due to how Paypal processes payments, there can be a delay between your customer paying and Quip Invoices receiving notification from Paypal IPN.", 'quip-invoices'); ?>
                        <?php _e("Other than that, Paypal payments will work the same as other payments and update your customer and invoice accordingly.", 'quip-invoices'); ?>
                    </p>
                </div>
                <div id="tab-panel-settings" class="help-tab-content">
                    <h3 class="title"><?php _e("Settings", 'quip-invoices'); ?></h3>
                    <p><?php _e("Setting up Quip Invoices is easy!", 'quip-invoices'); ?></p>
                    <h4><?php _e("Basic Settings", 'quip-invoices'); ?></h4>
                    <p><?php _e("Basic settings include adding values for your company name, email, phone and address which will be included on your invoices by default.  Only the company name and email are required however.", 'quip-invoices'); ?>
                        <?php _e("You can also set an image for company logo here, uploaded from your WordPress Media Library.  Recommended size around 200px wide by 50px height.", 'quip-invoices'); ?>
                        <?php _e("Leaving the logo image blank will use your company name on invoices instead.", 'quip-invoices'); ?></p>
                    <h4><?php _e("Email Settings", 'quip-invoices'); ?></h4>
                    <p><?php _e("The email settings allow you to set defaults for the subject line and message content of your invoice related emails.", 'quip-invoices'); ?>
                        <?php _e("You can use basic HTML in your email messages (use the Text tab on the editor) and there are also several dynamic tags", 'quip-invoices'); ?>:</p>
                    <ul>
                        <li><strong>%%INVOICE_AMOUNT%%</strong> - <?php _e('The invoice total amount value', 'quip-invoices'); ?></li>
                        <li><strong>%%INVOICE_DUE_DATE%%</strong> - <?php _e('The invoice due date', 'quip-invoices'); ?></li>
                        <li><strong>%%INVOICE_LINK%%</strong> - <?php _e('A link to view the invoice online', 'quip-invoices'); ?></li>
                        <li><strong>%%COMPANY_DETAILS%%</strong> - <?php _e('Your company details for this invoice (Name, Address, Phone, Email)', 'quip-invoices'); ?></li>
                        <li><strong>%%CLIENT_NAME%%</strong> - <?php _e('The client name for this invoice', 'quip-invoices'); ?></li>
                        <li><strong>%%CLIENT_CONTACT_NAME%%</strong> - <?php _e('The client contact name', 'quip-invoices'); ?></li>
                        <li><strong>%%CLIENT_EMAIL%%</strong> - <?php _e('The client email address', 'quip-invoices'); ?></li>
                    </ul>
                    <?php _e("Wherever in the email message you place one of the dynamic tags, Quip Invoices will automatically substitute the correct invoice/quote information before sending the email.", 'quip-invoices'); ?>
                    <h4><?php _e("Payment Settings", 'quip-invoices'); ?></h4>
                    <p><?php _e("If you'd like to accept credit cards, you first need a free account from Stripe", 'quip-invoices'); ?>. <a href="https://stripe.com"><?php _e("Get a free Stripe account here", 'quip-invoices'); ?></a>.
                            <?php _e("Once you have an account, fill out the API key fields on the payment settings page and Quip Invoices will take care of the rest.", 'quip-invoices'); ?>
                        <?php _e("If you'd prefer to use Payal, select the Paypal option and enter your Merchant ID here - this is usually the email address of your Paypal account.", 'quip-invoices'); ?>
                        <?php _e("Here you can also set the currency your account is using, which also determines the currency shown throughout the plugin and on invoices and quotes.", 'quip-invoices'); ?>
                        <?php _e("Finally, you can choose to put the plugin in Test mode which will only run test credit card payments.  Once you are ready to accept real payments please switch this to Live mode.", 'quip-invoices'); ?>
                    </p>
                    <h4><?php _e("Export", 'quip-invoices'); ?></h4>
                    <p><?php _e("Use the buttons on the Export page to download CSV files containing all of your invoices, payments and clients.", 'quip-invoices'); ?></p>
                </div>
                <div id="tab-panel-notifications" class="help-tab-content">
                    <h3 class="title"><?php _e("Automatic Notifications", 'quip-invoices'); ?></h3>
                    <p><?php _e("Automatic email notifications can be setup in the Settings menu of the plugin. Notifications can be configured to automatically send an email to the client in the following cases:", 'quip-invoices') ?></p>
                    <ul>
                        <li><?php _e("As soon as an invoice is created", 'quip-invoices') ?></li>
                        <li><?php _e("At a specific interval after the invoice is created, from 3 days up to 1 month later.", 'quip-invoices') ?></li>
                        <li><?php _e("On the invoice due date", 'quip-invoices') ?></li>
                        <li><?php _e("Up to 7 days before the invoice due date", 'quip-invoices') ?></li>
                        <li><?php _e("Up to 1 month after the invoice due date", 'quip-invoices') ?></li>
                    </ul>
                    <p><?php _e("The notification emails use the email templates from the Email Settings section. Invoice creation emails use the 'Invoice Email' template & subject line, and Due Date reminder emails use the 'Invoice Reminder Email' template & subject line.", 'quip-invoices') ?></p>
                    <h4><?php _e("Admin Notifications", 'quip-invoices') ?></h4>
                    <p><?php _e("You can also enabled administrator notification emails from the Notification settings tab. This will make Quip Invoices automatically send emails to the site admin whenever an invoice is viewed or paid by the client.", 'quip-invoices') ?></p>
                    <h3><?php _e("IMPORTANT: Notification Setup", 'quip-invoices') ?></h3>
                    <p><?php _e("In order for automatic email notifications to work, you MUST setup your web server to trigger cron jobs on a regular interval. A cron job is a recurring task on your web server that enables Quip Invoices to check notifications. It can be setup in multiple ways:", 'quip-invoices') ?></p>
                    <h5><?php _e("Using crontab", 'quip-invoices') ?></h5>
                    <p><?php _e("If you are proficient using the command line and have console access to your web server, you can use `crontab -e` and add the following line:", 'quip-invoices') ?></p>
                    <p><code>*/5 * * * * wget -q -O - <?php echo site_url(); ?>/wp-cron.php >/dev/null 2>&1</code></p>
                    <p><?php _e("This tells cron to visit your website to trigger the WordPress Cron process every 5 minutes.", 'quip-invoices') ?></p>
                    <h5><?php _e("Using cpanel", 'quip-invoices') ?></h5>
                    <p><?php _e("Most web hosting companies provide a backend interface to allow you to administer your web server. Good quality hosting companies will provide an option to add Cron jobs via a graphical interface.", 'quip-invoices') ?></p>
                    <p><?php _e("When adding the new job, make sure it is setup to trigger every 5 minutes and that the command it is set to run is: ", 'quip-invoices') ?></p>
                    <p><code>wget -q -O - <?php echo site_url(); ?>/wp-cron.php >/dev/null 2>&1</code></p>
                    <p><?php _e("If you get stuck with this, please ask your web hosting provider. Alternatively, we are always happy to try and help so send an email as we'll see what we can do.", 'quip-invoices') ?></p>
                    <h5><?php _e("Using external cron services", 'quip-invoices') ?></h5>
                    <p><?php _e("If you have no access to the web server console and your web hosting provider blocks Cron jobs (or has no option for them) then you can use an external cron service to achieve the same result.", 'quip-invoices') ?></p>
                    <p><?php _e("You can use a service such as www.easycron.com or Google for alternatives. Make sure to trigger the following command every 5 minutes:", 'quip-invoices') ?></p>
                    <p><code>wget -q -O - <?php echo site_url(); ?>/wp-cron.php >/dev/null 2>&1</code></p>
                    <p><?php _e("As always, if you get stuck with anything or require extra help please don't hesitate to get in touch with us.", 'quip-invoices') ?></p>
                </div>
                <div id="tab-panel-reports" class="help-tab-content">
                    <h3 class="title"><?php _e("Reports", 'quip-invoices'); ?></h3>
                    <p><?php _e("Version 1 of reports are now included in Quip Invoices!  The following information is included:", 'quip-invoices'); ?></p>
                    <ul>
                        <li><strong><?php _e("Coming Due", 'quip-invoices'); ?></strong> - <?php _e("This is the amount owed on invoices with a due date coming up in the next month (31 days).", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Outstanding", 'quip-invoices'); ?></strong> - <?php _e("The amount left owing to you on invoices issued in the selected year", 'quip-invoices'); ?></li>
                        <li><strong><?php _e("Received", 'quip-invoices'); ?></strong> - <?php _e("Payments received in the selected year.", 'quip-invoices'); ?></li>
                    </ul>
                    <p><?php _e("An overview chart is included that shows the totals billed and received by month for the selected year. You can change the year using the drop down in the top right to view previous years amounts.", 'quip-invoices'); ?></p>
                    <p><?php _e("More reports are planned in future but if you have specific feedback or recommendations please email us: ", 'quip-invoices'); ?><a href="mailto:jamie@quipcode.com">jamie@quipcode.com</a></p>
                </div>
            </div>
        </div>
    </div>
</div>