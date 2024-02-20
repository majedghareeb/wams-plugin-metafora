<?php

namespace wams\core;

use Exception;

if (!defined('ABSPATH')) {
    exit;
}
if (!class_exists('wams\core\RSS_Feed_Extractor')) {
    class RSS_Feed_Extractor
    {
        private $feedUrl;

        public function __construct($feedUrl)
        {
            $this->feedUrl = $feedUrl;
        }

        public function extractItems()
        {
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
    }
}
