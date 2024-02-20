<?php
$roles_associations = get_option('wams_roles_associations');

/*$mc_lists = get_posts( array(
	'post_type'     => 'wams_mailchimp',
	'numberposts'   => -1,
	'fields'        => 'ids'
) );*/

$p_query = new WP_Query;
$mc_lists = $p_query->query(array(
	'post_type'         => 'wams_mailchimp',
	'posts_per_page'    => -1,
	'fields'            => 'ids'
));

foreach ($mc_lists as $list_id) {
	$wams_roles = get_post_meta($list_id, '_wams_roles', true);
	$wams_roles = !$wams_roles ? array() : $wams_roles;
	if (!empty($wams_roles)) {
		foreach ($wams_roles as $i => $role_k) {
			$wams_roles[$i] = $roles_associations[$role_k];
		}

		update_post_meta($list_id, '_wams_roles', $wams_roles);
	}
}
