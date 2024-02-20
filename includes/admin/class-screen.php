<?php

namespace wams\admin;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Screen
 *
 * @since 2.8.0
 *
 * @package um\admin
 */
class Screen
{
	/**
	 * Boolean check if we're viewing WAMS backend.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public function is_own_screen()
	{
		global $current_screen;

		$is_wams_screen = false;
		if (!empty($current_screen) && isset($current_screen->id)) {
			$screen_id = $current_screen->id;
			if (
				strstr($screen_id, 'wams') ||
				strstr($screen_id, 'wams_')
			) {
				$is_wams_screen = true;
			}
		}

		if ($this->is_own_post_type()) {
			$is_wams_screen = true;
		}
		return apply_filters('is_wams_admin_screen', $is_wams_screen);
	}

	/**
	 * Check if current page load WAMS post type.
	 *
	 * @since 2.8.0
	 *
	 * @return bool
	 */
	public function is_own_post_type()
	{
		$cpt = WAMS()->common()->cpt()->get_list();

		if (isset($_REQUEST['post_type'])) {
			$post_type = sanitize_key($_REQUEST['post_type']);
			if (in_array($post_type, $cpt, true)) {
				return true;
			}
		} elseif (isset($_REQUEST['action']) && 'edit' === sanitize_key($_REQUEST['action'])) {
			$post_type = get_post_type();
			if (in_array($post_type, $cpt, true)) {
				return true;
			}
		}

		return false;
	}
}
