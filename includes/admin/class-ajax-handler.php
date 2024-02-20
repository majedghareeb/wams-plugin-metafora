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

        public function site_setup_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-admin-nonce') || !current_user_can('manage_options')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }
            switch ($_POST['param']) {
                case 'install_pages':
                    $messages = [];
                    $default_pages = [
                        [
                            'title' => 'Home',
                            'slug' => '/',
                            'content' => '[home-page]',
                        ],
                        [
                            'title' => 'Inbox',
                            'slug' => 'inbox',
                            'content' => '[gravityflow page="inbox"]',
                        ],
                        [
                            'title' => 'Status',
                            'slug' => 'status',
                            'content' => '[gravityflow page="status"]',
                        ],
                        [
                            'title' => 'notification',
                            'slug' => 'notification',
                            'content' => '[wams_notifications]',
                        ],
                        [
                            'title' => 'Messages',
                            'slug' => 'messages',
                            'content' => '[wams_messages]',
                        ],
                        [
                            'title' => 'My Logins',
                            'slug' => 'my-logins',
                            'content' => '[wams-user-logins]',
                        ],
                        [
                            'title' => 'Tasks Calendar',
                            'slug' => 'tasks-calendar',
                            'content' => '[wams-tasks-calendar]',
                        ],
                        [
                            'title' => 'Charts',
                            'slug' => 'charts',
                            'content' => '[wams-charts]',
                        ],
                        [
                            'title' => 'Link Telegram',
                            'slug' => 'link-my-telegram',
                            'content' => '[link-my-telegram]',
                        ],
                        [
                            'title' => 'Page Parser',
                            'slug' => 'page-parser',
                            'content' => '[page-parser]',
                        ],
                        [
                            'title' => 'Upload Vendors',
                            'slug' => 'upload-vendors-list',
                            'content' => '[upload-vendors-list]',
                        ],
                        [
                            'title' => 'Fetch RSS Feed',
                            'slug' => 'fetch-rss',
                            'content' => '[fetch-rss]',
                        ],
                    ];
                    foreach ($default_pages as $page) {
                        if (get_page_by_path($page['slug'])) {
                            $messages[] = 'Page : ' . $page['title'] . ' already exists';
                            continue;
                        }
                        $core_page = array(
                            'post_title'     => $page['title'],
                            'post_content'   => $page['content'],
                            'post_name'      => sanitize_title($page['slug']),
                            'post_type'      => 'page',
                            'post_status'    => 'publish',
                            'post_author'    => get_current_user_id(),
                            'comment_status' => 'closed',
                        );
                        $post_id = wp_insert_post($core_page);
                        update_post_meta($post_id, '_wams_core', sanitize_title($page['title']));
                        $messages[] = 'new page created :' . $page['title'];
                    }
                    $forms_page = [];
                    $forms = WAMS()->admin()->get_forms();
                    foreach ($forms as $id => $title) {
                        if (get_page_by_path(sanitize_title($title))) {
                            $messages[] = 'Page : ' . $title . ' already exists';
                            continue;
                        }
                        $forms_page = array(
                            'post_title'     => $title,
                            'post_content'   => '[gravityform id="' . $id . '" title="false" description="false" ajax="true" ]',
                            'post_name'      => sanitize_title($title),
                            'post_type'      => 'page',
                            'post_status'    => 'publish',
                            'post_author'    => get_current_user_id(),
                            'comment_status' => 'closed',
                        );

                        $post_id = wp_insert_post($forms_page);
                        update_post_meta($post_id, '_wams_forms', sanitize_title($title));
                        $messages[] = 'new page created :' . $title;
                    }
                    wp_send_json_success(['messages' => $messages]);
                    wp_die();
                    break;
                case 'install_views':
                    $messages = [];
                    $view = [];
                    $forms = WAMS()->admin()->get_forms();
                    foreach ($forms as $id => $title) {
                        if (get_page_by_path(sanitize_title('view-' . $title), OBJECT, 'gravityview')) {
                            $messages[] = 'View : ' . $title . ' already exists';
                            continue;
                        }
                        $messages[] = $title;
                        $view = array(
                            'post_title'     => 'View: ' . $title,
                            'post_content'   => '',
                            'post_name'      => sanitize_title('view-' . $title),
                            'post_type'      => 'gravityview',
                            'post_status'    => 'publish',
                            'post_author'    => get_current_user_id(),
                        );

                        $view_id = wp_insert_post($view);
                        update_post_meta($view_id, '_wams_views', sanitize_title('view_' . $title));
                        update_post_meta($view_id, '_gravityview_form_id', $id);
                        update_post_meta($view_id, '_gravityview_directory_template', 'default_table');
                        update_post_meta($view_id, '_gravityview_template_settings', `'a:39:{s:8:"lightbox";s:1:"0";s:18:"show_only_approved";s:1:"0";s:23:"admin_show_all_statuses";s:1:"0";s:9:"page_size";s:2:"25";s:19:"hide_until_searched";s:1:"0";s:10:"hide_empty";s:1:"1";s:18:"no_entries_options";s:1:"0";s:15:"no_results_text";s:0:"";s:15:"no_entries_form";s:0:"";s:21:"no_entries_form_title";s:1:"1";s:27:"no_entries_form_description";s:1:"1";s:19:"no_entries_redirect";s:0:"";s:22:"no_search_results_text";s:0:"";s:12:"single_title";s:0:"";s:15:"back_link_label";s:0:"";s:17:"hide_empty_single";s:1:"1";s:12:"edit_locking";s:1:"1";s:9:"user_edit";s:1:"0";s:14:"unapprove_edit";s:1:"0";s:13:"edit_redirect";s:0:"";s:17:"edit_redirect_url";s:0:"";s:19:"action_label_update";s:6:"Update";s:19:"action_label_cancel";s:6:"Cancel";s:19:"action_label_delete";s:6:"Delete";s:11:"user_delete";s:1:"0";s:15:"delete_redirect";s:1:"1";s:19:"delete_redirect_url";s:0:"";s:12:"sort_columns";s:0:"";s:10:"sort_field";a:2:{i:0;s:0:"";i:1;s:0:"";}s:14:"sort_direction";a:2:{i:0;s:3:"ASC";i:1;s:3:"ASC";}s:10:"start_date";s:0:"";s:8:"end_date";s:0:"";s:10:"embed_only";s:1:"0";s:14:"user_duplicate";s:1:"0";s:11:"rest_enable";s:1:"0";s:10:"csv_enable";s:1:"0";s:11:"csv_nolimit";s:1:"0";s:10:"custom_css";s:0:"";s:17:"custom_javascript";s:0:"";}'`);
                        $messages[] = 'new View created :' . $title;
                        $view_page = array(
                            'post_title'     => 'View: ' . $title,
                            'post_content'   => '[gravityview id="' . $view_id . ']',
                            'post_name'      => sanitize_title('view_' . $title),
                            'post_type'      => 'page',
                            'post_status'    => 'publish',
                            'post_author'    => get_current_user_id(),
                            'comment_status' => 'closed',
                        );

                        $post_id = wp_insert_post($view_page);
                        update_post_meta($post_id, '_wams_views', sanitize_title($title));
                        $messages[] = 'new page created :' . $title . ' for view id:  ' . $view_id;
                    }
                    wp_send_json_success(['messages' => $messages]);
                    wp_die();
                    break;
                case 'install_user_menu':
                    //Create User Menu

                    wp_send_json_success(['messages' => ['User Menu Setup']]);
                    break;
            }
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
                    WAMS()->web_notifications()->store_notification($user_id, "TEST", $arg);
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
