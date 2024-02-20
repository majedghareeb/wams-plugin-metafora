<?php if (!defined('ABSPATH')) exit;


function wams_upgrade_roles2054()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	include 'roles.php';

	update_option('wams_last_version_upgrade', '2.0.54');

	wp_send_json_success(array('message' => __('Roles was upgraded successfully', 'wams')));
}
