jQuery(document).ready(function ($) {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "wams_frontend";

    var clickedButton = '';

    // Detect a click on any of the submit buttons
    $('#page-parser input[type="submit"]').on('click', function (event) {
        // Store the value of the clicked button
        clickedButton = $(this).attr('name');
    });

    $("#page-parser").submit(function (event) {
        event.preventDefault(); // Prevent default form submission
        Swal.fire({
            title: 'Under Process',
            html: '<i class="fas fa-rss fa-2x m-2"></i> Please wait while getting page data',
            allowOutsideClick: true,
            showCancelButton: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });
        var formData = jQuery('#page-parser').serialize();

        formData += '&action=' + clickedButton;
        var wrapper = jQuery('#ajax-content');
        $.ajax({
            url: ajaxurl, // Replace with your endpoint URL
            type: "POST",
            data: {
                action: action,
                param: 'parse-url',
                formData: formData,
                nonce: nonce
            },
            success: function (response) {
                Swal.close();
                // Handle successful response, e.g., display success message
                var template = wp.template('page-info');
                var template_content = template({
                    result: response.data.result,
                });
                wrapper.html(template_content);
                Swal.fire({
                    icon: response.data.status,
                    title: response.data.message,

                })
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle errors, e.g., display error message
                console.error(textStatus + " " + errorThrown);
            }
        });
    });
});