<?php

namespace wams\admin\modules;

use GFCommon;
use wams\admin\core\Admin_Settings_API;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\admin\modules\Forms_Settings')) {

    /**
     * Class Admin_Settings
     * @package um\admin\core
     */
    class Forms_Settings
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

            $this->page = [
                'subpage' => [
                    [
                        'parent_slug' => 'wams',
                        'page_title' => 'Forms Settings',
                        'menu_title' => 'Forms Settings',
                        'capability' => 'edit_wams_settings',
                        'menu_slug' => 'wams_forms_settings',
                        'callback' => function () {
                            $this->settings_api->show_settings_page($title = 'Forms Settings');
                        }
                    ]
                ],
                'sections' => [
                    [
                        'id'    => 'wams_forms_settings',
                        'title' => __('Forms Settings', 'wams')
                    ],
                    [
                        'id'    => 'wams_archivable_forms',
                        'title' => __('Forms Archive Settings', 'wams'),
                        'desc' => '<h4>Please choose the forms to be allowed for archiving</h4><br><h6>only selected forms will bevisible in form archive menu</h6>'
                    ],
                    [
                        'id'    => 'wams_urls_form_settings',
                        'title' => __('URLs Form Settings', 'wams'),
                        'desc' => 'wams_urls_form_settings'
                    ],
                    [
                        'id'    => 'wams_vendor_form_settings',
                        'title' => __('Vendors Form Settings', 'wams'),
                        'desc' => 'wams_vendor_form_settings'
                    ],
                    [
                        'id'    => 'wams_domain_form_settings',
                        'title' => __('Domains Form Settings', 'wams'),
                        'desc' => 'wams_domain_form_settings'
                    ],
                    [
                        'id'    => 'wams_project_form_settings',
                        'title' => __('Project Form Settings', 'wams'),
                        'desc' => 'wams_prject_form_settings'
                    ],
                ],
                'fields' => [
                    'wams_forms_settings' => [
                        [
                            'name'    => 'company_form',
                            'label'   => __('Company Form', 'wams'),
                            'desc'    => __('wams_forms_settings["company_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'project_form',
                            'label'   => __('Projet Form', 'wams'),
                            'desc'    => __('wams_forms_settings["project_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'domain_form',
                            'label'   => __('Domain Form', 'wams'),
                            'desc'    => __('wams_forms_settings["domain_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'user_form',
                            'label'   => __('User Form', 'wams'),
                            'desc'    => __('wams_forms_settings["user_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'urls_form',
                            'label'   => __('URLs Form', 'wams'),
                            'desc'    => __('wams_forms_settings["urls_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'vendor_form',
                            'label'   => __('Vendor Form', 'wams'),
                            'desc'    => __('wams_forms_settings["vendor_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'vendor_projectss_form',
                            'label'   => __('Vendor Project Form', 'wams'),
                            'desc'    => __('wams_forms_settings["vendor_projects_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'    => 'vendor_companies_form',
                            'label'   => __('Vendor Companies Form', 'wams'),
                            'desc'    => __('wams_forms_settings["vendor_companies_form"]', 'wams'),
                            'type'    => 'select',
                            'default' => 'no',
                            'options' => ($forms)
                        ],
                        [
                            'name'              => 'vendor_personal_details_form',
                            'label'             => __('Vendor Personal Details Form', 'wams'),
                            'desc'              => __('Please choose vendor Personal Details Form', 'wams'),
                            'type'              => 'select',
                            'default'           => '',
                            'options' => ($forms)
                        ],
                        [
                            'name'              => 'vendor_banking_details_form',
                            'label'             => __('Vendor Banking Details Form', 'wams'),
                            'desc'              => __('Please choose vendor Banking Details Form', 'wams'),
                            'type'              => 'select',
                            'default'           => '',
                            'options' => ($forms)
                        ],
                        [
                            'name'              => 'vendor_rss_form',
                            'label'             => __('Vendor On RSS Feed Form', 'wams'),
                            'desc'              => __('vendor_rss_form', 'wams'),
                            'type'              => 'select',
                            'default'           => '',
                            'options' => ($forms)
                        ],
                    ]
                ]

            ];
            foreach ($forms as $form_id => $form_title) {
                $this->page['fields']['wams_archivable_forms'][] =
                    [
                        'name'  => $form_id,
                        'label' => $form_title,
                        'desc'  => $form_title,
                        'type'  => 'checkbox',
                    ];
            }
            $_form_fields = [];
            $wams_forms_settings = get_option('wams_forms_settings');
            if ($wams_forms_settings && isset($wams_forms_settings['urls_form'])) {
                $_form_fields = WAMS()->admin()->get_form_fields($wams_forms_settings['urls_form']);
                foreach ($_form_fields as $field) {
                    $this->page['fields']['wams_urls_form_settings'] =
                        [
                            [
                                'name'  => 'project',
                                'label' => 'Project',
                                'desc'  => 'Project',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'domain',
                                'label' => 'Domain',
                                'desc'  => 'Domain',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'link',
                                'label' => 'Link',
                                'desc'  => 'link',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'title',
                                'label' => 'Title',
                                'desc'  => 'title',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'description',
                                'label' => 'Description',
                                'desc'  => 'description',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'creator',
                                'label' => 'Creator',
                                'desc'  => 'creator',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'pub_date',
                                'label' => 'Pubished Date',
                                'desc'  => 'pub_date',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'thumbnail',
                                'label' => 'Thumbnail',
                                'desc'  => 'thumbnail',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'thumbnail_description',
                                'label' => 'Thumbnail Description',
                                'desc'  => 'thumbnail_description',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                        ];
                }
            }
            if ($wams_forms_settings && isset($wams_forms_settings['vendor_form'])) {
                $_form_fields = WAMS()->admin()->get_form_fields($wams_forms_settings['vendor_form']);
                foreach ($_form_fields as $field) {
                    $this->page['fields']['wams_vendor_form_settings'] =
                        [
                            [
                                'name'  => 'vendor_name',
                                'label' => 'Vendor Name',
                                'desc'  => 'Name',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'vendor_arabic_name',
                                'label' => 'Vendor Arabic Name',
                                'desc'  => 'Arabic Name',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'vendor_type',
                                'label' => 'Vendor Type',
                                'desc'  => 'Type',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'vendor_project',
                                'label' => 'Vendor Project',
                                'desc'  => 'Project',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'vendor_sap_id',
                                'label' => 'Vendor SAP ID',
                                'desc'  => 'SAP ID',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ]
                        ];
                }
            }
            if ($wams_forms_settings && isset($wams_forms_settings['project_form'])) {
                $_form_fields = WAMS()->admin()->get_form_fields($wams_forms_settings['project_form']);
                foreach ($_form_fields as $field) {
                    $this->page['fields']['wams_project_form_settings'] =
                        [
                            [
                                'name'  => 'project_name',
                                'label' => 'Project Name',
                                'desc'  => 'project_name',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'project_code',
                                'label' => 'Project Code',
                                'desc'  => 'project_code',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'project_manager',
                                'label' => 'Project Manager',
                                'desc'  => 'project_manager',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                        ];
                }
            }
            if ($wams_forms_settings && isset($wams_forms_settings['domain_form'])) {
                $_form_fields = WAMS()->admin()->get_form_fields($wams_forms_settings['domain_form']);
                foreach ($_form_fields as $field) {
                    $this->page['fields']['wams_domain_form_settings'] =
                        [
                            [
                                'name'  => 'domain_name',
                                'label' => 'Domain Name',
                                'desc'  => 'domain_name',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'host_name',
                                'label' => 'Host Name',
                                'desc'  => 'host_name',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'domain_url',
                                'label' => 'Domain URL',
                                'desc'  => 'domain_url',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                            [
                                'name'  => 'domain_project',
                                'label' => 'Domain Project',
                                'desc'  => 'domain_project',
                                'type'  => 'select',
                                'options' => $_form_fields
                            ],
                        ];
                }
            }
        }


        public function wams_get_domains_list($form_id)
        {
            $domain_ltd = [];
            if (class_exists('GFAPI')) {
                $entries = \GFAPI::get_entries($form_id);
                if (!is_wp_error($entries)) {
                    foreach ($entries as $entry) {
                        $domain_name = rgar($entry, '6', 'N/A');
                        $domain_ltd[] = $domain_name;
                    }
                }
            }
            return $domain_ltd;
        }
    }
}
