<?php

namespace wams\frontend;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\Shortcodes')) {

	/**
	 * Class Shortcodes
	 * @package wams\frontend
	 */
	class Shortcodes
	{

		/**
		 * @var array
		 */
		public $forms_exist = array();

		/**
		 * @var string
		 */
		public $profile_role = '';

		/**
		 * @var bool
		 */
		public $message_mode = false;

		/**
		 * @var string
		 */
		public $custom_message = '';

		/**
		 * @var array
		 */
		public $loop = array();

		/**
		 * @var array
		 */
		public $emoji = array();

		/**
		 * @var null|int
		 */
		public $form_id = null;

		/**
		 * @var null|string
		 */
		public $form_status = null;

		/**
		 * @var array
		 */
		public $set_args = array();

		/**
		 * Shortcodes constructor.
		 */
		public function __construct()
		{
			add_shortcode('home-page', array(&$this, 'home_page'));
			add_shortcode('fetch-rss', array(&$this, 'fetch_rss'));
			add_shortcode('upload-vendors-list', array(WAMS()->vendors_importer(), 'upload_vendors_list'));
			add_shortcode('google-analytics-updater', array(&$this, 'google_analytics_updater'));
			add_shortcode('page-parser', array(&$this, 'page_parser'));
			add_shortcode('link-my-telegram', array(WAMS()->telegram_notifications(), 'link_my_telegram_page'));
			add_shortcode('wams-charts', array(WAMS()->frontend()->charts(), 'show_chart'));
			add_shortcode('wams-tasks-calendar', array(WAMS()->frontend()->tasks_calendar(), 'show_calendar'));
			add_shortcode('wams-user-logins', array($this, 'user_login_table'));
			// add_shortcode('wams', array(&$this, 'wams'));
			add_shortcode('wams_searchform', array(&$this, 'wams_searchform'));
			add_shortcode('wams_notifications', array(&$this, 'notifications_page'));
			add_shortcode('wams_messages', array(WAMS()->messages(), 'messages_page'));
			add_shortcode('notification-settings', array(&$this, 'notification_settings'));
			add_shortcode('reporters-list', array(&$this, 'reporters_list'));
			add_shortcode('urls-list', array(&$this, 'urls_list'));
			add_shortcode('gravityflow-timeline', array(&$this, 'gravityflow_timeline'));
			add_shortcode('gravityflow-wizard', array(&$this, 'gravityflow_wizard'));

			add_shortcode('sample-code', array(&$this, 'sample_code'));
			// Misbar Site Shortcodes
			add_shortcode('claim-links', array(&$this, 'claim_links'));
			add_shortcode('payment-order', array(&$this, 'fill_payment_order'));
		}
		public function sample_code()
		{
			WAMS()->get_template('sample-code.php', '', [], true);
		}
		public function home_page()
		{
			if (!is_user_logged_in()) return '';
			WAMS()->enqueue()->load_home_script();
			WAMS()->enqueue()->load_bootstrap_table_script();
			$query = 'get';
			$search_value = '1';
			$dashboard = WAMS()->frontend()->user_dashboard();
			$dashboard_cards = [
				'my_tasks' => $dashboard->wams_user_tasks(),
				'my_requests' => $dashboard->wams_user_requests(),
				'my_team_tasks' => $dashboard->wams_user_team_tasks(),
				'user_profile_details' => $dashboard->show_user_profile_details(),
			];
			echo '<pre>';
			// print_r($dashboard->wams_user_team_tasks());
			// print_r($dashboard->wams_user_team_tasks());
			echo '</pre>';
			$current_blog = get_current_blog_id();
			switch ($current_blog) {
				case '2':
					$home = WAMS()->get_template('misbar-home-page.php', '', $dashboard_cards, true);
					break;
				default:
					$home = WAMS()->get_template('home-page.php', '', $dashboard_cards, true);

					break;
			}
		}
		function fill_payment_order($atts)
		{
			if (!isset($_GET['payment-order'])) {
				echo '<div class="alert alert-danger">Please select Payment Order First</div>';
				return;
			} else {
				$po_entry = \GFAPI::get_entry($_GET['payment-order']);
				if ($po_entry) {
					$po_id = rgar($po_entry, 'id', $_GET['payment-order']);
					$po_name = rgar($po_entry, 1, 'No Name');
					$po_assignee = rgar($po_entry, 2);
					$po_assignee_name = get_user_meta($po_assignee, 'first_name', true);
					$vars = [
						'po_id' => $po_id,
						'po_name' => $po_name,
						'po_assignee' => $po_assignee,
						'po_assignee_name' => $po_assignee_name,
					];
					// print_r($po_entry);
				} else {
					echo '<div class="alert alert-danger">The Payment Order is not valid!!</div>';
					return;
				}
			}
			WAMS()->enqueue()->load_tables_script();
			WAMS()->enqueue()->load_bootstrap_table_script();
			$vars['columns'] =
				[
					'orderId' => 34,
					'title' => 22,
					'cost' => 31,
					'vendor' => 'created_by',
					'vendor_name' => 'created_by_name',
				];

			WAMS()->get_template('misbar/payment-orders.php', '', $vars, true);
		}
		function claim_links($atts)
		{
			WAMS()->enqueue()->load_tables_script();
			WAMS()->enqueue()->load_bootstrap_table_script();
			$vars = [
				'links' => [
					'link' => 1,
					'name' => 3,
					'type' => 'gpnf_entry_nested_form_field'
				]
			];
			if (isset($atts['type'])) {
				switch ($atts['type']) {
					case 'claims':
						# code...
						break;

					case 'sources':
						# code...
						break;
				}
			}
			WAMS()->get_template('misbar/links-list.php', '', $vars, true);
		}
		function gravityflow_timeline($atts)
		{
			if (isset($atts['entry_id'])) {
				$entry = \GFAPI::get_entry($atts['entry_id']);
				$workflow_notes = \Gravity_Flow_Common::get_timeline_notes($entry);
				WAMS()->get_template('gravity/gravityflow-timeline.php', '', ['timeline' => $workflow_notes, 'entry' => $entry], true);
			} else echo 'Timeline is missing';
		}
		function gravityflow_wizard($atts, $content = null)
		{
			if (isset($atts['entry_id'])) {
				$entry = \GFAPI::get_entry($atts['entry_id']);
				$form_id = $entry['form_id'];
				$step_data = WAMS()->gravity()->get_step_data($form_id);
				if (empty($step_data)) return;
				WAMS()->get_template('gravity/gravityflow-wizard.php', '', ['steps' => $step_data, 'entry' => $entry], true);
			} else echo 'Timeline is missing';
		}
		function notifications_page()
		{
			WAMS()->enqueue()->load_notifications_page_script();
			WAMS()->enqueue()->load_bootstrap_table_script();
			WAMS()->get_template('notifications/notifications-settings.php', '', [], true);
			WAMS()->get_template('notifications/notifications.php', '', [], true);
		}
		public function user_login_table()
		{
			$logs = [];
			$logs['user_logins'] = WAMS()->common()->user_logins()->get_user_logins(get_current_user_id());

			WAMS()->get_template('user-logins.php', '', $logs, true);
		}
		public function google_analytics_updater()
		{
			WAMS()->enqueue()->load_bootstrap_table_script();
			WAMS()->enqueue()->load_google_analytics_script();
			$rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
			$urls_form_settings = get_option('wams_urls_form_settings');
			$flippedKeys = array_merge($urls_form_settings, ['id' => 'id', 'created_by' => 'created_by', 'date_created' => 'date_created']);
			$urls_form =  $rss_fetcher_settings['urls_form'];
			$flippedKeys = array_flip($flippedKeys);
			// print_r($flippedKeys);
			$entries = WAMS()->gravity()->get_cached_form_entries($urls_form, ['keys' => $flippedKeys]);
			WAMS()->get_template('google-analytics-updater.php', '', ['header' => $urls_form_settings, 'entries' => $entries], true);
		}
		public function fetch_rss()
		{
			WAMS()->enqueue()->load_bootstrap_table_script();
			WAMS()->enqueue()->load_rss_fetcher_script();
			echo '<div id="fetched-links"></div>';
			// WAMS()->get_template('rss-fetcher.php', '', [], true);
		}
		public function page_parser()
		{
			WAMS()->enqueue()->load_page_parser_script();
			WAMS()->get_template('page-parser.php', '', [], true);
		}
		public function urls_list()
		{
			WAMS()->enqueue()->load_bootstrap_table_script();
			WAMS()->enqueue()->load_tables_script();
			$rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
			$urls_form_settings = get_option('wams_urls_form_settings');
			$flippedKeys = array_merge($urls_form_settings, ['id' => 'id', 'created_by' => 'created_by', 'date_created' => 'date_created']);
			$urls_form =  $rss_fetcher_settings['urls_form'];
			$flippedKeys = array_flip($flippedKeys);
			WAMS()->get_template('urls-list.php', '', ['header' => $flippedKeys], true);
		}
		public function reporters_list()
		{
			WAMS()->enqueue()->load_bootstrap_table_script();

			$reporters_arr = [];

			$reporters = get_users(['role__not_in' => ['administrator', 'um_publish-team']]);
			foreach ($reporters as $reporter) {
				$roles = [];
				$direct_manager = get_user_meta($reporter->ID, 'direct_manager', true);
				if ($direct_manager != '') {
					$direct_manager_id = explode('|', $direct_manager)[1];
					$direct_manager = ($direct_manager_id) ? get_userdata($direct_manager_id)->display_name : '';
				}
				foreach ($reporter->roles as $role) {
					$role_name = str_replace(['um_', '-'], ['', ' '], $role);
					$roles[] = ucwords($role_name);
				}
				$reporters_arr[] = [
					'ID' => $reporter->ID,
					'user_login' => $reporter->user_login,
					'user_email' => $reporter->user_email,
					'user_registered' => $reporter->user_registered,
					'roles' => $roles,
					'first_name' => get_user_meta($reporter->ID, 'first_name', true),
					'last_name' => get_user_meta($reporter->ID, 'last_name', true),
					'phone_number' => get_user_meta($reporter->ID, 'phone_number', true),
					'telegram_chat_id' => get_user_meta($reporter->ID, 'telegram_chat_id', true),
					'direct_manager' => $direct_manager,
				];
			}
			WAMS()->get_template('reporters-list.php', '', ['reporters' => $reporters_arr], true);
		}




		/**
		 * Load a compatible template
		 *
		 * @param $tpl
		 */
		function load_template($tpl)
		{
			$loop = ($this->loop) ? $this->loop : array();

			if (isset($this->set_args) && is_array($this->set_args)) {
				$args = $this->set_args;

				unset($args['file'], $args['theme_file'], $args['tpl']);

				$args = apply_filters('wams_template_load_args', $args, $tpl);

				extract($args, EXTR_SKIP);
			}

			$file       = WAMS_PATH . "templates/{$tpl}.php";
			$theme_file = get_stylesheet_directory() . "/wams/templates/{$tpl}.php";
			if (file_exists($theme_file)) {
				$file = $theme_file;
			}

			if (file_exists($file)) {
				// Avoid Directory Traversal vulnerability by the checking the realpath.
				// Templates can be situated only in the get_stylesheet_directory() or plugindir templates.
				$real_file = wp_normalize_path(realpath($file));
				if (0 === strpos($real_file, wp_normalize_path(WAMS_PATH . "templates" . DIRECTORY_SEPARATOR)) || 0 === strpos($real_file, wp_normalize_path(get_stylesheet_directory() . DIRECTORY_SEPARATOR . 'wams' . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR))) {
					include $file;
				}
			}
		}


		/**
		 * Add class based on shortcode
		 *
		 * @param $mode
		 * @param array $args
		 *
		 * @return mixed|string|void
		 */
		function get_class($mode, $args = array())
		{

			$classes = 'wams-' . $mode;

			if (isset($args['template']) && $args['template'] != $args['mode']) {
				$classes .= ' wams-' . $args['template'];
			}

			$classes = apply_filters('wams_form_official_classes__hook', $classes);
			return $classes;
		}




		/**
		 * Shortcode
		 *
		 * @param array $args
		 *
		 * @return string
		 */
		public function wams($args = array())
		{
			// There is possible to use 'shortcode_atts_wams' filter for getting customized `$args`.
			$args = shortcode_atts(
				array(
					'form_id'  => '',
					'is_block' => false,
				),
				$args,
				'wams'
			);

			// Sanitize shortcode arguments.
			$args['form_id']  = !empty($args['form_id']) ? absint($args['form_id']) : '';
			$args['is_block'] = (bool) $args['is_block'];

			$disable_singleton_shortcode = apply_filters('wams_wams_shortcode_disable_singleton', true, $args);
			if (false === $disable_singleton_shortcode) {
				if (isset($args['form_id'])) {
					$id = $args['form_id'];
					if (isset($this->forms_exist[$id]) && true === $this->forms_exist[$id]) {
						return '';
					}
					$this->forms_exist[$id] = true;
				}
			}

			return $this->load($args);
		}

		/**
		 * Load a module with global function
		 *
		 * @param $args
		 *
		 * @return string
		 */
		public function load($args)
		{
			$defaults = array();
			$args     = wp_parse_args($args, $defaults);

			ob_start();

			$args = apply_filters('wams_pre_args_setup', $args);

			if (!isset($args['template'])) {
				$args['template'] = '';
			}

			$args = apply_filters('wams_shortcode_args_filter', $args);

			if (!array_key_exists('mode', $args) || !array_key_exists('template', $args)) {
				ob_get_clean();
				return '';
			}

			$content = apply_filters('wams_force_shortcode_render', false, $args);
			if (false !== $content) {
				ob_get_clean();
				return $content;
			}

			$this->template_load($args['template'], $args);


			do_action('wams_after_everything_output');

			return ob_get_clean();
		}




		/**
		 * Loads a template file
		 *
		 * @param $template
		 * @param array $args
		 */
		public function template_load($template, $args = array())
		{
			if (is_array($args)) {
				$this->set_args = $args;
			}
			$this->load_template($template);
		}


		/**
		 * Checks if a template file exists
		 *
		 * @param $template
		 *
		 * @return bool
		 */
		function template_exists($template)
		{

			$file = WAMS_PATH . 'templates/' . $template . '.php';
			$theme_file = get_stylesheet_directory() . '/wams/templates/' . $template . '.php';

			if (file_exists($theme_file) || file_exists($file)) {
				return true;
			}

			return false;
		}


		/**
		 * Get File Name without path and extension
		 *
		 * @param $file
		 *
		 * @return mixed|string
		 */
		function get_template_name($file)
		{
			$file = basename($file);
			$file = preg_replace('/\\.[^.\\s]{3,4}$/', '', $file);
			return $file;
		}


		/**
		 * Get Templates
		 *
		 * @param null $excluded
		 *
		 * @return mixed
		 */
		function get_templates($excluded = '')
		{

			if ($excluded) {
				$array[$excluded] = __('Default Template', 'wams');
			}

			$paths[] = glob(WAMS_PATH . 'templates/' . '*.php');

			if (file_exists(get_stylesheet_directory() . '/wams/templates/')) {
				$paths[] = glob(get_stylesheet_directory() . '/wams/templates/' . '*.php');
			}

			if (isset($paths) && !empty($paths)) {

				foreach ($paths as $k => $files) {

					if (isset($files) && !empty($files)) {

						foreach ($files as $file) {

							$clean_filename = $this->get_template_name($file);

							if (0 === strpos($clean_filename, $excluded)) {

								$source = file_get_contents($file);
								$tokens = @\token_get_all($source);
								$comment = array(
									T_COMMENT, // All comments since PHP5
									T_DOC_COMMENT, // PHPDoc comments
								);
								foreach ($tokens as $token) {
									if (in_array($token[0], $comment) && strstr($token[1], '/* Template:') && $clean_filename != $excluded) {
										$txt = $token[1];
										$txt = str_replace('/* Template: ', '', $txt);
										$txt = str_replace(' */', '', $txt);
										$array[$clean_filename] = $txt;
									}
								}
							}
						}
					}
				}
			}

			return $array;
		}


		/**
		 * Get Shortcode for given form ID
		 *
		 * @param $post_id
		 *
		 * @return string
		 */
		function get_shortcode($post_id)
		{
			$shortcode = '[wams form_id="' . $post_id . '"]';
			return $shortcode;
		}


		/**
		 * Get Shortcode for given form ID
		 *
		 * @param $post_id
		 *
		 * @return string
		 */
		function get_default_shortcode($post_id)
		{
			$mode = WAMS()->query()->get_attr('mode', $post_id);

			switch ($mode) {
				case 'home-page':
					$shortcode = '[home_page]';
					break;
				case 'my-tasks':
					$shortcode = '[my_tasks]';
					break;
				case 'my-team-tasks':
					$shortcode = '[my_team_tasks]';
					break;
				case 'my-requests':
					$shortcode = '[my_requests]';
					break;
			}

			return $shortcode;
		}





		/**
		 * @param array $args
		 * @param string $content
		 *
		 * @return string
		 */
		public function wams_searchform($args = array(), $content = '')
		{

			$query['s'] = !empty($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

			if (empty($query)) {
				return '';
			}

			$search_value = array_values($query);

			$template = WAMS()->get_template('searchform.php', '', array('query' => $query, 'search_value' => $search_value[0], 'search_page' => '/'));

			return $template;
		}
	}
}
