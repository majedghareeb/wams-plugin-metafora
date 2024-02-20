<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Main admin user actions.
 *
 * @param array $actions
 * @param int   $user_id
 *
 * @return array
 */
function wams_admin_user_actions_hook($actions, $user_id)
{
	wams_fetch_user($user_id);

	$role = get_role(WAMS()->roles()->get_priority_user_role(get_current_user_id()));

	$can_edit_users = null !== $role && current_user_can('edit_users') && $role->has_cap('edit_users');
	if ($can_edit_users) {
		$account_status = wams_user('account_status');

		if ('awaiting_admin_review' === $account_status) {
			$actions['wams_approve_membership'] = array('label' => __('Approve Membership', 'wams'));
			$actions['wams_reject_membership']  = array('label' => __('Reject Membership', 'wams'));
		}

		if ('rejected' === $account_status) {
			$actions['wams_approve_membership'] = array('label' => __('Approve Membership', 'wams'));
		}

		if ('approved' === $account_status) {
			$actions['wams_put_as_pending'] = array('label' => __('Put as Pending Review', 'wams'));
		}

		if ('awaiting_email_confirmation' === $account_status) {
			$actions['wams_resend_activation'] = array('label' => __('Resend Activation E-mail', 'wams'));
		}

		if ('inactive' !== $account_status) {
			$actions['wams_deactivate'] = array('label' => __('Deactivate this account', 'wams'));
		}

		if ('inactive' === $account_status) {
			$actions['wams_reenable'] = array('label' => __('Reactivate this account', 'wams'));
		}
	}

	if (WAMS()->roles()->wams_current_user_can('delete', $user_id)) {
		$actions['wams_delete'] = array('label' => __('Delete this user', 'wams'));
	}

	if (current_user_can('manage_options') && !is_super_admin($user_id)) {
		$actions['wams_switch_user'] = array('label' => __('Login as this user', 'wams'));
	}

	return $actions;
}
add_filter('wams_admin_user_actions_hook', 'wams_admin_user_actions_hook', 10, 2);

/**
 * Filter user basename.
 *
 * @param  string $value
 *
 * @return string
 */
function wams_clean_user_basename_filter($value, $raw)
{
	$permalink_base = WAMS()->options()->get('permalink_base');

	$user_query = new WP_User_Query(
		array(
			'meta_query' => array(
				'relation' => 'AND',
				array(
					'key'     => 'wams_user_profile_url_slug_' . $permalink_base,
					'value'   => $raw,
					'compare' => '=',
				),
			),
			'fields'     => array('ID'),
		)
	);

	if ($user_query->total_users > 0) {
		$result = current($user_query->get_results());
		if (isset($result->ID)) {
			$value = get_user_meta($result->ID, 'wams_user_profile_url_slug_' . $permalink_base, true);
		}
	}

	/**
	 * Filters the base user permalink value before cleaning.
	 *
	 * @param {string} $permalink User Profile permalink.
	 *
	 * @return {string} User Profile permalink.
	 *
	 * @since 1.3.x
	 * @hook wams_permalink_base_before_filter
	 *
	 * @example <caption>Change base permalink before cleaning.</caption>
	 * function my_permalink_base_before( $permalink ) {
	 *     // your code here
	 *     return $permalink;
	 * }
	 * add_filter( 'wams_permalink_base_before_filter', 'my_permalink_base_before' );
	 */
	$value       = apply_filters('wams_permalink_base_before_filter', $value);
	$raw_value   = $value;
	$filter_slug = '';

	switch ($permalink_base) {
		case 'name':
			if (!empty($value) && strrpos($value, '_') > -1) {
				$value = str_replace('_', '. ', $value);
			}

			if (!empty($value) && strrpos($value, '_') > -1) {
				$value = str_replace('_', '-', $value);
			}

			if (!empty($value) && strrpos($value, '.') > -1 && strrpos($raw_value, '_') <= -1) {
				$value = str_replace('.', ' ', $value);
			}

			$filter_slug = '_' . $permalink_base;
			break;
		case 'name_dash':
			if (!empty($value) && strrpos($value, '-') > -1) {
				$value = str_replace('-', ' ', $value);
			}

			if (!empty($value) && strrpos($value, '_') > -1) {
				$value = str_replace('_', '-', $value);
			}

			// Checks if last name has a dash
			if (!empty($value) && strrpos($value, '_') > -1) {
				$value = str_replace('_', '-', $value);
			}

			$filter_slug = '_' . $permalink_base;
			break;
		case 'name_plus':
			if (!empty($value) && strrpos($value, '+') > -1) {
				$value = str_replace('+', ' ', $value);
			}

			if (!empty($value) && strrpos($value, '_') > -1) {
				$value = str_replace('_', '+', $value);
			}

			$filter_slug = '_' . $permalink_base;
			break;
		default:
			// Checks if last name has a dash
			if (!empty($value) && strrpos($value, '_') > -1) {
				$value = str_replace('_', '-', $value);
			}
			break;
	}

	/**
	 * Filters the base user permalink value after cleaning.
	 * $filter_slug - can be empty '' or equals 'name', 'name_dash', 'name_plus'
	 *
	 * @param {string} $permalink User Profile permalink.
	 * @param {string} $raw_value The base user permalink value before cleaning.
	 *
	 * @return {string} User Profile permalink.
	 *
	 * @since 1.3.x
	 * @hook wams_permalink_base_after_filter{$filter_slug}
	 *
	 * @example <caption>Change base permalink after cleaning if permalink settings isn't connected with user first or last name.</caption>
	 * function my_permalink_base_after_filter( $permalink, $raw_permalink ) {
	 *     // your code here
	 *     return $permalink;
	 * }
	 * add_filter( 'wams_permalink_base_after_filter', 'my_permalink_base_after_filter', 10, 2 );
	 * @example <caption>Change base permalink after cleaning if permalink settings is a full name.</caption>
	 * function my_permalink_base_after_filter_name( $permalink, $raw_permalink ) {
	 *     // your code here
	 *     return $permalink;
	 * }
	 * add_filter( 'wams_permalink_base_after_filter_name', 'my_permalink_base_after_filter_name', 10, 2 );
	 * @example <caption>Change base permalink after cleaning if permalink settings is a full name connected by dash.</caption>
	 * function my_permalink_base_after_filter_name_dash( $permalink, $raw_permalink ) {
	 *     // your code here
	 *     return $permalink;
	 * }
	 * add_filter( 'wams_permalink_base_after_filter_name_dash', 'my_permalink_base_after_filter_name_dash', 10, 2 );
	 * @example <caption>Change base permalink after cleaning if permalink settings is a full name connected by plus.</caption>
	 * function my_permalink_base_after_filter_name_plus( $permalink, $raw_permalink ) {
	 *     // your code here
	 *     return $permalink;
	 * }
	 * add_filter( 'wams_permalink_base_after_filter_name_plus', 'my_permalink_base_after_filter_name_plus', 10, 2 );
	 */
	return apply_filters("wams_permalink_base_after_filter{$filter_slug}", $value, $raw_value);
}
add_filter('wams_clean_user_basename_filter', 'wams_clean_user_basename_filter', 2, 10);

/**
 * Filter before update profile to force utf8 strings
 *
 * @param  array $changes
 * @param int $user_id
 *
 * @return array
 */
function wams_before_update_profile($changes, $user_id)
{
	// todo check if this option required and maybe there are some WordPress native ways how to make that without custom unused functions. Maybe fully deprecate 'wams_force_utf8_strings' option which doesn't exist in UI.
	if (!WAMS()->options()->get('wams_force_utf8_strings')) {
		return $changes;
	}

	foreach ($changes as $key => $value) {
		$changes[$key] = wams_force_utf8_string($value);
	}

	return $changes;
}
add_filter('wams_before_update_profile', 'wams_before_update_profile', 10, 2);
