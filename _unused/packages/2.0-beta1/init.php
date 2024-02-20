<?php ?>

<script type="text/javascript">
	jQuery(document).ready(function() {
		var wams_roles_data;
		var users_per_page = 100;
		var users_pages;
		var forums_pages;
		var products_pages;
		var current_page = 1;

		//upgrade styles
		wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Styles...', 'wams')) ?>');

		jQuery.ajax({
			url: wp.ajax.settings.url,
			type: 'POST',
			dataType: 'json',
			data: {
				action: 'wams_styles20beta1',
				nonce: wams_admin_scripts.nonce
			},
			success: function(response) {
				if (typeof response.data != 'undefined') {
					wams_add_upgrade_log(response.data.message);

					setTimeout(function() {
						upgrade_roles();
					}, wams_request_throttle);
				} else {
					wams_wrong_ajax();
				}
			},
			error: function() {
				wams_something_wrong();
			}
		});


		function upgrade_roles() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Roles...', 'wams')) ?>');
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_user_roles20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);
						wams_roles_data = response.data.roles;

						wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Users...', 'wams')) ?>');

						setTimeout(function() {
							get_users_per_role();
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


		/**
		 *
		 * @returns {boolean}
		 */
		function get_users_per_role() {
			current_page = 1;
			if (wams_roles_data.length) {
				var role = wams_roles_data.shift();
				wams_add_upgrade_log('<?php echo esc_js(__('Getting ', 'wams')) ?>"' + role.role_key + '"<?php echo esc_js(__(' users...', 'wams')) ?>');
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'wams_get_users_per_role20beta1',
						key_in_meta: role.key_in_meta,
						nonce: wams_admin_scripts.nonce
					},
					success: function(response) {
						if (typeof response.data.count != 'undefined') {
							wams_add_upgrade_log('<?php echo esc_js(__('There are ', 'wams')) ?>' + response.data.count + '<?php echo esc_js(__(' users...', 'wams')) ?>');
							wams_add_upgrade_log('<?php echo esc_js(__('Start users upgrading...', 'wams')) ?>');
							users_pages = Math.ceil(response.data.count / users_per_page);

							setTimeout(function() {
								update_user_per_page(role.role_key, role.key_in_meta);
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
					upgrade_content_restriction();
				}, wams_request_throttle);
			}

			return false;
		}


		function update_user_per_page(role_key, key_in_meta) {
			if (current_page <= users_pages) {
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'wams_update_users_per_page20beta1',
						role_key: role_key,
						key_in_meta: key_in_meta,
						page: current_page,
						nonce: wams_admin_scripts.nonce
					},
					success: function(response) {
						if (typeof response.data != 'undefined') {
							wams_add_upgrade_log(response.data.message);
							current_page++;
							setTimeout(function() {
								update_user_per_page(role_key, key_in_meta);
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
					get_users_per_role();
				}, wams_request_throttle);
			}
		}


		function upgrade_content_restriction() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Content Restriction Settings...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_content_restriction20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);
						setTimeout(function() {
							upgrade_settings();
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


		function upgrade_settings() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Settings...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_settings20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);
						setTimeout(function() {
							upgrade_menus();
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


		function upgrade_menus() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Menu Items...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_menus20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);
						setTimeout(function() {
							upgrade_mc_lists();
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


		function upgrade_mc_lists() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Mailchimp Lists...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_mc_lists20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);
						setTimeout(function() {
							upgrade_social_login();
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


		function upgrade_social_login() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Social Login Forms...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_social_login20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);
						setTimeout(function() {
							upgrade_cpt();
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


		function upgrade_cpt() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade WAMS Custom Post Types...', 'wams')) ?>');

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_cpt20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);
						setTimeout(function() {
							get_forums();
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


		function get_forums() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade bbPress Forums...', 'wams')) ?>');
			wams_add_upgrade_log('<?php echo esc_js(__('Get bbPress Forums count...', 'wams')) ?>');
			current_page = 1;
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_get_forums20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);

						forums_pages = Math.ceil(response.data.count / users_per_page);

						setTimeout(function() {
							update_forums_per_page();
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


		function update_forums_per_page() {
			if (current_page <= forums_pages) {
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'wams_update_forwams_per_page20beta1',
						page: current_page,
						nonce: wams_admin_scripts.nonce
					},
					success: function(response) {
						if (typeof response.data != 'undefined') {
							wams_add_upgrade_log(response.data.message);
							current_page++;
							setTimeout(function() {
								update_forums_per_page();
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
					get_products();
				}, wams_request_throttle);
			}
		}


		function get_products() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Woocommerce Products...', 'wams')) ?>');
			wams_add_upgrade_log('<?php echo esc_js(__('Get all Products...', 'wams')) ?>');

			current_page = 1;

			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_get_products20beta1',
					nonce: wams_admin_scripts.nonce
				},
				success: function(response) {
					if (typeof response.data != 'undefined') {
						wams_add_upgrade_log(response.data.message);

						products_pages = Math.ceil(response.data.count / users_per_page);
						setTimeout(function() {
							update_products_per_page();
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


		function update_products_per_page() {
			if (current_page <= products_pages) {
				jQuery.ajax({
					url: wp.ajax.settings.url,
					type: 'POST',
					dataType: 'json',
					data: {
						action: 'wams_update_products_per_page20beta1',
						page: current_page,
						nonce: wams_admin_scripts.nonce
					},
					success: function(response) {
						if (typeof response.data != 'undefined') {
							wams_add_upgrade_log(response.data.message);
							current_page++;
							setTimeout(function() {
								update_products_per_page();
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
					upgrade_email_templates();
				}, wams_request_throttle);
			}
		}


		function upgrade_email_templates() {
			wams_add_upgrade_log('<?php echo esc_js(__('Upgrade Email Templates...', 'wams')) ?>');
			jQuery.ajax({
				url: wp.ajax.settings.url,
				type: 'POST',
				dataType: 'json',
				data: {
					action: 'wams_email_templates20beta1',
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