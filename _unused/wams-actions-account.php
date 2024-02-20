<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Validate for errors in account form
 *
 * @param array $args
 */
function wams_submit_account_errors_hook($args)
{
	global $current_user;

	if (!isset($args['_wams_account']) && !isset($args['_wams_account_tab'])) {
		return;
	}

	$tab = sanitize_key($args['_wams_account_tab']);

	if (!wp_verify_nonce($args['wams_account_nonce_' . $tab], 'wams_update_account_' . $tab)) {
		WAMS()->form()->add_error('wams_account_security', __('Are you hacking? Please try again!', 'wams'));
	}

	switch ($tab) {
		case 'delete': {
				// delete account
				if (WAMS()->account()->current_password_is_required('delete')) {
					if (strlen(trim($args['single_user_password'])) === 0) {
						WAMS()->form()->add_error('single_user_password', __('You must enter your password', 'wams'));
					} else {
						if (!wp_check_password(trim($args['single_user_password']), $current_user->data->user_pass, $current_user->data->ID)) {
							WAMS()->form()->add_error('single_user_password', __('This is not your password', 'wams'));
						}
					}
				}

				WAMS()->account()->current_tab = 'delete';

				break;
			}

		case 'password': {

				// change password
				WAMS()->account()->current_tab = 'password';

				if (isset($args['user_password'])) {
					$args['user_password'] = trim($args['user_password']);
				}

				if (isset($args['confirm_user_password'])) {
					$args['confirm_user_password'] = trim($args['confirm_user_password']);
				}

				if (empty($args['user_password'])) {
					WAMS()->form()->add_error('user_password', __('Password is required', 'wams'));
					return;
				}

				if (empty($args['confirm_user_password'])) {
					WAMS()->form()->add_error('user_password', __('Password confirmation is required', 'wams'));
					return;
				}

				// Check for "\" in password.
				if (false !== strpos(wp_unslash($args['user_password']), '\\')) {
					WAMS()->form()->add_error('user_password', __('Passwords may not contain the character "\\".', 'wams'));
					return;
				}

				if (!empty($args['user_password']) && !empty($args['confirm_user_password'])) {

					if (WAMS()->account()->current_password_is_required('password')) {
						if (empty($args['current_user_password'])) {
							WAMS()->form()->add_error('current_user_password', __('This is not your password', 'wams'));
							return;
						} else {
							if (!wp_check_password($args['current_user_password'], $current_user->data->user_pass, $current_user->data->ID)) {
								WAMS()->form()->add_error('current_user_password', __('This is not your password', 'wams'));
								return;
							}
						}
					}

					if ($args['user_password'] && $args['user_password'] !== $args['confirm_user_password']) {
						WAMS()->form()->add_error('user_password', __('Your new password does not match', 'wams'));
						return;
					}

					if (WAMS()->options()->get('require_strongpass')) {
						$min_length = WAMS()->options()->get('password_min_chars');
						$min_length = !empty($min_length) ? $min_length : 8;
						$max_length = WAMS()->options()->get('password_max_chars');
						$max_length = !empty($max_length) ? $max_length : 30;

						if (is_user_logged_in()) {
							wams_fetch_user(get_current_user_id());
						}

						$user_login = wams_user('user_login');
						$user_email = wams_user('user_email');

						if (mb_strlen(wp_unslash($args['user_password'])) < $min_length) {
							WAMS()->form()->add_error('user_password', sprintf(__('Your password must contain at least %d characters', 'wams'), $min_length));
						}

						if (mb_strlen(wp_unslash($args['user_password'])) > $max_length) {
							WAMS()->form()->add_error('user_password', sprintf(__('Your password must contain less than %d characters', 'wams'), $max_length));
						}

						if (strpos(strtolower($user_login), strtolower($args['user_password'])) > -1) {
							WAMS()->form()->add_error('user_password', __('Your password cannot contain the part of your username', 'wams'));
						}

						if (strpos(strtolower($user_email), strtolower($args['user_password'])) > -1) {
							WAMS()->form()->add_error('user_password', __('Your password cannot contain the part of your email address', 'wams'));
						}

						if (!WAMS()->validation()->strong_pass($args['user_password'])) {
							WAMS()->form()->add_error('user_password', __('Your password must contain at least one lowercase letter, one capital letter and one number', 'wams'));
						}
					}
				}

				break;
			}

		case 'account':
		case 'general': {
				// errors on general tab
				$account_name_require = WAMS()->options()->get('account_name_require');

				if (isset($args['user_login'])) {
					$args['user_login'] = sanitize_user($args['user_login']);
				}
				if (isset($args['first_name'])) {
					$args['first_name'] = sanitize_text_field($args['first_name']);
				}
				if (isset($args['last_name'])) {
					$args['last_name'] = sanitize_text_field($args['last_name']);
				}
				if (isset($args['user_email'])) {
					$args['user_email'] = sanitize_email($args['user_email']);
				}
				if (isset($args['single_user_password'])) {
					$args['single_user_password'] = trim($args['single_user_password']);
				}

				if (isset($args['first_name']) && (strlen(trim($args['first_name'])) === 0 && $account_name_require)) {
					WAMS()->form()->add_error('first_name', __('You must provide your first name', 'wams'));
				}

				if (isset($args['last_name']) && (strlen(trim($args['last_name'])) === 0 && $account_name_require)) {
					WAMS()->form()->add_error('last_name', __('You must provide your last name', 'wams'));
				}

				if (isset($args['user_email'])) {

					if (strlen(trim($args['user_email'])) === 0) {
						WAMS()->form()->add_error('user_email', __('You must provide your e-mail', 'wams'));
					}

					if (!is_email($args['user_email'])) {
						WAMS()->form()->add_error('user_email', __('Please provide a valid e-mail', 'wams'));
					}

					if (email_exists($args['user_email']) && email_exists($args['user_email']) !== get_current_user_id()) {
						WAMS()->form()->add_error('user_email', __('Please provide a valid e-mail', 'wams'));
					}
				}

				// check account password
				if (WAMS()->account()->current_password_is_required('general')) {
					if (strlen($args['single_user_password']) === 0) {
						WAMS()->form()->add_error('single_user_password', __('You must enter your password', 'wams'));
					} else {
						if (!wp_check_password($args['single_user_password'], $current_user->data->user_pass, $current_user->data->ID)) {
							WAMS()->form()->add_error('single_user_password', __('This is not your password', 'wams'));
						}
					}
				}

				break;
			}

		default:
			/**
			 * WAMS hook
			 *
			 * @type action
			 * @title wams_submit_account_{$tab}_tab_errors_hook
			 * @description On submit account current $tab validation
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'wams_submit_account_{$tab}_tab_errors_hook', 'function_name', 10 );
			 * @example
			 * <?php
			 * add_action( 'wams_submit_account_{$tab}_tab_errors_hook', 'my_submit_account_tab_errors', 10 );
			 * function my_submit_account_tab_errors() {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action('wams_submit_account_' . $tab . '_tab_errors_hook');
			break;
	}

	WAMS()->account()->current_tab = $tab;
}
add_action('wams_submit_account_errors_hook', 'wams_submit_account_errors_hook');


/**
 * Submit account page changes
 *
 * @param $args
 */
function wams_submit_account_details($args)
{
	$tab = (get_query_var('wams_tab')) ? get_query_var('wams_tab') : 'general';

	$current_tab = isset($args['_wams_account_tab']) ? sanitize_key($args['_wams_account_tab']) : '';

	$user_id = wams_user('ID');

	//change password account's tab
	if ('password' === $current_tab && $args['user_password'] && $args['confirm_user_password']) {
		$changes['user_pass'] = trim($args['user_password']);
		$args['user_id']      = get_current_user_id();

		WAMS()->user()->password_changed();

		add_filter('send_password_change_email', '__return_false');

		//clear all sessions with old passwords
		$user = WP_Session_Tokens::get_instance($args['user_id']);
		$user->destroy_all();

		wp_set_password($changes['user_pass'], $args['user_id']);

		do_action('wams_before_signon_after_account_changes', $args);

		wp_signon(
			array(
				'user_login'    => wams_user('user_login'),
				'user_password' => $changes['user_pass'],
			)
		);
	}

	// delete account
	if ('delete' === $current_tab) {
		if (current_user_can('delete_users') || wams_user('can_delete_profile')) {
			WAMS()->user()->delete();

			if (wams_user('after_delete') && wams_user('after_delete') === 'redirect_home') {
				wams_redirect_home();
			} elseif (wams_user('delete_redirect_url')) {
				/**
				 * WAMS hook
				 *
				 * @type filter
				 * @title wams_delete_account_redirect_url
				 * @description Change redirect URL after delete account
				 * @input_vars
				 * [{"var":"$url","type":"string","desc":"Redirect URL"},
				 * {"var":"$id","type":"int","desc":"User ID"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage
				 * <?php add_filter( 'wams_delete_account_redirect_url', 'function_name', 10, 2 ); ?>
				 * @example
				 * <?php
				 * add_filter( 'wams_delete_account_redirect_url', 'my_delete_account_redirect_url', 10, 2 );
				 * function my_delete_account_redirect_url( $url, $id ) {
				 *     // your code here
				 *     return $url;
				 * }
				 * ?>
				 */
				$redirect_url = apply_filters('wams_delete_account_redirect_url', wams_user('delete_redirect_url'), $user_id);
				wams_safe_redirect($redirect_url);
			} else {
				wams_redirect_home();
			}
		}
	}

	$arr_fields = array();
	if (WAMS()->account()->is_secure_enabled()) {
		$account_fields = get_user_meta($user_id, 'wams_account_secure_fields', true);

		/**
		 * WAMS hook
		 *
		 * @type filter
		 * @title wams_secure_account_fields
		 * @description Change secure account fields
		 * @input_vars
		 * [{"var":"$fields","type":"array","desc":"Secure account fields"},
		 * {"var":"$user_id","type":"int","desc":"User ID"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'wams_secure_account_fields', 'function_name', 10, 2 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'wams_secure_account_fields', 'my_secure_account_fields', 10, 2 );
		 * function my_secure_account_fields( $fields, $user_id ) {
		 *     // your code here
		 *     return $fields;
		 * }
		 * ?>
		 */
		$secure_fields = apply_filters('wams_secure_account_fields', $account_fields, $user_id);

		if (isset($secure_fields[$current_tab]) && is_array($secure_fields[$current_tab])) {
			$arr_fields = array_merge($arr_fields, $secure_fields[$current_tab]);
		}
	}

	$changes = array();
	foreach ($args as $k => $v) {
		if (!in_array($k, $arr_fields, true)) {
			continue;
		}

		if ('single_user_password' === $k || 'user_login' === $k) {
			continue;
		} elseif ('first_name' === $k || 'last_name' === $k || 'user_password' === $k) {
			$v = sanitize_text_field($v);
		} elseif ('user_email' === $k) {
			$v = sanitize_email($v);
		} elseif ('hide_in_members' === $k) {
			$v = array_map('sanitize_text_field', $v);
		}

		$changes[$k] = $v;
	}

	if (isset($changes['hide_in_members'])) {
		if (WAMS()->member_directory()->get_hide_in_members_default()) {
			if (__('Yes', 'wams') === $changes['hide_in_members'] || 'Yes' === $changes['hide_in_members'] || array_intersect(array('Yes', __('Yes', 'wams')), $changes['hide_in_members'])) {
				delete_user_meta($user_id, 'hide_in_members');
				unset($changes['hide_in_members']);
			}
		} else {
			if (__('No', 'wams') === $changes['hide_in_members'] || 'No' === $changes['hide_in_members'] || array_intersect(array('No', __('No', 'wams')), $changes['hide_in_members'])) {
				delete_user_meta($user_id, 'hide_in_members');
				unset($changes['hide_in_members']);
			}
		}
	}

	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_account_pre_updating_profile_array
	 * @description Change update profile data before saving
	 * @input_vars
	 * [{"var":"$changes","type":"array","desc":"Profile changes array"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_account_pre_updating_profile_array', 'function_name', 10, 1 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_account_pre_updating_profile_array', 'my_account_pre_updating_profile', 10, 1 );
	 * function my_account_pre_updating_profile( $changes ) {
	 *     // your code here
	 *     return $changes;
	 * }
	 * ?>
	 */
	$changes = apply_filters('wams_account_pre_updating_profile_array', $changes);

	/**
	 * WAMS hook
	 *
	 * @type action
	 * @title wams_account_pre_update_profile
	 * @description Fired on account page, just before updating profile
	 * @input_vars
	 * [{"var":"$changes","type":"array","desc":"Submitted data"},
	 * {"var":"$user_id","type":"int","desc":"User ID"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage add_action( 'wams_account_pre_update_profile', 'function_name', 10, 2 );
	 * @example
	 * <?php
	 * add_action( 'wams_account_pre_update_profile', 'my_account_pre_update_profile', 10, 2 );
	 * function my_account_pre_update_profile( $changes, $user_id ) {
	 *     // your code here
	 * }
	 * ?>
	 */
	do_action('wams_account_pre_update_profile', $changes, $user_id);

	if (isset($changes['first_name']) || isset($changes['last_name']) || isset($changes['nickname'])) {
		$user = get_userdata($user_id);
		if (!empty($user) && !is_wp_error($user)) {
			WAMS()->user()->previous_data['display_name'] = $user->display_name;

			if (isset($changes['first_name'])) {
				WAMS()->user()->previous_data['first_name'] = $user->first_name;
			}
			if (isset($changes['last_name'])) {
				WAMS()->user()->previous_data['last_name'] = $user->last_name;
			}
			if (isset($changes['nickname'])) {
				WAMS()->user()->previous_data['nickname'] = $user->nickname;
			}
		}
	}

	WAMS()->user()->update_profile($changes, 'account');

	if (WAMS()->account()->is_secure_enabled()) {
		update_user_meta($user_id, 'wams_account_secure_fields', array());
	}

	/**
	 * WAMS hook
	 *
	 * @type action
	 * @title wams_post_account_update
	 * @description Fired on account page, after updating profile
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage add_action( 'wams_post_account_update', 'function_name', 10 );
	 * @example
	 * <?php
	 * add_action( 'wams_post_account_update', 'my_post_account_update', 10 );
	 * function my_account_pre_update_profile() {
	 *     // your code here
	 * }
	 * ?>
	 */
	do_action('wams_post_account_update');
	/**
	 * WAMS hook
	 *
	 * @type action
	 * @title wams_after_user_account_updated
	 * @description Fired on account page, after updating profile
	 * @input_vars
	 * [{"var":"$user_id","type":"int","desc":"User ID"},
	 * {"var":"$changes","type":"array","desc":"Submitted data"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage add_action( 'wams_after_user_account_updated', 'function_name', 10, 2 );
	 * @example
	 * <?php
	 * add_action( 'wams_after_user_account_updated', 'my_after_user_account_updated', 10, 2 );
	 * function my_after_user_account_updated( $user_id, $changes ) {
	 *     // your code here
	 * }
	 * ?>
	 */
	do_action('wams_after_user_account_updated', $user_id, $changes);

	$url = '';
	if (wams_is_core_page('account')) {

		$url = WAMS()->account()->tab_link($tab);

		$url = add_query_arg('updated', 'account', $url);

		if (function_exists('icl_get_current_language')) {
			if (icl_get_current_language() != icl_get_default_language()) {
				$url = WAMS()->permalinks()->get_current_url(true);
				$url = add_query_arg('updated', 'account', $url);

				wams_js_redirect($url);
			}
		}
	}

	wams_js_redirect($url);
}
add_action('wams_submit_account_details', 'wams_submit_account_details');


/**
 * Hidden inputs for account form
 *
 * @param $args
 */
function wams_account_page_hidden_fields($args)
{
?>

	<input type="hidden" name="_wams_account" id="_wams_account" value="1" />
	<input type="hidden" name="_wams_account_tab" id="_wams_account_tab" value="<?php echo esc_attr(WAMS()->account()->current_tab); ?>" />

	<?php
}
add_action('wams_account_page_hidden_fields', 'wams_account_page_hidden_fields');


/**
 * Before delete account tab content
 */
function wams_before_account_delete()
{
	if (WAMS()->account()->current_password_is_required('delete')) {
		$text = WAMS()->options()->get('delete_account_text');
	} else {
		$text = WAMS()->options()->get('delete_account_no_pass_required_text');
	}

	printf(__('%s', 'wams'), wpautop(htmlspecialchars($text)));
}
add_action('wams_before_account_delete', 'wams_before_account_delete');

/**
 * Before notifications account tab content.
 *
 * @param array $args
 *
 * @throws Exception
 */
function wams_before_account_notifications($args = array())
{
	$output = WAMS()->account()->get_tab_fields('notifications', $args);
	if (substr_count($output, '_enable_new_')) {
	?>
		<p><?php esc_html_e('Select what email notifications you want to receive', 'wams'); ?></p>
	<?php
	}
}
add_action('wams_before_account_notifications', 'wams_before_account_notifications');

/**
 * Update Profile URL, display name, full name.
 *
 * @version 2.2.5
 *
 * @param   int   $user_id  The user ID.
 * @param   array $changes  An array of fields values.
 */
function wams_after_user_account_updated_permalink($user_id, $changes)
{
	if (isset($changes['first_name']) || isset($changes['last_name'])) {
		/** This action is documented in wams/includes/core/um-actions-register.php */
		do_action('wams_update_profile_full_name', $user_id, $changes);
	}
}
add_action('wams_after_user_account_updated', 'wams_after_user_account_updated_permalink', 10, 2);


/**
 * Update Account Email Notification
 *
 * @param $user_id
 * @param $changed
 */
function wams_account_updated_notification($user_id, $changed)
{
	wams_fetch_user($user_id);
	WAMS()->mail()->send(wams_user('user_email'), 'changedaccount_email');
}
add_action('wams_after_user_account_updated', 'wams_account_updated_notification', 20, 2);


/**
 * Disable WP native email notification when change email on user account
 *
 * @param $user_id
 * @param $changed
 */
function wams_disable_native_email_notificatiion($changed, $user_id)
{
	add_filter('send_email_change_email', '__return_false');
}
add_action('wams_account_pre_update_profile', 'wams_disable_native_email_notificatiion', 10, 2);


/**
 * Add export and erase user's data in privacy tab
 *
 * @param $args
 */
add_action('wams_after_account_privacy', 'wams_after_account_privacy');
function wams_after_account_privacy($args)
{
	global $wpdb;
	$user_id = get_current_user_id();
	?>

	<div class="um-field um-field-export_data">
		<div class="um-field-label">
			<label>
				<?php esc_html_e('Download your data', 'wams'); ?>
			</label>
			<span class="um-tip um-tip-<?php echo is_rtl() ? 'e' : 'w'; ?>" title="<?php esc_attr_e('You can request a file with the information that we believe is most relevant and useful to you.', 'wams'); ?>">
				<i class="um-icon-help-circled"></i>
			</span>
			<div class="um-clear"></div>
		</div>
		<?php
		$completed = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID
				FROM $wpdb->posts
				WHERE post_author = %d AND
					  post_type = 'user_request' AND
					  post_name = 'export_personal_data' AND
					  post_status = 'request-completed'
				ORDER BY ID DESC
				LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if (!empty($completed)) {

			$exports_url = wp_privacy_exports_url();

			echo '<p>' . esc_html__('You could download your previous data:', 'wams') . '</p>';
			echo '<a href="' . esc_attr($exports_url . get_post_meta($completed['ID'], '_export_file_name', true)) . '">' . esc_html__('Download Personal Data', 'wams') . '</a>';
			echo '<p>' . esc_html__('You could send a new request for an export of personal your data.', 'wams') . '</p>';
		}

		$pending = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, post_status
				FROM $wpdb->posts
				WHERE post_author = %d AND
					  post_type = 'user_request' AND
					  post_name = 'export_personal_data' AND
					  post_status != 'request-completed'
				ORDER BY ID DESC
				LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if (!empty($pending) && 'request-pending' === $pending['post_status']) {
			echo '<p>' . esc_html__('A confirmation email has been sent to your email. Click the link within the email to confirm your export request.', 'wams') . '</p>';
		} elseif (!empty($pending) && 'request-confirmed' === $pending['post_status']) {
			echo '<p>' . esc_html__('The administrator has not yet approved downloading the data. Please expect an email with a link to your data.', 'wams') . '</p>';
		} else {
			if (WAMS()->account()->current_password_is_required('privacy_download_data')) {
		?>
				<label name="um-export-data">
					<?php esc_html_e('Enter your current password to confirm a new export of your personal data.', 'wams'); ?>
				</label>
				<div class="um-field-area">
					<?php if (WAMS()->options()->get('toggle_password')) { ?>
						<div class="um-field-area-password">
							<input id="um-export-data" type="password" placeholder="<?php esc_attr_e('Password', 'wams'); ?>">
							<span class="um-toggle-password"><i class="um-icon-eye"></i></span>
						</div>
					<?php } else { ?>
						<input id="um-export-data" type="password" placeholder="<?php esc_attr_e('Password', 'wams'); ?>">
					<?php } ?>
					<div class="um-field-error um-export-data">
						<span class="um-field-arrow"><i class="um-faicon-caret-up"></i></span><?php esc_html_e('You must enter a password', 'wams'); ?>
					</div>
					<div class="um-field-area-response um-export-data"></div>
				</div>

			<?php } else { ?>

				<label name="um-export-data">
					<?php esc_html_e('To export of your personal data, click the button below.', 'wams'); ?>
				</label>
				<div class="um-field-area-response um-export-data"></div>

			<?php } ?>

			<a class="um-request-button um-export-data-button" data-action="um-export-data" href="javascript:void(0);">
				<?php esc_html_e('Request data', 'wams'); ?>
			</a>
		<?php } ?>

	</div>

	<div class="um-field um-field-export_data">
		<div class="um-field-label">
			<label>
				<?php esc_html_e('Erase of your data', 'wams'); ?>
			</label>
			<span class="um-tip um-tip-<?php echo is_rtl() ? 'e' : 'w'; ?>" title="<?php esc_attr_e('You can request erasing of the data that we have about you.', 'wams'); ?>">
				<i class="um-icon-help-circled"></i>
			</span>
			<div class="um-clear"></div>
		</div>

		<?php
		$completed = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID
				FROM $wpdb->posts
				WHERE post_author = %d AND
					  post_type = 'user_request' AND
					  post_name = 'remove_personal_data' AND
					  post_status = 'request-completed'
				ORDER BY ID DESC
				LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if (!empty($completed)) {

			echo '<p>' . esc_html__('Your personal data has been deleted.', 'wams') . '</p>';
			echo '<p>' . esc_html__('You could send a new request for deleting your personal data.', 'wams') . '</p>';
		}

		$pending = $wpdb->get_row(
			$wpdb->prepare(
				"SELECT ID, post_status
				FROM $wpdb->posts
				WHERE post_author = %d AND
					  post_type = 'user_request' AND
					  post_name = 'remove_personal_data' AND
					  post_status != 'request-completed'
				ORDER BY ID DESC
				LIMIT 1",
				$user_id
			),
			ARRAY_A
		);

		if (!empty($pending) && 'request-pending' === $pending['post_status']) {
			echo '<p>' . esc_html__('A confirmation email has been sent to your email. Click the link within the email to confirm your deletion request.', 'wams') . '</p>';
		} elseif (!empty($pending) && 'request-confirmed' === $pending['post_status']) {
			echo '<p>' . esc_html__('The administrator has not yet approved deleting your data. Please expect an email with a link to your data.', 'wams') . '</p>';
		} else {
			if (WAMS()->account()->current_password_is_required('privacy_erase_data')) {
		?>
				<label name="um-erase-data">
					<?php esc_html_e('Enter your current password to confirm the erasure of your personal data.', 'wams'); ?>
					<?php if (WAMS()->options()->get('toggle_password')) { ?>
						<div class="um-field-area-password">
							<input id="um-erase-data" type="password" placeholder="<?php esc_attr_e('Password', 'wams'); ?>">
							<span class="um-toggle-password"><i class="um-icon-eye"></i></span>
						</div>
					<?php } else { ?>
						<input id="um-erase-data" type="password" placeholder="<?php esc_attr_e('Password', 'wams'); ?>">
					<?php } ?>
					<div class="um-field-error um-erase-data">
						<span class="um-field-arrow"><i class="um-faicon-caret-up"></i></span><?php esc_html_e('You must enter a password', 'wams'); ?>
					</div>
					<div class="um-field-area-response um-erase-data"></div>
				</label>

			<?php } else { ?>

				<label name="um-erase-data">
					<?php esc_html_e('Require erasure of your personal data, click on the button below.', 'wams'); ?>
					<div class="um-field-area-response um-erase-data"></div>
				</label>

			<?php } ?>

			<a class="um-request-button um-erase-data-button" data-action="um-erase-data" href="javascript:void(0);">
				<?php esc_html_e('Request data erase', 'wams'); ?>
			</a>
		<?php } ?>

	</div>

<?php
}


function wams_request_user_data()
{
	WAMS()->check_ajax_nonce();

	if (!isset($_POST['request_action'])) {
		wp_send_json_error(__('Wrong request.', 'wams'));
	}

	$user_id        = get_current_user_id();
	$password       = !empty($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
	$user           = get_userdata($user_id);
	$hash           = $user->data->user_pass;
	$request_action = sanitize_key($_POST['request_action']);

	if ('um-export-data' === $request_action) {
		if (WAMS()->account()->current_password_is_required('privacy_download_data')) {
			if (!wp_check_password($password, $hash)) {
				$answer = esc_html__('The password you entered is incorrect.', 'wams');
				wp_send_json_success(array('answer' => $answer));
			}
		}
	} elseif ('um-erase-data' === $request_action) {
		if (WAMS()->account()->current_password_is_required('privacy_erase_data')) {
			if (!wp_check_password($password, $hash)) {
				$answer = esc_html__('The password you entered is incorrect.', 'wams');
				wp_send_json_success(array('answer' => $answer));
			}
		}
	}

	if ('um-export-data' === $request_action) {
		$request_id = wp_create_user_request($user->data->user_email, 'export_personal_data');
	} elseif ('um-erase-data' === $request_action) {
		$request_id = wp_create_user_request($user->data->user_email, 'remove_personal_data');
	}

	if (!isset($request_id) || empty($request_id)) {
		wp_send_json_error(__('Wrong request.', 'wams'));
	}

	if (is_wp_error($request_id)) {
		$answer = esc_html($request_id->get_error_message());
	} else {
		wp_send_user_request($request_id);
		if ('um-export-data' === $request_action) {
			$answer = esc_html__('A confirmation email has been sent to your email. Click the link within the email to confirm your export request.', 'wams');
		} elseif ('um-erase-data' === $request_action) {
			$answer = esc_html__('A confirmation email has been sent to your email. Click the link within the email to confirm your deletion request.', 'wams');
		}
	}

	wp_send_json_success(array('answer' => $answer));
}
add_action('wp_ajax_wams_request_user_data', 'wams_request_user_data');
