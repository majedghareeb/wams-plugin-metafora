jQuery(document).ready(function ($) {
	var ajaxurl = wams_frontend_scripts.ajaxurl;
	var nonce = wams_frontend_scripts.nonce
	var action = "wams_frontend";


	jQuery('.gv-table-view').addClass('table table-bordered table-striped table-responsive');

	jQuery("#stop-import").on("click", function (e) {
		// console.log(timer);
		clearTimeout(timer);
	});

	jQuery("#entry-doc-downloader").on("click", function (e) {
		e.preventDefault();
		var entry_id = $(this).attr('data-entry-id');
		console.log(entry_id);
		$.ajax({
			url: ajaxurl, // Replace with your endpoint URL
			type: "POST",
			data: {
				action: 'doc_downloader_request',
				entry_id: entry_id,
				nonce: nonce
			},
			success: function (response) {
				var blob = new Blob([response], {
					type: 'application/vnd.ms-word'
				});
				var link = document.createElement('a');
				link.href = URL.createObjectURL(blob);
				link.download = entry_id + '.doc'; // Customize filename
				link.click();
			},
			error: function (jqXHR, textStatus, errorThrown) {
				// Handle errors, e.g., display error message
				console.error(textStatus + " " + errorThrown);
			}
		});
	});

	jQuery("#import-start").on("click", function (e) {
		// var entry_id = jQuery("#entry_id").val();
		jQuery('#messages-list').html('');

		startImport(0, 1);

		function startImport(i, max) {
			console.log(i + ' : ' + max);

			if (i >= max) {
				// End of loop
				jQuery('#details').html('Completed!');
				return;
			}
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				url: ajaxurl,
				data: {
					action: 'vendors_importer_frontend_ajax_request',
					param: 'start_import',
					nonce: nonce
				},
				complete: function (response) {
					console.log(response);
					output = response.responseJSON;
					current = output.current;
					total = output.total;
					message = JSON.parse(output.message);
					console.log(output);
					if (response.status === 200) {
						jQuery('#details').html(parseInt(100 * current / total) + ' % : Current row : ' + current + ' / ' + total);
						jQuery('#messages-list').html('');
						for (var i = 0; i < message.length; i++) {
							jQuery('#messages-list').append('<li>' + message[i] + '</li>');
						}
						jQuery("#progressor").css("width", (100 * current / total) + "%");

						// jQuery('#entry-messages').html('');
						timer = setTimeout(startImport, 1000, current, total);
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: 'Something went wrong!',
						})
					}
				},

				error: function (xhr, status, error) {
					// console.log('Error:', xhr.responseText);
					jQuery('#messages-list').html(xhr.responseText);
				}
			});
		}

	});


	jQuery('.gv-table-view-content').addClass('table table-striped table-bordered');
	jQuery('.gv-table-view-content table').addClass('table');
	jQuery('.gv-table-view-content font').removeAttr("style");
	jQuery('#publishing-action > *').removeAttr("class");
	jQuery('.gpnf-add-entry').addClass('btn btn-primary');
	jQuery('.bulkactions').hide();


	/*
	 * Porgress bar
	 */
	jQuery("#btn-process").on("click", function () {
		jQuery('#btn-process').attr("disabled", true);
		var es;
		startTask();

		function startTask() {
			var location = jQuery('#btn-process').attr('location');
			//var month = jQuery('#btn-process').attr('month');
			es = new EventSource(location);
			//a message is received
			es.addEventListener('message', function (e) {
				var result = JSON.parse(e.data);
				//console.log(result);
				if (e.lastEventId == '999') {
					jQuery('#status').html('Completed 100%');
					jQuery('#btn-process').attr("disabled", false);
					es.close();
				} else {
					jQuery("#progressor").css("width", result.progress + "%");
					jQuery("#percentage").html(result.progress + '%');
					jQuery('#status').html(result.message);
				}
			});
			es.addEventListener('error', function (e) {
				jQuery('#status').html('Error occurred');
				es.close();
			});

		}

	}); // end of Porgress bar
	/*
	 * Fetch URL Data
	 */
	jQuery(document).on("click", "#btn-fetch-url", function () {
		var ajaxurl = wams.ajaxurl;
		var nonce = jQuery(this).attr("data-nonce");
		var action = "public_ajax_request";
		var url_text = jQuery('#url').val();
		if (isValidURL(url_text)) {
			jQuery.ajax({
				type: "POST",
				dataType: "json",
				url: ajaxurl,
				data: {
					action: action,
					param: "fetch_url_request",
					url_text: url_text,
					nonce: nonce
				},
				complete: function (response) {
					// console.log(response);
					if (response.status === 200) {

						json = JSON.parse(response.responseJSON.message);
						let text = "";
						for (var i = 0; i < json.length; i++) {
							for (var key in json[i]) {
								// console.log(json[i][key]);
								text += json[i][key] + '<br>';
								// for (var j = 0; j < json[i][key].length; j++) {
								// 	//console.log(json[i])
								// }
							}
						}
						jQuery("#html-content").html(text);
					} else {
						Swal.fire({
							icon: 'error',
							title: 'Oops...',
							text: response.responseText,
						})
					}
				}
			});

			//}

		} else {
			Swal.fire({
				icon: 'error',
				title: 'Not A Vaild URL',
				text: 'Please write a valid URL',

			});
		}
	}); // End of Fetch URL Data
});

function isValidURL(string) {
	var res = string.match(/^(?:(?:(?:https?|ftp):)?\/\/)(?:\S+(?::\S*)?@)?(?:(?!(?:10|127)(?:\.\d{1,3}){3})(?!(?:169\.254|192\.168)(?:\.\d{1,3}){2})(?!172\.(?:1[6-9]|2\d|3[0-1])(?:\.\d{1,3}){2})(?:[1-9]\d?|1\d\d|2[01]\d|22[0-3])(?:\.(?:1?\d{1,2}|2[0-4]\d|25[0-5])){2}(?:\.(?:[1-9]\d?|1\d\d|2[0-4]\d|25[0-4]))|(?:(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)(?:\.(?:[a-z\u00a1-\uffff0-9]-*)*[a-z\u00a1-\uffff0-9]+)*(?:\.(?:[a-z\u00a1-\uffff]{2,})).?)(?::\d{2,5})?(?:[/?#]\S*)?$/i);
	return (res !== null)
};