<?php
header("Content-type: text/csv");
header("Cache-Control: no-store, no-cache");
header('Content-Disposition: attachment; filename="QuipInvoicesExport_'. date("Ymd-His").'.csv"');

$type =  isset($_GET['type']) ? $_GET['type'] : 'invoices';

$outstream = fopen("php://output",'w');

if ($type == 'invoices')
{
    $spacer = ["","","","","","","",""];
    $headers = [
        __("Invoice Number", 'quip-invoices'),
        __("Invoice Date", 'quip-invoices'),
        __("Due Date", 'quip-invoices'),
        __("Client Name", 'quip-invoices'),
        __("Client Email", 'quip-invoices'),
        __("Tax", 'quip-invoices'),
        __("Total", 'quip-invoices'),
        __("Owed", 'quip-invoices')
    ];
    $lineItemHeaders = [
        "",
        "",
        __("Item", 'quip-invoices'),
        __("Rate", 'quip-invoices'),
        __("Quantity", 'quip-invoices'),
        __("Adjustment", 'quip-invoices'),
        __("Total", 'quip-invoices')
    ];

    fputcsv($outstream, $headers, ',', '"');

    $invoices = QuipInvoices::getInstance()->db->get_invoices();
    foreach ($invoices as $i)
    {
        $client = QuipInvoices::getInstance()->db->get_client($i->clientID);

        $row = array(
            $i->invoiceNumber,
            $i->invoiceDate,
            $i->dueDate,
            stripslashes($client->clientName),
            $client->clientEmail,
            $i->tax,
            $i->total,
            $i->owed,
        );

        fputcsv($outstream, $row, ',', '"');
        fputcsv($outstream, $spacer, ',', '"');
        // now line items
        fputcsv($outstream, $lineItemHeaders, ',', '"');
        $lineItems = QuipInvoices::getInstance()->db->get_line_items($i->invoiceID);
        foreach($lineItems as $li)
        {
            $row = array(
                "",
                "",
                $li->title,
                $li->rate,
                $li->quantity,
                $li->adjustment,
                $li->total,
            );

            fputcsv($outstream, $row, ',', '"');
        }

        fputcsv($outstream, $spacer, ',', '"');
    }
}
else if ($type == 'clients')
{
    $headers = [
        __("Client Name", 'quip-invoices'),
        __("Email", 'quip-invoices'),
        __("Phone", 'quip-invoices'),
        __("Address Line 1", 'quip-invoices'),
        __("Address Line 2", 'quip-invoices'),
        __("City", 'quip-invoices'),
        __("State", 'quip-invoices'),
        __("Zip", 'quip-invoices'),
        __("Country", 'quip-invoices'),
        "Stripe " . __("Customer", 'quip-invoices') . " ID"
    ];

    fputcsv($outstream, $headers, ',', '"');
    $clients = QuipInvoices::getInstance()->db->get_clients();
    foreach($clients as $c)
    {
        $row = array(
            stripslashes($c->clientName),
            $c->clientEmail,
            $c->clientPhone,
            $c->addressLine1,
            $c->addressLine2,
            $c->addressCity,
            $c->addressState,
            $c->addressZip,
            $c->addressCountry,
            $c->stripeCustomerID,
        );

        fputcsv($outstream, $row, ',', '"');
    }
}
else if ($type == 'payments')
{
    $headers = [
        __("Invoice Number", 'quip-invoices'),
        __("Client Name", 'quip-invoices'),
        __("Payment Amount", 'quip-invoices'),
        __("Payment Date", 'quip-invoices'),
        __("Payment Type", 'quip-invoices'),
        "Stripe . " . __("Payment", 'quip-invoices') . " ID"
    ];
    fputcsv($outstream, $headers, ',', '"');
    $payments = QuipInvoices::getInstance()->db->get_payments_for_export();
    foreach($payments as $p)
    {
        $type = '';
        if ($p->paymentType == 1) $type = __('Credit Card', 'quip-invoices');
        else if ($p->paymentType == 2) $type = __('Mail', 'quip-invoices');
        else if ($p->paymentType == 3) $type = __('Phone', 'quip-invoices');
        else if ($p->paymentType == 4) $type = __('In Person', 'quip-invoices');

        $row = array(
            $p->invoiceNumber,
            stripslashes($p->clientName),
            sprintf('%0.2f', $p->amount/100),
            $p->paymentDate,
            $type,
            $p->stripePaymentID
        );

        fputcsv($outstream, $row, ',', '"');
    }

}
else if ($type == 'quotes')
{
    $spacer = ["","","","","","","",""];
    $headers = [
        __("Quote Number", 'quip-invoices'),
        __("Quote Date", 'quip-invoices'),
        __("Expiry Date", 'quip-invoices'),
        __("Client Name", 'quip-invoices'),
        __("Client Email", 'quip-invoices'),
        __("Tax", 'quip-invoices'),
        __("Total", 'quip-invoices'),
        __("Owed", 'quip-invoices')
    ];
    $lineItemHeaders = [
        "",
        "",
        __("Item", 'quip-invoices'),
        __("Rate", 'quip-invoices'),
        __("Quantity", 'quip-invoices'),
        __("Adjustment", 'quip-invoices'),
        __("Total", 'quip-invoices')
    ];

    fputcsv($outstream, $headers, ',', '"');

    $quotes = QuipInvoices::getInstance()->db->get_quotes();
    foreach ($quotes as $i)
    {
        $client = QuipInvoices::getInstance()->db->get_client($i->clientID);

        $row = array(
            $i->invoiceNumber,
            $i->invoiceDate,
            $i->dueDate,
            stripslashes($client->clientName),
            $client->clientEmail,
            $i->tax,
            $i->total,
            $i->owed,
        );

        fputcsv($outstream, $row, ',', '"');
        fputcsv($outstream, $spacer, ',', '"');
        // now line items
        fputcsv($outstream, $lineItemHeaders, ',', '"');
        $lineItems = QuipInvoices::getInstance()->db->get_line_items($i->invoiceID);
        foreach($lineItems as $li)
        {
            $row = array(
                "",
                "",
                $li->title,
                $li->rate,
                $li->quantity,
                $li->adjustment,
                $li->total,
            );

            fputcsv($outstream, $row, ',', '"');
        }

        fputcsv($outstream, $spacer, ',', '"');
    }
}

fclose($outstream);
exit;