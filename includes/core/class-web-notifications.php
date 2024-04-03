<?php

namespace wams\core;


if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\core\Web_Notifications')) {

    class Web_Notifications
    {
        /**
         * @var bool|array
         */
        public $notitications_settings;

        public function __construct()
        {
            // $this->notitications_settings = get_option('wams_web_notitications_settings');
        }

        function web_notifications_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
                wp_die(esc_attr__('Nonce is wrong', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }
            $user_id = get_current_user_id();
            // return wp_send_json(['message' => "TEST AJAX from Admin " . __METHOD__]);
            switch ($_POST['param']) {
                case 'save-user-notification-settings':
                    $formData = wp_parse_args($_POST['formData']);
                    $update_user_meta = update_user_meta($user_id, 'notification-settings', $formData);
                    if (isset($formData['telegram-chat-id'])) update_user_meta($user_id, 'telegram_chat_id', $formData['telegram-chat-id']);
                    wp_send_json_success([
                        'status' => 'success',
                        'message' => __('Settings Saved', 'wams'),
                        'formData' => $formData
                    ]);
                    break;
                case 'ajax_get_menu_inbox_count':
                    wp_send_json($this->get_inbox_count());
                    break;
                case 'ajax_get_new_count':
                    wp_send_json($this->ajax_get_new_count());
                    break;
                case 'ajax_check_update':
                    echo ($this->reload_notification_box());
                    break;
                case 'notification_action_read':
                    $selectedIds = $_POST['selectedIds'] ?? 0;

                    if ($selectedIds && is_array($selectedIds)) {
                        foreach ($selectedIds as $id) {
                            $this->set_as_read($id);
                        }
                    } else {
                        if ($selectedIds) $this->set_as_read($selectedIds);
                    }
                    // $selectedIds = implode(", ", $selectedIds);
                    wp_send_json($selectedIds);
                    wp_die();
                    break;
                case 'notification_action_unread':
                    $selectedIds = $_POST['selectedIds'] ?? 0;
                    if ($selectedIds && is_array($selectedIds)) {
                        foreach ($selectedIds as $id) {
                            $this->set_as_unread($id);
                        }
                    } else {
                        if ($selectedIds) $this->set_as_unread($selectedIds);
                    }
                    wp_send_json(json_encode($_POST));
                    wp_die();

                    break;
                case 'notification_action_delete':
                    $selectedIds = $_POST['selectedIds'] ?? 0;
                    if ($selectedIds && is_array($selectedIds)) {
                        foreach ($selectedIds as $id) {
                            $this->delete_log($id);
                        }
                    } else {
                        if ($selectedIds) $this->delete_log($selectedIds);
                    }
                    // $selectedIds = implode(", ", $selectedIds);
                    wp_send_json($selectedIds);
                    wp_die();
                    break;
            }
        }

        /**
         * Did user enable this web notification?
         *
         * @param $key
         * @param $user_id
         *
         * @return bool
         */
        function user_enabled($key, $user_id)
        {

            $prefs = get_user_meta($user_id, 'notification-settings', true);
            $web_prefs = $prefs['web'] ?? false;
            if (isset($web_prefs['notification-enabled']) &&  $web_prefs['notification-enabled'] == 'on') {
                if (isset($web_prefs[$key]) && $web_prefs[$key] == 'on') {
                    return true;
                }
            }

            return false;
        }

        /**
         * Gets notifications
         *
         * @param int $per_page
         * @param bool $unread_only
         * @param bool $count
         * @return array|bool|int|null|object
         */
        function get_notifications($per_page = 10, $unread_only = false, $count = false)
        {
            global $wpdb;
            $user_id = get_current_user_id();
            $table_name = $wpdb->prefix . "wams_notifications";

            if ($unread_only == 'unread' && $count == true) {

                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT * 
				FROM {$table_name} 
				WHERE user = %d AND 
					  status = 'unread'",
                    $user_id
                ));

                return $wpdb->num_rows;
            } elseif ($unread_only == 'unread') {

                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT * 
				FROM {$table_name} 
				WHERE user = %d AND 
					  status = 'unread' 
				ORDER BY time DESC 
				LIMIT %d",
                    $user_id,
                    $per_page
                ));
            } else {

                $results = $wpdb->get_results($wpdb->prepare(
                    "SELECT * 
				FROM {$table_name} 
				WHERE user = %d 
				ORDER BY time DESC 
				LIMIT %d",
                    $user_id,
                    $per_page
                ));
            }

            if (!empty($results)) {
                return $results;
            }

            return false;
        }

        function show_notification_menu()
        {

            WAMS()->get_template('notifications/notifications-menu.php', '', [], true);
        }
        function reload_notification_box()
        {

            $notifications = $this->get_notifications();
            $args = [
                'notifications' => $notifications,
            ];
            WAMS()->get_template('notifications/notifications-box.php', '', $args, true);
        }

        /**
         * Saves a notification
         *
         * @param $user_id
         * @param $type
         * @param array $vars
         */
        function store_notification($user_id, $type = '', $title = '', $vars = array())
        {
            global $wpdb;

            // Check if user opted-in
            if ($type != '' && !$this->user_enabled($type, $user_id)) {
                return;
            }

            if ($vars && isset($vars['message'])) {
                $content = $vars['message'];
            } else {
                $content = 'No Message';
            }
            // $content = $this->get_notify_content($type, $vars);

            if ($vars && isset($vars['photo'])) {
                $photo = $vars['photo'];
            } else {
                $photo = um_get_default_avatar_uri($user_id);
            }

            $url = '';
            if ($vars && isset($vars['notification_uri'])) {
                $url = $vars['notification_uri'];
            }

            $table_name = $wpdb->prefix . "wams_notifications";


            if (!empty($content)) {
                // Try to update a similar log
                $result = $wpdb->get_var($wpdb->prepare(
                    "SELECT id 
				FROM {$table_name} 
				WHERE user = %d AND 
					  type = %s AND 
					  content = %s 
				ORDER BY time DESC",
                    $user_id,
                    $title,
                    $content
                ));

                if (!empty($result)) {
                    $wpdb->update(
                        $table_name,
                        array(
                            'status'    => 'unread',
                            'time'      => current_time('mysql'),
                            'url'       => $url
                        ),
                        array(
                            'user'      => $user_id,
                            'type'      => $title,
                            'content'   => $content
                        )
                    );
                    $do_not_insert = true;
                }
            }

            if (isset($do_not_insert)) {
                return;
            }

            if (!empty($content)) {
                $wpdb->insert(
                    $table_name,
                    array(
                        'time'      => current_time('mysql'),
                        'user'      => $user_id,
                        'status'    => 'unread',
                        'photo'     => $photo,
                        'type'      => $type,
                        'url'       => $url,
                        'content'   => $content
                    )
                );
            }
        }
        /**
         * Get notification content
         *
         * @param $type
         * @param array $vars
         *
         * @return string|null
         */
        function get_notify_content($type, $vars = array())
        {
            $content = 'No Content';
            if ($vars) {
                foreach ($vars as $key => $var) {
                    $content = str_replace('{' . $key . '}', $var, $content);
                }
            }

            $content = implode(' ', array_unique(explode(' ', $content)));

            return $content;
        }

        /**
         * Deletes a notification by its ID
         *
         * @param $notification_id
         */
        function delete_log($notification_id)
        {
            global $wpdb;
            if (!is_user_logged_in()) {
                return;
            }
            $table_name = $wpdb->prefix . "wams_notifications";
            $wpdb->delete($table_name, array('id' => $notification_id));
        }

        /**
         * Get unread count by user ID
         *
         * @param int $user_id
         * @return int
         */
        function unread_count($user_id = 0)
        {
            global $wpdb;

            $user_id = ($user_id > 0) ? $user_id : get_current_user_id();

            $table_name = $wpdb->prefix . "wams_notifications";
            $results = $wpdb->get_results($wpdb->prepare(
                "SELECT id 
			FROM {$table_name} 
			WHERE user = %d AND 
			      status='unread'",
                $user_id
            ));

            if ($wpdb->num_rows == 0) {
                return 0;
            } else {
                return $wpdb->num_rows;
            }
        }

        /**
         * Mark as read.
         *
         * @param int $notification_id
         */
        public function set_as_read($notification_id)
        {
            global $wpdb;

            $user_id = get_current_user_id();
            $wpdb->update(
                "{$wpdb->prefix}wams_notifications",
                array(
                    'status' => 'read',
                ),
                array(
                    'user' => $user_id,
                    'id'   => $notification_id,
                ),
                array(
                    '%s',
                ),
                array(
                    '%d',
                    '%d',
                )
            );
        }
        /**
         * Mark as read.
         *
         * @param int $notification_id
         */
        public function set_as_unread($notification_id)
        {
            global $wpdb;

            $user_id = get_current_user_id();
            $wpdb->update(
                "{$wpdb->prefix}wams_notifications",
                array(
                    'status' => 'unread',
                ),
                array(
                    'user' => $user_id,
                    'id'   => $notification_id,
                ),
                array(
                    '%s',
                ),
                array(
                    '%d',
                    '%d',
                )
            );
        }

        /**
         * Delete a notification
         */
        public function ajax_delete_log()
        {
            UM()->check_ajax_nonce();

            if (empty($_POST['notification_id'])) {
                wp_send_json_error(__('Wrong notification ID', 'um-notifications'));
            }

            $this->delete_log(absint($_POST['notification_id']));

            global $wpdb;

            $time       = absint($_POST['time']);
            $time_where = $wpdb->prepare(' AND time <= %s ', date('Y-m-d H:i:s', $time));

            $unread  = (bool) $_POST['unread'];
            $offset  = absint($_POST['offset']);
            $user_id = get_current_user_id();

            $unread_where = $unread ? " AND status = 'unread' " : '';
            $log_types    = array_keys($this->get_log_types());

            $notifications = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT *
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode("','", $log_types) . "') AND
				  user = %d
				  {$unread_where}
				  {$time_where}
				ORDER BY time DESC
				LIMIT 1
				OFFSET %d",
                    $user_id,
                    $offset - 1
                )
            );

            if (!empty($notifications)) {
                $notifications = apply_filters('um_notifications_get_notifications_response', $notifications, 1, $unread, $time);
                $notifications = $this->built_notifications_template($notifications);
            } else {
                $notifications = array();
            }

            $total = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*)
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode("','", $log_types) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}",
                    $user_id
                )
            );

            $total = !empty($total) ? absint($total) : 0;

            $output = apply_filters(
                'um_notifications_ajax_on_load_notification',
                array(
                    'notifications' => $notifications,
                    'total'         => $total,
                )
            );
            wp_send_json_success($output);
        }

        /**
         * Delete all notification
         */
        public function ajax_delete_all_log()
        {
            UM()->check_ajax_nonce();

            $log_types = array_keys($this->get_log_types());

            global $wpdb;
            $wpdb->query(
                $wpdb->prepare(
                    "DELETE
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode("','", $log_types) . "') AND
					  user = %d",
                    get_current_user_id()
                )
            );

            wp_send_json_success();
        }

        /**
         * Mark a notification as read
         */
        public function ajax_mark_as_read()
        {
            UM()->check_ajax_nonce();

            if (empty($_POST['notification_id'])) {
                wp_send_json_error(__('Wrong notification ID', 'um-notifications'));
            }

            $this->set_as_read(absint($_POST['notification_id']));

            $unread = isset($_POST['unread']) ? (bool) $_POST['unread'] : false;

            if (!$unread) {
                wp_send_json_success();
            } else {
                global $wpdb;

                $time = absint($_POST['time']);
                $time_where = $wpdb->prepare(' AND time <= %s ', date('Y-m-d H:i:s', $time));

                $offset   = absint($_POST['offset']);
                $user_id  = get_current_user_id();

                $unread_where = " AND status = 'unread' ";
                $log_types    = array_keys($this->get_log_types());

                $notifications = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT *
					FROM {$wpdb->prefix}um_notifications
					WHERE type IN('" . implode("','", $log_types) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}
					ORDER BY time DESC
					LIMIT 1
					OFFSET %d",
                        $user_id,
                        $offset - 1
                    )
                );

                if (!empty($notifications)) {
                    $notifications = apply_filters('um_notifications_get_notifications_response', $notifications, 1, $unread, $time);
                    $notifications = $this->built_notifications_template($notifications);
                } else {
                    $notifications = array();
                }

                $total = $wpdb->get_var(
                    $wpdb->prepare(
                        "SELECT COUNT(*)
					FROM {$wpdb->prefix}um_notifications
					WHERE type IN('" . implode("','", $log_types) . "') AND
						  user = %d
						  {$unread_where}
						  {$time_where}",
                        $user_id
                    )
                );

                $total = !empty($total) ? absint($total) : 0;

                $output = apply_filters(
                    'um_notifications_ajax_on_load_notification',
                    array(
                        'notifications' => $notifications,
                        'total'         => $total,
                    )
                );
                wp_send_json_success($output);
            }
        }

        /**
         * Mark all notifications as read
         */
        public function ajax_mark_all_as_read()
        {
            UM()->check_ajax_nonce();

            global $wpdb;
            $user_id = get_current_user_id();

            $log_types = array_keys($this->get_log_types());

            $wpdb->query(
                $wpdb->prepare(
                    "UPDATE {$wpdb->prefix}um_notifications
				SET status = 'read'
				WHERE type IN('" . implode("','", $log_types) . "') AND
					  status = 'unread' AND
					  user = %d",
                    $user_id
                )
            );

            wp_send_json_success();
        }

        public function ajax_change_notifications_prefs()
        {
            UM()->check_ajax_nonce();

            $user_id = get_current_user_id();
            $type    = sanitize_key($_POST['notification_type']);
            $prefs   = get_user_meta($user_id, '_notifications_prefs', true);

            if (empty($prefs)) {
                $prefs = UM()->Notifications_API()->api()->get_log_types();
                $prefs = array_fill_keys(array_keys($prefs), 1);
            }
            $prefs[$type] = 0;

            update_user_meta($user_id, '_notifications_prefs', $prefs);

            wp_send_json_success();
        }

        /**
         * Checks for update
         */
        public function ajax_check_update()
        {
            WAMS()->check_ajax_nonce();

            // if (!UM()->options()->get('realtime_notify')) {
            //     $output = apply_filters(
            //         'um_notifications_ajax_check_update_no_realtime',
            //         array(
            //             'notifications' => array(),
            //             'time'          => time(),
            //         )
            //     );
            //     wp_send_json_success($output);
            // }

            $unread = (bool) $_POST['unread'];
            $time   = absint($_POST['time']);

            //hard reset the new notifications because they all will be displayed after AJAX response
            update_user_meta(get_current_user_id(), 'wams_new_notifications', '');

            global $wpdb;

            $user_id      = get_current_user_id();
            $unread_where = " status = 'unread' ";

            $notifications = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT *
					FROM {$wpdb->prefix}wams_notifications
					WHERE user = %d AND
						  {$unread_where}
					ORDER BY time DESC",
                    $user_id,
                    date('Y-m-d H:i:s', time())
                )
            );

            // if (!empty($notifications)) {
            //     $notifications = $this->built_notifications_template($notifications);
            // } else {
            //     $notifications = array();
            // }

            $output = array(
                'notifications' => $notifications,
                'time'          => time(),
            );
            wp_send_json_success($output);
        }

        public function built_notifications_template($notifications)
        {

            foreach ($notifications as &$notification) {
                $notification->user_id = stripslashes(get_current_user_id());
                $notification->content = stripslashes($notification->content);
            }

            return $notifications;
        }

        /**
         * Get notifications on load
         */
        public function ajax_on_load_notification()
        {
            UM()->check_ajax_nonce();

            global $wpdb;
            // using time only for the pagination for not getting the wrong offset with newest notifications. There is the separate query for getting newest.
            $time       = 0;
            $time_where = '';
            if (isset($_POST['time'])) {
                $time       = absint($_POST['time']);
                $time_where = $wpdb->prepare(' AND time <= %s ', date('Y-m-d H:i:s', $time));
            }

            $unread   = (bool) $_POST['unread'];
            $offset   = absint($_POST['offset']);
            $per_page = absint($_POST['per_page']);
            $user_id  = get_current_user_id();

            //hard reset the new notifications because they all will be displayed after AJAX response
            update_user_meta($user_id, 'um_new_notifications', '');

            $unread_where = $unread ? " AND status = 'unread' " : '';
            $log_types    = array_keys($this->get_log_types());

            $notifications = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT *
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode("','", $log_types) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}
				ORDER BY time DESC
				LIMIT %d
				OFFSET %d",
                    $user_id,
                    $per_page,
                    $offset
                )
            );

            if (!empty($notifications)) {
                $notifications = apply_filters('um_notifications_get_notifications_response', $notifications, $per_page, $unread, $time);
                $notifications = $this->built_notifications_template($notifications);
            } else {
                $notifications = array();
            }

            $total = $wpdb->get_var(
                $wpdb->prepare(
                    "SELECT COUNT(*)
				FROM {$wpdb->prefix}um_notifications
				WHERE type IN('" . implode("','", $log_types) . "') AND
					  user = %d
					  {$unread_where}
					  {$time_where}",
                    $user_id
                )
            );

            $total = !empty($total) ? absint($total) : 0;

            $output = apply_filters(
                'um_notifications_ajax_on_load_notification',
                array(
                    'notifications' => $notifications,
                    'total'         => $total,
                    'time'          => time(),
                )
            );
            wp_send_json_success($output);
        }

        /**
         * Getting the new notifications count
         */
        public function ajax_get_new_count()
        {
            WAMS()->check_ajax_nonce();

            $new_notifications = 0;
            $user_id      = get_current_user_id();
            $unread_where = " status = 'unread' ";
            global $wpdb;
            $new_notifications = $wpdb->get_var(
                "SELECT COUNT(*)
            FROM {$wpdb->prefix}wams_notifications
            WHERE user = $user_id AND
						  {$unread_where}
					ORDER BY time DESC"
            );
            $new_notifications_formatted = absint($new_notifications);
            // $new_notifications_formatted = (absint($new_notifications) > 9) ? __('9+', 'wams') : absint($new_notifications);

            $output = array(
                'new_notifications_formatted' => esc_html($new_notifications_formatted),
                'new_notifications'           => $new_notifications,
                'inbox_count'           => $this->get_inbox_count(),
            );

            wp_send_json_success($output);
        }

        public function get_inbox_count()
        {
            $count_value = '';
            if (class_exists('Gravity_Flow_API')) {
                $count_value = get_transient('gflow_inbox_count_' . get_current_user_id());
                if ($count_value === false) {
                    $count_value = \Gravity_Flow_API::get_inbox_entries_count();
                    set_transient('gflow_inbox_count_' . get_current_user_id(), $count_value, MINUTE_IN_SECONDS);
                }
            }

            return $count_value;
        }
    }
}
