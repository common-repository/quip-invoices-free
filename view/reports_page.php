<?php
$localStrings = QuipInvoices::getInstance()->get_locale_strings();
$outstanding = QuipInvoices::getInstance()->report->get_total_owed_year(date('Y'));
$received = sprintf('%0.2f', QuipInvoices::getInstance()->report->get_total_received_year(date('Y')) / 100);
$due = QuipInvoices::getInstance()->report->get_coming_due();
$currentYear = date('Y');
$years = [$currentYear - 1, $currentYear - 2, $currentYear - 3, $currentYear - 4];

?>
<div class="wrap qi-bootstrap-wrapper">
    <img src="<?php echo plugins_url('/img/logo.png', dirname(__FILE__)); ?>" alt="Quip Invoices"/>
    <div id="updateDiv" style="display:none;"></div>
    <h2>Reports</h2>
    <div class="row" style="padding-bottom: 5px;">
        <div class="col-md-12">
            <form id="changeYearForm" class="form-inline pull-right">
                <div class="form-group">
                    <label for="selectYear"><?php _e('Select Year', 'quip-invoices'); ?>:</label>
                    <select id="selectYear" class="form-control">
                        <option value="<?php echo $currentYear; ?>" selected="selected"><?php echo $currentYear; ?></option>
                        <?php foreach ($years as $y): ?>
                            <option value="<?php echo $y; ?>"><?php echo $y; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>
    <div class="row" style="text-align: center;">
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="qi-report-header-title">Coming Due</p>
                    <p class="qi-report-amount-due"><?php echo $localStrings['symbol'] ?>
                        <span id="currentDue"><?php echo $due; ?></span></p>
                    <p class="small">in the next month</p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="qi-report-header-title">Outstanding</p>
                    <p class="qi-report-amount-outstanding"><?php echo $localStrings['symbol'] ?>
                        <span id="currentOutstanding"><?php echo $outstanding; ?></span></p>
                    <p class="small">in <span class="selectedYear"><?php echo $currentYear; ?></span></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p class="qi-report-header-title">Received</p>
                    <p class="qi-report-amount-received"><?php echo $localStrings['symbol'] ?>
                        <span id="currentReceived"><?php echo $received; ?></span></p>
                    <p class="small">in <span class="selectedYear"><?php echo $currentYear; ?></span></p>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-body">
                    <p>
                        <img src="<?php echo plugins_url('/img/loader.gif', dirname(__FILE__)); ?>" alt="Loading..." class="showLoading"/>
                    </p>
                    <canvas id="reportChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>