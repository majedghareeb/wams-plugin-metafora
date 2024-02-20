<?php

namespace wams\common;

use Exception;
use wams\common\Logger;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\common\Init')) {

	/**
	 * Class Init
	 *
	 * @package wams\common
	 */
	class Init
	{

		/**
		 * Create classes' instances where __construct isn't empty for hooks init
		 *
		 * @used-by \WAMS::includes()
		 */
		public function includes()
		{
			add_action('wp_login', [$this->user_logins(), 'log_user_login'], 10, 2);
			$this->hooks();
			$this->search_client_field();
			$this->gravityflow_custom_step();
			// add_action('wams_every_minute_scheduled_events', [$this, 'wams_cron_test']);
			// $this->cpt()->hooks();
			// $this->screen();
			// $this->logger();
			$this->register_scheduled_tasks();
		}

		public function register_scheduled_tasks()
		{
			// add_action('wams_every_minute_scheduled_events', [$this, 'log_user_login']);
			// error_log('Cron job is running.');
			// \wams\common\Logger::info('wams_cron_test is called from ' . __CLASS__ . ' class using cron');
			$tasks = get_option('wams_task_scheduler');
			if (!$tasks) return;
			foreach ($tasks as $task_hook => $task_schedule) {
				try {
					$task = explode('@', $task_hook);

					if ($task && class_exists($task[0]) &&  $task_schedule != 'none') {
						$class = new $task[0];
						$callback = $task[1];
						add_action('wams_' . $task_schedule . '_scheduled_events', [$class, $callback]);
					}
				} catch (Exception $th) {
					error_log($th);
				}
				// $hook_class = new \wams\admin\Admin();

				# code...
			}
		}




		public function register_hooks()
		{
		}

		/**
		 * @since 1.0.0
		 *
		 * @return CPT
		 */
		public function cpt()
		{
			if (empty(WAMS()->classes['wams\common\cpt'])) {
				WAMS()->classes['wams\common\cpt'] = new CPT();
			}
			return WAMS()->classes['wams\common\cpt'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return Hooks
		 */
		public function hooks()
		{
			if (empty(WAMS()->classes['wams\common\hooks'])) {
				WAMS()->classes['wams\common\hooks'] = new Hooks();
			}
			return WAMS()->classes['wams\common\hooks'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return Screen
		 */
		public function screen()
		{
			if (empty(WAMS()->classes['wams\common\screen'])) {
				WAMS()->classes['wams\common\screen'] = new Screen();
			}
			return WAMS()->classes['wams\common\screen'];
		}

		/**
		 * @since 1.0.0
		 *
		 * @return Logger
		 */
		public function logger()
		{
			if (empty(WAMS()->classes['wams\common\logger'])) {
				WAMS()->classes['wams\common\logger'] = new Logger();
			}
			return WAMS()->classes['wams\common\logger'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return User_Logins
		 */
		public function user_logins()
		{
			if (empty(WAMS()->classes['wams\common\user_logins'])) {
				WAMS()->classes['wams\common\user_logins'] = new User_Logins();
			}
			return WAMS()->classes['wams\common\user_logins'];
		}


		/**
		 * @since 1.0.0
		 *
		 * @return Search_Client_Field
		 */
		public function search_client_field()
		{
			if (empty(WAMS()->classes['wams\common\search_client_field'])) {
				WAMS()->classes['wams\common\search_client_field'] = new Search_Client_Field();
			}
			return WAMS()->classes['wams\common\search_client_field'];
		}
		/**
		 * @since 1.0.0
		 *
		 * @return Gravityflow_Custom_Step
		 */
		public function gravityflow_custom_step()
		{
			if (empty(WAMS()->classes['wams\common\gravityflow_custom_step'])) {
				WAMS()->classes['wams\common\gravityflow_custom_step'] = new Gravityflow_Custom_Step();
			}
			return WAMS()->classes['wams\common\gravityflow_custom_step'];
		}
	}
}
