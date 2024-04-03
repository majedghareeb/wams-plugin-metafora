<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Search_Vendor_Field')) {

	/**
	 * Class Search_Vendor_Field
	 * @package wams\admin\modules
	 */
	class Search_Vendor_Field
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
			$sites = WAMS()->admin()->get_system_sites();
			$_forms = [];
			$vendors_site_id = 0;
			$vendor_form_id = 0;
			$vendor_form_fields = [];
			$project_form_fields = [];

			$wams_seach_vendor_field_settings = get_option('wams_seach_vendor_field_settings');
			if ($wams_seach_vendor_field_settings) {

				if (isset($wams_seach_vendor_field_settings['vendor_site_id'])) {
					$vendors_site_id = $wams_seach_vendor_field_settings['vendor_site_id'];
					$_forms = WAMS()->admin()->get_forms($vendors_site_id);
				}
				if (isset($wams_seach_vendor_field_settings['vendor_form'])) {
					$vendor_form_id = $wams_seach_vendor_field_settings['vendor_form'];
					$vendor_form_fields = WAMS()->admin()->get_form_fields($vendor_form_id, $vendors_site_id);
				}
				if (isset($wams_seach_vendor_field_settings['project_form'])) {
					$project_form_id = $wams_seach_vendor_field_settings['project_form'];
					$project_form_fields = WAMS()->admin()->get_form_fields($project_form_id, $vendors_site_id);
				}
			}

			$this->page = [
				'subpage' => [
					[
						'parent_slug' => 'wams',
						'page_title' => 'Search Vendor Field',
						'menu_title' => 'Search Vendor Field',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams_search_vendor',
						'callback' => [$this, 'search_vendor_field_settings']
					]
				],
				'sections' => [
					[
						'id'    => 'wams_seach_vendor_field_settings',
						'title' => __('Search_Vendor_Field Settings', 'wams')
					]
				],
				'fields' => [
					'wams_seach_vendor_field_settings' => [
						[
							'name'    => 'enable_seach_vendor_field',
							'label' => __('Enable seach_vendor_field', 'wams'),
							'type' => 'checkbox',
							'default' => true,
						],
						[
							'name'              => 'vendor_site_id',
							'label'             => __('vendor Site ID', 'wams'),
							'desc'              => __('Please choose Which Site is for vendor', 'wams'),
							'type'              => 'select',
							'default'           => '',
							'options' => ($sites)
						],
						[
							'name'              => 'vendor_form',
							'label'             => __('Vendor Form ID', 'wams'),
							'desc'              => __('Please choose Add New vendor Form', 'wams'),
							'type'              => 'select',
							'default'           => '',
							'options' => ($_forms)
						],
						[
							'name'              => 'project_form',
							'label'             => __('Project Form ID', 'wams'),
							'desc'              => __('Please choose Add New Project Form', 'wams'),
							'type'              => 'select',
							'default'           => '',
							'options' => ($_forms)
						],
						[
							'name'  => 'project_name_field_id',
							'label' => 'Project Name Field ID',
							'desc'  => 'Please choose Porject name Field ID',
							'type'  => 'select',
							'options' => $project_form_fields
						],
						[
							'name'  => 'project_cost_center_field_id',
							'label' => 'Project Cost Center Field ID',
							'desc'  => 'Please choose Porject Cost Center Field ID',
							'type'  => 'select',
							'options' => $project_form_fields
						],
						[
							'name'  => 'vendor_name_field_id',
							'label' => 'Vendor Name Field ID',
							'desc'  => 'Please choose Vendor Name Field ID',
							'type'  => 'select',
							'options' => $vendor_form_fields

						],
						[
							'name'  => 'vendor_arabic_name_field_id',
							'label' => 'Vendor Arabic Name Field ID',
							'desc'  => 'Please choose Vendor Arabic Name Field ID',
							'type'  => 'select',
							'options' => $vendor_form_fields

						]
					],
				]


			];
		}

		function search_vendor_field_settings()
		{
			// print_r(get_option('wams_seach_vendor_field_settings'));
			$this->settings_api->show_settings_page('Search Vendors Field');
		}
	}
}
