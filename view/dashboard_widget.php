<div class="main">
    <ul>
        <li>
            <p><strong>Total Received: </strong>
                <a href="<?php echo admin_url('admin.php?page=quip-invoices-payments&tab=view'); ?>"><span style="float: right !important;"><?php echo $localStrings['symbol'] . sprintf('%0.2f', QuipInvoices::getInstance()->report->get_total_received()/100); ?></span></a>
            </p>
        </li>
        <li>
            <p><strong>Total Billed: </strong>
                <a href="<?php echo admin_url('admin.php?page=quip-invoices-invoices&tab=view'); ?>"><span style="float: right !important;"><?php echo $localStrings['symbol'] . QuipInvoices::getInstance()->report->get_total_billed(); ?></span></a>
            </p>
        </li>
        <li>
            <p><strong>Amount Due: </strong>
                <a href="<?php echo admin_url('admin.php?page=quip-invoices-invoices&tab=view'); ?>"><span style="float: right !important;"><?php echo $localStrings['symbol'] . QuipInvoices::getInstance()->report->get_total_owed(); ?></span></a>
            </p>
        </li>
    </ul>
</div>
