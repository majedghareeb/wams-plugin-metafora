/**
 * Global wp-admin scripts that must be enqueued everywhere on wp-admin.
 */

jQuery(document).ready(function () {

	jQuery(document.body).on('click', '.wams_secondary_dismiss', function () {
		jQuery(this).parents('.wams-admin-notice').find('.notice-dismiss').trigger('click');
	});

	jQuery(document.body).on('click', '.wams-admin-notice.is-dismissible .notice-dismiss', function () {
		let notice_key = jQuery(this).parents('.wams-admin-notice').data('key');

		wp.ajax.send('wams_dismiss_notice', {
			data: {
				key: notice_key,
				nonce: wams_admin_scripts.nonce
			},
			success: function () {
				return true;
			},
			error: function () {
				// On error make the force notice's dismiss via action link.
				let href_index;
				if (window.location.href.indexOf('?') > -1) {
					href_index = window.location.href + '&';
				} else {
					href_index = window.location.href + '?';
				}
				window.location.href = href_index + 'wams_dismiss_notice=' + notice_key + '&wams_admin_nonce=' + wams_admin_scripts.nonce;

				return false;
			}
		});
	});
});