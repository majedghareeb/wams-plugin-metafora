<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Add a force redirect to from $_get
 *
 * @param $args
 */
function wams_browser_url_redirect_to($args)
{
	$url = '';

	if (!empty($_REQUEST['redirect_to'])) {

		$url = esc_url_raw($_REQUEST['redirect_to']);
	} elseif (!empty($args['after_login'])) {

		switch ($args['after_login']) {

			case 'redirect_admin':
				$url = admin_url();
				break;

			case 'redirect_profile':
				$url = wams_user_profile_url();
				break;

			case 'redirect_url':
				$url = $args['redirect_url'];
				break;

			case 'refresh':
				$url = WAMS()->permalinks()->get_current_url();
				break;
		}
	}

	/**
	 * Filters 'redirect_to' URL for WAMS login forms.
	 *
	 * @param {string} $url Redirect URL.
	 *
	 * @return {string} Custom redirect URL.
	 *
	 * @since 1.3.x
	 * @hook wams_browser_url_redirect_to__filter
	 *
	 * @example <caption>Force redirect user after login to account page.</caption>
	 * function my_browser_url_redirect_to__filter( $url ) {
	 *     $url = '{site_url}/account';
	 *     return $url;
	 * }
	 * add_filter( 'wams_browser_url_redirect_to__filter', 'my_browser_url_redirect_to__filter' );
	 */
	$url = apply_filters('wams_browser_url_redirect_to__filter', $url);
	if (!empty($url)) {
		echo '<input type="hidden" name="redirect_to" id="redirect_to" value="' . esc_url($url) . '" />';
	}
}
add_action('wams_after_form_fields', 'wams_browser_url_redirect_to');

/**
 * Add a notice to WAMS Form after submission
 *
 * @param array $args
 */
function wams_add_update_notice($args)
{
	$output  = '';
	$err     = '';
	$success = '';

	// Skip if there are errors while submission.
	if (WAMS()->form()->errors) {
		return;
	}

	// phpcs:disable WordPress.Security.NonceVerification -- used for echo and already verified here.
	if (!empty($_REQUEST['updated'])) {
		$updated = sanitize_key($_REQUEST['updated']);
		switch ($updated) {
			default:
				/**
				 * Filters a custom success message.
				 *
				 * @since 1.3.x
				 * @since 2.6.4 Added `$args` parameter.
				 * @hook  wams_custom_success_message_handler
				 *
				 * @param {string} $success Success message.
				 * @param {string} $updated Updated key.
				 * @param {array}  $args    WAMS Form shortcode arguments.
				 *
				 * @return {string} Message.
				 *
				 * @example <caption>It adds a custom message for `custom_key_on_profile` updated key.</caption>
				 * function my_custom_success_message( $success, $updated, $args ) {
				 *     if ( 'custom_key_on_profile' === $updated ) {
				 *         $success = 'Some custom message';
				 *     }
				 *     return $success;
				 * }
				 * add_filter( 'wams_custom_success_message_handler', 'my_custom_success_message', 10, 3 );
				 */
				$success = apply_filters('wams_custom_success_message_handler', $success, $updated, $args);
				break;
			case 'account':
				$success = __('Your account was updated successfully.', 'wams');
				break;
			case 'password_changed':
				$success = __('You have successfully changed your password.', 'wams');
				break;
			case 'account_active':
				$success = __('Your account is now active! You can login.', 'wams');
				break;
		}
	}

	if (!empty($_REQUEST['err'])) {
		$request_error = sanitize_key($_REQUEST['err']);
		switch ($request_error) {
			default:
				/**
				 * Filters a custom error message.
				 *
				 * @since 1.3.x
				 * @since 2.6.4 Added `$args` parameter.
				 * @hook  wams_custom_error_message_handler
				 *
				 * @param {string} $error         Error message.
				 * @param {string} $request_error Error data.
				 * @param {array}  $args          WAMS Form shortcode arguments.
				 *
				 * @return {string} Error message.
				 *
				 * @example <caption>It adds a custom error for `custom_key_on_profile` error key.</caption>
				 * function my_custom_error_message( $error, $request_error, $args ) {
				 *     if ( 'custom_key_on_profile' === $request_error ) {
				 *         $error = 'Some custom message';
				 *     }
				 *     return $error;
				 * }
				 * add_filter( 'wams_custom_error_message_handler', 'my_custom_error_message', 10, 3 );
				 */
				$err = apply_filters('wams_custom_error_message_handler', $err, $request_error, $args);
				if (empty($err)) {
					$err = __('An error has been encountered', 'wams');
				}
				break;
			case 'registration_disabled':
				$err = __('Registration is currently disabled', 'wams');
				break;
			case 'blocked_email':
				$err = __('This email address has been blocked.', 'wams');
				break;
			case 'blocked_domain':
				$err = __('We do not accept registrations from that domain.', 'wams');
				break;
			case 'blocked_ip':
				$err = __('Your IP address has been blocked.', 'wams');
				break;
			case 'inactive':
				$err = __('Your account has been disabled.', 'wams');
				break;
			case 'awaiting_admin_review':
				$err = __('Your account has not been approved yet.', 'wams');
				break;
			case 'awaiting_email_confirmation':
				$err = __('Your account is awaiting e-mail verification.', 'wams');
				break;
			case 'rejected':
				$err = __('Your membership request has been rejected.', 'wams');
				break;
			case 'invalid_nonce':
				$err = __('An error has been encountered. Probably page was cached. Please try again.', 'wams');
				break;
		}
	}
	// phpcs:enable WordPress.Security.NonceVerification -- used for echo and already verified here.

	add_filter('wams_late_escaping_allowed_tags', 'wams_form_notices_additional_tags', 10, 2);

	if (!empty($err)) {
		$output .= '<p class="um-notice err"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>' . $err . '</p>';
	}

	if (!empty($success)) {
		$output .= '<p class="um-notice success"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>' . $success . '</p>';
	}

	echo wp_kses($output, WAMS()->get_allowed_html('templates'));

	remove_filter('wams_late_escaping_allowed_tags', 'wams_form_notices_additional_tags');
}
add_action('wams_before_form', 'wams_add_update_notice', 500);

/**
 * Extends allowed tags for displaying WAMS Form notices.
 *
 * @since 2.6.4
 *
 * @param array  $allowed_html
 * @param string $context
 * @return array
 */
function wams_form_notices_additional_tags($allowed_html, $context)
{
	if ('templates' === $context) {
		$allowed_html['i']['onclick'] = true;
	}
	return $allowed_html;
}
