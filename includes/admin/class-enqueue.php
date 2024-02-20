<?php

namespace wams\admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Enqueue
 *
 * @package um\admin
 */
final class Enqueue extends \wams\common\Enqueue
{

	/**
	 * @var string
	 * @deprecated 2.8.0
	 */
	public $js_url;

	/**
	 * @var string
	 * @deprecated 2.8.0
	 */
	public $css_url;

	/**
	 * @var string
	 * @deprecated 2.8.0
	 */
	public $front_js_baseurl;

	/**
	 * @var string
	 * @deprecated 2.8.0
	 */
	public $front_css_baseurl;

	/**
	 * @var bool
	 * @deprecated 2.8.0
	 */
	public $post_page;

	/**
	 * @var bool
	 */
	private static $wams_cpt_form_screen = false;

	/**
	 * Enqueue constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('admin_enqueue_scripts', array(&$this, 'admin_enqueue_scripts'));
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function admin_enqueue_scripts()
	{

		$this->load_global_scripts();

		if (WAMS()->admin()->screen()->is_own_screen()) {
			$this->register_scripts();
			$this->register_styles();
			wp_localize_script('wams_admin', "wams_admin_scripts", array(
				"name" => "WAMS Plugin",
				"ajaxurl" => admin_url("admin-ajax.php"),
				'nonce' => wp_create_nonce('wams-admin-nonce'),
			));

			//old settings before UM 2.0 CSS
			wp_enqueue_style('wams_admin');
			wp_enqueue_script('wams_admin');
		}
	}
	/**
	 * Register JS scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts()
	{
		$js_url   = self::get_url('js');

		wp_register_script('wams_jquery-ui', $js_url . 'admin/jquery-ui.min.js', array(), '1.13.2', true);
		wp_register_script('wams_bs', $js_url . 'bootstrap.bundle.min.js', array(), '5.2.3', true);
		wp_register_script('wams_admin', $js_url . 'admin/wams-admin.js', array('wams_bs', 'wams_jquery-ui'), WAMS_VERSION, true);
	}

	/**
	 * Register styles.
	 *
	 * @since 1.0.0
	 */
	public function register_styles()
	{
		$css_url = self::get_url('css');
		wp_register_style('wams_jquery-ui', $css_url . 'admin/jquery-ui.min.css', array(), '1.13.2');
		wp_register_style('wams_bs', $css_url . 'bootstrap.min.css', array(), '5.2.3');
		wp_register_style('wams_admin', $css_url . 'admin/wams-admin.css', array('wams_bs', 'wams_jquery-ui'), WAMS_VERSION);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function wp_enqueue_scripts()
	{
	}

	/**
	 * Load global assets.
	 *
	 * @since 2.0.18
	 */
	public function load_global_scripts()
	{
		$suffix  = self::get_suffix();
		$js_url  = self::get_url('js');
		$css_url = self::get_url('css');
		wp_register_style('wams_admin_global', $css_url . 'admin/global' . $suffix . '.css', array(), WAMS_VERSION);
		wp_enqueue_style('wams_admin_global');
	}
}
