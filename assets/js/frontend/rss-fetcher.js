jQuery(document).ready(function () {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "rss_fetcher";

    jQuery("#fetch-rss-feed").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            html: '<i class="fas fa-rss fa-2x m-2"></i> Please wait while Fetching RSS Feed',
            allowOutsideClick: true,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        jQuery('#fetched-links').html('');

        var param = "start_rss_fetch";
        jQuery.ajax({
            type: "POST",
            dataType: "html",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            complete: function (response) {
                // console.log(response);
                Swal.close();
                output = response.responseText;
                if (response.status === 200) {
                    jQuery('#fetched-links').html(output);
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
                jQuery('#fetched-links').html(xhr.responseText);
            }
        });

    });
    jQuery('#btn-rss-backlinks').on("click", function () {
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
    jQuery('#gf-fetch-backlinks').on("click", function (e) {
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
    jQuery('#gf-fetch').on("click", function (e) {
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
}); // End of Fetch URL Data