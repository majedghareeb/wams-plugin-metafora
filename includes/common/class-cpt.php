<?php

namespace wams\common;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\common\CPT')) {

	/**
	 * Class CPT
	 *
	 * @package um\common
	 *
	 * @since 2.6.8
	 */
	class CPT
	{

		public function hooks()
		{
			add_action('init', array(&$this, 'create_post_types'), 1);
		}

		/**
		 * Create taxonomies for use for UM
		 */
		public function create_post_types()
		{
			register_post_type(
				'wams_form',
				array(
					'labels'       => array(
						'name'               => __('Forms', 'wams'),
						'singular_name'      => __('Form', 'wams'),
						'add_new'            => __('Add New', 'wams'),
						'add_new_item'       => __('Add New Form', 'wams'),
						'edit_item'          => __('Edit Form', 'wams'),
						'not_found'          => __('You did not create any forms yet', 'wams'),
						'not_found_in_trash' => __('Nothing found in Trash', 'wams'),
						'search_items'       => __('Search Forms', 'wams'),
					),
					'capabilities' => array(
						'edit_post'          => 'manage_options',
						'read_post'          => 'manage_options',
						'delete_post'        => 'manage_options',
						'edit_posts'         => 'manage_options',
						'edit_others_posts'  => 'manage_options',
						'delete_posts'       => 'manage_options',
						'publish_posts'      => 'manage_options',
						'read_private_posts' => 'manage_options',
					),
					'show_ui'      => true,
					'show_in_menu' => false,
					'public'       => false,
					'show_in_rest' => true,
					'supports'     => array('title'),
				)
			);
		}

		/**
		 * @since 2.8.0
		 * @return array
		 */
		public function get_list()
		{
			$cpt_list = array(
				'wams_form',
			);
			return apply_filters('wams_cpt_list', $cpt_list);
		}

		/**
		 * @param null|string $post_type
		 *
		 * @since 2.8.0
		 *
		 * @return array
		 */
		public function get_taxonomies_list($post_type = null)
		{
			$taxonomies = apply_filters('wams_cpt_taxonomies_list', array());

			if (isset($post_type)) {
				$taxonomies = array_key_exists($post_type, $taxonomies) ? $taxonomies[$post_type] : array();
			}
			return $taxonomies;
		}
	}
}
