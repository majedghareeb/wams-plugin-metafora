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
        public $entries = [];
        /**
         * User Dashboard constructor.
         * @since 1.0.0
         */
        public function __construct()
        {
        }
        private function get_inbox_entries()
        {
            return  \Gravity_Flow_API::get_inbox_entries();
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
            $user_entries = [];
            $user_entries_cache = get_transient('wams_inbox_' . $user_id);
            $user_entries_cache = false;
            if ($user_entries_cache === false) {
                $field_filters   = array();
                $field_filters[] = array(
                    'key'   => 'workflow_user_id_' . $user_id,
                    'value' => 'pending',
                );

                $entries =  $this->get_gf_entries($field_filters, 100);

                foreach ($entries as $entry) {
                    $entry_details = $this->get_gf_steps($entry);
                    if ($entry_details)
                        $user_entries[$entry['id']] = $entry_details;
                }
                set_transient('wams_inbox_' . $user_id, $user_entries, MINUTE_IN_SECONDS);
                $user_entries_cache = $user_entries;
            }
            return $user_entries_cache;
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
            $user    = wp_get_current_user();
            // $team_inbox = get_transient('wams_team_inbox_' . $user->ID);
            $team_inbox = false;
            $team_inbox = false;
            if ($team_inbox === false) {
                $team_inbox = [];
                $active_roles = $this->get_active_roles_in_workflow();
                $user_roles = $user->roles;
                if (!empty($user_roles)) {

                    foreach ($user_roles as $user_role) {
                        $field_filters[] = array(
                            'key'   => 'workflow_role_' . $user_role,
                            'value' => 'pending',
                        );
                    }
                    if (!empty($field_filters)) {
                        $entries =  $this->get_gf_entries($field_filters, 200);
                        foreach ($entries as $entry) {
                            $entry_details = $this->get_gf_steps($entry);
                            if ($entry_details)
                                $team_inbox[$entry['id']] = $entry_details;
                        }
                    }
                    set_transient('wams_team_inbox_' . $user->ID, $team_inbox, 3 * MINUTE_IN_SECONDS);
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
            // $active_roles = get_transient('wams_workflow_active_roles');
            $active_roles = false;
            if ($active_roles === false) {
                global $wpdb;
                $active_roles = [];
                $entry_meta_table = $wpdb->prefix . 'gf_entry_meta';
                $sql =  "SELECT meta_key as role_name, COUNT(*) as task_count FROM $entry_meta_table WHERE meta_key like '%workflow_role_%' and meta_value='pending' GROUP BY meta_key";

                $roles = $wpdb->get_results(
                    $wpdb->prepare(
                        $sql
                    )
                );
                if ($roles !== null) {
                    foreach ($roles as $role) {
                        // echo (strstr($role->role_name, 'workflow_role_', false));
                        $role_name = str_replace('workflow_role_', '', $role->role_name); // remove workflow_role_ string
                        $active_roles[$role->task_count] = $role_name;
                    }
                }
                set_transient('wams_workflow_active_roles', $active_roles,  MINUTE_IN_SECONDS);
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
            // $user_requests = get_transient('wams_user_requests_' . $user_id);
            $user_requests = false;
            if ($user_requests === false) {
                $user_requests = [];
                $field_filters =  array(
                    array(
                        'key'   => 'created_by',
                        'value' => $user_id
                    )
                );
                $entries =  $this->get_gf_entries($field_filters, 50);
                if ($entries) {
                    foreach ($entries as $entry) {
                        $entry_details = $this->get_gf_steps($entry);
                        if ($entry_details)
                            $user_requests[$entry['id']] = $entry_details;
                    }
                }
                set_transient('wams_user_requests_' . $user_id, $user_requests, MINUTE_IN_SECONDS);
            }
            return $user_requests;
            // require_once(WAMS_PLUGIN_PATH . "public/partials/gravity/tmpl-user-my-requests.php");
        }

        private function get_gf_entries($field_filters, $number_of_row = 100)
        {
            $total_count = 0;
            $search_criteria = array();
            $sorting = array('key' => 'date_created', 'direction' => 'DESC');
            $paging = array(
                'page_size' => $number_of_row,
            );
            $search_criteria['start_date'] = date('Y-m-d', strtotime('today - 60 days'));
            $search_criteria['end_date'] = date('Y-m-d');
            $search_criteria['field_filters'] = $field_filters;
            $search_criteria['status'] = 'active';
            $search_criteria['field_filters']['mode'] = 'any';
            $entries = \GFAPI::get_entries(0, $search_criteria, $sorting, $paging, $total_count);
            return $entries;
        }


        private function get_gf_steps(&$entry)
        {
            if (!empty($entry)) {
                $entry['created_by_name'] = $entry['created_by'] ? get_userdata($entry['created_by'])->display_name : '';
                $entry['form_name'] = \GFAPI::get_form($entry['form_id'])['title'];
                $api = new  \Gravity_Flow_Api($entry['form_id']);
                $step = $api->get_current_step($entry);
                if ($step) :
                    $assignees = $step->get_assignees();
                    $assignees_list = [];
                    foreach ($assignees as $assignee) {
                        $assignee_type = $assignee->get_type();
                        $assignee_id = $assignee->get_id();
                        switch ($assignee_type) {
                            case 'user_id':
                                $user_id = $assignee_id;
                                $assignees_list[] = get_userdata($user_id)->display_name ?? 'No Name';
                                break;
                            case 'role':
                                $role_name = str_replace(['um_', '-'], ['', ' '], $assignee->get_id());
                                $assignees_list[] = ucwords($role_name);
                                break;
                        }
                    }
                    $entry['step_name'] = $step->get_name();


                    $entry['assignees'] = $assignees_list;
                endif;
                return $entry;
            } else {
                return false;
            }
        }
        private function get_step_assignees($assignees)
        {
            $assignees_list = [];
            if (is_array($assignees) && !empty($assignees)) {
                foreach ($assignees as $key => $assignee) {
                    if ($assignee->get_type() == 'role') {

                        $users = get_users(
                            array(
                                'role__in' => $assignee->get_id(),
                            )
                        );
                    }
                }
            }
            return $assignees_list;
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

                $roles = $user->roles;  // Array of user roles
                // $role = get_role($roles[0]); // Get the first user role
                // $role_name = $role->name;


                $profile_details = [
                    'ID' => $current_user->ID,
                    'user_login' => $current_user->user_login,
                    'display_name' => $current_user->display_name,
                    'user_email' => $current_user->user_email,
                    'role' => $roles,
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
