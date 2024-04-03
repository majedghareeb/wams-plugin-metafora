jQuery(document).ready(function () {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "web_notifications_frontend_ajax_request";

    $("#notification-settings").submit(function (event) {
        event.preventDefault(); // Prevent default form submission

        var formData = jQuery('#notification-settings').serialize()

        $.ajax({
            url: ajaxurl, // Replace with your endpoint URL
            type: "POST",
            data: {
                action: action,
                param: 'save-user-notification-settings',
                formData: formData,
                nonce: nonce
            },
            success: function (response) {
                // Handle successful response, e.g., display success message
                Swal.fire({
                    icon: response.data.status,
                    title: response.data.message,
                    timer: 1000,

                })
            },
            error: function (jqXHR, textStatus, errorThrown) {
                // Handle errors, e.g., display error message
                console.error(textStatus + " " + errorThrown);
            }
        });
    });


    jQuery('#checkAll').click(function () {
        var checkbox = $(this);
        jQuery('.table-checkbox').prop('checked', checkbox.is(':checked'));
        // Update UI (optional)
        if (checkbox.is(':checked')) {
            jQuery('#notifications-table').find('tr').addClass('table-active');
        } else {
            jQuery('#notifications-table').find('tr').removeClass('table-active');
        }
    });

    jQuery('input[type="checkbox"]').change(function () {
        $(this).closest('tr').toggleClass('table-active', $(this).is(':checked'));
    });

    jQuery('.action-button').click(function (event) {
        // Get the button and its parent row
        var actionType = event.currentTarget.dataset.actionType;
        var rowId = event.currentTarget.dataset.rowId;
        wams_notifications_actions(actionType, rowId)
        switch (actionType) {
            case 'delete':
                var row = jQuery(this).closest('tr');
                row.remove();
                break;
            case 'read':
                var status = jQuery(this).closest('tr').find('td .status');
                status.text('read');

                break;
            case 'unread':
                var status = jQuery(this).closest('tr').find('.status');
                status.text('unread');
                break;
            default:
                break;
        }
    });
    // Checkbox change
    jQuery('.multi-action-button').click(function (event) {
        var actionType = event.currentTarget.dataset.actionType;
        var selectedIds = [];
        jQuery('.table-checkbox:checked').each(function () {
            if (jQuery(this).attr('id') !== 'checkAll') {
                selectedIds.push(jQuery(this).attr('id'));
                switch (actionType) {
                    case 'delete':
                        var row = jQuery(this).closest('tr');
                        row.remove();
                        break;
                    case 'read':
                        var status = jQuery(this).closest('tr').find('td .status');
                        status.text('read');

                        break;
                    case 'unread':
                        var status = jQuery(this).closest('tr').find('.status');
                        status.text('unread');
                        break;
                    default:
                        break;
                }
            }


        });

        wams_notifications_actions(actionType, selectedIds)
    });


    /**
     * AJAX request for a new notification
     */
    function wams_notifications_actions(actionType, selectedIds) {

        var param = 'notification_action_' + actionType
        jQuery.ajax({
            type: "POST",
            dataType: "JSON",
            url: ajaxurl,
            data: {
                action: action,
                selectedIds: selectedIds,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {},
            complete: function () {},
            success: function (response) {}
        });
        // }
    }
}); // End of Fetch URL Data