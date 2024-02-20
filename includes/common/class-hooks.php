<?php

namespace wams\common;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\common\Hooks')) {

    /**
     * Class Screen
     *
     * @package wams\common
     */
    class Hooks
    {

        /**
         * Hooks constructor.
         */
        public function __construct()
        {
            add_action('gform_user_registered', [$this, 'add_user_role'], 10, 3);

            // add_filter('body_class', array(&$this, 'remove_admin_bar'), 1000, 1);
        }

        /**
         * Remove admin bar classes
         *
         * @param array $classes
         *
         * @return array
         */
        public function remove_admin_bar($classes)
        {
            // if (is_user_logged_in()) {
            // 	if (wams_user('can_not_see_adminbar')) {
            // 		$search = array_search('admin-bar', $classes, true);
            // 		if (!empty($search)) {
            // 			unset($classes[$search]);
            // 		}
            // 	}
            // }

            // return $classes;
        }

        /**
         * Add User Role when user register
         */
        function add_user_role($user_id, $feed, $entry)
        {
            // get role from field 5 of the entry.
            //TODO setup settings page for role field ID
            $selected_role = rgar($entry, '12');
            $user          = new \WP_User($user_id);
            $user->add_role($selected_role);
        }
    }
}
