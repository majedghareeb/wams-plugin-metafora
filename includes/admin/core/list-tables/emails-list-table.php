<?php if (!defined('ABSPATH')) exit;

global $wpdb;

if (isset($_REQUEST['_wp_http_referer'])) {
	$redirect = remove_query_arg(array('_wp_http_referer'), wp_unslash($_REQUEST['_wp_http_referer']));
} else {
	$redirect = get_admin_url() . 'admin.php?page=wams';
}

//remove extra query arg
if (!empty($_GET['_wp_http_referer'])) {
	wams_js_redirect(remove_query_arg(array('_wp_http_referer', '_wpnonce'), wp_unslash($_SERVER['REQUEST_URI'])));
}

if (!class_exists('WP_List_Table')) {
	require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}


/**
 * Class WAMS_Emails_List_Table
 */
class WAMS_Emails_List_Table extends WP_List_Table
{


	/**
	 * @var string
	 */
	var $no_items_message = '';


	/**
	 * @var array
	 */
	var $sortable_columns = array();


	/**
	 * @var string
	 */
	var $default_sorting_field = '';


	/**
	 * @var array
	 */
	var $actions = array();


	/**
	 * @var array
	 */
	var $bulk_actions = array();


	/**
	 * @var array
	 */
	var $columns = array();


	/**
	 * WAMS_Emails_List_Table constructor.
	 *
	 * @param array $args
	 */
	function __construct($args = array())
	{
		$args = wp_parse_args($args, array(
			'singular'  => __('item', 'wams'),
			'plural'    => __('items', 'wams'),
			'ajax'      => false
		));

		$this->no_items_message = $args['plural'] . ' ' . __('not found.', 'wams');

		parent::__construct($args);
	}


	/**
	 * @param callable $name
	 * @param array $arguments
	 *
	 * @return mixed
	 */
	function __call($name, $arguments)
	{
		return call_user_func_array(array($this, $name), $arguments);
	}


	/**
	 *
	 */
	function prepare_items()
	{
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns, $hidden, $sortable);
	}


	/**
	 * @param object $item
	 * @param string $column_name
	 *
	 * @return string
	 */
	function column_default($item, $column_name)
	{
		if (isset($item[$column_name])) {
			return $item[$column_name];
		} else {
			return '';
		}
	}


	/**
	 *
	 */
	function no_items()
	{
		echo $this->no_items_message;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_sortable_columns($args = array())
	{
		$return_args = array();
		foreach ($args as $k => $val) {
			if (is_numeric($k)) {
				$return_args[$val] = array($val, $val == $this->default_sorting_field);
			} else if (is_string($k)) {
				$return_args[$k] = array($val, $k == $this->default_sorting_field);
			} else {
				continue;
			}
		}
		$this->sortable_columns = $return_args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_sortable_columns()
	{
		return $this->sortable_columns;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_columns($args = array())
	{
		if (count($this->bulk_actions)) {
			$args = array_merge(array('cb' => '<input type="checkbox" />'), $args);
		}
		$this->columns = $args;

		return $this;
	}


	/**
	 * @return array
	 */
	function get_columns()
	{
		return $this->columns;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_actions($args = array())
	{
		$this->actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_actions()
	{
		return $this->actions;
	}


	/**
	 * @param array $args
	 *
	 * @return $this
	 */
	function set_bulk_actions($args = array())
	{
		$this->bulk_actions = $args;
		return $this;
	}


	/**
	 * @return array
	 */
	function get_bulk_actions()
	{
		return $this->bulk_actions;
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_email($item)
	{
		$active = WAMS()->options()->get($item['key'] . '_on');

		$icon = !empty($active) ? 'um-notification-is-active dashicons-yes' : 'dashicons-no-alt';
		$link = add_query_arg(array('email' => $item['key']));
		$text = '<span class="dashicons um-notification-status ' . esc_attr($icon) . '"></span><a href="' . esc_url($link) . '"><strong>' . $item['title'] . '</strong></a>';

		if (!empty($item['description'])) {
			$text .= ' <span class="wams_tooltip dashicons dashicons-editor-help" title="' . esc_attr__($item['description'], 'wams') . '"></span>';
		}

		return $text;
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_recipients($item)
	{
		if ($item['recipient'] == 'admin') {
			return WAMS()->options()->get('admin_email');
		} else {
			return __('Member', 'wams');
		}
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_configure($item)
	{
		return '<a class="button um-email-configure" href="' . add_query_arg(array('email' => $item['key'])) . '" title="' . esc_attr__('Edit template', 'wams') . '"><span class="dashicons dashicons-admin-generic"></span></a>';
	}


	/**
	 * @param $item
	 *
	 * @return string
	 */
	function column_icl_translations($item)
	{
		return WAMS()->external_integrations()->wpml_column_content($item);
	}


	/**
	 * @param array $attr
	 */
	function wpc_set_pagination_args($attr = array())
	{
		$this->set_pagination_args($attr);
	}
}

$ListTable = new WAMS_Emails_List_Table(array(
	'singular'  => __('Email Notification', 'wams'),
	'plural'    => __('Email Notifications', 'wams'),
	'ajax'      => false
));

$per_page   = 20;
$paged      = $ListTable->get_pagenum();

/**
 * WAMS hook
 *
 * @type filter
 * @title wams_email_templates_columns
 * @description Email Notifications List Table columns
 * @input_vars
 * [{"var":"$columns","type":"array","desc":"Columns"}]
 * @change_log
 * ["Since: 2.0"]
 * @usage add_filter( 'wams_email_templates_columns', 'function_name', 10, 1 );
 * @example
 * <?php
 * add_filter( 'wams_email_templates_columns', 'my_email_templates_columns', 10, 1 );
 * function my_email_templates_columns( $columns ) {
 *     // your code here
 *     $columns['my-custom-column'] = 'My Custom Column';
 *     return $columns;
 * }
 * ?>
 */
$columns = apply_filters('wams_email_templates_columns', array(
	'email'         => __('Email', 'wams'),
	'recipients'    => __('Recipient(s)', 'wams'),
	'configure'     => '',
));

$ListTable->set_columns($columns);

$emails = WAMS()->config()->email_notifications;

$ListTable->prepare_items();
$ListTable->items = array_slice($emails, ($paged - 1) * $per_page, $per_page);
$ListTable->wpc_set_pagination_args(array('total_items' => count($emails), 'per_page' => $per_page)); ?>

<p class="description" style="margin: 20px 0 0 0;">
	<?php
	// translators: %s: doc link.
	echo wp_kses(sprintf(__('You may get more details about email notifications customization <a href="%s">here</a>', 'wams'), 'https://docs.wams.com/article/1335-email-templates'), WAMS()->get_allowed_html('admin_notice'));
	?>
</p>

<form action="" method="get" name="um-settings-emails" id="um-settings-emails">
	<input type="hidden" name="page" value="wams_options" />
	<input type="hidden" name="tab" value="email" />

	<?php $ListTable->display(); ?>
</form>