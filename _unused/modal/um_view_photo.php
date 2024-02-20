<?php

/**
 * Template for the modal photo
 *
 * This template can be overridden by copying it to yourtheme/wams/modal/wams_view_photo.php
 *
 * @version 2.6.1
 */
if (!defined('ABSPATH')) {
	exit;
} ?>

<div id="wams_view_photo" style="display:none">

	<a href="javascript:void(0);" data-action="wams_remove_modal" class="um-modal-close" aria-label="<?php esc_attr_e('Close view photo modal', 'wams') ?>">
		<i class="um-faicon-times"></i>
	</a>

	<div class="um-modal-body photo">
		<div class="um-modal-photo"></div>
	</div>

</div>