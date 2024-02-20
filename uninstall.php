<?php

/**
 * Uninstall UM
 *
 */

// Exit if accessed directly.
if (!defined('WP_UNINSTALL_PLUGIN')) {
	exit;
}

if (!defined('WAMS_PATH')) {
	define('WAMS_PATH', plugin_dir_path(__FILE__));
}

if (!defined('WAMS_URL')) {
	define('WAMS_URL', plugin_dir_url(__FILE__));
}

if (!defined('WAMS_PLUGIN')) {
	define('WAMS_PLUGIN', plugin_basename(__FILE__));
}

//for delete Email options only for Core email notifications
remove_all_filters('wams_email_notifications');
//for delete only Core Theme Link pages
remove_all_filters('wams_core_pages');

require_once plugin_dir_path(__FILE__) . 'includes/class-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-init.php';

$delete_options = WAMS()->options()->get('uninstall_on_delete');
if (!empty($delete_options)) {

	//remove uploads
	$upl_folder = WAMS()->files()->upload_basedir;
	WAMS()->files()->remove_dir($upl_folder);

	//remove core settings
	$settings_defaults = WAMS()->config()->settings_defaults;
	foreach ($settings_defaults as $k => $v) {
		WAMS()->options()->remove($k);
	}

	//delete WAMS Custom Post Types posts
	$wams_posts = get_posts(
		array(
			'post_type'   => array(
				'wams_form',
				'wams_directory',
				'wams_role',
				'wams_private_content',
				'wams_mailchimp',
				'wams_profile_tabs',
				'wams_social_login',
				'wams_review',
				'wams_frontend_posting',
				'wams_notice',
			),
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			'numberposts' => -1,
		)
	);

	foreach ($wams_posts as $wams_post) {
		delete_option('wams_existing_rows_' . $wams_post->ID);
		delete_option('wams_form_rowdata_' . $wams_post->ID);
		wp_delete_post($wams_post->ID, 1);
	}

	global $wp_roles;

	if (class_exists('\WP_Roles')) {
		if (!isset($wp_roles)) {
			$wp_roles = new \WP_Roles();
		}

		$role_keys = get_option('wams_roles', array());
		if ($role_keys) {
			foreach ($role_keys as $roleID) {
				$role_meta = get_option("wams_role_{$roleID}_meta");
				if (!empty($role_meta) && !empty($wp_roles->roles[$roleID])) {
					$wp_roles->roles[$roleID] = array_diff($wp_roles->roles[$roleID], $role_meta);
				}
			}
		}

		update_option($wp_roles->role_key, $wp_roles->roles);
	}

	//remove user role meta
	$role_keys = get_option('wams_roles', array());
	if ($role_keys) {
		foreach ($role_keys as $role_key) {
			delete_option('wams_role_' . $role_key . '_meta');
		}

		$wams_custom_role_users = get_users(
			array(
				'role__in' => $role_keys,
			)
		);

		if (!empty($wams_custom_role_users)) {
			foreach ($wams_custom_role_users as $custom_role_user) {
				foreach ($role_keys as $role_key) {
					if (user_can($custom_role_user, $role_key)) {
						$custom_role_user->remove_role($role_key);
					}
				}
			}
		}
	}

	delete_option('__wams_sitekey');
	delete_option('wams_flush_rewrite_rules');

	$statuses = array(
		'approved',
		'awaiting_admin_review',
		'awaiting_email_confirmation',
		'inactive',
		'rejected',
	);

	foreach ($statuses as $status) {
		delete_transient("wams_count_users_{$status}");
	}
	delete_transient('wams_count_users_pending_dot');
	delete_transient('wams_count_users_unassigned');

	//remove all users cache
	WAMS()->user()->remove_cache_all_users();

	global $wpdb;

	$wpdb->query(
		"DELETE
        FROM {$wpdb->usermeta}
        WHERE meta_key LIKE '_wams%' OR
              meta_key LIKE 'wams%' OR
              meta_key LIKE 'reviews%' OR
              meta_key = 'submitted' OR
              meta_key = 'account_status' OR
              meta_key = 'password_rst_attempts' OR
              meta_key = 'profile_photo' OR
              meta_key = '_enable_new_follow' OR
              meta_key = '_enable_new_friend' OR
              meta_key = '_mylists' OR
              meta_key = '_enable_new_pm' OR
              meta_key = '_hidden_conversations' OR
              meta_key = '_pm_blocked' OR
              meta_key = '_notifications_prefs' OR
              meta_key = '_profile_progress' OR
              meta_key = '_completed' OR
              meta_key = '_cannot_add_review' OR
              meta_key = 'synced_profile_photo' OR
              meta_key = 'full_name' OR
              meta_key = '_reviews' OR
              meta_key = '_reviews_compound' OR
              meta_key = '_reviews_total' OR
              meta_key = '_reviews_avg'"
	);

	$wpdb->query(
		"DELETE
        FROM {$wpdb->postmeta}
        WHERE meta_key LIKE '_wams%' OR
              meta_key LIKE 'wams%'"
	);

	//remove all tables from extensions
	$all_tables = "SHOW TABLES LIKE '{$wpdb->prefix}wams\_%'";
	$results    = $wpdb->get_results($all_tables);
	if ($results) {
		foreach ($results as $index => $value) {
			foreach ($value as $table_name) {
				$wams_groups_members = $wpdb->prefix . 'wams_groups_members';
				if ($table_name === $wams_groups_members) {
					$wpdb->query(
						"
						DELETE posts, term_rel, pmeta, terms, tax, commetns
				        FROM {$wpdb->posts} posts
				        LEFT JOIN {$wpdb->term_relationships} term_rel
				        ON (posts.ID = term_rel.object_id)
				        LEFT JOIN {$wpdb->postmeta} pmeta
				        ON (posts.ID = pmeta.post_id)
				        LEFT JOIN {$wpdb->terms} terms
				        ON (term_rel.term_taxonomy_id = terms.term_id)
				        LEFT JOIN {$wpdb->term_taxonomy} tax
				        ON (term_rel.term_taxonomy_id = tax.term_taxonomy_id)
				        LEFT JOIN {$wpdb->comments} commetns
				        ON (commetns.comment_post_ID = posts.ID)
				        WHERE posts.post_type = 'wams_groups' OR posts.post_type = 'wams_groups_discussion'"
					);
				}
				// phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared -- $table_name is static variable
				$wpdb->query("DROP TABLE IF EXISTS $table_name");
			}
		}
	}

	//remove options from extensions
	//user photos
	$wams_user_photos = get_posts(
		array(
			'post_type'   => array(
				'wams_user_photos',
			),
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			'numberposts' => -1,
		)
	);
	if ($wams_user_photos) {
		foreach ($wams_user_photos as $wams_user_photo) {
			$attachments = get_attached_media('image', $wams_user_photo->ID);
			foreach ($attachments as $attachment) {
				wp_delete_attachment($attachment->ID, 1);
			}
			wp_delete_post($wams_user_photo->ID, 1);
		}
	}

	//user notes
	$wams_notes = get_posts(
		array(
			'post_type'   => array(
				'wams_notes',
			),
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			'numberposts' => -1,
		)
	);
	if ($wams_notes) {
		foreach ($wams_notes as $wams_note) {
			$attachments = get_attached_media('image', $wams_note->ID);
			foreach ($attachments as $attachment) {
				wp_delete_attachment($attachment->ID, 1);
			}
			wp_delete_post($wams_note->ID, 1);
		}
	}

	//user tags
	$wpdb->query(
		"
		DELETE tax, terms
    	FROM {$wpdb->term_taxonomy} tax
        LEFT JOIN {$wpdb->terms} terms
        ON (tax.term_taxonomy_id = terms.term_id)
    	WHERE tax.taxonomy = 'wams_user_tag'"
	);

	//mailchimp
	$mailchimp_log = WAMS()->files()->upload_basedir . 'mailchimp.log';
	if (file_exists($mailchimp_log)) {
		unlink($mailchimp_log);
	}

	$wams_options = $wpdb->get_results(
		"SELECT option_name
		FROM {$wpdb->options}
		WHERE option_name LIKE '_wams%' OR
              option_name LIKE 'wams_%' OR
              option_name LIKE 'widget_um%' OR
              option_name LIKE 'wams_%'"
	);

	foreach ($wams_options as $wams_option) {
		delete_option($wams_option->option_name);
	}

	//social activity
	$wams_activities = get_posts(
		array(
			'post_type'   => array(
				'wams_activity',
			),
			'post_status' => array('publish', 'pending', 'draft', 'auto-draft', 'future', 'private', 'inherit', 'trash'),
			'numberposts' => -1,
		)
	);
	foreach ($wams_activities as $wams_activity) {
		$image = get_post_meta($wams_activity->ID, '_photo', true);
		if ($image) {
			$user_id    = get_post_meta($wams_activity->ID, '_user_id', true);
			$upload_dir = wp_upload_dir();
			$image_path = $upload_dir['basedir'] . '/wams/' . $user_id . '/' . $image;
			if (file_exists($image_path)) {
				unlink($image_path);
			}
		}
		wp_delete_post($wams_activity->ID, 1);
	}
}
