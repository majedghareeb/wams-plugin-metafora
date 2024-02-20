jQuery(document).ready(function () {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "web_notifications_frontend_ajax_request";
    var wams_notifications_filter_trigger = false;
    var wams_notifications_interval_id;
    // Select all button click
    jQuery('.notification-item').click(function () {
        notification_id = jQuery(this).data('id');
        notification_url = jQuery(this).data('url');
        wams_notifications_actions('read', parseInt(notification_id));
        window.location.href = notification_url;
    });
    jQuery('#checkAll').click(function () {
        var checkbox = $(this);
        jQuery('.form-check-input').prop('checked', checkbox.is(':checked'));
        // Update UI (optional)
        if (checkbox.is(':checked')) {
            jQuery('#notifications-table').find('tr').addClass('selected');
        } else {
            jQuery('#notifications-table').find('tr').removeClass('selected');
        }
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
        jQuery('.form-check-input:checked').each(function () {
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

        // Send selectedRows to server, perform calculations, etc.
        console.log("actionType:", actionType);
        console.log("Selected rows:", selectedIds);
    });

    wams_notifications_init_interval();

    function wams_notifications_init_interval() {
        /* Load notifications */
        if (parseInt(wams_frontend_scripts.timer) !== 0) {
            wams_notifications_interval_id = setInterval(wams_load_notifications, parseInt(10000));
            // wams_notifications_interval_id = setInterval(wams_load_notifications, parseInt(wams_frontend_scripts.timer));
        }
    }
    // /* Play Notification Sound */
    // if (parseInt(wams_frontend_scripts.sound) && wams_frontend_scripts.sound_url) {
    //     jQuery(document).on('wams_notification_refresh_count', wams_notification_sound);
    // }

    // check if browser tab is active
    document.addEventListener('visibilitychange', function () {
        if (document.hidden) {
            // stop send ajax when browser tab is not active
            clearInterval(wams_notifications_interval_id);
        } else {
            // send ajax when browser tab is active
            wams_notifications_init_interval();
        }
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
            success: function (response) {
                console.log(response);
                // if (response.success !== true) {
                //     console.error("WAMS: Request 'wams_notifications_actions' failed.", response);
                //     return;
                // }

            }
        });
        // }
    }
    /**
     * Play Notification Sound
     * @returns null
     */
    function wams_notification_sound(e, data) {
        var sound = new Audio(wams_frontend_scripts.sound_url);
        var promise = sound.play();

        if (promise !== undefined) {
            promise.then(function (res) {
                console.log('Notification sound played!');
            }).catch(function (error) {
                console.log(error.message);
            });
        }
    }


    /**
     * AJAX request for a new notification
     */
    function wams_load_notifications() {
        console.log('triggered');
        if (wams_load_notifications.inProcess) {
            return;
        }

        if (wams_get_notifications.inProcess) {
            return;
        }
        var param = 'ajax_get_new_count'
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
                wams_load_notifications.inProcess = true;
            },
            complete: function () {
                wams_load_notifications.inProcess = false;
            },
            success: function (response) {
                if (response.success !== true) {
                    console.error("WAMS: Request 'wams_notification_get_new_count' failed.", response);
                    return;
                }
                // Display a quantity of new items as a number in red
                var new_count = response.data.new_notifications;
                if (jQuery('#unread-notifications-count').html() < new_count) {
                    if (new_count !== 0) {
                        jQuery('#unread-notifications-count').html(new_count)
                        jQuery('.notification-live-count').html(response.data.new_notifications_formatted);
                        wams_notification_sound();
                        wams_reload_notification_menu();
                    } else {
                        jQuery('.notification-live-count').html('');
                    }
                } else if (jQuery('#unread-notifications-count').html() > new_count) {
                    jQuery('#unread-notifications-count').html(new_count)
                    jQuery('.notification-live-count').html(response.data.new_notifications_formatted);
                    wams_reload_notification_menu();
                }
            }
        });
        // }
    }

    /**
     * AJAX get notifications
     */
    function wams_get_notifications(pagination) {
        if (wams_get_notifications.inProcess) {
            return;
        }

        if (!wams_notifications_filter_trigger) {
            if (wams_load_notifications.inProcess) {
                wams_load_notifications.xhr.abort();
                //return;
            }
        }
        var results_wrapper = jQuery('.um-notification-ajax');
        var offset = results_wrapper.data('offset');
        var per_page = results_wrapper.data('per_page');
        var request = {
            action: 'wams_get_on_load_notification',
            unread: unread,
            offset: offset,
            per_page: per_page,
            nonce: wams_frontend_scripts.nonce
        };
        if (pagination) {
            request.time = results_wrapper.data('time');
        }

        wams_get_notifications.xhr = jQuery.ajax({
            url: wams_frontend_scripts.ajaxurl,
            type: 'post',
            dataType: 'json',
            data: request,
            beforeSend: function () {
                wams_load_notifications.inProcess = true;
                wams_get_notifications.inProcess = true;
            },
            complete: function () {
                wams_load_notifications.inProcess = false;
                wams_get_notifications.inProcess = false;
                jQuery('.um-load-more-notifications').removeClass('disabled');
            },
            success: function (response) {
                jQuery('.um-ajax-loading-wrap').hide();

                var new_offset = parseInt(results_wrapper.data('offset'));

                if (response.data.notifications.length) {
                    new_offset = new_offset + parseInt(response.data.notifications.length);

                    var template = wp.template('um-notifications-list');
                    var template_content = template({
                        notifications: response.data.notifications
                    });
                    results_wrapper.append(template_content).data('offset', new_offset);

                    jQuery('.um-notifications-none').hide();

                    wams_init_new_dropdown();
                    wams_notifications_maybe_default_image();
                } else {
                    if (results_wrapper.find('.um-notification').length === 0) {
                        jQuery('.um-notifications-none').show();
                    }
                }

                if (!pagination) {
                    results_wrapper.data('time', response.data.time);
                }

                if (new_offset >= parseInt(response.data.total)) {
                    results_wrapper.siblings('.um-load-more-notifications').hide();
                } else {
                    results_wrapper.siblings('.um-load-more-notifications').show();
                }
            }
        });
    }

    // Show red counter if there are new notifications
    function wams_reload_notification_menu() {
        var param = 'ajax_check_update'
        jQuery.ajax({
            type: "POST",
            dataType: "HTML",
            url: ajaxurl,
            data: {
                action: action,
                param: param,
                nonce: nonce
            },
            beforeSend: function () {
                wams_load_notifications.inProcess = true;
            },
            complete: function () {
                wams_load_notifications.inProcess = false;
            },
            success: function (response) {
                if (!response) {
                    console.error("WAMS: Request 'wams_notification_get_new_count' failed.", response);
                    return;
                } else {
                    jQuery('#notifications-list-box').html(response);
                }
            }
        });
    }
});