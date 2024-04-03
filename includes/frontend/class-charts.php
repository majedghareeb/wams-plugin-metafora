<?php

namespace wams\frontend;

use GFAPI;
use GFFormsModel;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\core\Charts')) {

    /**
     * Class Shortcodes
     * @package wams\frontend
     */


    class Charts
    {
        function __construct()
        {
        }

        public function charts_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }

            switch ($_POST['param']) {
                case 'get_chart_data':
                    if (isset($_POST['form_id'])) {
                        $tasks = $this->wams_get_workflow_status($_POST['form_id']);
                        wp_send_json_success($tasks);
                    } else {
                        wp_send_json_error('Form Not Found');
                    }

                    break;

                default:
                    wp_send_json(['ok']);

                    break;
            }
            wp_die();
        }


        public function show_calendar_header_icon()
        {
            $page_link = '/';
            $wams_core_pages_settings = get_option('wams_core_pages_settings');
            if ($wams_core_pages_settings && isset($wams_core_pages_settings['tasks_calendar'])) {
                $page_link = $wams_core_pages_settings['tasks_calendar'];
            };

            echo  '<div class="nav-item d-none d-md-block" data-bs-toggle="tooltip" data-bs-title="Tasks Calendar" data-bs-placement="left">
                <a href="/' . $page_link . '" class="nav-link nav-icon" id="tasks-calendar">
                <i class="bi bi-calendar-date"></i></a>
            </div>';
        }
        public function show_chart()
        {
            $tasks = [];
            $allowed_forms = [1, 17];
            $forms = GFFormsModel::get_forms();
            if ($forms && !empty($forms)) {
                foreach ($forms as $key => $form) {
                    if (!in_array($form->id, $allowed_forms)) {
                        unset($forms[$key]);
                    }
                }
            }
            $tasks = $this->wams_get_workflow_status($allowed_forms[0]);
            // WAMS()->enqueue()->load_chart_script();
            WAMS()->enqueue()->load_apexcharts_script();
            WAMS()->get_template('apexcharts.php', '', ['forms' => $forms, 'tasks' => $tasks], true);
        }
        public function get_forms_tasks()
        {
            $forms = \GFAPI::get_forms();
            foreach ($forms as $form) {
                $labels[] = $form['title'];
                $entries_count = \GFAPI::count_entries($form['id'], []);
                $datasets['data'][] = $entries_count;
            }
        }
        public function get_tasks()
        {
            $current_user = wp_get_current_user();
            $user_id = $current_user->ID;
            $display_name = $current_user->display_name;
            // $form_id = 11;
            $tasks = get_transient('wams_forms_chart_' . $user_id);
            $tasks = false;
            if (!$tasks) :
                $tasks = [];
                $search_criteria = array(
                    'status'        => 'active',
                    'field_filters' => array(
                        'mode' => 'all',
                        array('key' => 'created_by', 'value' => $user_id),
                    ),
                );
                $search_criteria['start_date'] = date('Y-mm-dd', strtotime('today - 180 days'));
                $search_criteria['end_date'] = date('Y-mm-dd');
                // $sorting         = ['key' => 'date_created', 'direction' => 'DESC'];
                // $paging          = ['offset' => 0, 'page_size' => 100];
                $labels = [];
                $datasets = [];
                $datasets['label'] = $display_name;
                $datasets['backgroundColor'] = $this->int_to_hex('3');
                $datasets['borderColor'] = $this->int_to_hex('2');
                $datasets['borderWidth'] = 2;
                $forms = \GFAPI::get_forms();
                foreach ($forms as $form) {
                    $labels[] = $form['title'];
                    $entries_count = \GFAPI::count_entries($form['id'], $search_criteria);
                    $datasets['data'][] = $entries_count;
                }
                set_transient('wams_forms_chart_' . $user_id, $tasks, 5 * MINUTE_IN_SECONDS);

            endif;
            return $tasks = [
                'labels' => $labels,
                'datasets' => $datasets,
            ];
        }
        public function get_datasets($form_id, $search_criteria)
        {
        }
        public function int_to_hex($value)
        {
            /**
             * Generates a light hex color based on a string value.
             *
             * @param string $value The string value to use for generating the color.
             *
             * @return string A light hex color string in the format "#rrggbb".
             */

            // Hash the string using SHA-1 for a unique identifier
            $hash_digest = sha1($value);

            // Extract the first 6 characters for color components
            $color_code = substr($hash_digest, 0, 6);

            // Convert color components to integers and adjust for lightness
            $r = hexdec($color_code[0] . $color_code[1]) + 128;
            $g = hexdec($color_code[2] . $color_code[3]) + 128;
            $b = hexdec($color_code[4] . $color_code[5]) + 128;

            // Cap values at 255 for valid hex colors
            $r = min($r, 255);
            $g = min($g, 255);
            $b = min($b, 255);

            // Format as a hex color string with leading zeros
            return sprintf('#%02x%02x%02x', $r, $g, $b);
        }
        public function wams_workflow_assignees()
        {
            $output_arrays = $this->get_tasks(69);
            ob_start();
            include_once WAMS_PLUGIN_PATH . 'public/partials/tmpl-workflow-assignees.php';
            $template = ob_get_contents();
            $output = ob_get_clean();
            return $output;
        }

        public function wams_get_workflow_status($form_id)
        {
            $chart_cache = get_transient('wams_forms_chart_' . $form_id);
            $chart_cache = false;
            if (!$chart_cache) {
                global $wpdb;
                $results = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT meta_value as status, COUNT(*) AS count FROM {$wpdb->prefix}gf_entry_meta WHERE form_id= %d AND meta_key = 'workflow_final_status' GROUP BY meta_value",
                        $form_id
                    )
                );
                $labels = [];
                $data = [];

                if ($results) {
                    foreach ($results as $result) {
                        $labels[] = ucwords($result->status);
                        $data[] = intval($result->count);
                    }
                }

                $dataset = ['labels' => $labels, 'data' => $data];
                set_transient('wams_forms_chart_' . $form_id, $dataset,  MINUTE_IN_SECONDS);
                return $dataset;
            } else {
                return $chart_cache;
            }
        }
    }
}
