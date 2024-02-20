<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\admin\modules\RSS_Fetcher')) {

    /**
     * Class Telegram
     * @package wams\admin\core
     */
    class RSS_Fetcher
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
            $this->settings_api->set_sections($this->page['sections']);
            $this->settings_api->set_fields($this->page['fields']);
            $this->settings_api->register();
        }
        /**
         * 
         */
        public function init_variables()
        {
            $forms = [];

            $this->page = [
                'subpage' => [
                    [
                        'parent_slug' => 'wams',
                        'page_title' => 'RSS Fetcher',
                        'menu_title' => 'RSS Fetcher',
                        'capability' => 'edit_wams_settings',
                        'menu_slug' => 'wams_rss_fetcher',
                        'callback' => [$this, 'rss_fetcher']
                    ]
                ],
                'sections' => [
                    [
                        'id'    => 'wams_rss_fetcher_settings',
                        'title' => __('RSS URL', 'wams')
                    ],
                    [
                        'id'    => 'wams_url_ignored_authors',
                        'title' => __('Ignored Authors', 'wams')
                    ]
                ],
                'fields' => [

                    'wams_rss_fetcher_settings' => [
                        [
                            'name'    => 'rss_url',
                            'label'   => __('RSS URL', 'wams'),
                            'desc'    => __('wams_rss_fetcher_settings["rss_url"]', 'wams'),
                            'type'    => 'url',
                            'placeholder'    => 'http://www.example.com/rss',

                        ],
                        [
                            'name'    => 'enabled',
                            'label'   => __('Enable RSS Featcher', 'wams'),
                            'desc'    => __('wams_rss_fetcher_settings["enabled"]', 'wams'),
                            'type'    => 'checkbox',

                        ],
                    ],
                    'wams_url_ignored_authors' => [
                        [
                            'name'    => 'ignored_authors',
                            'label'   => __('Ignored Authors', 'wams'),
                            'desc'    => __('wams_url_ignored_authors["ignored_authors"]', 'wams'),
                            'type'    => 'list',
                            'items'   => []
                        ],
                    ]

                ]

            ];

            if (get_option('wams_forms_settings') && !empty(get_option('wams_forms_settings')['domain_form'])) {

                $domains = WAMS()->Admin()->get_domains_list('host_only');

                foreach ($domains as $domain) {
                    if ($domain == '') {
                        $message =  __('The selected Form ID :  ' . $domain . ' is not valid domains form <br/>');
                        $message .=  __('Please visit forms setting page to correct that!');
                        WAMS()->admin()->notices()->add_notice(
                            'wams_settings',
                            array(
                                'class'       => 'error',
                                'message'     => $message,
                                'dismissible' => true,
                            ),
                            10
                        );
                        continue;
                    }

                    $this->page['fields']['wams_rss_fetcher_settings'][] =
                        [
                            'name'    => $domain,
                            'label'   => __(strtoupper($domain), 'wams'),
                            'desc'    => __('Set RSS URL for domain : ' . $domain, 'wams'),
                            'type'    => 'text',
                            'default' => 'https://www.' . $domain . '/rss',
                            'placeholder' => $domain . ' RSS URL',
                        ];
                    $this->page['fields']['wams_rss_fetcher_scheduler'][] =
                        [
                            'name'    => $domain,
                            'label'   => __(strtoupper($domain), 'wams'),
                            'desc'    => __('Enable Scheduler fetch for domain : ' . $domain, 'wams'),
                            'type'    => 'checkbox',
                        ];
                }
            }
        }

        public function rss_fetcher()
        {
            // echo 'RSS Featcher';
            $this->settings_api->show_settings_page("RSS Fetcher");
        }
    }
}
