<?php

namespace wams\common;

use Gravity_Flow;
use Gravity_Flow_API;
use GFAPI;

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
            // add_action('gform_user_registered', [$this, 'add_user_role'], 10, 3);

            // add_filter('body_class', array(&$this, 'remove_admin_bar'), 1000, 1);
            // add_filter('gravityflow_step_assignees', array(&$this, 'wams_gravityflow_step_assignees'), 10, 2);

            /**
             * After processing the workflow step
             */
            add_action('gravityflow_post_process_workflow', [$this, 'gravityflow_post_process_function'], 10, 4);

            add_action('gravityflow_workflow_detail_sidebar', [$this, 'download_doc_gravityflow_inbox'], 10, 4);

            add_filter('gravityflow_entry_detail_args', [$this, 'gravityflow_filter_entry_details_page']);
        }

        function gravityflow_filter_entry_details_page($args)
        {
            $user_roles = wp_get_current_user()->roles;
            if (in_array('um_misbar-reporter', $user_roles)) {
                $args['timeline'] = false;
            }

            return $args;
        }


        /**
         * Display the PDF metabox in the Gravity Flow inbox
         *
         * @param array $form
         * @param array $entry
         * @param $current_step
         * @param $args
         *
         * @return void
         *
         * @since 6.8
         */
        public function download_doc_gravityflow_inbox($form, $entry, $current_step, $args)
        {
?>
            <div id="download-box-container" class="postbox">

                <h3 class="hndle" style="cursor:default;"> Download As Doc
                </h3>

                <div class="inside">
                    <button id="entry-doc-downloader" data-entry-id="<?php echo $entry['id']; ?>" class="btn btn-success"><?php esc_html_e('Download As DOC', 'wams'); ?></span>

                </div>
            </div>
<?php
        }

        function wams_gravityflow_step_assignees($assignees, $step)
        {


            // Replace the step ID
            // if ($step->get_id() == 81) {
            //     $entry = $step->get_entry();
            //     $c =  GFAPI::update_entry_field($entry['id'], 2, 'user_id|' . get_current_user_id());
            // }

            /* @var Gravity_Flow_Assignee[] $new_assignees */

            // $new_assignees = array();

            // foreach ($assignees as $key => $assignee) {
            //     if ($assignee->get_type() == 'role') {

            //         $users = get_users(
            //             array(
            //                 'role__in' => $assignee->get_id(),
            //             )
            //         );

            //         foreach ($users as $user) {
            //             $new_assignees[] = new \Gravity_Flow_Assignee('user_id|' . $user->ID, $step);
            //         }
            //         unset($assignees[$key]);
            //     }
            // }

            // if (!empty($new_assignees)) {
            //     $assignees = array_merge($assignees, $new_assignees);
            // }

            return $assignees;
        }



        function gravityflow_post_process_function($form, $entry_id, $step_id, $starting_step_id)
        {
            if (!class_exists('GFAPI') || !class_exists('Gravity_Flow_API')) return;

            // IF Misbar Send Claim Request 
            if ($form['id'] == 1 && $starting_step_id == 5) {
                $c =  GFAPI::update_entry_field($entry_id, 16, 'user_id|' . get_current_user_id());
                $api =  new Gravity_Flow_API($form['id']);
                $entry = GFAPI::get_entry($entry_id);
                $api->send_to_step($entry, $step_id);
            }

            $vars = [];
            $workflow_inbox_page = get_option('wams_pages_settings')['workflow_inbox'] ?? 'inbox';
            $inbox_page_path = get_page_by_path($workflow_inbox_page)->guid ?? '/inbox';
            //Entry Details
            $entry = GFAPI::get_entry($entry_id);
            $created_by = rgar($entry, 'created_by', 1);
            $created_by_name = get_userdata($entry['created_by'])->display_name ?? 'no name';
            //From Details
            $form_id = $form['id'];
            $form_title = $form['title'];
            // Step Details
            $api = new \Gravity_Flow_API($form_id);
            $step = $api->get_current_step($entry);
            $vars['notification_uri'] = $inbox_page_path . '?page=gravityflow-inbox&view=entry&id=' . $form_id . '&lid=' . $entry_id;
            $vars['photo'] = WAMS_URL . 'assets/images/icons/workflow_diagram.png';


            // Send Notification to Entry Creator
            $title = __('Workflow Update', 'wams');
            $vars['message'] = sprintf(__('Entry ID #: %1$d %4$s Form : %2$s %4$s New Step : %3$s', 'wams'), $entry_id, $form_title, $step->step_name ?? '', PHP_EOL);
            $this->send_notification($created_by, 'workflow-update', $title, $vars);

            // Send Notification to Assignees
            if ($step) {

                $assignees = $step->get_assignees();
                if ($entry['workflow_step_status_' . $step->get_id()] == 'pending' && $step_id != $starting_step_id) {

                    $vars['message'] = sprintf(__('Entry ID#  %3$d %5$s Form: %1$s %5$s Created By: %2$s %5$s Now in %4$s step', 'wams'), $form_title, $created_by_name, $entry_id, $step->step_name, PHP_EOL);

                    $title = __('New Task', 'wams');
                    // $telegram_message = $form_title . PHP_EOL . "Created By: " . $created_by . PHP_EOL . "Task No. :" . $entry['id'] . PHP_EOL . "Task: " . $step->step_name;
                    foreach ($assignees as $assignee) {
                        // var_dump ($assignee);
                        $assignee_type = $assignee->get_type();
                        $assignee_id = $assignee->get_id();
                        switch ($assignee_type) {
                            case 'user_id':
                                $user_id = $assignee_id;
                                $this->send_notification($assignee_id, 'new-task', $title, $vars);
                                break;
                            case 'role':
                                $assignee_ids =  get_users(['role__in' =>  $assignee_id]);
                                foreach ($assignee_ids as $user) {
                                    $this->send_notification($user->ID, 'new-task', $title, $vars);
                                }
                                break;
                        }
                    }
                }
            }
        }


        function wams_workflow_post_process_function($form, $entry_id, $step_id, $starting_step_id)
        {
            $entry = GFAPI::get_entry($entry_id);
            $created_by = rgar($entry, 'created_by', 1);
            $step = WAMS()->gravity()->get_gf_steps($step_id);
            $workflow_inbox_page = get_option('wams_pages_settings')['workflow_inbox'];
            $inbox_page_path = get_page_by_path($workflow_inbox_page)->guid;
            if ($entry && $step) {
                $args = [
                    'photo' => WAMS_URL . 'assets/images/icons/workflow_diagram.png',
                    'message' => sprintf(__('Your Entry #: %1$d Has New Step : %2$s', 'wams'), $entry_id, $step['step_name']),
                    'notification_uri' => $inbox_page_path . '?page=gravityflow-inbox&view=entry&id=' . $form['id'] . '&lid=' . $entry_id,
                ];
                WAMS()->web_notifications()->store_notification($created_by, __('WorkFlow Update', 'wams'), $args);
            }
        }



        function send_notification($user_id, $type = '', $title = '', $vars = [])
        {
            WAMS()->web_notifications()->store_notification($user_id, $type, $title, $vars);
            WAMS()->telegram_notifications()->send_telegram_message($user_id, $type, $title . PHP_EOL .  $vars['message'] . PHP_EOL . $vars['notification_uri']);
        }
        function send_telegram_notification($user_id, $message, $url)
        {
            if (get_option('notifications_settings')['telegram_notifcation_enabled'] != 'on') return; // retrun if disabled
            try {
                $chat_id = get_user_meta($user_id, 'telegram_chat_id', $single = true);
                if (!empty($chat_id)) {
                    // $apiKey = get_field('telegram_api_key', 'option');
                    $apiKey = get_option('telegram_api')['api_key'];
                    $telegram = new \Telegram($apiKey);
                    $content = array('chat_id' => $chat_id, 'text' => $message . PHP_EOL . $url);
                    $telegram->sendMessage($content);
                }
            } catch (\Exception $e) {
            }

            // restore_current_blog();
        }
        function send_telegram_channel_notification($channel_id, $message, $url)
        {
            // $apiKey = get_field('telegram_api_key', 'option');
            try {
                $apiKey = get_option('telegram_api')['api_key'];
                $channelId = isset($channel_id) ? $channel_id  : get_option('telegram_api')['channel_id'];
                $telegram = new \Telegram($apiKey);
                $content = array('chat_id' => $channelId, 'text' => $message . PHP_EOL . $url);
                $telegram->sendMessage($content);
            } catch (\Exception $e) {
            }
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
