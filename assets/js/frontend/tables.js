jQuery(document).ready(function ($) {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var form_id = jQuery('#wams-table').attr("data-form");
    var searchable_fields = jQuery('#wams-table').attr("data-searchable-fields");
    var action = "wams_tables";
    $('#wams-table').bootstrapTable({
        ignoreColumn: [],
        url: ajaxurl + '?action=' + action + '&form=' + form_id, // Replace with the URL of your PHP script
        method: 'GET',
        dataType: 'json',
        pagination: true,
        search: true,
        toolbar: "#toolbar",
        showExport: "true",
        clickToSelect: "true",
        toolbarAlign: "left",
        searchAlign: "right",
        // clickToSelect: "true",
        exportTypes: "['csv', 'excel', 'pdf']",
        loadingTemplate: '<i class="fa fa-spinner fa-spin fa-fw fa-3x"></i>',
        sidePagination: 'server', // Enable server-side pagination
        queryParamsType: 'limit',
        queryParams: function (params) {
            console.log('queryParams: ' + JSON.stringify(params))
            return {
                search_in: searchable_fields,
                limit: params.limit, // Number of rows per page
                offset: params.offset, // Current page number
                search: params.search, // Search query
                sort: params.sort,
                order: params.order, // Sort parameters
            };
        },
        responseHandler: function (response) {
            return {
                total: response.total,
                rows: response.rows
            };
        },

        // columns: [
        // 	// Define your table columns here
        // 	{
        // 		field: 'id',
        // 		title: 'ID'
        // 	},
        // 	{
        // 		field: 'title',
        // 		title: 'Title'
        // 	},
        // 	// Add more table headers as needed
        // ]
    });
    $('#clear-cache').click(function () {
        $.ajax({
            url: ajaxurl + '?action=' + action + '&form=' + form_id,
            type: "POST",
            data: {
                clear_cache: true,
                nonce: nonce
            },
        });
    });

    $('#add-to-order').click(function () {
        var orderId = $(this).data('order-id');
        // console.log(orderId);
        Swal.fire({
            title: "You are About to add Selected Rows to Order # " + orderId,
            showDenyButton: true,
            showCancelButton: true,
            confirmButtonText: "Save",
            denyButtonText: `Don't save`
        }).then((result) => {
            /* Read more about isConfirmed, isDenied below */
            if (result.isConfirmed) {
                var selections = $('#wams-table').bootstrapTable('getSelections');
                // Perform bulk action on selected rows
                var requests = [];
                selections.forEach(function (row) {
                    requests.push(row.id);

                    // You can perform your custom action on each selected row here
                });
                $.ajax({
                    url: ajaxurl, // Replace with your endpoint URL
                    type: "POST",
                    data: {
                        action: 'payment_orders',
                        param: 'update_payment_order',
                        orderId: orderId,
                        requests: requests,
                        nonce: nonce
                    },
                    success: function (response) {
                        console.log(response);
                        $('#wams-table').bootstrapTable('refresh')

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: response.data,
                                timer: 1000,

                            })
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: response.data,
                                timer: 1000,

                            })
                        }
                        // Handle successful response, e.g., display success message

                    },
                    error: function (jqXHR, textStatus, errorThrown) {
                        // Handle errors, e.g., display error message
                        console.error(textStatus + " " + errorThrown);
                    }
                });
            } else if (result.isDenied) {
                Swal.fire("Changes are not saved", "", "info");
            }
        });


    });

    $('#links-list-table').bootstrapTable({
        url: ajaxurl + '?action=links_list', // Replace with the URL of your PHP script
        method: 'GET',
        dataType: 'json',
        pagination: true,
        sidePagination: 'server', // Enable server-side pagination
        queryParamsType: 'limit',
        queryParams: function (params) {
            console.log('queryParams: ' + JSON.stringify(params))
            return {
                ids: [0, 1, 2, 3],
                limit: params.limit, // Number of rows per page
                offset: params.offset, // Current page number
                search: params.search, // Search query
                sort: params.sort,
                order: params.order, // Sort parameters
            };
        },
        responseHandler: function (response) {
            return {
                total: response.total,
                rows: response.rows
            };
        },
    });

    jQuery('.action-button').click(function (event) {
        // Get the button and its parent row
        var actionType = event.currentTarget.dataset.actionType;
        var rowId = event.currentTarget.dataset.rowId;
        console.log(rowId);
    });
    // Checkbox change
    jQuery('.multi-action-button').click(function (event) {
        getSelections = table.bootstrapTable('getSelections')
        for (let index = 0; index < getSelections.length; index++) {
            const element = getSelections[index];
            console.log(element);
        }
    });



});

function linkFormatter(value, row) {
    var url = row[1];
    return '<a target="_blank" href="' + url + '">' + value + '</a>';
}


function buttons() {
    return {
        btnUsersAdd: {
            text: 'Refresh',
            icon: 'bi-arrow-clockwise',
            event: function () {
                $.ajax({
                    url: ajaxurl, // Replace with your endpoint URL
                    type: "POST",
                    data: {
                        action: 'wams_table',
                        clear_cache: true,
                        requests: requests,
                        nonce: nonce
                    },
                });
            },
            attributes: {
                title: 'Clear Cache'
            }
        }
    }
}