<?php

namespace wams\frontend;

use GFAPI;
use WP_User;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\frontend\User_Dashboard')) {

    /**
     * Class User_Dashboard
     *
     * @package um\frontend
     *
     * @since 1.0.0
     */
    class User_Dashboard
    {
        /**
         * User Dashboard constructor.
         * @since 1.0.0
         */
        public function __construct()
        {
        }

        /**
         * Get User's Tasks from Gravity Flow and cache it in transiet wams_inbox_$User_ID
         * 
         * 
         * @since    1.0.0
         */
        public function wams_user_tasks()
        {
            if (!class_exists('GFAPI')) return [];
            $user_id    = get_current_user_id();
            $user_inbox = get_transient('wams_inbox_' . $user_id);
            if ($user_inbox === false) {
                $field_filters   = array();
                $field_filters[] = array(
                    'key'   => 'workflow_user_id_' . $user_id,
                    'value' => 'pending',
                );

                $entries =  $this->get_gf_entries($field_filters, 10);

                foreach ($entries as $entry) {
                    if ($this->get_gf_steps($entry))
                        $user_inbox[$entry['id']] = $this->get_gf_steps($entry);
                }
                set_transient('wams_inbox_' . $user_id, $user_inbox,  3 * MINUTE_IN_SECONDS);
            }
            return $user_inbox;
            // require_once(WAMS_PLUGIN_PATH . "public/partials/gravity/tmpl-user-my-tasks.php");
        }
        /**
         * Shortcode Call Back : [user-my-requests]
         * 
         * @since    1.0.0
         */
        public function wams_user_team_tasks()
        {
            if (!class_exists('GFAPI')) return [];
            $user_id    = get_current_user_id();
            $team_inbox = get_transient('wams_team_inbox_' . $user_id);
            if ($team_inbox === false) {
                $team_inbox = [];
                $active_roles = $this->get_active_roles_in_workflow();
                $user_meta = get_userdata($user_id);
                $user_roles = $user_meta->roles;
                if (!empty($user_roles)) {
                    $field_filters   = ['mode' => 'any'];
                    foreach ($user_roles as $user_role) {
                        $role_is_active =  array_search($user_role, $active_roles);
                        if ($role_is_active) {
                            $field_filters[] = array(
                                'key'   => 'workflow_role_' . $user_role,
                                'value' => 'pending',
                            );
                            // $pending[$user_role] = $role_is_active;
                        }
                    }
                    $entries =  $this->get_gf_entries($field_filters, 10);
                    foreach ($entries as $entry) {
                        if ($this->get_gf_steps($entry))
                            $team_inbox[$entry['id']] = $this->get_gf_steps($entry);
                    }
                    set_transient('wams_team_inbox_' . $user_id, $team_inbox, 3 * MINUTE_IN_SECONDS);
                }
            }
            return $team_inbox;
        }

        /**
         * Get Roles pending in tasks lists as assignees
         * To be used in filtering the search for team task
         */
        public function get_active_roles_in_workflow()
        {

            $active_roles = get_transient('wams_workflow_active_roles');
            if ($active_roles === false) {
                global $wpdb;
                $active_roles = [];
                $entry_meta_table = $wpdb->prefix . 'gf_entry_meta';

                $sql = $wpdb->prepare("SELECT meta_key as role_name, COUNT(*) as task_count FROM $entry_meta_table WHERE meta_key like '%workflow_role_%' and meta_value='pending' GROUP BY meta_key");
                $roles  = $wpdb->get_results($sql);
                if ($roles !== null) {
                    foreach ($roles as $role) {
                        // echo (strstr($role->role_name, 'workflow_role_', false));
                        $role_name = str_split($role->role_name, 14)[1]; // remove workflow_role_ string
                        $active_roles[$role->task_count] = $role_name;
                    }
                }
                set_transient('wams_workflow_active_roles', $active_roles, 3 * MINUTE_IN_SECONDS);
            }
            return $active_roles;
        }

        /**
         * Shortcode Call Back : [user-team-tasks]
         * 
         * @since    1.0.0
         */
        public function wams_user_requests()
        {
            // $roles = $this->get_active_roles_in_workflow();
            if (!class_exists('GFAPI')) return [];
            $user_id    = get_current_user_id();
            $user_requests = get_transient('wams_user_requests_' . $user_id);
            if ($user_requests === false) {
                $user_requests = [];
                $field_filters =  array(
                    array(
                        'key'   => 'created_by',
                        'value' => $user_id
                    ),
                    array(
                        'key'   => 'workflow_final_status',
                        'value' => 'pending'
                    )
                );
                $entries =  $this->get_gf_entries($field_filters, 10);
                if ($entries) {
                    foreach ($entries as $entry) {
                        if ($this->get_gf_steps($entry))
                            $user_requests[$entry['id']] = $this->get_gf_steps($entry);
                    }
                }
                set_transient('wams_user_requests_' . $user_id, $user_requests, MINUTE_IN_SECONDS);
            }
            return $user_requests;
            // require_once(WAMS_PLUGIN_PATH . "public/partials/gravity/tmpl-user-my-requests.php");
        }

        private function get_gf_entries($field_filters, $number_of_row = 30)
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

        private function get_gf_steps($entry)
        {
            if (!empty($entry['workflow_step'])) {
                $feed = \GFAPI::get_feed($entry['workflow_step']);
                $step = [];
                if ($feed) :

                    $step_name = $feed['meta']['step_name'];
                    $step['step_name'] = $step_name ? $step_name : '';
                    $step['form_id'] = $entry['form_id'];
                    $step['form_name'] = \GFAPI::get_form($entry['form_id'])['title'];
                    $step['created_by'] = $entry['created_by'] ?? false;
                    $step['created_by_name'] = $step['created_by'] ? get_userdata($entry['created_by'])->display_name : '';
                    $step['date_created'] = $entry['date_created'];
                    $step['date_updated'] = $entry['date_updated'];
                endif;
                return $step;
            } else {
                return false;
            }
        }

        /**
         * Get Field value for and entry
         * @param   string|Int  Entry ID
         * @param   string|Int  Field ID
         * 
         * @return  string|false Field Value or False if Entry does not exists
         * 
         * @since    1.0.0
         */
        public static function wams_get_entry($entry_id, $field_id)
        {
            $entry = \GFAPI::get_entry($entry_id);
            if ($entry) return rgar($entry, $field_id);
            return false;
        }

        /**
         * Get Current User Profile Data
         */
        public static function show_user_profile_details()
        {
            $current_user_id = get_current_user_id();
            $user = wp_get_current_user();
            $current_user = get_userdata($current_user_id);
            $profile_details = get_transient('wams_user_profile_details_' . $current_user_id);
            $profile_details = false;
            if ($profile_details === false) {
                $user_project_id = get_user_meta($current_user_id, $key = 'user_project', $single = true);
                $user_project = self::wams_get_entry($user_project_id, 1);
                $user_groups_id = get_user_meta($current_user_id, $key = 'user_groups', $single = true);
                if ($user_groups_id != '') {
                    $groups = explode(',', $user_groups_id);
                } else {
                    $groups = array('general');
                }
                $user_groups = '';
                // foreach ($groups as $group) {
                //     $user_groups .= '<li>';
                //     $user_groups .= self::get_gf_user_value('151', '5', str_replace(' ', '', $group), '1');
                //     $user_groups .= '</li>';
                // }
                $user_direct_manager_id = get_user_meta($current_user_id, $key = 'user_direct_manager', $single = true);
                // $user_direct_manager = self::get_gf_user_value('150', '10', $user_direct_manager_id, '6');
                // $user_nationality = get_user_meta($current_user_id, $key = 'user_nationality', $single = true);
                $job_title = get_user_meta($current_user_id, $key = 'job_title', $single = true);
                $phone_number = get_user_meta($current_user_id, $key = 'phone_number', $single = true);
                $nickname = get_user_meta($current_user_id, $key = 'nickname', $single = true);
                $_um_verified = get_user_meta($current_user_id, $key = '_um_verified', $single = true);
                $account_status = get_user_meta($current_user_id, $key = 'account_status', $single = true);

                $roles = $user->roles; // Array of user roles
                $role = get_role($roles[0]); // Get the first user role
                $role_name = $role->name;


                $profile_details = [
                    'ID' => $current_user->ID,
                    'user_login' => $current_user->user_login,
                    'display_name' => $current_user->display_name,
                    'user_email' => $current_user->user_email,
                    'role' => $role_name,
                    'job_title' => $job_title,
                    'user_project_id' => $user_project_id,
                    'user_project' => $user_project,
                    'user_groups' => $user_groups,
                    'phone_number' => $phone_number,
                    'account_status' => $account_status,
                ];
                set_transient('wams_user_profile_details_' . $current_user_id, $profile_details, 10 * MINUTE_IN_SECONDS);
            }
            return $profile_details;
        }
    }
}
