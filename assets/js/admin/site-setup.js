jQuery(document).ready(function () {
    var ajaxurl = wams_admin_scripts.ajaxurl;
    var nonce = wams_admin_scripts.nonce
    var action = "site_setup_ajax_request";
    var timer = null;

    function showSpinner() {
        jQuery('#loadingSpinner').removeClass('d-none').addClass('d-flex');
    }

    // Function to hide the spinner
    function hideSpinner() {
        jQuery('#loadingSpinner').removeClass('d-flex').addClass('d-none');
    }

    jQuery("#install-pages").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            text: 'Please wait while Installing Database Table',
            allowOutsideClick: true,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        jQuery('#messages-list').html('');
        var param = "install_pages";
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {
                jQuery('#messagesList').html('');
            },
            complete: function (response) {
                console.log(response);
                messages = response.responseJSON.data.messages;
                if (response.status === 200) {
                    Swal.close();
                    for (var i = 0; i < messages.length; i++) {
                        jQuery('#messagesList').append('<li>' + messages[i] + '</li>');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    })
                }
                hideSpinner();
            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }

        });
    });
    jQuery("#install-views").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            text: 'Please wait while Installing Database Table',
            allowOutsideClick: true,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        jQuery('#messages-list').html('');
        var param = "install_views";
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {
                jQuery('#messagesList').html('');
            },
            complete: function (response) {
                console.log(response);
                messages = response.responseJSON.data.messages;
                if (response.status === 200) {
                    Swal.close();
                    for (var i = 0; i < messages.length; i++) {
                        jQuery('#messagesList').append('<li>' + messages[i] + '</li>');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    })
                }
                hideSpinner();
            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }

        });
    });
    jQuery("#install-user-menu").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            text: 'Please wait while Installing Database Table',
            allowOutsideClick: true,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        jQuery('#messages-list').html('');
        var param = "install_user_menu";
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {
                jQuery('#messagesList').html('');
            },
            complete: function (response) {
                console.log(response);
                messages = response.responseJSON.data.messages;
                if (response.status === 200) {
                    Swal.close();
                    for (var i = 0; i < messages.length; i++) {
                        jQuery('#messagesList').append('<li>' + messages[i] + '</li>');
                    }
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: 'Something went wrong!',
                    })
                }
                hideSpinner();
            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }

        });
    });
});