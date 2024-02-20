<?php
function wams_upgrade_tempfolder2024()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	WAMS()->files()->remove_dir(WAMS()->files()->upload_temp);

	update_option('wams_last_version_upgrade', '2.0.24');

	wp_send_json_success(array('message' => __('Temporary dir was purged successfully', 'wams')));
}
