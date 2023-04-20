<?php
/**
 * Plugin Name: WP Download Analyzer
 * Plugin URI:  https://github.com/kognetiks/wp-download-analyzer
 * Description: A simple plugin to display plugin and theme downloads statistics from the WordPress API.
 * Version:     1.0.0
 * Author:      Kognetiks.com
 * Author URI:  https://www.kognetiks.com
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * 
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 2, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 * 
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * You should have received a copy of the GNU General Public License
 * along with WP Download Analyzer. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
 * 
 */

 function wp_download_analyzer() {
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
    $header .= "<p><b>Type: {$analysis_type}</bp</p>";
    // Add download link
    $header .= '<p><a href="' . esc_url(admin_url('admin-post.php?action=wp_download_analyzer_download_csv')) . '">Download Data as CSV</a></p>';

    $plugin_link = "https://wordpress.org/plugins/{$slug}/";
    $header .= "<p><b>Wordpress {$analysis_type} may be found here: <a href='{$plugin_link}' target='_blank'>{$slug}</a></b></p>";
    $header .= "<p>Plugin: {$plugin_link}</p>";
    $header .= "<p>Plugin Version: {$version_url}</p>";
    $header .= "<p>Plugin History: {$url}</p>";
    $header .= "<p>Plugin Summary: {$history_url}</p>";

    //
    // IF RETURN DATA IS EMPTY THEN DON'T DO SECTION 
    //
    // Return no data available
    if (empty($downloads_data)) {
        return $header . "<div><p>No data available.</p></div>";
    }

    // Version data if available
    $table = "";
    $table .= "<p>Version Data</p>";
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
    $table .= "<p>Summary Data</p>";
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
    $table .= "<p>Detail Data</p>";
    $table .= "<div>";
    $table .= '<table class="wp-stats-table">';
    $table .= '<thead><tr><th>Date</th><th>Downloads</th></tr></thead><tbody>';

    foreach ($downloads_data as $date => $downloads) {
        $table .= "<tr><td>{$date}</td><td>{$downloads}</td></tr>";
    }

    $table .= '</tbody></table>';
    $table .= '</div>';

    // Chart the data
    $table .= "<div style='width:100%; height: 400px;'><canvas id='downloadsChart' style='width: 100%; height: 100%;'></canvas></div>";

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


// Add the settings page to the WordPress admin menu.
function wp_download_analyzer_menu() {
    add_options_page(
        'WP Download Analyzer Options',
        'WP Download Analyzer',
        'manage_options',
        'wp-download-analyzer-stats',
        'wp_download_analyzer_options_page'
    );
}
add_action('admin_menu', 'wp_download_analyzer_menu');


// Create the settings page content.
function wp_download_analyzer_options_page() {
    ?>
    <div class="wrap">
        <h1>WP Download Analyzer Options</h1>
        <form method="post" action="options.php">
            <?php
                settings_fields('wp_download_analyzer_options');
                do_settings_sections('wp-download-analyzer-stats');
                submit_button();
            ?>
        </form>
        <?php
            $options = get_option('wp_download_analyzer_options');
            if (!empty($options['slug'])) {
                echo do_shortcode('[wp_download_analyzer]');
            }
        ?>
    </div>
    <?php
}


// Handle form submission
function wp_download_analyzer_form_submit() {
    // Check for and process the form data
    if (isset($_POST['wp_download_analyzer_options'])) {
        update_option('wp_download_analyzer_options', $_POST['wp_download_analyzer_options']);
    }

    // Redirect to the options page with the 'updated' query parameter
    $redirect_url = add_query_arg(array(
        'page' => 'wp-download-analyzer-stats',
        'updated' => 'true'
    ), admin_url('options-general.php'));

    wp_redirect($redirect_url);
    exit;
}
add_action('admin_post_wp_download_analyzer_form_submit', 'wp_download_analyzer_form_submit');


// Register and define the plugin settings.
function wp_download_analyzer_settings() {
    register_setting(
        'wp_download_analyzer_options',
        'wp_download_analyzer_options',
        'wp_download_analyzer_options_validate'
    );

    add_settings_section(
        'wp_download_analyzer_main',
        'Main Settings',
        'wp_download_analyzer_section_text',
        'wp-download-analyzer-stats'
    );

    add_settings_field(
        'wp_download_analyzer_slug',
        'Plugin Slug',
        'wp_download_analyzer_setting_slug',
        'wp-download-analyzer-stats',
        'wp_download_analyzer_main'
    );
}
add_action('admin_init', 'wp_download_analyzer_settings');


// Display the section text.
function wp_download_analyzer_section_text() {
    echo '<p>Enter the plugin or theme slug to display its downloads statistics:</p>';
}

function wp_download_analyzer_setting_slug() {
    $default_options = array('slug' => '');
    $options = get_option('wp_download_analyzer_options', $default_options);
    echo "<input id='wp_download_analyzer_slug' name='wp_download_analyzer_options[slug]' size='40' type='text' value='{$options['slug']}' />";
}


// Validate and sanitize the plugin settings input.
function wp_download_analyzer_options_validate($input) {
    $newinput['slug'] = sanitize_text_field($input['slug']);
    return $newinput;
}

function wp_download_analyzer_styles() {
    wp_enqueue_style('wp-download-analyzer-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    wp_enqueue_script('jquery');
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), '3.7.0', true);
}
add_action('wp_enqueue_scripts', 'wp_download_analyzer_styles');
add_action('admin_enqueue_scripts', 'wp_download_analyzer_styles');



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

