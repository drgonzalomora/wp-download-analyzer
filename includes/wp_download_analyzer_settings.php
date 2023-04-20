<?php
/**
 * WP Download Analyzer for WordPress - Settings Page
 *
 * This file contains the code for the WP Download Analyzer settings page.
 * It allows users to configure the Slug and other parameters.
 *
 * @package wp-download-analyzer
 */

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

// WP Download Analyszer Options
function wp_download_analyzer_options_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'options';

    ?>
    <div class="wrap">
        <h1>WP Download Analyzer Settings</h1>
        <h2 class="nav-tab-wrapper">
            <a href="?page=wp-download-analyzer-stats&tab=options" class="nav-tab <?php echo $active_tab == 'options' ? 'nav-tab-active' : ''; ?>">Options</a>
            <a href="?page=wp-download-analyzer-stats&tab=results" class="nav-tab <?php echo $active_tab == 'results' ? 'nav-tab-active' : ''; ?>">Results</a>
            <a href="?page=wp-download-analyzer-stats&tab=support" class="nav-tab <?php echo $active_tab == 'support' ? 'nav-tab-active' : ''; ?>">Support</a>
        </h2>
        <form method="post" action="options.php">
            <?php
            if ($active_tab == 'options') {
                settings_fields('wp_download_analyzer_options');
                do_settings_sections('wp-download-analyzer-stats');
            } elseif ($active_tab == 'results') {
                echo wp_download_analyzer(); // Display the results here
            } elseif ($active_tab == 'support') {
                settings_fields('wp_download_analyzer_support');
                do_settings_sections('wp-download-analyzer-support');
            }
            if ($active_tab !== 'results') {
                submit_button();
            } else {
                submit_button('Refresh Results', 'primary', 'submit', true, array('id' => 'refresh-results-button'));
                echo '<script>
                document.getElementById("refresh-results-button").addEventListener("click", function(event) {
                    event.preventDefault();
                    location.reload();
                });
                </script>';
            }
            ?>
        </form>
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

// Setting Slug
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