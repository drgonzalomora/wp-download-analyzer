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

// Analyzer Styles
function wp_download_analyzer_enqueue_styles($hook) {
    // Check if we're on the WP Download Analyzer settings page
    if ($hook === 'settings_page_wp-download-analyzer-stats') {
        wp_enqueue_style('wp-download-analyzer-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css');
    }
}
add_action('admin_enqueue_scripts', 'wp_download_analyzer_enqueue_styles');

// Chart Support
function wp_download_analyzer_enqueue_scripts($hook) {
    // Check if we're on the WP Download Analyzer settings page
    if ($hook === 'settings_page_wp-download-analyzer-stats') {
        // Enqueue the required scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), false, true);
        wp_enqueue_script('chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array('chartjs'), false, true);
    }
}
add_action('admin_enqueue_scripts', 'wp_download_analyzer_enqueue_scripts');

// Settings
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_settings.php';

// Results
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_results.php';

// Graph
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_graph.php';

// Dashboard
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_dashboard.php';