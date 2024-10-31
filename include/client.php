<?php
include_once 'base.controller.php';

if (!class_exists('QuipInvoicesClient'))
{
    class QuipInvoicesClient extends QuipInvoicesController
    {
        public function __construct()
        {
            parent::__construct();

            //quick save new client (from invoice form)
            add_action('wp_ajax_quip_invoices_quick_create_client', array($this, 'quick_create_client'));
            //create new client
            add_action('wp_ajax_quip_invoices_create_client', array($this, 'create_client'));
            //edit client
            add_action('wp_ajax_quip_invoices_edit_client', array($this, 'edit_client'));
            //delete client
            add_action('wp_ajax_quip_invoices_delete_client', array($this, 'delete_client'));
            //delete clients
            add_action('wp_ajax_quip_invoices_delete_clients', array($this, 'delete_clients'));
            //search clients (from invoice form)
            add_action('wp_ajax_quip_invoices_search_clients', array($this, 'search_clients'));
        }

        /**
         * Create a client from only name and email address.  Used from create/edit
         * invoice form to quickly create a new client.
         */
        public function quick_create_client()
        {
            $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
            if ($email === FALSE) $this->json_exit(false, __("Please enter a valid email address.", 'quip-invoices'), '');

            $name = sanitize_text_field($_POST['name']);
            if (count($name) > 3) $this->json_exit(false, __("Please enter a longer name.", 'quip-invoices'), '');

            $wpUserID = $this->create_or_get_wp_user($name, $email);

            $id = $this->db->insert_client(array(
                'clientName' => $name,
                'clientEmail' => $email,
                'wpUserID' => $wpUserID,
                'created' => date('Y-m-d H:i:s')
            ));

            header("Content-Type: application/json");
            echo json_encode(array('success' => true, 'id' => $id));
            exit;
        }

        /**
         * Handle create client form submission
         */
        public function create_client()
        {
            $email = filter_var($_POST['clientEmail'], FILTER_VALIDATE_EMAIL);
            if ($email === FALSE) $this->json_exit(false, __("Please enter a valid email address.", 'quip-invoices'), '');

            $name = sanitize_text_field($_POST['clientName']);
            if (count($name) > 3) $this->json_exit(false, __("Please enter a longer name.", 'quip-invoices'), '');

            $wpUserID = $this->create_or_get_wp_user($name, $email);

            $this->db->insert_client(array(
                'clientName' => $name,
                'clientContactName' => sanitize_text_field($_POST['clientContactName']),
                'clientEmail' => $email,
                'clientAltEmails' => str_replace(' ', '', sanitize_text_field($_POST['clientAltEmails'])),
                'clientPhone' => sanitize_text_field($_POST['clientPhone']),
                'addressLine1' => sanitize_text_field($_POST['addressLine1']),
                'addressLine2' => sanitize_text_field($_POST['addressLine2']),
                'addressCity' => sanitize_text_field($_POST['addressCity']),
                'addressState' => sanitize_text_field($_POST['addressState']),
                'addressZip' => sanitize_text_field($_POST['addressZip']),
                'addressCountry' => sanitize_text_field($_POST['addressCountry']),
                'wpUserID' => $wpUserID,
                'created' => date('Y-m-d H:i:s')
            ));

            $this->json_exit(true, "Client created.", admin_url('admin.php?page=quip-invoices-clients&tab=view'));
        }

        /**
         * Handle edit client form submission
         */
        public function edit_client()
        {
            $email = filter_var($_POST['clientEmail'], FILTER_VALIDATE_EMAIL);
            if ($email === FALSE) $this->json_exit(false, __("Please enter a valid email address.", 'quip-invoices'), '');

            $name = sanitize_text_field($_POST['clientName']);
            if (count($name) > 3) $this->json_exit(false, __("Please enter a longer name.", 'quip-invoices'), '');

            $wpUserID = $this->create_or_get_wp_user($name, $email);

            $clientID = $_POST['clientID'];

            $this->db->update_client($clientID, array(
                'clientName' => $name,
                'clientContactName' => sanitize_text_field($_POST['clientContactName']),
                'clientEmail' => $email,
                'clientAltEmails' => str_replace(' ', '', sanitize_text_field($_POST['clientAltEmails'])),
                'clientPhone' => sanitize_text_field($_POST['clientPhone']),
                'addressLine1' => sanitize_text_field($_POST['addressLine1']),
                'addressLine2' => sanitize_text_field($_POST['addressLine2']),
                'addressCity' => sanitize_text_field($_POST['addressCity']),
                'addressState' => sanitize_text_field($_POST['addressState']),
                'addressZip' => sanitize_text_field($_POST['addressZip']),
                'addressCountry' => sanitize_text_field($_POST['addressCountry']),
                'wpUserID' => $wpUserID
            ));

            $this->json_exit(true, "Client updated.", admin_url('admin.php?page=quip-invoices-clients&tab=view'));
        }

        /**
         * Handle soft delete of client triggered on clients table list
         */
        public function delete_client()
        {
            $this->db->delete_client(sanitize_text_field($_POST['id']));
            $this->json_exit(true, '', '');
        }

        public function delete_clients()
        {
            $ids = $_POST['ids'];
            $count = 0;
            foreach($ids as $id)
            {
                if ($id)
                {
                    $this->db->delete_client($id);
                    $count++;
                }
            }

            $this->json_exit(true, $count . " " . __("Clients Deleted Successfully"), '');
        }

        /**
         * Search clients by term. Used in autocomplete invoice form client select
         */
        public function search_clients()
        {
            $term = sanitize_text_field($_POST['term']);

            $clients = $this->db->search_clients($term);

            header("Content-Type: application/json");
            echo json_encode($clients);
            exit;
        }

        /**
         * Check if a wordpress user exists with email, if not, create new one and return ID
         */
        private function create_or_get_wp_user($name, $email)
        {
            // If the email exists, just use that WordPress user, otherwise create one.
            $wpUserID = email_exists($email);

            if (!$wpUserID)
            {
                // create a wp username & password for the client
                $username = sanitize_user($name, true);
                //TODO: look into sending this password to the user/admin in a nice way
                $password = wp_generate_password($length = 12, $include_standard_special_chars = false);

                $wpUserID = username_exists($username);
                if (!$wpUserID)
                {
                    $wpUserID = wp_create_user($username, $password, $email);
                }
                else
                {
                    $username .= '_' . date('his');
                    $wpUserID = wp_create_user($username, $password, $email);
                }
            }

            return $wpUserID;
        }

    }
}
