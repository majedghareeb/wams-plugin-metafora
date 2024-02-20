<?php if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Formats numbers nicely
 *
 * @param $count
 *
 * @return string
 */
function wams_pretty_number_formatting($count)
{
	$count = (int)$count;
	return number_format($count);
}
add_filter('wams_pretty_number_formatting', 'wams_pretty_number_formatting');
