<?php

namespace wams\frontend;

if (!defined('ABSPATH')) {
	exit;
}

/**
 * Class Enqueue.
 *
 * @package um\frontend
 */
final class Enqueue extends \wams\common\Enqueue
{
	/**
	 * Enqueue constructor.
	 */
	public function __construct()
	{
		parent::__construct();
		add_action('init', array(&$this, 'wp_enqueue_scripts'));
	}


	/**
	 * Register JS scripts.
	 *
	 * @since 1.0.0
	 */
	public function register_scripts()
	{
		$js_url   = self::get_url('js');
		$libs_url   = self::get_url('libs');

		wp_register_script('wams_frontend', $js_url . 'frontend/wams-public.js', array(), '1.0.6', true);
		wp_register_script('wams_home', $js_url . 'frontend/home.js', array(), '1.0.0', true);
		wp_register_script('wams_tables', $js_url . 'frontend/tables.js', array(), '1.0.0', true);
		wp_register_script('wams_charts', $js_url . 'frontend/charts.js', array('apexcharts'), '1.0.0', true);
		wp_register_script('sweetalert2', $libs_url . 'sweetalert2/sweetalert2.min.js', array(), false, true);
		wp_register_script("bootstrap-table", $libs_url . 'bootstrap-table/bootstrap-table.min.js', array(), false, false);
		wp_register_script("bootstrap-table-locale", $libs_url . 'bootstrap-table/bootstrap-table-locale-all.min.js', array(), false, false);
		wp_register_script("bootstrap-table-export", $libs_url . 'bootstrap-table/extensions/export/bootstrap-table-export.min.js', array(), false, false);
		wp_register_script("tableExport", $libs_url . 'bootstrap-table/extensions/export/tableExport.min.js', array(), false, false);

		wp_register_script("apexcharts", $libs_url . 'apexcharts/apexcharts.min.js', array(), '3.45.2', false);
		wp_register_script("chart", $libs_url . 'chart.js/chart.min.js', array(), WAMS_VERSION, false);
		wp_register_script("chart-datalabels", $libs_url . 'chart.js/chartjs-plugin-datalabels.min.js', array(), 'WAMS_VERSION', false);

		$localize = array(
			"ajaxurl" => admin_url("admin-ajax.php"),
			'nonce'           => wp_create_nonce('wams-frontend-nonce'),
		);

		$wams_web_notitications_settings = get_option('wams_web_notifications_settings');
		if ($wams_web_notitications_settings && $wams_web_notitications_settings['enabled'] == 'on') {
			$timer = $wams_web_notitications_settings['interval'] ?? '';
			$sound = $wams_web_notitications_settings['sound_enabled'] == 'on' ? 1 : 0;
			$localize =  array_merge(
				$localize,
				[
					'sound'      => (int) $sound,
					'sound_url'  => (string) WAMS_URL . ('assets/sound/light.mp3'),
					'timer'      => (int) $timer * 1000
				]
			);
			wp_register_script('wams_web_notifications', $js_url . 'frontend/web-notifications.js', array('jquery', 'wp-util'), '1.0.1', true);
		}
		wp_localize_script('wams_frontend', "wams_frontend_scripts", $localize);

		wp_register_script('notifications-page', $js_url . 'frontend/notifications-page.js', [], '1.0.1', true);
		wp_register_script('google-analytics', $js_url . 'frontend/google-analytics.js', [], '1.0.1', true);
		wp_register_script('page-parser', $js_url . 'frontend/page-parser.js', [], '1.0.1', true);
		wp_register_script('rss-fetcher', $js_url . 'frontend/rss-fetcher.js', [], '1.0.1', true);
		wp_register_script('telegram', $js_url . 'frontend/telegram.js', [], '1.0.1', true);
		wp_register_script('messages', $js_url . 'frontend/messages.js', [], '1.0.1', true);
	}

	/**
	 * Register styles.
	 *
	 * @since 1.0.0
	 */
	public function register_styles()
	{
		$css_url = self::get_url('css');
		$libs_url   = self::get_url('libs');

		wp_register_style('wams_frontend', $css_url . 'frontend/wams-public.css', array(), '1.1');
		wp_register_style('sweetalert2', $libs_url . 'sweetalert2/sweetalert2.min.css', array(), false);
		wp_register_style("bootstrap-table", $libs_url . 'bootstrap-table/bootstrap-table.min.css', array(), false);
		wp_register_style("bootstrap-icons", $libs_url . 'bootstrap-icons/bootstrap-icons.css', array(), false);
		wp_register_style("chart", $libs_url . 'chart.js/chart.css', array(), false);
		wp_register_style("apexcharts", $libs_url . 'apexcharts/apexcharts.css', array(), false);
	}

	/**
	 * Enqueue scripts and styles.
	 *
	 * @since 1.0.0
	 */
	public function wp_enqueue_scripts()
	{
		$this->register_scripts();
		$this->register_styles();

		wp_enqueue_style(['sweetalert2', 'wams_frontend']);
		wp_enqueue_script(['wams_frontend', 'sweetalert2', 'wams_web_notifications']);
	}

	public function load_notifications_page_script()
	{
		wp_enqueue_script('notifications-page');
	}
	public function load_google_analytics_script()
	{
		wp_enqueue_script('google-analytics');
	}
	public function load_page_parser_script()
	{
		wp_enqueue_script('page-parser');
	}

	public function load_rss_fetcher_script()
	{
		wp_enqueue_script('rss-fetcher');
	}
	public function load_telegram_script()
	{
		wp_enqueue_script('telegram');
	}
	public function load_messages_script()
	{
		wp_enqueue_script('messages');
	}
	public function load_home_script()
	{
		wp_enqueue_script('wams_home');
	}
	public function load_tables_script()
	{
		wp_enqueue_script('wams_tables');
	}
	public function load_bootstrap_table_script()
	{
		wp_enqueue_script(['bootstrap-table', 'tableExport', 'bootstrap-table-export']);
		wp_enqueue_style('bootstrap-table');
	}

	public function load_chart_script()
	{
		wp_enqueue_script(['chart', 'chart-datalabels']);
		wp_enqueue_style('chart');
	}
	public function load_apexcharts_script()
	{
		wp_enqueue_script('wams_charts');
		wp_enqueue_style('apexcharts');
	}
}
