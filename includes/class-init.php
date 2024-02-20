<?php
if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('WAMS')) {

	/**
	 * Main WAMS Class
	 *
	 * @class WAMS
	 * @version 1.0
	 *
	 */
	final class WAMS extends WAMS_Functions
	{
		/**
		 * @var WAMS the single instance of the class
		 */
		protected static $instance;

		/**
		 * @var array all plugin's classes
		 */
		public $classes = array();

		/**
		 * WP Native permalinks turned on?
		 *
		 * @var
		 */
		public $is_permalinks = false;

		/**
		 * @var null|string
		 */
		public $honeypot = null;

		/**
		 * Main WAMS Instance
		 *
		 * Ensures only one instance of WAMS is loaded or can be loaded.
		 * @class WAMS
		 * @since 1.0
		 */
		public static function instance()
		{
			if (is_null(self::$instance)) {
				self::$instance = new self();
				self::$instance->_wams_construct();
			}
			return self::$instance;
		}

		/**
		 * Function for add classes to $this->classes
		 * for run using WAMS()
		 *
		 * @since 2.0
		 *
		 * @param string $class_name
		 * @param bool $instance
		 */
		public function set_class($class_name, $instance = false)
		{
			if (empty($this->classes[$class_name])) {
				$class = 'WAMS_' . $class_name;
				$this->classes[$class_name] = $instance ? $class::instance() : new $class;
			}
		}


		/**
		 * Cloning is forbidden.
		 * @since 1.0
		 */
		public function __clone()
		{
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'wams'), '1.0');
		}


		/**
		 * Unserializing instances of this class is forbidden.
		 * @since 1.0
		 */
		public function __wakeup()
		{
			_doing_it_wrong(__FUNCTION__, __('Cheatin&#8217; huh?', 'wams'), '1.0');
		}


		/**
		 * WAMS constructor.
		 *
		 * @since 1.0
		 */
		function __construct()
		{
			parent::__construct();
		}


		/**
		 * WAMS pseudo-constructor.
		 *
		 * @since 2.0.18
		 */
		function _wams_construct()
		{
			//register autoloader for include WAMS classes \wams\common\Enqueue
			spl_autoload_register(array($this, 'wams__autoloader'));

			if (!defined('WP_UNINSTALL_PLUGIN')) {

				if (get_option('permalink_structure')) {
					$this->is_permalinks = true;
				}

				$this->honeypot = 'wams_request';

				// textdomain loading
				add_action('init', array(&$this, 'localize'), 0);

				// include WAMS classes
				$this->includes();

				// include hook files
				add_action('plugins_loaded', array(&$this, 'init'), 0);

				//run activation
				register_activation_hook(WAMS_PLUGIN, array(&$this, 'activation'));

				register_deactivation_hook(WAMS_PLUGIN, array(&$this, 'deactivation'));

				if (is_multisite() && !defined('DOING_AJAX')) {
					add_action('wp_loaded', array($this, 'maybe_network_activation'));
				}

				// init widgets
				// add_action('widgets_init', array(&$this, 'widgets_init'));

				//include short non class functions
				require_once 'wams-short-functions.php';
			}
		}


		/**
		 * Loading WAMS textdomain
		 *
		 * 'wams' by default
		 */
		public function localize()
		{
			// The function `get_user_locale()` will return `get_locale()` result by default if user or its locale is empty.
			$language_locale = get_user_locale();
			$language_locale = apply_filters('wams_language_locale', $language_locale);

			$language_domain = apply_filters('wams_language_textdomain', 'wams');

			$language_file = WP_LANG_DIR . '/plugins/' . $language_domain . '-' . $language_locale . '.mo';

			$language_file = apply_filters('wams_language_file', $language_file);

			// Unload textdomain if it has already loaded.
			if (is_textdomain_loaded($language_domain)) {
				unload_textdomain($language_domain, true);
			}
			load_textdomain($language_domain, $language_file);
		}


		/**
		 * Autoload WAMS classes handler
		 * \wams\common\Enqueue
		 * @since 2.0
		 *
		 * @param $class
		 */
		function wams__autoloader($class)
		{
			if (strpos($class, 'wams') === 0) {

				$array = explode('\\', strtolower($class));
				$array[count($array) - 1] = 'class-' . end($array);
				if (strpos($class, 'wams_ext') === 0) {
					$full_path = str_replace('wams', '', untrailingslashit(WAMS_PATH)) . str_replace('_', '-', $array[1]) . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR;
					unset($array[0], $array[1]);
					$path = implode(DIRECTORY_SEPARATOR, $array);
					$path = str_replace('_', '-', $path);
					$full_path .= $path . '.php';
				} else if (strpos($class, 'wams\\') === 0) {
					$class = implode('\\', $array);
					$slash = DIRECTORY_SEPARATOR;
					$path = str_replace(
						array('wams\\', '_', '\\'),
						array($slash, '-', $slash),
						$class
					);
					$full_path =  WAMS_PATH . 'includes' . $path . '.php';
				}

				if (isset($full_path) && file_exists($full_path)) {
					include_once $full_path;
				}
			}
		}


		/**
		 * Plugin Activation
		 *
		 * @since 2.0
		 */
		function activation()
		{
			$this->single_site_activation();
			if (is_multisite()) {
				update_network_option(get_current_network_id(), 'wams_maybe_network_wide_activation', 1);
			}
		}


		/**
		 * Plugin Deactivation
		 *
		 * @since 2.3
		 */
		function deactivation()
		{
			$this->cron()->unschedule_events();
		}


		/**
		 * Maybe need multisite activation process
		 *
		 * @since 2.1.7
		 */
		function maybe_network_activation()
		{
			$maybe_activation = get_network_option(get_current_network_id(), 'wams_maybe_network_wide_activation');

			if ($maybe_activation) {

				delete_network_option(get_current_network_id(), 'wams_maybe_network_wide_activation');

				if (is_plugin_active_for_network(WAMS_PLUGIN)) {
					// get all blogs
					$blogs = get_sites();
					if (!empty($blogs)) {
						foreach ($blogs as $blog) {
							switch_to_blog($blog->blog_id);
							//make activation script for each sites blog
							$this->single_site_activation();
							restore_current_blog();
						}
					}
				}
			}
		}


		/**
		 * Single site plugin activation handler
		 */
		function single_site_activation()
		{
			//first install
			$version = get_option('wams_version');
			if (!$version) {
				update_option('wams_last_version_upgrade', WAMS_VERSION);

				add_option('wams_first_activation_date', time());
			} else {
				WAMS()->options()->update('rest_api_version', '1.0');
			}

			if ($version != WAMS_VERSION) {
				update_option('wams_version', WAMS_VERSION);
			}

			//run setup
			// $this->common()->cpt()->create_post_types();
			$this->setup()->run_setup();

			$this->cron()->schedule_events();
		}

		/**
		 * Include required core files used in admin and on the frontend.
		 *
		 * @since 2.0
		 *
		 * @return void
		 */
		public function includes()
		{
			// TODO 

			// Not important now
			$this->common()->includes();

			if ($this->is_request('ajax')) {
				$this->ajax()->includes();
				$this->admin();
				$this->ajax_init(); // All Ajax Calls
				$this->admin_ajax_hooks();
				$this->frontend_ajax_hooks();
				// $this->admin()->notices();
			} elseif ($this->is_request('admin')) {
				$this->admin();
				$this->admin()->includes();

				// $this->admin()->test_module();

			} elseif ($this->is_request('frontend')) {
				$this->frontend()->includes();
				$this->web_notifications();
			}

			//common includes
			$this->rewrite();
			$this->rest_api();

			// $this->form()->hooks();
			// $this->permalinks();
			$this->cron();
			$this->telegram_notifications();
			$this->web_notifications();
			// $this->mobile();

			//if multisite networks active
			if (is_multisite()) {
				$this->multisite();
			}
		}


		/**
		 * @param $class
		 *
		 * @return mixed
		 */
		function call_class($class)
		{
			$key = strtolower($class);

			if (empty($this->classes[$key])) {
				$this->classes[$key] = new $class;
			}

			return $this->classes[$key];
		}

		/**
		 * @since 2.6.8
		 *
		 * @return wams\ajax\Init
		 */
		public function ajax()
		{
			if (empty($this->classes['wams\ajax\init'])) {
				$this->classes['wams\ajax\init'] = new wams\ajax\Init();
			}

			return $this->classes['wams\ajax\init'];
		}

		/**
		 * @since 2.0
		 * @since 2.6.8 changed namespace and class content.
		 *
		 * @return wams\common\Init
		 */
		public function common()
		{
			if (empty($this->classes['wams\common\init'])) {
				$this->classes['wams\common\init'] = new wams\common\Init();
			}
			return $this->classes['wams\common\init'];
		}

		/**
		 * @since 2.6.8
		 *
		 * @return wams\frontend\Init
		 */
		public function frontend()
		{
			if (empty($this->classes['wams\frontend\init'])) {
				$this->classes['wams\frontend\init'] = new wams\frontend\Init();
			}
			return $this->classes['wams\frontend\init'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return wams\core\Telegram_Notifications()
		 */
		function telegram_notifications()
		{
			if (empty($this->classes['telegram_notifications'])) {
				$this->classes['telegram_notifications'] = new wams\core\Telegram_Notifications();
			}
			return $this->classes['telegram_notifications'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return wams\core\Web_Notifications()
		 */
		function web_notifications()
		{
			if (empty($this->classes['web_notifications'])) {
				$this->classes['web_notifications'] = new wams\core\Web_Notifications();
			}
			return $this->classes['web_notifications'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return wams\core\Messages()
		 */
		function messages()
		{
			if (empty($this->classes['messages'])) {
				$this->classes['messages'] = new wams\core\Messages();
			}
			return $this->classes['messages'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Options()
		 */
		function options()
		{
			if (empty($this->classes['options'])) {
				$this->classes['options'] = new wams\core\Options();
			}
			return $this->classes['options'];
		}


		/**
		 * @since 2.0
		 */
		function ajax_init()
		{
			new wams\core\AJAX_Common();
		}

		/**
		 * @since 2.0.30
		 */
		function admin_ajax_hooks()
		{
			if (empty($this->classes['admin_ajax_hooks'])) {
				$this->classes['admin_ajax_hooks'] = new wams\admin\core\Admin_Ajax_Hooks();
			}
			return $this->classes['admin_ajax_hooks'];
		}

		/**
		 * @since 2.0.30
		 */
		function frontend_ajax_hooks()
		{
			if (empty($this->classes['frontend_ajax_hooks'])) {
				$this->classes['frontend_ajax_hooks'] = new wams\frontend\Ajax_Hooks();
			}
			return $this->classes['frontend_ajax_hooks'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\admin\Admin
		 */
		public function admin()
		{
			if (empty($this->classes['admin'])) {
				$this->classes['admin'] = new wams\admin\Admin();
			}
			return $this->classes['admin'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\Dependencies
		 */
		function dependencies()
		{
			if (empty($this->classes['dependencies'])) {
				$this->classes['dependencies'] = new wams\Dependencies();
			}

			return $this->classes['dependencies'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\Config
		 */
		function config()
		{
			if (empty($this->classes['config'])) {
				$this->classes['config'] = new wams\Config();
			}

			return $this->classes['config'];
		}


		/**
		 * @since 1.0.0
		 *
		 * @return wams\core\rest\API_v2
		 */
		function rest_api()
		{
			if (empty($this->classes['rest_api'])) {
				$this->classes['rest_api'] = new wams\core\rest\API_v2();
			}
			return $this->classes['rest_api'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Rewrite
		 */
		function rewrite()
		{
			if (empty($this->classes['rewrite'])) {
				$this->classes['rewrite'] = new wams\core\Rewrite();
			}

			return $this->classes['rewrite'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Setup
		 */
		function setup()
		{
			if (empty($this->classes['setup'])) {
				$this->classes['setup'] = new wams\core\Setup();
			}

			return $this->classes['setup'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\FontIcons
		 */
		function fonticons()
		{
			if (empty($this->classes['fonticons'])) {
				$this->classes['fonticons'] = new wams\core\FontIcons();
			}

			return $this->classes['fonticons'];
		}


		/**
		 * @since 1.0.0
		 *
		 * @return wams\core\Login
		 */
		function login()
		{
			if (empty($this->classes['login'])) {
				$this->classes['login'] = new wams\core\Login();
			}

			return $this->classes['login'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Register
		 */
		function register()
		{
			if (empty($this->classes['register'])) {
				$this->classes['register'] = new wams\core\Register();
			}

			return $this->classes['register'];
		}




		/**
		 * @since 2.0
		 *
		 * @return wams\core\Account
		 */
		function account()
		{
			if (empty($this->classes['account'])) {
				$this->classes['account'] = new wams\core\Account();
			}

			return $this->classes['account'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Password
		 */
		function password()
		{
			if (empty($this->classes['password'])) {
				$this->classes['password'] = new wams\core\Password();
			}

			return $this->classes['password'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Form
		 */
		public function form()
		{
			if (empty($this->classes['form'])) {
				$this->classes['form'] = new wams\core\Form();
			}

			return $this->classes['form'];
		}

		/**
		 * @since 2.0
		 *
		 * @return wams\core\Query
		 */
		function query()
		{
			if (empty($this->classes['query'])) {
				$this->classes['query'] = new wams\core\Query();
			}

			return $this->classes['query'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Date_Time
		 */
		function datetime()
		{
			if (empty($this->classes['datetime'])) {
				$this->classes['datetime'] = new wams\core\Date_Time();
			}

			return $this->classes['datetime'];
		}

		/**
		 * @since 2.0
		 *
		 * @return wams\core\Builtin
		 */
		function builtin()
		{
			if (empty($this->classes['builtin'])) {
				$this->classes['builtin'] = new wams\core\Builtin();
			}

			return $this->classes['builtin'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Files
		 */
		function files()
		{
			if (empty($this->classes['files'])) {
				$this->classes['files'] = new wams\core\Files();
			}

			return $this->classes['files'];
		}


		/**
		 * @since 1.0.0
		 *
		 * @return wams\core\Uploader
		 */
		function uploader()
		{
			if (empty($this->classes['uploader'])) {
				$this->classes['uploader'] = new wams\core\Uploader();
			}
			return $this->classes['uploader'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Validation
		 */
		function validation()
		{
			if (empty($this->classes['validation'])) {
				$this->classes['validation'] = new wams\core\Validation();
			}

			return $this->classes['validation'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Access
		 */
		function access()
		{
			if (empty($this->classes['access'])) {
				$this->classes['access'] = new wams\core\Access();
			}

			return $this->classes['access'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Cron
		 */
		function cron()
		{
			if (empty($this->classes['cron'])) {
				$this->classes['cron'] = new wams\core\Cron();
			}

			return $this->classes['cron'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\core\Templates
		 */
		function templates()
		{
			if (empty($this->classes['templates'])) {
				$this->classes['templates'] = new wams\core\Templates();
			}

			return $this->classes['templates'];
		}


		/**
		 * @since 2.0
		 *
		 * @return wams\lib\mobiledetect\Um_Mobile_Detect
		 */
		function mobile()
		{
			if (empty($this->classes['mobile'])) {
				$this->classes['mobile'] = new wams\lib\mobiledetect\Um_Mobile_Detect();
			}

			return $this->classes['mobile'];
		}


		/**
		 * @since 2.0.44
		 *
		 * @return wams\core\Multisite
		 */
		function multisite()
		{

			if (empty($this->classes['multisite'])) {
				$this->classes['multisite'] = new wams\core\Multisite();
			}

			return $this->classes['multisite'];
		}


		/**
		 * Include files with hooked filters/actions
		 *
		 * @since 1.0.0
		 */
		function init()
		{
			ob_start();
			require_once 'core/wams-actions-ajax.php';
			require_once 'core/wams-actions-global.php';
		}



		/**
		 * Init WAMS widgets
		 *
		 * @since 2.0
		 */
		function widgets_init()
		{
			register_widget('wams\widgets\WAMS_Search_Widget');
		}
	}
}


/**
 * Function for calling WAMS methods and variables
 *
 * @since 1.0.0
 *
 * @return WAMS
 */
function WAMS()
{
	return WAMS::instance();
}


// Global for backwards compatibility.
$GLOBALS['wams-plugin'] = WAMS();
