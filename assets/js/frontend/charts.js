jQuery(document).ready(function ($) {
    var ajaxurl = wams_frontend_scripts.ajaxurl;
    var nonce = wams_frontend_scripts.nonce
    var action = "charts_ajax_request";
    var primaryColor = getComputedStyle(document.documentElement).getPropertyValue('--bs-primary');

    $(".form-chart").click(function () {
        var form_id = $(this).data('form-id');
        var form_title = $(this).data('form-title');
        var data = get_dataset(form_id, 'bar').then(function (dataset) {
            if (dataset) {
                console.log(dataset.data);
                console.log(dataset.labels);
                bar_chart("bar_chart", form_title, dataset.data, dataset.labels);
                pie_chart("pie_chart", form_title, dataset.data, dataset.labels);
            }
            // console.log(data);
            // You can use the data here or perform other operations with it
        }).catch(function (error) {
            console.error("Error:", error);
        })
    });


    function bar_chart(div_id, title, data, lables) {
        var bar_chart = document.querySelector("#" + div_id);
        var options = {
            legend: {
                show: true
            },
            fill: {
                colors: [
                    "#1f77b4", // Blue
                    "#ff7f0e", // Orange
                    "#2ca02c", // Green
                ],
            },
            chart: {
                height: 350,
                type: "bar",
                toolbar: {
                    show: true
                }
            },
            plotOptions: {
                bar: {
                    borderRadius: 10,
                    dataLabels: {
                        position: "top"
                    }
                }
            },
            dataLabels: {
                enabled: !0,
                // formatter: function (e) {
                //     return e + "%"
                // },
                offsetY: -22,
                style: {
                    fontSize: "12px",
                    colors: ["#ff7f0e"]
                }
            },

            series: [{
                name: "Count",
                data: data
            }],
            grid: {
                borderColor: "#ff7f0e"
            },
            xaxis: {
                categories: lables,
                position: "top",
                labels: {
                    offsetY: -18
                },
                tooltip: {
                    enabled: !0,
                    offsetY: -35
                }
            },
            yaxis: {
                axisBorder: {
                    show: true
                },
                axisTicks: {
                    show: !1
                },
                labels: {
                    show: true,
                    // formatter: function (e) {
                    //     return e + "%"
                    // }
                }
            },
            title: {
                text: title,
                floating: !1,
                offsetY: 330,
                align: "center",
                style: {
                    color: '#ff7f0e',
                    fontWeight: "600"
                }
            }
        };
        var chart = new ApexCharts(bar_chart, options);
        chart.render();
    }

    function pie_chart(div_id, title, data, lables) {
        var dataAsNumbers = $.map(data, function (str) {
            return parseInt(str, 10); // or use parseFloat() for floating-point numbers
        });
        var pie_chart = document.querySelector("#" + div_id);
        options = {
            chart: {
                height: 320,
                type: "pie"
            },
            series: dataAsNumbers,
            labels: lables,
            colors: [
                "#1f77b4", // Blue
                "#ff7f0e", // Orange
                "#2ca02c", // Green
                "#d62728", // Red
                "#9467bd", // Purple
                "#8c564b", // Brown
                "#e377c2", // Pink
                "#7f7f7f", // Gray
                "#bcbd22", // Yellow-Green
                "#17becf" // Turquoise
            ],
            legend: {
                show: !0,
                position: "bottom",
                horizontalAlign: "center",
                verticalAlign: "middle",
                floating: !1,
                fontSize: "14px",
                offsetX: 0
            },
            responsive: [{
                breakpoint: 600,
                options: {
                    chart: {
                        height: 500
                    },
                    legend: {
                        show: !0
                    }
                }
            }]
        };
        var chart = new ApexCharts(pie_chart, options);
        chart.render();
    }

    /**
     * AJAX request for a new notification
     */
    function get_dataset(form_id, chart_type) {
        return new Promise(function (resolve, reject) {
            jQuery.ajax({
                type: "POST",
                dataType: "JSON",
                url: ajaxurl,
                data: {
                    action: action,
                    form_id: form_id,
                    chart_type: chart_type,
                    param: 'get_chart_data',
                    nonce: nonce
                },
                beforeSend: function () {},
                complete: function () {},
                success: function (response) {

                    resolve(response.data);

                },
                error: function (xhr, status, error) {
                    reject(error); // Reject the promise with the error
                }
            });

        });
        // }
    }
});