<?php
$roles_associations = get_option('wams_roles_associations');

//Content Restriction transfer

//for check all post types and taxonomies
$all_post_types = get_post_types(array('public' => true));

$all_taxonomies = get_taxonomies(array('public' => true));
$exclude_taxonomies = WAMS()->excluded_taxonomies();

foreach ($all_taxonomies as $key => $taxonomy) {
	if (in_array($key, $exclude_taxonomies)) {
		unset($all_taxonomies[$key]);
	}
}

foreach ($all_post_types as $key => $value) {
	$all_post_types[$key] = true;
}

foreach ($all_taxonomies as $key => $value) {
	$all_taxonomies[$key] = true;
}

WAMS()->options()->update('restricted_access_post_metabox', $all_post_types);
WAMS()->options()->update('restricted_access_taxonomy_metabox', $all_taxonomies);


$roles_array = WAMS()->roles()->get_roles(false, array('administrator'));

/*$posts = get_posts( array(
	'post_type'     => 'any',
	'meta_key'      => '_wams_custom_access_settings',
	'meta_value'    => '1',
	'fields'        => 'ids',
	'numberposts'   => -1
) );*/

$p_query = new WP_Query;
$posts = $p_query->query(array(
	'post_type'         => 'any',
	'meta_key'          => '_wams_custom_access_settings',
	'meta_value'        => '1',
	'posts_per_page'    => -1,
	'fields'            => 'ids'
));

if (!empty($posts)) {
	foreach ($posts as $post_id) {
		$wams_accessible = get_post_meta($post_id, '_wams_accessible', true);
		$wams_access_roles = get_post_meta($post_id, '_wams_access_roles', true);
		$wams_access_redirect = ($wams_accessible == '2') ? get_post_meta($post_id, '_wams_access_redirect', true) : get_post_meta($post_id, '_wams_access_redirect2', true);

		$access_roles = array();
		if (!empty($wams_access_roles)) {
			foreach ($roles_array as $role => $role_label) {
				//if ( in_array( substr( $role, 3 ), $wams_access_roles ) )
				if (false !== array_search($role, $roles_associations) && in_array(array_search($role, $roles_associations), $wams_access_roles))
					$access_roles[$role] = '1';
				else
					$access_roles[$role] = '0';
			}
		} else {
			foreach ($roles_array as $role => $role_label) {
				$access_roles[$role] = '0';
			}
		}

		$restrict_options = array(
			'_wams_custom_access_settings'        => '1',
			'_wams_accessible'                    => $wams_accessible,
			'_wams_access_roles'                  => $access_roles,
			'_wams_noaccess_action'               => '1',
			'_wams_restrict_by_custom_message'    => '0',
			'_wams_restrict_custom_message'       => '',
			'_wams_access_redirect'               => '1',
			'_wams_access_redirect_url'           => !empty($wams_access_redirect) ? $wams_access_redirect : '',
			'_wams_access_hide_from_queries'      => '0',
		);

		update_post_meta($post_id, 'wams_content_restriction', $restrict_options);
	}
}


$all_taxonomies = get_taxonomies(array('public' => true));
$exclude_taxonomies = WAMS()->excluded_taxonomies();

foreach ($all_taxonomies as $key => $taxonomy) {
	if (in_array($key, $exclude_taxonomies))
		continue;

	$terms = get_terms(array(
		'taxonomy'      => $taxonomy,
		'hide_empty'    => false,
		'fields'        => 'ids'
	));

	if (empty($terms))
		continue;

	foreach ($terms as $term_id) {
		$term_meta = get_option("category_{$term_id}");

		if (empty($term_meta))
			continue;

		$wams_accessible = !empty($term_meta['_wams_accessible']) ? $term_meta['_wams_accessible'] : false;
		$wams_access_roles = !empty($term_meta['_wams_roles']) ? $term_meta['_wams_roles'] : array();
		$redirect = !empty($term_meta['_wams_redirect']) ? $term_meta['_wams_redirect'] : '';
		$redirect2 = !empty($term_meta['_wams_redirect2']) ? $term_meta['_wams_redirect2'] : '';
		$wams_access_redirect = ($wams_accessible == '2') ? $redirect : $redirect2;

		$access_roles = array();
		if (!empty($wams_access_roles)) {
			foreach ($roles_array as $role => $role_label) {
				if (false !== array_search($role, $roles_associations) && in_array(array_search($role, $roles_associations), $wams_access_roles))
					$access_roles[$role] = '1';
				else
					$access_roles[$role] = '0';
			}
		} else {
			foreach ($roles_array as $role => $role_label) {
				$access_roles[$role] = '0';
			}
		}

		$restrict_options = array(
			'_wams_custom_access_settings'        => '1',
			'_wams_accessible'                    => $wams_accessible,
			'_wams_access_roles'                  => $access_roles,
			'_wams_noaccess_action'               => '1',
			'_wams_restrict_by_custom_message'    => '0',
			'_wams_restrict_custom_message'       => '',
			'_wams_access_redirect'               => '1',
			'_wams_access_redirect_url'           => !empty($wams_access_redirect) ? $wams_access_redirect : '',
			'_wams_access_hide_from_queries'      => '0',
		);

		update_term_meta($term_id, 'wams_content_restriction', $restrict_options);
	}
}
