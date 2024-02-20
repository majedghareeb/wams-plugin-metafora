<?php

namespace wams\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\FontIcons')) {

	/**
	 * Class FontIcons
	 * @package um\core
	 */
	class FontIcons
	{

		/**
		 * The list of the FontIcons.
		 *
		 * @var array
		 */
		public $all = array();

		/**
		 * FontIcons constructor.
		 */
		public function __construct()
		{
			$cached_option = get_option('wams_cache_fonticons', array());

			if (empty($cached_option)) {
				$files['ii'] = WAMS_PATH . 'assets/libs/legacy/fonticons/fonticons-ii.css';
				$files['fa'] = WAMS_PATH . 'assets/libs/legacy/fonticons/fonticons-fa.css';

				$array = array();
				foreach ($files as $c => $file) {
					$css = file_get_contents($file);

					if ('fa' === $c) {
						preg_match_all('/\.(um-faicon-.*?):before/', $css, $matches);
					} else {
						preg_match_all('/\.(um-icon-.*?):before/', $css, $matches);
					}

					foreach ($matches[1] as $match) {
						$icon    = str_replace(':before', '', $match);
						$array[] = $icon;
					}
					$array = array_unique($array);
				}

				update_option('wams_cache_fonticons', $array);
			}

			$this->all = $cached_option;
		}
	}
}
