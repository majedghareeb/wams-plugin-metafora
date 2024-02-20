jQuery(document).ready(function () {
	var ajaxurl = wams_frontend_scripts.ajaxurl;
	var nonce = wams_frontend_scripts.nonce
	var data_link = jQuery(this).attr("data-link");
	var action = "wams_frontend";

	jQuery('.gv-table-view').addClass('table table-bordered table-striped table-responsive');

	jQuery("#stop-import").on("click", function (e) {
		// console.log(timer);
		clearTimeout(timer);
	});
	jQuery(".fetch-rss-feed").on("click", function (e) {
		jQuery('#loading-container').show();
		var rss_url = jQuery(this).attr('data-rss-url');
		var param = "start_rss_fetch";
		jQuery.ajax({
			type: "POST",
			dataType: "html",
			url: ajaxurl,
			data: {
				action: action,
				param: param,
				rss_url: rss_url,
				nonce: nonce
			},
			complete: function (response) {
				// console.log(response);
				output = response.responseText;
				if (response.status === 200) {
					jQuery('#loading-container').hide();
					jQuery('#posts-list').html(output);
				}
				// output = response.responseJSON;
				// current = output.current;
				// total = output.total;
				// message = JSON.parse(output.message);
				// console.log(output);
				// if (response.status === 200) {
				// 	jQuery('#details').html(parseInt(100 * current / total) + ' % : Current row : ' + current + ' / ' + total);
				// 	jQuery('#posts-list').html('');
				// 	for (var i = 0; i < message.length; i++) {
				// 		jQuery('#posts-list').append('<li>' + message[i]['title'] + '</li>');
				// 	}
				// 	jQuery("#progressor").css("width", (100 * current / total) + "%");
				// }
				else {
					Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: 'Something went wrong!',
					})
				}
			},

			error: function (xhr, status, error) {
				// console.log('Error:', xhr.responseText);
				jQuery('#posts-list').html(xhr.responseText);
			}
		});

	});
	jQuery("#import-start").on("click", function (e) {
		// var entry_id = jQuery("#entry_id").val();
		jQuery('#messages-list').html('');
		var param = "start_import";

		startImport(0, 1);


		function startImport(i, max) {
			// console.log(i + ' : ' + max);

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
					action: action,
					param: param,
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
	jQuery(document).on("click", '#test', function () {
		var action = "public_ajax_request";
		var param = "rss";
		var url_text = jQuery('#url').val();
		jQuery.ajax({
			type: "POST",
			dataType: "html",
			url: ajaxurl,
			data: {
				action: action,
				param: param,
				url_text: url_text,
				nonce: nonce
			},
			complete: function (response) {
				// console.log(response);
				jQuery("#result").html(response.responseText);
			}
		});
	});

	jQuery(document).on("click", "#tasks-refresh", function () {
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: action,
				param: "refresh_tasks"
			},
			complete: function (response) {
				console.log(response);
				if (response.status === 200) {
					location.reload();
				}

			}
		});
	});
	jQuery(document).on("click", "#test-btn", function () {
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: action,
				param: "refresh_team_tasks"
			},
			complete: function (response) {

				if (response.status === 200) {
					console.log(response.responseJSON);
				}

			}
		});
	});
	jQuery(document).on("click", "#team-tasks-refresh", function () {
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: action,
				param: "refresh_team_tasks"
			},
			complete: function (response) {
				console.log(response);
				if (response.status === 200) {
					location.reload();
				}

			}
		});
	});
	jQuery(document).on("click", "#requests-refresh", function () {
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: action,
				param: "refresh_requests"
			},
			complete: function (response) {
				console.log(response);
				if (response.status === 200) {
					location.reload();
				}

			}
		});
	});
	jQuery('.gv-table-view-content').addClass('table table-striped table-bordered');
	jQuery('.gv-table-view-content table').addClass('table');
	jQuery('.gv-table-view-content font').removeAttr("style");
	jQuery('#publishing-action > *').removeAttr("class");
	jQuery('#publishing-action > a:contains("Delete")').addClass('btn btn-danger');
	jQuery('#publishing-action > a:contains("Cancel")').addClass('btn btn-info');
	jQuery('#publishing-action > input[type="submit"]').addClass('btn btn-lg btn-success');
	jQuery('.gpnf-add-entry').addClass('btn btn-primary');
	jQuery('.gfield_list').addClass('table table-striped table-responsive ');
	jQuery(document).on("click", '#btn-rss-backlinks', function () {
		jQuery('#btn-rss-backlinks').attr("disabled", true);
		var es;
		startTask();

		function startTask() {
			var location = jQuery('#btn-rss-backlinks').attr('location');
			var rssUrl = jQuery('#btn-rss-backlinks').attr('rssUrl');
			//var month = jQuery('#btn-process').attr('month');
			es = new EventSource(location + '?url=' + rssUrl);

			//a message is received
			es.addEventListener('message', function (e) {
				var result = JSON.parse(e.data);
				//console.log(result);
				if (e.lastEventId == '999') {
					jQuery('#status').html('Completed 100%');
					jQuery('#btn-rss-backlinks').attr("disabled", false);
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

	});
	jQuery(document).on("click", '#gf-fetch-backlinks', function (e) {
		trigger(gformDeleteListItem);
		e.preventDefault();
		url_text = jQuery('#input_4_12').val();

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
						//jQuery('#tinymce').attr("data-id");
						//json = JSON.parse(response.responseJSON.message);
						jQuery('#backlinks-content').html(response.responseJSON.message);
						// let text = "";
						// for (var i = 0; i < json.length; i++) {
						// 	for (var key in json) {
						// 		//console.log(json[i][key]);
						// 		text += '<a href="' + json[i] + '">Link</a><br>';
						// 		// for (var j = 0; j < json[i][key].length; j++) {
						// 		// 	//console.log(json[i])
						// 		// }
						// 	}
						// }
						//jQuery("#backlinks-list").html(text);
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
	});
	jQuery(document).on("click", '#gf-fetch', function (e) {
		e.preventDefault();
		jQuery('.add_list_item')[0].click();
		var action2 = "public_ajax_request";
		var param = "form_trigger";
		jQuery.ajax({
			type: "POST",
			dataType: "json",

			url: ajaxurl,
			data: {
				action: action2,
				param: param,
				nonce: nonce
			},
			complete: function (response) {
				// console.log(response);
			}
		});



	});



	// test ajax
	jQuery(document).on("click", "#btn-front-end-ajax", function () {
		var input_text = jQuery('#text_1').val();
		if (isValidURL(input_text)) {
			input_response = 'ok'
		} else {
			input_response = 'not URL'
		}
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: action,
				param: "	",
				nonce: nonce
			},
			complete: function (response) {
				// console.log(response);
				//var responsehtml = response.responseJSON.message;
				if (response.status === 200) {
					//alert(responsehtml);
					Swal.fire(

						input_response,
						"success"
					)
					// setTimeout(function () {
					// 	location.reload();
					// }, 1000);

				} else {
					Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: 'Something went wrong!',

					})
				}
			}
		});
	}); // end of test Ajax


	// test ajax
	jQuery(document).on("click", "#btn-test-analytics-ajax", function () {
		var input_text = jQuery('#text_1').val();
		if (isValidURL(input_text)) {
			input_response = 'ok'
		} else {
			input_response = 'not URL'
		}
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: action,
				param: "	",
				nonce: nonce
			},
			complete: function (response) {
				// console.log(response);
				//var responsehtml = response.responseJSON.message;
				if (response.status === 200) {
					//alert(responsehtml);
					Swal.fire(

						input_response,
						"success"
					)
					// setTimeout(function () {
					// 	location.reload();
					// }, 1000);

				} else {
					Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: 'Something went wrong!',

					})
				}
			}
		});
	}); // end of test Ajax


	// GA ajax
	jQuery(document).on("click", "#btn-analytics-ajax", function () {
		var url_text = jQuery('#url').val();
		jQuery('#status-message').addClass('alert-success');
		jQuery.ajax({
			type: "POST",
			dataType: "json",
			url: ajaxurl,
			data: {
				action: action,
				param: "ga_ajax_request",
				url_text: url_text,
				nonce: nonce
			},
			complete: function (response) {
				// console.log(response);
				json = response.responseJSON;
				var data = JSON.parse(response.responseText);
				if (data.status == 1) {

					Swal.fire(
						"Successfuly",
						data.message,
						"success"
					)
				} else {
					Swal.fire({
						icon: 'error',
						title: 'Oops...',
						text: 'Something went wrong!',

					})
				}
			}
		});
	}); // end of test Ajax

	/*
	 * Porgress bar
	 */
	jQuery(document).on("click", "#btn-process", function () {
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