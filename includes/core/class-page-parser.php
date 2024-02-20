<?php

namespace wams\frontend;

use Exception;
use GFAPI;

if (!defined('ABSPATH')) {
    exit;
}


class Page_Parser
{
    //private $dom;

    public $target_domains;
    public $url;
    public $dom;
    public $dom_error;
    public $error_message;


    public function __construct()
    {
        require_once WAMS_PATH . 'includes/lib/vendor/autoload.php';

        $this->dom = new \voku\helper\HtmlDomParser;
        // $this->target_domains = self::get_domains_list();
    }



    /**
     * Get Html Content
     * @param   String  URL
     */
    private function loadHtml($url)
    {

        $this->url = $url;

        if (!$this->validate_url($this->url)) {
            $this->dom_error = true;
            $this->error_message = 'URL does not Exist!';
            unset($this->dom);
            return false;
        }
        $this->url = $this::isEncoded($this->url) ? $this->url : $this::flash_encode($this->url);
        try {
            $this->dom->loadHtmlFile($this->url);
            $this->dom_error = false;
            return true;
        } catch (Exception $e) {

            $this->dom_error = true;
            $this->error_message = $e->getMessage();
            return false;
        }
    }

    public function validate_url($url)
    {
        $headers = @get_headers($url);

        // Use condition to check the existence of URL
        return ($headers || (isset($headers[0]) && strpos($headers[0], '404'))) ? true : false;
    }
    /**
     * Return Content Type of the Submitted URL
     * @return  array   Content-type, Code , Message
     */
    public function getContentType($url)
    {
        $response =  get_headers($url, 1);
        // $arr = [
        //     'content-type' => isset($response['Content-Type']) ? $response['Content-Type'] : '',
        //     'code' => $response[0]
        // ];
        return $response;
    }
    /**
     * Return List of Backlinks in submitted URL
     * @param   string  URL
     * @return  array   backlinks['domain','url','anchor_text']
     */
    public function get_backlinks($url)
    {
        if ($this->loadHtml($url)) {
            foreach ($this->dom->find('a') as $e) {
                $url = $e->href;
                $anchor_text = strip_tags($e->plaintext);
                if (filter_var($url, FILTER_VALIDATE_URL)) {
                    $tld = self::tld($url);
                    $backlinks[] = ['domain' => $tld, 'url' => $url, 'anchor_text' => $anchor_text];
                }
            }
        }
        $backlinks = [];

        return $backlinks;
    }
    /**
     * Return Content inside Article
     * 
     * @return  text
     */
    public function get_article_content($attr)
    {
        $content = [];

        foreach ($this->dom->find($attr) as $div) {
            $content[] = $div->innerHtml;
        }

        return $content;
    }
    /**
     * Return H1 tag of page
     * 
     * @return  string   H1 Tag
     */
    public function get_h1_tag()
    {

        $h1 = $this->dom->findOneOrFalse('h1');

        return $h1;
    }
    /**
     * Return Published Date of page
     * 
     * @return  string   Published Date
     */
    public function get_pubDate()
    {
        //$dom = HtmlDomParser::file_get_html($url);
        foreach ($this->dom->find('h1') as $e) {
            $h1 = $e->innerHtml;
        }
        return $h1;
    }

    /**
     * Return title tag of page
     * 
     * @return  string   title Tag
     */
    public function get_title_tag()
    {
        //$dom = HtmlDomParser::file_get_html($url);
        foreach ($this->dom->find('title') as $e) {
            $title_tag = $e->innerHtml;
        }
        return $title_tag;
    }

    /**
     * Return List of Meta tags of page
     * 
     * @return  array   Meta Tags Array ['tag name'=>'tag content']
     */
    public function get_meta_tags()
    {
        foreach ($this->dom->find('meta') as $meta) {
            if ($meta->hasAttribute('content')) {
                if ($meta->hasAttribute('name')) {
                    $meta_data[$meta->getAttribute('name')] = $meta->getAttribute('content');
                } elseif ($meta->hasAttribute('property')) {
                    $meta_data[$meta->getAttribute('property')] = $meta->getAttribute('content');
                }
            }
        }
        $meta_data = (isset($meta_data)) ? $meta_data :  [];
        return $meta_data;
    }
    /**
     * Return JSON output of page schema
     * 
     * @return  JSON   schema
     */
    public function get_schema()
    {
        foreach ($this->dom->find('script') as $script) {
            if ($script->hasAttribute('type') && $script->getAttribute('type') === 'application/ld+json') {

                return json_decode($script->innertext);
            }
        }
    }
    /**
     * Get domain list from Gravity Forms Add Domain Form
     */
    public static function get_domains_list($form_id = 3)
    {
        if (!class_exists('GFAPI')) return;
        $domain_ltd = [];
        $search_criteria = array();
        //$search_criteria['field_filters'][] = array('key' => 'id', 'value' => );
        $sorting = [];
        $paging = [];
        $total_count = 0;
        $entries = GFAPI::get_entries($form_id, $search_criteria, $sorting, $paging, $total_count);
        if ($entries) {
            foreach ($entries as $entry) {
                $domain_name = rgar($entry, '6', 'N/A');
                $domain_ltd[] = $domain_name;
            }
        }
        return $domain_ltd;
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
    /**
     * Encode URL when it is arabic
     * @param string    $str    URL
     * @return string   Endcoded URL
     */
    public static function flash_encode($string)
    {
        $string = rawurlencode($string);
        $string = str_replace("%2F", "/", $string);
        $string = str_replace("%3A", ":", $string);
        return $string;
    }

    /**
     *  Count Backlinks table
     */
    public function count_valid_backlinks()
    {
        $backlinks_list = $this->get_backlinks();
        $i = 0;
        foreach ($backlinks_list as $backlink_detail) {
            in_array($backlink_detail['domain'], $this->target_domains) ? $i++ : $i;
        }
        return $i;
    }
    /**
     *  Get Only Valid Backlinks array to be used in dynamic populating the list in "Add backlinks" form 
     */
    public function get_valid_backlinks()
    {
        if (count($this->validate_backlinks()['valid']) > 0) {
            $valid_backlinks = $this->validate_backlinks()['valid'];
            $populated_backlinks = [];
            foreach ($valid_backlinks as $valid_backlink) {

                $populated_backlinks[] =  $valid_backlink['domain'];
                $populated_backlinks[] =  $valid_backlink['url'];
                $populated_backlinks[] = $valid_backlink['anchor_text'];
            }
            return $populated_backlinks;
        } else return false;
    }
    /**
     *  Get Onlu Valid Unique TLD Domain from Backlinks
     */
    public function get_uniqe_domain_backlinks()
    {
        if (count($this->validate_backlinks()['valid']) > 0) {
            $backlinks_list =  $this->validate_backlinks()['valid'];
            foreach ($backlinks_list as $backlink_detail) {

                $valid_domains[] =  $backlink_detail['domain'];
            }
            return array_unique($valid_domains);
        } else {
            return false;
        }
    }
    /**
     *  Print Backlinks table
     */
    public function print_backlinks_table()
    {
        $backlinks_list = $this->validate_backlinks();
        // echo '<pre>';
        // print_r($backlinks_list);
        $i = 0;
        echo '<table class="table table-bordered border-dark">
            <thead class="thead-light">
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Domain</th>
                    <th scope="col">Anchor Text</th>
                    <th scope="col">URL Link</th>
                </tr>
            </thead>
            <tbody>';




        //if (stripos($backlink_detail['domain'], $target_domain)) {
        // $sytle = strpos($backlink_detail['domain'], $target_domain) ? 'style="background-color:red "' : '';


        $add_backlink = '';
        foreach ($backlinks_list as $type => $links_array) {
            //echo $type;
            $sytle =  'style="background-color:lightblue "';

            if (count($links_array) > 0) {
                foreach ($links_array as $backlink_detail) {
                    if ($type == 'valid') {
                        $add_backlink = '<a href="/create-backlink/?domain=' . $backlink_detail['domain'] . '&link=' . $backlink_detail['url'] . '"> Add Backlink </a>';
                        $sytle =  'style="background-color:LightGreen "';
                    }
                    $i++;
                    $domain = $backlink_detail['domain'];
                    echo '<tr ' . $sytle . '>';
                    echo    ' <th scope="row">' . $i . '</th>';
                    echo     '<td>' . $domain . '</td>';
                    echo     '<td>' . $backlink_detail['anchor_text'] . '</td>';
                    echo     '<td><a target="_blank" href="' . $backlink_detail['url'] . '">Link</a><br>';
                    echo  $add_backlink;
                    echo      '</td>';
                    echo '</tr>';
                }
            }
        }
        // $types = ['valid', 'invalid', 'currentdomain', 'subdomain'];
        // foreach ($types as $k => $type) {

        // }


        echo '</tbody></table>';
    }

    /**
     * Validate Backlinks for adding to form
     * 
     */
    public function validate_backlinks()
    {
        $backlinks['valid'] = [];
        $backlinks['invalid'] = [];
        $backlinks['currentdomain'] = [];
        $backlinks['subdomain'] = [];
        $ignore_subdomain = get_option('wams_domains_settings')['ignore_subdomain'];
        $ignore_parent_domain = get_option('wams_domains_settings')['ignore_parent_domain'];
        $backlinks_list = $this->get_backlinks();
        $target_domains = $this->target_domains;
        $currentDomain = self::tld($this->url);
        $subDomain = self::tldNoSubDomain($this->url);
        foreach ($backlinks_list as $backlink_detail) {
            $domain = $backlink_detail['domain'];
            // check if URL encoded then decode to get arabic url
            $url_o = $backlink_detail['url'];
            $url = ($this::isEncoded($url_o)) ? urldecode($url_o) : $url_o;
            $anchor = $backlink_detail['anchor_text'];
            if (in_array($domain, $target_domains)) {
                // If the backlinks belong to the same domain
                if ($domain == $currentDomain) {
                    $backlinks['currentdomain'][] = ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
                } elseif ($currentDomain != $domain) // Diffent Domain
                    // Check if it is subdomain
                    if ($ignore_subdomain && ((strpos($currentDomain, $domain) > 0) || (strpos($domain, $currentDomain) > 0))) {
                        $backlinks['subdomain'][] =  ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
                    }
                    // Check if it is parent Domain
                    elseif ($ignore_parent_domain && ((strpos($currentDomain, $domain) > 0) || (strpos($domain, $currentDomain) > 0))) {
                        $backlinks['parentdomain'][] =  ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
                    } else {
                        $backlinks['valid'][] = ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
                    }
            } else {
                $backlinks['invalid'][] = ['domain' => $domain, 'url' => $url, 'anchor_text' => $anchor];
            }
        }
        return $backlinks;
    }
    /**
     * Get Top level Domain name TLD from URL
     * @param   string  URL
     * @return  string  TLD
     */
    public static function tld($url)
    {
        if (filter_var($url, FILTER_VALIDATE_URL)) {
            $parsed_url = parse_url($url);
            if (isset($parsed_url['host'])) {
                $domain = $parsed_url['host'];
                $tld = preg_replace('/^www\./', '', $domain);
            } else {
                $tld = false;
            }
        } else {
            $tld = false;
        }

        return $tld;
    }
    /**
     * Get  TLD from URL and remove subdomain
     * @param   string  URL
     * @return  string  TLD
     */
    public static function get_domain_name($url)
    {
        // $parse = parse_url($url);
        // $url =  isset($parse['host']) ? preg_replace("/^([a-zA-Z0-9].*\.)?([a-zA-Z0-9][a-zA-Z0-9-]{1,61}[a-zA-Z0-9]\.[a-zA-Z.]{2,})$/", '$2', $parse['host']) : $url;
        $parseData = parse_url($url);
        $domain = isset($parse['host']) ? preg_replace('/^www\./', '', $parseData['host']) : $url;


        $array = explode(".", $domain);

        $url =  (array_key_exists(count($array) - 2, $array) ? $array[count($array) - 2] : "") . "." . $array[count($array) - 1];
        return $url;
    }
}
