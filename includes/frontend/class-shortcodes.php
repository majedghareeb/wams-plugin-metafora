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
			add_shortcode('upload-vendors-list', array(&$this, 'upload_vendors_list'));
			add_shortcode('google-analytics-test', array(&$this, 'google_analytics_test'));
			add_shortcode('page-parser', array(&$this, 'page_parser'));
			add_shortcode('link-my-telegram', array(WAMS()->telegram_notifications(), 'link_my_telegram_page'));
			add_shortcode('view-timeline', array(&$this, 'view_timeline'));
			add_shortcode('wams-charts', array(WAMS()->frontend()->charts(), 'show_chart'));
			add_shortcode('wams-tasks-calendar', array(WAMS()->frontend()->tasks_calendar(), 'show_calendar'));
			add_shortcode('wams-user-logins', array($this, 'user_login_table'));
			// add_shortcode('wams', array(&$this, 'wams'));
			add_shortcode('wams_searchform', array(&$this, 'wams_searchform'));
			add_shortcode('wams_notifications', array(WAMS()->web_notifications(), 'notifications_page'));
			add_shortcode('wams_messages', array(WAMS()->messages(), 'messages_page'));
		}
		public function home_page()
		{
			if (!is_user_logged_in()) return '';
			$query = 'get';
			$search_value = '1';
			$dashboard = WAMS()->frontend()->user_dashboard();
			$dashboard_cards = [
				'my_tasks' => $dashboard->wams_user_tasks(),
				'my_requests' => $dashboard->wams_user_requests(),
				'my_team_tasks' => $dashboard->wams_user_team_tasks(),
				'user_profile_details' => $dashboard->show_user_profile_details(),
			];
			$home = WAMS()->get_template('home-page.php', '', $dashboard_cards, true);
		}

		public function user_login_table()
		{
			$logs = [];
			$logs['user_logins'] = WAMS()->common()->user_logins()->get_user_logins(get_current_user_id());

			WAMS()->get_template('user-logins.php', '', $logs, true);
		}
		public function google_analytics_test()
		{
			echo '<h1>google_analytics_test</h1>';
		}
		public function fetch_rss()
		{
			echo '<button id="test-btn" >fetch_rss</button>';
		}
		public function upload_vendors_list()
		{
			echo '<button id="test-btn" >fetch_rss</button>';
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
