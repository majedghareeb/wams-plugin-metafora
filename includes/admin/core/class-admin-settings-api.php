<?php

namespace wams\admin\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\core\Admin_Settings_API')) {

	/**
	 * Class Admin_Settings
	 * @package wams\admin\core
	 */
	class Admin_Settings_API
	{
		/**
		 * @var array
		 */

		private $admin_pages = array();

		/**
		 * @var array
		 */

		private $admin_subpages = array();

		/**
		 * @var array
		 */

		private $settings = array();

		/**
		 * @var array
		 */

		private $sections = array();

		/**
		 * @var array
		 */

		private $fields = array();



		public function register()
		{
			if (!empty($this->admin_pages) || !empty($this->admin_subpages)) {
				add_action('admin_menu', array($this, 'addAdminMenu'));
			}

			if (!empty($this->sections)) {
				add_action('admin_init', array($this, 'adminInit'));
			}
		}

		/**
		 * Initialize and registers the settings sections and fileds to WordPress
		 *
		 * Usually this should be called at `admin_init` hook.
		 *
		 * This function gets the initiated settings sections and fields. Then
		 * registers them to WordPress and ready for use.
		 */
		function adminInit()
		{
			//register settings sections
			foreach ($this->sections as $section) {
				register_setting($section['id'], $section['id'], array($this, 'sanitize_options'));
				if (false == get_option($section['id'])) {
					add_option($section['id']);
				}

				if (isset($section['desc']) && !empty($section['desc'])) {
					$section['desc'] = '<div class="inside">' . $section['desc'] . '</div>';
					$callback = function () use ($section) {
						echo str_replace('"', '\"', $section['desc']);
					};
				} else if (isset($section['callback'])) {
					$callback = $section['callback'];
				} else {
					$callback = null;
				}

				add_settings_section($section['id'], $section['title'], $callback, $section['id']);
			}

			//register settings fields
			foreach ($this->fields as $section => $field) {
				foreach ($field as $option) {
					$name = $option['name'];
					$type = isset($option['type']) ? $option['type'] : 'text';
					$label = isset($option['label']) ? $option['label'] : '';
					$callback = isset($option['callback']) ? $option['callback'] : array($this, 'callback_' . $type);

					$args = array(
						'id'                => $name,
						'class'             => isset($option['class']) ? $option['class'] : $name,
						'label_for'         => "{$section}[{$name}]",
						'desc'              => isset($option['desc']) ? $option['desc'] : '',
						'name'              => $label,
						'section'           => $section,
						'size'              => isset($option['size']) ? $option['size'] : null,
						'options'           => isset($option['options']) ? $option['options'] : '',
						'std'               => isset($option['default']) ? $option['default'] : '',
						'sanitize_callback' => isset($option['sanitize_callback']) ? $option['sanitize_callback'] : '',
						'type'              => $type,
						'placeholder'       => isset($option['placeholder']) ? $option['placeholder'] : '',
						'min'               => isset($option['min']) ? $option['min'] : '',
						'max'               => isset($option['max']) ? $option['max'] : '',
						'step'              => isset($option['step']) ? $option['step'] : '',
					);

					add_settings_field("{$section}[{$name}]", $label, $callback, $section, $section, $args);
				}
			}
		}

		public function addPages(array $pages)
		{
			$this->admin_pages = $pages;

			return $this;
		}

		public function withSubPage(string $title = null)
		{
			if (empty($this->admin_pages)) {
				return $this;
			}

			$admin_page = $this->admin_pages[0];

			$subpage = array(
				array(
					'parent_slug' => $admin_page['menu_slug'],
					'page_title' => $admin_page['page_title'],
					'menu_title' => ($title) ? $title : $admin_page['menu_title'],
					'capability' => $admin_page['capability'],
					'menu_slug' => $admin_page['menu_slug'],
					'callback' => $admin_page['callback']
				)
			);

			$this->admin_subpages = $subpage;

			return $this;
		}

		public function addSubPages(array $pages)
		{
			$this->admin_subpages = array_merge($this->admin_subpages, $pages);

			return $this;
		}

		public function addAdminMenu()
		{
			foreach ($this->admin_pages as $page) {
				add_menu_page($page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback'], $page['icon_url'], $page['position']);
			}

			foreach ($this->admin_subpages as $page) {
				add_submenu_page($page['parent_slug'], $page['page_title'], $page['menu_title'], $page['capability'], $page['menu_slug'], $page['callback']);
			}
		}

		public function set_settings(array $settings)
		{
			$this->settings = $settings;

			return $this;
		}

		function set_sections($sections)
		{
			$this->sections = $sections;

			return $this;
		}

		function add_section($section)
		{
			$this->sections[] = $section;

			return $this;
		}

		function set_fields($fields)
		{
			$this->fields = $fields;

			return $this;
		}

		function add_field($section, $field)
		{
			$defaults = array(
				'name'  => '',
				'label' => '',
				'desc'  => '',
				'type'  => 'text'
			);

			$arg = wp_parse_args($field, $defaults);
			$this->fields[$section][] = $arg;

			return $this;
		}





		/**
		 * Get field description for display
		 *
		 * @param array   $args settings field args
		 */
		public function get_field_description($args)
		{
			if (!empty($args['desc'])) {
				$desc = sprintf('<p class="description">%s</p>', $args['desc']);
			} else {
				$desc = '';
			}

			return $desc;
		}

		/**
		 * Displays a text field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_text($args)
		{

			$value       = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$type        = isset($args['type']) ? $args['type'] : 'text';
			$placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';

			$html        = sprintf('<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder);
			$html       .= $this->get_field_description($args);

			echo $html;
		}
		/**
		 * Displays a repeater field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_repeater($args)
		{
			$value = $this->get_option($args['id'], $args['section'], $args['std']);
			$html  = '<div id="myRepeatingFields">';
			$html  = '<fieldset class="entry">';
			$html .= sprintf('<input type="hidden" name="%1$s[%2$s][]" value="" />', $args['section'], $args['id']);
			foreach ($args['options'] as $key => $label) {
				// $checked = isset($value[$key]) ? $value[$key] : '0';
				$html    .= sprintf('<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
				$html    .= sprintf('<input type="text" class="text" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" />', $args['section'], $args['id'], $key);
				$html    .= sprintf('%1$s</label><button type="button" class="btn btn-success btn-lg btn-add">
                <span class="dashicons dashicons-plus" aria-hidden="true"></span>
            </button><br>',  $label);
			}

			$html .= $this->get_field_description($args);
			$html .= '</fieldset>';
			$html .= '</div>';
			echo $html;
		}

		/**
		 * Displays a url field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_url($args)
		{
			$value       = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$type        = isset($args['type']) ? $args['type'] : 'url';
			$placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';

			$html        = sprintf('<input type="%1$s" class="%2$s-text" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder);
			$html       .= $this->get_field_description($args);

			echo $html;
		}

		/**
		 * Displays a number field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_number($args)
		{
			$value       = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$type        = isset($args['type']) ? $args['type'] : 'number';
			$placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';
			$min         = ($args['min'] == '') ? '' : ' min="' . $args['min'] . '"';
			$max         = ($args['max'] == '') ? '' : ' max="' . $args['max'] . '"';
			$step        = ($args['step'] == '') ? '' : ' step="' . $args['step'] . '"';

			$html        = sprintf('<input type="%1$s" class="%2$s-number" id="%3$s[%4$s]" name="%3$s[%4$s]" value="%5$s"%6$s%7$s%8$s%9$s/>', $type, $size, $args['section'], $args['id'], $value, $placeholder, $min, $max, $step);
			$html       .= $this->get_field_description($args);

			echo $html;
		}

		/**
		 * Displays a checkbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_checkbox($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));

			$html  = '<fieldset>';
			$html  .= sprintf('<label for="wpuf-%1$s[%2$s]">', $args['section'], $args['id']);
			$html  .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="off" />', $args['section'], $args['id']);
			$html  .= sprintf('<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s]" name="%1$s[%2$s]" value="on" %3$s />', $args['section'], $args['id'], checked($value, 'on', false));
			$html  .= sprintf('%1$s</label>', $args['desc']);
			$html  .= '</fieldset>';

			echo $html;
		}

		/**
		 * Displays a multicheckbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_multicheck($args)
		{

			$value = $this->get_option($args['id'], $args['section'], $args['std']);
			$html  = '<fieldset>';
			$html .= sprintf('<input type="hidden" name="%1$s[%2$s]" value="" />', $args['section'], $args['id']);
			foreach ($args['options'] as $key => $label) {
				$checked = isset($value[$key]) ? $value[$key] : '0';
				$html    .= sprintf('<label for="wpuf-%1$s[%2$s][%3$s]">', $args['section'], $args['id'], $key);
				$html    .= sprintf('<input type="checkbox" class="checkbox" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s][%3$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($checked, $key, false));
				$html    .= sprintf('%1$s</label><br>',  $label);
			}

			$html .= $this->get_field_description($args);
			$html .= '</fieldset>';

			echo $html;
		}

		/**
		 * Displays a radio button for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_radio($args)
		{

			$value = $this->get_option($args['id'], $args['section'], $args['std']);
			$html  = '<fieldset>';

			foreach ($args['options'] as $key => $label) {
				$html .= sprintf('<label for="wpuf-%1$s[%2$s][%3$s]">',  $args['section'], $args['id'], $key);
				$html .= sprintf('<input type="radio" class="radio" id="wpuf-%1$s[%2$s][%3$s]" name="%1$s[%2$s]" value="%3$s" %4$s />', $args['section'], $args['id'], $key, checked($value, $key, false));
				$html .= sprintf('%1$s</label><br>', $label);
			}

			$html .= $this->get_field_description($args);
			$html .= '</fieldset>';

			echo $html;
		}

		/**
		 * Displays a selectbox for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_select($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$html  = sprintf('<select class="%1$s" name="%2$s[%3$s]" id="%2$s[%3$s]">', $size, $args['section'], $args['id']);

			foreach ($args['options'] as $key => $label) {
				$html .= sprintf('<option value="%s"%s>%s</option>', $key, selected($value, $key, false), $label);
			}

			$html .= sprintf('</select>');
			$html .= $this->get_field_description($args);

			echo $html;
		}

		/**
		 * Displays a textarea for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_textarea($args)
		{

			$value       = esc_textarea($this->get_option($args['id'], $args['section'], $args['std']));
			$size        = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$placeholder = empty($args['placeholder']) ? '' : ' placeholder="' . $args['placeholder'] . '"';

			$html        = sprintf('<textarea rows="5" cols="55" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]"%4$s>%5$s</textarea>', $size, $args['section'], $args['id'], $placeholder, $value);
			$html        .= $this->get_field_description($args);

			echo $html;
		}

		/**
		 * Displays the html for a settings field
		 *
		 * @param array   $args settings field args
		 * @return string
		 */
		function callback_html($args)
		{
			echo $this->get_field_description($args);
		}

		/**
		 * Displays a rich text textarea for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_wysiwyg($args)
		{

			$value = $this->get_option($args['id'], $args['section'], $args['std']);
			$size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : '500px';

			echo '<div style="max-width: ' . $size . ';">';

			$editor_settings = array(
				'teeny'         => true,
				'textarea_name' => $args['section'] . '[' . $args['id'] . ']',
				'textarea_rows' => 10
			);

			if (isset($args['options']) && is_array($args['options'])) {
				$editor_settings = array_merge($editor_settings, $args['options']);
			}

			wp_editor($value, $args['section'] . '-' . $args['id'], $editor_settings);

			echo '</div>';

			echo $this->get_field_description($args);
		}

		/**
		 * Displays a file upload field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_file($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$id    = $args['section']  . '[' . $args['id'] . ']';
			$label = isset($args['options']['button_label']) ? $args['options']['button_label'] : __('Choose File');

			$html  = sprintf('<input type="text" class="%1$s-text wpsa-url" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
			$html  .= '<input type="button" class="button wpsa-browse" value="' . $label . '" />';
			$html  .= $this->get_field_description($args);

			echo $html;
		}

		/**
		 * Displays a password field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_password($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';
			$html  = sprintf('<input type="password" class="%1$s-text" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s"/>', $size, $args['section'], $args['id'], $value);
			$html  .= $this->get_field_description($args);

			echo $html;
		}

		/**
		 * Displays a color picker field for a settings field
		 *
		 * @param array   $args settings field args
		 */
		function callback_color($args)
		{

			$value = esc_attr($this->get_option($args['id'], $args['section'], $args['std']));
			$size  = isset($args['size']) && !is_null($args['size']) ? $args['size'] : 'regular';

			$html  = sprintf('<input type="text" class="%1$s-text wp-color-picker-field" id="%2$s[%3$s]" name="%2$s[%3$s]" value="%4$s" data-default-color="%5$s" />', $size, $args['section'], $args['id'], $value, $args['std']);
			$html  .= $this->get_field_description($args);

			echo $html;
		}
		/**
		 * Displays a list field for a settings field
		 *
		 * @param array   $args settings field args
		 */



		function callback_list($args)
		{

			$stored_items = $this->get_option($args['id'], $args['section'], '');
			$html  = '<fieldset>';
			$html  = '<div class="input-group mb-3">';
			$html  .= '<input type="text" id="new-item" placeholder="New Item" />';
			$html  .= '<div class="input-group-prepend">';
			$html  .= '<button type="button" class="btn btn-success btn-sm" id="add-item"><span class="dashicons dashicons-plus" aria-hidden="true"></button>';
			$html .=  '</div>';
			$html .= '</fieldset>';
			$html  .= '</div>';
			$html .= sprintf('<input type="hidden" id="hidden-list" name="%1$s[%2$s]" value="%3$s" />', $args['section'], $args['id'], esc_attr($stored_items));
			$html .=  '<div id="custom-list">';
			$html  .= $this->get_field_description($args);
			$items = json_decode($stored_items, true);
			if (!empty($items) && is_array($items)) {
				foreach ($items as $index => $item) {
					$html  .= '<div class="list-row input-group py-2"><input type="text" class="list-item" name="custom_list_items[]" value="' . esc_attr($item) . '" /><button type="button" class="delete-item btn btn-danger btn-sm"><span class="dashicons dashicons-minus" aria-hidden="true"></button></div>';
				}
			} else {
				$html  .= '<div class="list-row input-group d-none"><input type="text" class="list-item" name="custom_list_items[]" value="" /><button type="button" class="delete-item btn btn-danger"><span class="dashicons dashicons-minus" aria-hidden="true"></button></div>';
			}

			echo $html;
		}

		/**
		 * Displays a select box for creating the pages select box
		 *
		 * @param array   $args settings field args
		 */
		function callback_pages($args)
		{
			$dropdown_args = array(
				'selected' => esc_attr($this->get_option($args['id'], $args['section'], $args['std'])),
				'name'     => $args['section'] . '[' . $args['id'] . ']',
				'id'       => $args['section'] . '[' . $args['id'] . ']',
				'echo'     => 0
			);
			$html = wp_dropdown_pages($dropdown_args);
			echo $html;
		}

		/**
		 * Sanitize callback for Settings API
		 *
		 * @return mixed
		 */
		function sanitize_options($options)
		{

			if (!$options) {
				return $options;
			}

			foreach ($options as $option_slug => $option_value) {
				$sanitize_callback = $this->get_sanitize_callback($option_slug);

				// If callback is set, call it
				if ($sanitize_callback) {
					$options[$option_slug] = call_user_func($sanitize_callback, $option_value);
					continue;
				}
			}

			return $options;
		}

		/**
		 * Get sanitization callback for given option slug
		 *
		 * @param string $slug option slug
		 *
		 * @return mixed string or bool false
		 */
		function get_sanitize_callback($slug = '')
		{
			if (empty($slug)) {
				return false;
			}

			// Iterate over registered fields and see if we can find proper callback
			foreach ($this->fields as $section => $options) {
				foreach ($options as $option) {
					if ($option['name'] != $slug) {
						continue;
					}

					// Return the callback name
					return isset($option['sanitize_callback']) && is_callable($option['sanitize_callback']) ? $option['sanitize_callback'] : false;
				}
			}

			return false;
		}

		/**
		 * Get the value of a settings field
		 *
		 * @param string  $option  settings field name
		 * @param string  $section the section name this field belongs to
		 * @param string  $default default text if it's not found
		 * @return string
		 */
		function get_option($option, $section, $default = '')
		{

			$options = get_option($section);

			if (isset($options[$option])) {
				return $options[$option];
			}

			return $default;
		}

		/**
		 * Show navigations as tab
		 *
		 * Shows all the settings section labels as tab
		 */
		function show_navigation()
		{
			$html = '<ul>';
			settings_errors();

			$count = count($this->sections);

			// don't show the navigation if only one section exists
			if ($count === 1) {
				return;
			}

			foreach ($this->sections as $tab) {
				$html .= sprintf('<li><a href="#%1$s">%2$s</a></li>', $tab['id'], $tab['title']);
			}

			$html .= '</ul>';

			echo $html;
		}

		function show_settings_page($page_title = '')
		{
			echo '<h1>' . $page_title . '</h1>';
			echo '<div class="wraper">';
			echo '<div id="tabs">';
			$this->show_navigation();
			echo '<div class="border p-3">';
			$this->show_forms();
			echo '</div>';
			echo '</div>';
			echo '</div>';
		}

		/**
		 * Show the section settings forms
		 *
		 * This function displays every sections in a different form
		 */
		function show_forms()
		{
?>
			<?php
			foreach ($this->sections as $form) {
			?>
				<div id="<?php echo $form['id']; ?>">

					<form method="post" action="options.php">
						<?php

						settings_fields($form['id']);
						do_settings_sections($form['id']);
						if (isset($this->fields[$form['id']])) :
						?>
							<div style="padding-left: 10px">
								<?php submit_button(); ?>
							</div>
						<?php endif; ?>

				</div>
				</form>
<?php
			}
		}
	}
}
