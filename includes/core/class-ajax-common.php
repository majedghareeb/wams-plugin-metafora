<?php

namespace wams\core;

// Exit if executed directly
if (!defined('ABSPATH')) exit;

if (!class_exists('wams\core\AJAX_Common')) {


	/**
	 * Class AJAX_Common
	 * @package wams\core
	 */
	class AJAX_Common
	{


		/**
		 * AJAX_Common constructor.
		 */
		function __construct()
		{
			$ajax_actions = array();

			foreach ($ajax_actions as $action => $nopriv) {

				add_action('wp_ajax_wams_' . $action, array($this, $action));

				if ($nopriv) {
					add_action('wp_ajax_nopriv_wams_' . $action, array($this, $action));
				}
			}
			add_action('wp_ajax_wams_frontend', array(&$this, 'ajax_frontend'));
			add_action('wp_ajax_nopriv_wams_frontend', array(&$this, 'ajax_frontend'));
		}

		/**
		 *
		 */
		public function ajax_frontend()
		{
			WAMS()->check_ajax_nonce();
			$arr_options = [
				'status' => 'ok',
				'message' => 'AJAX Call is OK'
			];
			wp_send_json($arr_options);
		}
	}
}
