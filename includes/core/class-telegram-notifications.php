<?php

namespace wams\core;


if (!defined('ABSPATH')) {
    exit;
}



if (!class_exists('wams\core\Telegram_Notifications')) {

    class Telegram_Notifications
    {
        /**
         * @var array
         */
        public $telegram_api = [];

        public function __construct()
        {
            $wams_telegram_api = get_option('wams_telegram_api');

            if ($wams_telegram_api) {
                $this->telegram_api = [
                    'api_key' => $wams_telegram_api['api_key'] ?? 0,
                    'bot_username' => $wams_telegram_api['bot_username'] ?? 0,
                    'channel_id' => $wams_telegram_api['channel_id'] ?? 0,
                ];
            }
        }

        /**
         * Show page from Shortcode [link-my-telegram]
         */
        public function link_my_telegram_page()
        {
            if (!class_exists('Telegram')) {
                echo '<div class="alert alert-danger" role="alert">Telegram Service is not installed on Deactived</div>';
                return;
            }
            wp_enqueue_script("telegram", WAMS_URL . 'assets/js/frontend/telegram.js', array(), WAMS_VERSION, false);
            wp_enqueue_style("sweetalert2", WAMS_URL . 'assets/css/sweetalert2.min.css', array(), WAMS_VERSION);
            wp_enqueue_script("sweetalert2", WAMS_URL . 'assets/js/sweetalert2.min.js', array(), WAMS_VERSION, false);
            $current_user = wp_get_current_user();
            $display_name = $current_user->display_name;
            $current_user_id = $current_user->ID;
            $chat_id = get_user_meta($current_user_id, 'telegram_chat_id', $single = true);

            $arg = [
                'current_user_id' => $current_user_id,
                'user_display_name' => $display_name,
                'chat_id' => $chat_id ?? 0,
                'bot_username' => $this->telegram_api['bot_username'],
            ];

            WAMS()->get_template('link-telegram.php', '', $arg, true);
        }

        function telegram_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }

            // return wp_send_json(['message' => "TEST AJAX from Admin " . __METHOD__]);
            switch ($_POST['param']) {
                case 'sendcode':
                    $telegram = new \Telegram($this->telegram_api['api_key']);
                    $req = $telegram->getUpdates();
                    $result = $telegram->UpdateCount();

                    for ($i = 0; $i <= $result; $i++) {
                        // You NEED to call serveUpdate before accessing the values of message in Telegram Class
                        $telegram->serveUpdate($i);
                        $text = $telegram->Text();
                        $chat_id = $telegram->ChatID();
                        if ($text == '/activate') {
                            $reply = 'Thank you for activating Fadaat Portal Notification' . PHP_EOL . 'Your activation code is:' . PHP_EOL . $chat_id . $this->telegram_api['channel_chat_id'];
                            $content = array('chat_id' => $chat_id, 'text' => $reply);
                            $telegram->sendMessage($content);
                            $content = array('chat_id' => $this->telegram_api['channel_chat_id'], 'text' => 'New user chat with Bot ' . PHP_EOL . 'with Chat ID:' . $chat_id);
                            $telegram->sendMessage($content);
                        }
                    }
                    wp_send_json(array(
                        "status" => 1,
                        "message" => $this->telegram_api['api_key'] . ' Activation Code Has Been Sent!',
                    ));


                    break;
                case 'save_telegram_chat_id':
                    $telegram_user_id = isset($_REQUEST['user_id']) ? $_REQUEST['user_id'] : 0;
                    $telegram_chat_id = isset($_REQUEST['chat_id']) ? $_REQUEST['chat_id'] : 0;

                    if ($telegram_user_id && $telegram_chat_id) {
                        $previous_telegram_chat_id = get_user_meta($telegram_user_id, 'telegram_chat_id', true);
                        $new_chat_id = update_user_meta($telegram_user_id, 'telegram_chat_id', $telegram_chat_id, $previous_telegram_chat_id);
                        if ($new_chat_id == $previous_telegram_chat_id) {
                            wp_send_json(array(
                                "status" => 1,
                                "message" => 'Activation Done for User: ' . $telegram_user_id . ' With New Code:' . $telegram_chat_id,
                            ));
                        } else {
                            wp_send_json(array(
                                "status" => 2,
                                "message" => 'You Did not change the ID',
                            ));
                            // wp_send_json_error('We Could Not Activate your account');
                        }
                    } else {
                        wp_send_json(array(
                            'status' => 2,
                            'message' => 'Activation Code not found!',
                        ));
                    }
                    break;
                case 'send_test_messgae':
                    $telegram_chat_id = isset($_REQUEST['chat_id']) ? $_REQUEST['chat_id'] : 0;
                    $telegram_message = isset($_REQUEST['test_message']) ? $_REQUEST['test_message'] : "Test Message";
                    $channel_chat_id = $this->telegram_api['channel_id'] ?? 0;
                    if ($telegram_chat_id) {
                        $telegram = new \Telegram($this->telegram_api['api_key']);
                        $content = array('chat_id' => $telegram_chat_id, 'text' => $telegram_message);
                        $telegram->sendMessage($content);
                        $content = array('chat_id' => $channel_chat_id, 'text' => $telegram_message);
                        $telegram->sendMessage($content);
                        wp_send_json(array(
                            "status" => 1,
                            "message" => 'Test Message has been sent to your telegram account please check!',
                        ));
                    } else {
                        wp_send_json(array(
                            "status" => 0,
                            "message" => "Failed to send test message!"
                        ));
                    }

                    break;
                default:
                    wp_send_json(array(
                        "status" => 1,
                        "message" => "Successfully completed first ajax from frontend"
                    ));
                    break;
            }
        }
    }
}
