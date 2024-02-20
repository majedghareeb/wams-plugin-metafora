<?php

namespace wams\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\core\Cron')) {

	/**
	 * Class Cron
	 * @package wams\core
	 */
	class Cron
	{

		/**
		 * Cron constructor.
		 */
		public function __construct()
		{
			add_filter('cron_schedules', array($this, 'add_schedules'));
			add_action('wp', array($this, 'schedule_events'));
		}

		/**
		 * @return bool
		 */
		private function cron_disabled()
		{
			/**
			 * Filters variable for disable WAMS WP Cron actions.
			 *
			 * @since 2.0
			 * @hook  wams_cron_disable
			 *
			 * @param {bool} $is_disabled Shortcode arguments.
			 *
			 * @return {bool} Do Cron actions are disabled? True for disable.
			 *
			 * @example <caption>Disable all WAMS WP Cron actions.</caption>
			 * add_filter( 'wams_cron_disable', '__return_true' );
			 */
			return apply_filters('wams_cron_disable', false);
		}

		/**
		 * Adds once weekly to the existing schedules.
		 *
		 * @param array $schedules
		 *
		 * @return array
		 */
		public function add_schedules($schedules = array())
		{
			if ($this->cron_disabled()) {
				return $schedules;
			}

			$schedules['weekly'] = array(
				'interval' => 604800,
				'display'  => __('Once Weekly', 'wams'),
			);
			$schedules['every_5_minute'] = array(
				'interval' => 300,
				'display'  => __('Every 1 Minute', 'wams'),
			);
			$schedules['every_10_minute'] = array(
				'interval' => 600,
				'display'  => __('Every 10 Minute', 'wams'),
			);
			$schedules['every_15_minute'] = array(
				'interval' => 900,
				'display'  => __('Every 15 Minute', 'wams'),
			);
			$schedules['every_30_minute'] = array(
				'interval' => 1800,
				'display'  => __('Every 30 Minute', 'wams'),
			);

			return $schedules;
		}

		/**
		 *
		 */
		public function schedule_events()
		{
			if ($this->cron_disabled()) {
				return;
			}

			$this->weekly_events();
			$this->daily_events();
			$this->twicedaily_events();
			$this->hourly_events();
			$this->every_5_minute_events();
			// $this->every_10_minute_events();
			$this->every_15_minute_events();
			$this->every_30_minute_events();
		}

		/**
		 *
		 */
		private function weekly_events()
		{
			$sunday_start   = wp_date('w');
			$week_start     = $sunday_start - absint(get_option('start_of_week'));
			$week_start_day = strtotime('-' . $week_start . ' days');
			$time           = mktime(0, 0, 0, wp_date('m', $week_start_day), wp_date('d', $week_start_day), wp_date('Y', $week_start_day));
			if (!wp_next_scheduled('wams_weekly_scheduled_events')) {
				wp_schedule_event($time, 'weekly', 'wams_weekly_scheduled_events');
			}
		}

		/**
		 *
		 */
		private function daily_events()
		{
			if (!wp_next_scheduled('wams_daily_scheduled_events')) {
				$time = mktime(0, 0, 0, wp_date('m'), wp_date('d'), wp_date('Y'));
				wp_schedule_event($time, 'daily', 'wams_daily_scheduled_events');
			}
		}

		/**
		 *
		 */
		private function twicedaily_events()
		{
			if (!wp_next_scheduled('wams_twicedaily_scheduled_events')) {
				$time = mktime(0, 0, 0, wp_date('m'), wp_date('d'), wp_date('Y'));
				wp_schedule_event($time, 'twicedaily', 'wams_twicedaily_scheduled_events');
			}
		}

		/**
		 *
		 */
		private function hourly_events()
		{
			if (!wp_next_scheduled('wams_hourly_scheduled_events')) {
				$time = mktime(wp_date('H'), 0, 0, wp_date('m'), wp_date('d'), wp_date('Y'));
				wp_schedule_event($time, 'hourly', 'wams_hourly_scheduled_events');
			}
		}

		/**
		 *
		 */
		private function every_5_minute_events()
		{
			if (!wp_next_scheduled('wams_every_5_minute_scheduled_events')) {
				$time = mktime(0, 5, 0, wp_date('m'), wp_date('d'), wp_date('Y'));
				wp_schedule_event($time, 'every_5_minute', 'wams_every_5_minute_scheduled_events');
			}
		}
		/**
		 *
		 */
		// private function every_10_minute_events()
		// {
		// 	if (!wp_next_scheduled('wams_every_01_minute_scheduled_events')) {
		// 		$time = mktime(0, 10, 0, wp_date('m'), wp_date('d'), wp_date('Y'));
		// 		wp_schedule_event($time, 'every_10_minute', 'wams_every_10_minute_scheduled_events');
		// 	}
		// }
		/**
		 *
		 */
		private function every_15_minute_events()
		{
			if (!wp_next_scheduled('wams_every_15_minute_scheduled_events')) {
				$time = mktime(0, 15, 0, wp_date('m'), wp_date('d'), wp_date('Y'));
				wp_schedule_event($time, 'every_15_minute', 'wams_every_15_minute_scheduled_events');
			}
		}
		/**
		 *
		 */
		private function every_30_minute_events()
		{
			if (!wp_next_scheduled('wams_every_30_minute_scheduled_events')) {
				$time = mktime(0, 30, 0, wp_date('m'), wp_date('d'), wp_date('Y'));
				wp_schedule_event($time, 'every_30_minute', 'wams_every_30_minute_scheduled_events');
			}
		}

		/**
		 * Breaks all WAMS registered schedule events.
		 */
		public function unschedule_events()
		{
			wp_clear_scheduled_hook('wams_weekly_scheduled_events');
			wp_clear_scheduled_hook('wams_daily_scheduled_events');
			wp_clear_scheduled_hook('wams_twicedaily_scheduled_events');
			wp_clear_scheduled_hook('wams_hourly_scheduled_events');
			wp_clear_scheduled_hook('wams_every_5_minute_scheduled_events');
			// wp_clear_scheduled_hook('wams_every_10_minute_scheduled_events');
			wp_clear_scheduled_hook('wams_every_15_minute_scheduled_events');
			wp_clear_scheduled_hook('wams_every_30_minute_scheduled_events');
		}
	}
}
