<?php if (!defined('ABSPATH')) {
	exit;
}


function wams_upgrade_phone_fields250()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	$forms_query = new \WP_Query;
	$forms       = $forms_query->query(array(
		'post_type'      => 'wams_form',
		'posts_per_page' => -1,
		'fields'         => 'ids',
	));

	foreach ($forms as $form_id) {
		$forms_fields = get_post_meta($form_id, '_wams_custom_fields', true);
		if (!is_array($forms_fields)) {
			continue;
		}

		$need_update = false;
		foreach ($forms_fields as $key => &$field) {
			if (in_array($key, array('phone_number', 'mobile_number'), true)) {
				$field['type'] = 'tel';
				$need_update = true;
			}
		}

		if ($need_update) {
			update_post_meta($form_id, '_wams_custom_fields', $forms_fields);
		}
	}

	// remove cached option with users count, don't create separate AJAX upgrade for that
	delete_option('wams_cached_users_queue');

	// delete temporarily option for fields upgrade
	update_option('wams_last_version_upgrade', '2.5.0');

	wp_send_json_success(array('message' => __('Phone Number and Mobile Number fields have been successfully updated.', 'wams')));
}
