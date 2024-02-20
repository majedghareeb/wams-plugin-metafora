<?php

namespace wams\admin\modules;

use wams\admin\core\Admin_Settings_API;


if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Search_Client_Field')) {

	/**
	 * Class Search_Client_Field
	 * @package wams\admin\modules
	 */
	class Search_Client_Field
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
			$_input_forms = [];
			$client_site_id = 0;
			$client_add_new_form_id = 0;
			$_form_fields = [];

			$wams_seach_client_field_settings = get_option('wams_seach_client_field_settings');
			if ($wams_seach_client_field_settings) {

				if (isset($wams_seach_client_field_settings['client_site_id'])) {
					$client_site_id = $wams_seach_client_field_settings['client_site_id'];
					$_input_forms = WAMS()->admin()->get_forms($client_site_id);
				}
				if (isset($wams_seach_client_field_settings['client_add_new_form'])) {
					$client_add_new_form_id = $wams_seach_client_field_settings['client_add_new_form'];
					$_form_fields = WAMS()->admin()->get_form_fields($client_add_new_form_id, $client_site_id);
				}
			}

			$this->page = [
				'subpage' => [
					[
						'parent_slug' => 'wams',
						'page_title' => 'Search_Client_Field',
						'menu_title' => 'Search_Client_Field',
						'capability' => 'edit_wams_settings',
						'menu_slug' => 'wams_search_client_field',
						'callback' => [$this, 'search_client_field_settings']
					]
				],
				'sections' => [
					[
						'id'    => 'wams_seach_client_field_settings',
						'title' => __('Search_Client_Field Settings', 'wams')
					]
				],
				'fields' => [
					'wams_seach_client_field_settings' => [
						[
							'name'    => 'enable_seach_client_field',
							'label' => __('Enable seach_client_field', 'wams'),
							'type' => 'checkbox',
							'default' => true,
						],
						[
							'name'              => 'client_site_id',
							'label'             => __('Client Site ID', 'wams'),
							'desc'              => __('Please choose Which Site is for Client', 'wams'),
							'type'              => 'select',
							'default'           => '',
							'options' => ($sites)
						],
						[
							'name'              => 'client_add_new_form',
							'label'             => __('New Client Form', 'wams'),
							'desc'              => __('Please choose Add New Client Form', 'wams'),
							'type'              => 'select',
							'default'           => '',
							'options' => ($_input_forms)
						],
						[
							'name'  => 'client_type',
							'label' => 'Client Type Field ID',
							'desc'  => 'Please choose Client Type Field ID',
							'type'  => 'select',
							'options' => $_form_fields
						],
					],
				]


			];
		}

		function search_client_field_settings()
		{
			// print_r(get_option('wams_seach_client_field_settings'));
			$this->settings_api->show_navigation();
			$this->settings_api->show_forms();
		}
	}
}
