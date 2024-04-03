jQuery(document).ready(function ($) {
    var ajaxurl = wams_admin_scripts.ajaxurl
    var nonce = wams_admin_scripts.nonce
    var action = "telegram_notifications_admin_ajax_request";

    jQuery('#notifications-test').on('submit', function (event) {
        event.preventDefault(); // Prevent default form submission

        // Get the number input value
        var message = jQuery('#message').html();
        var user_id = jQuery('#user-id').val();
        var channel_id = jQuery('#channel-id').val();

        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                param: "send_test_notification",
                message: message,
                user_id: user_id,
                channel_id: channel_id,
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