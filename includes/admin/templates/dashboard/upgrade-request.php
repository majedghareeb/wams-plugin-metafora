<?php
if (!defined('ABSPATH')) {
	exit;
}

$url = add_query_arg(
	array(
		'wams_adm_action' => 'manual_upgrades_request',
		'_wpnonce'      => wp_create_nonce('manual_upgrades_request'),
	)
);
?>

<p><?php esc_html_e('Run this task from time to time if you have issues with WP Cron and need to get WAMS extension updates.', 'wams'); ?></p>
<p>
	<a href="<?php echo esc_url($url); ?>" class="button">
		<?php esc_html_e('Get latest versions', 'wams'); ?>
	</a>
</p>