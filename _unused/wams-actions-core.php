<?php if (!defined('ABSPATH')) {
	exit;
}

/**
 * Processes the requests of WAMS actions
 *
 */
function wams_action_request_process()
{
	if (is_admin()) {
		return;
	}

	if (!is_user_logged_in()) {
		return;
	}

	if (!isset($_REQUEST['wams_action'])) {
		return;
	}

	$action = sanitize_key($_REQUEST['wams_action']);

	$uid = 0;
	if (isset($_REQUEST['uid'])) {
		$uid = absint($_REQUEST['uid']);
	}

	if (!empty($uid) && !WAMS()->user()->user_exists_by_id($uid)) {
		return;
	}

	if (!empty($uid) && is_super_admin($uid)) {
		wp_die(esc_html__('Super administrators can not be modified.', 'wams'));
	}

	$role           = get_role(WAMS()->roles()->get_priority_user_role(get_current_user_id()));
	$can_edit_users = current_user_can('edit_users') && $role->has_cap('edit_users');

	switch ($action) {
		default:
			/**
			 * WAMS hook
			 *
			 * @type action
			 * @title wams_action_user_request_hook
			 * @description Integration for user actions
			 * @input_vars
			 * [{"var":"$action","type":"string","desc":"Action for user"},
			 * {"var":"$user_id","type":"int","desc":"User ID"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'wams_action_user_request_hook', 'function_name', 10, 2 );
			 * @example
			 * <?php
			 * add_action( 'wams_action_user_request_hook', 'my_action_user_request', 10, 2 );
			 * function my_action_user_request( $action, $user_id ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action('wams_action_user_request_hook', $action, $uid);
			break;

		case 'edit':
			WAMS()->fields()->editing = true;
			if (!wams_is_myprofile()) {
				if (!WAMS()->roles()->wams_current_user_can('edit', wams_profile_id())) {
					exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
				}
			} else {
				if (!wams_can_edit_my_profile()) {
					$url = wams_edit_my_profile_cancel_uri();
					exit(wp_redirect($url));
				}
			}
			break;

		case 'wams_switch_user':
			if (!current_user_can('manage_options')) {
				return;
			}
			WAMS()->user()->auto_login($uid);
			exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
			break;

		case 'wams_reject_membership':
			if (!$can_edit_users) {
				wp_die(esc_html__('You do not have permission to make this action.', 'wams'));
			}

			wams_fetch_user($uid);
			WAMS()->user()->reject();
			exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
			break;

		case 'wams_approve_membership':
		case 'wams_reenable':
			if (!$can_edit_users) {
				wp_die(esc_html__('You do not have permission to make this action.', 'wams'));
			}

			add_filter('wams_template_tags_patterns_hook', array(WAMS()->password(), 'add_placeholder'), 10, 1);
			add_filter('wams_template_tags_replaces_hook', array(WAMS()->password(), 'add_replace_placeholder'), 10, 1);

			wams_fetch_user($uid);
			WAMS()->user()->approve();
			exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
			break;

		case 'wams_put_as_pending':
			if (!$can_edit_users) {
				wp_die(esc_html__('You do not have permission to make this action.', 'wams'));
			}

			wams_fetch_user($uid);
			WAMS()->user()->pending();
			exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
			break;

		case 'wams_resend_activation':
			if (!$can_edit_users) {
				wp_die(esc_html__('You do not have permission to make this action.', 'wams'));
			}

			add_filter('wams_template_tags_patterns_hook', array(WAMS()->user(), 'add_activation_placeholder'), 10, 1);
			add_filter('wams_template_tags_replaces_hook', array(WAMS()->user(), 'add_activation_replace_placeholder'), 10, 1);

			wams_fetch_user($uid);
			WAMS()->user()->email_pending();
			exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
			break;

		case 'wams_deactivate':
			if (!$can_edit_users) {
				wp_die(esc_html__('You do not have permission to make this action.', 'wams'));
			}

			wams_fetch_user($uid);
			WAMS()->user()->deactivate();
			exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
			break;

		case 'wams_delete':
			if (!WAMS()->roles()->wams_current_user_can('delete', $uid)) {
				wp_die(esc_html__('You do not have permission to delete this user.', 'wams'));
			}

			wams_fetch_user($uid);
			WAMS()->user()->delete();
			exit(wp_redirect(WAMS()->permalinks()->get_current_url(true)));
			break;
	}
}
add_action('template_redirect', 'wams_action_request_process', 10000);
