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
            $forms = WAMS()->admin()->get_forms();
            $urls_form_fields = [];
            $wams_rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
            if ($wams_rss_fetcher_settings && isset($wams_rss_fetcher_settings['urls_form'])) {
                $urls_form_fields = WAMS()->admin()->get_form_fields($wams_rss_fetcher_settings['urls_form']);
            }
            $vendor_rss_form_fields = [];
            $wams_rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
            if ($wams_rss_fetcher_settings && isset($wams_rss_fetcher_settings['vendor_rss_form'])) {
                $vendor_rss_form_fields = WAMS()->admin()->get_form_fields($wams_rss_fetcher_settings['vendor_rss_form']);
            }
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
                    ],
                    [
                        'id'    => 'wams_urls_form_settings',
                        'title' => __('URLs Form Settings', 'wams'),
                    ],
                    [
                        'id'    => 'wams_vendor_on_rss_form_settings',
                        'title' => __('Vendor on RSS Form Settings', 'wams'),
                        'desc'  => 'wams_vendor_on_rss_form_settings[]'
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
                        [
                            'name'    => 'urls_form',
                            'label'   => __('URLs Form', 'wams'),
                            'desc'    => __('wams_rss_fetcher_settings["urls_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'vendor_rss_form',
                            'label'   => __('Vendor RSS Form', 'wams'),
                            'desc'    => __('wams_rss_fetcher_settings["vendor_rss_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'fetch_scheduled',
                            'label'   => __('Enable Scheduler', 'wams'),
                            'desc'    => __('Enable Scheduler fetch for domain : ' . get_bloginfo('name'), 'wams'),
                            'type'    => 'checkbox',
                        ]
                    ],
                    'wams_url_ignored_authors' => [
                        [
                            'name'    => 'ignored_authors',
                            'label'   => __('Ignored Authors', 'wams'),
                            'desc'    => __('wams_url_ignored_authors["ignored_authors"]', 'wams'),
                            'type'    => 'list',
                            'items'   => []
                        ],
                    ],
                    'wams_urls_form_settings' => [
                        [
                            'name'  => 'project',
                            'label' => 'Project',
                            'desc'  => 'Project',
                            'type'  => 'select',
                            'options' => ($urls_form_fields)
                        ],
                        [
                            'name'  => 'pageviews',
                            'label' => 'pageviews',
                            'desc'  => 'pageviews',
                            'type'  => 'select',
                            'options' => ($urls_form_fields)
                        ],
                        [
                            'name'  => 'sessions',
                            'label' => 'Sessions',
                            'desc'  => 'Sessions',
                            'type'  => 'select',
                            'options' => ($urls_form_fields)
                        ],
                        [
                            'name'  => 'domain',
                            'label' => 'Domain',
                            'desc'  => 'Domain',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'link',
                            'label' => 'Link',
                            'desc'  => 'link',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'title',
                            'label' => 'Title',
                            'desc'  => 'title',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'description',
                            'label' => 'Description',
                            'desc'  => 'description',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'creator',
                            'label' => 'Creator',
                            'desc'  => 'creator',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'pub_date',
                            'label' => 'Pubished Date',
                            'desc'  => 'pub_date',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'thumbnail',
                            'label' => 'Thumbnail',
                            'desc'  => 'thumbnail',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'thumbnail_description',
                            'label' => 'Thumbnail Description',
                            'desc'  => 'thumbnail_description',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'pageviews',
                            'label' => 'GA4 pageviews',
                            'desc'  => 'pageviews',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'sessions',
                            'label' => 'GA4 Sessions',
                            'desc'  => 'sessions',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'avg_engament_time',
                            'label' => 'Average Engagement Time',
                            'desc'  => 'avg_engament_time',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                        [
                            'name'  => 'users',
                            'label' => 'GA4 Users',
                            'desc'  => 'users',
                            'type'  => 'select',
                            'options' => $urls_form_fields
                        ],
                    ],
                    'wams_vendor_on_rss_form_settings' => [
                        [
                            'name'  => 'author_name',
                            'label' => 'Athor Name on RSS Feed',
                            'desc'  => 'author_name',
                            'type'  => 'select',
                            'options' => ($vendor_rss_form_fields)
                        ],
                        [
                            'name'  => 'vendor_id',
                            'label' => 'Vendor ID',
                            'desc'  => 'vendor_id',
                            'type'  => 'select',
                            'options' => ($vendor_rss_form_fields)
                        ],
                    ]
                ]

            ];
            // if (get_option('wams_forms_settings') && !empty(get_option('wams_forms_settings')['domain_form'])) {

            //     $domains = WAMS()->Admin()->get_domains_list('host_only');

            //     foreach ($domains as $domain) {
            //         if ($domain == '') {
            //             $message =  __('The selected Form ID :  ' . $domain . ' is not valid domains form <br/>');
            //             $message .=  __('Please visit forms setting page to correct that!');
            //             WAMS()->admin()->notices()->add_notice(
            //                 'wams_settings',
            //                 array(
            //                     'class'       => 'error',
            //                     'message'     => $message,
            //                     'dismissible' => true,
            //                 ),
            //                 10
            //             );
            //             continue;
            //         }

            //         $this->page['fields']['wams_rss_fetcher_settings'][] =
            //             [
            //                 'name'    => $domain,
            //                 'label'   => __(strtoupper($domain), 'wams'),
            //                 'desc'    => __('Set RSS URL for domain : ' . $domain, 'wams'),
            //                 'type'    => 'text',
            //                 'default' => 'https://www.' . $domain . '/rss',
            //                 'placeholder' => $domain . ' RSS URL',
            //             ];
            //         $this->page['fields']['wams_rss_fetcher_scheduler'][] =
            //             [
            //                 'name'    => $domain,
            //                 'label'   => __(strtoupper($domain), 'wams'),
            //                 'desc'    => __('Enable Scheduler fetch for domain : ' . $domain, 'wams'),
            //                 'type'    => 'checkbox',
            //             ];
            //     }
            // }
        }

        public function rss_fetcher()
        {
            // echo 'RSS Featcher';
            $this->settings_api->show_settings_page("RSS Fetcher");
        }
    }
}
