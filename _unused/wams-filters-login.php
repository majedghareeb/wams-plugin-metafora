<?php if (!defined('ABSPATH')) {
	exit;
}


/**
 * Filter to customize errors
 *
 * @param $message
 *
 * @return string
 */
function wams_custom_wp_err_messages($message)
{

	if (!empty($_REQUEST['err'])) {
		switch (sanitize_key($_REQUEST['err'])) {
			case 'blocked_email':
				$err = __('This email address has been blocked.', 'wams');
				break;
			case 'blocked_ip':
				$err = __('Your IP address has been blocked.', 'wams');
				break;
		}
	}

	if (isset($err)) {
		$message = '<div class="login" id="login_error">' . $err . '</div>';
	}

	return $message;
}
add_filter('login_message', 'wams_custom_wp_err_messages');


/**
 * Check for blocked IPs or Email on wp-login.php form
 *
 * @param $user
 * @param $username
 * @param $password
 *
 * @return mixed
 */
function wams_wp_form_errors_hook_ip_test($user, $username, $password)
{
	if (!empty($username)) {
		/** This action is documented in includes/core/um-actions-form.php */
		do_action('wams_submit_form_errors_hook__blockedips', array(), null);
		/** This action is documented in includes/core/um-actions-form.php */
		do_action('wams_submit_form_errors_hook__blockedemails', array('username' => $username), null);
	}

	return $user;
}
add_filter('authenticate', 'wams_wp_form_errors_hook_ip_test', 10, 3);


/**
 * Login checks through the WordPress admin login.
 *
 * @param $user
 * @param $username
 * @param $password
 *
 * @return WP_Error|WP_User
 */
function wams_wp_form_errors_hook_logincheck($user, $username, $password)
{

	if (isset($user->ID)) {

		wams_fetch_user($user->ID);
		$status = wams_user('account_status');

		switch ($status) {
			case 'inactive':
				return new WP_Error($status, __('Your account has been disabled.', 'wams'));
				break;
			case 'awaiting_admin_review':
				return new WP_Error($status, __('Your account has not been approved yet.', 'wams'));
				break;
			case 'awaiting_email_confirmation':
				return new WP_Error($status, __('Your account is awaiting e-mail verification.', 'wams'));
				break;
			case 'rejected':
				return new WP_Error($status, __('Your membership request has been rejected.', 'wams'));
				break;
		}
	}

	return $user;
}
add_filter('authenticate', 'wams_wp_form_errors_hook_logincheck', 50, 3);


/**
 * Change lost password url in WAMS Login form
 * @param  string $lostpassword_url
 * @return string
 */
function wams_lostpassword_url($lostpassword_url)
{

	if (wams_is_core_page('login')) {
		return wams_get_core_page('password-reset');
	}

	return $lostpassword_url;
}
add_filter('lostpassword_url', 'wams_lostpassword_url', 10, 1);
