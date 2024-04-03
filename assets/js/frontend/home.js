jQuery(document).ready(function ($) {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "wams_frontend";

    $('.my-tasks-step-count').click(function () {
        var searchValue = $(this).attr('data-step-name'); // Get the value from input and trim whitespace
        if (searchValue !== '') {
            // Set the search value
            // Trigger the search
            $('#my-tasks-table').bootstrapTable('refreshOptions', {
                searchText: searchValue
            });
        } else {
            $('#my-tasks-table').bootstrapTable('refreshOptions', {
                searchText: null
            });
        }
    });
    $('.my-team-tasks-step-count').click(function () {
        var searchValue = $(this).attr('data-step-name'); // Get the value from input and trim whitespace
        if (searchValue !== '') {
            // Set the search value
            // Trigger the search
            $('#my-team-tasks-table').bootstrapTable('refreshOptions', {
                searchText: searchValue
            });
        } else {
            $('#my-team-tasks-table').bootstrapTable('refreshOptions', {
                searchText: null
            });
        }
    });

    jQuery(document).on("click", "#tasks-refresh", function () {
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: action,
                nonce: nonce,
                param: "refresh_tasks"
            },
            complete: function (response) {
                if (response.status === 200) {
                    // console.log(response);
                    $('#my-tasks-table').bootstrapTable('refresh')
                }

            }
        });
    });

    jQuery(document).on("click", "#team-tasks-refresh", function () {
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: action,
                nonce: nonce,
                param: "refresh_team_tasks"
            },
            complete: function (response) {
                if (response.status === 200) {
                    // console.log(response);
                    $('#my-team-tasks-table').bootstrapTable('refresh')
                }

            }
        });
    });
    jQuery(document).on("click", "#requests-refresh", function () {
        jQuery.ajax({
            type: "POST",
            dataType: "json",
            url: ajaxurl,
            data: {
                action: action,
                nonce: nonce,
                param: "refresh_requests"
            },
            complete: function (response) {

                if (response.status === 200) {
                    // console.log(response);
                    $('#my-requests-table').bootstrapTable('refresh')
                }

            }
        });
    });



});