<?php

namespace wams\admin\core;

use WP_Post;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\core\Admin_Columns')) {


	/**
	 * Class Admin_Columns
	 * @package um\admin\core
	 */
	class Admin_Columns
	{

		/**
		 * Admin_Columns constructor.
		 */
		public function __construct()
		{

			add_filter('manage_edit-wams_form_columns', array(&$this, 'manage_edit_wams_form_columns'));
			add_action('manage_wams_form_posts_custom_column', array(&$this, 'manage_wams_form_posts_custom_column'), 10, 3);

			add_filter('manage_edit-wams_directory_columns', array(&$this, 'manage_edit_wams_directory_columns'));
			add_action('manage_wams_directory_posts_custom_column', array(&$this, 'manage_wams_directory_posts_custom_column'), 10, 3);

			add_filter('post_row_actions', array(&$this, 'post_row_actions'), 99, 2);

			// Add a post display state for special WAMS pages.
			add_filter('display_post_states', array(&$this, 'add_display_post_states'), 10, 2);

			add_filter('post_row_actions', array(&$this, 'remove_bulk_actions_wams_form_inline'), 10, 2);

			add_filter('manage_users_columns', array(&$this, 'manage_users_columns'));

			add_filter('manage_users_custom_column', array(&$this, 'manage_users_custom_column'), 10, 3);
		}

		/**
		 * Filter: Add column 'Status'
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		public function manage_users_columns($columns)
		{
			$columns['account_status'] = __('Status', 'wams');
			return $columns;
		}


		/**
		 * Filter: Show column 'Status'
		 *
		 * @param string $val
		 * @param string $column_name
		 * @param int $user_id
		 *
		 * @return string
		 */
		public function manage_users_custom_column($val, $column_name, $user_id)
		{
			if ($column_name == 'account_status') {
				wams_fetch_user($user_id);
				$value = wams_user('account_status_name');
				wams_reset_user();
				return $value;
			}
			return $val;
		}

		/**
		 * This will remove the "Edit" bulk action, which is actually quick edit.
		 *
		 * @param array $actions
		 * @param WP_Post $post
		 *
		 * @return array
		 */
		public function remove_bulk_actions_wams_form_inline($actions, $post)
		{
			$remove_quick_edit = array(
				'wams_form',
				'wams_directory',
			);
			$remove_quick_edit = apply_filters('wams_cpt_remove_quick_edit', $remove_quick_edit);

			if (in_array($post->post_type, $remove_quick_edit, true)) {
				unset($actions['inline hide-if-no-js']);
			}

			return $actions;
		}

		/**
		 * Custom row actions
		 *
		 * @param array $actions
		 * @param WP_Post $post
		 *
		 * @return mixed
		 */
		public function post_row_actions($actions, $post)
		{
			//check for your post type
			if ('wams_form' === $post->post_type) {
				$actions['wams_duplicate'] = '<a href="' . esc_url($this->duplicate_uri($post->ID)) . '">' . __('Duplicate', 'wams') . '</a>';
			}
			return $actions;
		}

		/**
		 * Duplicate a form
		 *
		 * @param int $id
		 *
		 * @return string
		 */
		private function duplicate_uri($id)
		{
			$url = add_query_arg(
				array(
					'post_type'     => 'wams_form',
					'wams_adm_action' => 'duplicate_form',
					'post_id'       => $id,
					'_wpnonce'      => wp_create_nonce("um-duplicate_form{$id}"),
				),
				admin_url('edit.php')
			);

			return $url;
		}

		/**
		 * Custom columns for Form
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		function manage_edit_wams_form_columns($columns)
		{
			$new_columns['cb'] = '<input type="checkbox" />';
			$new_columns['title'] = __('Title', 'wams');
			$new_columns['id'] = __('ID', 'wams');
			$new_columns['mode'] = __('Type', 'wams');
			$new_columns['is_default'] = __('Default', 'wams');
			$new_columns['shortcode'] = __('Shortcode', 'wams');
			$new_columns['date'] = __('Date', 'wams');

			return $new_columns;
		}


		/**
		 * Custom columns for Directory
		 *
		 * @param array $columns
		 *
		 * @return array
		 */
		function manage_edit_wams_directory_columns($columns)
		{
			$new_columns['cb'] = '<input type="checkbox" />';
			$new_columns['title'] = __('Title', 'wams');
			$new_columns['id'] = __('ID', 'wams');
			$new_columns['is_default'] = __('Default', 'wams');
			$new_columns['shortcode'] = __('Shortcode', 'wams');
			$new_columns['date'] = __('Date', 'wams');

			return $new_columns;
		}


		/**
		 * Display custom columns for Form
		 *
		 * @param string $column_name
		 * @param int $id
		 */
		function manage_wams_form_posts_custom_column($column_name, $id)
		{
			switch ($column_name) {
				case 'id':
					echo '<span class="um-admin-number">' . $id . '</span>';
					break;

				case 'shortcode':
					$is_default = WAMS()->query()->get_attr('is_default', $id);

					if ($is_default) {
						echo WAMS()->shortcodes()->get_default_shortcode($id);
					} else {
						echo WAMS()->shortcodes()->get_shortcode($id);
					}

					break;

				case 'is_default':
					$is_default = WAMS()->query()->get_attr('is_default', $id);
					echo empty($is_default) ? __('No', 'wams') : __('Yes', 'wams');
					break;

				case 'mode':
					$mode = WAMS()->query()->get_attr('mode', $id);
					echo WAMS()->form()->display_form_type($mode, $id);
					break;
			}
		}


		/**
		 * Display custom columns for Directory
		 *
		 * @param string $column_name
		 * @param int $id
		 */
		function manage_wams_directory_posts_custom_column($column_name, $id)
		{
			switch ($column_name) {
				case 'id':
					echo '<span class="um-admin-number">' . $id . '</span>';
					break;
				case 'shortcode':
					$is_default = WAMS()->query()->get_attr('is_default', $id);

					if ($is_default) {
						echo WAMS()->shortcodes()->get_default_shortcode($id);
					} else {
						echo WAMS()->shortcodes()->get_shortcode($id);
					}
					break;
				case 'is_default':
					$is_default = WAMS()->query()->get_attr('is_default', $id);
					echo empty($is_default) ? __('No', 'wams') : __('Yes', 'wams');
					break;
			}
		}


		/**
		 * Add a post display state for special WAMS pages in the page list table.
		 *
		 * @param array $post_states An array of post display states.
		 * @param WP_Post $post The current post object.
		 *
		 * @return mixed
		 */
		public function add_display_post_states($post_states, $post)
		{

			foreach (WAMS()->config()->core_pages as $page_key => $page_value) {
				$page_id = WAMS()->options()->get(WAMS()->options()->get_core_page_id($page_key));

				if ($page_id == $post->ID) {
					$post_states['wams_core_page_' . $page_key] = sprintf('WAMS %s', $page_value['title']);
				}
			}

			return $post_states;
		}
	}
}
