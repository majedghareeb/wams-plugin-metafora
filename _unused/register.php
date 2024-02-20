<?php

/**
 * Template for the register page
 *
 * This template can be overridden by copying it to yourtheme/wams/templates/register.php
 *
 * Page: "Register"
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

if (!is_user_logged_in()) {
	wams_reset_user();
}
?>

<div class="um <?php echo esc_attr($this->get_class($mode)); ?> um-<?php echo esc_attr($form_id); ?>">
	<div class="um-form" data-mode="<?php echo esc_attr($mode); ?>">
		<form method="post" action="">
			<?php
			/** This action is documented in includes/core/um-actions-profile.php */
			do_action('wams_before_form', $args);
			/** This action is documented in includes/core/um-actions-profile.php */
			do_action("wams_before_{$mode}_fields", $args);
			/** This action is documented in includes/core/um-actions-profile.php */
			do_action("wams_main_{$mode}_fields", $args);
			/** This action is documented in includes/core/um-actions-profile.php */
			do_action('wams_after_form_fields', $args);
			/** This action is documented in includes/core/um-actions-profile.php */
			do_action("wams_after_{$mode}_fields", $args);
			/** This action is documented in includes/core/um-actions-profile.php */
			do_action('wams_after_form', $args);
			?>
		</form>
	</div>
</div>