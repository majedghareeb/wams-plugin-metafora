<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\admin\modules\Telegram_Settings')) {

    /**
     * Class Telegram
     * @package wams\admin\core
     */
    class Telegram_Settings
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
                        'page_title' => 'Telegram Settings',
                        'menu_title' => 'Telegram Settings',
                        'capability' => 'edit_wams_settings',
                        'menu_slug' => 'wams_telegram_settings',
                        'callback' => [$this, 'show_page']
                    ]
                ],
                'sections' => [
                    [
                        'id'    => 'wams_telegram_settings',
                        'title' => __('Notifications Settings', 'wams')
                    ],
                    [
                        'id'    => 'wams_telegram_api',
                        'title' => __('Telegram Settings', 'wams')
                    ],
                    [
                        'id'    => 'wams_telegram_channel',
                        'title' => __('Channels Settings', 'wams'),
                        'desc'  => __('Public Channel on Telegram could be used to publish articles')
                    ]
                ],
                'fields' => [
                    'wams_telegram_api' => [
                        [
                            'name'              => 'api_key',
                            'label'             => __('API Key', 'wams'),
                            'desc'              => __('You can get API key from telegram app', 'wams'),
                            'placeholder'       => __('key', 'wams'),
                            'type'              => 'text',
                            'default'           => '',
                            'sanitize_callback' => 'sanitize_text_field'
                        ],
                        [
                            'name'              => 'bot_username',
                            'label'             => __('BOT Username', 'wams'),
                            'desc'              => __('BOT Username you can get from telegram @botfather', 'wams'),
                            'placeholder'       => __('bot_username', 'wams'),
                            'type'              => 'text',
                            'default'           => '',
                            'sanitize_callback' => 'sanitize_text_field'
                        ],
                        [
                            'name'              => 'channel_id',
                            'label'             => __('Telegram Admin Channel Chat ID', 'wams'),
                            'desc'              => __('Send /activate command from your channel first you will get the Channel ID', 'wams'),
                            'placeholder'       => __(''),
                            'min'               => 0,
                            'max'               => 9999999999999,
                            'step'              => '',
                            'type'              => 'number',
                            'default'           => '',
                        ],
                    ],
                    'wams_telegram_channel' => [
                        [
                            'name'              => 'News',
                            'label'             => __('Telegram News Channel Chat ID', 'wams'),
                            'desc'              => __('Send /activate command from your channel first you will get the Channel ID Go to https://web.telegram.org
                            Click on your channel
                            Look at the URL and find the part that looks like c12112121212_17878787878787878
                            Remove the underscore and after c12112121212
                            Remove the prefixed letter 12112121212
                            Prefix with a -100 so -10012112121212', 'wams'),
                            'placeholder'       => __('-100xxxxxxxxxxx'),
                            'min'               => 0,
                            'max'               => 9999999999999,
                            'step'              => '',
                            'type'              => 'number',
                            'default'           => '',
                        ],
                        [
                            'name'              => 'Programs',
                            'label'             => __('Telegram Programs Channel Chat ID', 'wams'),
                            'desc'              => __('Send /activate command from your channel first you will get the Channel ID', 'wams'),
                            'placeholder'       => __('-100xxxxxxxxxxx'),
                            'min'               => 0,
                            'max'               => 9999999999999,
                            'step'              => '',
                            'type'              => 'number',
                            'default'           => '',
                            'sanitize_callback' => 'number'
                        ],
                    ],
                    'wams_telegram_settings' => [
                        [
                            'name'  => 'wams_telegram_enabled',
                            'label' => __('Enable', 'wams'),
                            'desc'  => __('Enable telegram notifications', 'wams'),
                            'default' => 'on',
                            'type'  => 'checkbox'
                        ]
                    ]
                ]

            ];

            if (get_option('wams_forms_settings') && !empty(get_option('wams_forms_settings')['domain_form'])) {

                $domains = WAMS()->Admin()->get_domains_list();

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
                    $this->page['fields']['wams_telegram_channel'][] =
                        [
                            'name'              => $domain . '_channel_ID',
                            'label'             => __($domain, 'wams'),
                            'desc'              => __(strtoupper($domain) . ' Telegram Channel ID', 'wams'),
                            'placeholder'       => __('Channel ID', 'wams'),
                            'type'              => 'text',
                            'default'           => '',
                            'sanitize_callback' => 'sanitize_text_field'

                        ];
                    $this->page['fields']['wams_telegram_channel'][] =
                        [
                            'name'  => $domain . '_tg_notification',
                            'label' => __('Enable', 'wams'),
                            'desc'  => __('Enable on ' . $domain . ' public channel', 'wams'),
                            'default' => 'off',
                            'type'  => 'checkbox'
                        ];
                }
            }
        }

        public function show_page()
        {
            echo '<h1>Telegram Settings</h1>';

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
