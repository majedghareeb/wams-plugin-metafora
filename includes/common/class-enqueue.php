<?php

namespace wams\common;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Enqueue
 *
 * @package wams\common
 */
class Enqueue
{

	/**
	 * @var string scripts' Standard or Minified versions.
	 *
	 * @since 2.7.0
	 */
	public static $suffix = '';

	/**
	 * @var array URLs for easy using.
	 *
	 * @since 2.7.0
	 */
	public static $urls = array(
		'js'   => WAMS_URL . 'assets/js/',
		'css'  => WAMS_URL . 'assets/css/',
		'libs' => WAMS_URL . 'assets/libs/',
	);

	/**
	 * @var string scripts' Standard or Minified versions.
	 *
	 * @since 2.7.0
	 */
	public static $select2_handle = 'select2';

	/**
	 * Enqueue constructor.
	 *
	 * @since 2.7.0
	 */
	public function __construct()
	{
		add_action('admin_enqueue_scripts', array(&$this, 'common_libs'), 9);
		add_action('wp_enqueue_scripts', array(&$this, 'common_libs'), 9);
	}

	/**
	 * Get assets URL.
	 * @since 2.7.0
	 *
	 * @param string $type Can be "js", "css" or "libs".
	 *
	 * @return string
	 */
	public static function get_url($type)
	{
		if (!in_array($type, array('js', 'css', 'libs'), true)) {
			return '';
		}

		return self::$urls[$type];
	}

	/**
	 * Get scripts minified suffix.
	 *
	 * @since 2.7.0
	 *
	 * @return string
	 */
	public static function get_suffix()
	{
		if (empty(self::$suffix)) {
			self::$suffix = ((defined('SCRIPT_DEBUG') && SCRIPT_DEBUG) || (defined('WAMS_SCRIPT_DEBUG') &&  'WAMS_SCRIPT_DEBUG')) ? '' : '.min';
		}
		return self::$suffix;
	}

	/**
	 * Register jQuery-UI styles.
	 *
	 * @since 2.7.0
	 */
	protected function register_jquery_ui()
	{
		wp_register_style('wams_ui', self::get_url('libs') . 'jquery-ui/jquery-ui' . self::get_suffix() . '.css', array(), '1.13.2');
	}

	/**
	 * Register common JS/CSS libraries.
	 *
	 * @since 1.0.0
	 */
	public function common_libs()
	{
		// $this->register_jquery_ui();
		$libs_url = self::get_url('libs');
		$js_url   = self::get_url('js');
		$css_url  = self::get_url('css');
		wp_register_script('wams_jquery_form', $libs_url . 'jquery-form/jquery-form.js', array('jquery'), WAMS_VERSION, true);

		wp_register_script('wams_fileupload', $libs_url . 'fileupload/fileupload.js', array('wams_jquery_form'), WAMS_VERSION, true);
		wp_register_script('wams_functions', $js_url . 'wams-functions.js', array('jquery', 'wams_fileupload', 'wams_jquery_form'), '1.0.0', true);

		wp_register_script('wams_common', $js_url . 'wams_common.js', array('jquery'), '1.0.0', true);
		wp_register_style('wams_common', $css_url . 'wams_common.css', array(), '1.0.0');

		wp_enqueue_style('wams_common');
		wp_enqueue_script('wams_common');
		wp_enqueue_script('wams_functions');
	}
}
