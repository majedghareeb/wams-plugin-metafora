<?php

namespace wams\core;

use wams\core\google\GA_Api_Controller;
use Exception;

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('wams\core\Google_Analytics')) {
    class Google_Analytics
    {
        /**
         * @var object
         */
        private $ga;

        public function __construct()
        {
            // switch_to_blog(1);
            $this->ga = new GA_Api_Controller;
            // restore_current_blog();
        }



        public function google_analytics_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }

            // return wp_send_json(['message' => "TEST AJAX from Admin " . __METHOD__]);
            switch ($_POST['param']) {
                case 'get_analytics_data':
                    $title = $_POST['title'] ?? false;
                    $entry_id = $_POST['entry_id'] ?? false;

                    if ($title) {
                        $urls_form_settings = get_option('wams_urls_form_settings');

                        $ga4_data = $this->get_pageTitle_analytics(false, false, $title);
                        if (!empty($ga4_data) && $entry = \GFAPI::get_entry($entry_id)) {

                            $pageviews_field_id = $urls_form_settings['pageviews'];
                            $sessions_field_id = $urls_form_settings['sessions'];
                            $avg_engagement_field_id = $urls_form_settings['avg_engament_time'];
                            $users_field_id = $urls_form_settings['users'];
                            if (isset($pageviews_field_id) && isset($sessions_field_id)) {
                                $entry[$pageviews_field_id] = $ga4_data[0]['Pageviews'] ?? 0;
                                $entry[$sessions_field_id] = $ga4_data[0]['Sessions'] ?? 0;
                                $entry[$users_field_id] = $ga4_data[0]['Users'] ?? 0;
                                $entry[$avg_engagement_field_id] = $ga4_data[0]['User Engagement Duration'] ?? 0;
                            }
                            \GFAPI::update_entry($entry);
                        } else {
                            $ga4_data = [['Pageviews' => 'No Data', 'Sessions' => 'No Data']];
                        }

                        wp_send_json_success($ga4_data);
                    } else {
                        wp_send_json_error('Title Not Fount!');
                    }
                    // echo print_r($posts, true);
                    wp_die();
                    break;
            }
        }


        public function get_analytics_data()
        {
            // WAMS()->common()->logger()::debug('get_analytics_data');
            $forms_settings = get_option('wams_forms_settings');
            $urls_form_settings = get_option('wams_forms_settings');

            $url_list_form_id = $forms_settings['urls_form'] ?? 0;
            $link = $urls_form_settings['link'] ?? 0;
            $title = $urls_form_settings['title'] ?? 0;
            // $this->get_pageTitle_analytics()
        }
        public function get_pageTitle_analytics($from = false, $to = false, $title)
        {
            $options = $this->ga->ga_config->options;
            $account = $options['default_profile'] ?? false;
            if (!$account) __return_false();
            $from_date = $from ? $from : date('Y') . '-01-01';
            $to_date = $to ? $to : date('Y-m-d');
            $t = 0;
            $result = $this->ga->get($account, 'pageTitle',  $from_date, $to_date, $title);
            if (is_array($result)) return $result;
            else __return_false();
        }
    }
}
