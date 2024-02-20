<?php
// Exit if accessed directly
if (!defined('ABSPATH')) exit;

/**
 * Run check if username exists
 * @uses action hooks: wp_ajax_nopriv_wams_check_username_exists, wp_ajax_wams_check_username_exists
 * @return boolean
 */
function wams_check_username_exists()
{
	WAMS()->check_ajax_nonce();

	$username = isset($_REQUEST['username']) ? sanitize_user($_REQUEST['username']) : '';
	$exists   = username_exists($username);

	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_validate_username_exists
	 * @description Change username exists validation
	 * @input_vars
	 * [{"var":"$exists","type":"bool","desc":"Exists?"},
	 * {"var":"$username","type":"string","desc":"Username"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_validate_username_exists', 'function_name', 10, 2 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_validate_username_exists', 'my_validate_username_exists', 10, 2 );
	 * function my_account_pre_updating_profile( $exists, $username ) {
	 *     // your code here
	 *     return $exists;
	 * }
	 * ?>
	 */
	$exists = apply_filters('wams_validate_username_exists', $exists, $username);

	if ($exists) {
		echo 1;
	} else {
		echo 0;
	}

	die();
}
add_action('wp_ajax_nopriv_wams_check_username_exists', 'wams_check_username_exists');
add_action('wp_ajax_wams_check_username_exists', 'wams_check_username_exists');
