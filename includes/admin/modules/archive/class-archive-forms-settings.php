<?php

namespace wams\admin\modules\archive;

use GFCommon;
use wams\admin\core\Admin_Settings_API;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\admin\modules\Archive_Forms_Settings')) {

    /**
     * Class Archive_Forms_Settings
     * @package um\admin\core
     */
    class Archive_Forms_Settings
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
         * Admin_Settings constructor.
         */
        /**
         * Admin_Menu constructor.
         */
        function __construct()

        {
            $this->settings_api = new Admin_Settings_API();
            $this->init_variables();
            $this->settings_api->addSubpages($this->page['subpage']);
            // $this->settings_api->set_sections($this->page['sections']);
            // $this->settings_api->set_fields($this->page['fields']);
            $this->settings_api->register();
        }
        /**
         * 
         */
        public function init_variables()
        {


            $this->page = [
                'subpage' => [
                    [
                        'parent_slug' => 'wams',
                        'page_title' => 'Forms Settings',
                        'menu_title' => 'Forms Settings',
                        'capability' => 'edit_wams_settings',
                        'menu_slug' => 'wams_forms_settings',
                        'callback' => function () {
                            $this->settings_api->show_settings_page($title = 'Archive Settings');
                        }
                    ]
                ],
                'sections' => [
                    [
                        'id'    => 'wams_archivable_forms',
                        'title' => __('Forms Archive Settings', 'wams'),
                        'desc' => '<h4>Please choose the forms to be allowed for archiving</h4><br><h6>only selected forms will bevisible in form archive menu</h6>'
                    ],

                ],
                'fields' => [
                    'wams_forms_settings' => []
                ]

            ];
        }
    }
}
