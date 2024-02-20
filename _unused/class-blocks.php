<?php

namespace wams\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\Blocks')) {

	/**
	 * Class Blocks
	 * @package um\core
	 */
	class Blocks
	{

		/**
		 * Blocks constructor.
		 */
		public function __construct()
		{
			add_action('init', array(&$this, 'block_editor_render'));
			add_filter('block_type_metadata_settings', array(&$this, 'block_type_metadata_settings'), 9999, 2);
		}

		/**
		 * Add attribute types if restricted blocks is active.
		 *
		 * @param array $settings Array of determined settings for registering a block type.
		 * @param array $args     Metadata provided for registering a block type.
		 *
		 * @return array
		 */
		public function block_type_metadata_settings($settings, $args)
		{
			$restricted_blocks = WAMS()->options()->get('restricted_blocks');
			if (empty($restricted_blocks)) {
				return $settings;
			}

			if (empty($settings['attributes']['wams_is_restrict'])) {
				$settings['attributes']['wams_is_restrict'] = array(
					'type' => 'boolean',
				);
			}
			if (empty($settings['attributes']['wams_who_access'])) {
				$settings['attributes']['wams_who_access'] = array(
					'type' => 'string',
				);
			}
			if (empty($settings['attributes']['wams_roles_access'])) {
				$settings['attributes']['wams_roles_access'] = array(
					'type' => 'array',
				);
			}
			if (empty($settings['attributes']['wams_message_type'])) {
				$settings['attributes']['wams_message_type'] = array(
					'type' => 'string',
				);
			}
			if (empty($settings['attributes']['wams_message_content'])) {
				$settings['attributes']['wams_message_content'] = array(
					'type' => 'string',
				);
			}

			return $settings;
		}

		/**
		 * Register WAMS Blocks.
		 *
		 * @uses register_block_type_from_metadata()
		 */
		public function block_editor_render()
		{
			/**
			 * Filters the variable to disable adding WAMS Blocks to Gutenberg editor.
			 *
			 * Note: It's "false" by default. To disable Gutenberg scripts to avoid the conflicts set it to "true"
			 *
			 * @since 2.6.3
			 * @hook wams_disable_blocks_script
			 *
			 * @param {bool} $disable_script Disabling block scripts variable.
			 *
			 * @return {bool} It's true for disabling block scripts.
			 *
			 * @example <caption>Disable block scripts.</caption>
			 * function my_custom_wams_disable_blocks_script( $disable_script ) {
			 *     $disable_script = true;
			 *     return $disable_script;
			 * }
			 * add_filter( 'wams_disable_blocks_script', 'my_custom_wams_disable_blocks_script', 10, 1 );
			 */
			$disable_script = apply_filters('wams_disable_blocks_script', false);
			if ($disable_script) {
				return;
			}

			$enable_blocks = WAMS()->options()->get('enable_blocks');
			if (empty($enable_blocks)) {
				return;
			}

			$blocks = array(
				'um-block/um-member-directories' => array(
					'render_callback' => array($this, 'member_directories_render'),
					'attributes'      => array(
						'member_id' => array(
							'type' => 'string',
						),
					),
				),
				'um-block/um-forms'              => array(
					'render_callback' => array($this, 'forms_render'),
					'attributes'      => array(
						'form_id' => array(
							'type' => 'string',
						),
					),
				),
				'um-block/um-password-reset'     => array(
					'render_callback' => array($this, 'password_reset_render'),
				),
				'um-block/um-account'            => array(
					'render_callback' => array($this, 'account_render'),
					'attributes'      => array(
						'tab' => array(
							'type' => 'string',
						),
					),
				),
			);

			foreach ($blocks as $k => $block_data) {
				$block_type = str_replace('um-block/', '', $k);
				register_block_type_from_metadata(WAMS_PATH . 'includes/blocks/' . $block_type, $block_data);
			}
		}

		/**
		 * Renders member directory block.
		 *
		 * @param array $atts Block attributes.
		 *
		 * @return string
		 *
		 * @uses apply_shortcodes()
		 */
		public function member_directories_render($atts)
		{
			$shortcode = '[wams';

			if (isset($atts['member_id']) && '' !== $atts['member_id']) {
				$shortcode .= ' form_id="' . $atts['member_id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes($shortcode);
		}

		/**
		 * Renders WAMS Form block.
		 *
		 * @param array $atts Block attributes.
		 *
		 * @return string
		 *
		 * @uses apply_shortcodes()
		 */
		public function forms_render($atts)
		{
			if (isset($atts['form_id']) && '' !== $atts['form_id']) {
				if (wams_is_core_page('account')) {
					if (defined('REST_REQUEST') && REST_REQUEST) {
						return '<div class="um-block-notice">' . esc_html__('This block cannot be used on this page', 'wams') . '</div>';
					} else {
						return '';
					}
				}
			}

			$shortcode = '[wams is_block="1"';

			if (isset($atts['form_id']) && '' !== $atts['form_id']) {
				$shortcode .= ' form_id="' . $atts['form_id'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes($shortcode);
		}

		/**
		 * Renders WAMS Reset Password form block.
		 *
		 * @return string
		 *
		 * @uses apply_shortcodes()
		 */
		public function password_reset_render()
		{
			$shortcode = '[wams_password]';

			return apply_shortcodes($shortcode);
		}

		/**
		 * Renders WAMS Account block.
		 *
		 * @param array $atts Block attributes.
		 *
		 * @return string
		 *
		 * @uses apply_shortcodes()
		 */
		public function account_render($atts)
		{
			if (wams_is_core_page('user')) {
				if (defined('REST_REQUEST') && REST_REQUEST) {
					return '<div class="um-block-notice">' . esc_html__('This block cannot be used on this page', 'wams') . '</div>';
				} else {
					return '';
				}
			}

			$shortcode = '[wams_account is_block="1"';

			if (isset($atts['tab']) && 'all' !== $atts['tab']) {
				$shortcode .= ' tab="' . $atts['tab'] . '"';
			}

			$shortcode .= ']';

			return apply_shortcodes($shortcode);
		}
	}
}
