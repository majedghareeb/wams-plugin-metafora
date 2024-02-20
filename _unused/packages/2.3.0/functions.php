<?php if (!defined('ABSPATH')) exit;


function wams_upgrade_skypeid_fields230()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	$forms_query = new WP_Query;
	$forms = $forms_query->query(array(
		'post_type'         => 'wams_form',
		'posts_per_page'    => -1,
		'fields'            => 'ids'
	));

	$fields_for_upgrade = array();

	foreach ($forms as $form_id) {
		$forms_fields = get_post_meta($form_id, '_wams_custom_fields', true);

		$changed = false;
		foreach ($forms_fields as $key => &$field) {
			if (isset($field['validate']) && 'skype' === $field['validate']) {
				if (isset($field['type']) && 'url' === $field['type']) {
					$field['type'] = 'text';
					$changed       = true;

					$fields_for_upgrade[] = $field['metakey'];
				}
			}
		}

		if ($changed) {
			update_post_meta($form_id, '_wams_custom_fields', $forms_fields);
		}
	}

	$changed       = false;
	$custom_fields = get_option('wams_fields', array());
	foreach ($custom_fields as &$custom_field) {
		if (isset($custom_field['validate']) && 'skype' === $custom_field['validate']) {
			if (isset($custom_field['type']) && 'url' === $custom_field['type']) {
				$custom_field['type'] = 'text';
				$changed              = true;

				$fields_for_upgrade[] = $custom_field['metakey'];
			}
		}
	}
	if ($changed) {
		update_option('wams_fields', $custom_fields);
	}

	$fields_for_upgrade = array_unique($fields_for_upgrade);

	if (!empty($fields_for_upgrade)) {
		update_option('wams_upgrade_230_skype_fields_for_upgrade', $fields_for_upgrade);
		wp_send_json_success(array('message' => __('SkypeID fields have been updated successfully', 'wams'), 'count' => count($fields_for_upgrade)));
	} else {
		wp_send_json_success(array('message' => __('Database has been updated successfully', 'wams'), 'count' => 0));
	}
}


function wams_upgrade_usermeta_count230()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	$fields_for_upgrade = get_option('wams_upgrade_230_skype_fields_for_upgrade', array());

	if (!empty($fields_for_upgrade)) {
		global $wpdb;
		$count = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->usermeta} WHERE meta_key IN( '" . implode("','", $fields_for_upgrade) . "' )");
	} else {
		$count = 0;
	}

	wp_send_json_success(array('count' => $count));
}


function wams_upgrade_usermeta_part230()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	if (empty($_POST['page'])) {
		wp_send_json_error(__('Wrong data', 'wams'));
	}

	$fields_for_upgrade = get_option('wams_upgrade_230_skype_fields_for_upgrade', array());
	if (empty($fields_for_upgrade)) {
		wp_send_json_success(array('message' => __('Database has been updated successfully', 'wams')));
	}

	$per_page = 100;

	// avoid 'https://', 'http://' at the start of the Skype field is there is nickname but not https://join.skype.com/
	// change only links with nickname skip https://join.skype.com/
	global $wpdb;
	$usermetas = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT user_id,
				  meta_key,
				  meta_value
			FROM {$wpdb->usermeta}
			WHERE meta_key IN( '" . implode("','", $fields_for_upgrade) . "' )
			LIMIT %d, %d",
			(absint($_POST['page']) - 1) * $per_page,
			$per_page
		),
		ARRAY_A
	);

	if (!empty($usermetas)) {
		foreach ($usermetas as $usermeta) {
			if (false !== strstr($usermeta['meta_value'], 'https://') || false !== strstr($usermeta['meta_value'], 'http://')) {
				if (false === strstr($usermeta['meta_value'], 'join.skype.com/')) {
					$usermeta['meta_value'] = str_replace(array('https://', 'http://'), '', $usermeta['meta_value']);
					update_user_meta($usermeta['user_id'], $usermeta['meta_key'], $usermeta['meta_value']);

					delete_option("wams_cache_userdata_{$usermeta['user_id']}");
				}
			}
		}
	}

	$from = (absint($_POST['page']) * $per_page) - $per_page + 1;
	$to   = absint($_POST['page']) * $per_page;

	// translators: %1$s is a from; %2$s is a to.
	wp_send_json_success(array('message' => sprintf(__('Metadata from %1$s to %2$s row were upgraded successfully...', 'wams'), $from, $to)));
}


function wams_upgrade_reset_password230()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	$require_strongpass = WAMS()->options()->get('account_require_strongpass') || WAMS()->options()->get('reset_require_strongpass');

	WAMS()->options()->update('require_strongpass', $require_strongpass);
	WAMS()->options()->remove('account_require_strongpass');
	WAMS()->options()->remove('reset_require_strongpass');

	// delete temporarily option for fields upgrade
	delete_option('wams_upgrade_230_skype_fields_for_upgrade');
	update_option('wams_last_version_upgrade', '2.3.0');

	wp_send_json_success(array('message' => __('Reset Password options have been updated successfully.', 'wams')));
}
