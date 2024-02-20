<?php

namespace wams\admin\core;

use GFAPI;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\modules\Gravity_Functions')) {

	/**
	 * Class Debug
	 * @package wams\admin\core
	 */
	class Gravity_Functions
	{

		/**
		 * Copy Entry From One Site to another
		 * @param	int 	Entry ID
		 * @param	int		Source Site ID
		 * @param	int		Destination Site ID
		 * 
		 * @return	Mixed	WP_ERROR or True
		 */
		public function copy_entries($entry_id, $source_blog_id, $destination_blog_id)
		{
			/**
			 * Copy Entries
			 */
			global $wpdb;

			$message = [];
			if ($source_blog_id != 1) {
				$entry_source_table = $wpdb->prefix . $source_blog_id . '_gf_entry';
				$entry_meta_source_table = $wpdb->prefix . $source_blog_id . '_gf_entry_meta';
				$entry_notes_table = $wpdb->prefix . $source_blog_id . '_gf_entry_notes';
			} else {
				$entry_source_table = $wpdb->prefix . 'gf_entry';
				$entry_meta_source_table = $wpdb->prefix . 'gf_entry_meta';
				$entry_notes_table = $wpdb->prefix . 'gf_entry_notes';
			}

			if ($destination_blog_id != 1) {
				$entry_destination_table = $wpdb->prefix . $destination_blog_id . '_gf_entry';
				$entry_meta_destination_table = $wpdb->prefix . $destination_blog_id . '_gf_entry_meta';
				$entry_notes_destination_table = $wpdb->prefix . $destination_blog_id . '_gf_entry_notes';
			} else {
				$entry_destination_table = $wpdb->prefix .  '_gf_entry';
				$entry_meta_destination_table = $wpdb->prefix .  '_gf_entry_meta';
				$entry_notes_destination_table = $wpdb->prefix .  '_gf_entry_notes';
			}

			// Check if entry exists
			$result = $wpdb->get_row("SELECT id FROM $entry_destination_table WHERE id=$entry_id");
			if ($result) {
				$message[] =  ' ' . $entry_id . ' Entry already exists';
			} else {

				$sql1 = $wpdb->prepare("INSERT INTO $entry_destination_table SELECT * FROM $entry_source_table WHERE id=%d", $entry_id);
				$sql2 = $wpdb->prepare("INSERT INTO $entry_meta_destination_table SELECT * FROM $entry_meta_source_table WHERE entry_id=%d", $entry_id);
				$sql3 = $wpdb->prepare("INSERT INTO $entry_notes_destination_table SELECT * FROM $entry_notes_table WHERE entry_id=%d", $entry_id);
				$result1 = $wpdb->query($sql1);
				if ($result1 === false) {
					$message[] =  '' . $wpdb->last_error . '';
				} else {
					$message[] =  '' . $entry_id . ' Copied! ';
				}
				$result2 = $wpdb->query($sql2);
				if ($result2 === false) {
					$message[] =  ($result2 === false) ? '' . $wpdb->last_error . '' : '';
				}
				$result3 = $wpdb->query($sql3);
				if ($result3 === false) {
					$message[] =  ($result3 === false) ? '' . $wpdb->last_error . '' : '';
				}
				if ($result1 && $result2) {

					// $delete = \GFAPI::delete_entry($entry_id);
					// if ($delete) {
					// 	$message['messages'][] =  '' . $entry_id . ' Original Deleted! ';
					// }
					// $sql4 = $wpdb->prepare("DELETE FROM $entry_notes_table WHERE entry_id=%d", $entry_id);
					// $result4 = $wpdb->query($sql4);
					// if ($result4 === false) {
					// 	$message['messages'][] =  ($result4 === false) ? '' . $wpdb->last_error . '' : '';
					// }
				}
				restore_current_blog();
			}

			return $message;
		}

		/**
		 * Copy View from One Site to another
		 * @param	int 	Form ID
		 * @param	int 	View ID
		 * @param	int		Source Site ID
		 * @param	int		Destination Site ID
		 * 
		 * @return	Mixed	WP_ERROR or True
		 */
		public function copy_view($form_id, $view_id, $dest_form_id, $source_blog_id, $destination_blog_id)
		{
			/**
			 * Copy connected views
			 */
			global $wpdb;
			$message = [];
			if ($source_blog_id != 1) {
				$source_post_table = $wpdb->prefix . $source_blog_id . '_posts';
				$source_post_meta_table = $wpdb->prefix . $source_blog_id . '_postmeta';
			} else {
				$source_post_table = $wpdb->prefix . 'posts';
				$source_post_meta_table = $wpdb->prefix . 'postmeta';
			}

			if ($destination_blog_id != 1) {
				$destination_post_table = $wpdb->prefix . $destination_blog_id . '_posts';
				$destination_post_meta_table = $wpdb->prefix . $destination_blog_id . '_postmeta';
			} else {
				$destination_post_table = $wpdb->prefix . 'posts';
				$destination_post_meta_table = $wpdb->prefix . 'postmeta';
			}



			$results = $wpdb->get_row("SELECT post_id FROM $source_post_meta_table WHERE meta_key='_gravityview_form_id' AND meta_value=$form_id");

			if (!$results) {
				$message[] = "View for Form ID $form_id does not exists";
			} else {
				$get_view = $wpdb->get_row("SELECT id FROM $destination_post_table WHERE id=$view_id");
				if (!$get_view) {
					$sql1 = $wpdb->prepare("INSERT INTO $destination_post_table SELECT * FROM $source_post_table WHERE id=%d", $view_id);
					$result = $wpdb->query($sql1);
					if ($result === false) {
						$message[] =  '' . $wpdb->last_error . '';
					}
					$sql2 = $wpdb->prepare("INSERT INTO $destination_post_meta_table SELECT * FROM $source_post_meta_table WHERE post_id=%d", $view_id);
					$result = $wpdb->query($sql2);
					if ($result === false) {
						$message[] =  '' . $wpdb->last_error . '';
					}
					$message[] = "View of form ID: $form_id with Post ID: $view_id has been copied!";
				} else {
					$message[] = "View ID $view_id already exists";
				}
			}

			// $sql_notes = $wpdb->prepare("INSERT INTO $destination_post_meta_table (meta_id,post_id, meta_key, meta_value) (
			// 	SELECT meta_id,post_id,
			// 		CASE 
			// 			WHEN meta_key = '_gravityview_form_id' AND meta_value = %1d  THEN %2d
			// 			ELSE entry_id
			// 			END as meta_id meta_value 
			// 	FROM $entry_notes_source_table WHERE note_type like '%gravityflow%' AND entry_id=%d)", $form_id, $dest_form_id);

			return $message;
		}

		/**
		 * Copy Form from One Site to another
		 * @param	int 	Form  ID
		 * @param	int		Source Site ID
		 * @param	int		Destination Site ID
		 * 
		 * @return	Mixed	WP_ERROR or True
		 */

		public function copy_form($form_id, $source_blog_id, $destination_blog_id)
		{
			$message = [];
			switch_to_blog($destination_blog_id);
			$form = \GFAPI::get_form($form_id);
			switch_to_blog($source_blog_id);
			if ($form) {
				$message[] = 'Form Already Exists';
				// return $message;
			} else {
				global $wpdb;
				if ($source_blog_id != 1) {
					$source_form_table = $wpdb->prefix . $source_blog_id . '_gf_form';
					$source_form_meta_table = $wpdb->prefix . $source_blog_id . '_gf_form_meta';
					$source_addon_feed_table = $wpdb->prefix . $source_blog_id . '_gf_addon_feed';
				} else {
					$source_form_table = $wpdb->prefix . 'gf_form';
					$source_form_meta_table = $wpdb->prefix . 'gf_form_meta';
					$source_addon_feed_table = $wpdb->prefix . 'gf_addon_feed';
				}

				if ($destination_blog_id != 1) {
					$destination_form_table = $wpdb->prefix . $destination_blog_id . '_gf_form';
					$destination_form_meta_table = $wpdb->prefix . $destination_blog_id . '_gf_form_meta';
					$destination_addon_feed_table = $wpdb->prefix . $destination_blog_id . '_gf_addon_feed';
				} else {
					$destination_form_table = $wpdb->prefix . 'gf_form';
					$destination_form_meta_table = $wpdb->prefix . 'gf_form_meta';
					$destination_addon_feed_table = $wpdb->prefix . 'gf_addon_feed';
				}
				/**
				 * Check Form if exists
				 */
				$result = $wpdb->get_row("SELECT id FROM $destination_form_table WHERE id=$form_id");

				if (!$result) {

					$message[] =  'Form Does Not exist; the form will be copied!';
					$sql1 = $wpdb->prepare("INSERT INTO $destination_form_table SELECT * FROM $source_form_table WHERE id=%d", $form_id);
					$result1 = $wpdb->query($sql1);
					$message[] = ($result1 === false) ? '' . $wpdb->last_error . '' :  'form ' . $form_id . '  copy done';
					$sql2 = $wpdb->prepare("INSERT INTO $destination_form_meta_table SELECT * FROM $source_form_meta_table WHERE form_id=%d", $form_id);
					$result2 = $wpdb->query($sql2);
					$message[] = ($result2 === false) ? '' . $wpdb->last_error . '' :  'form ' . $form_id . ' meta copy done';
					$sql3 = $wpdb->prepare("INSERT INTO $destination_addon_feed_table SELECT * FROM $source_addon_feed_table WHERE form_id=%d", $form_id);
					$result3 = $wpdb->query($sql3);
					$message[] = ($result3 === false) ? '' . $wpdb->last_error . '' : 'form ' . $form_id . ' addon feeds copy done';
				} else {
					$message[] = 'Form Already Exists';
				}
			}
			return $message;
		}

		public static function get_entry_workflow_final_status($form_id, $status = '', $year = null)
		{
			$field_filters   = array();
			// if ($status != null) {
			//     $field_filters[] = array(
			//         'key'   => 'workflow_final_status',
			//         'value' => $status,
			//     );
			// }
			$field_filters[] = array(
				'key'   => 'workflow_final_status',
				'value' => $status,
			);
			if ($year != null) {
				$search_criteria['start_date'] = date($year . '-01-01');
				$search_criteria['end_date'] = date($year . '-12-31');
			}
			$search_criteria['field_filters'] = $field_filters;
			// $search_criteria['status'] = 'active';
			$count = GFAPI::count_entries($form_id, $search_criteria);
			// $search_criteria['start_date'] = date('2021-01-01');
			// $search_criteria['end_date'] = date('2021-12-31');
			return $count;
		}
		public static function archive_entries($form_id, $year)
		{

			$field_filters[] = array(
				'key'   => 'workflow_final_status',
				'operator' => 'IS NOT',
				'value' => 'pending',
			);
			$search_criteria['field_filters'] = $field_filters;
			$sorting         = array();
			$paging          = array('offset' => 0, 'page_size' => 500);
			$search_criteria['start_date'] = date($year . '-01-01');
			$search_criteria['end_date'] = date($year . '-12-31');
			$entry_ids = GFAPI::get_entry_ids($form_id, $search_criteria, $sorting, $paging);
			return $entry_ids;
		}

		/**
		 * Get Breakdown of Gravity Forms Entries per year for the past 5 years
		 * 
		 * @param 	int	Form ID
		 * 
		 * @return array [ [year,status,number of entries]]
		 */

		public static function breakdown_entry_count($form_id)
		{
			$statuses = [
				'approved',
				'complete',
				'rejected',
				'cancelled',
				'pending',
				'',
			];

			$currentYear = date('Y');
			//TODO : Change it to settings
			$lastSixYears = [];
			for ($i = 0; $i < 6; $i++) {
				$lastFiveYears[] = $currentYear - $i;
			}

			$result = [];
			foreach ($lastFiveYears as $year) {
				foreach ($statuses as $status) {
					$field_filters   = array();
					$field_filters[] = array(
						'key'   => 'workflow_final_status',
						'value' => $status,
					);
					$search_criteria['start_date'] = date($year . '-01-01');
					$search_criteria['end_date'] = date($year . '-12-31');
					$search_criteria['field_filters'] = $field_filters;
					// $search_criteria['status'] = 'active';

					$result[$year][$status] = GFAPI::count_entries($form_id, $search_criteria);
				}
			}
			return $result;
		}
	}
}
