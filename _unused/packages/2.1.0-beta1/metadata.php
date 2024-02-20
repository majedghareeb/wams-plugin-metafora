<?php if (!defined('ABSPATH')) exit;

global $wpdb;

$wpdb->update(
	"{$wpdb->usermeta}",
	array(
		'meta_value'    => serialize(array()),
	),
	array(
		'meta_key'      => 'wams_account_secure_fields',
	),
	array(
		'%s'
	),
	array(
		'%s'
	)
);
