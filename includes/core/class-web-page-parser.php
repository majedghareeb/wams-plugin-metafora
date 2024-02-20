<?php

namespace wams\core;


if (!defined('ABSPATH')) {
    exit;
}
require_once WAMS_PATH . 'includes/lib/vendor/autoload.php';

use GuzzleHttp\Client;
use Symfony\Component\DomCrawler\Crawler;
use Exception;

if (!class_exists('wams\core\Web_Page_Parser')) {
    class Web_Page_Parser
    {
        private $url;
        private $html;

        public function __construct($url)
        {
            $this->url = $url;
            $this->fetchHTML();
        }

        private function fetchHTML()
        {
            $client = new Client();
            $response = $client->get($this->url);

            if ($response->getStatusCode() == 200) {
                $this->html = $response->getBody()->getContents();
            } else {
                throw new Exception("Failed to fetch HTML. Status code: " . $response->getStatusCode());
            }
        }

        public function getTitle()
        {
            $crawler = new Crawler($this->html);

            return $crawler->filter('title')->text();
        }

        public function getMetaTags()
        {
            $crawler = new Crawler($this->html);

            $metaTags = array();
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


            return $metaTags;
        }

        public function getLinks()
        {
            $crawler = new Crawler($this->html);

            $links = array();
            $crawler->filter('a')->each(function (Crawler $node) use (&$links) {
                $links[] = array(
                    'href' => $node->attr('href'),
                    'text' => $node->text(),
                );
            });

            return $links;
        }
        /**
         * Return List of Backlinks in submitted URL
         * 
         * @return  array   backlinks['domain','url','anchor_text']
         */
        public function get_backlinks()
        {
            $crawler = new Crawler($this->html);

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
        }

        public function getExternalLinks()
        {
            $crawler = new Crawler($this->html);

            $externalLinks = array();
            $crawler->filter('a')->each(function (Crawler $node) use (&$externalLinks) {
                $href = $node->attr('href');
                if (strpos($href, 'http') === 0 || strpos($href, 'www') === 0) {
                    $externalLinks[] = $href;
                }
            });

            return $externalLinks;
        }

        /**
         * Get Page JSON Schema
         *
         * @return void
         */
        public function get_schema()
        {
            $crawler = new Crawler($this->html);
            $scriptElements = $crawler->filter('script[type="application/ld+json"]');
            $schemas = [];
            foreach ($scriptElements as $scriptElement) {
                $schemas[] = json_decode($scriptElement->textContent, true);
            }
            return $schemas;
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




// class Page_Crawler
// {
//     //private $dom;

//     public $targeted_domains;
//     private $url;
//     private $client;
//     public $crawler;
//     public $headers;
//     public $statusCode;
//     public $error_message;


//     public function __construct()
//     {

//         $this->client = new \GuzzleHttp\Client([
//             'allow_redirects' => [
//                 'max' => 2, // Set the maximum number of redirects to follow
//                 'strict' => true, // Enable strict redirect rules
//                 'referer' => true, // Set the Referer header on redirects
//             ],
//         ]);
//     }

//     /**
//      * Crawel URL and instatiate crwaler 
//      *
//      * @param String $url
//      * @return false if $URL is not valid
//      */
//     public function crawl(String $url)
//     {
//         try {
//             $response = $this->client->request('GET', $url);
//             $this->statusCode = $response->getStatusCode();

//             if ($this->statusCode === 200) {
//                 $html = $response->getBody()->getContents();
//                 $this->crawler = new \Symfony\Component\DomCrawler\Crawler($html);
//                 $headers = $response->getHeaders();
//                 foreach ($headers as $name => $values) {
//                     $this->headers[$name] =  implode(', ', $values);
//                 }
//                 return true;
//             } else {
//                 return false;
//             }
//         } catch (\GuzzleHttp\Exception\RequestException $e) {
//             echo "Error: Unable to access the URL.";
//         }
//     }

//     /**
//      * Get JSON Structred Data
//      */
//     /**
//      * Return Page Title
//      * 
//      * @return  text
//      */
//     public function get_title($url)
//     {
//         $this->crawl($url);
//         return $this->crawler;
//     }
//     /**
//      * Return Page Header
//      * 



    

//     /**
//      * Search Schema for Key
//      *
//      * @param [type] $array
//      * @param [type] $key
//      * @return void
//      */
//     public function searchKeyInArray($array, $key)
//     {
//         // Iterate through each element of the array
//         foreach ($array as $arrayKey => $value) {
//             // Check if the current element is an array
//             if ($arrayKey === $key) {
//                 return $value;
//             }
//             if (is_array($value)) {
//                 // Recursively search in the nested array
//                 $result = $this->searchKeyInArray($value, $key);

//                 // If the key is found, return the result
//                 if ($result !== null) {
//                     return $result;
//                 }
//             } elseif ($arrayKey === $key) {
//                 // If the current element matches the key, return its value
//                 return $value;
//             }
//         }

//         // If the key is not found, return null
//         return null;
//     }

//     /**
//      * Get Html Content
//      * @param   String  URL
//      */
//     public function check_url($url)
//     {
//         if (!$this->validate_url($url)) {
//             $this->error_message = 'URL does not Exist!';
//             return false;
//         }
//         return  $this::isEncoded($this->url) ? $url : $this::flash_encode($url);
//     }

//     public function validate_url($url)
//     {
//         $headers = @get_headers($url);

//         // Use condition to check the existence of URL
//         if ($headers[0]) {
//             // Check if the content length is greater than 0
//             return true;
//         } elseif (strpos($headers[0], '404') !== false) {
//             return false;
//         }
//         return false;
//     }




//     /**
//      * Encode URL when it is arabic
//      * @param string    $str    URL
//      * @return string   Endcoded URL
//      */
//     public static function flash_encode($string)
//     {
//         $string = rawurlencode($string);
//         $string = str_replace("%2F", "/", $string);
//         $string = str_replace("%3A", ":", $string);
//         return $string;
//     }

//     /**
//      * Validate Backlinks for adding to form
//      * 
//      */
//     public function validate_backlinks()
//     {
//         $backlinks['valid'] = [];
//         $backlinks['invalid'] = [];
//         $backlinks['currentdomain'] = [];
//         $backlinks['subdomain'] = [];
//         $ignore_subdomain = get_option('wams_domains_settings')['ignore_subdomain'];
//         $ignore_parent_domain = get_option('wams_domains_settings')['ignore_parent_domain'];
//         $backlinks_list = $this->get_backlinks();
//         $target_domains = $this->target_domains;
//         $currentDomain = self::tld($this->url);
//         $subDomain = self::tldNoSubDomain($this->url);
//         foreach ($backlinks_list as $backlink_detail) {
//             $domain = $backlink_detail['domain'];
//             // check if URL encoded then decode to get arabic url
//             $url_o = $backlink_detail['url'];
//             $url = ($this::isEncoded($url_o)) ? urldecode($url_o) : $url_o;
//             $anchor = $backlink_detail['anchor_text'];
//             if (in_array($domain, $target_domains)) {
//                 // If the backlinks belong to the same domain
//                 if ($domain == $currentDomain) {
//                     $backlinks['currentdomain'][] = ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
//                 } elseif ($currentDomain != $domain) // Diffent Domain
//                     // Check if it is subdomain
//                     if ($ignore_subdomain && ((strpos($currentDomain, $domain) > 0) || (strpos($domain, $currentDomain) > 0))) {
//                         $backlinks['subdomain'][] =  ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
//                     }
//                     // Check if it is parent Domain
//                     elseif ($ignore_parent_domain && ((strpos($currentDomain, $domain) > 0) || (strpos($domain, $currentDomain) > 0))) {
//                         $backlinks['parentdomain'][] =  ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
//                     } else {
//                         $backlinks['valid'][] = ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
//                     }
//             } else {
//                 $backlinks['invalid'][] = ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
//             }
//         }
//         return $backlinks;
//     }
//     /**
//      * Get Top level Domain name TLD from URL
//      * @param   string  URL
//      * @return  string  TLD
//      */
//     public static function get_domain($url)
//     {
//         $domain = parse_url($url, PHP_URL_HOST);

//         // Remove "www." prefix if present
//         $domain = preg_replace('/^www\./', '', $domain);

//         // Validate and extract domain name only
//         if (filter_var($domain, FILTER_VALIDATE_DOMAIN, FILTER_FLAG_HOSTNAME)) {
//             $domainName = explode('.', $domain, 2)[0];
//             return $domain;
//         } else {
//             return false;
//         }
//     }
//     /**
//      * Get  TLD from URL and remove subdomain
//      * @param   string  URL
//      * @return  string  TLD
//      */
//     public function tldNoSubDomain($url)
//     {
//         // $parse = parse_url($url);
//         // $url =  isset($parse['host']) ? preg_replace("/^([a-zA-Z0-9].*\.)?([a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z.]{2,})$/", '$2', $parse['host']) : $url;
//         $parseData = parse_url($url);
//         $domain = isset($parse['host']) ? preg_replace('/^www\./', '', $parseData['host']) : $url;


//         $array = explode(".", $domain);

//         $url =  (array_key_exists(count($array) - 2, $array) ? $array[count($array) - 2] : "") . "." . $array[count($array) - 1];
//         return $url;
//     }
// }
