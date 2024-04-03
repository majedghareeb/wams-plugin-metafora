jQuery(document).ready(function () {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "telegram_ajax_request";

    /*
     * Activate Telegram account
     */
    jQuery(document).on("click", "#next", function () {
        jQuery('#send-code-form').removeClass('d-none');
    });

    jQuery(document).on("click", "#btn-send-chat-id", function () {
        jQuery('#btn-send-chat-id').prop('disabled', true);
        sendCode();
        jQuery('#activate-form').removeClass('d-none');
        setTimeout(resendCode, 5000);


        function sendCode() {
            //
            // Send Code to Telegram
            var param = 'sendcode';
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
                    Swal.fire({
                        icon: response.responseJSON.status,
                        text: response.responseJSON.message,
                    })

                }
            });
        }



    });

    function resendCode() {
        jQuery('#btn-send-chat-id').html('Resend Code');
        jQuery('#btn-send-chat-id').prop('disabled', false);
        //sendCode();
    }
    /**
     * AJAX - Save Chat ID
     */

    jQuery("#btn-save-chat-id").on("click", function (e) {

        var user_id = jQuery(this).data("user_id");
        var user_name = jQuery(this).data("user_name");
        var chat_id = jQuery('#chat-id').val();
        var param = "save_telegram_chat_id";
        if (!isNaN(chat_id)) {
            Swal.fire({
                title: 'New Code is: ' + chat_id,
                html: user_name + ' <br> You are about to activate your telegram ID <br> User ID: ' + user_id + chat_id,
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Ok",
                cancelButtonText: "Cancel"
            }).then(function (result) {
                if (result.value) {
                    jQuery.ajax({
                        type: "POST",
                        dataType: "JSON",
                        url: ajaxurl,
                        data: {
                            action: action,
                            param: param,
                            user_id: user_id,
                            chat_id: chat_id,
                            nonce: nonce
                        },
                        complete: function (response) {
                            data = response.responseJSON;
                            Swal.fire({
                                icon: data.status,
                                text: data.message,
                            })
                            setTimeout(function () {
                                location.reload();
                            }, 3000);
                        }
                    });
                }

            });
        } else {
            Swal.fire('Please Enter a Vaild Code');
        }
        // if (!isNaN(chat_id)) {

        // }
        // else {
        // 	Swal.fire('Please Enter a Vaild Code');
        // }

    });

    jQuery("#sendTestMessage").on("click", function (e) {
        //alert(ajaxurl);
        var param = "send_test_messgae";
        var testMessage = jQuery("#testMessage").val();
        var chat_id = jQuery(this).attr("chat-id");
        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                chat_id: chat_id,
                test_message: testMessage,
                nonce: nonce
            },
            complete: function (response) {
                Swal.fire({
                    icon: response.responseJSON.status,
                    text: response.responseJSON.message,
                })
            }
        });
    });
});