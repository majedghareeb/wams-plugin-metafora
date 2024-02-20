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
		 * Check if current page load WAMS post type
		 *
		 * @deprecated 2.8.0
		 *
		 * @return bool
		 */
		public function is_plugin_post_type()
		{
			_deprecated_function(__METHOD__, '2.8.0', 'WAMS()->admin()->screen()->is_own_post_type()');
			return WAMS()->admin()->screen()->is_own_post_type();
		}

		/**
		 * Get Domains as array from Domain Form
		 * @param string	default rerturn only host names
		 * @return array	array of domains
		 */
		public function get_domains_list($type = "host_only")
		{
			$form_id = get_option('wams_forms_settings')['domain_form'] ?? 0;
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

		public function wams_as_rss_import()
		{
			$domains = WAMS()->Admin()->get_domains_list('full');
			\wams\common\Logger::info('wams_as_rss_import is called from ' . __CLASS__ . ' class using cron');

			foreach ($domains as $domain) {
				$wams_rss_fetcher_scheduler = get_option('wams_rss_fetcher_scheduler');
				if ($wams_rss_fetcher_scheduler && $wams_rss_fetcher_scheduler[$domain['host']] == 'on') {
					\wams\common\Logger::info('Scheduler is en for domain ' . $domain['host'] . ' as cron');
					$wams_rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
					if ($wams_rss_fetcher_settings && $wams_rss_fetcher_settings[$domain['host']] != '') {
						$url = $wams_rss_fetcher_settings[$domain['host']];
						$posts = [];
						$rssExtractor = new \wams\core\RSS_Feed_Extractor($url);
						try {
							$items = $rssExtractor->extractItems();

							foreach ($items as $item) {
								$dateTime = new \DateTime($item['pubDate']);
								$posts[] = [
									'project' =>  $domain['project'],
									'domain' =>  $domain['host'],
									'link' =>    htmlspecialchars($item['link']),
									'title' =>   str_replace('<br />', '', strip_tags($item['title'])) ?? '',
									'description' =>    strip_tags($item['description']) ?? '',
									'creator' =>    strip_tags($item['creator']) ?? '',
									'pub_date' =>    strip_tags($dateTime->format('Y-m-d')),
									'thumbnail' => isset($item['thumbnail']) ? strip_tags($item['thumbnail']) : '',
									'thumbnail_description' =>  isset($item['thumbnail_description']) ?  strip_tags($item['thumbnail_description']) : '',
								];
								// echo '<div class="card mb-3"">';
								// echo '<div class="row g-0">';
								// echo '<div class="col-md-4">';
								// echo '<img src="' . htmlspecialchars($item['thumbnail']) . '" alt="' . htmlspecialchars($item['thumbnail_description']) . '" class="img-fluid rounded-start">';
								// echo '<p>Description: ' . htmlspecialchars($item['thumbnail_description']) . '</p>';
								// echo '</div>';
								// echo '<div class="col-md-8">';
								// echo '<div class="card-body">';
								// echo '<a href="' . htmlspecialchars($item['link']) . '" target="_blank"><h5 class="card-title">' .  htmlspecialchars($item['title']) . '</h5></a>';
								// echo '<p class="card-text">' . htmlspecialchars($item['description']) . '</p>';
								// echo '<p class="card-text">' . htmlspecialchars($item['creator']) . '</p>';
								// echo '<p class="card-text"><small class="text-body-secondary">' . htmlspecialchars($dateTime->format('Y-m-d')) . '</small></p>';
								// echo '</div>';
								// echo '</div>';
								// echo '</div>';
								// echo '</div>';
							}
						} catch (\Exception $e) {
							\wams\common\Logger::error('Error in RSS Fetch: ' . $e->getMessage());
						}

						$this->save_new_fetched_posts($posts);
					}
				}
			}
			return;
		}


		/**
		 * Save New Post on URL List Form
		 */
		function save_new_fetched_posts(array $posts = [])
		{
			if (!is_array($posts) || empty($posts)) return;
			\wams\common\Logger::info('importing started');

			$wams_forms_settings = get_option('wams_forms_settings');
			$wams_urls_form_settings = get_option('wams_urls_form_settings');
			if ($wams_forms_settings && isset($wams_forms_settings['urls_form']) && isset($wams_urls_form_settings)) {
				$entry = [];
				foreach ($posts as $post) {
					foreach ($post as $key => $value) {
						//TODO create setting to choose user id for rss agent
						$entry['created_by'] = 24;
						$entry['form_id'] = $wams_forms_settings['urls_form'];
						$entry[$wams_urls_form_settings[$key]] = $post[$key];
						//TODO create setting to choose "Published" field ID
						$entry['16'] = 'Yes';
						//TODO create setting to choose "service type" field ID
						$entry['17'] = 'Article';
					}
					if ($this->check_if_entry_exists($wams_forms_settings['urls_form'], $post['link'], $wams_urls_form_settings['link'])) {
						// \wams\common\Logger::info('entry already exists');
						continue;
					} else {
						if ($new_entry = \GFAPI::add_entry($entry)) {
							$this->save_auhor_to_vendor_rss_form($post['creator']);
							\wams\common\Logger::info('new entry created ' . $new_entry);
						}
					}
				}
			}
		}

		public function save_auhor_to_vendor_rss_form($author)
		{
			$wams_forms_settings = get_option('wams_forms_settings');
			$wams_url_ignored_authors = get_option('wams_url_ignored_authors');
			$wams_vendor_form_settings = get_option('wams_vendor_form_settings');
			if ($wams_forms_settings && isset($wams_forms_settings['vendor_rss_form'])) {
				$author_on_rss = $author ?? '';
				$vendor_name = $wams_vendor_form_settings['vendor_name'] ?? '';
				$vendor_rss_form_id = $wams_forms_settings['vendor_rss_form'] ?? 23;

				$ignored_authors = $wams_url_ignored_authors['ignored_authors'];
				if (!strpos($ignored_authors, $author)) {
					$check_if_author_is_exists = $this->check_if_entry_exists($vendor_rss_form_id, $author, 1);
					if ($check_if_author_is_exists === false) {
						$entry = [];
						$entry['form_id'] = $vendor_rss_form_id;
						//TODO create setting to choose "author" field ID
						$entry['1'] = $author;
						if (!$new_entry = \GFAPI::add_entry($entry)) {
							\wams\common\Logger::error('Error in Creating new Author ' . $new_entry);
						}
					}
				}
			}
		}

		/**
		 * Check if enry exists
		 */
		public function check_if_entry_exists($form_id, $value_to_check, $match_field)
		{
			$search_criteria  = array(
				'field_filters' => array(
					array(
						'key'   => $match_field,  // Original Client ID Field ID in Add New Clients Form
						'value' => $value_to_check,
					)
				)
			);
			$t = 0;
			$existing_entries = \GFAPI::get_entries($form_id, $search_criteria, null, null, $t);
			if ($t > 0) return true;
			return false;
		}
		/**
		 * Fethc RSS feed items
		 */

		public function fetch_rss($url)
		{
			if (!$url) return;
			$posts = [];
			$rssExtractor = new \wams\core\RSS_Feed_Extractor($url);
			try {
				$items = $rssExtractor->extractItems();

				foreach ($items as $item) {
					$dateTime = new \DateTime($item['pubDate']);
					echo '<div class="card mb-3"">';
					echo '<div class="row g-0">';
					echo '<div class="col-md-4">';
					echo '<img src="' . htmlspecialchars($item['thumbnail']) . '" alt="' . htmlspecialchars($item['thumbnail_description']) . '" class="img-fluid rounded-start">';
					echo '<p>Description: ' . htmlspecialchars($item['thumbnail_description']) . '</p>';
					echo '</div>';
					echo '<div class="col-md-8">';
					echo '<div class="card-body">';
					echo '<a href="' . htmlspecialchars($item['link']) . '" target="_blank"><h5 class="card-title">' .  htmlspecialchars($item['title']) . '</h5></a>';
					echo '<p class="card-text">' . htmlspecialchars($item['description']) . '</p>';
					echo '<p class="card-text">' . htmlspecialchars($item['creator']) . '</p>';
					echo '<p class="card-text"><small class="text-body-secondary">' . htmlspecialchars($dateTime->format('Y-m-d')) . '</small></p>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
					echo '</div>';
				}
			} catch (\Exception $e) {
				echo 'Error: ' . $e->getMessage();
			}
		}



		public function get_input_site_forms($input_site_id)
		{
			if (!class_exists('GFFormsModel')) return [];
			$input_site_id = isset($domain_settings['input_site']) ? $domain_settings['input_site'] : 3;

			switch_to_blog($input_site_id);
			$input_forms = [];
			$gf_forms = GFFormsModel::get_forms(true);
			foreach ($gf_forms as $form) {
				$form_id = absint($form->id);
				$input_forms[$form_id] =  $form->title;
			}
			restore_current_blog();
			return $input_forms;
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
