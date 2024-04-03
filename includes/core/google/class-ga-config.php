<?php

namespace wams\core\google;

if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('wams\core\google\GA_Config')) {

    final class GA_Config
    {

        public $options;

        public $reporting_ready;

        public function __construct()
        {
            $this->get_plugin_options(); // Get plugin options

            $this->reporting_ready = $this->options['default_profile'] ?? '';
        }

        // Validates data before storing
        public function validate_data($options)
        {
            /* @formatter:off */

            return $options;
        }

        public function set_plugin_options()
        {
            $options = $this->options;
            update_option('wams_ga_options', json_encode($options));
        }

        private function get_plugin_options()
        {
            global $blog_id;
            if (!get_option('wams_ga_options')) {
                $this->install();
            }
            $this->options = (array) json_decode(get_option('wams_ga_options'));
            // Maintain Compatibility
        }
        private function install()
        {
            if (!get_option('token')) {
                $options = array();
                $options['client_id'] = '';
                $options['client_secret'] = '';
                $options['access_front'][] = 'administrator';
                $options['access_back'][] = 'administrator';
                $options['default_profile'] = '';
                $options['reporting_type'] = 1;
                $options['token'] = '';
                $options['user_api'] = 0;
                $options['ga4_profiles_list'] = array();
                $options['reporting_type'] = 0;
                $options['api_backoff'] = 0;
                $options['ga_target_geomap'] = '';
                $options['pagetitle_404'] = 'Page Not Found';
            }
            add_option('wams_ga_options', json_encode($options));
        }
    }
}
