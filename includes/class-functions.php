<?php if (!defined('ABSPATH')) exit;


if (!class_exists('WAMS_Functions')) {


	/**
	 * Class WAMS_Functions
	 */
	class WAMS_Functions
	{

		/**WAMS_
		 * WAMS_Functions constructor.
		 */
		function __construct()
		{
		}


		/**
		 * Check if AJAX now
		 *
		 * @return bool
		 */
		function is_ajax()
		{
			return function_exists('wp_doing_ajax') ? wp_doing_ajax() : defined('DOING_AJAX');
		}


		/**
		 * Check frontend nonce
		 *
		 * @param bool $action
		 */
		function check_ajax_nonce($action = false)
		{
			$nonce = isset($_REQUEST['nonce']) ? sanitize_text_field($_REQUEST['nonce']) : '';
			$action = empty($action) ? 'wams-frontend-nonce' : $action;

			if (!wp_verify_nonce($nonce, $action)) {
				wp_send_json_error(esc_js(__('Wrong Nonce', 'wams')));
			}
		}


		/**
		 * What type of request is this?
		 *
		 * @param string $type String containing name of request type (ajax, frontend, cron or admin)
		 *
		 * @return bool
		 */
		public function is_request($type)
		{
			switch ($type) {
				case 'admin':
					return is_admin();
				case 'ajax':
					return defined('DOING_AJAX');
				case 'cron':
					return defined('DOING_CRON');
				case 'frontend':
					return (!is_admin() || defined('DOING_AJAX')) && !defined('DOING_CRON');
			}

			return false;
		}


		/**
		 * Help Tip displaying
		 *
		 * Function for render/displaying WAMS help tip
		 *
		 * @since  2.0.0
		 *
		 * @param string $tip Help tip text
		 * @param bool $allow_html Allow sanitized HTML if true or escape
		 * @param bool $echo Return HTML or echo
		 * @return string
		 */
		function tooltip($tip, $allow_html = false, $echo = true)
		{
			if ($allow_html) {

				$tip = htmlspecialchars(wp_kses(html_entity_decode($tip), array(
					'br'     => array(),
					'em'     => array(),
					'strong' => array(),
					'small'  => array(),
					'span'   => array(),
					'ul'     => array(),
					'li'     => array(),
					'ol'     => array(),
					'p'      => array(),
				)));
			} else {
				$tip = esc_attr($tip);
			}

			ob_start();

			echo '<span class="wams_tooltip dashicons dashicons-editor-help" title="' . $tip . '"></span>';
			if ($echo) {
				ob_get_flush();
				return '';
			} else {
				return ob_get_clean();
			}
		}

		/**
		 * Output templates
		 *
		 * @access public
		 * @param string $template_name
		 * @param string $basename (default: '')
		 * @param array $t_args (default: array())
		 * @param bool $echo
		 *
		 * @return string|void
		 */
		public function get_template($template_name, $basename = '', $t_args = array(), $echo = false)
		{
			if (!empty($t_args) && is_array($t_args)) {
				/*
				 * This use of extract() cannot be removed. There are many possible ways that
				 * templates could depend on variables that it creates existing, and no way to
				 * detect and deprecate it.
				 *
				 * Passing the EXTR_SKIP flag is the safest option, ensuring globals and
				 * function variables cannot be overwritten.
				 */
				// phpcs:ignore WordPress.PHP.DontExtract.extract_extract
				extract($t_args, EXTR_SKIP);
			}

			$path = '';
			if ($basename) {
				// use '/' instead of "DIRECTORY_SEPARATOR", because wp_normalize_path makes the correct replacement
				$array = explode('/', wp_normalize_path(trim($basename)));
				$path  = $array[0];
			}

			$located = $this->locate_template($template_name, $path);
			if (!file_exists($located)) {
				_doing_it_wrong(__FUNCTION__, sprintf('<code>%s</code> does not exist.', $located), '2.1');
				return;
			}

			ob_start();
			include $located;
			$html = ob_get_clean();
			if (!$echo) {
				return $html;
			} else {
				echo $html;
				return;
			}
		}


		/**
		 * Locate a template and return the path for inclusion.
		 *
		 * @access public
		 * @param string $template_name
		 * @param string $path (default: '')
		 * @return string
		 */
		function locate_template($template_name, $path = '')
		{
			// check if there is template at theme folder
			$template = locate_template(array(
				trailingslashit('wams' . DIRECTORY_SEPARATOR . $path) . $template_name
			));

			if (!$template) {
				if ($path) {
					$template = trailingslashit(trailingslashit(WP_PLUGIN_DIR) . $path);
				} else {
					$template = trailingslashit(WAMS_PATH);
				}
				$template .= 'templates' . DIRECTORY_SEPARATOR . $template_name;
			}
			return $template;
		}

		/**
		 * @param array $array
		 * @param string $key
		 * @param array $insert_array
		 *
		 * @return array
		 */
		function array_insert_before($array, $key, $insert_array)
		{
			$index = array_search($key, array_keys($array));
			if ($index === false) {
				return $array;
			}

			$array = array_slice($array, 0, $index, true) +
				$insert_array +
				array_slice($array, $index, count($array) - 1, true);

			return $array;
		}


		/**
		 * @since 2.1.0
		 *
		 * @param $var
		 * @return array|string
		 */
		function clean_array($var)
		{
			if (is_array($var)) {
				return array_map(array($this, 'clean_array'), $var);
			} else {
				return !is_scalar($var) ? sanitize_text_field($var) : $var;
			}
		}


		/**
		 * Replace the first match in the string, alternative for the `str_replace()` function
		 *
		 * @param string $search
		 * @param string $replace
		 * @param string $subject
		 *
		 * @return string
		 */
		function str_replace_first($search, $replace, $subject)
		{
			$search = '/' . preg_quote($search, '/') . '/';
			return preg_replace($search, $replace, $subject, 1);
		}

		/**
		 * @param string $context
		 *
		 * @return array
		 */
		public function get_allowed_html($context = '')
		{
			switch ($context) {
				case 'wp-admin':
					$allowed_html = array(
						'img'      => array(
							'alt'      => true,
							'align'    => true,
							'border'   => true,
							'height'   => true,
							'hspace'   => true,
							'loading'  => true,
							'longdesc' => true,
							'vspace'   => true,
							'src'      => true,
							'srcset'   => true,
							'usemap'   => true,
							'width'    => true,
						),
						'ol'       => array(),
						'ul'       => array(),
						'li'       => array(),
						'h1'       => array(
							'align' => true,
						),
						'h2'       => array(
							'align' => true,
						),
						'h3'       => array(
							'align' => true,
						),
						'p'        => array(
							'align' => true,
							'dir'   => true,
							'lang'  => true,
						),
						'form'     => array(
							'action'         => true,
							'accept'         => true,
							'accept-charset' => true,
							'enctype'        => true,
							'method'         => true,
							'name'           => true,
							'target'         => true,
						),
						'label'    => array(
							'for' => true,
						),
						'select'   => array(
							'name'         => true,
							'multiple'     => true,
							'disabled'     => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'option'   => array(
							'value'    => true,
							'selected' => true,
							'disabled' => true,
						),
						'input'    => array(
							'type'         => true,
							'name'         => true,
							'value'        => true,
							'placeholder'  => true,
							'readonly'     => true,
							'disabled'     => true,
							'checked'      => true,
							'selected'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'textarea' => array(
							'cols'         => true,
							'rows'         => true,
							'disabled'     => true,
							'name'         => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'table'    => array(
							'align'       => true,
							'bgcolor'     => true,
							'border'      => true,
							'cellpadding' => true,
							'cellspacing' => true,
							'dir'         => true,
							'rules'       => true,
							'summary'     => true,
							'width'       => true,
						),
						'tbody'    => array(
							'align'   => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
						'td'       => array(
							'abbr'    => true,
							'align'   => true,
							'axis'    => true,
							'bgcolor' => true,
							'char'    => true,
							'charoff' => true,
							'colspan' => true,
							'dir'     => true,
							'headers' => true,
							'height'  => true,
							'nowrap'  => true,
							'rowspan' => true,
							'scope'   => true,
							'valign'  => true,
							'width'   => true,
						),
						'tfoot'    => array(
							'align'   => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
						'th'       => array(
							'abbr'    => true,
							'align'   => true,
							'axis'    => true,
							'bgcolor' => true,
							'char'    => true,
							'charoff' => true,
							'colspan' => true,
							'headers' => true,
							'height'  => true,
							'nowrap'  => true,
							'rowspan' => true,
							'scope'   => true,
							'valign'  => true,
							'width'   => true,
						),
						'thead'    => array(
							'align'   => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
						'tr'       => array(
							'align'   => true,
							'bgcolor' => true,
							'char'    => true,
							'charoff' => true,
							'valign'  => true,
						),
						'a'        => array(
							'onclick' => array(),
						),
					);
					break;
				case 'templates':
					$allowed_html = array(
						'style'    => array(),
						'link'     => array(
							'rel'   => true,
							'href'  => true,
							'media' => true,
						),
						'form'     => array(
							'action'         => true,
							'accept'         => true,
							'accept-charset' => true,
							'enctype'        => true,
							'method'         => true,
							'name'           => true,
							'target'         => true,
						),
						'label'    => array(
							'for' => true,
						),
						'select'   => array(
							'name'         => true,
							'multiple'     => true,
							'disabled'     => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'option'   => array(
							'value'    => true,
							'selected' => true,
							'disabled' => true,
						),
						'input'    => array(
							'type'         => true,
							'name'         => true,
							'value'        => true,
							'placeholder'  => true,
							'readonly'     => true,
							'disabled'     => true,
							'checked'      => true,
							'selected'     => true,
							'required'     => true,
							'autocomplete' => true,
							'size'         => true,
							'step'         => true,
							'min'          => true,
							'max'          => true,
							'minlength'    => true,
							'maxlength'    => true,
							'pattern'      => true,
						),
						'textarea' => array(
							'cols'         => true,
							'rows'         => true,
							'disabled'     => true,
							'name'         => true,
							'readonly'     => true,
							'required'     => true,
							'autocomplete' => true,
						),
						'img'      => array(
							'alt'      => true,
							'align'    => true,
							'border'   => true,
							'height'   => true,
							'hspace'   => true,
							'loading'  => true,
							'longdesc' => true,
							'vspace'   => true,
							'src'      => true,
							'srcset'   => true,
							'usemap'   => true,
							'width'    => true,
						),
						'h1'       => array(
							'align' => true,
						),
						'h2'       => array(
							'align' => true,
						),
						'h3'       => array(
							'align' => true,
						),
						'p'        => array(
							'align' => true,
							'dir'   => true,
							'lang'  => true,
						),
						'ol'       => array(),
						'ul'       => array(),
						'li'       => array(),
						'time'     => array(
							'datetime' => true,
						),
					);
					break;
				case 'admin_notice':
					$allowed_html = array(
						'p'      => array(
							'align' => true,
							'dir'   => true,
							'lang'  => true,
						),
						'label'  => array(
							'for' => true,
						),
						'strong' => array(
							'style' => true,
						),
					);
					break;
				default:
					$allowed_html = array();
					break;
			}

			$global_allowed = array(
				'a'      => array(
					'href'     => array(),
					'rel'      => true,
					'rev'      => true,
					'name'     => true,
					'target'   => true,
					'download' => array(
						'valueless' => 'y',
					),
				),
				'em'     => array(),
				'i'      => array(),
				'b'      => array(),
				'q'      => array(
					'cite' => true,
				),
				's'      => array(),
				'strike' => array(),
				'strong' => array(),
				'br'     => array(),
				'div'    => array(
					'align' => true,
					'dir'   => true,
					'lang'  => true,
				),
				'span'   => array(
					'dir'   => true,
					'align' => true,
					'lang'  => true,
				),
				'code'   => array(),
				'hr'     => array(
					'style' => true,
				),
			);

			$allowed_html = array_merge($global_allowed, $allowed_html);
			$allowed_html = array_map('_wp_add_global_attributes', $allowed_html);
			return $allowed_html;
		}
	}
}
