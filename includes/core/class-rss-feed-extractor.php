<?php

namespace wams\core;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('wams\core\RSS_Feed_Extractor')) {
    class RSS_Feed_Extractor
    {
        /**
         * @var string
         */
        private $feedUrl;

        public function __construct()
        {
        }

        public function rss_fetcher_ajax_handler()
        {
            if (!wp_verify_nonce($_POST['nonce'], 'wams-frontend-nonce')) {
                wp_die(esc_attr__('Security Check', 'wams'));
            }

            if (empty($_POST['param'])) {
                wp_send_json_error(__('Invalid Action.', 'wams'));
            }

            // return wp_send_json(['message' => "TEST AJAX from Admin " . __METHOD__]);
            switch ($_POST['param']) {
                case 'start_rss_fetch':
                    $posts = $this->wams_rss_import('manual');
                    WAMS()->get_template('rss-fetcher.php', '', ['posts' => $posts], true);
                    // echo print_r($posts, true);
                    wp_die();
                    break;
            }
        }


        public function extractItems($feedUrl)
        {
            $this->feedUrl = $feedUrl;
            // Load the RSS feed using SimpleXML
            $xml = simplexml_load_file($this->feedUrl);

            // Check if the XML is valid
            if ($xml === false) {
                throw new Exception('Error loading XML');
            }

            $itemsData = [];

            // Loop through each item in the feed
            foreach ($xml->channel->item as $item) {
                $itemData =
                    $itemData    = [
                        'title' => (string)$item->title,
                        'description' => (string)$item->description,
                        'link' => (string)$item->link,
                        'pubDate' => (string)$item->pubDate,
                    ];
                $creator = (string)$item->children('dc', true)->creator;
                if (!empty($creator)) {
                    $itemData['creator'] = (string)$creator;
                }
                // Get the thumbnail URL, description, and credit from the media content
                $mediaContent = $item->children('media', true)->content;

                $thumbnailUrl = ($mediaContent) ? (string)$mediaContent->attributes()['url'] : '';
                $description = ($mediaContent) ? (string)$mediaContent->description : '';
                $credit = ($mediaContent) ? (string)$mediaContent->credit : '';

                // Check if the fields are not empty before adding to the result
                if (!empty($thumbnailUrl) || !empty($description) || !empty($credit)) {
                    $itemData['thumbnail'] = $thumbnailUrl;
                    $itemData['thumbnail_description'] = $description;
                    $itemData['thumbnail_credit'] = $credit;
                }
                $itemsData[] = $itemData;
            }

            return $itemsData;
        }

        /**
         * @param   string  Method to call the function scheduled || manual
         */
        public function wams_rss_import($method = "scheduled")
        {
            $saved_posts = [];
            if ($method = "scheduled") \wams\common\Logger::info('wams_rss_import is called from ' . __CLASS__ . ' class using cron');
            $wams_domains_settings = get_option('wams_domains_settings');
            $blog_id = intval($wams_domains_settings['blog_id']) ?? 1;
            $project_name = $wams_domains_settings['project_name'] ?? get_bloginfo('name');
            $domain_name = $wams_domains_settings['domain_name'] ?? get_bloginfo('url');
            $wams_rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
            if ($wams_rss_fetcher_settings && $wams_rss_fetcher_settings['fetch_scheduled'] == 'on') {
                $posts = [];
                try {
                    $items = $this->extractItems($wams_rss_fetcher_settings['rss_url']);
                    foreach ($items as $item) {
                        $dateTime = new \DateTime($item['pubDate']);
                        $posts[] = [
                            'project' =>  $project_name,
                            'domain' =>  $domain_name,
                            'link' =>    htmlspecialchars($item['link']),
                            'title' =>   str_replace('<br />', '', strip_tags($item['title'])) ?? '',
                            'description' =>    strip_tags($item['description']) ?? '',
                            'creator' =>    strip_tags($item['creator']) ?? '',
                            'pub_date' =>    strip_tags($dateTime->format('Y-m-d')),
                            'thumbnail' => isset($item['thumbnail']) ? strip_tags($item['thumbnail']) : '',
                            'thumbnail_description' =>  isset($item['thumbnail_description']) ?  strip_tags($item['thumbnail_description']) : '',
                        ];
                    }
                } catch (\Exception $e) {
                    \wams\common\Logger::error('Error in RSS Fetch: ' . $e->getMessage());
                }
                $saved_posts = $this->save_new_fetched_posts($posts, $blog_id);
            }
            return $saved_posts;
        }

        /**
         * Save New Post on URL List Form
         */
        function save_new_fetched_posts($posts = [], $blog_id)
        {
            if (get_current_blog_id() != $blog_id) switch_to_blog($blog_id);
            $saved_posts = [];
            if (!is_array($posts) || empty($posts)) return;
            $wams_rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
            $wams_urls_form_settings = get_option('wams_urls_form_settings');
            if ($wams_rss_fetcher_settings && isset($wams_rss_fetcher_settings['urls_form'])) {
                $entry = [];

                foreach ($posts as $post) {

                    $entry = array_combine($wams_urls_form_settings, $post);
                    $entry['created_by'] = 1;
                    $entry['form_id'] = intval($wams_rss_fetcher_settings['urls_form']);
                    // $entry[$wams_urls_form_settings[$key]] = $post[$key];
                    //TODO create setting to choose "Published" field ID
                    $entry['16'] = 'Yes';
                    //TODO create setting to choose "service type" field ID
                    $entry['17'] = 'Article';
                    $post['Type'] = 'Article';
                    if ($this->check_if_entry_exists($wams_rss_fetcher_settings['urls_form'], $post['link'], $wams_urls_form_settings['link'])) {
                        $post['saved'] = 'entry already exists';
                    } else {
                        if ($new_entry = \GFAPI::add_entry($entry)) {
                            $this->save_auhor_to_vendor_rss_form($post['creator']);
                            $post['saved'] = 'new entry created : ' . $new_entry;
                        }
                    }
                    $saved_posts[]  = $post;
                }
            }
            restore_current_blog();
            return $saved_posts;
        }


        public function save_auhor_to_vendor_rss_form($author)
        {
            $wams_url_ignored_authors = get_option('wams_url_ignored_authors');
            $wams_rss_fetcher_settings = get_option('wams_rss_fetcher_settings');
            if ($wams_rss_fetcher_settings && isset($wams_rss_fetcher_settings['vendor_rss_form'])) {
                $author_on_rss = $author ?? '';
                $vendor_name = $wams_vendor_form_settings['vendor_name'] ?? '';
                $vendor_rss_form_id = $wams_rss_fetcher_settings['vendor_rss_form'] ?? 0;

                $ignored_authors = $wams_url_ignored_authors['ignored_authors'];
                if (!strpos($ignored_authors, $author)) {
                    $check_if_author_is_exists = $this->check_if_entry_exists($vendor_rss_form_id, $author, 1);
                    if ($check_if_author_is_exists === false) {
                        $entry = [];
                        $entry['form_id'] = intval($vendor_rss_form_id);
                        //TODO create setting to choose "author" field ID
                        $entry['1'] = $author;
                        if (!$new_entry = \GFAPI::add_entry($entry)) {
                            \wams\common\Logger::error('Error in Creating new Author ' . $new_entry);
                        }
                    }
                }
            }
        }

        /**
         * Check if enry exists
         */
        public function check_if_entry_exists($form_id, $value_to_check, $match_field)
        {
            $search_criteria  = array(
                'field_filters' => array(
                    array(
                        'key'   => $match_field,  // Original Client ID Field ID in Add New Clients Form
                        'value' => $value_to_check,
                    )
                )
            );
            $t = 0;
            $existing_entries = \GFAPI::get_entries($form_id, $search_criteria, null, null, $t);
            if ($t > 0) return true;
            return false;
        }
    }
}
