<?php

namespace wams\frontend;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\core\Tasks_Calendar')) {

    /**
     * Class Shortcodes
     * @package wams\frontend
     */


    class Tasks_Calendar
    {
        function __construct()
        {
            add_action('wams_theme_after_header_icons', [$this, 'show_calendar_header_icon']);
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
        public function show_calendar()
        {
            $tasks = [];
            $tasks = $this->get_tasks();
            $home = WAMS()->get_template('tasks-calendar.php', '', ['tasks' => $tasks], true);
        }
        public function get_tasks($form_id = 0)
        {
            $current_user_id = get_current_user_id();
            // $form_id = 11;
            $tasks = get_transient('wams_forms_tasks_' . $form_id);
            $tasks = false;
            if (!$tasks) :
                $tasks = [];
                $tasks = [];
                $year = date('Y');
                $search_criteria = array(
                    'status'        => 'active',
                    'field_filters' => array(
                        'mode' => 'all',
                        array('key' => 'created_by', 'value' => $current_user_id),
                    ),
                );
                $search_criteria['start_date'] = date('Y-mm-dd', strtotime('today - 180 days'));
                $search_criteria['end_date'] = date('Y-mm-dd');
                $total = 0;

                $paging = array('offset' => 0, 'page_size' => 100);
                $entries = \GFAPI::get_entries($form_id, $search_criteria, null, $paging);
                foreach ($entries as $entry) {
                    $user_id = rgar($entry, 'created_by', '0');
                    $form_id = rgar($entry, 'form_id');
                    $form = \GFAPI::get_form($form_id);
                    // Get the form title
                    $form_title = rgar($form, 'title');
                    $user_name = get_userdata($user_id)->display_name;
                    $task_color = $this->int_to_hex($entry['form_id']);
                    $date_time = rgar($entry, 'date_created');
                    $startDateTime = new \DateTime($date_time);
                    $interval = new \DateInterval('PT1H');
                    // $date = \DateTime::createFromFormat('Y-m-d H:i:s', $date_time);
                    // $date->add(new \DateInterval('PT30M'));
                    $workflow_final_status = $entry['workflow_final_status'] ?? '';
                    $tasks[] = [
                        'Entry ID' => $entry['id'],
                        'allDay' => false,
                        'display' => 'block',
                        'title' => rgar($entry, 'id') . '-' . $form_title . '-' . $workflow_final_status,
                        'start' => $startDateTime->format('Y-m-d H:i:s'),
                        'end' => $startDateTime->add($interval)->format('Y-m-d H:i:s'),
                        'textColor' => '#000',
                        'color' => $task_color,
                        'Status' => $workflow_final_status,
                        'description' => $workflow_final_status,
                        'url' => '/status/?page=gravityflow-inbox&view=entry&id=' . $form_id . '&lid=' . $entry['id'],
                        'mouseEnterInfo' => 'hhhhh'
                    ];
                }
                set_transient('wams_forms_tasks_' . $form_id, $tasks, 5 * MINUTE_IN_SECONDS);
            endif;
            return $tasks;
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
    }
}
