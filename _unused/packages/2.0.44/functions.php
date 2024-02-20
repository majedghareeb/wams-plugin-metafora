<?php if (!defined('ABSPATH')) exit;


function wams_upgrade_fields2044()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	include 'metafields.php';

	update_option('wams_last_version_upgrade', '2.0.44');

	wp_send_json_success(array('message' => __('Field was upgraded successfully', 'wams')));
}
