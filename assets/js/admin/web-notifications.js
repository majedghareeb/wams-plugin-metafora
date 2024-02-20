jQuery(document).ready(function ($) {
    var ajaxurl = wams_admin_scripts.ajaxurl
    var nonce = wams_admin_scripts.nonce
    var action = "web_notifications_admin_ajax_request";

    jQuery("#install-db-table").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            text: 'Please wait while Installing Database Table',
            allowOutsideClick: false,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        var param = "install_db_table"
        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {
                // Show spinner before sending the request

            },
            complete: function (response) {
                Swal.close();
                Swal.fire({
                    icon: response.responseJSON[0],
                    text: response.responseJSON[1],
                })
                console.log(response);

            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }
        });
    });
    jQuery("#install-page").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            text: 'Please wait while Creating Page',
            allowOutsideClick: false,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        var param = "install_page"
        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {
                // Show spinner before sending the request

            },
            complete: function (response) {
                Swal.close();
                Swal.fire({
                    icon: response.responseJSON[0],
                    text: response.responseJSON[1],
                })
                console.log(response);

            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }
        });
    });
    jQuery("#send-test-notification").on("click", function (e) {
        Swal.fire({
            title: 'Under Process',
            text: 'Please wait while Sending test notifcation',
            allowOutsideClick: false,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        var param = "send_test_notification"
        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {
                // Show spinner before sending the request

            },
            complete: function (response) {
                console.log(response);
                Swal.close();
                Swal.fire({
                    icon: response.responseJSON[0],
                    text: response.responseJSON[1],
                })

            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }
        });
    });
    jQuery('#notifications-settings').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Get the number input value
        var param = "save_notifications_settings";
        var enabled = jQuery('#enabled').prop('checked');
        var interval = jQuery('#check-interval').val();
        var sound = jQuery('#sound').prop('checked');
        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                enabled: enabled,
                interval: interval,
                sound: sound,
                nonce: nonce
            },
            beforeSend: function () {
                // Show spinner before sending the request

            },
            complete: function (response) {
                console.log(response);
                Swal.close();
                Swal.fire({
                    icon: response.responseJSON[0],
                    text: response.responseJSON[1],
                })

            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }
        });

    });
    jQuery('#notifications-test').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Get the number input value
        var param = "send_notification_test";
        var message = jQuery('#message').val();
        var user_id = jQuery('#user-id').val();

        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                message: message,
                user_id: user_id,
                nonce: nonce
            },
            beforeSend: function () {
                // Show spinner before sending the request

            },
            complete: function (response) {
                console.log(response);
                Swal.close();
                Swal.fire({
                    icon: response.responseJSON[0],
                    text: response.responseJSON[1],
                })

            },
            error: function (xhr, status, error) {
                // Handle errors
                console.error(status + ': ' + error);
            }
        });

    });

});