/*
 Plugin Name: WAMS
 Description: Frontend scripts
 Version:     1.0.0
 Author:      WAMS
 Author URI:  http://www.rakami.net
 */

if (typeof (window.WAMS) !== 'object') {
	window.WAMS = {};
}

WAMS.dropdown = {
	/**
	 * Hide the menu
	 * @param   {object}    menu
	 * @returns {undefined}
	 */
	hide: function (menu) {

		var $menu = jQuery(menu);
		$menu.parents('div').find('a').removeClass('active');
		$menu.hide();

	},
	/**
	 * Hide all menus
	 * @returns {undefined}
	 */
	hideAll: function () {

		var $menu = jQuery('.wams-dropdown');
		$menu.parents('div').find('a').removeClass('active');
		$menu.hide();

	},
	/**
	 * Update the menu position
	 * @param   {object}    menu
	 * @returns {undefined}
	 */
	setPosition: function (menu) {

		var $menu = jQuery(menu),
			menu_width = 200;

		var direction = jQuery('html').attr('dir'),
			element = $menu.attr('data-element'),
			position = $menu.attr('data-position'),
			trigger = $menu.attr('data-trigger');

		var $element = element && jQuery(element).length ? jQuery(element) : ($menu.siblings('a').length ? $menu.siblings('a').first() : $menu.parent());
		$element.addClass('wams-trigger-menu-on-' + trigger);

		var gap_right = 0,
			left_p = ($element.outerWidth() - menu_width) / 2,
			top_p = $element.outerHeight(),
			coord = $element.offset();

		// profile photo
		if ($element.is('.wams-profile-photo')) {
			var $imgBox = $element.find('.wams-profile-photo-img');
			if ($element.closest('div.uimob500').length) {
				top_p = $element.outerHeight() - $imgBox.outerHeight() / 4;
			} else {
				left_p = ($imgBox.outerWidth() - menu_width) / 2;
				top_p = $imgBox.outerHeight() / 4;
			}
		}

		// cover photo
		if ($element.is('.wams-cover')) {
			var $imgBox = $element.find('.wams-cover-e');
			if ($element.closest('div.uimob500').length) {
				left_p = ($imgBox.outerWidth() - menu_width) / 2;
				top_p = $imgBox.outerHeight() / 2 + 24;
			} else {
				left_p = ($imgBox.outerWidth() - menu_width) / 2;
				top_p = $imgBox.outerHeight() / 2 + 46;
			}
		}

		// position
		if (position === 'lc' && direction === 'rtl') {
			position = 'rc';
		}
		if ($element.outerWidth() < menu_width) {
			if (direction === 'rtl' && coord.left < menu_width * 0.5) {
				position = 'rc';
			} else if (direction !== 'rtl' && (window.innerWidth - coord.left - $element.outerWidth()) < menu_width * 0.5) {
				position = 'lc';
			}
		}

		switch (position) {
			case 'lc':

				gap_right = $element.width() + 17;
				$menu.css({
					'top': 0,
					'width': menu_width,
					'left': 'auto',
					'right': gap_right + 'px',
					'text-align': 'center'
				});

				$menu.find('.wams-dropdown-arr').css({
					'top': '4px',
					'left': 'auto',
					'right': '-17px'
				}).find('i').removeClass().addClass('wams-icon-arrow-right-b');
				break;

			case 'rc':

				gap_right = $element.width() + 25;
				$menu.css({
					'top': 0,
					'width': menu_width,
					'left': gap_right + 'px',
					'right': 'auto',
					'text-align': 'center'
				});

				$menu.find('.wams-dropdown-arr').css({
					'top': '4px',
					'left': '-17px',
					'right': 'auto'
				}).find('i').removeClass().addClass('wams-icon-arrow-left-b');
				break;

			case 'bc':
			default:

				var top_offset = $menu.data('top-offset');
				if (typeof top_offset !== 'undefined') {
					top_p += top_offset;
				}

				$menu.css({
					'top': top_p + 6,
					'width': menu_width,
					'left': left_p,
					'right': 'auto',
					'text-align': 'center'
				});

				$menu.find('.wams-dropdown-arr').css({
					'top': '-17px',
					'left': ($menu.width() / 2) - 12,
					'right': 'auto'
				}).find('i').removeClass().addClass('wams-icon-arrow-up-b');
				break;
		}
	},
	/**
	 * Show the menu
	 * @param   {object}    menu
	 * @returns {undefined}
	 */
	show: function (menu) {

		var $menu = jQuery(menu);
		WAMS.dropdown.hideAll();
		WAMS.dropdown.setPosition($menu);
		$menu.show();

	}
};



function initImageUpload_WAMS(trigger) {

	if (trigger.data('upload_help_text')) {
		upload_help_text = '<span class="help">' + trigger.data('upload_help_text') + '</span>';
	} else {
		upload_help_text = '';
	}

	if (trigger.data('icon')) {
		icon = '<span class="icon"><i class="' + trigger.data('icon') + '"></i></span>';
	} else {
		icon = '';
	}

	if (trigger.data('upload_text')) {
		upload_text = '<span class="str">' + trigger.data('upload_text') + '</span>';
	} else {
		upload_text = '';
	}

	var user_id = 0;

	if (jQuery('#wams_upload_single:visible').data('user_id')) {
		user_id = jQuery('#wams_upload_single:visible').data('user_id');
	}

	trigger.uploadFile({
		url: wp.ajax.settings.url,
		method: "POST",
		multiple: false,
		formData: {
			action: 'wams_imageupload',
			key: trigger.data('key'),
			set_id: trigger.data('set_id'),
			set_mode: trigger.data('set_mode'),
			_wpnonce: trigger.data('nonce'),
			timestamp: trigger.data('timestamp'),
			user_id: user_id
		},
		fileName: trigger.data('key'),
		allowedTypes: trigger.data('allowed_types'),
		maxFileSize: trigger.data('max_size'),
		dragDropStr: icon + upload_text + upload_help_text,
		sizeErrorStr: trigger.data('max_size_error'),
		extErrorStr: trigger.data('extension_error'),
		maxFileCountErrorStr: trigger.data('max_files_error'),
		maxFileCount: 1,
		showDelete: false,
		showAbort: false,
		showDone: false,
		showFileCounter: false,
		showStatusAfterSuccess: true,
		returnType: 'json',
		onSubmit: function (files) {

			trigger.parents('.wams-modal-body').find('.wams-error-block').remove();

		},
		onSuccess: function (files, response, xhr) {

			trigger.selectedFiles = 0;

			if (response.success && response.success == false || typeof response.data.error !== 'undefined') {

				trigger.parents('.wams-modal-body').append('<div class="wams-error-block">' + response.data.error + '</div>');
				trigger.parents('.wams-modal-body').find('.upload-statusbar').hide(0);
				wams_modal_responsive();

			} else {

				jQuery.each(response.data, function (i, d) {

					var img_id = trigger.parents('.wams-modal-body').find('.wams-single-image-preview img');
					var img_id_h = trigger.parents('.wams-modal-body').find('.wams-single-image-preview');

					var cache_ts = new Date();

					img_id.attr("src", d.url + "?" + cache_ts.getTime());
					img_id.data("file", d.file);

					img_id.on('load', function () {

						trigger.parents('.wams-modal-body').find('.wams-modal-btn.wams-finish-upload.disabled').removeClass('disabled');
						trigger.parents('.wams-modal-body').find('.ajax-upload-dragdrop,.upload-statusbar').hide(0);
						img_id_h.show(0);
						wams_modal_responsive();

					});

				});

			}

		},
		onError: function (e) {
			console.log(e);
		}
	});

}

function initFileUpload_WAMS(trigger) {

	if (trigger.data('upload_help_text')) {
		upload_help_text = '<span class="help">' + trigger.data('upload_help_text') + '</span>';
	} else {
		upload_help_text = '';
	}

	if (trigger.data('icon')) {
		icon = '<span class="icon"><i class="' + trigger.data('icon') + '"></i></span>';
	} else {
		icon = '';
	}

	if (trigger.data('upload_text')) {
		upload_text = '<span class="str">' + trigger.data('upload_text') + '</span>';
	} else {
		upload_text = '';
	}

	if (jQuery('#wams_upload_single:visible').data('user_id')) {
		user_id = jQuery('#wams_upload_single:visible').data('user_id');
	}

	trigger.uploadFile({
		url: wp.ajax.settings.url,
		method: "POST",
		multiple: false,
		formData: {
			action: 'wams_fileupload',
			key: trigger.data('key'),
			set_id: trigger.data('set_id'),
			user_id: trigger.data('user_id'),
			set_mode: trigger.data('set_mode'),
			_wpnonce: trigger.data('nonce'),
			timestamp: trigger.data('timestamp')
		},
		fileName: trigger.data('key'),
		allowedTypes: trigger.data('allowed_types'),
		maxFileSize: trigger.data('max_size'),
		dragDropStr: icon + upload_text + upload_help_text,
		sizeErrorStr: trigger.data('max_size_error'),
		extErrorStr: trigger.data('extension_error'),
		maxFileCountErrorStr: trigger.data('max_files_error'),
		maxFileCount: 1,
		showDelete: false,
		showAbort: false,
		showDone: false,
		showFileCounter: false,
		showStatusAfterSuccess: true,
		onSubmit: function (files) {

			trigger.parents('.wams-modal-body').find('.wams-error-block').remove();

		},
		onSuccess: function (files, response, xhr) {

			trigger.selectedFiles = 0;

			if (response.success && response.success == false || typeof response.data.error !== 'undefined') {

				trigger.parents('.wams-modal-body').append('<div class="wams-error-block">' + response.data.error + '</div>');
				trigger.parents('.wams-modal-body').find('.upload-statusbar').hide(0);

				setTimeout(function () {
					wams_modal_responsive();
				}, 1000);

			} else {

				jQuery.each(response.data, function (key, value) {

					trigger.parents('.wams-modal-body').find('.wams-modal-btn.wams-finish-upload.disabled').removeClass('disabled');
					trigger.parents('.wams-modal-body').find('.ajax-upload-dragdrop,.upload-statusbar').hide(0);
					trigger.parents('.wams-modal-body').find('.wams-single-file-preview').show(0);

					if (key == 'icon') {

						trigger.parents('.wams-modal-body').find('.wams-single-fileinfo i').removeClass().addClass(value);

					} else if (key == 'icon_bg') {

						trigger.parents('.wams-modal-body').find('.wams-single-fileinfo span.icon').css({
							'background-color': value
						});

					} else if (key == 'filename') {

						trigger.parents('.wams-modal-body').find('.wams-single-fileinfo a').attr('data-file', value);

					} else if (key == 'original_name') {

						trigger.parents('.wams-modal-body').find('.wams-single-fileinfo a').attr('data-orignal-name', value);
						trigger.parents('.wams-modal-body').find('.wams-single-fileinfo span.filename').html(value);

					} else if (key == 'url') {

						trigger.parents('.wams-modal-body').find('.wams-single-fileinfo a').attr('href', value);

					}

				});

				setTimeout(function () {
					wams_modal_responsive();
				}, 1000);

			}

		},
		onError: function (e) {
			console.log(e);
		}
	});

}

function wams_new_modal(id, size, isPhoto, source) {
	var modalOverlay = jQuery('.wams-modal-overlay');
	if (modalOverlay.length !== 0) {
		modalOverlay.hide();
		modalOverlay.next('.wams-modal').hide();
	}

	WAMS.common.tipsy.hide();

	WAMS.dropdown.hideAll();

	jQuery('body,html,textarea').css('overflow', 'hidden');

	jQuery(document).bind("touchmove", function (e) {
		e.preventDefault();
	});
	jQuery('.wams-modal').on('touchmove', function (e) {
		e.stopPropagation();
	});

	var $tpl = jQuery('<div class="wams-modal-overlay"></div><div class="wams-modal"></div>');
	var $modal = $tpl.filter('.wams-modal');
	$modal.append(jQuery('#' + id));

	jQuery('body').append($tpl);

	if (isPhoto) {
		var photo_ = jQuery('<img src="' + source + '" />'),
			photo_maxw = jQuery(window).width() - 60,
			photo_maxh = jQuery(window).height() - jQuery(window).height() * 0.25;

		photo_.on('load', function () {
			$modal.find('.wams-modal-photo').html(photo_);

			$modal.addClass('is-photo').css({
				'width': photo_.width(),
				'margin-left': '-' + photo_.width() / 2 + 'px'
			}).show().children().show();

			photo_.css({
				'opacity': 0,
				'max-width': photo_maxw,
				'max-height': photo_maxh
			}).animate({
				'opacity': 1
			}, 1000);

			wams_modal_responsive();
		});
	} else {

		$modal.addClass('no-photo').show().children().show();

		wams_modal_size(size);

		initImageUpload_WAMS(jQuery('.wams-modal:visible .wams-single-image-upload'));
		initFileUpload_WAMS(jQuery('.wams-modal:visible .wams-single-file-upload'));

		wams_modal_responsive();

	}

}

function wams_modal_responsive() {
	var w = window.innerWidth ||
		document.documentElement.clientWidth ||
		document.body.clientWidth;

	var h = window.innerHeight ||
		document.documentElement.clientHeight ||
		document.body.clientHeight;

	var modal = jQuery('.wams-modal:visible').not('.wams-modal-hidden');
	var photo_modal = modal.find('.wams-modal-body.photo:visible');

	if (!photo_modal.length && !modal.length) {
		return;
	}

	let half_gap = (h - modal.innerHeight()) / 2 + 'px';

	modal.removeClass('uimob340').removeClass('uimob500');

	if (photo_modal.length) {
		var photo_ = jQuery('.wams-modal-photo img');
		var photo_maxw = w - 60;
		var photo_maxh = h - (h * 0.25);

		photo_.css({
			'opacity': 0
		});
		photo_.css({
			'max-width': photo_maxw
		});
		photo_.css({
			'max-height': photo_maxh
		});

		modal.css({
			'width': photo_.width(),
			'margin-left': '-' + photo_.width() / 2 + 'px'
		});

		photo_.animate({
			'opacity': 1
		}, 1000);

		modal.animate({
			'bottom': half_gap
		}, 300);

	} else if (modal.length) {
		if (w <= 340) {
			modal.addClass('uimob340');
		} else if (w <= 500) {
			modal.addClass('uimob500');
		}
		WAMS.frontend.cropper.init();
		if (w <= 500) {
			modal.animate({
				'bottom': 0
			}, 300);
		} else {
			modal.animate({
				'bottom': half_gap
			}, 300);
		}
	}
}

function wams_remove_modal() {
	wp.hooks.doAction('wams_remove_modal');

	jQuery('body,html,textarea').css("overflow", "auto");

	jQuery(document).unbind('touchmove');

	jQuery('body > .wams-modal div[id^="wams_"]').hide().appendTo('body');
	jQuery('body > .wams-modal, body > .wams-modal-overlay').remove();

}

function wams_modal_size(aclass) {
	jQuery('.wams-modal:visible').not('.wams-modal-hidden').addClass(aclass);
}

function prepare_Modal() {
	if (jQuery('.wams-popup-overlay').length == 0) {
		jQuery('body').append('<div class="wams-popup-overlay"></div>');
		jQuery('body').append('<div class="wams-popup"></div>');
		jQuery('.wams-popup').addClass('loading');
		jQuery("body,html").css({
			overflow: 'hidden'
		});
	}
}

function remove_Modal() {
	if (jQuery('.wams-popup-overlay').length) {
		wp.hooks.doAction('wams_before_modal_removed', jQuery('.wams-popup'));

		WAMS.common.tipsy.hide();
		jQuery('.wams-popup').empty().remove();
		jQuery('.wams-popup-overlay').empty().remove();
		jQuery("body,html").css({
			overflow: 'auto'
		});
	}
}

function show_Modal(contents) {
	if (jQuery('.wams-popup-overlay').length) {
		jQuery('.wams-popup').removeClass('loading').html(contents);
		WAMS.common.tipsy.init();
	}
}

function responsive_Modal() {
	if (jQuery('.wams-popup-overlay').length) {

		ag_height = jQuery(window).height() - jQuery('.wams-popup .wams-popup-header').outerHeight() - jQuery('.wams-popup .wams-popup-footer').outerHeight() - 80;
		if (ag_height > 350) {
			ag_height = 350;
		}

		if (jQuery('.wams-popup-autogrow:visible').length) {

			jQuery('.wams-popup-autogrow:visible').css({
				'height': ag_height + 'px'
			});

		} else if (jQuery('.wams-popup-autogrow2:visible').length) {

			jQuery('.wams-popup-autogrow2:visible').css({
				'max-height': ag_height + 'px'
			});

		}
	}
}

function wams_reset_field(dOm) {
	//console.log(dOm);
	jQuery(dOm)
		.find('div.wams-field-area')
		.find('input,textarea,select')
		.not(':button, :submit, :reset, :hidden')
		.val('')
		.prop('checked', false)
		.prop('selected', false);
}

jQuery(function () {

	// Submit search form on keypress 'Enter'
	jQuery(".wams-search form *").on('keypress', function (e) {
		if (e.which == 13) {
			jQuery('.wams-search form').trigger('submit');
			return false;
		}
	});

	if (jQuery('input[data-key=user_password],input[data-key=confirm_user_password]').length == 2) {
		WAMS_check_password_matched();
	}

});