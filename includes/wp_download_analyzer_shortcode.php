<?php
/**
 * Chatbot ChatGPT for WordPress - Shortcode Registration
 *
 * This file contains the code for registering the shortcode used
 * to display the WP Download Analyzer on the website.
 *
 * @package wp-download-analyzer
 */

 function wp_download_analyzer_shortcode( $atts = array() ) {

    // Set default Parameters
    $atts = shortcode_atts(array(
        'slug' => 'null',
        'type' => 'Plugin' // Either 'Plugin' or 'Theme'
    ), $atts);

    ob_start(); // Start output buffering

    ?>
    <div id="wp_download_analyzer">
        <div id="wp-download-analyzer-header">
            <div id="wp-stats-table" class="wp-stats-body">
                <div><h1>SHORT CODE DEMO <?php echo $atts['slug']; ?> <?php echo $atts['type']; ?></h1></div>
                <?php wp_download_analyzer($atts); ?>
            </div>
        </div>
    <?php

    return ob_get_clean(); // Return the buffered output
}
add_shortcode( 'wp_download_stats', 'wp_download_analyzer_shortcode' );