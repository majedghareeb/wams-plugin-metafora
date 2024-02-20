<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Account automatically approved.
 *
 * @param int $user_id
 */
function wams_post_registration_approved_hook($user_id)
{
	wams_fetch_user($user_id);

	WAMS()->user()->approve();
}
add_action('wams_post_registration_approved_hook', 'wams_post_registration_approved_hook');

/**
 * Account needs email validation.
 *
 * @param int $user_id
 */
function wams_post_registration_checkmail_hook($user_id)
{
	wams_fetch_user($user_id);

	WAMS()->user()->email_pending();
}
add_action('wams_post_registration_checkmail_hook', 'wams_post_registration_checkmail_hook');

/**
 * Account needs admin review.
 *
 * @param int $user_id
 */
function wams_post_registration_pending_hook($user_id)
{
	wams_fetch_user($user_id);

	WAMS()->user()->pending();
}
add_action('wams_post_registration_pending_hook', 'wams_post_registration_pending_hook');

/**
 * After insert a new user run at frontend and backend.
 *
 * @param int|WP_Error $user_id
 * @param array        $args
 * @param null|array   $form_data It's null in case when posted from wp-admin > Add user
 */
function wams_after_insert_user($user_id, $args, $form_data = null)
{
	if (empty($user_id) || is_wp_error($user_id)) {
		return;
	}

	// Set usermeta from submission.
	wams_fetch_user($user_id);
	if (!empty($args['submitted'])) {
		// It's only frontend case.
		WAMS()->user()->set_registration_details($args['submitted'], $args, $form_data);
	}

	// Set user status.
	$status = wams_user('status');
	if (empty($status)) {
		wams_fetch_user($user_id);
		$status = wams_user('status');
	}
	WAMS()->user()->set_status($status);

	// Create user uploads directory.
	WAMS()->uploader()->get_upload_user_base_dir($user_id, true);

	/**
	 * Fires after insert user to DB and there you can set any extra details.
	 *
	 * Internal WAMS callbacks (Priority -> Callback name -> Excerpt):
	 * 10  - `wams_registration_save_files()`            Save registration files.
	 * 100 - `wams_registration_set_profile_full_name()` Set user's full name.
	 *
	 * @since 2.0
	 * @hook wams_registration_set_extra_data
	 *
	 * @param {int}   $user_id        User ID.
	 * @param {array} $submitted_data $_POST Submission array.
	 * @param {array} $form_data      WAMS form data. Since 2.6.7
	 *
	 * @example <caption>Make any custom action after insert user to DB.</caption>
	 * function my_registration_set_extra_data( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_registration_set_extra_data', 'my_registration_set_extra_data', 10, 3 );
	 */
	do_action('wams_registration_set_extra_data', $user_id, $args, $form_data);
	/**
	 * Fires after complete WAMS user registration.
	 * Note: Native redirects handlers at 100 priority, you can add some info before redirects.
	 *
	 * Internal WAMS callbacks (Priority -> Callback name -> Excerpt):
	 * 10  - `wams_send_registration_notification()` Send notifications.
	 * 100 - `wams_check_user_status()`              Redirect after registration based on user status.
	 *
	 * @since 2.0
	 * @hook wams_registration_complete
	 *
	 * @param {int}   $user_id        User ID.
	 * @param {array} $submitted_data $_POST Submission array.
	 * @param {array} $form_data      WAMS form data. Since 2.6.7
	 *
	 * @example <caption>Make any common action after complete WAMS user registration.</caption>
	 * function my_registration_complete( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_registration_complete', 'my_registration_complete', 10, 3 );
	 */
	do_action('wams_registration_complete', $user_id, $args, $form_data);
}
add_action('wams_user_register', 'wams_after_insert_user', 1, 3);

/**
 * Send notification about registration
 *
 * @param $user_id
 */
function wams_send_registration_notification($user_id)
{
	wams_fetch_user($user_id);

	$emails = wams_multi_admin_email();
	if (!empty($emails)) {
		foreach ($emails as $email) {
			if ('pending' !== wams_user('account_status')) {
				WAMS()->mail()->send($email, 'notification_new_user', array('admin' => true));
			} else {
				WAMS()->mail()->send($email, 'notification_review', array('admin' => true));
			}
		}
	}
}
add_action('wams_registration_complete', 'wams_send_registration_notification');

/**
 * Check user status and redirect it after registration
 *
 * @param int        $user_id
 * @param array      $args
 * @param null|array $form_data
 */
function wams_check_user_status($user_id, $args, $form_data = null)
{
	$status = wams_user('account_status');
	/**
	 * Fires after complete WAMS user registration.
	 * Where $status can be equal to 'approved', 'checkmail' or 'pending'.
	 *
	 * @since 1.3.x
	 * @since 2.6.8 Added $form_data argument.
	 *
	 * @hook  wams_post_registration_{$status}_hook
	 *
	 * @param {int}   $user_id        User ID.
	 * @param {array} $submitted_data Registration form submitted data.
	 * @param {array} $form_data      Form data. Since 2.6.8
	 *
	 * @example <caption>Make a custom action after complete WAMS user registration when user get an approved status.</caption>
	 * function my_wams_post_registration( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_post_registration_approved_hook', 'my_wams_post_registration', 10, 3 );
	 * @example <caption>Make a custom action after complete WAMS user registration when user requires email activation.</caption>
	 * function my_wams_post_registration( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_post_registration_checkmail_hook', 'my_wams_post_registration', 10, 3 );
	 * @example <caption>Make a custom action after complete WAMS user registration when user requires admin review.</caption>
	 * function my_wams_post_registration( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_post_registration_pending_hook', 'my_wams_post_registration', 10, 3 );
	 */
	do_action("wams_post_registration_{$status}_hook", $user_id, $args, $form_data);

	if (is_null($form_data) || is_admin()) {
		return;
	}

	/**
	 * Fires after complete WAMS user registration. Only for the frontend action which is run before autologin and redirects.
	 * Where $status can be equal to 'approved', 'checkmail' or 'pending'.
	 *
	 * @since 1.3.x
	 * @since 2.6.8 Added $user_id, $submitted_data, $form_data arguments.
	 *
	 * @hook  track_{$status}_user_registration
	 *
	 * @param {int}   $user_id        User ID. Since 2.6.8
	 * @param {array} $submitted_data Registration form submitted data. Since 2.6.8
	 * @param {array} $form_data      Form data. Since 2.6.8
	 *
	 * @example <caption>Make a custom action after complete WAMS user registration when user get an approved status.</caption>
	 * function my_wams_post_registration( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'track_approved_user_registration', 'my_wams_post_registration', 10, 3 );
	 * @example <caption>Make a custom action after complete WAMS user registration when user requires email activation.</caption>
	 * function my_wams_post_registration( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'track_checkmail_user_registration', 'my_wams_post_registration', 10, 3 );
	 * @example <caption>Make a custom action after complete WAMS user registration when user requires admin review.</caption>
	 * function my_wams_post_registration( $user_id, $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'track_pending_user_registration', 'my_wams_post_registration', 10, 3 );
	 */
	do_action("track_{$status}_user_registration", $user_id, $args, $form_data);

	if ('approved' === $status) {
		// Check if user is logged in because there can be the customized way when through 'wams_registration_for_loggedin_users' hook the registration is enabled for the logged-in users (e.g. Administrator).
		if (!is_user_logged_in()) {
			// Custom way if 'wams_registration_for_loggedin_users' hook after custom callbacks returns true. Then don't make auto-login because user is already logged-in.
			WAMS()->user()->auto_login($user_id);
		}
		WAMS()->user()->generate_profile_slug($user_id);

		/**
		 * Fires after complete WAMS user registration and autologin.
		 *
		 * @since 1.3.65
		 * @hook  wams_registration_after_auto_login
		 *
		 * @param {int} $user_id User ID.
		 *
		 * @example <caption>Make a custom action after complete WAMS user registration and autologin.</caption>
		 * function my_wams_registration_after_auto_login( $user_id ) {
		 *     // your code here
		 * }
		 * add_action( 'wams_registration_after_auto_login', 'my_wams_registration_after_auto_login' );
		 */
		do_action('wams_registration_after_auto_login', $user_id);

		// Priority redirect
		if (isset($args['redirect_to'])) {
			wams_safe_redirect(urldecode($args['redirect_to']));
		}

		wams_fetch_user($user_id);

		if ('redirect_url' === wams_user('auto_approve_act') && '' !== wams_user('auto_approve_url')) {
			wams_safe_redirect(wams_user('auto_approve_url'));
		}

		if ('redirect_profile' === wams_user('auto_approve_act')) {
			// Not `wams_safe_redirect()` because predefined user profile page is situated on the same host.
			wp_safe_redirect(wams_user_profile_url());
			exit;
		}
	} else {
		if ('redirect_url' === wams_user($status . '_action') && '' !== wams_user($status . '_url')) {
			/**
			 * Filters the redirect URL for pending user after registration.
			 *
			 * @since 2.0
			 * @hook  wams_registration_pending_user_redirect
			 *
			 * @param {string} $url      Redirect URL.
			 * @param {string} $status   User status.
			 * @param {int}    $user_id  User ID.
			 *
			 * @return {string} Redirect URL.
			 *
			 * @example <caption>Change redirect URL for pending user after registration.</caption>
			 * function my_registration_pending_user_redirect( $url, $status, $user_id ) {
			 *     // your code here
			 *     return $url;
			 * }
			 * add_filter( 'wams_registration_pending_user_redirect', 'my_registration_pending_user_redirect', 10, 3 );
			 */
			$redirect_url = apply_filters('wams_registration_pending_user_redirect', wams_user($status . '_url'), $status, wams_user('ID'));
			wams_safe_redirect($redirect_url);
		}

		if ('show_message' === wams_user($status . '_action') && '' !== wams_user($status . '_message')) {
			$url = WAMS()->permalinks()->get_current_url();
			$url = add_query_arg('message', esc_attr($status), $url);
			// Add only priority role to URL.
			$url = add_query_arg('wams_role', esc_attr(wams_user('role')), $url);
			$url = add_query_arg('wams_form_id', esc_attr($form_data['form_id']), $url);
			/**
			 * Filters the redirect URL for user after registration based on its status when need to show message.
			 *
			 * @since 2.6.11
			 * @hook  wams_registration_show_message_redirect_url
			 *
			 * @param {string} $url       Redirect URL.
			 * @param {string} $status    User status.
			 * @param {int}    $user_id   User ID.
			 * @param {array}  $form_data Form data.
			 *
			 * @return {string} Redirect URL.
			 *
			 * @example <caption>Change redirect URL for user after registration based on its status when need to show message.</caption>
			 * function my_wams_registration_show_message_redirect_url( $url, $status, $user_id ) {
			 *     // your code here
			 *     return $url;
			 * }
			 * add_filter( 'wams_registration_show_message_redirect_url', 'my_wams_registration_show_message_redirect_url', 10, 4 );
			 */
			$url = apply_filters('wams_registration_show_message_redirect_url', $url, $status, wams_user('ID'), $form_data);
			// Not `wams_safe_redirect()` because WAMS()->permalinks()->get_current_url() is situated on the same host.
			wp_safe_redirect($url);
			exit;
		}
	}
}
add_action('wams_registration_complete', 'wams_check_user_status', 100, 3);

/**
 * Validate user password field on registration.
 *
 * @param array $submitted_data
 */
function wams_submit_form_errors_hook__registration($submitted_data)
{
	// Check for "\" in password.
	if (array_key_exists('user_password', $submitted_data) && false !== strpos(wp_unslash(trim($submitted_data['user_password'])), '\\')) {
		WAMS()->form()->add_error('user_password', __('Passwords may not contain the character "\\".', 'wams'));
	}
}
add_action('wams_submit_form_errors_hook__registration', 'wams_submit_form_errors_hook__registration');

/**
 * Registration form submit handler.
 *
 * @param array $args
 * @param array $form_data
 */
function wams_submit_form_register($args, $form_data)
{
	if (isset(WAMS()->form()->errors)) {
		return;
	}

	/**
	 * Filters user data submitted by a registration form.
	 *
	 * Note: Data is already sanitized here.
	 *
	 * @since 1.3.x
	 * @hook  wams_add_user_frontend_submitted
	 *
	 * @param {array} $submitted Submitted registration data.
	 * @param {array} $form_data WAMS form data. Since 2.6.7
	 *
	 * @return {array} Extended registration data.
	 *
	 * @example <caption>Extends registration data.</caption>
	 * function my_add_user_frontend_submitted( $submitted, $form_data ) {
	 *     // your code here
	 *     return $submitted;
	 * }
	 * add_filter( 'wams_add_user_frontend_submitted', 'my_add_user_frontend_submitted', 10, 2 );
	 */
	$args = apply_filters('wams_add_user_frontend_submitted', $args, $form_data);

	if (!empty($args['user_login'])) {
		$user_login = $args['user_login'];
	}
	if (!empty($args['username']) && empty($args['user_login'])) {
		$user_login = $args['username'];
	}

	if (!empty($args['first_name']) && !empty($args['last_name']) && empty($user_login)) {

		switch (WAMS()->options()->get('permalink_base')) {
			case 'name':
				$user_login = str_replace(' ', '.', $args['first_name'] . ' ' . $args['last_name']);
				break;

			case 'name_dash':
				$user_login = str_replace(' ', '-', $args['first_name'] . ' ' . $args['last_name']);
				break;

			case 'name_plus':
				$user_login = str_replace(' ', '+', $args['first_name'] . ' ' . $args['last_name']);
				break;

			default:
				$user_login = str_replace(' ', '', $args['first_name'] . ' ' . $args['last_name']);
				break;
		}
		$user_login = sanitize_user(strtolower(remove_accents($user_login)), true);

		if (!empty($user_login)) {
			$count           = 1;
			$temp_user_login = $user_login;
			while (username_exists($temp_user_login)) {
				$temp_user_login = $user_login . $count;
				$count++;
			}
			$user_login = $temp_user_login;
		}
	}

	if (empty($user_login) && !empty($args['user_email'])) {
		$user_login = $args['user_email'];
	}

	$unique_user_id = uniqid();

	// see dbDelta and WP native DB structure user_login varchar(60)
	if (empty($user_login) || (mb_strlen($user_login) > 60 && !is_email($user_login))) {
		$user_login = 'user' . $unique_user_id;
		while (username_exists($user_login)) {
			$unique_user_id = uniqid();
			$user_login     = 'user' . $unique_user_id;
		}
	}

	if (isset($args['username']) && is_email($args['username'])) {
		$user_email = $args['username'];
	} elseif (!empty($args['user_email'])) {
		$user_email = $args['user_email'];
	}

	if (!isset($args['user_password'])) {
		$user_password = WAMS()->validation()->generate(8);
	} else {
		$user_password = $args['user_password'];
	}

	if (empty($user_email)) {
		$site_url   = wp_parse_url(get_site_url(), PHP_URL_HOST);
		$user_email = 'nobody' . $unique_user_id . '@' . $site_url;
		while (email_exists($user_email)) {
			$unique_user_id = uniqid();
			$user_email     = 'nobody' . $unique_user_id . '@' . $site_url;
		}

		/**
		 * Filters change user default email if it's empty on registration.
		 *
		 * @since 1.3.x
		 * @hook  wams_user_register_submitted__email
		 *
		 * @param {string} $user_email Default email.
		 *
		 * @return {string} Default customized email.
		 *
		 * @example <caption>Change user default email if it's empty on registration.</caption>
		 * function my_user_register_submitted__email( $user_email ) {
		 *     // your code here
		 *     return $user_email;
		 * }
		 * add_filter( 'wams_user_register_submitted__email', 'my_user_register_submitted__email' );
		 */
		$user_email = apply_filters('wams_user_register_submitted__email', $user_email);
	}

	$credentials = array(
		'user_login'    => $user_login,
		'user_password' => $user_password,
		'user_email'    => trim($user_email),
	);

	// @todo test when ready maybe remove
	if (!empty($args['submitted'])) {
		$args['submitted'] = WAMS()->form()->clean_submitted_data($args['submitted']);
	}

	$args['submitted'] = array_merge($args['submitted'], $credentials);
	$args              = array_merge($args, $credentials);

	//get user role from global or form's settings
	$user_role = WAMS()->form()->assigned_role(WAMS()->form()->form_id);

	//get user role from field Role dropdown or radio
	if (isset($args['role'])) {
		global $wp_roles;
		$exclude_roles = array_diff(array_keys($wp_roles->roles), WAMS()->roles()->get_editable_user_roles());

		//if role is properly set it
		if (!in_array($args['role'], $exclude_roles, true)) {
			$user_role = $args['role'];
		}
	}

	/**
	 * Filters change user role on registration process
	 *
	 * @since 2.0
	 * @hook  wams_registration_user_role
	 *
	 * @param {string} $user_role User role.
	 * @param {array}  $args      Registration data.
	 * @param {array}  $form_data WAMS form data. Since 2.6.7
	 *
	 * @return {string} User role.
	 *
	 * @example <caption>Change user role on registration process.</caption>
	 * function my_registration_user_role( $user_role, $args, $form_data ) {
	 *     // your code here
	 *     return $user_role;
	 * }
	 * add_filter( 'wams_registration_user_role', 'my_registration_user_role', 10, 3 );
	 */
	$user_role = apply_filters('wams_registration_user_role', $user_role, $args, $form_data);

	$userdata = array(
		'user_login' => $user_login,
		'user_pass'  => $user_password,
		'user_email' => $user_email,
		'role'       => $user_role,
	);

	$user_id = wp_insert_user($userdata);
	if (is_wp_error($user_id)) {
		// Default WordPress validation if there aren't any WAMS native for the registration fields.
		if ('existing_user_login' === $user_id->get_error_code()) {
			WAMS()->form()->add_error('user_login', $user_id->get_error_message());
		} elseif ('existing_user_email' === $user_id->get_error_code()) {
			WAMS()->form()->add_error('user_email', $user_id->get_error_message());
		} else {
			WAMS()->form()->add_error('user_login', $user_id->get_error_message());
		}
		return;
	}

	/**
	 * Fires after complete WAMS user registration.
	 *
	 * Internal WAMS callbacks (Priority -> Callback name -> Excerpt):
	 * 1 - `wams_after_insert_user()` Make all WAMS data set and actions after user registration|added via wp-admin.
	 *
	 * @since 2.0
	 * @hook  wams_user_register
	 *
	 * @param {int}   $user_id   User ID.
	 * @param {array} $args      Form data.
	 * @param {array} $form_data WAMS form data. Since 2.6.7
	 *
	 * @example <caption>Make any custom action after complete WAMS user registration.</caption>
	 * function my_wams_user_register( $user_id, $args, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_user_register', 'my_wams_user_register', 10, 3 );
	 */
	do_action('wams_user_register', $user_id, $args, $form_data);
}
add_action('wams_submit_form_register', 'wams_submit_form_register', 10, 2);

/**
 * Show the submit button
 *
 * @param $args
 */
function wams_add_submit_button_to_register($args)
{
	$primary_btn_word = $args['primary_btn_word'];
	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_register_form_button_one
	 * @description Change Register Form Primary button
	 * @input_vars
	 * [{"var":"$primary_btn_word","type":"string","desc":"Button text"},
	 * {"var":"$args","type":"array","desc":"Registration Form arguments"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_register_form_button_one', 'function_name', 10, 2 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_register_form_button_one', 'my_register_form_button_one', 10, 2 );
	 * function my_register_form_button_one( $primary_btn_word, $args ) {
	 *     // your code here
	 *     return $primary_btn_word;
	 * }
	 * ?>
	 */
	$primary_btn_word = apply_filters('wams_register_form_button_one', $primary_btn_word, $args);

	if (!isset($primary_btn_word) || $primary_btn_word == '') {
		$primary_btn_word = WAMS()->options()->get('register_primary_btn_word');
	}

	$secondary_btn_word = $args['secondary_btn_word'];
	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_register_form_button_two
	 * @description Change Registration Form Secondary button
	 * @input_vars
	 * [{"var":"$secondary_btn_word","type":"string","desc":"Button text"},
	 * {"var":"$args","type":"array","desc":"Registration Form arguments"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_register_form_button_two', 'function_name', 10, 2 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_register_form_button_two', 'my_register_form_button_two', 10, 2 );
	 * function my_register_form_button_two( $secondary_btn_word, $args ) {
	 *     // your code here
	 *     return $secondary_btn_word;
	 * }
	 * ?>
	 */
	$secondary_btn_word = apply_filters('wams_register_form_button_two', $secondary_btn_word, $args);

	if (!isset($secondary_btn_word) || $secondary_btn_word == '') {
		$secondary_btn_word = WAMS()->options()->get('register_secondary_btn_word');
	}

	$secondary_btn_url = (isset($args['secondary_btn_url']) && $args['secondary_btn_url']) ? $args['secondary_btn_url'] : wams_get_core_page('login');
	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_register_form_button_two_url
	 * @description Change Registration Form Secondary button URL
	 * @input_vars
	 * [{"var":"$secondary_btn_url","type":"string","desc":"Button URL"},
	 * {"var":"$args","type":"array","desc":"Registration Form arguments"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_register_form_button_two_url', 'function_name', 10, 2 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_register_form_button_two_url', 'my_register_form_button_two_url', 10, 2 );
	 * function my_register_form_button_two_url( $secondary_btn_url, $args ) {
	 *     // your code here
	 *     return $secondary_btn_url;
	 * }
	 * ?>
	 */
	$secondary_btn_url = apply_filters('wams_register_form_button_two_url', $secondary_btn_url, $args); ?>

	<div class="um-col-alt">

		<?php if (!empty($args['secondary_btn'])) { ?>

			<div class="um-left um-half">
				<input type="submit" value="<?php esc_attr_e(wp_unslash($primary_btn_word), 'wams') ?>" class="um-button" id="um-submit-btn" />
			</div>
			<div class="um-right um-half">
				<a href="<?php echo esc_url($secondary_btn_url); ?>" class="um-button um-alt">
					<?php _e(wp_unslash($secondary_btn_word), 'wams'); ?>
				</a>
			</div>

		<?php } else { ?>

			<div class="um-center">
				<input type="submit" value="<?php esc_attr_e(wp_unslash($primary_btn_word), 'wams') ?>" class="um-button" id="um-submit-btn" />
			</div>

		<?php } ?>

		<div class="um-clear"></div>

	</div>

<?php
}
add_action('wams_after_register_fields', 'wams_add_submit_button_to_register', 1000);


/**
 * Show Fields
 *
 * @param $args
 */
function wams_add_register_fields($args)
{
	echo WAMS()->fields()->display('register', $args);
}
add_action('wams_main_register_fields', 'wams_add_register_fields', 100);

/**
 * Saving files to register a new user, if there are fields with files.
 *
 * @param $user_id
 * @param $args
 * @param $form_data
 */
function wams_registration_save_files($user_id, $args, $form_data)
{
	if (empty($args['submitted'])) {
		// It's only frontend case.
		return;
	}

	$files = array();

	$fields = maybe_unserialize($form_data['custom_fields']);
	if (!empty($fields) && is_array($fields)) {
		foreach ($fields as $key => $array) {
			if (isset($args['submitted'][$key])) {
				if (
					isset($array['type']) && in_array($array['type'], array('image', 'file'), true) &&
					(wams_is_temp_file($args['submitted'][$key]) || 'empty_file' === $args['submitted'][$key])
				) {
					$files[$key] = $args['submitted'][$key];
				}
			}
		}
	}

	/**
	 * Filters files submitted by the WAMS registration or profile form.
	 *
	 * @param {array} $files   Submitted files.
	 * @param {int}   $user_id User ID.
	 *
	 * @return {array} Submitted files.
	 *
	 * @since 1.3.x
	 * @hook wams_user_pre_updating_files_array
	 *
	 * @example <caption>Extends submitted files.</caption>
	 * function my_user_pre_updating_files( $files, $user_id ) {
	 *     $files[] = 'some file';
	 *     return $files;
	 * }
	 * add_filter( 'wams_user_pre_updating_files_array', 'my_user_pre_updating_files', 10, 2 );
	 */
	$files = apply_filters('wams_user_pre_updating_files_array', $files, $user_id);
	if (!empty($files) && is_array($files)) {
		WAMS()->uploader()->replace_upload_dir = true;
		WAMS()->uploader()->move_temporary_files($user_id, $files);
		WAMS()->uploader()->replace_upload_dir = false;
	}
}
add_action('wams_registration_set_extra_data', 'wams_registration_save_files', 10, 3);


/**
 * Update user Full Name
 *
 * @profile name update
 *
 * @param $user_id
 * @param $args
 */
function wams_registration_set_profile_full_name($user_id, $args)
{
	/**
	 * Fires for updating user profile full name.
	 *
	 * @since 1.3.x
	 * @hook wams_registration_set_extra_data
	 *
	 * @param {int}   $user_id        User ID.
	 * @param {array} $submitted_data $_POST Submission array.
	 *
	 * @example <caption>Make any custom action when updating user profile full name.</caption>
	 * function my_registration_set_extra_data( $user_id, $submitted_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_update_profile_full_name', 'my_update_profile_full_name', 10, 2 );
	 */
	do_action('wams_update_profile_full_name', $user_id, $args);
}
add_action('wams_registration_set_extra_data', 'wams_registration_set_profile_full_name', 10, 2);

/**
 * Redirect from default registration to WAMS registration page
 */
function wams_form_register_redirect()
{
	$page_id = WAMS()->options()->get(WAMS()->options()->get_core_page_id('register'));
	// Do not redirect if the registration page is not published.
	if (!empty($page_id) && 'publish' === get_post_status($page_id)) {
		// Not `wams_safe_redirect()` because predefined register page is situated on the same host.
		wp_safe_redirect(get_permalink($page_id));
		exit();
	}
}
add_action('login_form_register', 'wams_form_register_redirect', 10);
