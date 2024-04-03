<?php

namespace wams\admin;

use GFFormsModel;
use GFAPI;
use wams\admin\core\Gravity_Functions;


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\admin\AJAX_Handler')) {

    /**
     * Class AJAX_Handler
     * Handle All Ajax Requests from Admin Panel
     * @package wams\admin
     */
    class AJAX_Handler
    {

        private $options = [];
        /**
         * @since 1.0.0
         *
         */

        public function __construct()
        {
        }

        public function db_cleanup_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-admin-nonce') || !current_user_can('manage_options')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }

            $gravity = new Gravity_Functions();
            switch ($_POST['param']) {
                case 'get_form_breakdown':
                    $archivable_forms = get_option('wams_archivable_forms');
                    $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : "1";
                    $result = $gravity->breakdown_entry_count($form_id);
                    require_once(WAMS()->admin()->templates_path .  'db-cleanup-forms-breakdown.php');
                    break;
                case 'archive_entries':
                    $message = [];

                    $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : false;
                    $year = isset($_REQUEST['year']) ? $_REQUEST['year'] : false;
                    if ($this->options['archive_site']) {
                        switch_to_blog($this->options['archive_site']);
                        $form_on_archive = GFAPI::get_form($form_id);
                        restore_current_blog();
                        if (!$form_on_archive) {
                            $gravity->copy_form($form_id, $current_blog_id, $this->options['archive_site']);
                        }

                        if ($year != date('Y')) {
                            $field_filters[] = array(
                                'key'   => 'workflow_final_status',
                                'operator' => 'not in',
                                'value' => array('pending')
                            );
                            $field_filters['mode'] = 'all';
                            $search_criteria['field_filters'] = $field_filters;
                            $search_criteria['start_date'] = date($year . '-01-01');
                            $search_criteria['end_date'] = date($year . '-12-31');
                            // print_r($search_criteria);
                            $entry_ids = GFAPI::get_entry_ids($form_id, $search_criteria);

                            $rows = 0;
                            foreach ($entry_ids as $entry_id) {
                                $gravity->copy_entries($entry_id, $current_blog_id, $this->options['archive_site']);
                                $delete_entry = GFAPI::delete_entry($entry_id);
                                if ($delete_entry) $rows++;
                            }
                            $message[] =  'Total Entries Moved To Archive: ' . $rows . ' !';
                            $total_rows = GFAPI::count_entries($form_id);
                            $message[] = 'Total Entry Left: ' . $total_rows . ' !';
                            wp_send_json(['message' => $message, 'status'  => true]);
                            wp_die();
                        } else {
                            wp_send_json([
                                'message' => ['this year entry is not allowed to be archived'],
                                'status'  => false
                            ]);
                            wp_die();
                        }
                    }

                    break;
                case 'cancel_workflow':
                    $message = [];
                    $form_id = isset($_REQUEST['form_id']) ? $_REQUEST['form_id'] : false;
                    $year = isset($_REQUEST['year']) ? $_REQUEST['year'] : false;
                    if ($year == date('Y')) {
                        $field_filters[] = array(
                            'key'   => 'workflow_final_status',
                            'operator' => 'in',
                            'value' => array('complete', 'approved', 'rejected')
                        );
                        $field_filters['mode'] = 'all';
                        $search_criteria['field_filters'] = $field_filters;
                    }

                    $search_criteria['start_date'] = date($year . '-01-01');
                    $search_criteria['end_date'] = date($year . '-12-31');
                    // print_r($search_criteria);
                    $entries = GFAPI::get_entry_ids($form_id, $search_criteria);
                    global $wpdb;
                    $entry_notes_table = $current_blog_id == 1 ?  $wpdb->prefix . 'gf_entry_meta' : $wpdb->prefix . $current_blog_id  . '_gf_entry_meta';
                    $rows = 0;
                    if ($year != date('Y')) {
                        foreach ($entries as $entry) {
                            $sql = $wpdb->prepare("DELETE FROM $entry_notes_table WHERE entry_id=%d AND meta_key like '%workflow_%'", $entry);
                            $result = $wpdb->query($sql);
                            $rows += $result;
                            if ($result === false) {
                                $message[] =  'error_deleteing' . $entry_id;
                            }
                        }
                        $message[] =  'Total Workflow Rows Deleted : ' . $rows . ' !';
                        $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $entry_notes_table");
                        $message[] = 'Total Entry Left: ' . $total_rows . ' !';
                        wp_send_json(['message' => $message, 'status'  => true]);
                        wp_die();
                    } else {
                        wp_send_json([
                            'message' => ['this year entries workflows is not allowed to be deleted'],
                            'status'  => false
                        ]);
                        wp_die();
                    }
                    break;
                case 'get_workflow_count':
                    global $wpdb;
                    $entry_notes_table = $current_blog_id == 1 ?  $wpdb->prefix . 'gf_entry_meta' : $wpdb->prefix . $current_blog_id  . '_gf_entry_meta';
                    $total_rows = $wpdb->get_var("SELECT COUNT(*) FROM $entry_notes_table");
                    wp_send_json([$total_rows]);
                    wp_die();
                    break;
            }
        }


        public function web_notifications_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-admin-nonce') || !current_user_can('manage_options')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }
            switch ($_POST['param']) {
                case 'install_page':
                    $new_page = array(
                        'post_title'    => 'Notifications List',
                        'post_content'  => '[wams_notifications]',
                        'post_status'   => 'publish',
                        'post_type'     => 'page'
                    );

                    // Insert the post into the database
                    $page_id = wp_insert_post($new_page);
                    if ($page_id) {
                        wp_send_json(['success', 'Notification page created']);
                    } else {
                        wp_send_json(['warning', 'Notification page could not be created!']);
                    }
                    wp_die();
                    break;
                case 'save_notifications_settings':
                    $enabled = $_POST['enabled'] ?? false;
                    $interval = $_POST['interval'] ?? 60;
                    $sound = $_POST['sound'] ?? false;
                    $options = [
                        'enabled' => $enabled,
                        'interval' => $interval,
                        'sound' => $sound,
                    ];
                    $save_option = update_option('wams_web_notitications_settings', $options);
                    if ($save_option) {
                        wp_send_json(['success', ' Notification settings has been saved']);
                    } else {
                        wp_send_json(['warning', 'No Changes!']);
                    }
                    wp_die();
                    break;
                case 'send_notification_test':
                    $message = $_POST['message'] ?? 'TEST Message';
                    $user_id = $_POST['user_id'] ?? get_current_user_id();
                    $arg = [
                        'notification_uri' => '/',
                        'message' => $message . ' # ' . rand(1, 100),
                    ];
                    WAMS()->web_notifications()->store_notification($user_id, "new-task", "New Message", $arg);
                    wp_send_json(['success', 'Test Message has been sent']);

                    break;
            }
        }
        public function telegram_notifications_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-admin-nonce') || !current_user_can('manage_options')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }
            switch ($_POST['param']) {
                case 'send_test_notification':
                    $message = $_POST['message'] ?? 'TEST Message';
                    $user_id = $_POST['user_id'] ?? get_current_user_id();
                    $channel_id = $_POST['user_channel_id'] ?? false;

                    WAMS()->telegram_notifications()->send_telegram_message($user_id, $message);
                    if (!$channel_id) WAMS()->telegram_notifications()->send_message_to_channel($channel_id, $message);
                    wp_send_json(['success', 'Test Message has been sent']);

                    break;
            }
        }

        /**
         * Check if The table name is exist and return true 
         */
        public function check_db_table_if_exists($table_name)
        {
            global $wpdb;
            $sql = "SELECT COUNT(*) FROM information_schema.TABLES WHERE TABLE_SCHEMA = '{$wpdb->dbname}' AND TABLE_NAME = '$table_name'";

            // Execute the query
            $result = $wpdb->get_var($sql); // Adjust this line if you're not using WordPress

            // Check if the result is greater than 0
            return $result > 0;
        }
    }
}
