<?php

namespace wams\core;

use Gravity_Flow_Api;

if (!defined('ABSPATH')) {
    exit;
}



if (!class_exists('wams\core\Gravity')) {

    class Gravity
    {
        public function get_gf_entries($field_filters, $number_of_row = 30)
        {
            $total_count = 0;
            $search_criteria = array();
            $sorting = array();
            $paging = array(
                'page_size' => $number_of_row,
            );
            $search_criteria['start_date'] = date('Y-mm-dd', strtotime('today - 60 days'));
            $search_criteria['end_date'] = date('Y-mm-dd');
            $search_criteria['field_filters'] = $field_filters;
            $search_criteria['status'] = 'active';
            $entries = \GFAPI::get_entries(0, $search_criteria, $sorting, $paging, $total_count);
            return $entries;
        }
        public function get_gf_steps($step_id)
        {
            $feed = \GFAPI::get_feed($step_id);
            $step = [];
            if ($feed) {
                $step['step_name'] = $feed['meta']['step_name'];
                $step['description'] = $feed['meta']['description'];
                $step['step_type'] = $feed['meta']['step_type'];
                return $step;
            } else {
                return false;
            }
        }

        public function get_step_data($form_id)
        {

            $api = new  Gravity_Flow_Api($form_id);

            $steps = $api->get_steps();

            $count = count($steps);

            $step_data = array();

            foreach ($steps as $i => $step) {
                if (!$step->is_active()) {
                    continue;
                }

                $step_id = $step->get_id();

                $step_icon = $step->get_icon_url();

                $feed_meta =  $step->get_feed_meta();

                $next_step = $step->get_next_step_id();

                $scheduled = $step->scheduled ? $step->get_schedule_timestamp() : null;

                $data = array(
                    'id'           => $step_id,
                    'type'         => $step->get_type(),
                    'name'         => $step->get_name(),
                    'label'        => $step->get_label(),
                    'is_active'        => $step->is_active(),
                    'icon'         => $step_icon,
                    'settings_url' => admin_url('?page=gf_edit_forms&view=settings&subview=gravityflow&id=' . $form_id . '&fid=' . $step_id),
                    'scheduled'    => $scheduled,
                );

                $statuses = $step->get_status_config();

                if ($step->supports_expiration() && $step->expiration) {
                    $statuses[] = array('status' => 'expired');
                }

                if ($step->revertEnable && $step->revertValue) {
                    $statuses[] = array('status' => 'reverted');
                }

                if ($feed_meta['feed_condition_conditional_logic']) {
                    $statuses[] = array('status' => 'skipped');
                }

                $targets = array();

                foreach ($statuses as $status) {

                    if ($status['status'] == 'reverted') {
                        $target = $step->revertValue;
                    } elseif ($status['status'] == 'skipped') {
                        $target = $next_step;
                    } else {
                        $destination_status_key = 'destination_' . $status['status'];
                        if (isset($step->{$destination_status_key})) {
                            $target = $step->{$destination_status_key};
                        } else {
                            $target = 'next';
                        }

                        if ($target == 'next') {
                            $target = $next_step;
                        }
                    }

                    if (is_numeric($target)) {
                        $target_step = gravity_flow()->get_step($target);
                        if (!$target_step->is_active()) {
                            $target = $step->get_next_step_id();
                        }
                    }

                    $targets[] = array(
                        'step_id' => $target,
                        'status' => $status['status'],
                    );
                }
                $data['targets'] = $targets;

                $step_data[] = $data;
            }

            return $step_data;
        }
        public function get_workflow_inbox()
        {
            // $args = array(
            //     'id_column'      => $request->get_param( config::ID_COLUMN ),
            //     'actions_column' => $request->get_param( config::ACTIONS_COLUMN ),
            //     'last_updated'   => $request->get_param( config::LAST_UPDATED ),
            //     'due_date'       => $request->get_param( config::DUE_DATE ),
            //     'form_id'        => $form_ids,
            //     'field_ids'      => GFAPI::current_user_can_any( 'gravityflow_status_view_all' ) ? $request->get_param( config::FIELDS ) : '',
            // );

            // $args = gravity_flow()->booleanize_shortcode_attributes( $args );
            // $args = wp_parse_args( $args, Gravity_Flow_Inbox::get_defaults() );
            $args = [];
            $entries     = \Gravity_Flow_API::get_inbox_entries($args, $total_count);
            $form_titles = array();
            $form_ids    = wp_list_pluck($entries, 'form_id');
            $forms       = \GFFormsModel::get_forms();

            foreach ($forms as $form) {
                if (isset($form_ids[$form->id])) {
                    $form_titles[$form->id] = $form->title;
                }
            }

            // $columns            = \Gravity_Flow_Inbox::get_columns($args);
            // $columns['form_id'] = __('Form ID', 'gravityforms');
            $rows               = array();

            foreach ($entries as $entry) {
                $row  = array();



                $rows[] = $entry;
            }

            // JavaScript doesn't guarantee the order of object keys so deliver as numeric array


            // $data = array(
            //     'total_count' => $total_count,
            //     'rows'        => $rows,
            //     'columns'     => $columns_numeric_array,
            //     'form_titles' => $form_titles,
            // );

            return $entries;
        }

        function get_cached_form_entries($form_id, $args = [])
        {
            if (!intval($form_id) > 0) return;
            $entry_keys =  (isset($args['keys']) && is_array($args['keys'])) ? $args['keys'] : false;
            $all_entries = get_transient($form_id . '_cached_entries');
            if ($all_entries == false) {
                $search_criteria = array(
                    'status'        => 'active',
                );
                $total_count = 0;
                $all_entries = [];
                $page = 0;
                $batch_size = 100;
                do {
                    $paging = array('offset' => $page, 'page_size' => $batch_size); // Adjust this based on your requirements
                    $entries = \GFAPI::get_entries($form_id, $search_criteria, [], $paging, $total_count);
                    foreach ($entries as $entry) {

                        $all_entries[] = ($entry_keys) ? array_intersect_key($entry, $entry_keys) : $entry;
                    }
                    // Increment the page number for the next request
                    $page = $batch_size + $page;
                } while (count($entries) === $batch_size);
                set_transient($form_id . '_cached_entries', $all_entries, 5 * MINUTE_IN_SECONDS);
            }
            return $all_entries;
        }
    }
}
