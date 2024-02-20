<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Support multisite
 *
 * @param $dir
 *
 * @return string
 */
function wams_multisite_urls_support($dir)
{

	if (is_multisite()) { // Need to the work

		if (get_current_blog_id() == '1') {
			return $dir;
		}

		/**
		 * WAMS hook
		 *
		 * @type filter
		 * @title wams_multisite_upload_sites_directory
		 * @description Change multisite uploads directory
		 * @input_vars
		 * [{"var":"$sites_dir","type":"string","desc":"Upload sites directory"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'wams_multisite_upload_sites_directory', 'function_name', 10, 1 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'wams_multisite_upload_sites_directory', 'my_multisite_upload_sites_directory', 10, 1 );
		 * function my_multisite_upload_sites_directory( $sites_dir ) {
		 *     // your code here
		 *     return $sites_dir;
		 * }
		 * ?>
		 */
		$sites_dir = apply_filters('wams_multisite_upload_sites_directory', 'sites/');
		$split = explode($sites_dir, $dir);
		/**
		 * WAMS hook
		 *
		 * @type filter
		 * @title wams_multisite_upload_directory
		 * @description Change multisite WAMS uploads directory
		 * @input_vars
		 * [{"var":"$wams_dir","type":"string","desc":"Upload WAMS directory"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'wams_multisite_upload_directory', 'function_name', 10, 1 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'wams_multisite_upload_directory', 'my_multisite_upload_directory', 10, 1 );
		 * function my_multisite_upload_directory( $wams_dir ) {
		 *     // your code here
		 *     return $wams_dir;
		 * }
		 * ?>
		 */
		$wams_dir = apply_filters('wams_multisite_upload_directory', 'wams/');
		$dir = $split[0] . $wams_dir;
	}

	return $dir;
}
add_filter('wams_upload_basedir_filter', 'wams_multisite_urls_support', 99);
add_filter('wams_upload_baseurl_filter', 'wams_multisite_urls_support', 99);
