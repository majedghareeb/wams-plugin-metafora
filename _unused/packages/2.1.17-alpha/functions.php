<?php if (!defined('ABSPATH')) exit;


/**
 * @param $tab
 *
 * @return mixed
 */
function wams_upgrade_get_slug2117($tab)
{
	$slug = get_post_meta($tab->ID, 'wams_tab_slug', true);
	if (WAMS()->external_integrations()->is_wpml_active()) {
		global $sitepress;

		$tab_id = $sitepress->get_object_id($tab->ID, 'wams_profile_tabs', true, $sitepress->get_default_language());
		if ($tab_id && $tab_id != $tab->ID) {
			$slug = get_post_meta($tab_id, 'wams_tab_slug', true);
		}
	}

	return $slug;
}


function wams_upgrade_profile_tabs2117()
{
	WAMS()->admin()->check_ajax_nonce();

	wams_maybe_unset_time_limit();

	$labels = [
		'name'              => _x('Profile Tabs', 'Post Type General Name', 'wams'),
		'singular_name'     => _x('Profile tab', 'Post Type Singular Name', 'wams'),
		'menu_name'         => __('Profile Tabs', 'wams'),
		'name_admin_bar'    => __('Profile Tabs', 'wams'),
		'archives'          => __('Item Archives', 'wams'),
		'attributes'        => __('Item Attributes', 'wams'),
		'parent_item_colon' => __('Parent Item:', 'wams'),
		'all_items'         => __('All Items', 'wams'),
		'add_new_item'      => __('Add New Item', 'wams'),
		'add_new'           => __('Add New', 'wams'),
		'new_item'          => __('New Item', 'wams'),
		'edit_item'         => __('Edit Item', 'wams'),
		'update_item'       => __('Update Item', 'wams'),
		'view_item'         => __('View Item', 'wams'),
		'view_items'        => __('View Items', 'wams'),
		'search_items'      => __('Search Item', 'wams'),
		'not_found'         => __('Not found', 'wams'),
	];

	$args = [
		'label'                 => __('Profile Tabs', 'wams'),
		'description'           => __('', 'wams'),
		'labels'                => $labels,
		'supports'              => ['title', 'editor'],
		'hierarchical'          => false,
		'public'                => false,
		'show_ui'               => true,
		'show_in_menu'          => false,
		'menu_position'         => 5,
		'show_in_admin_bar'     => false,
		'show_in_nav_menus'     => false,
		'can_export'            => true,
		'has_archive'           => false,
		'exclude_from_search'   => true,
		'publicly_queryable'    => true,
		'capability_type'       => 'page',
	];

	register_post_type('wams_profile_tabs', $args);

	$profile_tabs = get_posts([
		'post_type'         => 'wams_profile_tabs',
		'orderby'           => 'menu_order',
		'posts_per_page'    => -1,
	]);

	if (!empty($profile_tabs)) {
		$tabs_slugs = [];

		foreach ($profile_tabs as $tab) {
			$slug = wams_upgrade_get_slug2117($tab);
			if (!empty($slug) && in_array($slug, $tabs_slugs)) {
				continue;
			}

			if (preg_match("/[a-z0-9]+$/i", urldecode($tab->post_name))) {
				$tab_slug = sanitize_title($tab->post_name);
			} else {
				// otherwise use autoincrement and slug generator
				$auto_increment = WAMS()->options()->get('custom_profiletab_increment');
				$auto_increment = !empty($auto_increment) ? $auto_increment : 1;
				$tab_slug = "custom_profiletab_{$auto_increment}";
			}

			if (WAMS()->external_integrations()->is_wpml_active()) {
				global $sitepress;

				$tab_id = $sitepress->get_object_id($tab->ID, 'wams_profile_tabs', true, $sitepress->get_default_language());
				if ($tab_id && $tab_id == $tab->ID) {
					update_post_meta($tab->ID, 'wams_tab_slug', $tab_slug);

					$tabs_slugs[] = $tab_slug;

					if (isset($auto_increment)) {
						$auto_increment++;
						WAMS()->options()->update('custom_profiletab_increment', $auto_increment);
					}

					// show new profile tab by default - update WAMS Appearances > Profile Tabs settings
					if (WAMS()->options()->get('profile_tab_' . $tab_slug) === '') {
						WAMS()->options()->update('profile_tab_' . $tab_slug, '1');
						WAMS()->options()->update('profile_tab_' . $tab_slug . '_privacy', '0');
					}
				}
			} else {
				update_post_meta($tab->ID, 'wams_tab_slug', $tab_slug);

				$tabs_slugs[] = $tab_slug;

				if (isset($auto_increment)) {
					$auto_increment++;
					WAMS()->options()->update('custom_profiletab_increment', $auto_increment);
				}

				// show new profile tab by default - update WAMS Appearances > Profile Tabs settings
				if (WAMS()->options()->get('profile_tab_' . $tab_slug) === '') {
					WAMS()->options()->update('profile_tab_' . $tab_slug, '1');
					WAMS()->options()->update('profile_tab_' . $tab_slug . '_privacy', '0');
				}
			}
		}
	}

	update_option('wams_last_version_upgrade', '2.1.17-alpha');

	if (!empty($profile_tabs)) {
		wp_send_json_success(array('message' => __('Profile tabs have been updated successfully', 'wams')));
	} else {
		wp_send_json_success(array('message' => __('Database has been updated successfully', 'wams')));
	}
}
