<?php

namespace wams\ajax;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\ajax\Init')) {

	/**
	 * Class Init
	 *
	 * @package um\ajax
	 */
	class Init
	{

		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \UM::includes()
		 */
		public function includes()
		{
			// wp_send_json(['message' => 'OK']);
		}
	}
}
