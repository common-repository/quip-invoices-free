jQuery(document).ready(function ($)
{
    var $loading = $(".showLoading");
    $loading.show();
    var selectedYear = quip_invoices.reportYear;
    var chartOptions =
    {
        title: {
            display: true,
            text: selectedYear,
            fontSize: 16,
            padding: 0
        },
        tooltips: {
            mode: 'point'
        }

    };
    var billedSettings =
    {
        label: 'Billed',
        borderColor: 'rgba(25, 25, 255, 0.8)',
        backgroundColor: 'rgba(25, 25, 255, 0.8)',
        fill: false,
        lineTension: 0,
        pointRadius: 4
    };
    var receivedSettings =
    {
        label: 'Received',
        borderColor: 'rgba(25, 255, 25, 0.8)',
        backgroundColor: 'rgba(25, 255, 25, 0.8)',
        fill: false,
        lineTension: 0,
        pointRadius: 4
    };
    var labels = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    var chart = null;

    function loadChart()
    {
        $loading.show();
        if (chart) chart.destroy();

        // get the chart data for new year
        $.post(quip_invoices.ajaxurl, {action:'quip_invoices_get_year_chart', year: selectedYear}, function(data)
        {
            $loading.hide();
            billedSettings.data = data.billed;
            receivedSettings.data = data.received;
            chartOptions.title.text = selectedYear;

            // load up chart
            var ctx = document.getElementById("reportChart").getContext('2d');
            chart = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [billedSettings, receivedSettings]
                },
                options: chartOptions
            });
        });
    }

    function loadSummaries()
    {
        $.post(quip_invoices.ajaxurl, {action:'quip_invoices_get_report_summaries', year: selectedYear}, function(data)
        {
            $('#currentOutstanding').text(data.outstanding);
            $('#currentReceived').text(data.received);
            $('.selectedYear').text(selectedYear);
        });
    }

    $('#selectYear').change(function()
    {
        selectedYear = $(this).val();
        loadChart();
        loadSummaries();
    });

    // start by loading the chart
    loadChart();
});