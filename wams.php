<?php

/**
 * Plugin Name: WAMS2
 * Plugin URI: http://wams.com/
 * Description: The New WAMS Plugin
 * Version: 1.0.0
 * Author: WAMS
 * Author URI: http://wams.com/
 * Text Domain: wams
 * Domain Path: /languages
 * Requires at least: 5.5
 * Requires PHP: 8.0
 *
 * @package WAMS
 */

defined('ABSPATH') || exit;

require_once ABSPATH . 'wp-admin/includes/plugin.php';
$plugin_data = get_plugin_data(__FILE__);

// phpcs:disable Generic.NamingConventions.UpperCaseConstantName
define('wams_url', plugin_dir_url(__FILE__));
define('wams_path', plugin_dir_path(__FILE__));
define('wams_plugin', plugin_basename(__FILE__));
define('wams_version', $plugin_data['Version']);
define('wams_plugin_name', $plugin_data['Name']);
// phpcs:enable Generic.NamingConventions.UpperCaseConstantName

define('WAMS_URL', plugin_dir_url(__FILE__));
define('WAMS_PATH', plugin_dir_path(__FILE__));
define('WAMS_PLUGIN', plugin_basename(__FILE__));
define('WAMS_VERSION', $plugin_data['Version']);
define('WAMS_PLUGIN_NAME', $plugin_data['Name']);

// define( 'WAMS_LEGACY_BUILDER_OFF', true );

define('WAMS_MAIN_BLOG_ID', 1);
define('WAMS_ARCHIVE_BLOG_ID', 2);
define('WAMS_INPUT_BLOG_ID', 3);

require_once 'includes/class-functions.php';
require_once 'includes/class-init.php';