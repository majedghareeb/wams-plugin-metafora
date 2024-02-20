<?php if (!defined('ABSPATH')) exit; ?>


<script type="text/javascript">
	jQuery(document).ready(function() {
		//upgrade styles
		wams_add_upgrade_log('<?php echo esc_js(__('Upgrade user metadata...', 'wams')) ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_metadata210beta1',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data != 'undefined') {
					wams_add_upgrade_log(response.data.message);
					setTimeout(function() {
						wams_memberdir210beta1();
					}, wams_request_throttle);
				} else {
					wams_wrong_ajax();
				}
			},
			error: function() {
				wams_something_wrong();
			}
		});


		//clear users cache
		function wams_memberdir210beta1() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Member Directories...', 'wams')) ?>');
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_memberdir210beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
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
		}
	});
</script>