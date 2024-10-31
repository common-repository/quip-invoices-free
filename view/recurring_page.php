<?php
 $parent = QuipInvoices::getInstance()->db->get_invoice(sanitize_text_field($_GET['id']));
?>

<div class="wrap">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>
    <h2><?php _e('Repeating Invoices for invoice: ' . $parent->invoiceNumber, 'quip-invoices'); ?></h2>
    <p>
    <a href="<?php echo  admin_url('admin.php?page=quip-invoices-edit&type=invoice&id=' . $parent->invoiceID); ?>" class="button button-primary"><?php _e('Edit Parent', 'quip-invoices'); ?></a>
    <a href="<?php echo admin_url('admin.php?page=quip-invoices-invoices'); ?>" class="button"><?php _e('Back to Invoices', 'quip-invoices'); ?></a>
    </p>
    <form method="post" id="bulk-action-form">
        <input type="hidden" name="type" value="invoices" />
        <div class="qu-list-table">
            <?php $table->display(); ?>
        </div>
    </form>
</div>
<!-- dialog -->
<div id="deleteInvoiceDialog" title="Delete Invoice?" style="display:none;">
    <p><?php _e('This will delete this invoice and all related items. Are you sure?', 'quip-invoices'); ?></p>
</div>