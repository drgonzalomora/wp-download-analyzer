<?php
/**
 * WP Download Analyzer for WordPress - Settings Page
 *
 * This file contains the code for the WP Download Analyzer results page.
 *
 * @package wp-download-analyzer
 */

if (!function_exists('wp_download_analyzer')) {
    function wp_download_analyzer() {
        wp_download_analyzer_enqueue_extra_styles();
        $default_options = array('slug' => '');
        $options = get_option('wp_download_analyzer_options', $default_options);
        $slug = $options['slug'];

        // TEMPORARY
        $analysis_type = 'plugin';
        
        if (empty($slug)) {
            return "Please set a slug for the Plugin or Theme Downloads you wish to analyze.";
        }
        
        if ($analysis_type == 'plugin'){
            $url =          "https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug={$slug}";
            $history_url =  "https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug={$slug}&historical_summary=1";
            $version_url =  "https://api.wordpress.org/stats/plugin/1.0/?slug={$slug}";
        } elseif ($analysis_type == 'theme'){
            $url = "https://api.wordpress.org/stats/themes/1.0/downloads.php?slug={$slug}";
            $history_url = "https://api.wordpress.org/stats/themes/1.0/downloads.php?slug={$slug}&historical_summary=1";
        }

        // Retrieve the detailed data
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            return "Slug Error: " . $response->get_error_message();
        }

        $downloads_data = json_decode(wp_remote_retrieve_body($response), true);

        // Retrieve this history data
        $response = wp_remote_get($history_url);

        if (is_wp_error($response)) {
            return "Detail Error: " . $response->get_error_message();
        }

        $history_data = json_decode(wp_remote_retrieve_body($response), true);

        if (is_wp_error($history_data)) {
            return "History Error: " . $history_data->get_error_message();
        }

        // Retrieve the version data
        $response = wp_remote_get($version_url);

        $version_data = json_decode(wp_remote_retrieve_body($response), true);

        if (is_wp_error($version_data)) {
            return "Version Error: " . $version_data->get_error_message();
        }

        // Analysis header
        $header = "";
        $header .= "<h1>WP Download Analyzer: {$slug}</h1>";

        // Refresh Data and Download link
        // $header .= '<p><a href="' . esc_url(admin_url('admin-post.php?action=wp_download_analyzer_download_csv')) . '" class="wpda-download-csv-btn">Download Data as CSV</a></p>';
        $header .= '<div class="button-container">';
        $header .= '<a class="button button-primary" href="' . esc_url(add_query_arg(array('settings-updated' => false))) . '">Refresh Results</a>';
        $header .= '<a class="button button-primary" href="' . esc_url(admin_url('admin-post.php?action=wp_download_analyzer_download_csv')) . '">Download Data as CSV</a>';
        $header .= '</div>';


        $header .= "<p><b>Type: {$analysis_type}</b></p>";
        $plugin_link = "https://wordpress.org/plugins/{$slug}/";
        $header .= "<p><b>Wordpress {$analysis_type} may be found here: <a href='{$plugin_link}' target='_blank'>{$slug}</a></b></p>";
        // This link is the same as the one above
        // $header .= "<p>Plugin: <a href='{$plugin_link}' target='_blank' rel='nofollow'>{$plugin_link}</a></p>";
        $header .= "<p>Plugin Version: <a href='{$version_url}' target='_blank' rel='nofollow'>{$version_url}</a></p>";
        $header .= "<p>Plugin History: <a href='{$url}' target='_blank' rel='nofollow'>{$url}</a></p>";
        $header .= "<p>Plugin Summary: <a href='{$history_url}' target='_blank' rel='nofollow'>{$history_url}</a></p>";
        

        //
        // IF RETURN DATA IS EMPTY THEN DON'T DO SECTION 
        //
        // Return no data available
        if (empty($downloads_data)) {
            return $header . "<div><p>No data available.</p></div>";
        }

        // Version data if available
        $table = "";
        $table .= "<h2>Version Data</h2>";
        $table .= "<div>";
        $table .= '<table class="wp-stats-table">';
        $table .= '<thead><tr><th>Version</th><th>% Downloads</th></tr></thead><tbody>';
        
        foreach ($version_data as $version => $downloads) {
            $version_label = ucfirst(str_replace('_', ' ', $version));
            $table .= "<tr><td>{$version_label}</td><td>{$downloads}</td></tr>";
        }
        
        $table .= '</tbody></table>';
        $table .= '</div>';

        // Summary data if available
        $table .= "<h2>Summary Data</h2>";
        $table .= "<div>";
        $table .= '<table class="wp-stats-table">';
        $table .= '<thead><tr><th>Period</th><th>Downloads</th></tr></thead><tbody>';
        
        foreach ($history_data as $period => $downloads) {
            $period_label = ucfirst(str_replace('_', ' ', $period));
            $table .= "<tr><td>{$period_label}</td><td>{$downloads}</td></tr>";
        }
        
        $table .= '</tbody></table>';
        $table .= '</div>';

        // Detailed data if available
        $table .= "<h2>Detail Data</h2>";
        $table .= "<div>";
        $table .= '<table class="wp-stats-table">';
        $table .= '<thead><tr><th>Date</th><th>Downloads</th></tr></thead><tbody>';

        foreach ($downloads_data as $date => $downloads) {
            $table .= "<tr><td>{$date}</td><td>{$downloads}</td></tr>";
        }

        $table .= '</tbody></table>';
        $table .= '</div>';

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
        $chart_data_json = json_encode($chart_data);
        
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
        
        return $header . $table . $chart_js;
    }
    add_shortcode('wp_download_analyzer', 'wp_download_analyzer');


    // Download the data
    function wp_download_analyzer_download_csv() {
        $default_options = array('slug' => '');
        $options = get_option('wp_download_analyzer_options', $default_options);
        $slug = $options['slug'];

        // TEMPORARY
        $analysis_type = 'plugin';
        
        if (empty($slug)) {
            wp_die("Please set a slug for the Plugin or Theme Downloads you wish to analyze.");
        }
        
        if ($analysis_type == 'plugin'){
            $url = "https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug={$slug}";
        } elseif ($analysis_type == 'theme'){
            $url = "https://api.wordpress.org/stats/themes/1.0/downloads.php?slug={$slug}";
        }

        // Retrieve the detailed data
        $response = wp_remote_get($url);

        if (is_wp_error($response)) {
            wp_die("Slug Error: " . $response->get_error_message());
        }

        $downloads_data = json_decode(wp_remote_retrieve_body($response), true);

        if (is_wp_error($downloads_data)) {
            wp_die("Downloads Data Error: " . $downloads_data->get_error_message());
        }

        $csv_data = "Date,Downloads\n";
        foreach ($downloads_data as $date => $downloads) {
            $csv_data .= "{$date},{$downloads}\n";
        }

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment;filename=' . $slug . '_download_data.csv');
        echo $csv_data;
        exit;
    }
    add_action('admin_post_wp_download_analyzer_download_csv', 'wp_download_analyzer_download_csv');
}


// Special CSS include to ensure button are wrapped
function wp_download_analyzer_enqueue_extra_styles() {
    wp_register_style('wp-download-analyzer-style', false);
    wp_enqueue_style('wp-download-analyzer-style');
    
    $custom_css = "
        .button-container {
            display: flex;
            gap: 8px;
            margin-bottom: 16px;
        }
        .wp-download-analyzer-button {
            border-radius: 4px;
        }
    ";

    wp_add_inline_style('wp-download-analyzer-style', $custom_css);
}