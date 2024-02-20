jQuery(document).ready(function () {
    var ajaxurl = wams_admin_scripts.ajaxurl;
    var nonce = wams_admin_scripts.nonce
    var action = "db_cleanup_ajax_request";

    function showSpinner() {
        jQuery('#loadingSpinner').removeClass('d-none').addClass('d-flex');
    }

    function hideSpinner() {
        jQuery('#loadingSpinner').removeClass('d-flex').addClass('d-none');
    }

    jQuery("#test").on("click", function (e) {
        let progress = 0;

        // Show SweetAlert2 modal with progress bar
        Swal.fire({
            title: 'Processing...',
            html: '<div class="progress"><div class="progress-bar" role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div>',
            showConfirmButton: false,
            showCancelButton: false,
            willOpen: () => {
                const progressBar = Swal.getHtmlContainer().querySelector('.progress-bar');
                const updateProgressBar = () => {
                    progressBar.style.width = progress + '%';
                    progressBar.setAttribute('aria-valuenow', progress);
                };

                const timer = setInterval(() => {
                    progress += 5;
                    updateProgressBar();

                    if (progress >= 100) {
                        clearInterval(timer);
                        Swal.close();
                    }
                }, 500);
            }
        });


    });
    jQuery("#refresh-workflow-count").on("click", function (e) {
        var param = "get_workflow_count";
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
                if (response.status === 200) {
                    jQuery('#workflow-count').html(output);
                }
            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }
        });
    });
    jQuery(".get-entry-breakdown").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            text: 'Please wait while fetching data from the Database...',
            allowOutsideClick: false,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        var form_id = jQuery(this).attr("data-form-id");
        var param = "get_form_breakdown";
        jQuery("#breakdown-row-" + form_id).removeClass('d-none');
        jQuery.ajax({
            type: "POST",
            dataType: "html",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                form_id: form_id,
                nonce: nonce
            },
            beforeSend: function () {
                // Show spinner before sending the request
                jQuery('#output-data').html('');
            },
            complete: function (response) {
                jQuery("#loading-breakdown-" + form_id).addClass('d-none');
                // console.log(response);
                output = response.responseText;
                if (response.status === 200) {
                    jQuery('#breakdown-' + form_id).html(output);
                    Swal.close();
                    attachEventHandlers();
                } else {
                    Swal.close();
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    })
                }
            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }
        });

    });

    function attachEventHandlers() {
        jQuery(".cancel_workflow").on("click", function (e) {
            var form_id = jQuery(this).attr("data-form_id");
            var year = jQuery(this).attr("data-year");
            var param = "cancel_workflow";
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: action,
                    param: param,
                    form_id: form_id,
                    year: year,
                    nonce: nonce
                },
                beforeSend: function () {
                    Swal.showLoading();
                },
                complete: function (response) {
                    console.log(response);
                    output = response.responseJSON;
                    m = output.message;
                    if (response.status === 200) {
                        icon = output.status ? 'success' : 'warning';
                        Swal.fire({
                            icon: icon,
                            title: m[0] ?? 'Workflow Canceled',
                            html: m[1] ?? ''
                        })
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        })
                    }
                },
                error: function (xhr, status, error) {
                    // Handle errors
                    console.error(status + ': ' + error);
                }
            });

        });
        jQuery(".archive_entries").on("click", function (e) {
            var form_id = jQuery(this).attr("data-form_id");
            var year = jQuery(this).attr("data-year");
            var param = "archive_entries";
            jQuery.ajax({
                type: "POST",
                dataType: "json",
                url: ajaxurl,
                data: {
                    action: action,
                    param: param,
                    form_id: form_id,
                    year: year,
                    nonce: nonce
                },
                beforeSend: function () {
                    Swal.fire({
                        title: 'Archiving...',
                        text: 'Please wait while sending entries to Archive',
                        allowOutsideClick: false,
                        showCancelButton: false,
                        showConfirmButton: false,
                        willOpen: () => {
                            Swal.showLoading();
                        }
                    });
                },
                complete: function (response) {
                    // console.log(response);
                    output = response.responseJSON;
                    m = output.message;
                    if (response.status === 200) {
                        icon = output.status ? 'success' : 'warning';
                        Swal.fire({
                            icon: icon,
                            title: m[0] ?? 'archived',
                            html: m[1] ?? ''
                        })
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Something went wrong!',
                        })
                    }
                },
                error: function (xhr, status, error) {
                    // Handle errors
                    console.error(status + ': ' + error);
                }
            });

        });
    }
});