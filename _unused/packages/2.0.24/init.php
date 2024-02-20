<?php ?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		//upgrade styles
		wams_add_upgrade_log('<?php echo esc_js(__('Purge temp files dir...', 'wams')) ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_tempfolder2024',
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
	});
</script>