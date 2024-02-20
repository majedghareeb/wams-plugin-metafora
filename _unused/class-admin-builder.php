<?php

namespace wams\admin\core;

if (!defined('ABSPATH')) {
	exit;
}

if (!class_exists('wams\admin\core\Admin_Builder')) {


	/**
	 * Class Admin_Builder
	 * @package um\admin\core
	 */
	class Admin_Builder
	{

		/**
		 * @var int
		 */
		public $form_id;

		/**
		 * @var array
		 */
		public $global_fields = array();

		/**
		 * Admin_Builder constructor.
		 */
		public function __construct()
		{
			add_action('wams_admin_field_modal_header', array(&$this, 'add_message_handlers'));
			add_action('wams_admin_field_modal_footer', array(&$this, 'add_conditional_support'), 10, 4);
			add_filter('wams_admin_builder_skip_field_validation', array(&$this, 'skip_field_validation'), 10, 3);
			add_filter('wams_admin_pre_save_field_to_form', array(&$this, 'wams_admin_pre_save_field_to_form'), 1);
			add_filter('wams_admin_pre_save_fields_hook', array(&$this, 'wams_admin_pre_save_fields_hook'), 1);
			add_filter('wams_admin_field_update_error_handling', array(&$this, 'wams_admin_field_update_error_handling'), 1, 2);
		}

		/**
		 * Apply a filter to handle errors for field updating in backend.
		 *
		 * @param null|array $errors
		 * @param array      $submission_data
		 *
		 * @return array
		 */
		public function wams_admin_field_update_error_handling($errors, $submission_data)
		{
			if (!array_key_exists('field_type', $submission_data)) {
				return $errors;
			}

			$blacklist_error = WAMS()->builtin()->blacklist_field_err($submission_data['post']['_metakey']);
			if (!empty($blacklist_error)) {
				$errors['_metakey'] = $blacklist_error;
				return $errors;
			}

			$field_attr = WAMS()->builtin()->get_core_field_attrs($submission_data['field_type']);
			if (!array_key_exists('validate', $field_attr)) {
				return $errors;
			}

			$validate = $field_attr['validate'];
			foreach ($validate as $post_input => $arr) {
				/**
				 * Filters the marker for skipping field validation.
				 *
				 * @param {bool}   $skip            Errors list. It's null by default.
				 * @param {string} $post_input      Field key for validation.
				 * @param {array}  $submission_data Update field handler data.
				 *
				 * @return {bool} True for skipping validation.
				 *
				 * @since 2.1.0
				 * @hook wams_admin_builder_skip_field_validation
				 *
				 * @example <caption>Skipping validation for the `_options` setting field for `billing_country` and `shipping_country` form fields.</caption>
				 * function my_custom_wams_admin_builder_skip_field_validation( $skip, $post_input, $submission_data ) {
				 *     if ( $post_input === '_options' && isset( $submission_data['post']['_metakey'] ) && in_array( $submission_data['post']['_metakey'], array( 'billing_country', 'shipping_country' ), true ) ) {
				 *         $skip = true;
				 *     }
				 *     return $skip;
				 * }
				 * add_filter( 'wams_admin_builder_skip_field_validation', 'my_custom_wams_admin_builder_skip_field_validation', 10, 3 );
				 */
				$skip = apply_filters('wams_admin_builder_skip_field_validation', false, $post_input, $submission_data);
				if ($skip) {
					continue;
				}

				if (!array_key_exists('mode', $arr)) {
					continue;
				}

				switch ($arr['mode']) {
					case 'numeric':
						if (!empty($submission_data['post'][$post_input]) && !is_numeric($submission_data['post'][$post_input])) {
							$errors[$post_input] = $arr['error'];
						}
						break;
					case 'unique':
						if (!isset($submission_data['post']['edit_mode'])) {
							$mode_error = WAMS()->builtin()->unique_field_err($submission_data['post'][$post_input]);
							if (!empty($mode_error)) {
								$errors[$post_input] = $mode_error;
							}
						}
						break;
					case 'required':
						if ('' === $submission_data['post'][$post_input]) {
							$errors[$post_input] = $arr['error'];
						}
						break;
					case 'range-start':
						if ('date_range' === $submission_data['post']['_range']) {
							$mode_error = WAMS()->builtin()->date_range_start_err($submission_data['post'][$post_input]);
							if (!empty($mode_error)) {
								$errors[$post_input] = $mode_error;
							}
						}
						break;
					case 'range-end':
						if ('date_range' === $submission_data['post']['_range']) {
							$mode_error = WAMS()->builtin()->date_range_end_err($submission_data['post'][$post_input], $submission_data['post']['_range_start']);
							if (!empty($mode_error)) {
								$errors[$post_input] = $mode_error;
							}
						}
						break;
				}
			}

			return $errors;
		}

		/**
		 * Some fields may require extra fields before saving.
		 *
		 * @param array $submission_data
		 *
		 * @return array
		 */
		public function wams_admin_pre_save_fields_hook($submission_data)
		{
			if (!array_key_exists('form_id', $submission_data) || !array_key_exists('field_type', $submission_data) || !array_key_exists('post', $submission_data)) {
				return $submission_data;
			}

			$form_id    = $submission_data['form_id'];
			$field_type = $submission_data['field_type'];

			$fields = WAMS()->query()->get_attr('custom_fields', $form_id);
			$count  = 1;
			if (!empty($fields)) {
				$count = count($fields) + 1;
			}

			// Set unique meta key.
			$fields_without_metakey = WAMS()->builtin()->get_fields_without_metakey();
			if (!array_key_exists('_metakey', $submission_data['post']) && in_array($field_type, $fields_without_metakey, true)) {
				$submission_data['post']['_metakey'] = "wams_{$field_type}_{$form_id}_{$count}";
			}

			// Set position.
			if (!array_key_exists('_position', $submission_data['post'])) {
				$submission_data['post']['_position'] = $count;
			}

			return $submission_data;
		}

		/**
		 * Modify field args just before it is saved into form
		 *
		 * @param $array
		 *
		 * @return mixed
		 */
		function wams_admin_pre_save_field_to_form($array)
		{
			unset($array['conditions']);
			if (isset($array['conditional_field']) && !empty($array['conditional_action']) && !empty($array['conditional_operator'])) {
				$array['conditional_value'] = isset($array['conditional_value']) ? $array['conditional_value'] : '';
				$array['conditions'][] = array($array['conditional_action'], $array['conditional_field'], $array['conditional_operator'], $array['conditional_value']);
			}

			if (isset($array['conditional_field1']) && !empty($array['conditional_action1']) && !empty($array['conditional_operator1'])) {
				$array['conditional_value1'] = isset($array['conditional_value1']) ? $array['conditional_value1'] : '';
				$array['conditions'][] = array($array['conditional_action1'], $array['conditional_field1'], $array['conditional_operator1'], $array['conditional_value1']);
			}

			if (isset($array['conditional_field2']) && !empty($array['conditional_action2']) && !empty($array['conditional_operator2'])) {
				$array['conditional_value2'] = isset($array['conditional_value2']) ? $array['conditional_value2'] : '';
				$array['conditions'][] = array($array['conditional_action2'], $array['conditional_field2'], $array['conditional_operator2'], $array['conditional_value2']);
			}

			if (isset($array['conditional_field3']) && !empty($array['conditional_action3']) && !empty($array['conditional_operator3'])) {
				$array['conditional_value3'] = isset($array['conditional_value3']) ? $array['conditional_value3'] : '';
				$array['conditions'][] = array($array['conditional_action3'], $array['conditional_field3'], $array['conditional_operator3'], $array['conditional_value3']);
			}

			if (isset($array['conditional_field4']) && !empty($array['conditional_action4']) && !empty($array['conditional_operator4'])) {
				$array['conditional_value4'] = isset($array['conditional_value4']) ? $array['conditional_value4'] : '';
				$array['conditions'][] = array($array['conditional_action4'], $array['conditional_field4'], $array['conditional_operator4'], $array['conditional_value4']);
			}

			return $array;
		}


		/**
		 * Put status handler in modal
		 */
		function add_message_handlers()
		{
?>
			<div class="um-admin-error-block"></div>
			<div class="um-admin-success-block"></div>
		<?php
		}


		/**
		 * Footer of modal
		 *
		 * @param $form_id
		 * @param $field_args
		 * @param $in_edit
		 * @param $edit_array
		 */
		function add_conditional_support($form_id, $field_args, $in_edit, $edit_array)
		{
			$metabox = WAMS()->metabox();

			if (isset($field_args['conditional_support']) && $field_args['conditional_support'] == 0) {
				return;
			} ?>

			<div class="um-admin-btn-toggle">

				<?php if ($in_edit) {
					$metabox->in_edit = true;
					$metabox->edit_array = $edit_array; ?>
					<a href="javascript:void(0);"><i class="um-icon-plus"></i><?php _e('Manage conditional fields support'); ?></a> <?php WAMS()->tooltip(__('Here you can setup conditional logic to show/hide this field based on specific fields value or conditions', 'wams')); ?>
				<?php } else { ?>
					<a href="javascript:void(0);"><i class="um-icon-plus"></i><?php _e('Add conditional fields support'); ?></a> <?php WAMS()->tooltip(__('Here you can setup conditional logic to show/hide this field based on specific fields value or conditions', 'wams')); ?>
				<?php } ?>

				<div class="um-admin-btn-content">
					<div class="um-admin-cur-condition-template">

						<?php $metabox->field_input('_conditional_action', $form_id); ?>
						<?php $metabox->field_input('_conditional_field', $form_id); ?>
						<?php $metabox->field_input('_conditional_operator', $form_id); ?>
						<?php $metabox->field_input('_conditional_value', $form_id); ?>

						<p><a href="javascript:void(0);" class="um-admin-remove-condition button um-tip-n" title="Remove condition"><i class="um-icon-close" style="margin-right:0!important"></i></a></p>

						<div class="clear"></div>
					</div>
					<p class="um-admin-conditions-notice">
						<small>
							<?php _e('Use the condition operator `equals to` or `not equals` if the parent field has a single option.', 'wams'); ?>
							<br><?php _e('Use the condition operator `greater than` or `less than` if the parent field is a number.', 'wams'); ?>
							<br><?php _e('Use the condition operator `contains` if the parent field has multiple options.', 'wams'); ?>
						</small>
					</p>
					<p><a href="javascript:void(0);" class="um-admin-new-condition button button-primary um-tip-n" title="Add new condition"><?php _e('Add new rule', 'wams'); ?></a></p>
					<p class="um-admin-reset-conditions"><a href="javascript:void(0);" class="button"><?php _e('Reset all rules', 'wams'); ?></a></p>

					<div class="clear"></div>

					<?php if (isset($edit_array['conditions']) && count($edit_array['conditions']) != 0) {

						foreach ($edit_array['conditions'] as $k => $arr) {

							if ($k == 0) $k = ''; ?>

							<div class="um-admin-cur-condition">

								<?php $metabox->field_input('_conditional_action' . $k, $form_id); ?>
								<?php $metabox->field_input('_conditional_field' . $k, $form_id); ?>
								<?php $metabox->field_input('_conditional_operator' . $k, $form_id); ?>
								<?php $metabox->field_input('_conditional_value' . $k, $form_id); ?>

								<p><a href="#" class="um-admin-remove-condition button um-tip-n" title="Remove condition"><i class="um-icon-close" style="margin-right:0!important"></i></a></p>

								<div class="clear"></div>
							</div>

						<?php
						}
					} else { ?>

						<div class="um-admin-cur-condition">

							<?php $metabox->field_input('_conditional_action', $form_id); ?>
							<?php $metabox->field_input('_conditional_field', $form_id); ?>
							<?php $metabox->field_input('_conditional_operator', $form_id); ?>
							<?php $metabox->field_input('_conditional_value', $form_id); ?>

							<p><a href="#" class="um-admin-remove-condition button um-tip-n" title="Remove condition"><i class="um-icon-close" style="margin-right:0!important"></i></a></p>

							<div class="clear"></div>
						</div>

					<?php } ?>
				</div>
			</div>

			<?php
		}


		/**
		 * Update the builder area
		 */
		function update_builder()
		{
			WAMS()->admin()->check_ajax_nonce();

			if (!is_user_logged_in() || !current_user_can('manage_options')) {
				wp_send_json_error(__('Please login as administrator', 'wams'));
			}

			ob_start();

			$this->form_id = absint($_POST['form_id']);

			$this->show_builder();

			$output = ob_get_clean();

			if (is_array($output)) {
				print_r($output);
			} else {
				echo $output;
			}
			die;
		}

		/**
		 * Sort sub-row fields by position.
		 * Callback for `uasort()` function
		 *
		 * @param array $a Array item.
		 * @param array $b Array item.
		 *
		 * @return int
		 */
		public function sorting_fields_by_position($a, $b)
		{
			if (empty($a['position'])) {
				$a['position'] = 0;
			}

			if (empty($b['position'])) {
				$b['position'] = 0;
			}
			$a['position'] = absint($a['position']);
			$b['position'] = absint($b['position']);

			if ($a['position'] === $b['position']) {
				return 0;
			}
			return ($a['position'] < $b['position']) ? -1 : 1;
		}

		/**
		 * Get fields in row
		 *
		 * @param $row_id
		 *
		 * @return string
		 */
		function get_fields_by_row($row_id)
		{

			if (empty($this->global_fields) || !is_array($this->global_fields)) {
				$this->global_fields = array();
			}

			foreach ($this->global_fields as $key => $array) {
				if (!isset($array['in_row']) || (isset($array['in_row']) && $array['in_row'] == $row_id)) {
					$results[$key] = $array;
					unset($this->global_fields[$key]);
				}
			}

			return (isset($results)) ? $results : '';
		}


		/**
		 * Get fields by sub row
		 *
		 * @param $row_fields
		 * @param $subrow_id
		 *
		 * @return string
		 */
		function get_fields_in_subrow($row_fields, $subrow_id)
		{
			if (!is_array($row_fields)) {
				return '';
			}

			foreach ($row_fields as $key => $array) {
				if (!isset($array['in_sub_row']) || (isset($array['in_sub_row']) && $array['in_sub_row'] == $subrow_id)) {
					$results[$key] = $array;
					unset($this->global_fields[$key]);
				}
			}

			return (isset($results)) ? $results : '';
		}

		/**
		 * Display the builder.
		 */
		public function show_builder()
		{
			$fields = WAMS()->query()->get_attr('custom_fields', $this->form_id);

			if (empty($fields)) {
			?>
				<div class="um-admin-drag-row">
					<span class="um-admin-row-loading"><span></span></span>
					<!-- Master Row Actions -->
					<div class="um-admin-drag-row-icons">
						<a href="javascript:void(0);" class="um-admin-drag-rowsub-add um-tip-n" title="<?php esc_attr_e('Add Row', 'wams'); ?>" data-row_action="add_subrow"><i class="um-icon-plus"></i></a>
						<a href="javascript:void(0);" class="um-admin-drag-row-edit um-tip-n" title="<?php esc_attr_e('Edit Row', 'wams'); ?>" data-modal="WAMS_edit_row" data-modal-size="normal" data-dynamic-content="wams_admin_edit_field_popup" data-arg1="row" data-arg2="<?php echo esc_attr($this->form_id); ?>" data-arg3="_wams_row_1"><i class="um-faicon-pencil"></i></a>
						<span class="um-admin-drag-row-start"><i class="um-icon-arrow-move"></i></span>
					</div>
					<div class="clear"></div>
					<div class="um-admin-drag-rowsubs">
						<div class="um-admin-drag-rowsub">
							<span class="um-admin-row-loading"><span></span></span>
							<!-- Column Layout -->
							<div class="um-admin-drag-ctrls columns">
								<a href="javascript:void(0);" class="active" data-cols="1"></a>
								<a href="javascript:void(0);" data-cols="2"></a>
								<a href="javascript:void(0);" data-cols="3"></a>
							</div>
							<!-- Sub Row Actions -->
							<div class="um-admin-drag-rowsub-icons">
								<span class="um-admin-drag-rowsub-start"><i class="um-icon-arrow-move"></i></span>
							</div>
							<div class="clear"></div>
							<!-- Columns -->
							<div class="um-admin-drag-col"></div>
							<div class="um-admin-drag-col-dynamic"></div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<?php
			} else {
				$rows                = array();
				$this->global_fields = is_array($fields) ? $fields : array();
				foreach ($this->global_fields as $key => $field_data) {
					if (array_key_exists('type', $field_data) && 'row' === $field_data['type']) {
						$rows[$key] = $field_data;
						unset($this->global_fields[$key]); // Remove rows from global fields because not needed below.
					}
				}

				// Set 1st row if there aren't any rows in form.
				if (empty($rows)) {
					$rows = array(
						'_wams_row_1' => array(
							'type'     => 'row',
							'id'       => '_wams_row_1',
							'sub_rows' => 1,
							'cols'     => 1,
						),
					);
				}

				foreach ($rows as $row_id => $array) {
				?>
					<div class="um-admin-drag-row" data-original="<?php echo esc_attr($row_id); ?>">
						<span class="um-admin-row-loading"><span></span></span>
						<!-- Master Row Actions -->
						<div class="um-admin-drag-row-icons">
							<a href="javascript:void(0);" class="um-admin-drag-rowsub-add um-tip-n" title="<?php esc_attr_e('Add Row', 'wams'); ?>" data-row_action="add_subrow"><i class="um-icon-plus"></i></a>
							<a href="javascript:void(0);" class="um-admin-drag-row-edit um-tip-n" title="<?php esc_attr_e('Edit Row', 'wams'); ?>" data-modal="WAMS_edit_row" data-modal-size="normal" data-dynamic-content="wams_admin_edit_field_popup" data-arg1="row" data-arg2="<?php echo esc_attr($this->form_id); ?>" data-arg3="<?php echo esc_attr($row_id); ?>"><i class="um-faicon-pencil"></i></a>
							<span class="um-admin-drag-row-start"><i class="um-icon-arrow-move"></i></span>
							<?php if ('_wams_row_1' !== $row_id) { ?>
								<a href="javascript:void(0);" class="um-tip-n" title="<?php esc_attr_e('Delete Row', 'wams'); ?>" data-remove_element="um-admin-drag-row"><i class="um-faicon-trash-o"></i></a>
							<?php } ?>
						</div>
						<div class="clear"></div>
						<div class="um-admin-drag-rowsubs">
							<?php
							$row_fields = $this->get_fields_by_row($row_id);
							$sub_rows   = array_key_exists('sub_rows', $array) ? $array['sub_rows'] : 1;

							for ($c = 0; $c < $sub_rows; $c++) {
								$subrow_fields = $this->get_fields_in_subrow($row_fields, $c);
							?>
								<div class="um-admin-drag-rowsub">
									<span class="um-admin-row-loading"><span></span></span>
									<!-- Column Layout -->
									<div class="um-admin-drag-ctrls columns">
										<?php
										if (!array_key_exists('cols', $array) || empty($array['cols'])) {
											$col_num = 1;
										} elseif (is_numeric($array['cols'])) {
											$col_num = (int) $array['cols'];
										} else {
											$col_split = explode(':', $array['cols']);
											$col_num   = (int) $col_split[$c];
										}

										for ($i = 1; $i <= 3; $i++) {
											$col_class = ($col_num === $i) ? 'active' : '';
										?>
											<a href="javascript:void(0);" class="<?php echo esc_attr($col_class); ?>" data-cols="<?php echo esc_attr($i); ?>"></a>
										<?php
										}
										?>
									</div>
									<!-- Sub Row Actions -->
									<div class="um-admin-drag-rowsub-icons">
										<span class="um-admin-drag-rowsub-start"><i class="um-icon-arrow-move"></i></span>
										<?php if ($c > 0) { ?>
											<a href="javascript:void(0);" class="um-tip-n" title="<?php esc_attr_e('Delete Row', 'wams'); ?>" data-remove_element="um-admin-drag-rowsub"><i class="um-faicon-trash-o"></i></a>
										<?php } ?>
									</div>
									<div class="clear"></div>
									<!-- Columns -->
									<div class="um-admin-drag-col">
										<?php
										if (is_array($subrow_fields)) {
											uasort($subrow_fields, array(&$this, 'sorting_fields_by_position'));
											foreach ($subrow_fields as $key => $keyarray) {
												if (!array_key_exists('type', $keyarray) || !array_key_exists('title', $keyarray)) {
													continue;
												}

												$field_type  = $keyarray['type'];
												$field_title = $keyarray['title'];
												$in_group    = array_key_exists('in_group', $keyarray) ? $keyarray['in_group'] : '';
												$in_column   = array_key_exists('in_column', $keyarray) ? $keyarray['in_column'] : 1;
												$icon        = array_key_exists('icon', $keyarray) ? $keyarray['icon'] : '';
												$field_name  = __('Invalid field type', 'wams');
												if (array_key_exists($field_type, WAMS()->builtin()->core_fields) && array_key_exists('name', WAMS()->builtin()->core_fields[$field_type])) {
													$field_name = WAMS()->builtin()->core_fields[$field_type]['name'];
												}
										?>
												<div class="um-admin-drag-fld um-admin-delete-area um-field-type-<?php echo esc_attr($field_type); ?> <?php echo esc_attr($key); ?>" data-group="<?php echo esc_attr($in_group); ?>" data-key="<?php echo esc_attr($key); ?>" data-column="<?php echo esc_attr($in_column); ?>">
													<div class="um-admin-drag-fld-title um-field-type-<?php echo esc_attr($field_type); ?>">
														<?php if ('group' === $field_type) { ?>
															<i class="um-icon-plus"></i>
														<?php } elseif (!empty($icon)) { ?>
															<i class="<?php echo esc_attr($icon); ?>"></i>
														<?php } ?>
														<?php echo !empty($field_title) ? esc_html($field_title) : esc_html__('(no title)', 'wams'); ?>
													</div>
													<div class="um-admin-drag-fld-type um-field-type-<?php echo esc_attr($field_type); ?>"><?php echo esc_html($field_name); ?></div>
													<div class="um-admin-drag-fld-icons um-field-type-<?php echo esc_attr($field_type); ?>">
														<a href="javascript:void(0);" class="um-tip-n" title="<?php esc_attr_e('Edit', 'wams'); ?>" data-modal="WAMS_edit_field" data-modal-size="normal" data-dynamic-content="wams_admin_edit_field_popup" data-arg1="<?php echo esc_attr($field_type); ?>" data-arg2="<?php echo esc_attr($this->form_id); ?>" data-arg3="<?php echo esc_attr($key); ?>"><i class="um-faicon-pencil"></i></a>
														<a href="javascript:void(0);" class="um-tip-n wams_admin_duplicate_field" title="<?php esc_attr_e('Duplicate', 'wams'); ?>" data-silent_action="wams_admin_duplicate_field" data-arg1="<?php echo esc_attr($key); ?>" data-arg2="<?php echo esc_attr($this->form_id); ?>"><i class="um-faicon-files-o"></i></a>
														<?php if ('group' === $field_type) { ?>
															<a href="javascript:void(0);" class="um-tip-n" title="<?php esc_attr_e('Delete Group', 'wams'); ?>" data-remove_element="um-admin-drag-fld.um-field-type-group" data-silent_action="wams_admin_remove_field" data-arg1="<?php echo esc_attr($key); ?>" data-arg2="<?php echo esc_attr($this->form_id); ?>"><i class="um-faicon-trash-o"></i></a>
														<?php } else { ?>
															<a href="javascript:void(0);" class="um-tip-n" title="<?php esc_attr_e('Delete', 'wams'); ?>" data-silent_action="wams_admin_remove_field" data-arg1="<?php echo esc_attr($key); ?>" data-arg2="<?php echo esc_attr($this->form_id); ?>"><i class="um-faicon-trash-o"></i></a>
														<?php } ?>
													</div>
													<div class="clear"></div>
													<?php if ('group' === $field_type) { ?>
														<div class="um-admin-drag-group"></div>
													<?php } ?>
												</div>
										<?php
											}
										}
										?>
									</div>
									<div class="um-admin-drag-col-dynamic"></div>
									<div class="clear"></div>
								</div>
							<?php
							}
							?>
						</div>
					</div>
				<?php
				}
			}
		}

		/**
		 * AJAX handler for save the custom field in Form Builder.
		 */
		public function update_field()
		{
			WAMS()->admin()->check_ajax_nonce();

			if (!is_user_logged_in() || !current_user_can('manage_options')) {
				wp_send_json_error(__('Please login as administrator', 'wams'));
			}

			$output['error'] = null;

			// phpcs:disable WordPress.Security.NonceVerification -- Already verified by `WAMS()->admin()->check_ajax_nonce()`
			$array = array(
				'field_type' => sanitize_key($_POST['_type']),
				'form_id'    => absint($_POST['post_id']),
				'args'       => WAMS()->builtin()->get_core_field_attrs(sanitize_key($_POST['_type'])),
				'post'       => WAMS()->admin()->sanitize_builder_field_meta($_POST),
			);
			// phpcs:enable WordPress.Security.NonceVerification -- Already verified by `WAMS()->admin()->check_ajax_nonce()`

			/**
			 * Filters the field data before save in Form Builder.
			 *
			 * @param {array} $submission_data Update field handler data. Already sanitized here.
			 *
			 * @return {array} Update field handler data.
			 *
			 * @since 1.3.x
			 * @hook wams_admin_pre_save_fields_hook
			 *
			 * @example <caption>Change submitted value to new one by the field key.</caption>
			 * function my_custom_wams_admin_pre_save_fields_hook( $submission_data ) {
			 *     $submission_data['post']['{field_key}'] = {new value};
			 *     return $submission_data;
			 * }
			 * add_filter( 'wams_admin_pre_save_fields_hook', 'my_custom_wams_admin_pre_save_fields_hook' );
			 */
			$array = apply_filters('wams_admin_pre_save_fields_hook', $array);

			/**
			 * Filters the validation errors on the update field in Form Builder.
			 *
			 * @param {null|array} $errors          Errors list. It's null by default.
			 * @param {array}      $submission_data Update field handler data.
			 *
			 * @return {array} Errors list.
			 *
			 * @since 1.3.x
			 * @hook wams_admin_field_update_error_handling
			 *
			 * @example <caption>Added error with Error text to the field by the field key.</caption>
			 * function my_custom_wams_admin_field_update_error_handling( $errors, $submission_data ) {
			 *     $errors['{field_key}'] = {Error text};
			 *     return $errors;
			 * }
			 * add_filter( 'wams_admin_field_update_error_handling', 'my_custom_wams_admin_field_update_error_handling', 10, 2 );
			 */
			$output['error'] = apply_filters('wams_admin_field_update_error_handling', $output['error'], $array);
			if (empty($output['error'])) {
				$save              = array();
				$field_id          = $array['post']['_metakey']; // Set field ID as it's metakey.
				$save[$field_id] = null;
				foreach ($array['post'] as $key => $val) {
					if ('' !== $val && '_' === substr($key, 0, 1)) { // field attribute
						$new_key = ltrim($key, '_');

						if ('options' === $new_key) {
							$save[$field_id][$new_key] = preg_split('/[\r\n]+/', $val, -1, PREG_SPLIT_NO_EMPTY);
						} else {
							$save[$field_id][$new_key] = $val;
						}
					} elseif (false !== strpos($key, 'wams_editor')) {
						if ('block' === $array['post']['_type']) {
							// the nl2br() function does not work as expected, there is an extra empty line left
							// use str_replace for correct work
							$val                          = str_replace("\r\n\r\n", '<br>', $val);
							$save[$field_id]['content'] = wp_kses_post($val);
						} else {
							$save[$field_id]['content'] = sanitize_textarea_field($val);
						}
					}
				}

				/**
				 * Filters the field options before save to form on the update field in Form Builder.
				 *
				 * @param {array} $field_args Field Options.
				 *
				 * @return {array} Field Options.
				 *
				 * @since 1.3.x
				 * @hook wams_admin_pre_save_field_to_form
				 *
				 * @example <caption>Force change the field's metakey when store it to DB for the form.</caption>
				 * function my_custom_wams_admin_pre_save_field_to_form( $field_args ) {
				 *     $field_args['metakey'] = {new_metakey};
				 *     return $field_args;
				 * }
				 * add_filter( 'wams_admin_pre_save_field_to_form', 'my_custom_wams_admin_pre_save_field_to_form' );
				 */
				$field_args = apply_filters('wams_admin_pre_save_field_to_form', $save[$field_id]);

				WAMS()->fields()->update_field($field_id, $field_args, $array['post']['post_id']);

				/**
				 * Filters the field options before save to DB (globally) on the update field in Form Builder.
				 *
				 * @param {array} $field_args Field Options.
				 *
				 * @return {array} Field Options.
				 *
				 * @since 1.3.x
				 * @hook wams_admin_pre_save_field_to_db
				 *
				 * @example <caption>Force change the field's metakey when store it to DB globally.</caption>
				 * function my_custom_wams_admin_pre_save_field_to_db( $field_args ) {
				 *     $field_args['metakey'] = {new_metakey};
				 *     return $field_args;
				 * }
				 * add_filter( 'wams_admin_pre_save_field_to_db', 'my_custom_wams_admin_pre_save_field_to_db' );
				 */
				$field_args = apply_filters('wams_admin_pre_save_field_to_db', $field_args);

				if (!isset($array['args']['form_only'])) {
					if (!isset(WAMS()->builtin()->predefined_fields[$field_id])) {
						WAMS()->fields()->globally_update_field($field_id, $field_args);
					}
				}
			}

			wp_send_json_success($output);
		}

		/**
		 * AJAX handler for dynamic content inside the modal window.
		 */
		public function dynamic_modal_content()
		{
			WAMS()->admin()->check_ajax_nonce();

			if (!is_user_logged_in() || !current_user_can('manage_options')) {
				wp_send_json_error(__('Please login as administrator', 'wams'));
			}

			// phpcs:disable WordPress.Security.NonceVerification -- already verified here
			if (empty($_POST['act_id'])) {
				wp_send_json_error(__('Wrong dynamic-content attribute.', 'wams'));
			}

			$metabox = WAMS()->metabox();
			$act_id  = sanitize_key($_POST['act_id']);

			$arg1 = null;
			if (isset($_POST['arg1'])) {
				$arg1 = sanitize_text_field($_POST['arg1']);
			}

			$arg2 = null;
			if (isset($_POST['arg2'])) {
				$arg2 = sanitize_text_field($_POST['arg2']);
			}

			$arg3 = null;
			if (isset($_POST['arg3'])) {
				$arg3 = sanitize_text_field($_POST['arg3']);
			}

			$form_mode = null;
			if (isset($_POST['form_mode'])) {
				$form_mode = sanitize_key($_POST['form_mode']);
			}

			$in_row = null;
			if (isset($_POST['in_row'])) {
				$in_row = absint($_POST['in_row']);
			}

			$in_sub_row = null;
			if (isset($_POST['in_sub_row'])) {
				$in_sub_row = absint($_POST['in_sub_row']);
			}

			$in_column = null;
			if (isset($_POST['in_column'])) {
				$in_column = absint($_POST['in_column']);
			}

			$in_group = null;
			if (isset($_POST['in_group'])) {
				$in_group = absint($_POST['in_group']);
			}
			// phpcs:enable WordPress.Security.NonceVerification -- already verified here

			switch ($act_id) {
				default:
					ob_start();
					/**
					 * Fires for integration on AJAX popup admin builder modal content.
					 *
					 * @since 1.3.x
					 * @hook wams_admin_ajax_modal_content__hook
					 *
					 * @param {string} $act_id `data-dynamic-content` attribute value. Modal action.
					 *
					 * @example <caption>Pass HTML to the custom WAMS modal with data-dynamic-content="user_info".</caption>
					 * function my_custom_wams_admin_ajax_modal_content__hook( $act_id ) {
					 *     if ( 'user_info' === $act_id ) {
					 *         // Your HTML is here
					 *     }
					 * }
					 * add_action( 'wams_admin_ajax_modal_content__hook', 'my_custom_wams_admin_ajax_modal_content__hook' );
					 */
					do_action('wams_admin_ajax_modal_content__hook', $act_id);
					/**
					 * Fires for integration on AJAX popup admin builder modal content.
					 *
					 * Note: $act_id `data-dynamic-content` attribute value. Modal action.
					 *
					 * @since 1.3.x
					 * @hook wams_admin_ajax_modal_content__hook_{$act_id}
					 * @deprecated Partially deprecated since 2.6.4. Use common 'wams_admin_ajax_modal_content__hook' and pass `$act_id` as callback attribute.
					 * @todo Fully deprecate since 2.7.0
					 *
					 * @example <caption>Pass HTML to the custom WAMS modal with data-dynamic-content="user_info".</caption>
					 * function my_custom_wams_admin_ajax_modal_content__hook_user_info() {
					 *     // Your HTML is here for `user_info` modal
					 * }
					 * add_action( 'wams_admin_ajax_modal_content__hook_user_info', 'my_custom_wams_admin_ajax_modal_content__hook_user_info' );
					 */
					do_action('wams_admin_ajax_modal_content__hook_' . $act_id);
					$output = ob_get_clean();
					break;
				case 'wams_admin_fonticon_selector':
					ob_start();
				?>
					<div class="um-admin-metabox">
						<p class="_icon_search">
							<label class="screen-reader-text" for="_icon_search"><?php esc_html_e('Search Icons...', 'wams'); ?></label>
							<input type="text" name="_icon_search" id="_icon_search" value="" placeholder="<?php esc_attr_e('Search Icons...', 'wams'); ?>" />
						</p>
					</div>
					<div class="um-admin-icons">
						<?php foreach (WAMS()->fonticons()->all as $icon) { ?>
							<span data-code="<?php echo esc_attr($icon); ?>" title="<?php echo esc_attr($icon); ?>" class="um-tip-n"><i class="<?php echo esc_attr($icon); ?>"></i></span>
						<?php } ?>
					</div>
					<div class="clear"></div>
				<?php
					$output = ob_get_clean();
					break;
				case 'wams_admin_show_fields':
					// $arg2 means `form_id` variable in this case.
					ob_start();
					$form_fields = WAMS()->query()->get_attr('custom_fields', $arg2);
					$form_fields = array_values(array_filter(array_keys($form_fields)));
				?>
					<h4><?php esc_html_e('Setup New Field', 'wams'); ?></h4>
					<div class="um-admin-btns">
						<?php
						if (WAMS()->builtin()->core_fields) {
							foreach (WAMS()->builtin()->core_fields as $field_type => $field_data) {
								if (isset($field_data['in_fields']) && false === $field_data['in_fields']) {
									continue;
								}
						?>
								<a href="javascript:void(0);" class="button" data-modal="WAMS_add_field" data-modal-size="normal" data-dynamic-content="wams_admin_new_field_popup" data-arg1="<?php echo esc_attr($field_type); ?>" data-arg2="<?php echo esc_attr($arg2); ?>"><?php echo esc_html($field_data['name']); ?></a>
						<?php
							}
						}
						?>
					</div>
					<h4><?php esc_html_e('Predefined Fields', 'wams'); ?></h4>
					<div class="um-admin-btns">
						<?php
						if (WAMS()->builtin()->predefined_fields) {
							foreach (WAMS()->builtin()->predefined_fields as $field_key => $field_data) {
								if (array_key_exists('account_only', $field_data) && true === $field_data['account_only']) {
									continue;
								}
								if (array_key_exists('private_use', $field_data) && true === $field_data['private_use']) {
									continue;
								}
						?>
								<a href="javascript:void(0);" class="button" <?php disabled(in_array($field_key, $form_fields, true)); ?> data-silent_action="wams_admin_add_field_from_predefined" data-arg1="<?php echo esc_attr($field_key); ?>" data-arg2="<?php echo esc_attr($arg2); ?>" title="<?php echo esc_attr($field_data['title']); ?>"><?php echo esc_html(wams_trim_string($field_data['title'])); ?></a>
							<?php
							}
						} else {
							?>
							<p><?php esc_html_e('None', 'wams'); ?></p>
						<?php
						}
						?>
					</div>
					<h4><?php esc_html_e('Custom Fields', 'wams'); ?></h4>
					<div class="um-admin-btns">
						<?php
						if (WAMS()->builtin()->custom_fields) {
							foreach (WAMS()->builtin()->custom_fields as $field_key => $array) {
								if (empty($array['title']) || empty($array['type'])) {
									continue;
								}
						?>
								<?php // translators: %s is a field metakey. 
								?>
								<a href="javascript:void(0);" class="button with-icon" <?php disabled(in_array($field_key, $form_fields, true)) ?> data-silent_action="wams_admin_add_field_from_list" data-arg1="<?php echo esc_attr($field_key); ?>" data-arg2="<?php echo esc_attr($arg2); ?>" title="<?php echo esc_attr(sprintf(__('Meta Key - %s', 'wams'), $field_key)); ?>">
									<?php echo esc_html(wams_trim_string(stripslashes($array['title']))); ?> (<?php echo esc_html(ucfirst($array['type'])); ?>)
									<span class="remove dashicons dashicons-dismiss"></span>
								</a>

						<?php
							}
						}
						?>

						<p class="um-no-custom-fields" <?php if (WAMS()->builtin()->custom_fields) { ?> style="display: none;" <?php } ?>>
							<?php esc_html_e('You did not create any custom fields.', 'wams'); ?>
						</p>
					</div>
					<?php
					$output = ob_get_clean();
					break;
				case 'wams_admin_edit_field_popup':
					// $arg1 means `field_type` variable in this case.
					// $arg2 means `form_id` variable in this case.
					// $arg3 means `field_metakey` variable in this case.
					$field_type_data = WAMS()->builtin()->get_core_field_attrs($arg1);
					$form_fields     = WAMS()->query()->get_attr('custom_fields', $arg2);

					if (!array_key_exists($arg3, $form_fields)) {
						$output = '<p>' . esc_html__('This field is not setup correctly for this form.', 'wams') . '</p>';
						break;
					}

					$metabox->set_field_type = $arg1;
					$metabox->in_edit        = true;
					$metabox->edit_array     = $form_fields[$arg3];

					if (!array_key_exists('metakey', $metabox->edit_array)) {
						$metabox->edit_array['metakey'] = $metabox->edit_array['id'];
					}

					if (!array_key_exists('position', $metabox->edit_array)) {
						$metabox->edit_array['position'] = $metabox->edit_array['id'];
					}

					ob_start();

					if (!array_key_exists('col1', $field_type_data)) {
					?>
						<p><?php esc_html_e('This field type is not setup correctly.', 'wams'); ?></p>
						<?php
					} else {
						if ('row' !== $arg1) {
						?>
							<input type="hidden" name="_in_row" id="_in_row" value="<?php echo esc_attr($metabox->edit_array['in_row']); ?>" />
							<input type="hidden" name="_in_sub_row" id="_in_sub_row" value="<?php echo esc_attr($metabox->edit_array['in_sub_row']); ?>" />
							<input type="hidden" name="_in_column" id="_in_column" value="<?php echo esc_attr($metabox->edit_array['in_column']); ?>" />
							<input type="hidden" name="_in_group" id="_in_group" value="<?php echo esc_attr($metabox->edit_array['in_group']); ?>" />
						<?php } ?>
						<input type="hidden" name="_type" id="_type" value="<?php echo esc_attr($arg1); ?>" />
						<input type="hidden" name="post_id" id="post_id" value="<?php echo esc_attr($arg2); ?>" />
						<input type="hidden" name="edit_mode" id="edit_mode" value="true" />
						<input type="hidden" name="_metakey" id="_metakey" value="<?php echo esc_attr($metabox->edit_array['metakey']); ?>" />
						<input type="hidden" name="_position" id="_position" value="<?php echo esc_attr($metabox->edit_array['position']); ?>" />

						<?php if (array_key_exists('mce_content', $field_type_data) && true === $field_type_data['mce_content']) { ?>
							<div class="dynamic-mce-content"><?php echo !empty($metabox->edit_array['content']) ? wp_kses($metabox->edit_array['content'], WAMS()->get_allowed_html('templates')) : ''; ?></div>
						<?php } ?>

						<?php $this->modal_header(); ?>

						<div class="um-admin-half">
							<?php
							if (is_array($field_type_data['col1'])) {
								foreach ($field_type_data['col1'] as $opt) {
									$metabox->field_input($opt, $arg2, $metabox->edit_array);
								}
							}
							?>
						</div>
						<div class="um-admin-half um-admin-right">
							<?php
							if (array_key_exists('col2', $field_type_data) && is_array($field_type_data['col2'])) {
								foreach ($field_type_data['col2'] as $opt) {
									$metabox->field_input($opt, $arg2, $metabox->edit_array);
								}
							}
							?>
						</div>
						<div class="clear"></div>
						<?php
						if (array_key_exists('col3', $field_type_data) && is_array($field_type_data['col3'])) {
							foreach ($field_type_data['col3'] as $opt) {
								$metabox->field_input($opt, $arg2, $metabox->edit_array);
							}
						}
						?>
						<div class="clear"></div>
						<?php
						if (array_key_exists('col_full', $field_type_data) && is_array($field_type_data['col_full'])) {
							foreach ($field_type_data['col_full'] as $opt) {
								$metabox->field_input($opt, $arg2, $metabox->edit_array);
							}
						}
						$this->modal_footer($arg2, $field_type_data, $metabox);
					}
					$output = ob_get_clean();
					break;
				case 'wams_admin_new_field_popup':
					// $arg1 means `field_type` variable in this case.
					// $arg2 means `form_id` variable in this case.
					$field_type_data         = WAMS()->builtin()->get_core_field_attrs($arg1);
					$metabox->set_field_type = $arg1;

					ob_start();

					if (!array_key_exists('col1', $field_type_data)) {
						?>
						<p><?php esc_html_e('This field type is not setup correctly.', 'wams'); ?></p>
					<?php
					} else {
					?>
						<input type="hidden" name="_in_row" id="_in_row" value="_wams_row_<?php echo esc_attr($in_row + 1); ?>" />
						<input type="hidden" name="_in_sub_row" id="_in_sub_row" value="<?php echo esc_attr($in_sub_row); ?>" />
						<input type="hidden" name="_in_column" id="_in_column" value="<?php echo esc_attr($in_column); ?>" />
						<input type="hidden" name="_in_group" id="_in_group" value="<?php echo esc_attr($in_group); ?>" />
						<input type="hidden" name="_type" id="_type" value="<?php echo esc_attr($arg1); ?>" />
						<input type="hidden" name="post_id" id="post_id" value="<?php echo esc_attr($arg2); ?>" />

						<?php $this->modal_header(); ?>

						<div class="um-admin-half">
							<?php
							if (is_array($field_type_data['col1'])) {
								foreach ($field_type_data['col1'] as $opt) {
									$metabox->field_input($opt, $arg2);
								}
							}
							?>
						</div>
						<div class="um-admin-half um-admin-right">
							<?php
							if (array_key_exists('col2', $field_type_data) && is_array($field_type_data['col2'])) {
								foreach ($field_type_data['col2'] as $opt) {
									$metabox->field_input($opt, $arg2);
								}
							}
							?>
						</div>
						<div class="clear"></div>
						<?php
						if (array_key_exists('col3', $field_type_data) && is_array($field_type_data['col3'])) {
							foreach ($field_type_data['col3'] as $opt) {
								$metabox->field_input($opt, $arg2);
							}
						}
						?>
						<div class="clear"></div>
<?php
						if (array_key_exists('col_full', $field_type_data) && is_array($field_type_data['col_full'])) {
							foreach ($field_type_data['col_full'] as $opt) {
								$metabox->field_input($opt, $arg2);
							}
						}

						$this->modal_footer($arg2, $field_type_data, $metabox);
					}
					$output = ob_get_clean();
					break;
				case 'wams_admin_preview_form':
					// $arg1 means `form_id` variable in this case.
					WAMS()->user()->preview = true;

					$mode = WAMS()->query()->get_attr('mode', $arg1);
					if (empty($mode)) {
						$mode = $form_mode;
					}
					if ('profile' === $mode) {
						WAMS()->fields()->editing = true;
					}

					$output  = '<div class="um-admin-preview-overlay"></div>';
					$output .= apply_shortcodes('[wams form_id="' . $arg1 . '" /]');
					break;
				case 'wams_admin_review_registration':
					// $arg1 means `user_id` variable in this case.
					if (!current_user_can('administrator') && !wams_can_view_profile($arg1)) {
						$output = '';
						break;
					}
					wams_fetch_user($arg1);
					WAMS()->user()->preview = true;
					$output               = wams_user_submitted_registration_formatted(true);
					wams_reset_user();
					break;
			}

			// @todo WPCS through wp_kses.
			echo $output;
			die;
		}

		/**
		 *
		 */
		function modal_header()
		{
			/**
			 * WAMS hook
			 *
			 * @type action
			 * @title wams_admin_field_modal_header
			 * @description Modal Window Header
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'wams_admin_field_modal_header', 'function_name', 10 );
			 * @example
			 * <?php
			 * add_action( 'wams_admin_field_modal_header', 'my_admin_field_modal_header', 10 );
			 * function my_admin_field_modal_header() {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action('wams_admin_field_modal_header');
		}


		/**
		 * Modal Footer loading
		 *
		 * @param $arg2
		 * @param $args
		 * @param $metabox
		 */
		function modal_footer($arg2, $args, $metabox)
		{
			/**
			 * WAMS hook
			 *
			 * @type action
			 * @title wams_admin_field_modal_footer
			 * @description Modal Window Footer
			 * @input_vars
			 * [{"var":"$arg2","type":"string","desc":"Ajax Action"},
			 * {"var":"$args","type":"array","desc":"Modal window arguments"},
			 * {"var":"$in_edit","type":"bool","desc":"Is edit mode?"},
			 * {"var":"$edit_array","type":"array","desc":"Edit Array"}]
			 * @change_log
			 * ["Since: 2.0"]
			 * @usage add_action( 'wams_admin_field_modal_footer', 'function_name', 10, 4 );
			 * @example
			 * <?php
			 * add_action( 'wams_admin_field_modal_footer', 'my_admin_field_modal_footer', 10, 4 );
			 * function my_admin_field_modal_footer( $arg2, $args, $in_edit, $edit_array ) {
			 *     // your code here
			 * }
			 * ?>
			 */
			do_action('wams_admin_field_modal_footer', $arg2, $args, $metabox->in_edit, (isset($metabox->edit_array)) ? $metabox->edit_array : '');
		}


		/**
		 * Skip field validation for:
		 *  - '_options' if Choices Callback specified
		 *
		 * @param boolean $skip
		 * @param string $post_input
		 * @param array $array
		 * @return boolean
		 */
		public function skip_field_validation($skip, $post_input, $array)
		{
			if ($post_input === '_options' && isset($array['post']['_custom_dropdown_options_source'])) {
				$skip = function_exists(wp_unslash($array['post']['_custom_dropdown_options_source']));
			}

			return $skip;
		}


		/**
		 *  Retrieves dropdown/multi-select options from a callback function
		 */
		function populate_dropdown_options()
		{
			WAMS()->admin()->check_ajax_nonce();

			if (!is_user_logged_in() || !current_user_can('manage_options')) {
				wp_send_json_error(__('This is not possible for security reasons.', 'wams'));
			}

			$arr_options = array();

			// we can not use `sanitize_key()` because it removes backslash needed for namespace and uppercase symbols
			$wams_callback_func = sanitize_text_field($_POST['wams_option_callback']);
			// removed added by sanitize slashes for the namespaces
			$wams_callback_func = wp_unslash($wams_callback_func);

			if (empty($wams_callback_func)) {
				$arr_options['status'] = 'empty';
				$arr_options['function_name'] = $wams_callback_func;
				$arr_options['function_exists'] = function_exists($wams_callback_func);
			}

			if (WAMS()->fields()->is_source_blacklisted($wams_callback_func)) {
				wp_send_json_error(__('This is not possible for security reasons. Don\'t use internal PHP functions.', 'wams'));
			}

			$arr_options['data'] = array();
			if (function_exists($wams_callback_func)) {
				$arr_options['data'] = call_user_func($wams_callback_func);
			}

			wp_send_json($arr_options);
		}
	}
}
