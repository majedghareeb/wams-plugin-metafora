<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Fix for plugin "The SEO Framework", dynamic profile page title
 * @link https://ru.wordpress.org/plugins/autodescription/
 *
 * @param $title
 * @param string $sep
 *
 * @return mixed|string
 */
function wams_dynamic_user_profile_pagetitle($title, $sep = '')
{

	if (wams_is_core_page('user') && wams_get_requested_user()) {

		$user_id = wams_get_requested_user();

		if (WAMS()->user()->is_profile_noindex($user_id)) {
			return $title;
		}

		$profile_title = WAMS()->options()->get('profile_title');

		wams_fetch_user(wams_get_requested_user());

		$profile_title = wams_convert_tags($profile_title);

		$title = stripslashes($profile_title);

		wams_reset_user();
	}

	return $title;
}
add_filter('the_seo_framework_pro_add_title', 'wams_dynamic_user_profile_pagetitle', 100000, 2);
add_filter('wp_title', 'wams_dynamic_user_profile_pagetitle', 100000, 2);
add_filter('pre_get_document_title', 'wams_dynamic_user_profile_pagetitle', 100000, 2);


/**
 * Try and modify the page title in page
 *
 * @param $title
 * @param string $id
 *
 * @return string
 */
function wams_dynamic_user_profile_title($title, $id = '')
{
	if (is_admin()) {
		return $title;
	}

	if (wams_is_core_page('user')) {
		if ($id == WAMS()->config()->permalinks['user'] && in_the_loop()) {
			if (wams_get_requested_user()) {
				$title = wams_get_display_name(wams_get_requested_user());
			} elseif (is_user_logged_in()) {
				$title = wams_get_display_name(get_current_user_id());
			}
		}
	}

	if (!function_exists('mb_convert_encoding')) {
		return $title;
	}

	return (strlen($title) !== mb_strlen($title)) ? $title : mb_convert_encoding($title, 'UTF-8');
}
add_filter('the_title', 'wams_dynamic_user_profile_title', 100000, 2);


/**
 * Fix SEO canonical for the profile page
 *
 * @param  string       $canonical_url The canonical URL.
 * @param  WP_Post      $post          Optional. Post ID or object. Default is global `$post`.
 * @return string|false                The canonical URL, or false if current URL is canonical.
 */
function wams_get_canonical_url($canonical_url, $post)
{
	if (WAMS()->config()->permalinks['user'] == $post->ID || (WAMS()->external_integrations()->is_wpml_active() && WAMS()->config()->permalinks['user'] == wpml_object_id_filter($post->ID, 'page', true, icl_get_default_language()))) {

		/**
		 * WAMS hook
		 *
		 * @type filter
		 * @title wams_allow_canonical__filter
		 * @description Allow canonical
		 * @input_vars
		 * [{"var":"$allow_canonical","type":"bool","desc":"Allow?"}]
		 * @change_log
		 * ["Since: 2.0"]
		 * @usage
		 * <?php add_filter( 'wams_allow_canonical__filter', 'function_name', 10, 1 ); ?>
		 * @example
		 * <?php
		 * add_filter( 'wams_allow_canonical__filter', 'my_allow_canonical', 10, 1 );
		 * function my_allow_canonical( $allow_canonical ) {
		 *     // your code here
		 *     return $allow_canonical;
		 * }
		 * ?>
		 */
		$enable_canonical = apply_filters('wams_allow_canonical__filter', true);

		if ($enable_canonical) {
			$url = wams_user_profile_url(wams_get_requested_user());
			$canonical_url = ($url === home_url($_SERVER['REQUEST_URI'])) ? false : $url;

			if ($page = get_query_var('cpage')) {
				$canonical_url = get_comments_pagenwams_link($page);
			}
		}
	}

	return $canonical_url;
}
add_filter('get_canonical_url', 'wams_get_canonical_url', 20, 2);


/**
 * Add cover photo label of file size limit
 *
 * @param array $fields Predefined fields
 *
 * @return array
 */
function wams_change_profile_cover_photo_label($fields)
{
	$max_size = WAMS()->files()->format_bytes($fields['cover_photo']['max_size']);
	if (!empty($max_size)) {
		list($file_size, $unit) = explode(' ', $max_size);

		if ($file_size < 999999999) {
			$fields['cover_photo']['upload_text'] .= '<small class="um-max-filesize">( ' . __('max', 'wams') . ': <span>' . $file_size . $unit . '</span> )</small>';
		}
	}
	return $fields;
}
add_filter('wams_predefined_fields_hook', 'wams_change_profile_cover_photo_label', 10, 1);


/**
 * Add profile photo label of file size limit
 *
 * @param array $fields Predefined fields
 *
 * @return array
 */
function wams_change_profile_photo_label($fields)
{
	$max_size = WAMS()->files()->format_bytes($fields['profile_photo']['max_size']);
	if (!empty($max_size)) {
		list($file_size, $unit) = explode(' ', $max_size);

		if ($file_size < 999999999) {
			$fields['profile_photo']['upload_text'] .= '<small class="um-max-filesize">( ' . __('max', 'wams') . ': <span>' . $file_size . $unit . '</span> )</small>';
		}
	}
	return $fields;
}
add_filter('wams_predefined_fields_hook', 'wams_change_profile_photo_label', 10, 1);
