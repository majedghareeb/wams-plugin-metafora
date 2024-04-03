<?php

namespace wams\common;

use WP_Error;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\common\User_Logins')) {

    /**
     * Class User_Logins
     *
     * @package wams\common
     */
    class User_Logins
    {

        function log_user_login($user_login, $user)
        {
            // Get user IP address
            $user_ip = $_SERVER['REMOTE_ADDR'];
            // Get user browser information
            $user_browser = $_SERVER['HTTP_USER_AGENT'];
            // Format log data
            // $log_data = date('Y-m-d H:i:s') . " - User: $user_login (ID: $user->ID) logged in. IP: $user_ip, Browser: $user_browser\n";
            $log_data = [
                'date' =>     date('Y-m-d H:i:s', current_time('timestamp')),
                'user_login' => $user_login,
                'user_id' => $user->ID,
                'ip_address' => $user_ip,
                'browser' => $user_browser,
            ];
            $this->insert_user_logins($log_data);
        }

        function delete_user_logins($user_id)
        {
            global $wpdb;
            $wpdb->delete(
                "{$wpdb->prefix}wams_user_logins",
                array(
                    'user_id'    => $user_id
                ),
                array(
                    '%d'
                )
            );
        }
        function insert_user_logins($log_data)
        {
            global $wpdb;
            $wpdb->insert(
                "{$wpdb->prefix}wams_user_logins",
                array(
                    'date'   => $log_data['date'],
                    'user_id'   => $log_data['user_id'],
                    'user_login'    => $log_data['user_login'],
                    'ip_address'  => $log_data['ip_address'],
                    'browser'  => $log_data['browser'],
                ),
                array(
                    '%s',
                    '%d',
                    '%s',
                    '%s',
                    '%s',
                )
            );
        }
        function get_user_logins($user_id)
        {
            global $wpdb;
            $result = $wpdb->get_results($wpdb->prepare(
                "SELECT *
				FROM {$wpdb->prefix}wams_user_logins
				WHERE user_id = %d
				LIMIT 50",
                $user_id,
            ), ARRAY_A);
            return $result;
        }
    }
}
