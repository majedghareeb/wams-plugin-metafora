<?php

namespace wams\frontend;

use GFFormsModel;
use Inc\Wams;
use GFAPI;

// Exit if accessed directly.
if (!defined('ABSPATH')) exit;


if (!class_exists('wams\frontend\Ajax_Hooks')) {


	/**
	 * Class Admin_Ajax_Hooks
	 * @package um\admin\core
	 */
	class Ajax_Hooks
	{
		/**
		 * Admin_Columns constructor.
		 */
		function __construct()
		{
			add_action('wp_ajax_telegram_ajax_request', array(WAMS()->telegram_notifications(), 'telegram_ajax_handler'));
			add_action('wp_ajax_rss_fetcher', array(WAMS()->rss_feed_extractor(), 'rss_fetcher_ajax_handler'));
			add_action('wp_ajax_web_notifications_frontend_ajax_request', array(WAMS()->web_notifications(), 'web_notifications_ajax_handler'));
			add_action('wp_ajax_vendors_importer_frontend_ajax_request', array(WAMS()->vendors_importer(), 'vendors_importer_ajax_handler'));
			add_action('wp_ajax_google_analytics_ajax_request', array(WAMS()->google_analytics(), 'google_analytics_ajax_handler'));
			add_action('wp_ajax_wams_tables', array(&$this, 'tables_ajax_handler'));
			add_action('wp_ajax_payment_orders', array(&$this, 'payment_orders_ajax_handler'));
			add_action('wp_ajax_charts_ajax_request', array(WAMS()->frontend()->charts(), 'charts_ajax_handler'));
			add_action('wp_ajax_doc_downloader_request', array($this, 'doc_downloader_ajax_handler'));
			add_action('wp_ajax_messages_ajax_request', array(WAMS()->messages(), 'messages_ajax_handler'));

			// add_action('wp_ajax_wams_same_page_update', array(WAMS()->admin_settings(), 'same_page_update_ajax'));
		}


		function doc_downloader_ajax_handler()
		{
			if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
				wp_die(esc_attr__('Security Check', 'wams'));
			}

			if (empty($_POST['entry_id'])) {
				wp_send_json_error(__('Invalid Entry ID.', 'wams'));
			}
			$entry_id = $_POST['entry_id'];
			$entry = GFAPI::get_entry($entry_id);
			// Start generating Word document
			$form_id = $entry['form_id'];
			$html = '<html><body dir="rtl"><div class = "content">';
			$form_fields = GFAPI::get_form($form_id)['fields'];
			foreach ($form_fields as $field) {
				if ($entry[$field->id] == '') continue;
				if ($field->type == 'workflow_user' || $field->type == 'workflow_assignee_select') continue;
				$html .= '<h2>' . $field->label . '</h2>';
				if ($field->type == 'form') {
					$link_entries = explode(',', $entry[$field->id]);
					if (empty($link_entries)) continue;
					$html .= '<ul>';
					foreach ($link_entries as $entry_id) {
						$link_entry = GFAPI::get_entry($entry_id);
						if (!is_wp_error($link_entry)) {
							$html .= '<li><a href=' . $link_entry['1'] . '">' . $link_entry['1'] . '</a></li>';
						}
					}
					$html .= '</ul>';
				} else {
					$html .= '<div>' . $entry[$field->id] . '</div>';
				}
				$html .= '<br>';
			}
			$html .= '</div></body></html>';
			require_once WAMS_PATH . 'includes/lib/html2doc/class-export-to-word.php';
			$css = '<style type = "text/css">body, h1, h2, h3, h4, h5, h6  {
				font-family: Tahoma, "Trebuchet MS", sans-serif;
			  }</style>';
			$docContent = \ExportToWord::htmlToDoc($html, $css);
			echo $docContent;
			exit();
		}
		function payment_orders_ajax_handler()
		{
			if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
				wp_die(esc_attr__('Security Check', 'wams'));
			}

			if (empty($_POST['param'])) {
				wp_send_json_error(__('Invalid Action.', 'wams'));
			}

			// return wp_send_json(['message' => "TEST AJAX from Admin " . __METHOD__]);
			switch ($_POST['param']) {
				case 'update_payment_order':
					$order_id = $_POST['orderId'];
					$requests =  $_POST['requests'];
					if (isset($requests) && is_array($requests)) {
						foreach ($requests as $request_id) {
							$entry = GFAPI::get_entry($request_id);
							if ($entry) {
								$entry['34'] = $order_id;
								$update = GFAPI::update_entry($entry);
								if (is_wp_error($update)) {
									wams()->common()->logger()::error(esc_html($update->get_error_message()));
								}
							}
						}
						wp_send_json_success(__('The Payment Order # ' . $order_id . ' Has been updated.', 'wams'));
					} else {
						wp_send_json_error(__('Invalid Action.', 'wams'));
					}

					// echo print_r($posts, true);
					wp_die();
					break;
			}
		}



		function tables_ajax_handler()
		{

			if (!isset($_GET['form'])) return;
			header("Content-Type: application/json");
			// $form_fields = WAMS()->admin()->get_form_fields(1);
			$form_id = $_GET['form'];
			$searchable_fields = isset($_GET['search_in']) ? $_GET['search_in'] : [];
			$offset = isset($_GET['offset']) ? $_GET['offset'] : 1;
			$limit = isset($_GET['limit']) ? $_GET['limit'] : 10; // Set default limit as 10
			$search = isset($_GET['search']) ? $_GET['search'] : ''; // Get search query
			$sort = isset($_GET['sort']) ? $_GET['sort'] : 'id'; // Get sort parameter
			$order = isset($_GET['order']) ? $_GET['order'] : 'ASC'; // Get sort Order
			$cache_key = crc32($form_id . $offset . $limit . $search . $sort . $order);
			$transient = wams_get_cache($cache_key);
			if (false === $transient) {
				$sorting = array('key' => $sort, 'direction' => $order);
				$paging          = array('offset' => $offset, 'page_size' => $limit);
				$search_criteria = ['status' => 'active'];
				if ($search != '' && !empty($searchable_fields)) {
					$keys = explode(',', $searchable_fields);
					foreach ($keys as $key) {
						$search_criteria['field_filters'][] = [
							'key'   => $key,  // Original Client ID Field ID in Add New Clients Form
							'operator' => 'CONTAINS', // check if the entry already copied
							'value' => $search
						];
					}
				}
				$total = 0;
				$data = GFAPI::get_entries(intval($form_id), $search_criteria, $sorting, $paging, $total);
				foreach ($data as &$entry) {
					$user_info = get_userdata($entry['created_by']);
					if ($user_info) {
						$display_name = $user_info->display_name;
						$entry['created_by_name'] =  $display_name;
					} else {
						$entry['created_by_name'] = "User not found";
					}
				}
				wams_set_cache($cache_key, ['data' => $data, 'total' => $total], 60 * MINUTE_IN_SECONDS);
			} else {
				$data = $transient['data'];
				$total = $transient['total'];
			}
			// Response data
			$response = array(
				'total' => $total,
				'rows' => $data
			);
			if (isset($_POST['clear_cache'])) {
				wams_delete_cache($cache_key);
			}
			// Convert data to JSON
			echo  json_encode($response);

			wp_die();
		}
	}
}
