<h1>Charts</h1>
<?php //print_r($output_arrays); 
wp_enqueue_script("chart", WAMS_URL . 'assets/js/frontend/chart.min.js', array(), WAMS_VERSION, false);
wp_enqueue_script("chart-datalabels", WAMS_URL . 'assets/js/frontend/chartjs-plugin-datalabels.min.js', array(), WAMS_VERSION, false);
if (!empty($tasks)) :
?>
    <script>
        jQuery(document).ready(function($) {
            // Your Chart.js configuration
            var ctx = document.getElementById('myChart').getContext('2d');
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
                    indexAxis: 'y',
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                },

            });
        });
    </script>
<?php endif; ?>
<div>
    <canvas id="myChart"></canvas>
</div>