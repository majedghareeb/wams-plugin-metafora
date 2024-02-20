<?php

namespace wams\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\Rewrite')) {

	/**
	 * Class Rewrite
	 * @package wams\core
	 */
	class Rewrite
	{

		/**
		 * Rewrite constructor.
		 */
		public function __construct()
		{
			if (!defined('DOING_AJAX')) {
				add_action('wp_loaded', array($this, 'maybe_flush_rewrite_rules'));
			}
			add_filter('query_vars', array(&$this, 'query_vars'));
		}

		/**
		 * Update "flush" option for reset rules on wp_loaded hook.
		 */
		public function reset_rules()
		{
			update_option('wams_flush_rewrite_rules', 1);
		}

		/**
		 * Reset Rewrite rules if need it.
		 */
		public function maybe_flush_rewrite_rules()
		{
			if (get_option('wams_flush_rewrite_rules')) {
				flush_rewrite_rules(false);
				delete_option('wams_flush_rewrite_rules');
			}
		}

		/**
		 * Modify global query vars.
		 *
		 * @param array $public_query_vars
		 *
		 * @return array
		 */
		public function query_vars($public_query_vars)
		{
			$public_query_vars[] = 'wams_page';
			$public_query_vars[] = 'wams_action';
			$public_query_vars[] = 'wams_verify';

			return $public_query_vars;
		}
	}
}
