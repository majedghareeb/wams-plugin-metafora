<?php
function wams_upgrade_styles2010()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	include 'styles.php';
	wp_send_json_success(array('message' => __('Styles was upgraded successfully', 'wams')));
}


function wams_upgrade_cache2010()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	WAMS()->user()->remove_cache_all_users();

	update_option('wams_last_version_upgrade', '2.0.10');

	wp_send_json_success(array('message' => __('Users cache was cleared successfully', 'wams')));
}
