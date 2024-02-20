<?php if (!defined('ABSPATH')) exit;


/**
 * Adds main links to a logout widget
 *
 * @param $args
 */
function wams_logout_user_links($args)
{
?>

	<li>
		<a href="<?php echo esc_url(wams_get_core_page('account')); ?>">
			<?php _e('Your account', 'wams'); ?>
		</a>
	</li>
	<li>
		<a href="<?php echo esc_url(add_query_arg('redirect_to', WAMS()->permalinks()->get_current_url(true), wams_get_core_page('logout'))); ?>">
			<?php _e('Logout', 'wams'); ?>
		</a>
	</li>

<?php
}
add_action('wams_logout_user_links', 'wams_logout_user_links', 100);
