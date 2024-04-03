jQuery(document).ready(function () {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "google_analytics_ajax_request";

    var table = $('#urls-list-table');

    jQuery('#get-analytics').click(function (event) {
        var actionType = event.currentTarget.dataset.actionType;
        getSelections = table.bootstrapTable('getSelections')
        console.log(getSelections);
        for (let i = 0; i < getSelections.length; i++) {
            const element = getSelections[i];
            console.log(element.id);

            get_analytics_data(element.title, element.entry_id).then(function (data) {
                if (data[0]) {
                    table.bootstrapTable('updateRow', {
                        index: element.id,
                        row: {
                            id: element.entry_id,
                            pageview: data[0].Pageviews,
                            sessions: data[0].Sessions
                        }
                    })
                }
                // console.log(data);
                // You can use the data here or perform other operations with it
            }).catch(function (error) {
                console.error("Error:", error);
            });

        }

        // wams_actions(actionType, selectedTitles)
    });


    /**
     * AJAX request for a new notification
     */
    function get_analytics_data(title, entry_id) {
        return new Promise(function (resolve, reject) {
            jQuery.ajax({
                type: "POST",
                dataType: "JSON",
                url: ajaxurl,
                data: {
                    action: action,
                    title: title,
                    entry_id: entry_id,
                    param: 'get_analytics_data',
                    nonce: nonce
                },
                beforeSend: function () {},
                complete: function () {},
                success: function (response) {
                    resolve(response.data);
                    console.log(response.data);

                },
                error: function (xhr, status, error) {
                    reject(error); // Reject the promise with the error
                }
            });

        });
        // }
    }
}); // End of Fetch URL Data