<?php if (!defined('ABSPATH')) exit;


function wams_upgrade_metadata210beta1()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	include 'metadata.php';

	wp_send_json_success(array('message' => __('Usermeta was upgraded successfully', 'wams')));
}


function wams_upgrade_memberdir210beta1()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	include 'member-directory.php';

	update_option('wams_last_version_upgrade', '2.1.0-beta1');

	wp_send_json_success(array('message' => __('Member directories were upgraded successfully', 'wams')));
}
