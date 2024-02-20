<?php

namespace wams\frontend;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\frontend\Init')) {

	/**
	 * Class Init
	 *
	 * @package um\frontend
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
			$this->user_dashboard();
			$this->Shortcodes();
			$this->enqueue();
			$this->secure();
		}

		/**
		 * @since 2.0
		 *
		 * @return wams\frontend\Shortcodes
		 */
		function shortcodes()
		{
			if (empty(WAMS()->classes['shortcodes'])) {
				WAMS()->classes['shortcodes'] = new Shortcodes();
			}

			return WAMS()->classes['shortcodes'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return User_Dashboard
		 */
		public function user_dashboard()
		{
			if (empty(WAMS()->classes['wams\frontend\user_dashboard'])) {
				WAMS()->classes['wams\frontend\user_dashboard'] = new User_Dashboard();
			}

			return WAMS()->classes['wams\frontend\user_dashboard'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return Tasks_Calendar
		 */
		public function tasks_calendar()
		{
			if (empty(WAMS()->classes['wams\frontend\tasks_calendar'])) {
				WAMS()->classes['wams\frontend\tasks_calendar'] = new Tasks_Calendar();
			}

			return WAMS()->classes['wams\frontend\tasks_calendar'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return Charts
		 */
		public function charts()
		{
			if (empty(WAMS()->classes['wams\frontend\charts'])) {
				WAMS()->classes['wams\frontend\charts'] = new Charts();
			}

			return WAMS()->classes['wams\frontend\charts'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return Theme_Hooks
		 */
		public function theme_hooks()
		{
			if (empty(WAMS()->classes['wams\frontend\theme_hooks'])) {
				WAMS()->classes['wams\frontend\theme_hooks'] = new Theme_Hooks();
			}

			return WAMS()->classes['wams\frontend\theme_hooks'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return Enqueue
		 */
		public function enqueue()
		{
			if (empty(WAMS()->classes['wams\frontend\enqueue'])) {
				WAMS()->classes['wams\frontend\enqueue'] = new Enqueue();
			}

			return WAMS()->classes['wams\frontend\enqueue'];
		}

		/**
		 * @since 2.6.8
		 *
		 * @return Secure
		 */
		public function secure()
		{
			if (empty(WAMS()->classes['wams\frontend\secure'])) {
				WAMS()->classes['wams\frontend\secure'] = new Secure();
			}
			return WAMS()->classes['wams\frontend\secure'];
		}
	}
}
