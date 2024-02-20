<?php

namespace wams\admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Site_Health
 *
 * @package um\admin
 */
class Site_Health
{

	/**
	 * Site_Health constructor.
	 */
	public function __construct()
	{
		add_filter('debug_information', array($this, 'debug_information'), 20);
	}

	private function get_roles()
	{
		return WAMS()->roles()->get_roles();
	}

	private function get_forms()
	{
		$forms_data = get_posts(
			array(
				'post_type'      => 'wams_form',
				'posts_per_page' => -1,
			)
		);
		$forms      = array();
		foreach ($forms_data as $form) {
			$forms['ID#' . $form->ID] = $form->post_title;
		}
		return $forms;
	}

	private function get_role_meta($key)
	{
		return get_option("wams_role_{$key}_meta", false);
	}

	public function array_map($item)
	{
		if (is_array($item)) {
			$item = maybe_serialize($item);
		}
		return $item;
	}

	private function get_field_data($info, $key, $field_key, $field)
	{
		$row        = isset($field['metakey']) ? false : true;
		$title      = $row ? __('Row: ', 'wams') . $field['id'] : __('Field: ', 'wams') . $field['metakey'];
		$field      = array_map(array(&$this, 'array_map'), $field);
		$field_info = array(
			'um-field_' . $field_key => array(
				'label' => $title,
				'value' => $field,
			),
		);

		return $field_info;
	}

	private function get_member_directories()
	{
		$query              = new \WP_Query();
		$member_directories = $query->query(
			array(
				'post_type'      => 'wams_directory',
				'posts_per_page' => -1,
			)
		);

		$directories = array();
		foreach ($member_directories as $directory) {
			$directories['ID#' . $directory->ID] = $directory->post_title;
		}

		return $directories;
	}

	/**
	 * Add our data to Site Health information.
	 *
	 * @since 2.7.0
	 *
	 * @param array $info The Site Health information.
	 *
	 * @return array The updated Site Health information.
	 */
	public function debug_information($info)
	{
		$labels = array(
			'yes'     => __('Yes', 'wams'),
			'no'      => __('No', 'wams'),
			'all'     => __('All', 'wams'),
			'default' => __('Default', 'wams'),
			'nopages' => __('No predefined page', 'wams'),
		);

		$info['wams'] = array(
			'label'       => __('WAMS', 'wams'),
			'description' => __('This debug information for your WAMS installation can assist you in getting support.', 'wams'),
			'fields'      => array(),
		);

		// Pages settings.
		$pages            = array();
		$predefined_pages = WAMS()->config()->core_pages;
		foreach ($predefined_pages as $page_s => $page) {
			$page_id    = WAMS()->options()->get_core_page_id($page_s);
			$page_title = !empty($page['title']) ? $page['title'] : '';
			if (empty($page_title)) {
				continue;
			}

			$predefined_page_id = WAMS()->options()->get($page_id);

			if (empty($predefined_page_id)) {
				$pages[$page_title] = $labels['nopages'];
				continue;
			}
			// translators: %1$s is a predefined page title; %2$d is a predefined page ID; %3$s is a predefined page permalink.
			$pages[$page_title] = sprintf(__('%1$s (ID#%2$d) | %3$s', 'wams'), get_the_title($predefined_page_id), $predefined_page_id, get_permalink($predefined_page_id));
		}

		$pages = apply_filters('wams_debug_information_pages', $pages);

		$pages_settings = array(
			'um-pages' => array(
				'label' => __('Pages', 'wams'),
				'value' => $pages,
			),
		);

		// User settings
		$permalink_base = WAMS()->config()->permalink_base_options;
		$display_name   = WAMS()->config()->display_name_options;

		$user_settings = array(
			'um-permalink_base'              => array(
				'label' => __('Profile Permalink Base', 'wams'),
				'value' => isset($permalink_base[WAMS()->options()->get('permalink_base')]) ? $permalink_base[WAMS()->options()->get('permalink_base')] : $labels['no'],
			),
			'um-display_name'                => array(
				'label' => __('User Display Name', 'wams'),
				'value' => isset($display_name[WAMS()->options()->get('display_name')]) ? $display_name[WAMS()->options()->get('display_name')] : $labels['no'],
			),
			'um-author_redirect'             => array(
				'label' => __('Automatically redirect author page to their profile?', 'wams'),
				'value' => WAMS()->options()->get('author_redirect') ? $labels['yes'] : $labels['no'],
			),
			'um-members_page'                => array(
				'label' => __('Enable Members Directory', 'wams'),
				'value' => WAMS()->options()->get('members_page') ? $labels['yes'] : $labels['no'],
			),
			'um-toggle_password'             => array(
				'label' => __('Show/hide password button', 'wams'),
				'value' => WAMS()->options()->get('toggle_password') ? $labels['yes'] : $labels['no'],
			),
			'um-require_strongpass'          => array(
				'label' => __('Require Strong Passwords', 'wams'),
				'value' => WAMS()->options()->get('require_strongpass') ? $labels['yes'] : $labels['no'],
			),
			'um-password_min_chars'          => array(
				'label' => __('Require Strong Passwords', 'wams'),
				'value' => WAMS()->options()->get('password_min_chars'),
			),
			'um-password_max_chars'          => array(
				'label' => __('Require Strong Passwords', 'wams'),
				'value' => WAMS()->options()->get('password_max_chars'),
			),
			'um-profile_noindex'             => array(
				'label' => __('Avoid indexing profile by search engines', 'wams'),
				'value' => WAMS()->options()->get('profile_noindex') ? $labels['yes'] : $labels['no'],
			),
			'um-activation_link_expiry_time' => array(
				'label' => __('Activation link lifetime', 'wams'),
				'value' => WAMS()->options()->get('activation_link_expiry_time'),
			),
			'um-use_gravatars'               => array(
				'label' => __('Use Gravatars?', 'wams'),
				'value' => WAMS()->options()->get('use_gravatars') ? $labels['yes'] : $labels['no'],
			),
			'um-delete_comments'             => array(
				'label' => __('Deleting user comments after deleting a user', 'wams'),
				'value' => WAMS()->options()->get('delete_comments') ? $labels['yes'] : $labels['no'],
			),
		);

		if ('custom_meta' === WAMS()->options()->get('permalink_base')) {
			$user_settings = WAMS()->array_insert_before(
				$user_settings,
				'um-display_name',
				array(
					'um-permalink_base_custom_meta' => array(
						'label' => __('Profile Permalink Base Custom Meta Key', 'wams'),
						'value' => WAMS()->options()->get('permalink_base_custom_meta'),
					),
				)
			);
		}

		if ('field' === WAMS()->options()->get('display_name')) {
			$user_settings = WAMS()->array_insert_before(
				$user_settings,
				'um-author_redirect',
				array(
					'um-display_name_field' => array(
						'label' => __('Display Name Custom Field(s)', 'wams'),
						'value' => WAMS()->options()->get('display_name_field'),
					),
				)
			);
		}

		if (WAMS()->options()->get('use_gravatars')) {
			$gravatar_options = array(
				'default'   => __('Default', 'wams'),
				'404'       => __('404 ( File Not Found response )', 'wams'),
				'mm'        => __('Mystery Man', 'wams'),
				'identicon' => __('Identicon', 'wams'),
				'monsterid' => __('Monsterid', 'wams'),
				'wavatar'   => __('Wavatar', 'wams'),
				'retro'     => __('Retro', 'wams'),
				'blank'     => __('Blank ( a transparent PNG image )', 'wams'),
			);

			$user_settings['um-use_wams_gravatar_default_builtin_image'] = array(
				'label' => __('Use Gravatar builtin image', 'wams'),
				'value' => $gravatar_options[WAMS()->options()->get('use_wams_gravatar_default_builtin_image')],
			);
			if ('default' === WAMS()->options()->get('use_wams_gravatar_default_builtin_image')) {
				$user_settings['um-use_wams_gravatar_default_image'] = array(
					'label' => __('Use Default plugin avatar as Gravatar\'s Default avatar', 'wams'),
					'value' => WAMS()->options()->get('use_wams_gravatar_default_image') ? $labels['yes'] : $labels['no'],
				);
			}
		}

		// Account settings
		$account_settings = array(
			'um-account_tab_password' => array(
				'label' => __('Password Account Tab', 'wams'),
				'value' => WAMS()->options()->get('account_tab_password') ? $labels['yes'] : $labels['no'],
			),
			'um-account_tab_privacy'  => array(
				'label' => __('Privacy Account Tab', 'wams'),
				'value' => WAMS()->options()->get('account_tab_privacy') ? $labels['yes'] : $labels['no'],
			),
		);

		if (false !== WAMS()->account()->is_notifications_tab_visible()) {
			$account_settings['um-account_tab_notifications'] = array(
				'label' => __('Notifications Account Tab', 'wams'),
				'value' => WAMS()->options()->get('account_tab_notifications') ? $labels['yes'] : $labels['no'],
			);
		}

		$account_settings = array_merge(
			$account_settings,
			array(
				'um-account_tab_delete'                   => array(
					'label' => __('Delete Account Tab', 'wams'),
					'value' => WAMS()->options()->get('account_tab_delete') ? $labels['yes'] : $labels['no'],
				),
				'um-delete_account_text'                  => array(
					'label' => __('Account Deletion Custom Text', 'wams'),
					'value' => WAMS()->options()->get('delete_account_text'),
				),
				'um-delete_account_no_pass_required_text' => array(
					'label' => __('Account Deletion without password Custom Text', 'wams'),
					'value' => WAMS()->options()->get('delete_account_no_pass_required_text'),
				),
				'um-account_name'                         => array(
					'label' => __('Add a First & Last Name fields', 'wams'),
					'value' => WAMS()->options()->get('account_name') ? $labels['yes'] : $labels['no'],
				),
			)
		);

		if (WAMS()->options()->get('account_name')) {
			$account_settings['um-account_name_disable'] = array(
				'label' => __('Disable First & Last name field editing', 'wams'),
				'value' => WAMS()->options()->get('account_name_disable') ? $labels['yes'] : $labels['no'],
			);
			$account_settings['um-account_name_require'] = array(
				'label' => __('Require First & Last Name', 'wams'),
				'value' => WAMS()->options()->get('account_name_require') ? $labels['yes'] : $labels['no'],
			);
		}

		$account_settings['um-account_email'] = array(
			'label' => __('Allow users to change e-mail', 'wams'),
			'value' => WAMS()->options()->get('account_email') ? $labels['yes'] : $labels['no'],
		);

		$account_settings['um-account_general_password'] = array(
			'label' => __('Password is required?', 'wams'),
			'value' => WAMS()->options()->get('account_general_password') ? $labels['yes'] : $labels['no'],
		);

		$account_settings['um-account_hide_in_directory'] = array(
			'label' => __('Allow users to hide their profiles from directory', 'wams'),
			'value' => WAMS()->options()->get('account_hide_in_directory') ? $labels['yes'] : $labels['no'],
		);

		if (WAMS()->options()->get('account_hide_in_directory')) {
			$account_settings['um-account_hide_in_directory_default'] = array(
				'label' => __('Hide profiles from directory by default', 'wams'),
				'value' => WAMS()->options()->get('account_hide_in_directory_default'),
			);
		}

		// Uploads settings
		$profile_sizes_list = '';
		$profile_sizes      = WAMS()->options()->get('photo_thumb_sizes');
		if (!empty($profile_sizes)) {
			foreach ($profile_sizes as $size) {
				$profile_sizes_list = empty($profile_sizes_list) ? $size : $profile_sizes_list . ', ' . $size;
			}
		}
		$cover_sizes_list = '';
		$cover_sizes      = WAMS()->options()->get('cover_thumb_sizes');
		if (!empty($cover_sizes)) {
			foreach ($cover_sizes as $size) {
				$cover_sizes_list = empty($cover_sizes_list) ? $size : $cover_sizes_list . ', ' . $size;
			}
		}
		$uploads_settings = array(
			'um-profile_photo_max_size'    => array(
				'label' => __('Profile Photo Maximum File Size (bytes)', 'wams'),
				'value' => WAMS()->options()->get('profile_photo_max_size'),
			),
			'um-cover_photo_max_size'      => array(
				'label' => __('Cover Photo Maximum File Size (bytes)', 'wams'),
				'value' => WAMS()->options()->get('cover_photo_max_size'),
			),
			'um-photo_thumb_sizes'         => array(
				'label' => __('Profile Photo Thumbnail Sizes (px)', 'wams'),
				'value' => $profile_sizes_list,
			),
			'um-cover_thumb_sizes'         => array(
				'label' => __('Cover Photo Thumbnail Sizes (px)', 'wams'),
				'value' => $cover_sizes_list,
			),
			'um-image_orientation_by_exif' => array(
				'label' => __('Change image orientation', 'wams'),
				'value' => WAMS()->options()->get('image_orientation_by_exif') ? $labels['yes'] : $labels['no'],
			),
			'um-image_compression'         => array(
				'label' => __('Image Quality', 'wams'),
				'value' => WAMS()->options()->get('image_compression'),
			),
			'um-image_max_width'           => array(
				'label' => __('Image Upload Maximum Width (px)', 'wams'),
				'value' => WAMS()->options()->get('image_max_width'),
			),
			'um-cover_min_width'           => array(
				'label' => __('Cover Photo Minimum Width (px)', 'wams'),
				'value' => WAMS()->options()->get('cover_min_width'),
			),
		);

		// Content Restriction settings
		$restricted_posts      = WAMS()->options()->get('restricted_access_post_metabox');
		$restricted_posts_list = '';
		if (!empty($restricted_posts)) {
			foreach ($restricted_posts as $key => $posts) {
				$restricted_posts_list = empty($restricted_posts_list) ? $key : $restricted_posts_list . ', ' . $key;
			}
		}
		$restricted_taxonomy      = WAMS()->options()->get('restricted_access_taxonomy_metabox');
		$restricted_taxonomy_list = '';
		if (!empty($restricted_taxonomy)) {
			foreach ($restricted_taxonomy as $key => $posts) {
				$restricted_taxonomy_list = empty($restricted_taxonomy_list) ? $key : $restricted_taxonomy_list . ', ' . $key;
			}
		}

		$accessible = absint(WAMS()->options()->get('accessible'));

		$restrict_settings = array(
			'um-accessible' => array(
				'label' => __('Global Site Access', 'wams'),
				'value' => 0 === $accessible ? __('Site accessible to Everyone', 'wams') : __('Site accessible to Logged In Users', 'wams'),
			),
		);

		if (2 === $accessible) {
			$exclude_uris      = WAMS()->options()->get('access_exclude_uris');
			$exclude_uris_list = '';
			if (!empty($exclude_uris)) {
				$exclude_uris_list = implode(', ', $exclude_uris);
			}
			$restrict_settings['um-access_redirect']          = array(
				'label' => __('Custom Redirect URL', 'wams'),
				'value' => WAMS()->options()->get('access_redirect'),
			);
			$restrict_settings['um-access_exclude_uris']      = array(
				'label' => __('Exclude the following URLs', 'wams'),
				'value' => $exclude_uris_list,
			);
			$restrict_settings['um-home_page_accessible']     = array(
				'label' => __('Allow Homepage to be accessible', 'wams'),
				'value' => WAMS()->options()->get('home_page_accessible') ? $labels['yes'] : $labels['no'],
			);
			$restrict_settings['um-category_page_accessible'] = array(
				'label' => __('Allow Category pages to be accessible', 'wams'),
				'value' => WAMS()->options()->get('category_page_accessible') ? $labels['yes'] : $labels['no'],
			);
		}

		$restrict_settings['um-restricted_post_title_replace'] = array(
			'label' => __('Restricted Content Titles', 'wams'),
			'value' => WAMS()->options()->get('restricted_post_title_replace') ? $labels['yes'] : $labels['no'],
		);
		if (WAMS()->options()->get('restricted_post_title_replace')) {
			$restrict_settings['um-restricted_access_post_title'] = array(
				'label' => __('Restricted Content Title Text', 'wams'),
				'value' => stripslashes(WAMS()->options()->get('restricted_access_post_title')),
			);
		}

		$restrict_settings['um-restricted_access_message'] = array(
			'label' => __('Restricted Access Message', 'wams'),
			'value' => stripslashes(WAMS()->options()->get('restricted_access_message')),
		);
		$restrict_settings['um-restricted_blocks']         = array(
			'label' => __('Enable the "Content Restriction" settings for the Gutenberg Blocks', 'wams'),
			'value' => WAMS()->options()->get('restricted_blocks') ? $labels['yes'] : $labels['no'],
		);
		if (WAMS()->options()->get('restricted_blocks')) {
			$restrict_settings['um-restricted_block_message'] = array(
				'label' => __('Restricted Access Block Message', 'wams'),
				'value' => stripslashes(WAMS()->options()->get('restricted_block_message')),
			);
		}
		$restrict_settings['um-restricted_access_post_metabox']     = array(
			'label' => __('Enable the "Content Restriction" settings for post types', 'wams'),
			'value' => $restricted_posts_list,
		);
		$restrict_settings['um-restricted_access_taxonomy_metabox'] = array(
			'label' => __('Enable the "Content Restriction" settings for taxonomies', 'wams'),
			'value' => $restricted_taxonomy_list,
		);

		// Access other settings
		$blocked_emails    = str_replace('<br />', ', ', nl2br(WAMS()->options()->get('blocked_emails')));
		$blocked_words     = str_replace('<br />', ', ', nl2br(WAMS()->options()->get('blocked_words')));
		$allowed_callbacks = str_replace('<br />', ', ', nl2br(WAMS()->options()->get('allowed_choice_callbacks')));

		$access_other_settings = array(
			'um-enable_reset_password_limit' => array(
				'label' => __('Enable the Reset Password Limit?', 'wams'),
				'value' => WAMS()->options()->get('enable_reset_password_limit') ? $labels['yes'] : $labels['no'],
			),
		);
		if (1 === absint(WAMS()->options()->get('enable_reset_password_limit'))) {
			$access_other_settings['um-reset_password_limit_number'] = array(
				'label' => __('Reset Password Limit ', 'wams'),
				'value' => WAMS()->options()->get('reset_password_limit_number'),
			);
		}
		$access_other_settings['um-change_password_request_limit'] = array(
			'label' => __('Change Password request limit ', 'wams'),
			'value' => WAMS()->options()->get('change_password_request_limit'),
		);
		$access_other_settings['um-blocked_emails']                = array(
			'label' => __('Blocked Email Addresses', 'wams'),
			'value' => stripslashes($blocked_emails),
		);
		$access_other_settings['um-blocked_words']                 = array(
			'label' => __('Blacklist Words', 'wams'),
			'value' => stripslashes($blocked_words),
		);
		$access_other_settings['um-allowed_choice_callbacks']      = array(
			'label' => __('Allowed Choice Callbacks', 'wams'),
			'value' => stripslashes($allowed_callbacks),
		);
		$access_other_settings['um-allow_url_redirect_confirm']    = array(
			'label' => __('Allow external link redirect confirm ', 'wams'),
			'value' => WAMS()->options()->get('allow_url_redirect_confirm') ? $labels['yes'] : $labels['no'],
		);

		// Email settings
		$email_settings = array(
			'um-admin_email'    => array(
				'label' => __('Admin E-mail Address', 'wams'),
				'value' => WAMS()->options()->get('admin_email'),
			),
			'um-mail_from'      => array(
				'label' => __('Mail appears from', 'wams'),
				'value' => WAMS()->options()->get('mail_from'),
			),
			'um-mail_from_addr' => array(
				'label' => __('Mail appears from address', 'wams'),
				'value' => WAMS()->options()->get('mail_from_addr'),
			),
			'um-email_html'     => array(
				'label' => __('Use HTML for E-mails?', 'wams'),
				'value' => WAMS()->options()->get('email_html') ? $labels['yes'] : $labels['no'],
			),
		);

		$emails = WAMS()->config()->email_notifications;
		foreach ($emails as $key => $email) {
			if (WAMS()->options()->get($key . '_on')) {
				$email_settings['um-' . $key] = array(
					// translators: %s is email template title.
					'label' => sprintf(__('"%s" Subject', 'wams'), $email['title']),
					'value' => WAMS()->options()->get($key . '_sub'),
				);

				$email_settings['um-theme_' . $key] = array(
					// translators: %s is email template title.
					'label' => sprintf(__('Template "%s" in theme?', 'wams'), $email['title']),
					'value' => '' !== locate_template(array('wams/emails/' . $key . '.php')) ? $labels['yes'] : $labels['no'],
				);
			}
		}

		// Appearance settings.
		// > Profile section.
		$icons_display_options       = array(
			'field' => __('Show inside text field', 'wams'),
			'label' => __('Show with label', 'wams'),
			'off'   => __('Turn off', 'wams'),
		);
		$profile_header_menu_options = array(
			'bc' => __('Bottom of Icon', 'wams'),
			'lc' => __('Left of Icon (right for RTL)', 'wams'),
		);

		$profile_templates      = WAMS()->shortcodes()->get_templates('profile');
		$profile_template_key   = WAMS()->options()->get('profile_template');
		$profile_template_title = array_key_exists($profile_template_key, $profile_templates) ? $profile_templates[$profile_template_key] : __('No template name', 'wams');
		$profile_secondary_btn  = WAMS()->options()->get('profile_secondary_btn');
		$profile_cover_enabled  = WAMS()->options()->get('profile_cover_enabled');
		$profile_empty_text     = WAMS()->options()->get('profile_empty_text');

		$appearance_settings = array(
			'um-profile_template'         => array(
				'label' => __('Profile Default Template', 'wams'),
				// translators: %1$s - profile template name, %2$s - profile template filename
				'value' => sprintf(__('%1$s (filename: %2$s.php)', 'wams'), $profile_template_title, $profile_template_key),
			),
			'um-profile_max_width'        => array(
				'label' => __('Profile Maximum Width', 'wams'),
				'value' => WAMS()->options()->get('profile_max_width'),
			),
			'um-profile_area_max_width'   => array(
				'label' => __('Profile Area Maximum Width', 'wams'),
				'value' => WAMS()->options()->get('profile_area_max_width'),
			),
			'um-profile_icons'            => array(
				'label' => __('Profile Field Icons', 'wams'),
				'value' => $icons_display_options[WAMS()->options()->get('profile_icons')],
			),
			'um-profile_primary_btn_word' => array(
				'label' => __('Profile Primary Button Text', 'wams'),
				'value' => WAMS()->options()->get('profile_primary_btn_word'),
			),
			'um-profile_secondary_btn'    => array(
				'label' => __('Profile Secondary Button', 'wams'),
				'value' => $profile_secondary_btn ? $labels['yes'] : $labels['no'],
			),
		);
		if (!empty($profile_secondary_btn)) {
			$appearance_settings['um-profile_secondary_btn_word'] = array(
				'label' => __('Profile Secondary Button Text ', 'wams'),
				'value' => WAMS()->options()->get('profile_secondary_btn_word'),
			);
		}

		$default_avatar = WAMS()->options()->get('default_avatar');
		$default_cover  = WAMS()->options()->get('default_cover');

		$appearance_settings['um-default_avatar']               = array(
			'label' => __('Default Profile Photo', 'wams'),
			'value' => !empty($default_avatar['url']) ? $default_avatar['url'] : '',
		);
		$appearance_settings['um-default_cover']                = array(
			'label' => __('Default Cover Photo', 'wams'),
			'value' => !empty($default_cover['url']) ? $default_cover['url'] : '',
		);
		$appearance_settings['um-disable_profile_photo_upload'] = array(
			'label' => __('Disable Profile Photo Upload', 'wams'),
			'value' => WAMS()->options()->get('disable_profile_photo_upload') ? $labels['yes'] : $labels['no'],
		);
		$appearance_settings['um-profile_photosize']            = array(
			'label' => __('Profile Photo Size', 'wams'),
			'value' => WAMS()->options()->get('profile_photosize') . 'x' . WAMS()->options()->get('profile_photosize') . 'px',
		);
		$appearance_settings['um-profile_cover_enabled']        = array(
			'label' => __('Profile Cover Photos', 'wams'),
			'value' => $profile_cover_enabled ? $labels['yes'] : $labels['no'],
		);
		if (!empty($profile_cover_enabled)) {
			$appearance_settings['um-profile_coversize']   = array(
				'label' => __('Profile Cover Size', 'wams'),
				'value' => WAMS()->options()->get('profile_coversize') . 'px',
			);
			$appearance_settings['um-profile_cover_ratio'] = array(
				'label' => __('Profile Cover Ratio', 'wams'),
				'value' => WAMS()->options()->get('profile_cover_ratio'),
			);
		}
		$appearance_settings['um-profile_show_metaicon']     = array(
			'label' => __('Profile Header Meta Text Icon', 'wams'),
			'value' => WAMS()->options()->get('profile_show_metaicon') ? $labels['yes'] : $labels['no'],
		);
		$appearance_settings['um-profile_show_name']         = array(
			'label' => __('Show display name in profile header', 'wams'),
			'value' => WAMS()->options()->get('profile_show_name') ? $labels['yes'] : $labels['no'],
		);
		$appearance_settings['um-profile_show_social_links'] = array(
			'label' => __('Show social links in profile header', 'wams'),
			'value' => WAMS()->options()->get('profile_show_social_links') ? $labels['yes'] : $labels['no'],
		);
		$appearance_settings['um-profile_show_bio']          = array(
			'label' => __('Show user description in header', 'wams'),
			'value' => WAMS()->options()->get('profile_show_bio') ? $labels['yes'] : $labels['no'],
		);
		$appearance_settings['um-profile_show_html_bio']     = array(
			'label' => __('Enable HTML support for user description', 'wams'),
			'value' => WAMS()->options()->get('profile_show_html_bio') ? $labels['yes'] : $labels['no'],
		);
		$appearance_settings['um-profile_bio_maxchars']      = array(
			'label' => __('User description maximum chars', 'wams'),
			'value' => WAMS()->options()->get('profile_bio_maxchars'),
		);
		$appearance_settings['um-profile_header_menu']       = array(
			'label' => __('Profile Header Menu Position', 'wams'),
			'value' => $profile_header_menu_options[WAMS()->options()->get('profile_header_menu')],
		);
		$appearance_settings['um-profile_empty_text']        = array(
			'label' => __('Show a custom message if profile is empty', 'wams'),
			'value' => $profile_empty_text ? $labels['yes'] : $labels['no'],
		);
		if (!empty($profile_empty_text)) {
			$appearance_settings['um-profile_empty_text_emo'] = array(
				'label' => __('Show the emoticon', 'wams'),
				'value' => WAMS()->options()->get('profile_empty_text_emo') ? $labels['yes'] : $labels['no'],
			);
		}

		// > Profile Menu section.
		$profile_menu = WAMS()->options()->get('profile_menu');

		$appearance_settings['um-profile_menu'] = array(
			'label' => __('Enable profile menu', 'wams'),
			'value' => $profile_menu ? $labels['yes'] : $labels['no'],
		);

		if (!empty($profile_menu)) {
			/**
			 * Filters a privacy list extend.
			 *
			 * @since 2.7.0
			 * @hook wams_profile_tabs_privacy_list
			 *
			 * @param {array} $privacy_option Add options for profile tabs' privacy.
			 *
			 * @return {array} Options for profile tabs' privacy.
			 *
			 * @example <caption>Add options for profile tabs' privacy.</caption>
			 * function wams_profile_menu_link_attrs( $privacy_option ) {
			 *     // your code here
			 *     return $privacy_option;
			 * }
			 * add_filter( 'wams_profile_tabs_privacy_list', 'wams_profile_tabs_privacy_list', 10, 1 );
			 */
			$privacy_option = WAMS()->profile()->tabs_privacy();

			$tabs = WAMS()->profile()->tabs();
			foreach ($tabs as $id => $tab) {
				if (!empty($tab['hidden'])) {
					continue;
				}

				$tab_enabled = WAMS()->options()->get('profile_tab_' . $id);

				$appearance_settings['um-profile_tab_' . $id] = array(
					// translators: %s Profile Tab Title
					'label' => sprintf(__('%s Tab', 'wams'), $tab['name']),
					'value' => $tab_enabled ? $labels['yes'] : $labels['no'],
				);

				if (!isset($tab['default_privacy']) && !empty($tab_enabled)) {
					$privacy = WAMS()->options()->get('profile_tab_' . $id . '_privacy');
					if (is_numeric($privacy)) {
						$appearance_settings['um-profile_tab_' . $id . '_privacy'] = array(
							// translators: %s Profile Tab Title
							'label' => sprintf(__('Who can see %s Tab?', 'wams'), $tab['name']),
							'value' => $privacy_option[WAMS()->options()->get('profile_tab_' . $id . '_privacy')],
						);
					}
				}
			}
			/**
			 * Filters appearance settings for Site Health extend.
			 *
			 * @since 2.7.0
			 * @hook wams_profile_tabs_site_health
			 *
			 * @param {array} $appearance_settings Appearance settings for Site Health.
			 *
			 * @return {array} Appearance settings for Site Health.
			 *
			 * @example <caption>Add options for appearance settings for Site Health.</caption>
			 * function wams_profile_tabs_site_health( $appearance_settings ) {
			 *     // your code here
			 *     return $appearance_settings;
			 * }
			 * add_filter( 'wams_profile_tabs_site_health', 'wams_profile_tabs_site_health', 10, 1 );
			 */
			$appearance_settings = apply_filters('wams_profile_tabs_site_health', $appearance_settings);

			/**
			 * Filters user profile tabs
			 *
			 * @since 2.7.0
			 * @hook wams_profile_tabs
			 *
			 * @param {array} $tabs tabs list.
			 *
			 * @return {array} tabs list.
			 *
			 * @example <caption>Add options for profile tabs' privacy.</caption>
			 * function wams_profile_tabs( $tabs ) {
			 *     // your code here
			 *     return $tabs;
			 * }
			 * add_filter( 'wams_profile_tabs', 'wams_profile_tabs', 10, 1 );
			 */
			$tabs_options = apply_filters(
				'wams_profile_tabs',
				array(
					'main'     => array(
						'name' => __('About', 'wams'),
						'icon' => 'um-faicon-user',
					),
					'posts'    => array(
						'name' => __('Posts', 'wams'),
						'icon' => 'um-faicon-pencil',
					),
					'comments' => array(
						'name' => __('Comments', 'wams'),
						'icon' => 'um-faicon-comment',
					),
				)
			);

			$appearance_settings['um-profile_menu_default_tab'] = array(
				'label' => __('Profile menu default tab', 'wams'),
				'value' => $tabs_options[WAMS()->options()->get('profile_menu_default_tab')],
			);
			$appearance_settings['um-profile_menu_icons']       = array(
				'label' => __('Enable menu icons in desktop view', 'wams'),
				'value' => WAMS()->options()->get('profile_menu_icons') ? $labels['yes'] : $labels['no'],
			);
		}

		// > Registration Form section.
		$register_templates      = WAMS()->shortcodes()->get_templates('register');
		$register_template_key   = WAMS()->options()->get('register_template');
		$register_template_title = array_key_exists($register_template_key, $register_templates) ? $register_templates[$register_template_key] : __('No template name', 'wams');
		$register_secondary_btn  = WAMS()->options()->get('register_secondary_btn');

		$form_align_options = array(
			'center' => __('Centered', 'wams'),
			'left'   => __('Left aligned', 'wams'),
			'right'  => __('Right aligned', 'wams'),
		);

		$appearance_settings['um-register_template']         = array(
			'label' => __('Registration Default Template', 'wams'),
			// translators: %1$s - register template name, %2$s - register template filename
			'value' => sprintf(__('%1$s (filename: %2$s.php)', 'wams'), $register_template_title, $register_template_key),
		);
		$appearance_settings['um-register_max_width']        = array(
			'label' => __('Registration Maximum Width', 'wams'),
			'value' => WAMS()->options()->get('register_max_width'),
		);
		$appearance_settings['um-register_align']            = array(
			'label' => __('Registration Shortcode Alignment', 'wams'),
			'value' => $form_align_options[WAMS()->options()->get('register_align')],
		);
		$appearance_settings['um-register_icons']            = array(
			'label' => __('Registration Field Icons', 'wams'),
			'value' => $icons_display_options[WAMS()->options()->get('register_icons')],
		);
		$appearance_settings['um-register_primary_btn_word'] = array(
			'label' => __('Registration Primary Button Text ', 'wams'),
			'value' => WAMS()->options()->get('register_primary_btn_word'),
		);
		$appearance_settings['um-register_secondary_btn']    = array(
			'label' => __('Registration Secondary Button', 'wams'),
			'value' => $register_secondary_btn ? $labels['yes'] : $labels['no'],
		);
		if (!empty($register_secondary_btn)) {
			$appearance_settings['um-register_secondary_btn_word'] = array(
				'label' => __('Registration Secondary Button Text', 'wams'),
				'value' => WAMS()->options()->get('register_secondary_btn_word'),
			);
			$appearance_settings['um-register_secondary_btn_url']  = array(
				'label' => __('Registration Secondary Button URL', 'wams'),
				'value' => WAMS()->options()->get('register_secondary_btn_url'),
			);
		}
		$appearance_settings['um-register_role'] = array(
			'label' => __('Registration Default Role', 'wams'),
			'value' => !empty(WAMS()->options()->get('register_role')) ? WAMS()->options()->get('register_role') : __('Default', 'wams'),
		);

		// > Login Form section.
		$login_templates      = WAMS()->shortcodes()->get_templates('login');
		$login_template_key   = WAMS()->options()->get('login_template');
		$login_template_title = array_key_exists($login_template_key, $login_templates) ? $login_templates[$login_template_key] : __('No template name', 'wams');
		$login_secondary_btn  = WAMS()->options()->get('login_secondary_btn');

		$appearance_settings['um-login_template']         = array(
			'label' => __('Login Default Template', 'wams'),
			// translators: %1$s - login template name, %2$s - login template filename
			'value' => sprintf(__('%1$s (filename: %2$s.php)', 'wams'), $login_template_title, $login_template_key),
		);
		$appearance_settings['um-login_max_width']        = array(
			'label' => __('Login Maximum Width', 'wams'),
			'value' => WAMS()->options()->get('login_max_width'),
		);
		$appearance_settings['um-login_align']            = array(
			'label' => __('Login Shortcode Alignment', 'wams'),
			'value' => $form_align_options[WAMS()->options()->get('login_align')],
		);
		$appearance_settings['um-login_icons']            = array(
			'label' => __('Login Field Icons', 'wams'),
			'value' => $icons_display_options[WAMS()->options()->get('login_icons')],
		);
		$appearance_settings['um-login_primary_btn_word'] = array(
			'label' => __('Login Primary Button Text', 'wams'),
			'value' => WAMS()->options()->get('login_primary_btn_word'),
		);
		$appearance_settings['um-login_secondary_btn']    = array(
			'label' => __('Login Secondary Button', 'wams'),
			'value' => $login_secondary_btn ? $labels['yes'] : $labels['no'],
		);
		if (!empty($login_secondary_btn)) {
			$appearance_settings['um-login_secondary_btn_word'] = array(
				'label' => __('Login Secondary Button Text', 'wams'),
				'value' => WAMS()->options()->get('login_secondary_btn_word'),
			);
			$appearance_settings['um-login_secondary_btn_url']  = array(
				'label' => __('Login Secondary Button URL', 'wams'),
				'value' => WAMS()->options()->get('login_secondary_btn_url'),
			);
		}
		$appearance_settings['um-login_forgot_pass_link'] = array(
			'label' => __('Login Forgot Password Link', 'wams'),
			'value' => WAMS()->options()->get('login_forgot_pass_link') ? $labels['yes'] : $labels['no'],
		);
		$appearance_settings['um-login_show_rememberme']  = array(
			'label' => __('Show "Remember Me"', 'wams'),
			'value' => WAMS()->options()->get('login_show_rememberme') ? $labels['yes'] : $labels['no'],
		);

		// Misc settings.
		$misc_settings = array(
			'um-form_asterisk'                   => array(
				'label' => __('Show an asterisk for required fields', 'wams'),
				'value' => WAMS()->options()->get('form_asterisk') ? $labels['yes'] : $labels['no'],
			),
			'um-profile_title'                   => array(
				'label' => __('User Profile Title', 'wams'),
				'value' => stripslashes(WAMS()->options()->get('profile_title')),
			),
			'um-profile_desc'                    => array(
				'label' => __('User Profile Dynamic Meta Description', 'wams'),
				'value' => stripslashes(WAMS()->options()->get('profile_desc')),
			),
			'um-wams_profile_object_cache_stop'    => array(
				'label' => __('Disable Cache User Profile', 'wams'),
				'value' => WAMS()->options()->get('wams_profile_object_cache_stop') ? $labels['yes'] : $labels['no'],
			),
			'um-enable_blocks'                   => array(
				'label' => __('Enable Gutenberg Blocks', 'wams'),
				'value' => WAMS()->options()->get('enable_blocks') ? $labels['yes'] : $labels['no'],
			),
			'um-rest_api_version'                => array(
				'label' => __('REST API version', 'wams'),
				'value' => WAMS()->options()->get('rest_api_version'),
			),
			'um-disable_restriction_pre_queries' => array(
				'label' => __('Disable pre-queries for restriction content logic (advanced)', 'wams'),
				'value' => WAMS()->options()->get('disable_restriction_pre_queries') ? $labels['yes'] : $labels['no'],
			),
			'um-member_directory_own_table'      => array(
				'label' => __('Enable custom table for usermeta', 'wams'),
				'value' => WAMS()->options()->get('member_directory_own_table') ? $labels['yes'] : $labels['no'],
			),
			'um-uninstall_on_delete'             => array(
				'label' => __('Remove Data on Uninstall?', 'wams'),
				'value' => WAMS()->options()->get('uninstall_on_delete') ? $labels['yes'] : $labels['no'],
			),
		);

		// Secure settings
		$secure_ban_admins_accounts = WAMS()->options()->get('secure_ban_admins_accounts');

		$banned_capabilities_opt = WAMS()->options()->get('banned_capabilities');
		$banned_capabilities     = array();
		if (!empty($banned_capabilities_opt)) {
			if (is_string($banned_capabilities_opt)) {
				$banned_capabilities = array($banned_capabilities_opt);
			} else {
				$banned_capabilities = $banned_capabilities_opt;
			}
		}

		$secure_settings = array(
			'um-banned_capabilities'        => array(
				'label' => __('Banned Administrative Capabilities', 'wams'),
				'value' => !empty($banned_capabilities) ? implode(', ', $banned_capabilities) : '',
			),
			'um-lock_register_forms'        => array(
				'label' => __('Lock All Register Forms', 'wams'),
				'value' => WAMS()->options()->get('lock_register_forms') ? $labels['yes'] : $labels['no'],
			),
			'um-display_login_form_notice'  => array(
				'label' => __('Display Login form notice to reset passwords', 'wams'),
				'value' => WAMS()->options()->get('display_login_form_notice') ? $labels['yes'] : $labels['no'],
			),
			'um-secure_ban_admins_accounts' => array(
				'label' => __('Enable ban for administrative capabilities', 'wams'),
				'value' => $secure_ban_admins_accounts ? $labels['yes'] : $labels['no'],
			),
		);
		if (!empty($secure_ban_admins_accounts)) {
			$secure_notify_admins_banned_accounts = WAMS()->options()->get('secure_notify_admins_banned_accounts');

			$secure_settings['um-secure_notify_admins_banned_accounts'] = array(
				'label' => __('Notify Administrators', 'wams'),
				'value' => $secure_notify_admins_banned_accounts ? $labels['yes'] : $labels['no'],
			);
			if (!empty($secure_notify_admins_banned_accounts)) {
				$secure_notify_admins_banned_accounts_options = array(
					'instant' => __('Send Immediately', 'wams'),
					'hourly'  => __('Hourly', 'wams'),
					'daily'   => __('Daily', 'wams'),
				);

				$secure_settings['um-secure_notify_admins_banned_accounts__interval'] = array(
					'label' => __('Notification Schedule', 'wams'),
					'value' => $secure_notify_admins_banned_accounts_options[WAMS()->options()->get('secure_notify_admins_banned_accounts__interval')],
				);
			}
		}

		$secure_allowed_redirect_hosts = WAMS()->options()->get('secure_allowed_redirect_hosts');
		$secure_allowed_redirect_hosts = explode(PHP_EOL, $secure_allowed_redirect_hosts);

		$secure_settings['um-secure_allowed_redirect_hosts'] = array(
			'label' => __('Allowed hosts for safe redirect', 'wams'),
			'value' => $secure_allowed_redirect_hosts,
		);

		// Licenses settings.
		$license_settings = array(
			'um-licenses' => array(
				'label' => __('Licenses', 'wams'),
				'value' => array(),
			),
		);

		/**
		 * Filters licenses settings for Site Health.
		 *
		 * @since 2.7.0
		 * @hook wams_licenses_site_health
		 *
		 * @param {array} $license_settings licenses settings for Site Health.
		 *
		 * @return {array} licenses settings for Site Health.
		 *
		 * @example <caption>Extend licenses settings for Site Health.</caption>
		 * function wams_licenses_site_health( $license_settings ) {
		 *     // your code here
		 *     return $license_settings;
		 * }
		 * add_filter( 'wams_licenses_site_health', 'wams_licenses_site_health', 10, 1 );
		 */
		$license_settings = apply_filters('wams_licenses_site_health', $license_settings);

		$info['wams']['fields'] = array_merge($info['wams']['fields'], $pages_settings, $user_settings, $account_settings, $uploads_settings, $restrict_settings, $access_other_settings, $email_settings, $appearance_settings, $misc_settings, $secure_settings, $license_settings);

		// User roles settings
		$roles_array = array();
		foreach ($this->get_roles() as $key => $role) {
			if (strpos($key, 'wams_') === 0) {
				$key = substr($key, 3);
			}
			$rolemeta = $this->get_role_meta($key);
			if (false === $rolemeta) {
				continue;
			}
			$priority = !empty($rolemeta['_wams_priority']) ? $rolemeta['_wams_priority'] : 0;

			$k                 = $priority . '-' . $role;
			$roles_array[$k] = $role . '(' . $priority . ')';

			krsort($roles_array, SORT_NUMERIC);
		}

		$info['wams-user-roles'] = array(
			'label'       => __('User roles', 'wams'),
			'description' => __('This debug information about user roles.', 'wams'),
			'fields'      => array(
				'um-roles'         => array(
					'label' => __('User Roles (priority)', 'wams'),
					'value' => implode(', ', $roles_array),
				),
				'um-register_role' => array(
					'label' => __('WordPress Default New User Role', 'wams'),
					'value' => get_option('default_role'),
				),
			),
		);

		foreach ($this->get_roles() as $key => $role) {
			if (strpos($key, 'wams_') === 0) {
				$key = substr($key, 3);
			}

			$rolemeta = $this->get_role_meta($key);
			if (false === $rolemeta) {
				continue;
			}

			$info['wams-' . $key] = array(
				'label'       => ' - ' . $role . __(' role settings', 'wams'),
				'description' => __('This debug information about user role.', 'wams'),
				'fields'      => array(),
			);

			if (array_key_exists('_wams_can_access_wpadmin', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_access_wpadmin' => array(
							'label' => __('Can access wp-admin?', 'wams'),
							'value' => $rolemeta['_wams_can_access_wpadmin'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_not_see_adminbar', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_not_see_adminbar' => array(
							'label' => __('Force hiding adminbar in frontend?', 'wams'),
							'value' => $rolemeta['_wams_can_not_see_adminbar'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_edit_everyone', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_edit_everyone' => array(
							'label' => __('Can edit other member accounts?', 'wams'),
							'value' => $rolemeta['_wams_can_edit_everyone'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_edit_everyone', $rolemeta) && 1 === absint($rolemeta['_wams_can_edit_everyone'])) {
				$can_edit_roles_meta = !empty($rolemeta['_wams_can_edit_roles']) ? $rolemeta['_wams_can_edit_roles'] : array();
				$can_edit_roles      = array();
				if (!empty($can_edit_roles_meta)) {
					if (is_string($can_edit_roles_meta)) {
						$can_edit_roles = array($can_edit_roles_meta);
					} else {
						$can_edit_roles = $can_edit_roles_meta;
					}
				}

				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_edit_roles' => array(
							'label' => __('Can edit these user roles only', 'wams'),
							'value' => !empty($can_edit_roles) ? implode(', ', $can_edit_roles) : $labels['all'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_delete_everyone', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_delete_everyone' => array(
							'label' => __('Can delete other member accounts?', 'wams'),
							'value' => $rolemeta['_wams_can_delete_everyone'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_delete_everyone', $rolemeta) && 1 === absint($rolemeta['_wams_can_delete_everyone'])) {
				$can_delete_roles_meta = !empty($rolemeta['_wams_can_delete_roles']) ? $rolemeta['_wams_can_delete_roles'] : array();
				$can_delete_roles      = array();
				if (!empty($can_delete_roles_meta)) {
					if (is_string($can_delete_roles_meta)) {
						$can_delete_roles = array($can_delete_roles_meta);
					} else {
						$can_delete_roles = $can_delete_roles_meta;
					}
				}

				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_delete_roles' => array(
							'label' => __('Can delete these user roles only', 'wams'),
							'value' => !empty($can_delete_roles) ? implode(', ', $can_delete_roles) : $labels['all'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_edit_profile', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_edit_profile' => array(
							'label' => __('Can edit their profile?', 'wams'),
							'value' => $rolemeta['_wams_can_edit_profile'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_delete_profile', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_delete_profile' => array(
							'label' => __('Can delete their account?', 'wams'),
							'value' => $rolemeta['_wams_can_delete_profile'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_view_all', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_view_all' => array(
							'label' => __('Can view other member profiles?', 'wams'),
							'value' => $rolemeta['_wams_can_view_all'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_view_all', $rolemeta) && 1 === absint($rolemeta['_wams_can_view_all'])) {
				$can_view_roles_meta = !empty($rolemeta['_wams_can_view_roles']) ? $rolemeta['_wams_can_view_roles'] : array();
				$can_view_roles      = array();
				if (!empty($can_view_roles_meta)) {
					if (is_string($can_view_roles_meta)) {
						$can_view_roles = array($can_view_roles_meta);
					} else {
						$can_view_roles = $can_view_roles_meta;
					}
				}

				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_view_roles' => array(
							'label' => __('Can view these user roles only', 'wams'),
							'value' => !empty($can_view_roles) ? implode(', ', $can_view_roles) : $labels['all'],
						),
					)
				);
			}

			if (isset($rolemeta['_wams_profile_noindex']) && '' !== $rolemeta['_wams_profile_noindex']) {
				$profile_noindex = $rolemeta['_wams_profile_noindex'] ? $labels['yes'] : $labels['no'];
			} else {
				$profile_noindex = __('Default', 'wams');
			}
			if (isset($rolemeta['_wams_default_homepage']) && '' !== $rolemeta['_wams_default_homepage']) {
				$default_homepage = $rolemeta['_wams_default_homepage'] ? $labels['yes'] : $labels['no'];
			} else {
				$default_homepage = __('No such option', 'wams');
			}

			if (array_key_exists('_wams_can_make_private_profile', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_make_private_profile' => array(
							'label' => __('Can make their profile private?', 'wams'),
							'value' => $rolemeta['_wams_can_make_private_profile'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			if (array_key_exists('_wams_can_access_private_profile', $rolemeta)) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-can_access_private_profile' => array(
							'label' => __('Can view/access private profiles?', 'wams'),
							'value' => $rolemeta['_wams_can_access_private_profile'] ? $labels['yes'] : $labels['no'],
						),
					)
				);
			}

			$info['wams-' . $key]['fields'] = array_merge(
				$info['wams-' . $key]['fields'],
				array(
					'um-profile_noindex'  => array(
						'label' => __('Avoid indexing profile by search engines', 'wams'),
						'value' => $profile_noindex,
					),
					'um-default_homepage' => array(
						'label' => __('Can view default homepage?', 'wams'),
						'value' => $default_homepage,
					),
				)
			);

			if (isset($rolemeta['_wams_default_homepage']) && 0 === absint($rolemeta['_wams_default_homepage'])) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-redirect_homepage' => array(
							'label' => __('Custom Homepage Redirect', 'wams'),
							'value' => $rolemeta['_wams_redirect_homepage'],
						),
					)
				);
			}

			$status_options = array(
				'approved'  => __('Auto Approve', 'wams'),
				'checkmail' => __('Require Email Activation', 'wams'),
				'pending'   => __('Require Admin Review', 'wams'),
			);

			if (array_key_exists('_wams_status', $rolemeta) && isset($status_options[$rolemeta['_wams_status']])) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-status' => array(
							'label' => __('Registration Status', 'wams'),
							'value' => $status_options[$rolemeta['_wams_status']],
						),
					)
				);
			}

			if (array_key_exists('_wams_status', $rolemeta) && 'approved' === $rolemeta['_wams_status']) {
				$auto_approve_act = array(
					'redirect_profile' => __('Redirect to profile', 'wams'),
					'redirect_url'     => __('Redirect to URL', 'wams'),
				);

				if (isset($auto_approve_act[$rolemeta['_wams_auto_approve_act']])) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-auto_approve_act' => array(
								'label' => __('Custom Homepage Redirect', 'wams'),
								'value' => $auto_approve_act[$rolemeta['_wams_auto_approve_act']],
							),
						)
					);
				}

				if ('redirect_url' === $rolemeta['_wams_auto_approve_act'] && array_key_exists('_wams_auto_approve_url', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-auto_approve_url' => array(
								'label' => __('Set Custom Redirect URL', 'wams'),
								'value' => $rolemeta['_wams_auto_approve_url'],
							),
						)
					);
				}
			}

			if (array_key_exists('_wams_status', $rolemeta) && 'checkmail' === $rolemeta['_wams_status']) {
				$checkmail_action = array(
					'show_message' => __('Show custom message', 'wams'),
					'redirect_url' => __('Redirect to URL', 'wams'),
				);

				if (array_key_exists('_wams_login_email_activate', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-login_email_activate' => array(
								'label' => __('Login user after validating the activation link?', 'wams'),
								'value' => $rolemeta['_wams_login_email_activate'] ? $labels['yes'] : $labels['no'],
							),
						)
					);
				}

				if (isset($checkmail_action[$rolemeta['_wams_checkmail_action']])) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-checkmail_action' => array(
								'label' => __('Action to be taken after registration', 'wams'),
								'value' => $checkmail_action[$rolemeta['_wams_checkmail_action']],
							),
						)
					);
				}

				if ('show_message' === $rolemeta['_wams_checkmail_action']) {
					if (array_key_exists('_wams_checkmail_message', $rolemeta)) {
						$info['wams-' . $key]['fields'] = array_merge(
							$info['wams-' . $key]['fields'],
							array(
								'um-checkmail_message' => array(
									'label' => __('Personalize the custom message', 'wams'),
									'value' => stripslashes($rolemeta['_wams_checkmail_message']),
								),
							)
						);
					}
				} else {
					if (array_key_exists('_wams_checkmail_url', $rolemeta)) {
						$info['wams-' . $key]['fields'] = array_merge(
							$info['wams-' . $key]['fields'],
							array(
								'um-checkmail_url' => array(
									'label' => __('Set Custom Redirect URL', 'wams'),
									'value' => $rolemeta['_wams_checkmail_url'],
								),
							)
						);
					}
				}

				if (array_key_exists('_wams_url_email_activate', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-url_email_activate' => array(
								'label' => __('URL redirect after e-mail activation', 'wams'),
								'value' => $rolemeta['_wams_url_email_activate'],
							),
						)
					);
				}
			}

			if (array_key_exists('_wams_status', $rolemeta) && 'pending' === $rolemeta['_wams_status']) {
				$pending_action = array(
					'show_message' => __('Show custom message', 'wams'),
					'redirect_url' => __('Redirect to URL', 'wams'),
				);

				if (array_key_exists('_wams_pending_action', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-pending_action' => array(
								'label' => __('Action to be taken after registration', 'wams'),
								'value' => $pending_action[$rolemeta['_wams_pending_action']],
							),
						)
					);
				}

				if ('show_message' === $rolemeta['_wams_pending_action']) {
					if (array_key_exists('_wams_pending_message', $rolemeta)) {
						$info['wams-' . $key]['fields'] = array_merge(
							$info['wams-' . $key]['fields'],
							array(
								'um-pending_message' => array(
									'label' => __('Personalize the custom message', 'wams'),
									'value' => stripslashes($rolemeta['_wams_pending_message']),
								),
							)
						);
					}
				} else {
					if (array_key_exists('_wams_pending_url', $rolemeta)) {
						$info['wams-' . $key]['fields'] = array_merge(
							$info['wams-' . $key]['fields'],
							array(
								'um-pending_url' => array(
									'label' => __('Set Custom Redirect URL', 'wams'),
									'value' => $rolemeta['_wams_pending_url'],
								),
							)
						);
					}
				}
			}

			$after_login_options = array(
				'redirect_profile' => __('Redirect to profile', 'wams'),
				'redirect_url'     => __('Redirect to URL', 'wams'),
				'refresh'          => __('Refresh active page', 'wams'),
				'redirect_admin'   => __('Redirect to WordPress Admin', 'wams'),
			);

			if (array_key_exists('_wams_after_login', $rolemeta) && isset($after_login_options[$rolemeta['_wams_after_login']])) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-after_login' => array(
							'label' => __('Action to be taken after login', 'wams'),
							'value' => $after_login_options[$rolemeta['_wams_after_login']],
						),
					)
				);
			}

			if (array_key_exists('_wams_login_redirect_url', $rolemeta) && 'redirect_url' === $rolemeta['_wams_login_redirect_url']) {
				if (array_key_exists('_wams_pending_url', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-login_redirect_url' => array(
								'label' => __('Set Custom Redirect URL', 'wams'),
								'value' => $rolemeta['_wams_login_redirect_url'],
							),
						)
					);
				}
			}

			$redirect_options = array(
				'redirect_home' => __('Go to Homepage', 'wams'),
				'redirect_url'  => __('Go to Custom URL', 'wams'),
			);
			if (!isset($rolemeta['_wams_after_logout'])) {
				$rolemeta['_wams_after_logout'] = 'redirect_home';
			}
			if (array_key_exists('_wams_after_logout', $rolemeta) && isset($redirect_options[$rolemeta['_wams_after_logout']])) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-after_logout' => array(
							'label' => __('Action to be taken after logout', 'wams'),
							'value' => $redirect_options[$rolemeta['_wams_after_logout']],
						),
					)
				);
			}

			if ('redirect_url' === $rolemeta['_wams_after_logout']) {
				if (array_key_exists('_wams_logout_redirect_url', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-logout_redirect_url' => array(
								'label' => __('Set Custom Redirect URL', 'wams'),
								'value' => $rolemeta['_wams_logout_redirect_url'],
							),
						)
					);
				}
			}

			if (!isset($rolemeta['_wams_after_delete'])) {
				$rolemeta['_wams_after_delete'] = 'redirect_home';
			}
			if (array_key_exists('_wams_after_delete', $rolemeta) && isset($redirect_options[$rolemeta['_wams_after_delete']])) {
				$info['wams-' . $key]['fields'] = array_merge(
					$info['wams-' . $key]['fields'],
					array(
						'um-after_delete' => array(
							'label' => __('Action to be taken after account is deleted', 'wams'),
							'value' => $redirect_options[$rolemeta['_wams_after_delete']],
						),
					)
				);
			}

			if ('redirect_url' === $rolemeta['_wams_after_delete']) {
				if (array_key_exists('_wams_delete_redirect_url', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-delete_redirect_url' => array(
								'label' => __('Set Custom Redirect URL', 'wams'),
								'value' => $rolemeta['_wams_delete_redirect_url'],
							),
						)
					);
				}
			}

			if (!empty($rolemeta['wp_capabilities'])) {
				if (array_key_exists('wp_capabilities', $rolemeta)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-wp_capabilities' => array(
								'label' => __('WP Capabilities', 'wams'),
								'value' => $rolemeta['wp_capabilities'],
							),
						)
					);
				}
			}

			$info = apply_filters('wams_debug_information_user_role', $info, $key);
		}

		// Forms settings
		if (!empty($this->get_forms())) {
			$info['wams-forms'] = array(
				'label'       => __('WAMS Forms', 'wams'),
				'description' => __('This debug information for your WAMS forms.', 'wams'),
				'fields'      => array(
					'um-forms' => array(
						'label' => __('WAMS Forms', 'wams'),
						'value' => $this->get_forms(),
					),
				),
			);

			foreach ($this->get_forms() as $key => $form) {
				if (strpos($key, 'ID#') === 0) {
					$key = substr($key, 3);
				}

				$info['wams-' . $key] = array(
					'label'       => ' - ' . $form . __(' form settings', 'wams'),
					'description' => __('This debug information for your WAMS form.', 'wams'),
					'fields'      => array(
						'um-form-shortcode' => array(
							'label' => __('Shortcode', 'wams'),
							'value' => '[wams form_id="' . $key . '"]',
						),
						'um-mode'           => array(
							'label' => __('Type', 'wams'),
							'value' => get_post_meta($key, '_wams_mode', true),
						),
					),
				);

				if ('register' === get_post_meta($key, '_wams_mode', true)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-register_role'             => array(
								'label' => __('User registration role', 'wams'),
								'value' => 0 === absint(get_post_meta($key, '_wams_register_role', true)) ? $labels['default'] : get_post_meta($key, '_wams_register_role', true),
							),
							'um-register_template'         => array(
								'label' => __('Template', 'wams'),
								'value' => 0 === absint(get_post_meta($key, '_wams_register_template', true)) ? $labels['default'] : get_post_meta($key, '_wams_register_template', true),
							),
							'um-register_primary_btn_word' => array(
								'label' => __('Primary Button Text', 'wams'),
								'value' => !get_post_meta($key, '_wams_register_primary_btn_word', true) ? $labels['default'] : get_post_meta($key, '_wams_register_primary_btn_word', true),
							),
							'um-register_use_gdpr'         => array(
								'label' => __('Enable privacy policy agreement', 'wams'),
								'value' => get_post_meta($key, '_wams_register_use_gdpr', true) ? $labels['yes'] : $labels['no'],
							),
						)
					);

					if (1 === absint(get_post_meta($key, '_wams_register_use_gdpr', true))) {
						$gdpr_content_id = get_post_meta($key, '_wams_register_use_gdpr_content_id', true);

						$info['wams-' . $key]['fields'] = array_merge(
							$info['wams-' . $key]['fields'],
							array(
								'um-register_use_gdpr_content_id' => array(
									'label' => __('Privacy policy content', 'wams'),
									'value' => $gdpr_content_id ? get_the_title($gdpr_content_id) . '(' . $gdpr_content_id . ')' . get_the_permalink($gdpr_content_id) : '',
								),
								'um-register_use_gdpr_toggle_show' => array(
									'label' => __('Toggle Show text', 'wams'),
									'value' => get_post_meta($key, '_wams_register_use_gdpr_toggle_show', true),
								),
								'um-register_use_gdpr_toggle_hide' => array(
									'label' => __('Toggle Hide text', 'wams'),
									'value' => get_post_meta($key, '_wams_register_use_gdpr_toggle_hide', true),
								),
								'um-register_use_gdpr_agreement' => array(
									'label' => __('Checkbox agreement description', 'wams'),
									'value' => get_post_meta($key, '_wams_register_use_gdpr_agreement', true),
								),
								'um-register_use_gdpr_error_text' => array(
									'label' => __('Error Text', 'wams'),
									'value' => get_post_meta($key, '_wams_register_use_gdpr_error_text', true),
								),
							)
						);
					}

					$info = apply_filters('wams_debug_information_register_form', $info, $key);

					$fields = get_post_meta($key, '_wams_custom_fields', true);
					if (!empty($fields) && is_array($fields)) {
						foreach ($fields as $field_key => $field) {
							$field_info = $this->get_field_data($info, $key, $field_key, $field);

							$info['wams-' . $key]['fields'] = array_merge(
								$info['wams-' . $key]['fields'],
								$field_info
							);
						}
					}
				} elseif ('login' === get_post_meta($key, '_wams_mode', true)) {
					$login_redirect_options = array(
						'0'                => __('Default', 'wams'),
						'redirect_profile' => __('Redirect to profile', 'wams'),
						'redirect_url'     => __('Redirect to URL', 'wams'),
						'refresh'          => __('Refresh active page', 'wams'),
						'redirect_admin'   => __('Redirect to WordPress Admin', 'wams'),
					);

					$login_after_login = get_post_meta($key, '_wams_login_after_login', true);
					$login_after_login = '' === $login_after_login ? '0' : $login_after_login;

					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-login_template'         => array(
								'label' => __('Template', 'wams'),
								'value' => 0 === absint(get_post_meta($key, '_wams_login_template', true)) ? $labels['default'] : get_post_meta($key, '_wams_login_template', true),
							),
							'um-login_primary_btn_word' => array(
								'label' => __('Primary Button Text', 'wams'),
								'value' => !get_post_meta($key, '_wams_login_primary_btn_word', true) ? $labels['default'] : get_post_meta($key, '_wams_login_primary_btn_word', true),
							),
							'um-login_forgot_pass_link' => array(
								'label' => __('Show Forgot Password Link?', 'wams'),
								'value' => get_post_meta($key, '_wams_login_forgot_pass_link', true) ? $labels['yes'] : $labels['no'],
							),
							'um-login_show_rememberme'  => array(
								'label' => __('Show "Remember Me"?', 'wams'),
								'value' => get_post_meta($key, '_wams_login_show_rememberme', true) ? $labels['yes'] : $labels['no'],
							),
							'um-login_after_login'      => array(
								'label' => __('Redirection after Login', 'wams'),
								'value' => $login_redirect_options[$login_after_login],
							),
						)
					);

					if ('redirect_url' === get_post_meta($key, '_wams_login_after_login', true)) {
						$info['wams-' . $key]['fields'] = array_merge(
							$info['wams-' . $key]['fields'],
							array(
								'um-login_redirect_url' => array(
									'label' => __('Set Custom Redirect URL', 'wams'),
									'value' => get_post_meta($key, '_wams_login_redirect_url', true),
								),
							)
						);
					}

					$info = apply_filters('wams_debug_information_login_form', $info, $key);

					$fields = get_post_meta($key, '_wams_custom_fields', true);
					if (!empty($fields) && is_array($fields)) {
						foreach ($fields as $field_key => $field) {
							$field_info = $this->get_field_data($info, $key, $field_key, $field);

							$info['wams-' . $key]['fields'] = array_merge(
								$info['wams-' . $key]['fields'],
								$field_info
							);
						}
					}
				} elseif ('profile' === get_post_meta($key, '_wams_mode', true)) {
					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-profile_role'             => array(
								'label' => __('Make this profile form role-specific', 'wams'),
								'value' => !empty(get_post_meta($key, '_wams_profile_role', true)) ? get_post_meta($key, '_wams_profile_role', true) : $labels['all'],
							),
							'um-profile_template'         => array(
								'label' => __('Template', 'wams'),
								'value' => 0 === absint(get_post_meta($key, '_wams_profile_template', true)) ? $labels['default'] : get_post_meta($key, '_wams_profile_template', true),
							),
							'um-profile_primary_btn_word' => array(
								'label' => __('Primary Button Text', 'wams'),
								'value' => !get_post_meta($key, '_wams_profile_primary_btn_word', true) ? $labels['default'] : get_post_meta($key, '_wams_profile_primary_btn_word', true),
							),
							'um-profile_cover_enabled'    => array(
								'label' => __('Enable Cover Photos', 'wams'),
								'value' => get_post_meta($key, '_wams_profile_cover_enabled', true) ? $labels['yes'] : $labels['no'],
							),
							'um-profile_disable_photo_upload' => array(
								'label' => __('Disable Profile Photo Upload', 'wams'),
								'value' => get_post_meta($key, '_wams_profile_disable_photo_upload', true) ? $labels['yes'] : $labels['no'],
							),
						)
					);

					if (0 === absint(get_post_meta($key, '_wams_profile_disable_photo_upload', true))) {
						$info['wams-' . $key]['fields'] = array_merge(
							$info['wams-' . $key]['fields'],
							array(
								'um-profile_photo_required' => array(
									'label' => __('Make Profile Photo Required', 'wams'),
									'value' => get_post_meta($key, '_wams_profile_photo_required', true) ? $labels['yes'] : $labels['no'],
								),
							)
						);
					}

					$info['wams-' . $key]['fields'] = array_merge(
						$info['wams-' . $key]['fields'],
						array(
							'um-profile_show_name'         => array(
								'label' => __('Show display name in profile header?', 'wams'),
								'value' => get_post_meta($key, '_wams_profile_show_name', true) ? $labels['yes'] : $labels['no'],
							),
							'um-profile_show_social_links' => array(
								'label' => __('Show social links in profile header?', 'wams'),
								'value' => get_post_meta($key, '_wams_profile_show_social_links', true) ? $labels['yes'] : $labels['no'],
							),
							'um-profile_show_bio'          => array(
								'label' => __('Show user description in profile header?', 'wams'),
								'value' => get_post_meta($key, '_wams_profile_show_bio', true) ? $labels['yes'] : $labels['no'],
							),
						)
					);

					$fields = get_post_meta($key, '_wams_custom_fields', true);
					if (!empty($fields) && is_array($fields)) {
						foreach ($fields as $field_key => $field) {
							$field_info = $this->get_field_data($info, $key, $field_key, $field);

							$info['wams-' . $key]['fields'] = array_merge(
								$info['wams-' . $key]['fields'],
								$field_info
							);
						}
					}

					$profile_metafields = get_post_meta($key, '_wams_profile_metafields', true);
					if (!empty($profile_metafields) && is_array($profile_metafields)) {
						foreach ($profile_metafields as $k => $field) {
							$info['wams-' . $key]['fields'] = array_merge(
								$info['wams-' . $key]['fields'],
								array(
									'um-profile_metafields-' . $k => array(
										'label' => __('Field to show in user meta', 'wams'),
										'value' => $field,
									),
								)
							);
						}
					}
				}
			}
		}

		// Members directory
		$options = array(
			'country'              => __('Country', 'wams'),
			'gender'               => __('Gender', 'wams'),
			'languages'            => __('Languages', 'wams'),
			'role'                 => __('Roles', 'wams'),
			'birth_date'           => __('Age', 'wams'),
			'last_login'           => __('Last Login', 'wams'),
			'user_registered'      => __('User Registered', 'wams'),
			'first_name'           => __('First Name', 'wams'),
			'last_name'            => __('Last Name', 'wams'),
			'nickname'             => __('Nickname', 'wams'),
			'secondary_user_email' => __('Secondary E-mail Address', 'wams'),
			'description'          => __('Biography', 'wams'),
			'phone_number'         => __('Phone Number', 'wams'),
			'mobile_number'        => __('Mobile Number', 'wams'),
			'role_select'          => __('Roles (Dropdown)', 'wams'),
			'role_radio'           => __('Roles (Radio)', 'wams'),
			'whatsapp'             => __('WhatsApp number', 'wams'),
			'facebook'             => __('Facebook', 'wams'),
			'twitter'              => __('Twitter', 'wams'),
			'viber'                => __('Viber number', 'wams'),
			'skype'                => __('Skype ID', 'wams'),
			'telegram'             => __('Telegram', 'wams'),
			'discord'              => __('Discord', 'wams'),
			'youtube'              => __('Youtube', 'wams'),
			'soundcloud'           => __('SoundCloud', 'wams'),
			'user_registered_desc' => __('New users first', 'wams'),
			'user_registered_asc'  => __('Old users first', 'wams'),
			'username'             => __('Username', 'wams'),
			'display_name'         => __('Display name', 'wams'),
			'last_first_name'      => __('Last & First name', 'wams'),
			'random'               => __('Random', 'wams'),
			'other'                => __('Other (Custom Field)', 'wams'),
		);

		$info['wams-directories'] = array(
			'label'       => __('WAMS Directories', 'wams'),
			'description' => __('This debug information about WAMS directories.', 'wams'),
			'fields'      => array(
				'um-directory' => array(
					'label' => __('Member directories', 'wams'),
					'value' => !empty($this->get_member_directories()) ? $this->get_member_directories() : $labels['no-dir'],
				),
			),
		);

		if (!empty($this->get_member_directories())) {
			foreach ($this->get_member_directories() as $key => $directory) {
				if (0 === strpos($key, 'ID#')) {
					$key = substr($key, 3);
				}

				$_wams_view_types_value = get_post_meta($key, '_wams_view_types', true);
				$_wams_view_types_value = empty($_wams_view_types_value) ? array('grid', 'list') : $_wams_view_types_value;
				$_wams_view_types_value = is_string($_wams_view_types_value) ? array($_wams_view_types_value) : $_wams_view_types_value;

				$info['wams-directory-' . $key] = array(
					'label'       => ' - ' . $directory . __(' directory settings', 'wams'),
					'description' => __('This debug information for your WAMS directory.', 'wams'),
					'fields'      => array(
						'um-directory-shortcode'    => array(
							'label' => __('Shortcode', 'wams'),
							'value' => '[wams_directory id="' . $key . '"]',
						),
						'um-directory_template'     => array(
							'label' => __('Template', 'wams'),
							'value' => get_post_meta($key, '_wams_directory_template', true) ? get_post_meta($key, '_wams_directory_template', true) : $labels['default'],
						),
						'um-directory-view_types'   => array(
							'label' => __('View types', 'wams'),
							'value' => implode(', ', $_wams_view_types_value),
						),
						'um-directory-default_view' => array(
							'label' => __('Default view type', 'wams'),
							'value' => get_post_meta($key, '_wams_default_view', true),
						),
					),
				);

				if (isset($options[get_post_meta($key, '_wams_sortby', true)])) {
					$sortby_label = $options[get_post_meta($key, '_wams_sortby', true)];
				} else {
					$sortby_label = get_post_meta($key, '_wams_sortby', true);
				}

				$directory_roles_meta = get_post_meta($key, '_wams_roles', true);
				$directory_roles      = array();
				if (!empty($directory_roles_meta)) {
					if (is_string($directory_roles_meta)) {
						$directory_roles = array($directory_roles_meta);
					} else {
						$directory_roles = $directory_roles_meta;
					}
				}

				$directory_show_these_users_meta = get_post_meta($key, '_wams_show_these_users', true);
				$show_these_users                = array();
				if (!empty($directory_show_these_users_meta)) {
					if (is_string($directory_show_these_users_meta)) {
						$show_these_users = array($directory_show_these_users_meta);
					} else {
						$show_these_users = $directory_show_these_users_meta;
					}
				}

				$directory_exclude_these_users_meta = get_post_meta($key, '_wams_exclude_these_users', true);
				$exclude_these_users                = array();
				if (!empty($directory_exclude_these_users_meta)) {
					if (is_string($directory_exclude_these_users_meta)) {
						$exclude_these_users = array($directory_exclude_these_users_meta);
					} else {
						$exclude_these_users = $directory_exclude_these_users_meta;
					}
				}

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-roles'               => array(
							'label' => __('User Roles to display', 'wams'),
							'value' => !empty($directory_roles) ? implode(', ', $directory_roles) : $labels['all'],
						),
						'um-directory-has_profile_photo'   => array(
							'label' => __('Only show members who have uploaded a profile photo', 'wams'),
							'value' => get_post_meta($key, '_wams_has_profile_photo', true) ? $labels['yes'] : $labels['no'],
						),
						'um-directory-has_cover_photo'     => array(
							'label' => __('Only show members who have uploaded a profile photo', 'wams'),
							'value' => get_post_meta($key, '_wams_has_cover_photo', true) ? $labels['yes'] : $labels['no'],
						),
						'um-directory-show_these_users'    => array(
							'label' => __('Only show specific users (Enter one username per line)', 'wams'),
							'value' => !empty($show_these_users) ? implode(', ', $show_these_users) : '',
						),
						'um-directory-exclude_these_users' => array(
							'label' => __('Exclude specific users (Enter one username per line)', 'wams'),
							'value' => !empty($exclude_these_users) ? implode(', ', $exclude_these_users) : '',
						),
					)
				);

				$info = apply_filters('wams_debug_member_directory_general_extend', $info, $key);

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-sortby' => array(
							'label' => __('Default sort users by', 'wams'),
							'value' => $sortby_label,
						),
					)
				);

				if ('other' === get_post_meta($key, '_wams_sortby', true)) {
					$info['wams-directory-' . $key]['fields'] = array_merge(
						$info['wams-directory-' . $key]['fields'],
						array(
							'um-directory-enable_sorting' => array(
								'label' => __('Enable custom sorting', 'wams'),
								'value' => get_post_meta($key, '_wams_enable_sorting', true) ? $labels['yes'] : $labels['no'],
							),
							'um-directory-sortby_custom'  => array(
								'label' => __('Custom sorting meta key', 'wams'),
								'value' => get_post_meta($key, '_wams_sortby_custom', true),
							),
							'um-directory-sortby_custom_label' => array(
								'label' => __('Label of custom sort', 'wams'),
								'value' => get_post_meta($key, '_wams_sortby_custom_label', true),
							),
						)
					);
				}

				if (1 === absint(get_post_meta($key, '_wams_enable_sorting', true))) {
					$sorting_fields = get_post_meta($key, '_wams_sorting_fields', true);
					if (!empty($sorting_fields)) {
						foreach ($sorting_fields as $k => $field) {
							if (is_array($field)) {
								$info['wams-directory-' . $key]['fields'] = array_merge(
									$info['wams-directory-' . $key]['fields'],
									array(
										'um-directory-sorting_fields-' . $k => array(
											'label' => __('Field(s) to enable in sorting', 'wams'),
											'value' => __('Label: ', 'wams') . array_values($field)[0] . ' | ' . __('Meta key: ', 'wams') . stripslashes(array_keys($field)[0]),
										),
									)
								);
							} else {
								if (isset($options[$field])) {
									$sortby_label = $options[$field];
								} else {
									$sortby_label = $field;
								}
								$info['wams-directory-' . $key]['fields'] = array_merge(
									$info['wams-directory-' . $key]['fields'],
									array(
										'um-directory-sorting_fields-' . $k => array(
											'label' => __('Field to enable in sorting', 'wams'),
											'value' => $sortby_label,
										),
									)
								);
							}
						}
					}
				}

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-profile_photo' => array(
							'label' => __('Enable Profile Photo', 'wams'),
							'value' => get_post_meta($key, '_wams_profile_photo', true) ? $labels['yes'] : $labels['no'],
						),
						'um-directory-cover_photos'  => array(
							'label' => __('Enable Cover Photo', 'wams'),
							'value' => get_post_meta($key, '_wams_cover_photos', true) ? $labels['yes'] : $labels['no'],
						),
						'um-directory-show_name'     => array(
							'label' => __('Show display name', 'wams'),
							'value' => get_post_meta($key, '_wams_show_name', true) ? $labels['yes'] : $labels['no'],
						),
					)
				);

				$info = apply_filters('wams_debug_member_directory_profile_extend', $info, $key);

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-show_tagline' => array(
							'label' => __('Show tagline below profile name', 'wams'),
							'value' => get_post_meta($key, '_wams_show_tagline', true) ? $labels['yes'] : $labels['no'],
						),
					)
				);

				if (1 === absint(get_post_meta($key, '_wams_show_tagline', true))) {
					$tagline_fields = get_post_meta($key, '_wams_tagline_fields', true);
					if (!empty($tagline_fields) && is_array($tagline_fields)) {
						foreach ($tagline_fields as $k => $field) {
							$label = isset($options[$field]) ? $options[$field] : $field;
							$info['wams-directory-' . $key]['fields'] = array_merge(
								$info['wams-directory-' . $key]['fields'],
								array(
									'um-directory-tagline_fields-' . $k => array(
										'label' => __('Field to display in tagline', 'wams'),
										'value' => $label,
									),
								)
							);
						}
					}
				}

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-show_userinfo' => array(
							'label' => __('Show extra user information below tagline?', 'wams'),
							'value' => get_post_meta($key, '_wams_show_userinfo', true) ? $labels['yes'] : $labels['no'],
						),
					)
				);

				if (1 === absint(get_post_meta($key, '_wams_show_userinfo', true))) {
					$reveal_fields = get_post_meta($key, '_wams_reveal_fields', true);
					if (!empty($reveal_fields) && is_array($reveal_fields)) {
						foreach ($reveal_fields as $k => $field) {
							$label = isset($options[$field]) ? $options[$field] : $field;
							$info['wams-directory-' . $key]['fields'] = array_merge(
								$info['wams-directory-' . $key]['fields'],
								array(
									'um-directory-reveal_fields-' . $k => array(
										'label' => __('Field to display in extra user information section', 'wams'),
										'value' => $label,
									),
								)
							);
						}
					}
				}

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-show_social' => array(
							'label' => __('Show social connect icons in extra user information section', 'wams'),
							'value' => get_post_meta($key, '_wams_show_social', true) ? $labels['yes'] : $labels['no'],
						),
						'um-directory-search'      => array(
							'label' => __('Enable Search feature', 'wams'),
							'value' => get_post_meta($key, '_wams_search', true) ? $labels['yes'] : $labels['no'],
						),
					)
				);

				if (1 === absint(get_post_meta($key, '_wams_search', true))) {
					$directory_roles_can_search_meta = get_post_meta($key, '_wams_roles_can_search', true);
					$roles_can_search                = array();
					if (!empty($directory_roles_can_search_meta)) {
						if (is_string($directory_roles_can_search_meta)) {
							$roles_can_search = array($directory_roles_can_search_meta);
						} else {
							$roles_can_search = $directory_roles_can_search_meta;
						}
					}

					$info['wams-directory-' . $key]['fields'] = array_merge(
						$info['wams-directory-' . $key]['fields'],
						array(
							'um-directory-roles_can_search' => array(
								'label' => __('User Roles that can use search', 'wams'),
								'value' => !empty($roles_can_search) ? implode(', ', $roles_can_search) : $labels['all'],
							),
						)
					);
				}

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-filters' => array(
							'label' => __('Enable Filters feature', 'wams'),
							'value' => get_post_meta($key, '_wams_filters', true) ? $labels['yes'] : $labels['no'],
						),
					)
				);

				if (1 === absint(get_post_meta($key, '_wams_filters', true))) {
					$directory_roles_can_filter_meta = get_post_meta($key, '_wams_roles_can_filter', true);
					$roles_can_filter                = array();
					if (!empty($directory_roles_can_filter_meta)) {
						if (is_string($directory_roles_can_filter_meta)) {
							$roles_can_filter = array($directory_roles_can_filter_meta);
						} else {
							$roles_can_filter = $directory_roles_can_filter_meta;
						}
					}

					$info['wams-directory-' . $key]['fields'] = array_merge(
						$info['wams-directory-' . $key]['fields'],
						array(
							'um-directory-roles_can_filter' => array(
								'label' => __('User Roles that can use filters', 'wams'),
								'value' => !empty($roles_can_filter) ? implode(', ', $roles_can_filter) : $labels['all'],
							),
						)
					);

					$search_fields = get_post_meta($key, '_wams_search_fields', true);
					if (!empty($search_fields) && is_array($search_fields)) {
						foreach ($search_fields as $k => $field) {
							$label = isset($options[$field]) ? $options[$field] : $field;
							$info['wams-directory-' . $key]['fields'] = array_merge(
								$info['wams-directory-' . $key]['fields'],
								array(
									'um-directory-search_fields-' . $k => array(
										'label' => __('Filter meta to enable', 'wams'),
										'value' => $label,
									),
								)
							);
						}
					}
				}

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-filters_expanded' => array(
							'label' => __('Expand the filter bar by default', 'wams'),
							'value' => get_post_meta($key, '_wams_filters_expanded', true) ? $labels['yes'] : $labels['no'],
						),
					)
				);

				if (1 === absint(get_post_meta($key, '_wams_filters_expanded', true))) {
					$info['wams-directory-' . $key]['fields'] = array_merge(
						$info['wams-directory-' . $key]['fields'],
						array(
							'um-directory-filters_is_collapsible' => array(
								'label' => __('Can filter bar be collapsed', 'wams'),
								'value' => get_post_meta($key, '_wams_filters_is_collapsible', true) ? $labels['yes'] : $labels['no'],
							),
						)
					);
				}

				$search_filters = get_post_meta($key, '_wams_search_filters', true);
				if (!empty($search_filters) && is_array($search_filters)) {
					foreach ($search_filters as $k => $field) {
						$label = isset($options[$k]) ? $options[$k] : $k;
						$value = $field;
						if (is_array($field)) {
							$value = __('From ', 'wams') . $field[0] . __(' to ', 'wams') . $field[1];
						}
						$info['wams-directory-' . $key]['fields'] = array_merge(
							$info['wams-directory-' . $key]['fields'],
							array(
								'um-directory-search_filters-' . $k => array(
									'label' => __('Admin filtering', 'wams'),
									'value' => $label . ' - ' . $value,
								),
							)
						);
					}
				}

				$info['wams-directory-' . $key]['fields'] = array_merge(
					$info['wams-directory-' . $key]['fields'],
					array(
						'um-directory-must_search'        => array(
							'label' => __('Show results only after search/filtration', 'wams'),
							'value' => get_post_meta($key, '_wams_must_search', true) ? $labels['yes'] : $labels['no'],
						),
						'um-directory-max_users'          => array(
							'label' => __('Maximum number of profiles', 'wams'),
							'value' => get_post_meta($key, '_wams_max_users', true),
						),
						'um-directory-profiles_per_page'  => array(
							'label' => __('Number of profiles per page', 'wams'),
							'value' => get_post_meta($key, '_wams_profiles_per_page', true),
						),
						'um-directory-profiles_per_page_mobile' => array(
							'label' => __('Maximum number of profiles', 'wams'),
							'value' => get_post_meta($key, '_wams_profiles_per_page_mobile', true),
						),
						'um-directory-directory_header'   => array(
							'label' => __('Results Text', 'wams'),
							'value' => get_post_meta($key, '_wams_directory_header', true),
						),
						'um-directory-directory_header_single' => array(
							'label' => __('Single Result Text', 'wams'),
							'value' => get_post_meta($key, '_wams_directory_header_single', true),
						),
						'um-directory-directory_no_users' => array(
							'label' => __('Custom text if no users were found', 'wams'),
							'value' => get_post_meta($key, '_wams_directory_no_users', true),
						),
					)
				);

				$info = apply_filters('wams_debug_member_directory_extend', $info, $key);
			}
		}

		return $info;
	}
}
