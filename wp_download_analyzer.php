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
 * along with Chatbot ChatGPT. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
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
        $url = "http://api.wordpress.org/stats/plugin/1.0/downloads.php?slug={$slug}";
        $history_url = "http://api.wordpress.org/stats/plugin/1.0/downloads.php?slug={$slug}&historical_summary=1";
    } elseif ($analysis_type == 'theme'){
        $url = "https://api.wordpress.org/stats/themes/1.0/downloads.php?slug={$slug}";
        $history_url = "https://api.wordpress.org/stats/themes/1.0/downloads.php?slug={$slug}&historical_summary=1";
    }
    $response = wp_remote_get($url);

    if (is_wp_error($response)) {
        return "Error: " . $response->get_error_message();
    }

    $downloads_data = json_decode(wp_remote_retrieve_body($response), true);

    // Analysis header
    $header = "";
    $header .= "<h1>WP Download Analyzer: {$slug}</h1>";
    $header .= "<p><b>Type: {$analysis_type}</bp</p>";

    $plugin_link = "https://wordpress.org/plugins/{$slug}/";
    $header .= "<p><b>Wordpress Plugin may be found here: <a href='{$plugin_link}' target='_blank'>{$slug}</a></b></p>";
    // $header .= "<p></p>";

    // Return no data available
    if (empty($downloads_data)) {
        return $header . "<div><p>No data available.</p></div>";
    }

    // Return data if available
    $table = "";
    $table .= "<div>";
    $table .= '<table class="wp-stats-table">';
    $table .= '<thead><tr><th>Date</th><th>Downloads</th></tr></thead><tbody>';

    foreach ($downloads_data as $date => $downloads) {
        $table .= "<tr><td>{$date}</td><td>{$downloads}</td></tr>";
    }

    $table .= '</tbody></table>';
    $table .= '</div>';

    return $header . $table;
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
    </div>
    <?php
}


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

// Add the style.css file
function wp_download_analyzer_styles() {
    wp_enqueue_style('wp-download-analyzer-style', plugin_dir_url(__FILE__) . 'assets/css/style.css');
}
add_action('wp_enqueue_scripts', 'wp_download_analyzer_styles');