<?php

/**
 * Template for the search form
 *
 * This template can be overridden by copying it to yourtheme/wams/searchform.php
 *
 * Call: function wams_searchform()
 *
 * @version 1.0.0
 *
 * @var string $search_value
 * @var array  $query
 */
if (!defined('ABSPATH')) {
	exit;
} ?>

<div class="search-form wams-search-form" data-search_page="<?php echo esc_url($search_page); ?>">
	<?php foreach (array_keys($query) as $key) { ?>
		<input type="hidden" name="wams-search-keys[]" value="<?php echo esc_attr($key) ?>" />
	<?php } ?>
	<div class="wams-search-area">
		<span class="screen-reader-text"><?php echo _x('Search for:', 'label'); ?></span>
		<input type="search" class="wams-search-field search-field" placeholder="<?php echo esc_attr_x('Search &hellip;', 'placeholder'); ?>" value="<?php echo esc_attr($search_value); ?>" name="search" title="<?php echo esc_attr_x('Search for:', 'label'); ?>" />
		<a href="javascript:void(0);" id="wams-search-button" class="wams-search-icon wams-faicon wams-faicon-search">Search</a>
	</div>
</div>