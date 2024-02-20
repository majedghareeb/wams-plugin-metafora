<?php ?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		//upgrade styles
		wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Styles...', 'wams')) ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_styles2010',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data != 'undefined') {
					wams_add_upgrade_log(response.data.message);
					setTimeout(function() {
						wams_clear_cache2010();
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
		function wams_clear_cache2010() {
			wams_add_upgrade_log('<?php echo esc_js(__('Clear Users Cache...', 'wams')) ?>');
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_cache2010',
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