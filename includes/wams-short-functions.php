<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly.


//Make public functions without class creation

/**
 * Gets time in user-friendly way
 *
 * @param $time
 *
 * @return string
 */
function wams_nice_time($time)
{

	$from_time_unix = strtotime($time);
	$offset = get_option('gmt_offset');
	$from_time = $from_time_unix - $offset * HOUR_IN_SECONDS;
	$current_time = current_time('timestamp') - $offset * HOUR_IN_SECONDS;
	$nice_time = human_time_diff($from_time, $current_time);
	$time = sprintf(__('%s ago', 'wams'), $nice_time);

	return $time;
}

/**
 * Trim string by char length
 *
 *
 * @param $s
 * @param int $length
 *
 * @return string
 */
function wams_trim_string($s, $length = 20)
{
	$s = mb_strlen($s) > $length ? substr($s, 0, $length) . "..." : $s;

	return $s;
}

/**
 * @function wams_user_ip()
 *
 * @description This function returns the IP address of user.
 *
 * @usage <?php $user_ip = wams_user_ip(); ?>
 *
 * @return string The user's IP address.
 *
 * @example The example below can retrieve the user's IP address
 *
 * <?php
 *
 * $user_ip = wams_user_ip();
 * echo 'User IP address is: ' . $user_ip; // prints the user IP address e.g. 127.0.0.1
 *
 * ?>
 */ {
	$ip = '127.0.0.1';

	if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
		//check ip from share internet
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} else if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
		//to check ip is pass from proxy
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	} else if (!empty($_SERVER['REMOTE_ADDR'])) {
		$ip = $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * WAMS hook
	 *
	 * @type filter
	 * @title wams_user_ip
	 * @description Change User IP
	 * @input_vars
	 * [{"var":"$ip","type":"string","desc":"User IP"}]
	 * @change_log
	 * ["Since: 2.0"]
	 * @usage add_filter( 'wams_user_ip', 'function_name', 10, 1 );
	 * @example
	 * <?php
	 * add_filter( 'wams_user_ip', 'my_user_ip', 10, 1 );
	 * function my_user_ip( $ip ) {
	 *     // your code here
	 *     return $ip;
	 * }
	 * ?>
	 */
	return apply_filters('wams_user_ip', $ip);
}

/**
 * Get YouTube video ID from URL.
 *
 * @param string $url
 *
 * @return bool|string
 */
function wams_youtube_id_from_url($url)
{
	$url = preg_replace('/&ab_channel=.*/', '', $url); // ADBlock argument.
	$url = preg_replace('/\?si=.*/', '', $url); // referral attribute.

	$pattern =
		'%^            # Match any youtube URL
		(?:https?://)? # Optional scheme. Either http or https
		(?:                 # Optional subdomain, for example m or www.
			[a-z0-9]          # Subdomain begins with alpha-num.
			(?:               # Optionally more than one char.
				[a-z0-9-]{0,61} # Middle part may have dashes.
				[a-z0-9]        # Starts and ends with alpha-num.
			)?                # Subdomain length from 1 to 63.
			\.                # Required dot separates subdomains.
		)?                  # Subdomain is optional.
		(?:            # Group host alternatives
		  youtu\.be/   # Either youtu.be,
		| youtube\.com # or youtube.com
		  (?:          # Group path alternatives
			/embed/      # Either /embed/
		  | /v/        # or /v/
		  | /watch\?v= # or /watch\?v=
		  | /shorts/   # or /shorts/ for short videos
		  )            # End path alternatives.
		)              # End host alternatives.
		([\w-]{10,12}) # Allow 10-12 for 11 char youtube id.
		(?:            # Additional parameters
		  (?:\?|\&)
		  \w+=[^&$]+
		)*
		$%x';

	$result = preg_match($pattern, $url, $matches);
	if (false !== $result && isset($matches[1])) {
		return $matches[1];
	}

	return false;
}

/**
 * Find closest number in an array
 *
 * @param $array
 * @param $number
 *
 * @return mixed
 */
function wams_closest_num($array, $number)
{
	sort($array);
	foreach ($array as $a) {
		if ($a >= $number) return $a;
	}

	return end($array);
}

/**
 * Get server protocol
 *
 * @return  string
 */
function wams_get_domain_protocol()
{

	if (is_ssl()) {
		$protocol = 'https://';
	} else {
		$protocol = 'http://';
	}

	return $protocol;
}

/**
 * Force strings to UTF-8 encoded
 *
 * @param  mixed $value
 *
 * @return mixed
 */
function wams_force_utf8_string($value)
{

	if (is_array($value)) {
		$arr_value = array();
		foreach ($value as $key => $v) {
			if (!function_exists('utf8_decode')) {
				continue;
			}

			$utf8_decoded_value = utf8_decode($v);

			if (function_exists('mb_check_encoding') && mb_check_encoding($utf8_decoded_value, 'UTF-8')) {
				array_push($arr_value, $utf8_decoded_value);
			} else {
				array_push($arr_value, $v);
			}
		}

		return $arr_value;
	} else {
		if (function_exists('utf8_decode')) {
			$utf8_decoded_value = utf8_decode($value);

			if (function_exists('mb_check_encoding') && mb_check_encoding($utf8_decoded_value, 'UTF-8')) {
				return $utf8_decoded_value;
			}
		}
	}

	return $value;
}


/**
 * Get user host
 *
 * Returns the webhost this site is using if possible
 *
 * @since 1.3.68
 * @return mixed string $host if detected, false otherwise
 */
function wams_get_host()
{
	$host = false;

	if (defined('WPE_APIKEY')) {
		$host = 'WP Engine';
	} else if (defined('PAGELYBIN')) {
		$host = 'Pagely';
	} else if (DB_HOST == 'localhost:/tmp/mysql5.sock') {
		$host = 'ICDSoft';
	} else if (DB_HOST == 'mysqlv5') {
		$host = 'NetworkSolutions';
	} else if (strpos(DB_HOST, 'ipagemysql.com') !== false) {
		$host = 'iPage';
	} else if (strpos(DB_HOST, 'ipowermysql.com') !== false) {
		$host = 'IPower';
	} else if (strpos(DB_HOST, '.gridserver.com') !== false) {
		$host = 'MediaTemple Grid';
	} else if (strpos(DB_HOST, '.pair.com') !== false) {
		$host = 'pair Networks';
	} else if (strpos(DB_HOST, '.stabletransit.com') !== false) {
		$host = 'Rackspace Cloud';
	} else if (strpos(DB_HOST, '.sysfix.eu') !== false) {
		$host = 'SysFix.eu Power Hosting';
	} else if (strpos($_SERVER['SERVER_NAME'], 'Flywheel') !== false) {
		$host = 'Flywheel';
	} else {
		// Adding a general fallback for data gathering
		$host = 'DBH: ' . DB_HOST . ', SRV: ' . $_SERVER['SERVER_NAME'];
	}

	return $host;
}


/**
 * Check if we are on WAMS page
 *
 * @return bool
 */
function is_wams()
{
	global $post;

	if (isset($post->ID) && in_array($post->ID, WAMS()->config()->permalinks))
		return true;

	return false;
}

/**
 * Maybe set empty time limit
 */
function wams_maybe_unset_time_limit()
{
	@set_time_limit(0);
}

function wams_set_cache($name, $value, $expiration = 0)
{
	$option = array('value' => $value, 'expires' => time() + (int) $expiration);
	update_option('wams_cache_' . $name, $option, 'no');
}

function wams_delete_cache($name)
{
	delete_option('wams_cache_' . $name);
}

function wams_get_cache($name)
{
	$option = get_option('wams_cache_' . $name);
	if (false === $option || !isset($option['value']) || !isset($option['expires'])) {
		return false;
	}
	if ($option['expires'] < time()) {
		delete_option('wams_cache_' . $name);
		return false;
	} else {
		return $option['value'];
	}
}

function wams_clear_cache()
{
	global $wpdb;
	$sqlquery = $wpdb->query("DELETE FROM $wpdb->options WHERE option_name LIKE 'wams_cache_%%'");
}
