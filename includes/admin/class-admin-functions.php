<?php

namespace wams\admin;

use GFFormsModel;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\Admin_Functions')) {

	/**
	 * Class Admin_Functions
	 * @package um\admin\core
	 */
	class Admin_Functions
	{

		/**
		 * Check wp-admin nonce
		 *
		 * @param bool $action
		 */
		public function check_ajax_nonce($action = false)
		{
			$nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
			$action = empty($action) ? 'wams-admin-nonce' : $action;

			if (!wp_verify_nonce($nonce, $action)) {
				wp_send_json_error(esc_js(__('Wrong Nonce', 'wams')));
			}
		}

		/**
		 * Get Domains as array from Domain Form
		 * @param string	default rerturn only host names
		 * @return array	array of domains
		 */
		public function get_domains_list($type = "host_only")
		{
			$form_id = get_blog_option(1, 'wams_forms_settings')['domain_form'] ?? 0;
			// $form_id = get_option('wams_forms_settings')['domain_form'] ?? 0;
			$domains_list = [];
			$full_domains_list = [];
			if (class_exists('GFAPI') && $form_id != 0) {
				$entries = \GFAPI::get_entries($form_id);
				if (!is_wp_error($entries) && $domain_form = get_option('wams_domain_form_settings')) {
					$host_name = $domain_form['host_name'] ?? 6;
					foreach ($entries as $entry) {
						$domain_host_name = rgar($entry, $host_name);
						$domains_list[] = $domain_host_name;
						$full_domains_list[] = [
							'name' => rgar($entry, $domain_form['domain_name'] ?? 1),
							'host' => rgar($entry, $domain_form['host_name'] ?? 6),
							'url' => rgar($entry, $domain_form['domain_url'] ?? 3),
							'project' => rgar($entry, $domain_form['domain_project'] ?? 8),
						];
					}
				}
			};
			if ($type == 'host_only') {
				return $domains_list;
			} else {
				return $full_domains_list;
			}
		}
		/**
		 * Get System Active Pages
		 * @return array	Pages List
		 */
		public function wams_get_pages()
		{
			$pages_list = [];
			$pages = get_pages(array('post_type' => 'page'));
			if ($pages) {
				foreach ($pages as $page) {
					$pages_list[$page->post_name] = $page->post_title;
				}
			}
			return $pages_list;
		}
		/**
		 * Get Forms List
		 *	@return array	forms
		 */
		public function get_forms($site_id = 0)
		{
			$forms_list = [];
			if ($site_id) {
				switch_to_blog($site_id);
			}
			if (class_exists('GFAPI')) {
				$forms = \GFAPI::get_forms();
				if (!is_wp_error($forms)) {
					foreach ($forms as $form) {
						$forms_list[$form['id']] = $form['title'];
					}
				}
			}
			restore_current_blog();
			return $forms_list;
		}
		/**
		 * Get Fields of a Form
		 * @param int	$form_id
		 *	@return array	field
		 */
		public function get_form_fields($form_id, $site_id = 0)
		{
			$fields_list = [];
			if ($site_id) {
				switch_to_blog($site_id);
			}
			if (class_exists('GFAPI') && isset($form_id)) {
				if ($form = \GFAPI::get_form($form_id)) {
					foreach ($form['fields'] as $field) {
						$fields_list[$field['id']] =  $field['label'];
					}
				}
			}
			restore_current_blog();
			return $fields_list;
		}

		/**
		 * Get  Host Name from URL and remove subdomain
		 * @param   string  URL
		 * @return  string  TLD
		 */
		public function get_host_name($url)
		{
			$parseData = parse_url($url);
			$domain = isset($parse['host']) ? preg_replace('/^www\./', '', $parseData['host']) : $url;
			$array = explode(".", $domain);
			$url =  (array_key_exists(count($array) - 2, $array) ? $array[count($array) - 2] : "") . "." . $array[count($array) - 1];
			return $url;
		}













		public function get_main_site_forms()
		{
			switch_to_blog(WAMS_MAIN_BLOG_ID);


			restore_current_blog();
		}

		public function get_vendors_data()
		{
			switch_to_blog(WAMS_MAIN_BLOG_ID);
			$wams_forms_settings = get_option('wams_forms_settings');
			$vendors_form_id = $wams_forms_settings['vendor_form'] ?? false;
			if ($vendors_form_id && !class_exists('GFFormsModel')) return false;
			$vendors_entries = [];
			$vendors_entries = \GFAPI::get_entries($vendors_form_id);
			restore_current_blog();
			return $vendors_entries;
		}


		public function get_system_sites()
		{
			$sites = [];
			foreach (get_sites() as $site) {
				$sites[$site->blog_id] =  $site->path;
			}
			return $sites;
		}

		/**
		 * Get System Domains List
		 */
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
