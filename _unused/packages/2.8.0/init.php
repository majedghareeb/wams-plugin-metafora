<?php
if (!defined('ABSPATH')) {
	exit;
}
?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		var users_pages;
		var current_page = 1;
		var users_per_page = 100;

		wams_add_upgrade_log('<?php echo esc_js(__('Upgrade user metadata...', 'wams')); ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_usermeta_count280',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data.count != 'undefined') {
					wams_add_upgrade_log('<?php echo esc_js(__('There are ', 'wams')); ?>' + response.data.count + '<?php echo esc_js(__(' metarows...', 'wams')); ?>');
					wams_add_upgrade_log('<?php echo esc_js(__('Start metadata upgrading...', 'wams')); ?>');

					users_pages = Math.ceil(response.data.count / users_per_page);

					setTimeout(function() {
						wams_update_metadata_per_user280();
					}, wams_request_throttle);
				} else {
					wams_wrong_ajax();
				}
			},
			error: function() {
				wams_something_wrong();
			}
		});

		function wams_update_metadata_per_user280() {
			if (current_page <= users_pages) {
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'wams_metadata_per_user280',
						page: current_page,
						nonce: wams_admin_scripts.nonce
					},
					success: function(response) {
						if (typeof response.data != 'undefined') {
							wams_add_upgrade_log(response.data.message);
							current_page++;
							setTimeout(function() {
								wams_update_metadata_per_user280();
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
					wams_option_update280();
				}, wams_request_throttle);
			}
		}


		//clear users cache
		function wams_option_update280() {
			wams_add_upgrade_log('<?php echo esc_js(__('Update options table...', 'wams')); ?>');
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_update_options280',
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