<html>
<head>
    <meta charset="<?php bloginfo('charset'); ?>"/>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php _e('Quote', 'quip-invoices'); ?>: <?php echo $invoice->invoiceNumber; ?> | <?php wp_title('|', true, 'right'); ?></title>
    <link rel="profile" href="http://gmpg.org/xfn/11"/>
    <link rel="pingback" href="<?php bloginfo('pingback_url'); ?>"/>
    <?php qu_in_invoice_header($invoice); ?>
    <meta name="robots" content="noindex, nofollow"/>
</head>
<body style="background-color: #cccccc">
<div class="row" style="padding-top: 20px"></div>
<div class="container" style="background-color: #fff; padding-top: 20px;">
    <div class="row" style="display:none" id="error-message">
        <div class="col-md-12">
            <p class="alert alert-danger" id="error-message-text"></p>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4">
            <?php if ($options['companyLogo'] != ''): ?>
                <img src="<?php echo $options['companyLogo']?>" alt="Logo" class="qi-logoImg"/>
            <?php else: ?>
                <h2><?php echo stripslashes($options['companyName']); ?></h2>
            <?php endif; ?>
        </div>
        <div class="col-md-offset-2 col-md-2 qi-invoice-status">
        </div>
        <div class="col-md-4">
            <div class="pull-right">
                <p style="font-size: 16px; font-weight: bold"><?php _e('Quote', 'quip-invoices'); ?>: <?php echo $invoice->invoiceNumber; ?></p>
                <address>
                    <?php echo stripslashes($invoice->companyDetails); ?>
                </address>
                <?php
                    //Simon 2.0
                    //Opportunity to Print the invoice
                    $res = do_action('qi-add-print-quote-button');
                    echo $res;
                ?>
            </div>
        </div>
    </div>
    <div class="row">
        <hr/>
    </div>
    <div class="row qi-invoice-details">
        <div class="col-md-2">
            <address>
                <?php echo QuipInvoices::getInstance()->get_formatted_client_details($invoice->clientID); ?>
            </address>
        </div>
        <div class="col-md-10">
            <div class="pull-right">
                <dl class="dl-horizontal">
                    <dt><?php _e('Quote Number', 'quip-invoices'); ?>:</dt>
                    <dd><?php echo $invoice->invoiceNumber; ?></dd>
                    <dt><?php _e('Quote Date', 'quip-invoices'); ?>:</dt>
                    <dd><?php echo date('F jS Y', strtotime($invoice->invoiceDate)); ?></dd>
                    <?php if ($invoice->dueDate): ?>
                        <dt><?php _e('Quote Expiry', 'quip-invoices'); ?>:</dt>
                        <dd><?php echo date('F jS Y', strtotime($invoice->dueDate)); ?></dd>
                    <?php endif; ?>
                    <dt><?php _e('Quote Total', 'quip-invoices'); ?>:</dt>
                    <dd><?php echo $localeStrings['symbol'] . $invoice->total; ?></dd>
                </dl>
            </div>
        </div>
    </div>
    <div class="row qi-invoice-details-print">
        <div class="_25">
            <?php echo QuipInvoices::getInstance()->get_formatted_client_details($invoice->clientID); ?>
        </div>
        <div class="_75">
            <div class="pull-right">
                <p><strong><?php _e('Quote Number', 'quip-invoices'); ?>:</strong> <?php echo $invoice->invoiceNumber; ?></p>
                <p><strong><?php _e('Quote Date', 'quip-invoices'); ?>:</strong> <?php echo date('F jS Y', strtotime($invoice->invoiceDate)); ?></p>
                <?php if ($invoice->dueDate): ?>
                    <p><strong><?php _e('Quote Expiry', 'quip-invoices'); ?>:</strong> <?php echo date('F jS Y', strtotime($invoice->dueDate)); ?></p>
                <?php endif; ?>
                <p><strong><?php _e('Quote Total', 'quip-invoices'); ?>:</strong> <?php echo $localeStrings['symbol'] . $invoice->total; ?></p>
            </div>
        </div>
    </div>
    <div class="row">
        <hr/>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                    <tr>
                        <th><?php _e('Product/Service', 'quip-invoices'); ?></th>
                        <th><?php _e('Rate', 'quip-invoices'); ?></th>
                        <th><?php _e('Quantity', 'quip-invoices'); ?></th>
                        <th><?php _e('Adjustment', 'quip-invoices'); ?> (%)</th>
                        <th style="text-align: right;"><?php _e('Amount', 'quip-invoices'); ?></th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($invoice->lineItems as $li): ?>
                        <tr>
                            <td><?php echo $li->title; ?></td>
                            <td><?php echo $localeStrings['symbol'] . $li->rate; ?></td>
                            <td><?php echo $li->quantity; ?></td>
                            <td><?php echo $li->adjustment; ?></td>
                            <td style="text-align: right;"><?php echo $localeStrings['symbol'] . $li->total; ?></td>
                        </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <!-- totals -->
    <div class="row">
        <div class="col-md-12">
            <table class="table">
                <tr>
                    <td><strong><?php _e('Sub-total', 'quip-invoices'); ?>:</strong></td>
                    <td style="text-align: right;"><?php echo $localeStrings['symbol'] . $invoice->subTotal; ?></td>
                </tr>
                <tr>
                    <td><strong><?php _e('Tax', 'quip-invoices'); ?> (<?php echo $invoice->tax; ?>%):</strong></td>
                    <td style="text-align: right;"><?php echo $localeStrings['symbol'] . round($invoice->subTotal * ($invoice->tax / 100), 2);  ?></td>
                </tr>
                <tr>
                    <td><h3><?php _e('Grand Total', 'quip-invoices'); ?>:</h3></td>
                    <td style="text-align: right;"><h3><?php echo $localeStrings['symbol'] . $invoice->total; ?></h3></td>
                </tr>
            </table>
        </div>
    </div>

    <?php if ($invoice->notes): ?>
        <div class="row">
            <div class="col-md-12">
                <strong>Notes:</strong><br/>
                <pre><?php echo stripslashes($invoice->notes); ?></pre>
            </div>
        </div>
    <?php endif; ?>
</div>
<div class="row" style="padding-top: 20px"></div>
</body>
</html>
