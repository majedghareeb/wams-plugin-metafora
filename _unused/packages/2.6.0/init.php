<?php if (!defined('ABSPATH')) exit; ?>


<script type="text/javascript">
	jQuery(document).ready(function() {
		wams_add_upgrade_log('<?php echo esc_js(__('Updated social URLs fields in the WAMS Forms fields...', 'wams')) ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_social_fields260',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data.message != 'undefined') {
					wams_add_upgrade_log(response.data.message);
					//switch to the next package
					wams_run_upgrade();
				} else {
					wams_wrong_ajax();
				}
			},
			error: function() {
				wams_something_wrong();
			}
		});
	});
</script>