<?php

namespace wams\core;


if (!defined('ABSPATH')) {
    exit;
}
require_once WAMS_PATH . 'includes/lib/dom/vendor/autoload.php';

use Symfony\Component\DomCrawler\Crawler;
use GuzzleHttp\Client;

if (!class_exists('wams\core\Web_Page_Parser')) {
    class Web_Page_Parser
    {
        private $client;

        public function __construct()
        {
            $this->client = new Client();
        }



        private function getContent($url)
        {
            try {
                // Send a GET request to the URL
                $response = $this->client->get($url);

                // Get the status code
                $statusCode = $response->getStatusCode();
                if ($response->getStatusCode() == 200) {
                    return $response->getBody()->getContents();
                }
            } catch (\Exception $e) {
                // If an exception occurs (e.g., connection error, timeout), catch it and return an error message
                return false;
            }
        }


        public function getStatusCode($url)
        {
            try {
                // Send a GET request to the URL
                $response = $this->client->get($url);

                // Get the status code
                $statusCode = $response->getStatusCode();
                return $statusCode;
            } catch (\Exception $e) {
                // If an exception occurs (e.g., connection error, timeout), catch it and return an error message
                return false;
            }
        }
        public function getHeaders($url)
        {
            try {
                // Send a GET request to the URL
                $response = $this->client->get($url);

                // Get the headers
                $headers = $response->getHeaders();

                return [
                    'headers' => $headers
                ];
            } catch (\Exception $e) {
                // If an exception occurs (e.g., connection error, timeout), catch it and return an error message
                return false;
            }
        }
        /** 
         * Get Page title of URL
         * @param   string  URL
         * @param   string  Type '' | 'full' for all details
         * @return  string  Title Tag
         */
        public function getPageDetails($url, $type = '')
        {
            try {
                // Send a GET request to the URL
                $html = $this->getContent($url);

                // Get the status code
                $metaTags = [];

                if ($html !== false) {
                    $crawler = new Crawler($html);
                    if ($type == 'full') {
                        $crawler->filter('meta')->each(function (Crawler $node) use (&$metaTags) {
                            if (!is_null($node->attr('name'))) {
                                $metaTags[] = array(
                                    'name'    => $node->attr('name'),
                                    'content' => $node->attr('content'),
                                );
                            }
                        });
                        $crawler->filter('meta')->each(function (Crawler $node) use (&$metaTags) {
                            $metaTags[] = array(
                                'property'    => $node->attr('property'),
                                'content' => $node->attr('content'),
                            );
                        });
                    } else {
                        $metaTags[] = array(
                            'title'    => 'title tag',
                            'content' => $crawler->filter('title')->text(),
                        );
                        $crawler->filter('meta')->each(function (Crawler $node) use (&$metaTags) {
                            if (in_array($node->attr('name'), ['title', 'description'])) {

                                $metaTags[] = array(
                                    'name'    => $node->attr('name'),
                                    'content' => $node->attr('content'),
                                );
                            }
                        });
                    }
                    return $metaTags;
                } else {
                    return __('Could Not Get Page Title', 'wams');
                }
            } catch (\Exception $e) {
                // If an exception occurs (e.g., connection error, timeout), catch it and return an error message
                return ['error' => $e->getMessage()];
            }
        }
        /** 
         * Get Page title of URL
         * @param   string  URL
         * @return  string  Title Tag
         */
        public function getTitle($url)
        {
            $titleTag = [];
            $html = $this->getContent($url);
            if ($html !== false) {
                try {
                    $crawler = new Crawler($html);
                    $titleTag[] = [
                        'title' => $crawler->filter('title')->text()
                    ];
                    return $titleTag;
                } catch (\Throwable $e) {
                    return ['error' => $e->getMessage()];
                }
            } else {
                return ['error' => __('Could Not Get Page Title', 'wams')];
            }
        }

        /** 
         * Get Meta Tags of URL
         * @param   string  URL
         * @return  array  Meta Tags
         */
        public function getMetaTags($url)
        {
            $metaTags = array();
            if ($html = $this->getContent($url)) {
                $crawler = new Crawler($html);
                $crawler->filter('meta')->each(function (Crawler $node) use (&$metaTags) {
                    $metaTags[] = array(
                        'name'    => $node->attr('name'),
                        'content' => $node->attr('content'),
                    );
                });
                $crawler->filter('meta')->each(function (Crawler $node) use (&$metaTags) {
                    $metaTags[] = array(
                        'property'    => $node->attr('property'),
                        'content' => $node->attr('content'),
                    );
                });
            }
            return ['Error' => __('Could Not Get Page Meta Tags', 'wams')];
        }

        /** 
         * Get Links of URL
         * @param   string  URL
         * @return  array  Links in the page
         */

        public function getLinks($url)
        {
            if ($html = $this->getContent($url)) {
                $crawler = new Crawler($html);

                $links = array();
                $crawler->filter('a')->each(function (Crawler $node) use (&$links) {
                    $links[] = array(
                        'href' => $node->attr('href'),
                        'text' => $node->text(),
                    );
                });
                return $links;
            } else {
                return ['Error' => __('Could Not Get Page Links', 'wams')];
            }
        }


        /** 
         * Get Links of URL
         * @param   string  URL
         * @return  array   backlinks['domain','url','anchor_text']
         */
        public function get_backlinks($url)
        {
            if ($html = $this->getContent($url)) {
                $crawler = new Crawler($html);

                $externalLinks = array();
                $crawler->filter('a')->each(function (Crawler $node) use (&$externalLinks) {
                    $href = $node->attr('href');
                    $anchor_text = $node->text();

                    $domain = parse_url($href, PHP_URL_HOST);
                    // Remove "www." prefix if present
                    $domain = preg_replace('/^www\./', '', $domain);


                    if (strpos($href, 'http') === 0 || strpos($href, 'www') === 0) {
                        $externalLinks[] = [
                            $href,
                            $anchor_text,
                            $domain
                        ];
                    }
                });

                return $externalLinks;
            } else {
                return ['Error' => __('Could Not Get Page Backlinks', 'wams')];
            }
        }

        public function getExternalLinks($url)
        {
            if ($html = $this->getContent($url)) {
                $crawler = new Crawler($html);

                $externalLinks = array();
                $crawler->filter('a')->each(function (Crawler $node) use (&$externalLinks) {
                    $href = $node->attr('href');
                    if (strpos($href, 'http') === 0 || strpos($href, 'www') === 0) {
                        $externalLinks[] = $href;
                    }
                });

                return $externalLinks;
            } else {
                return $html;
            }
        }

        /**
         * Get Page JSON Schema
         *
         * @return void
         */
        public function get_schema($url)
        {
            if ($html = $this->getContent($url)) {
                $crawler = new Crawler($html);
                $scriptElements = $crawler->filter('script[type="application/ld+json"]');
                $schemas = [];
                foreach ($scriptElements as $scriptElement) {
                    $schemas[] = json_decode($scriptElement->textContent, true);
                }
                return $schemas;
            } else {
                return ['Error' => __('Could Not Get Page Schema', 'wams')];
            }
        }

        /**
         * Check if the URL Encoded Or Not
         * @param string    $str    URL
         * @return bool    True if Endcoded URL
         */
        public static function isEncoded($str)
        {
            return gettype($str)  == "string" && urldecode($str) !== $str;
        }
    }
}
