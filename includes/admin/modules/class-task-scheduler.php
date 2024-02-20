<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;

use wams\common\Logger;

use ActionScheduler;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\admin\modules\Task_Scheduler')) {

    /**
     * Class Task Scheduler
     * @package wams\admin\modules
     */
    class Task_Scheduler
    {
        /**
         * @var object
         */
        private $settings_api;

        /**
         * @var array
         */

        private $page;

        /**
         * Admin_Menu constructor.
         */
        function __construct()

        {
            $this->settings_api = new Admin_Settings_API();
            $this->init_variables();
            $this->settings_api->addSubpages($this->page['subpage']);
            $this->settings_api->set_sections($this->page['sections']);
            $this->settings_api->set_fields($this->page['fields']);
            $this->settings_api->register();
        }


        public function init_variables()
        {
            $avaiable_intervals = [
                'none' => 'DISABLED',
                'weekly' => 'Once Weekly',
                'daily' => 'Once Daily',
                'twicedaily' => 'Twice Daily',
                'hourly' => 'Every 1 Hour',
                'every_5_minute' => 'Every 5 Minutes',
                'every_15_minute' => 'Every 15 Minutes',
                'every_30_minute' => 'Every 30 Minutes',
            ];
            // print_r($avaiable_intervals);
            $avaiable_hooks = [
                '\wams\admin\Admin@wams_as_analytics_import' => 'Import Google Analytics Data',
                '\wams\admin\Admin@wams_as_rss_import' => 'Fetch RSS Content'
            ];
            $this->page = [
                'subpage' => [
                    [
                        'parent_slug' => 'wams',
                        'page_title' => 'Task Scheduler',
                        'menu_title' => 'Task Scheduler',
                        'capability' => 'edit_wams_settings',
                        'menu_slug' => 'wams_task_scheduler',
                        'callback' => [$this, 'show_page']
                    ]
                ],
                'sections' => [
                    [
                        'id'    => 'wams_task_scheduler_settings',
                        'title' => __('Task Scheduler Settings', 'wams')
                    ]

                ]
            ];

            $this->page['fields']['wams_task_scheduler_settings'][] =
                [
                    'name'    => 'enable_wams_task_scheduler',
                    'label' => __('Enable Task Scheduler', 'wams'),
                    'type' => 'checkbox',
                    'default' => true,
                ];

            $wams_task_scheduler = get_option('wams_task_scheduler_settings');
            if ($wams_task_scheduler && isset($wams_task_scheduler['enable_wams_task_scheduler']) && $wams_task_scheduler['enable_wams_task_scheduler'] == 'on') {
                // 
                if (!get_option('wams_task_scheduler')) {
                    WAMS()->cron()->schedule_events();
                }
                $this->page['sections'][] = [
                    'id'    => 'wams_task_scheduler',
                    'title' => __('Task Scheduler', 'wams')
                ];
                foreach ($avaiable_hooks as $hook => $hook_name) {
                    $this->page['fields']['wams_task_scheduler'][] = [
                        'name'              =>  $hook,
                        'label'             => __($hook_name, 'wams'),
                        'desc'              => __('Enable Schedule ' . $hook_name, 'wams'),
                        'type'              => 'select',
                        'default'           => '',
                        'options' => ($avaiable_intervals)
                    ];
                }
            } else {
                WAMS()->cron()->unschedule_events();
                delete_option('wams_task_scheduler');
            }
            return;
        }

        public function show_page()
        {
            echo '<h1>Dashboard</h1>';

            echo '<div class="wraper">';
            echo '<div id="tabs">';
            $this->settings_api->show_navigation();
            echo '<div class="border p-3">';
            $this->settings_api->show_forms();
            echo '</div>';
            echo '</div>';
            echo '</div>';
        }
    }
}
