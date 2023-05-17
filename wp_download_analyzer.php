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

// Enqueue all styles
function wp_download_analyzer_enqueue_all_styles() {
    // Always enqueue dashicons and the plugin's style.css
    wp_enqueue_style( 'dashicons' );
    wp_enqueue_style('wp-download-analyzer-styles', plugin_dir_url(__FILE__) . 'assets/css/style.css');

    // If we're on the WP Download Analyzer settings page or the front-end, enqueue extra styles
    $hook = isset($GLOBALS['pagenow']) ? $GLOBALS['pagenow'] : false;
    if ( $hook === 'settings_page_wp-download-analyzer-settings' || !is_admin() ) {
        wp_register_style('wp-download-analyzer-extra-style', false);
        wp_enqueue_style('wp-download-analyzer-extra-style');

        $custom_css = "
            .wp-download-button-container {
                display: flex;
                gap: 8px;
                margin-bottom: 16px;
            }
            .wp-download-analyzer-button {
                border-radius: 4px;
            }
        ";

        wp_add_inline_style('wp-download-analyzer-extra-style', $custom_css);

    }
}
add_action('wp_enqueue_scripts', 'wp_download_analyzer_enqueue_all_styles');
add_action('admin_enqueue_scripts', 'wp_download_analyzer_enqueue_all_styles');


// Chart Support for the admin page
function wp_download_analyzer_enqueue_scripts($hook) {
    // Check if we're on the WP Download Analyzer settings page
    if ($hook === 'settings_page_wp-download-analyzer-settings' || $hook === 'index.php') {
        // Enqueue the required scripts
        wp_enqueue_script('jquery');
        wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), false, true);
        wp_enqueue_script('chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array('chartjs'), false, true);
    }
}
add_action('admin_enqueue_scripts', 'wp_download_analyzer_enqueue_scripts');


// Add Chart Support for the frontend/shortcode
function wp_download_analyzer_enqueue_frontend_scripts() {
    // Enqueue the required scripts
    wp_enqueue_script('jquery');
    wp_enqueue_script('chartjs', 'https://cdn.jsdelivr.net/npm/chart.js', array('jquery'), false, true);
    wp_enqueue_script('chartjs-adapter-date-fns', 'https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns/dist/chartjs-adapter-date-fns.bundle.min.js', array('chartjs'), false, true);
    }
add_action('wp_enqueue_scripts', 'wp_download_analyzer_enqueue_frontend_scripts');


// Add link to WP Download Analyzer options - setting page
function wp_download_analyzer_plugin_action_links($links) {
    $settings_link = '<a href="../wp-admin/options-general.php?page=wp-download-analyzer-settings">' . __('Settings', 'wp_download_analyzer') . '</a>';
    array_unshift($links, $settings_link);
    return $links;
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'wp_download_analyzer_plugin_action_links');

// Settings
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_settings.php';

// Results
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_results.php';

// Graph
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_graph.php';

// Dashboard
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_dashboard.php';

// Shortcode
include plugin_dir_path(__FILE__) . 'includes/wp_download_analyzer_shortcode.php';