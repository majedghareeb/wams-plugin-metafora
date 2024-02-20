<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Disables first and last name fields in account page.
 *
 * @param  array $fields
 * @return array
 */
function wams_account_disable_name_fields($fields)
{
	if (!WAMS()->options()->get('account_name_disable')) {
		return $fields;
	}

	if (wams_is_core_page('account')) {
		$fields['disabled'] = ' disabled="disabled" ';
	}

	return $fields;
}
add_filter('wams_get_field__first_name', 'wams_account_disable_name_fields');
add_filter('wams_get_field__last_name', 'wams_account_disable_name_fields');

/**
 * Sanitize inputs on Account update.
 *
 * @param $data
 *
 * @return mixed
 */
function wams_account_sanitize_data($data)
{
	foreach ($data as $key => $value) {
		if (is_array($value)) {
			$data[$key] = array_filter(
				$value,
				function ($var) {
					$var = trim(esc_html(wp_strip_all_tags($var)));
					return $var;
				}
			);
		} else {
			$data[$key] = trim(esc_html(wp_strip_all_tags($value)));
		}
	}

	return $data;
}
add_filter('wams_account_pre_updating_profile_array', 'wams_account_sanitize_data', 10, 1);

/**
 * Fix for the account field "Avoid indexing my profile by search engines".
 *
 * @since  2.1.16
 * @param  mixed|int $value
 * @return int
 */
function wams_account_profile_noindex_value($value)
{
	if (!is_numeric($value)) {
		$value = (int) get_user_meta(wams_profile_id(), 'profile_noindex', true);
	}
	return $value;
}
add_filter('wams_profile_profile_noindex_empty__filter', 'wams_account_profile_noindex_value');
