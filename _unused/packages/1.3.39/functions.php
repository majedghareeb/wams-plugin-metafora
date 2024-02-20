<?php
function wams_upgrade_usermetaquery1339()
{
	WAMS()->admin()->check_ajax_nonce();

	include 'usermeta_query.php';

	update_option('wams_last_version_upgrade', '1.3.39');

	wp_send_json_success(array('message' => 'Usermeta was upgraded successfully'));
}
