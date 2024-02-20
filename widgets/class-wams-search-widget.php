<?php

namespace wams\widgets;


// Exit if accessed directly
if (!defined('ABSPATH')) exit;


/**
 * Class WAMS_Search_Widget
 * @package wams\widgets
 */
class WAMS_Search_Widget extends \WP_Widget
{


	/**
	 * WAMS_Search_Widget constructor.
	 */
	function __construct()
	{

		parent::__construct(

			// Base ID of your widget
			'wams_search_widget',

			// Widget name will appear in UI
			__('WAMS - Search', 'wams'),

			// Widget description
			array('description' => __('Shows the search member form.', 'wams'),)
		);
	}


	/**
	 * Creating widget front-end
	 *
	 * @param array $args
	 * @param array $instance
	 */
	public function widget($args, $instance)
	{
		if (defined('REST_REQUEST') && REST_REQUEST) {
			return;
		}

		if (!empty($_GET['legacy-widget-preview']) && defined('IFRAME_REQUEST') && IFRAME_REQUEST) {
			return;
		}

		$title = array_key_exists('title', $instance) ? $instance['title'] : '';
		$title = apply_filters('widget_title', $title);

		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if (!empty($title)) {
			echo $args['before_title'] . $title . $args['after_title'];
		}

		// display the search form
		if (version_compare(get_bloginfo('version'), '5.4', '<')) {
			echo do_shortcode('[wams_searchform /]');
		} else {
			echo apply_shortcodes('[wams_searchform /]');
		}


		echo $args['after_widget'];
	}


	/**
	 * Widget Backend
	 *
	 * @param array $instance
	 */
	public function form($instance)
	{
		if (isset($instance['title'])) {
			$title = $instance['title'];
		} else {
			$title = __('Search Users', 'wams');
		}

		if (isset($instance['max'])) {
			$max = $instance['max'];
		} else {
			$max = 11;
		}

		// Widget admin form
?>

		<p>
			<label for="<?php echo esc_attr($this->get_field_id('title')); ?>"><?php _e('Title', 'wams'); ?>:</label>
			<input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>" name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text" value="<?php echo esc_attr($title); ?>" />
		</p>

<?php
	}


	/**
	 * Updating widget replacing old instances with new
	 *
	 * @param array $new_instance
	 * @param array $old_instance
	 *
	 * @return array
	 */
	public function update($new_instance, $old_instance)
	{
		$instance = array();
		$instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
		return $instance;
	}
}
