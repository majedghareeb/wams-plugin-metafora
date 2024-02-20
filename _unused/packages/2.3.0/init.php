<?php if (!defined('ABSPATH')) exit; ?>


<script type="text/javascript">
	jQuery(document).ready(function() {
		var metarows_pages;
		var current_page = 1;
		var metarows_per_page = 100;

		wams_add_upgrade_log('<?php echo esc_js(__('Upgrade SkypeID fields in WAMS Forms and generally in predefined fields...', 'wams')) ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_skypeid_fields230',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data.message != 'undefined') {
					wams_add_upgrade_log(response.data.message);

					setTimeout(function() {
						if (response.data.count > 0) {
							wams_update_get_usermeta_count230();
						} else {
							wams_reset_password230();
						}
					}, wams_request_throttle);
				} else {
					wams_wrong_ajax();
				}
			},
			error: function() {
				wams_something_wrong();
			}
		});


		function wams_update_get_usermeta_count230() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade SkypeID fields metadata for users...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_usermeta_count230',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data.count != 'undefined') {
						wams_add_upgrade_log('<?php echo esc_js(__('There are ', 'wams')) ?>' + response.data.count + '<?php echo esc_js(__(' metadata rows...', 'wams')) ?>');
						wams_add_upgrade_log('<?php echo esc_js(__('Start metadata upgrading...', 'wams')) ?>');

						metarows_pages = Math.ceil(response.data.count / metarows_per_page);

						setTimeout(function() {
							wams_update_usermeta_part230();
						}, wams_request_throttle);
					} else {
						wams_wrong_ajax();
					}
				},
				error: function() {
					wams_something_wrong();
				}
			});
		}


		function wams_update_usermeta_part230() {
			if (current_page <= metarows_pages) {
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'wams_usermeta_part230',
						page: current_page,
						nonce: wams_admin_scripts.nonce
					},
					success: function(response) {
						if (typeof response.data != 'undefined') {
							wams_add_upgrade_log(response.data.message);
							current_page++;
							setTimeout(function() {
								wams_update_usermeta_part230();
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
					wams_reset_password230();
				}, wams_request_throttle);
			}
		}


		function wams_reset_password230() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade the "Require strong password" options...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_reset_password230',
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
		}
	});
</script>