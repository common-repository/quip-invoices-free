<?php

class QuipInvoicesTableInvoices extends WP_List_Table
{
    function __construct()
    {
        parent::__construct(array(
            'singular' => __('Invoice', 'quip-invoices'), //Singular label
            'plural' => __('Invoices', 'quip-invoices'), //plural label, also this well be one of the table css class
            'ajax' => false //We won't support Ajax for this table
        ));
    }

    /**
     * Add extra markup in the toolbars before or after the list
     * @param string $which , helps you decide if you add the markup after (bottom) or before (top) the list
     */
    function extra_tablenav($which)
    {
        if ($which == "top")
        {
            //The code that goes before the table is here
            echo '<div class="wrap">';
        }
        if ($which == "bottom")
        {
            //The code that goes after the table is there
            echo '</div>';

            //Simon 2.0
            //Now add the PDF Popup
            echo '
<div id="upgradePDFDialog" class="qi-upgrade-dialog" title="Upgrade to Pro Now" style="display:none;">
<p>
Upgrade to our Pro version to gain access to PDF versions of invoices and <i>much, much more...</i>
</p>
<BR>
<a href="https://bit.ly/3fCpzU4" target="_blank">Click here for more info...</a>
</div>';
        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns()
    {
        return $columns = array(
            'cb' => '<input type="checkbox" />',
            'invoiceNumber' => __('Invoice Number', 'quip-invoices'),
            'invoiceDate' => __('Date', 'quip-invoices'),
            'dueDate' => __('Due Date', 'quip-invoices'),
            'status' => __('Status', 'quip-invoices'),
            'clientID' => __('Client', 'quip-invoices'),
            'total' => __('Total', 'quip-invoices'),
            'owed' => __('Owed', 'quip-invoices'),
            'repeat' => __('Repeating', 'quip-invoices'),
        );
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns()
    {
        return $sortable = array(
            'invoiceNumber' => array('invoiceNumber', false),
            'clientID' => array('clientID', false),
            'dueDate' => array('dueDate', false),
            'total' => array('total', false),
            'owed' => array('owed', false)
        );
    }

    protected function get_bulk_actions()
    {
        $actions = array(
            'delete' => __( 'Delete' )
        );

        return $actions;
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items()
    {
        global $wpdb;
        $screen = get_current_screen();

        // Preparing your query
        $query = "SELECT * FROM " . $wpdb->prefix . 'qi_invoices' . " WHERE active=1 AND type='invoice'";
        // add recurring base invoices only
        $query .= ' AND (recurring = 0 or baseInvoiceID = invoiceID)';

        // check if we are searching too
        if (!empty($_GET['s']))
        {
            $search = sanitize_text_field($_GET['s']);
            $query .= " AND (invoiceNumber LIKE '%$search%')";
        }

        //Parameters that are going to be used to order the result
        $orderby = !empty($_REQUEST["orderby"]) ? esc_sql($_REQUEST["orderby"]) : 'ASC';
        $order = !empty($_REQUEST["order"]) ? esc_sql($_REQUEST["order"]) : '';
        if (!empty($orderby) && !empty($order))
        {
            $query .= ' ORDER BY ' . $orderby . ' ' . $order;
        }

        //Number of elements in your table?
        $totalitems = $wpdb->query($query); //return the total number of affected rows
        //How many to display per page?
        $perpage = 10;
        //Which page is this?
        $paged = !empty($_GET["paged"]) ? esc_sql($_GET["paged"]) : '';
        //Page Number
        if (empty($paged) || !is_numeric($paged) || $paged <= 0)
        {
            $paged = 1;
        }
        //How many pages do we have in total?
        $totalpages = ceil($totalitems / $perpage);
        //adjust the query to take pagination into account
        if (!empty($paged) && !empty($perpage))
        {
            $offset = ($paged - 1) * $perpage;
            $query .= ' LIMIT ' . (int)$offset . ',' . (int)$perpage;
        }

        // Register the pagination
        $this->set_pagination_args(array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page" => $perpage,
        ));
        //The pagination links are automatically built according to those parameters

        //Register the Columns
        $columns = $this->get_columns();
        $hidden = array();
        $sortable = $this->get_sortable_columns();
        $this->_column_headers = array($columns, $hidden, $sortable);

        // Fetch the items
        $this->items = $wpdb->get_results($query);
    }

    /**
     * Display the rows of records in the table
     * @return string, echo the markup of the rows
     */
    function display_rows()
    {
        //Get the records registered in the prepare_items method
        $records = $this->items;

        //Get the columns registered in the get_columns and get_sortable_columns methods
        list($columns, $hidden) = $this->get_column_info();

        //Loop for each record
        if (!empty($records))
        {
            $localeStrings = QuipInvoices::getInstance()->get_locale_strings();

            foreach ($records as $rec)
            {
                //Open the line
                echo '<tr id="record_' . $rec->invoiceID . '">';
                foreach ($columns as $column_name => $column_display_name)
                {
                    //Style attributes for each col
                    $class = "class='$column_name column-$column_name'";
                    $style = "";
                    if (in_array($column_name, $hidden)) $style = ' style="display:none;"';
                    $attributes = $class . $style;

                    //Display the cell
                    switch ($column_name)
                    {
                        case "cb":
                            $cb = "<input type='checkbox' name='checked[]' value='" . $rec->invoiceID . "' id='checkbox_" . $rec->invoiceID . "' />";
                            echo "<th scope='row' class='check-column'>$cb</th>";
                            break;
                        case "invoiceNumber":
                            $row = '<td ' . $attributes . '><strong><a href="' . admin_url('admin.php?page=quip-invoices-edit&type=invoice&id=' . $rec->invoiceID) . '" >' . $rec->invoiceNumber . '</a></strong>';
                            $row .= '<div class="row-actions visible">';
                            $row .= '<span><a href="' . site_url('?qinvoice=' . $rec->hash . '&view=admin') . '" class="view-invoice">' . __('View', 'quip-invoices') . '</a> | </span>';
                            $row .= '<span><a href="' . admin_url('admin.php?page=quip-invoices-details&type=invoice&id=' . $rec->invoiceID) . '" class="details-invoice">' . __('Details', 'quip-invoices') . '</a> | </span>';
                            $row .= '<span><a href="' . admin_url('admin.php?page=quip-invoices-send&type=invoice&id=' . $rec->invoiceID) . '" class="send-invoice">' . __('Send', 'quip-invoices') . '</a> | </span>';
                            $row .= '<span><a href="copy" data-id="' . $rec->invoiceID . '" class="copy-invoice">' . __('Copy', 'quip-invoices') . '</a> | </span>';
                            $row .= '<span><a href="pdf" data-id="' . $rec->invoiceID . '" class="pdf-invoice">' . __('PDF', 'quip-invoices') . '</a> | </span>';
                            $row .= '<span class="delete" ><a href="delete" data-id="' . $rec->invoiceID . '" class="delete-invoice">' . __('Delete', 'quip-invoices') . '</a></span>';
                            $row .= '</div>';
                            $row .= '</td>';
                            echo $row;
                            break;
                        case "status":
                            $status = QuipInvoices::getInstance()->invoice->calculate_invoice_status($rec->invoiceID);
                            $statusClass = '';
                            if ($status->pastDue) $statusClass = 'class="qi-past-due"';
                            if ($status->paid) $statusClass = 'class="qi-paid"';

                            $col = '<td ' . $attributes . '><strong ' . $statusClass . '>' . $status->displayStatus . '</strong>';
                            if ($status->sent && !$status->viewed && !$status->paid && !$status->paymentsReceived)
                                $col .= '<div><small>' . __('on', 'quip-invoices') . ' ' . date('F jS \a\t g:ia', strtotime($rec->sent)) . '</small></div>';
                            else if ($status->viewed && !$status->paid && !$status->paymentsReceived)
                                $col .= '<div><small>' . __('on', 'quip-invoices') . ' ' . date('F jS \a\t g:ia', strtotime($rec->viewed)) . '</small></div>';
                            $col .= '</td>';
                            echo $col;
                            break;
                        case "clientID":
                            $client = QuipInvoices::getInstance()->db->get_client($rec->clientID);
                            echo '<td ' . $attributes . '><a href="' . admin_url('admin.php?page=quip-invoices-details&type=client&id=' . $rec->clientID) . '">' . stripslashes($client->clientName) . '</a></td>';
                            break;
                        case "total":
                            echo '<td ' . $attributes . '>' . $localeStrings['symbol'] . sprintf('%0.2f', $rec->total) . '</td>';
                            break;
                        case "owed":
                            echo '<td ' . $attributes . '>' . $localeStrings['symbol'] . sprintf('%0.2f', $rec->owed) . '</td>';
                            break;
                        case "invoiceDate":
                            echo '<td ' . $attributes . '>' . date('F jS Y', strtotime($rec->invoiceDate)) . '</td>';
                            break;
                        case "dueDate":
                            echo '<td ' . $attributes . '>' . date('F jS Y', strtotime($rec->dueDate)) . '</td>';
                            break;
                        case "repeat":
                            echo '<td ' . $attributes . '>';
                            if ($rec->recurring)
                            {
                                echo '<strong>Yes</strong><br/><small><a href="' . admin_url('admin.php?page=quip-invoices-recurring&type=invoice&id=' . $rec->invoiceID) . '">View Invoices</a></small>';
                            }
                            else
                            {
                                echo '<strong>No</strong>';
                            }
                            echo '</td>';
                            break;
                    }
                }

                //Close the line
                echo '</tr>';
            }
        }
    }
}
