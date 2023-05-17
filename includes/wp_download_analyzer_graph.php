<?php
/**
 * WP Download Analyzer for WordPress - Graph Support
 *
 * This file contains the code for the WP Download Analyzer Graphs.
 *
 * @package wp-download-analyzer
 */

 function wp_download_analyzer_render_chart($downloads_data) {

    $table = "";

    // Chart the data
    $table .= "<h2>Chart Detail</h2>";
    $table .= "<div class='chart-container'><canvas id='downloadsChart'></canvas></div>";

    $chart_data = array(
        'labels' => array_keys($downloads_data),
        'datasets' => array(
            array(
                'label' => 'Downloads',
                'data' => array_values($downloads_data),
                'backgroundColor' => 'rgba(75, 192, 192, 0.2)',
                'borderColor' => 'rgba(75, 192, 192, 1)',
                'borderWidth' => 1
            )
        )
    );

    // Chart data results
    $chart_data_json = json_encode($chart_data);

    // Chart the data
    $chart_js = <<<EOT
    <script>
    jQuery(document).ready(function() {
        var ctx = document.getElementById('downloadsChart').getContext('2d');
        var chartData = {$chart_data_json};
        var downloadsChart = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            unit: 'day'
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                },
                responsive: true,
                maintainAspectRatio: false
            }
        });
    });
    </script>
    EOT;

    // Append the chart to the results table
    $table = $table . $chart_js;

    return $table;
    
}