<?php if (!defined('ABSPATH')) exit; ?>


<script type="text/javascript">
	jQuery(document).ready(function() {
		var users_pages;
		var current_page = 1;
		var users_per_page = 50;

		wams_add_upgrade_log('<?php echo esc_js(__('Upgrade user metadata...', 'wams')) ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_users_count213beta3',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data.count != 'undefined') {
					wams_add_upgrade_log('<?php echo esc_js(__('There are ', 'wams')) ?>' + response.data.count + '<?php echo esc_js(__(' users...', 'wams')) ?>');
					wams_add_upgrade_log('<?php echo esc_js(__('Start metadata upgrading...', 'wams')) ?>');

					users_pages = Math.ceil(response.data.count / users_per_page);

					setTimeout(function() {
						wams_update_metadata_per_user213beta3();
					}, wams_request_throttle);
				} else {
					wams_wrong_ajax();
				}
			},
			error: function() {
				wams_something_wrong();
			}
		});

		function wams_update_metadata_per_user213beta3() {
			if (current_page <= users_pages) {
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'wams_metadata_per_user213beta3',
						page: current_page,
						nonce: wams_admin_scripts.nonce
					},
					success: function(response) {
						if (typeof response.data != 'undefined') {
							wams_add_upgrade_log(response.data.message);
							current_page++;
							setTimeout(function() {
								wams_update_metadata_per_user213beta3();
							}, wams_request_throttle);
						} else {
							wams_wrong_ajax();
						}
					},
					error: function() {
						wams_something_wrong();
					}
				});
			} else {
				setTimeout(function() {
					wams_metatable213beta3();
				}, wams_request_throttle);
			}
		}


		//clear users cache
		function wams_metatable213beta3() {
			wams_add_upgrade_log('<?php echo esc_js(__('Create additional metadata table...', 'wams')) ?>');
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_metatable213beta3',
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