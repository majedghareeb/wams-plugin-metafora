<?php

namespace wams\frontend;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Enqueue.
 *
 * @package um\frontend
 */
final class Enqueue extends \wams\common\Enqueue
{

	/**
	 * @var string
	 * @deprecated 2.8.0
	 */
	public $js_baseurl = '';

	/**
	 * @var string
	 * @deprecated 2.8.0
	 */
	public $css_baseurl = '';

	/**
	 * Enqueue constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('init', array(&$this, 'scripts_enqueue_priority'));
	}

	/**
	 * @since 2.1.3
	 */
	public function scripts_enqueue_priority()
	{
		add_action('wp_enqueue_scripts', array(&$this, 'wp_enqueue_scripts'), $this->get_priority());
	}

	/**
	 * @since 2.1.3
	 * @return int
	 */
	public function get_priority()
	{
		/**
		 * Filters WAMS frontend scripts enqueue priority.
		 *
		 * @since 1.3.x
		 * @hook wams_core_enqueue_priority
		 *
		 * @param {int} $priority WAMS frontend scripts enqueue priority.
		 *
		 * @return {int} WAMS frontend scripts enqueue priority.
		 *
		 * @example <caption>Change WAMS frontend enqueue scripts priority.</caption>
		 * function custom_wams_core_enqueue_priority( $priority ) {
		 *     $priority = 101;
		 *     return $priority;
		 * }
		 * add_filter( 'wams_core_enqueue_priority', 'custom_wams_core_enqueue_priority' );
		 */
		return apply_filters('wams_core_enqueue_priority', 100);
	}

	/**
	 * Register JS scripts.
	 *
	 * @since 2.0.30
	 */
	public function register_scripts()
	{
		$c = get_current_blog_id();
		$js_url   = self::get_url('js');


		wp_register_script('wams_frontend', $js_url . 'frontend/wams-public.js', array(), WAMS_VERSION, true);
		wp_register_script('sweetalert2', $js_url . 'sweetalert2.min.js', array(), WAMS_VERSION, true);
		$localize = array(
			"ajaxurl" => admin_url("admin-ajax.php"),
			'nonce'           => wp_create_nonce('wams-frontend-nonce'),
		);


		$wams_web_notitications_settings = get_option('wams_web_notifications_settings');
		if ($wams_web_notitications_settings && $wams_web_notitications_settings['enabled'] == 'on') {
			$timer = $wams_web_notitications_settings['interval'] ?? '';
			$sound = $wams_web_notitications_settings['sound_enabled'] == 'on' ? 1 : 0;
			$localize =  array_merge(
				$localize,
				[
					'sound'      => (int) $sound,
					'sound_url'  => (string) WAMS_URL . ('assets/sound/light.mp3'),
					'timer'      => (int) $timer * 1000
				]

			);
			wp_register_script('wams_web_notifications', $js_url . 'frontend/web-notifications.js', array('jquery', 'wp-util'), WAMS_VERSION, true);
		}
		wp_localize_script('wams_frontend', "wams_frontend_scripts", $localize);
	}

	/**
	 * Register styles.
	 *
	 * @since 2.0.30
	 */
	public function register_styles()
	{
		$css_url = self::get_url('css');
		wp_register_style('sweetalert2', $css_url . 'sweetalert2.min.css', array(), WAMS_VERSION);
		wp_register_style('wams_frontend', $css_url . 'frontend/wams-public.css', array('sweetalert2'), WAMS_VERSION);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function wp_enqueue_scripts()
	{
		$this->register_scripts();
		$this->register_styles();


		//old settings before UM 2.0 CSS
		wp_enqueue_style('sweetalert2', 'wams_frontend');
		wp_enqueue_script(['wams_frontend', 'sweetalert2', 'wams_web_notifications']);
	}






	/**
	 * Load JS functions.
	 *
	 * @since 2.0.0
	 */
	public function load_functions()
	{
		wp_enqueue_script('wams_functions');
	}

	/**
	 * Load custom JS.
	 *
	 * @since 2.0.0
	 */
	public function load_customjs()
	{
		wp_enqueue_script('wams_conditional');
		wp_enqueue_script('wams_scripts');
		wp_enqueue_script('wams_profile');
		wp_enqueue_script('wams_account');
	}

	/**
	 * Load modal.
	 *
	 * @since 2.0.0
	 */
	public function load_modal()
	{
		wp_enqueue_script('wams_modal');
		wp_enqueue_style('wams_modal');
	}

	/**
	 * Load responsive styles.
	 *
	 * @since 2.0.0
	 */
	public function load_responsive()
	{
		wp_enqueue_script('wams_responsive');
		wp_enqueue_style('wams_responsive');
	}

	/**
	 * Include Google charts
	 * @deprecated 2.8.0
	 */
	public function load_google_charts()
	{
	}

	/**
	 * Load fileupload JS
	 * @deprecated 2.8.0
	 */
	public function load_fileupload()
	{
	}

	/**
	 * Load date & time picker
	 * @deprecated 2.8.0
	 */
	public function load_datetimepicker()
	{
	}

	/**
	 * Load scrollbar
	 * @deprecated 2.8.0
	 */
	public function load_scrollbar()
	{
	}

	/**
	 * Load crop script
	 * @deprecated 2.8.0
	 */
	public function load_imagecrop()
	{
	}
}
