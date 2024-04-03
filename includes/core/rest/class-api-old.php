<?php

namespace wams\core\rest;


if (!defined('ABSPATH')) exit;


if (!class_exists('wams\core\rest\API')) {


	/**
	 * Class API
	 *
	 * @package wams\core\rest
	 */
	class API
	{


		/**
		 * @var bool|int|null
		 */
		protected $pretty_print = false;


		/**
		 * @var bool|mixed|void
		 */
		public 	$log_requests = true;


		/**
		 * @var bool
		 */
		protected $is_valid_request = false;


		/**
		 * @var int
		 */
		protected $user_id = 0;


		/**
		 * @var
		 */
		protected $stats;


		/**
		 * @var array
		 */
		protected $data = array();


		/**
		 * @var bool
		 */
		protected $override = true;


		/**
		 * @var array
		 */
		protected $vars = array();


		/**
		 * REST_API constructor.
		 */
		public function __construct()
		{

			add_action('init', array($this, 'add_endpoint'));
			add_action('template_redirect', array($this, 'api_test'), -1);

			// Determine if JSON_PRETTY_PRINT is available
			$this->pretty_print = defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : null;
		}



		public function api_test()
		{
			global $wp_query;

			$this->data = get_query_var('wams-api', false);
			if (isset($wp_query->query['name']) && $wp_query->query['name'] == 'wams-api') {
				$this->output();
			}
		}

		/**
		 * Registers a new rewrite endpoint for accessing the API
		 *
		 * @param $rewrite_rules
		 */
		public function add_endpoint($rewrite_rules)
		{
			add_rewrite_endpoint('wams-api', EP_PERMALINK);
		}


		function template_redirect()
		{
			global $wp_query;
			if (isset($wp_query->query['wams-api'])) {
				$args = array();
				$this->data = $this->get_stats($args);
				$this->output();
			}
		}

		public function handle_api_requests()
		{
			global $wp;

			if (!empty($_GET['wams-api'])) { // WPCS: input var okay, CSRF ok.
				$wp->query_vars['wams-api'] = sanitize_key(wp_unslash($_GET['wams-api'])); // WPCS: input var okay, CSRF ok.
			}

			// wams-api endpoint requests.
			if (!empty($wp->query_vars['wams-api'])) {

				$this->data = ['Data is Here'];


				$this->output();
			}
		}

		/**
		 * Listens for the API and then processes the API requests
		 */
		public function process_query()
		{
			global $wp_query;

			// Check for wams-api var. Get out if not present
			if (!isset($wp_query->query_vars['wams-api'])) {
				return;
			}

			// Check for a valid user and set errors if necessary
			$this->validate_request();

			// Only proceed if no errors have been noted
			if (!$this->is_valid_request) {
				return;
			}

			if (!defined('WAMS_DOING_API')) {
				define('WAMS_DOING_API', true);
			}

			// Determine the kind of query
			$args = array();
			$query_mode = $this->get_query_mode();
			foreach ($this->vars as $k) {
				$args[$k] = isset($wp_query->query_vars[$k]) ? $wp_query->query_vars[$k] : null;
			}

			$data = array();

			switch ($query_mode) {
				case 'get.stats':
					$data = $this->get_stats($args);
					break;

				case 'get.users':
					$data = $this->get_users($args);
					break;

				case 'get.user':
					$data = $this->get_auser($args);
					break;

				case 'update.user':
					$data = $this->update_user($args);
					break;

				case 'delete.user':
					$data = $this->delete_user($args);
					break;

				default:

					$data = apply_filters('wams_rest_query_mode', $data, $query_mode, $args);
					break;
			}


			$this->data = apply_filters('wams_api_output_data', $data, $query_mode, $this);

			// Log this API request, if enabled. We log it here because we have access to errors.
			$this->log_request($this->data);

			// Send out data to the output function
			$this->output();
		}


		/**
		 * Validate the API request
		 */
		protected function validate_request()
		{
		}


		/**
		 * Retrieve the user ID based on the public key provided
		 *
		 * @param string $key
		 *
		 * @return bool
		 */
		public function get_user($key = '')
		{
			return false;
		}


		/**
		 * Displays a missing authentication error if all the parameters aren't
		 * provided
		 */
		protected function missing_auth()
		{
			$error = array();
			$error['error'] = __('You must specify both a token and API key!', 'wams');

			$this->data = $error;
			$this->output(401);
		}


		/**
		 * Displays an authentication failed error if the user failed to provide valid credentials
		 */
		protected function invalid_auth()
		{
			$error = array();
			$error['error'] = __('Your request could not be authenticated', 'wams');

			$this->data = $error;
			$this->output(401);
		}


		/**
		 * Displays an invalid API key error if the API key provided couldn't be validated
		 */
		protected function invalid_key()
		{
			$error = array();
			$error['error'] = __('Invalid API key', 'wams');

			$this->data = $error;
			$this->output(401);
		}


		/**
		 * Get some stats
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function get_stats($args)
		{
			global $wpdb;

			$response = array();

			$count = absint($wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}users"));
			$response['stats']['total_users'] = $count;

			$pending = WAMS()->query()->get_pending_users_count();
			$response['stats']['pending_users'] = absint($pending);


			$response = apply_filters('wams_rest_api_get_stats', $response);
			return $response;
		}


		/**
		 * Process Get users API Request
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function get_users($args)
		{
			return array();
		}


		/**
		 * Update user API query
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function update_user($args)
		{
			return array();
		}


		/**
		 * Process delete user via API
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function delete_user($args)
		{
			return array();
		}


		/**
		 * Process Get user API Request
		 *
		 * @param $args
		 *
		 * @return array
		 */
		public function get_auser($args)
		{
			return array();
		}


		/**
		 * Get source
		 *
		 * @param $image
		 *
		 * @return string
		 */
		protected function getsrc($image)
		{
			if (preg_match('/<img.+?src(?: )*=(?: )*[\'"](.*?)[\'"]/si', $image, $arrResult)) {
				return $arrResult[1];
			}
			return '';
		}


		/**
		 * Determines the kind of query requested and also ensure it is a valid query
		 *
		 * @return null
		 */
		public function get_query_mode()
		{
			global $wp_query;


			$accepted = apply_filters('wams_api_valid_query_modes', array(
				'get.users',
				'get.user',
				'update.user',
				'delete.user',
				'get.following',
				'get.followers',
				'get.stats',
			));

			$query = isset($wp_query->query_vars['wams-api']) ? $wp_query->query_vars['wams-api'] : null;
			$error = array();
			// Make sure our query is valid
			if (!in_array($query, $accepted)) {
				$error['error'] = __('Invalid query!', 'wams');

				$this->data = $error;
				$this->output();
			}

			return $query;
		}


		/**
		 * Get page number
		 */
		public function get_paged()
		{
			global $wp_query;

			return isset($wp_query->query_vars['page']) ? $wp_query->query_vars['page'] : 1;
		}


		/**
		 * Retrieve the output format
		 */
		public function get_output_format()
		{
			return apply_filters('wams_api_output_format', 'json');
		}


		/**
		 * Log each API request, if enabled
		 *
		 * @param array $data
		 */
		protected function log_request($data = array())
		{
			if (!$this->log_requests) {
				return;
			}
		}


		/**
		 * Retrieve the output data
		 */
		public function get_output()
		{
			return $this->data;
		}


		/**
		 * Output Query in either JSON/XML. The query data is outputted as JSON
		 * by default
		 *
		 * @param int $status_code
		 */
		public function output($status_code = 200)
		{
			$format = $this->get_output_format();

			status_header($status_code);

			do_action('wams_api_output_before', $this->data, $this, $format);

			switch ($format) {

				case 'xml':

					require_once WAMS_PATH . 'includes/lib/array2xml.php';
					$xml = \Array2XML::createXML('um', $this->data);
					echo $xml->saveXML();

					break;

				case 'json':
				case '':

					header('Content-Type: application/json');
					if (!empty($this->pretty_print)) {
						echo json_encode($this->data, $this->pretty_print);
					} else {
						echo json_encode($this->data);
					}

					break;


				default:

					do_action('wams_api_output_' . $format, $this->data, $this);

					break;
			}

			do_action('wams_api_output_after', $this->data, $this, $format);

			die();
		}
	}
}
