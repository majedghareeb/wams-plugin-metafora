jQuery(document).ready(function () {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "web_notifications_frontend_ajax_request";
    var wams_notifications_filter_trigger = false;
    var wams_notifications_interval_id;

    wams_notifications_init_interval();

    function wams_notifications_init_interval() {
        /* Load notifications */
        if (parseInt(wams_frontend_scripts.timer) !== 0) {
            // wams_notifications_interval_id = setInterval(wams_load_notifications, parseInt(10000));
            wams_notifications_interval_id = setInterval(wams_load_notifications, parseInt(wams_frontend_scripts.timer));
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

    jQuery('.notification-item').click(function () {
        notification_id = jQuery(this).data('id');
        notification_url = jQuery(this).data('url');
        wams_notifications_actions('read', parseInt(notification_id));
        window.location.href = notification_url;
    });


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
        // console.log('triggered');

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
                var inbox_count = response.data.inbox_count;
                if (inbox_count > 0) jQuery('.workflow-inbox-count').html(inbox_count)
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