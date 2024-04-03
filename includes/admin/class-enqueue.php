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
			wp_enqueue_script(['jquery-ui', 'bootstrap', 'jquery-ui', 'sweetalert2', 'wams_admin']);
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
		$libs_url = self::get_url('libs');

		wp_register_script('jquery-ui', $libs_url . 'jquery-ui/jquery-ui.min.js', array(), '1.13.2', true);
		wp_register_script('bootstrap', $libs_url . 'bootstrap/js/bootstrap.min.js', array(), '5.3', true);
		wp_register_script('sweetalert2', $libs_url . 'sweetalert2/sweetalert2.min.js', array(), false, true);
		wp_register_script('wams_admin', $js_url . 'admin/wams-admin.js', array(), '1.0.1', true);
	}

	/**
	 * Register styles.
	 *
	 * @since 1.0.0
	 */
	public function register_styles()
	{
		$css_url = self::get_url('css');
		$libs_url = self::get_url('libs');
		wp_register_style('jquery-ui', $libs_url . 'jquery-ui/jquery-ui.min.css', array(), '1.13.2');
		wp_register_style('bootstrap', $libs_url . 'bootstrap/css/bootstrap.min.css', array(), '5.3');
		wp_register_style('sweetalert2', $libs_url . 'sweetalert2/sweetalert2.min.css', array(), false);
		wp_register_style('wams_admin', $css_url . 'admin/wams-admin.css', array('bootstrap', 'jquery-ui', 'sweetalert2'), '1.0.1');
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 2.0.0
	 */
	public function wp_enqueue_scripts()
	{
	}
}
