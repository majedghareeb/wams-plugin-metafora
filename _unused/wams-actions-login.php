<?php
if (!defined('ABSPATH')) {
	exit;
}

/**
 * Error processing hook for login.
 *
 * @param $submitted_data
 */
function wams_submit_form_errors_hook_login($submitted_data)
{
	$user_password = $submitted_data['user_password'];

	if (isset($submitted_data['username']) && '' === $submitted_data['username']) {
		WAMS()->form()->add_error('username', __('Please enter your username or email', 'wams'));
	}

	if (isset($submitted_data['user_login']) && '' === $submitted_data['user_login']) {
		WAMS()->form()->add_error('user_login', __('Please enter your username', 'wams'));
	}

	if (isset($submitted_data['user_email']) && ('' === $submitted_data['user_email'] || !is_email($submitted_data['user_email']))) {
		WAMS()->form()->add_error('user_email', __('Please enter your email', 'wams'));
	}

	if (isset($submitted_data['username'])) {
		$authenticate = $submitted_data['username'];
		$field = 'username';
		if (is_email($submitted_data['username'])) {
			$data = get_user_by('email', $submitted_data['username']);
			$user_name = isset($data->user_login) ? $data->user_login : '';
		} else {
			$user_name  = $submitted_data['username'];
		}
	} elseif (isset($submitted_data['user_email'])) {
		$authenticate = $submitted_data['user_email'];
		$field = 'user_email';
		$data = get_user_by('email', $submitted_data['user_email']);
		$user_name = isset($data->user_login) ? $data->user_login : '';
	} else {
		$field = 'user_login';
		$user_name = $submitted_data['user_login'];
		$authenticate = $submitted_data['user_login'];
	}

	if ($submitted_data['user_password'] == '') {
		WAMS()->form()->add_error('user_password', __('Please enter your password', 'wams'));
	}

	$user = get_user_by('login', $user_name);
	if ($user && wp_check_password($submitted_data['user_password'], $user->data->user_pass, $user->ID)) {
		WAMS()->login()->auth_id = username_exists($user_name);
	} else {
		WAMS()->form()->add_error('user_password', __('Password is incorrect. Please try again.', 'wams'));
	}

	// Integration with 3rd-party login handlers e.g. 3rd-party reCAPTCHA etc.
	$third_party_codes = apply_filters('wams_custom_authenticate_error_codes', array());

	// @since 4.18 replacement for 'wp_login_failed' action hook
	// see WP function wp_authenticate()
	$ignore_codes = array('empty_username', 'empty_password');

	$user = apply_filters('authenticate', null, $authenticate, $submitted_data['user_password']);
	if (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes)) {
		if (!empty($third_party_codes) && in_array($user->get_error_code(), $third_party_codes)) {
			WAMS()->form()->add_error($user->get_error_code(), $user->get_error_message());
		} else {
			WAMS()->form()->add_error('user_password', __('Password is incorrect. Please try again.', 'wams'));
		}
	}

	$user = apply_filters('wp_authenticate_user', $user, $submitted_data['user_password']);
	if (is_wp_error($user) && !in_array($user->get_error_code(), $ignore_codes)) {
		if (!empty($third_party_codes) && in_array($user->get_error_code(), $third_party_codes)) {
			WAMS()->form()->add_error($user->get_error_code(), $user->get_error_message());
		} else {
			WAMS()->form()->add_error('user_password', __('Password is incorrect. Please try again.', 'wams'));
		}
	}

	// if there is an error notify wp
	if (WAMS()->form()->has_error($field) || WAMS()->form()->has_error($user_password) || WAMS()->form()->count_errors() > 0) {
		do_action('wp_login_failed', $user_name, WAMS()->form()->get_wp_error());
	}
}
add_action('wams_submit_form_errors_hook_login', 'wams_submit_form_errors_hook_login');


/**
 * Display the login errors from other plugins
 *
 * @param $args
 */
function wams_display_login_errors($args)
{
	if (WAMS()->form()->count_errors() > 0) {
		$errors = WAMS()->form()->errors;
		// hook for other plugins to display error
		$error_keys = array_keys($errors);
	}

	if (isset($args['custom_fields'])) {
		$custom_fields = $args['custom_fields'];
	}

	if (!empty($error_keys) && !empty($custom_fields)) {
		foreach ($error_keys as $error) {
			if (trim($error) && !isset($custom_fields[$error]) && !empty($errors[$error])) {
				$error_message = apply_filters('login_errors', $errors[$error], $error);
				if (empty($error_message)) {
					return;
				}
				echo '<p class="um-notice err um-error-code-' . esc_attr($error) . '"><i class="um-icon-ios-close-empty" onclick="jQuery(this).parent().fadeOut();"></i>' . $error_message  . '</p>';
			}
		}
	}
}
add_action('wams_before_login_fields', 'wams_display_login_errors');

/**
 * Login checks through the frontend login
 *
 * @param array $submitted_data
 * @param array $form_data
 */
function wams_submit_form_errors_hook_logincheck($submitted_data, $form_data)
{
	// Logout if logged in
	if (is_user_logged_in()) {
		wp_logout();
	}

	$user_id = (isset(WAMS()->login()->auth_id)) ? WAMS()->login()->auth_id : '';
	wams_fetch_user($user_id);

	$status = wams_user('account_status'); // account status
	switch ($status) {
			// If user can't log in to site...
		case 'inactive':
		case 'awaiting_admin_review':
		case 'awaiting_email_confirmation':
		case 'rejected':
			wams_reset_user();
			// Not `wams_safe_redirect()` because WAMS()->permalinks()->get_current_url() is situated on the same host.
			wp_safe_redirect(add_query_arg('err', esc_attr($status), WAMS()->permalinks()->get_current_url()));
			exit;
	}

	if (isset($form_data['form_id']) && absint($form_data['form_id']) === absint(WAMS()->shortcodes()->core_login_form()) && WAMS()->form()->errors && !isset($_POST[WAMS()->honeypot])) {
		// Not `wams_safe_redirect()` because predefined login page is situated on the same host.
		wp_safe_redirect(wams_get_core_page('login'));
		exit;
	}
}
add_action('wams_submit_form_errors_hook_logincheck', 'wams_submit_form_errors_hook_logincheck', 9999, 2);

/**
 * Store last login timestamp
 *
 * @param $user_id
 */
function wams_store_lastlogin_timestamp($user_id)
{
	update_user_meta($user_id, '_wams_last_login', current_time('mysql', true));
	// Flush user cache after updating last_login timestamp.
	WAMS()->user()->remove_cache($user_id);
}
add_action('wams_on_login_before_redirect', 'wams_store_lastlogin_timestamp', 10, 1);


/**
 * @param $login
 */
function wams_store_lastlogin_timestamp_($login)
{
	$user = get_user_by('login', $login);

	if (false !== $user) {
		wams_store_lastlogin_timestamp($user->ID);

		$attempts = (int) get_user_meta($user->ID, 'password_rst_attempts', true);
		if ($attempts) {
			//don't create meta but update if it's exists only
			update_user_meta($user->ID, 'password_rst_attempts', 0);
		}
	}
}
add_action('wp_login', 'wams_store_lastlogin_timestamp_');

/**
 * Login user process.
 *
 * @param array $submitted_data
 */
function wams_user_login($submitted_data)
{
	// phpcs:disable WordPress.Security.NonceVerification -- already verified here
	$rememberme = (isset($_REQUEST['rememberme'], $submitted_data['rememberme']) && 1 === (int) $submitted_data['rememberme']) ? 1 : 0;

	// @todo check using the 'deny_admin_frontend_login' option
	if (false !== strrpos(wams_user('wp_roles'), 'administrator') && (!isset($_GET['provider']) && WAMS()->options()->get('deny_admin_frontend_login'))) {
		wp_die(esc_html__('This action has been prevented for security measures.', 'wams'));
	}

	WAMS()->user()->auto_login(wams_user('ID'), $rememberme);

	/**
	 * Fires after successful login and before user is redirected.
	 *
	 * @since 1.3.x
	 * @hook  wams_on_login_before_redirect
	 *
	 * @param {int} $user_id User ID.
	 *
	 * @example <caption>Make any custom action after successful login and before user is redirected.</caption>
	 * function my_on_login_before_redirect( $user_id ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_on_login_before_redirect', 'my_on_login_before_redirect', 10, 1 );
	 */
	do_action('wams_on_login_before_redirect', wams_user('ID'));

	// Priority redirect from $_GET attribute.
	if (!empty($submitted_data['redirect_to'])) {
		wams_safe_redirect($submitted_data['redirect_to']);
		exit;
	}

	// Role redirect
	$after_login = wams_user('after_login');
	if (empty($after_login)) {
		// Not `wams_safe_redirect()` because predefined user profile page is situated on the same host.
		wp_safe_redirect(wams_user_profile_url());
		exit;
	}

	switch ($after_login) {
		case 'redirect_admin':
			// Not `wams_safe_redirect()` because is redirected to wp-admin.
			wp_safe_redirect(admin_url());
			exit;
		case 'redirect_url':
			/**
			 * Filters change redirect URL after successful login.
			 *
			 * @since 2.0
			 * @hook  wams_login_redirect_url
			 *
			 * @param {string} $can_view Redirect URL.
			 * @param {int}    $user_id  User ID.
			 *
			 * @return {string} Redirect URL.
			 *
			 * @example <caption>Change redirect URL.</caption>
			 * function my_login_redirect_url( $url, $id ) {
			 *     // your code here
			 *     return $url;
			 * }
			 * add_filter( 'wams_login_redirect_url', 'my_login_redirect_url', 10, 2 );
			 */
			$redirect_url = apply_filters('wams_login_redirect_url', wams_user('login_redirect_url'), wams_user('ID'));
			wams_safe_redirect($redirect_url);
			exit;
		case 'refresh':
			// Not `wams_safe_redirect()` because WAMS()->permalinks()->get_current_url() is situated on the same host.
			wp_safe_redirect(WAMS()->permalinks()->get_current_url());
			exit;
		case 'redirect_profile':
		default:
			// Not `wams_safe_redirect()` because predefined user profile page is situated on the same host.
			wp_safe_redirect(wams_user_profile_url());
			exit;
	}
	// phpcs:enable WordPress.Security.NonceVerification -- already verified here
}
add_action('wams_user_login', 'wams_user_login');

/**
 * Form processing
 *
 * @param array $submitted_data
 * @param array $form_data
 */
function wams_submit_form_login($submitted_data, $form_data)
{
	if (!isset(WAMS()->form()->errors)) {
		/**
		 * Fires after successful submit login form.
		 *
		 * Internal WAMS callbacks (Priority -> Callback name -> Excerpt):
		 * * 10 - `wams_user_login()` Login form main handler.
		 *
		 * @since 1.3.x
		 * @hook wams_user_login
		 *
		 * @param {array} $submitted_data $_POST Submission array.
		 * @param {array} $form_data      WAMS form data. Since 2.6.7
		 *
		 * @example <caption>Make any custom login action if submission is valid.</caption>
		 * function my_user_login( $submitted_data, $form_data ) {
		 *     // your code here
		 * }
		 * add_action( 'wams_user_login', 'my_user_login', 10, 2 );
		 */
		do_action('wams_user_login', $submitted_data, $form_data);
	}
	/**
	 * Fires after submit login form.
	 *
	 * Internal WAMS callbacks (Priority -> Callback name -> Excerpt):
	 * * 10 - um-messaging.
	 *
	 * @since 1.3.x
	 * @hook wams_user_login_extra_hook
	 *
	 * @param {array} $submitted_data $_POST Submission array.
	 * @param {array} $form_data      WAMS form data. Since 2.6.7
	 *
	 * @example <caption>Make any custom login action.</caption>
	 * function my_user_login_extra( $submitted_data, $form_data ) {
	 *     // your code here
	 * }
	 * add_action( 'wams_user_login_extra_hook', 'my_user_login_extra', 10, 2 );
	 */
	do_action('wams_user_login_extra_hook', $submitted_data, $form_data);
}
add_action('wams_submit_form_login', 'wams_submit_form_login', 10, 2);

/**
 * Show the submit button
 *
 * @param $args
 */
function wams_add_submit_button_to_login($args)
{
	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_login_form_button_one
	 * @description Change Login Form Primary button
	 * @input_vars
	 * [{"var":"$primary_btn_word","type":"string","desc":"Button text"},
	 * {"var":"$args","type":"array","desc":"Login Form arguments"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_login_form_button_one', 'function_name', 10, 2 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_login_form_button_one', 'my_login_form_button_one', 10, 2 );
	 * function my_login_form_button_one( $primary_btn_word, $args ) {
	 *     // your code here
	 *     return $primary_btn_word;
	 * }
	 * ?>
	 */
	$primary_btn_word = apply_filters('wams_login_form_button_one', $args['primary_btn_word'], $args);

	if (!isset($primary_btn_word) || $primary_btn_word == '') {
		$primary_btn_word = WAMS()->options()->get('login_primary_btn_word');
	}

	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_login_form_button_two
	 * @description Change Login Form Secondary button
	 * @input_vars
	 * [{"var":"$secondary_btn_word","type":"string","desc":"Button text"},
	 * {"var":"$args","type":"array","desc":"Login Form arguments"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_login_form_button_two', 'function_name', 10, 2 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_login_form_button_two', 'my_login_form_button_two', 10, 2 );
	 * function my_login_form_button_two( $secondary_btn_word, $args ) {
	 *     // your code here
	 *     return $secondary_btn_word;
	 * }
	 * ?>
	 */
	$secondary_btn_word = apply_filters('wams_login_form_button_two', $args['secondary_btn_word'], $args);

	if (!isset($secondary_btn_word) || $secondary_btn_word == '') {
		$secondary_btn_word = WAMS()->options()->get('login_secondary_btn_word');
	}

	$secondary_btn_url = !empty($args['secondary_btn_url']) ? $args['secondary_btn_url'] : wams_get_core_page('register');
	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_login_form_button_two_url
	 * @description Change Login Form Secondary button URL
	 * @input_vars
	 * [{"var":"$secondary_btn_url","type":"string","desc":"Button URL"},
	 * {"var":"$args","type":"array","desc":"Login Form arguments"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage
	 * <?php add_filter( 'wams_login_form_button_two_url', 'function_name', 10, 2 ); ?>
	 * @example
	 * <?php
	 * add_filter( 'wams_login_form_button_two_url', 'my_login_form_button_two_url', 10, 2 );
	 * function my_login_form_button_two_url( $secondary_btn_url, $args ) {
	 *     // your code here
	 *     return $secondary_btn_url;
	 * }
	 * ?>
	 */
	$secondary_btn_url = apply_filters('wams_login_form_button_two_url', $secondary_btn_url, $args); ?>

	<div class="um-col-alt">

		<?php if (!empty($args['show_rememberme'])) {
			WAMS()->fields()->checkbox('rememberme', __('Keep me signed in', 'wams'), false); ?>
			<div class="um-clear"></div>
		<?php }

		if (!empty($args['secondary_btn'])) { ?>

			<div class="um-left um-half">
				<input type="submit" value="<?php esc_attr_e(wp_unslash($primary_btn_word), 'wams'); ?>" class="um-button" id="um-submit-btn" />
			</div>
			<div class="um-right um-half">
				<a href="<?php echo esc_url($secondary_btn_url); ?>" class="um-button um-alt">
					<?php _e(wp_unslash($secondary_btn_word), 'wams'); ?>
				</a>
			</div>

		<?php } else { ?>

			<div class="um-center">
				<input type="submit" value="<?php esc_attr_e(wp_unslash($primary_btn_word), 'wams'); ?>" class="um-button" id="um-submit-btn" />
			</div>

		<?php } ?>

		<div class="um-clear"></div>

	</div>

<?php
}
add_action('wams_after_login_fields', 'wams_add_submit_button_to_login', 1000);


/**
 * Display a forgot password link
 *
 * @param $args
 */
function wams_after_login_submit($args)
{
	if (empty($args['forgot_pass_link'])) {
		return;
	} ?>

	<div class="um-col-alt-b">
		<a href="<?php echo esc_url(wams_get_core_page('password-reset')); ?>" class="um-link-alt">
			<?php _e('Forgot your password?', 'wams'); ?>
		</a>
	</div>

<?php
}
add_action('wams_after_login_fields', 'wams_after_login_submit', 1001);


/**
 * Show Fields
 *
 * @param $args
 */
function wams_add_login_fields($args)
{
	echo WAMS()->fields()->display('login', $args);
}
add_action('wams_main_login_fields', 'wams_add_login_fields', 100);
