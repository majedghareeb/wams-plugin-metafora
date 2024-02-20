<?php

/**
 * Template for the password reset
 *
 * This template can be overridden by copying it to yourtheme/wams/templates/password-reset.php
 *
 * Call: function wams_password()
 *
 * @version 2.7.0
 *
 * @var string $mode
 * @var int    $form_id
 * @var array  $args
 */
if (!defined('ABSPATH')) {
	exit;
}
?>

<div class="um <?php echo esc_attr($this->get_class($mode)); ?> um-<?php echo esc_attr($form_id); ?>">
	<div class="um-form">
		<form method="post" action="">
			<?php if (isset($_GET['updated']) && 'checkemail' === sanitize_key($_GET['updated'])) { ?>
				<div class="um-field um-field-block um-field-type_block">
					<div class="um-field-block">
						<div style="text-align:center;">
							<?php esc_html_e('If an account matching the provided details exists, we will send a password reset link. Please check your inbox.', 'wams'); ?>
						</div>
					</div>
				</div>
			<?php } elseif (isset($_GET['updated']) && 'password_changed' === sanitize_key($_GET['updated'])) { ?>
				<div class="um-field um-field-block um-field-type_block">
					<div class="um-field-block">
						<div style="text-align:center;">
							<?php esc_html_e('You have successfully changed password.', 'wams'); ?>
						</div>
					</div>
				</div>
			<?php } else { ?>

				<input type="hidden" name="_wams_password_reset" id="_wams_password_reset" value="1" />

				<?php
				/**
				 * WAMS hook
				 *
				 * @type action
				 * @title wams_reset_password_page_hidden_fields
				 * @description Password reset hidden fields
				 * @input_vars
				 * [{"var":"$args","type":"array","desc":"Password reset shortcode arguments"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_action( 'wams_reset_password_page_hidden_fields', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_action( 'wams_reset_password_page_hidden_fields', 'my_reset_password_page_hidden_fields', 10, 1 );
				 * function my_reset_password_page_hidden_fields( $args ) {
				 *     // your code here
				 * }
				 * ?>
				 */
				do_action('wams_reset_password_page_hidden_fields', $args);

				if (!empty($_GET['updated'])) { ?>
					<div class="um-field um-field-block um-field-type_block">
						<div class="um-field-block">
							<div style="text-align:center;">
								<?php if ('expiredkey' === sanitize_key($_GET['updated'])) {
									esc_html_e('Your password reset link has expired. Please request a new link below.', 'wams');
								} elseif ('invalidkey' === sanitize_key($_GET['updated'])) {
									esc_html_e('Your password reset link appears to be invalid. Please request a new link below.', 'wams');
								} ?>
							</div>
						</div>
					</div>
				<?php } else { ?>
					<div class="um-field um-field-block um-field-type_block">
						<div class="um-field-block">
							<div style="text-align:center;">
								<?php esc_html_e('To reset your password, please enter your email address or username below.', 'wams'); ?>
							</div>
						</div>
					</div>
				<?php }

				$fields = WAMS()->builtin()->get_specific_fields('username_b');

				$output = null;
				foreach ($fields as $key => $data) {
					$output .= WAMS()->fields()->edit_field($key, $data);
				}
				echo $output;

				/**
				 * WAMS hook
				 *
				 * @type action
				 * @title wams_after_password_reset_fields
				 * @description Hook that runs after user reset their password
				 * @input_vars
				 * [{"var":"$args","type":"array","desc":"Form data"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_action( 'wams_after_password_reset_fields', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_action( 'wams_after_password_reset_fields', 'my_after_password_reset_fields', 10, 1 );
				 * function my_after_password_reset_fields( $args ) {
				 *     // your code here
				 * }
				 * ?>
				 */
				do_action('wams_after_password_reset_fields', $args); ?>

				<div class="um-col-alt um-col-alt-b">

					<div class="um-center">
						<input type="submit" value="<?php esc_attr_e('Reset password', 'wams'); ?>" class="um-button" id="um-submit-btn" />
					</div>

					<div class="um-clear"></div>

				</div>

			<?php
				/**
				 * WAMS hook
				 *
				 * @type action
				 * @title wams_reset_password_form
				 * @description Password reset display form
				 * @input_vars
				 * [{"var":"$args","type":"array","desc":"Password reset shortcode arguments"}]
				 * @change_log
				 * ["Since: 2.0"]
				 * @usage add_action( 'wams_reset_password_form', 'function_name', 10, 1 );
				 * @example
				 * <?php
				 * add_action( 'wams_reset_password_form', 'my_reset_password_form', 10, 1 );
				 * function my_reset_password_form( $args ) {
				 *     // your code here
				 * }
				 * ?>
				 */
				do_action('wams_reset_password_form', $args);
				/** This action is documented in includes/core/um-actions-profile.php */
				do_action('wams_after_form_fields', $args);
			}
			?>
		</form>
	</div>
</div>