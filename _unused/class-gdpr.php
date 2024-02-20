<?php

namespace wams\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\GDPR')) {

	/**
	 * Class Admin_GDPR
	 * @package um\core
	 */
	class GDPR
	{

		/**
		 * Admin_GDPR constructor.
		 */
		public function __construct()
		{
			add_action('wams_submit_form_register', array(&$this, 'agreement_validation'), 9, 2);

			add_filter('wams_whitelisted_metakeys', array(&$this, 'extend_whitelisted'), 10, 2);

			add_filter('wams_before_save_filter_submitted', array(&$this, 'add_agreement_date'));
			add_filter('wams_email_registration_data', array(&$this, 'email_registration_data'), 10, 1);

			add_action('wams_after_form_fields', array(&$this, 'display_option'));
		}

		/**
		 * @param $args
		 */
		public function display_option($args)
		{
			if (!empty($args['use_gdpr'])) {
				$template_path = trailingslashit(get_stylesheet_directory()) . '/wams/templates/gdpr-register.php';
				if (file_exists($template_path)) {
					require $template_path;
				} else {
					require WAMS_PATH . 'templates/gdpr-register.php';
				}
			}
		}

		/**
		 * @param array $submitted_data
		 * @param array $form_data
		 */
		public function agreement_validation($submitted_data, $form_data)
		{
			$gdpr_enabled        = get_post_meta($form_data['form_id'], '_wams_register_use_gdpr', true);
			$use_gdpr_error_text = get_post_meta($form_data['form_id'], '_wams_register_use_gdpr_error_text', true);
			$use_gdpr_error_text = !empty($use_gdpr_error_text) ? $use_gdpr_error_text : __('Please agree privacy policy.', 'wams');

			if ($gdpr_enabled && !isset($submitted_data['submitted']['use_gdpr_agreement'])) {
				WAMS()->form()->add_error('use_gdpr_agreement', $use_gdpr_error_text);
			}
		}

		/**
		 * @param array $metakeys
		 * @param array $form_data
		 */
		public function extend_whitelisted($metakeys, $form_data)
		{
			$gdpr_enabled = get_post_meta($form_data['form_id'], '_wams_register_use_gdpr', true);
			if (!empty($gdpr_enabled)) {
				$metakeys[] = 'use_gdpr_agreement';
			}
			return $metakeys;
		}

		/**
		 * @param $submitted
		 *
		 * @return mixed
		 */
		public function add_agreement_date($submitted)
		{
			if (isset($submitted['use_gdpr_agreement'])) {
				$submitted['use_gdpr_agreement'] = current_time('mysql', true);
			}

			return $submitted;
		}

		/**
		 * @param $submitted
		 *
		 * @return mixed
		 */
		public function email_registration_data($submitted)
		{
			if (!empty($submitted['use_gdpr_agreement'])) {
				$title               = __('GDPR Applied', 'wams');
				$submitted[$title] = wp_date(get_option('date_format', 'F j, Y') . ' ' . get_option('time_format', 'g:i a'), strtotime($submitted['use_gdpr_agreement']));
				unset($submitted['use_gdpr_agreement']);
			}

			return $submitted;
		}
	}
}