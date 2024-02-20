<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Profile Access
 *
 * @param int $user_id
 */
function wams_access_profile($user_id)
{

	if (!wams_is_myprofile() && wams_is_core_page('user') && !current_user_can('edit_users')) {

		wams_fetch_user($user_id);

		$account_status = wams_user('account_status');
		if (!in_array($account_status, array('approved'))) {
			wams_redirect_home();
		}

		wams_reset_user();
	}
}
add_action('wams_access_profile', 'wams_access_profile');
