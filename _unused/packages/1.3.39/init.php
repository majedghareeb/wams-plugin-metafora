<?php ?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		wams_add_upgrade_log('Upgrade Usermeta...');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_usermetaquery1339',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data != 'undefined') {
					wams_add_upgrade_log(response.data.message);
					//switch to the next package
					wams_run_upgrade();
				} else {
					wams_add_upgrade_log('Wrong AJAX response...');
					wams_add_upgrade_log('Your upgrade was crashed, please contact with support');
				}
			},
			error: function() {
				wams_add_upgrade_log('Something went wrong with AJAX request...');
				wams_add_upgrade_log('Your upgrade was crashed, please contact with support');
			}
		});
	});
</script>