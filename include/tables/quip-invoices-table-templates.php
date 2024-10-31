<?php

class QuipInvoicesTableTemplates extends WP_List_Table
{
    function __construct()
    {
        parent::__construct(array(
            'singular' => __('Template', 'quip-invoices'), //Singular label
            'plural' => __('Templates', 'quip-invoices'), //plural label, also this well be one of the table css class
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
        }
    }

    /**
     * Define the columns that are going to be used in the table
     * @return array $columns, the array of columns to use with the table
     */
    function get_columns()
    {
        return $columns = array(
            'name' => __('Template Name', 'quip-invoices'),
            'lineItems' => __('Line Items', 'quip-invoices'),
            'companyDetails' => __('Company Details', 'quip-invoices'),
            'notes' => __('Notes', 'quip-invoices'),
            'payment' => __('Payment Options', 'quip-invoices'),
        );
    }

    /**
     * Decide which columns to activate the sorting functionality on
     * @return array $sortable, the array of columns that can be sorted by the user
     */
    public function get_sortable_columns()
    {
        return $sortable = array(
            'name' => array('name', false),
        );
    }

    /**
     * Prepare the table with different parameters, pagination, columns and table elements
     */
    function prepare_items()
    {
        global $wpdb;
        $screen = get_current_screen();

        // Preparing your query
        $query = "SELECT * FROM " . $wpdb->prefix . 'qi_templates';

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
                $template = json_decode($rec->template);
                //Open the line
                echo '<tr id="record_' . $rec->id . '">';
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
                        case "name":
                            $row = '<td ' . $attributes . '><strong><a href="' . admin_url('admin.php?page=quip-invoices-edit&type=template&id=' . $rec->id) . '" >' . $rec->name . '</a></strong>';
                            $row .= '<div class="row-actions visible">';
                            $row .= '<span><a href="' . admin_url('admin.php?page=quip-invoices-invoices&tab=create&template=' . $rec->id) . '">' . __('Create Invoice', 'quip-invoices') . '</a> | </span>';
                            $row .= '<span><a href="' . admin_url('admin.php?page=quip-invoices-edit&type=template&id=' . $rec->id) . '">' . __('Edit', 'quip-invoices') . '</a> | </span>';
                            $row .= '<span class="delete" ><a href="delete" class="delete-template" data-id="' . $rec->id . '" >' . __('Delete', 'quip-invoices') . '</a></span>';
                            $row .= '</div>';
                            $row .= '</td>';
                            echo $row;
                            break;
                        case "lineItems":
                            $col = "<td $attributes >";
                            foreach ($template->lineItems as $li)
                            {
                                $col .= "<p><small>{$li->title} - {$li->quantity} at {$localeStrings['symbol']}{$li->rate}</small></p>";
                            }
                            $col .= "</td>";
                            echo $col;
                            break;
                        case "companyDetails":
                            echo "<td $attributes><small>{$template->companyDetails}</small></td>";
                            break;
                        case "notes":
                            echo "<td $attributes><small>{$template->notes}</small></td>";
                            break;
                        case "payment":
                            $col = "<td $attributes >";
                            $col .= "<p><small><strong>Payment Types: </strong>" . $this->format_payment_types($template->paymentTypes) . "</small></p>";
                            $col .= "<p><small><strong>Payment Instructions: </strong>{$template->paymentInstructions}</small></p>";
                            $col .= "<p><small><strong>Allow Partial Payment: </strong>" . ($template->allowPartialPayment == 0 ? "No" : "Yes") . "</small></p>";
                            $col .= "</td>";
                            echo $col;
                            break;
                    }
                }

                //Close the line
                echo '</tr>';
            }
        }
    }

    private function format_payment_types($types)
    {
        return str_replace(
            array("1", "2", "3", "4", "5"),
            array("Credit Card", "Mail", "Phone", "In-Person", "Paypal"),
            $types);
    }
}
