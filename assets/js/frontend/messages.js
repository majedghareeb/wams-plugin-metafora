jQuery(document).ready(function () {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "messages_ajax_request";

    $("#send-message").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        var formData = jQuery('#send-message').serialize()

        $.ajax({
            url: ajaxurl, // Replace with your endpoint URL
            type: "POST",
            data: {
                action: action,
                param: 'send_message',
                formData: formData,
                nonce: nonce
            },
            success: function (response) {
                console.log(response);
                // Handle successful response, e.g., display success message
                $("#send-message").trigger("reset");
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle errors, e.g., display error message
                console.error(textStatus + " " + errorThrown);
            }
        });
    });

});