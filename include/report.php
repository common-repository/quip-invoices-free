<?php
include_once 'base.controller.php';

if (!class_exists('QuipInvoicesReport'))
{
    class QuipInvoicesReport extends QuipInvoicesController
    {
        public function __construct()
        {
            parent::__construct();

            // hook to admin menu action to add extra scripts for this page only
            add_action('admin_print_scripts-quip-invoices_page_quip-invoices-reports', array($this, 'enqueue_scripts'));

            // get chart data hook
            add_action('wp_ajax_quip_invoices_get_year_chart', array($this, 'get_chart_data'));

            add_action('wp_ajax_quip_invoices_get_report_summaries', array($this, 'get_report_summaries'));
        }

        public function enqueue_scripts()
        {
            wp_enqueue_style('bootstrap-css', plugins_url('/css/quip-bootstrap.css', dirname(__FILE__)));

            wp_enqueue_script('chart-js', plugins_url('/js/chart.min.js', dirname(__FILE__)), array('jquery'));
        }

        /**
         * Get total of all payments received
         */
        public function get_total_received()
        {
            $result = $this->db->get_invoice_total_received();
            return isset($result->total) ? $result->total : 0;
        }

        public function get_total_received_year($year)
        {
            $result = $this->db->get_invoice_total_received_year($year);
            return isset($result->total) ? $result->total : 0;
        }

        /**
         * Get total of all invoices billed
         */
        public function get_total_billed()
        {
            $result = $this->db->get_invoice_total_billed();
            return isset($result->total) ? $result->total : 0;
        }

        /**
         * Get total of all invoices owed amount
         */
        public function get_total_owed()
        {
            $result = $this->db->get_invoice_total_owed();
            return isset($result->total) ? $result->total : 0;
        }

        public function get_total_owed_year($year)
        {
            $result = $this->db->get_invoice_total_owed_year($year);
            return isset($result->total) ? $result->total : 0;
        }

        /**
         * Get amount coming due in the next month
         */
        public function get_coming_due()
        {
            $start = date('Y-m-d');
            $end = new DateTime($start);
            $end->add(new DateInterval("P31D"));

            $result = $this->db->get_due_between_dates($start, $end->format('Y-m-d'));
            return isset($result->due) ? $result->due : 0;
        }

        /**
         * Get data for summary boxes at top of reports
         */
        public function get_report_summaries()
        {
            $year = isset($_POST['year']) ? $_POST['year'] : date('Y');
            $data = [
                'outstanding' => $this->get_total_owed_year($year),
                'received' => sprintf('%0.2f', $this->get_total_received_year($year)/100)
            ];

            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }

        /**
         * Get data for yearly chart on reports page
         */
        public function get_chart_data()
        {
            $year = isset($_POST['year']) ? $_POST['year'] : date('Y');

            // calculate monthly totals for billed and received money
            $data = [
                'billed' => $this->get_total_billed_by_month($year),
                'received' => $this->get_total_received_by_month($year)
            ];

            header("Content-Type: application/json");
            echo json_encode($data);
            exit;
        }

        public function get_total_received_by_month($year)
        {
            $billed = [];
            for($i = 0  ; $i < 12 ; ++$i) $billed[$i] = 0;

            $results = $this->db->get_invoice_total_received_by_month($year);

            foreach($results as $row)
            {
                $billed[$row->month] = sprintf('%0.2f',$row->total/100);
            }

            return $billed;
        }

        public function get_total_billed_by_month($year)
        {
            $received = [];
            for($i = 0  ; $i < 12 ; ++$i) $received[$i] = 0;

            $results = $this->db->get_total_billed_by_month($year);

            foreach($results as $row)
            {
                $received[$row->month] = $row->total;
            }

            return $received;
        }
    }
}