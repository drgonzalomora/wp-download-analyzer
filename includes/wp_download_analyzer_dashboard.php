<?php
/**
 * WP Download Analyzer for WordPress - Dashboard Widget
 *
 * This file contains the code for the WP Download Analyzer Dashboard page.
 * It allows users to display high level statistcs.
 *
 * @package wp-download-analyzer
 */


function wp_download_analyzer_dashboard_widget_content() {

    // Fetch the downloads_data
    $default_options = array('slug' => '');
    $options = get_option('wp_download_analyzer_options', $default_options);
    $slug = $options['slug'];
    $analysis_type = isset($options['analysis_type']) ? $options['analysis_type'] : 'Plugin';
    
    if ($analysis_type == 'Plugin'){
        $url = "https://api.wordpress.org/stats/plugin/1.0/downloads.php?slug={$slug}";
    } elseif ($analysis_type == 'Theme'){
        $url = "https://api.wordpress.org/stats/themes/1.0/downloads.php?slug={$slug}";
    }

    // Retrieve the detailed data
    $response = wp_remote_get($url);
    if (is_wp_error($response)) {
        wp_die("Slug Error: " . $response->get_error_message());
    }

    // Decode the detailed data
    $downloads_data = json_decode(wp_remote_retrieve_body($response), true);
    if (is_wp_error($downloads_data)) {
        wp_die("Downloads Data Error: " . $downloads_data->get_error_message());
    }

    // Call the wp_download_analyzer_render_chart function to generate the chart:
    $table = wp_download_analyzer_render_chart($downloads_data);

    // echo $summary_data . $chart_js;
    // echo "Downloads";
    echo $table;

}


function wp_download_analyzer_add_dashboard_widget() {
    wp_add_dashboard_widget(
        'wp_download_analyzer_dashboard_widget', // Widget ID
        'WP Download Analyzer', // Widget title
        'wp_download_analyzer_dashboard_widget_content' // Callback function to display the widget content
    );
}

add_action('wp_dashboard_setup', 'wp_download_analyzer_add_dashboard_widget');
