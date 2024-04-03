jQuery(document).ready(function ($) {
	var ajaxurl = wams_admin_scripts.ajaxurl;
	var nonce = wams_admin_scripts.nonce;
	var action = "admin_ajax_request";
	var timer = null;
	var activeTabIndex = localStorage.getItem('activeTabIndex');
	jQuery('#tabs').tabs({
		active: activeTabIndex,
		activate: function (event, ui) {
			// Save the active tab index to localStorage
			localStorage.setItem('activeTabIndex', ui.newTab.index());
		}
	});
	jQuery("#accordion").accordion({
		heightStyle: "content"
	});
	jQuery("#test-google-analytics").on("click", function (e) {
		var account = jQuery('#default_profile').val()
		jQuery.ajax({
			url: ajaxurl, // Replace with your endpoint URL
			type: "POST",
			dataType: "json",
			data: {
				action: action,
				param: 'test_google_analytics',
				account: account,
				nonce: nonce
			},
			success: function (response) {
				console.log(response);
				// Handle successful response, e.g., display success message
				// var result = response.responseJSON.account
				// $('#test-result').html(response.responseJSON.result);
				// Swal.fire({
				// 	icon: response.data.status,
				// 	title: response.data.message,

				// })
			},
			error: function (jqXHR, textStatus, errorThrown) {
				// Handle errors, e.g., display error message
				console.error(textStatus + " " + errorThrown);
			}
		});
	});
	/**
	 * Just to test AJAX call handler
	 */
	jQuery("#test-btn").on("click", function (e) {
		console.log(ajaxurl);
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			data: {
				action: 'wams_test_ajax',
				nonce: wams_admin_scripts.nonce
			},

			success: function (response) {
				console.log(response.message);
				jQuery('#wams-admin-test-ajax').html(response.message);
			},
			error: function (data) {

			}
		});
	});
	jQuery(".clear-option").on("click", function (e) {
		var param = 'delete_option';
		var option_id = jQuery(this).data('option-id');
		jQuery.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: "json",
			data: {
				action: action,
				param: param,
				option_id: option_id,
				nonce: nonce
			},

			success: function (response) {
				Swal.fire({
					icon: response.responseJSON[0],
					text: response.responseJSON[1],
				})
				console.log(response);

			},
			error: function (data) {

			}
		});
	});
	/**
	 * Handle Admin Panel RSS Feed Tester
	 */
	$(".rss-fetcher-test").on("click", function (e) {
		e.preventDefault();
		var field_id = $(this).data('field_id');
		var rss_url = $(this).prev('input').html();
		console.log(rss_url);
		alert(rss_url);
	});



});

jQuery(document).ready(function ($) {
	$('.tab-content').hide();
	$('.tab-content:first').show();
	$('.nav-tab-wrapper a:first').addClass('nav-tab-active');

	$(".nav-tab-wrapper").on("click", ".nav-tab", function (e) {
		e.preventDefault();
		var link = $(this),
			content = $(link.attr('href'));
		console.log(link);

		$(".nav-tab").removeClass("nav-tab-active");
		$(".tab-content").hide();
		$(this).addClass("nav-tab-active");
		$($(this).attr("href")).show();
	});


});
/**
 * Handle the repeater field
 */


jQuery(document).ready(function ($) {
	// Add Item
	$('#add-item').click(function (event) {
		event.preventDefault(); // Prevent form submission

		var newItem = $('#new-item').val();

		if (newItem) {
			var listRow = $(".list-row:first").clone().removeClass('d-none');
			listRow.find('.list-item').val(newItem);
			$('#custom-list').append(listRow);
			$('#new-item').val('');
			updateHiddenField();
		}
	});

	// Delete Item
	$('#custom-list').on('click', '.delete-item', function (event) {
		event.preventDefault(); // Prevent form submission
		$(this).closest('div').remove();
		updateHiddenField();
	});

	// Update Hidden Field
	function updateHiddenField() {
		var items = [];
		console.log($('.list-item'));
		$('#custom-list .list-item').each(function () {
			if ($(this).val() == '') return;
			items.push($(this).val().trim());
			// console.log($(this).val());
		});

		// Update the hidden field
		$('#hidden-list').val(JSON.stringify(items));
	}
});

/**
 * This function updates the builder area with fields
 *
 * @returns {boolean}
 */
function wams_admin_test_ajax() {
	var form_id = jQuery('.test-btn').data('value');


	jQuery.ajax({
		url: wp.ajax.settings.url,
		type: 'POST',
		data: {
			action: 'wams_test_ajax',
			form_id: form_id,
			nonce: wams_admin_scripts.nonce
		},
		success: function (data) {
			jQuery('.um-admin-drag-ajax').html(data);
			UM.common.tipsy.hide();

			/* trigger columns at start */
			allow_update_via_col_click = false;
			jQuery('.um-admin-drag-ctrls.columns a.active').each(function () {
				jQuery(this).trigger('click');
			}).promise().done(function () {
				allow_update_via_col_click = true;
			});

		},
		error: function (data) {

		}
	});

	return false;
}