<h1>Charts</h1>
<?php //print_r($output_arrays); 
echo json_encode($tasks['datasets']);
if (!empty($tasks)) :
?>
    <script>
        jQuery(document).ready(function($) {
            // Your Chart.js configuration
            var ctx = document.getElementById('myChart').getContext('2d');
            var pie = document.getElementById('myChart_pie').getContext('2d');
            Chart.register(ChartDataLabels);
            Chart.defaults.set('plugins.datalabels', {
                anchor: 'end',
                align: 'end',
                color: 'black',
                font: {
                    weight: 'bold'
                },
                formatter: function(value, context) {
                    return value;
                }
            });
            const data = [<?php echo json_encode($tasks['datasets']); ?>];
            var myChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($tasks['labels']); ?>,
                    datasets: data
                },
                options: {
                    indexAxis: 'x',
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                },

            });

            var pie_chart = new Chart(pie, {
                type: "pie",
                data: {
                    labels: <?php echo json_encode($tasks['labels']); ?>,
                    datasets: data
                },
                options: {}
            });
        });
    </script>
<?php endif; ?>
<div class="row">
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Bar Chart</h4>
            </div>
            <div class="card-body">

                <canvas id="myChart" height="300"></canvas>

            </div>
        </div>
    </div> <!-- end col -->
    <div class="col-xl-6">
        <div class="card">
            <div class="card-header">
                <h4 class="card-title mb-0">Pie Chart</h4>
            </div>
            <div class="card-body">
                <canvas id="myChart_pie" width="451" height="100"></canvas>
            </div>
        </div>
    </div> <!-- end col -->
</div>