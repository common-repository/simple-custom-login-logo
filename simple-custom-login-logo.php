<?php
/*
Plugin Name: Simple Custom Login Logo
Plugin URI:  https://stelnyk.com/wp-custom-login-logo
Donate link: https://stelnyk.com/wp-custom-login-logo
Description: A simple plugin to customize the login logo.
Version:     1.1
Author:      Nick Stelnyk
Author URI:  https://stelnyk.com
Text Domain: simple-custom-login-logo
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Enqueue admin scripts
function scll_admin_enqueue_scripts() {
    $ver = '1.1';
    $plugin_url = plugin_dir_url(__FILE__);
    wp_enqueue_media();
    wp_register_script('scll-login-logo-js', $plugin_url . 'scll.js', array('jquery'), $ver, true);
    wp_enqueue_script('scll-login-logo-js');
    wp_enqueue_style('wp-color-picker');
    wp_register_script('scll-color-script', $plugin_url . 'scll-color.js', array('wp-color-picker'), $ver, true);
    wp_enqueue_script('scll-color-script');
}
add_action('admin_enqueue_scripts', 'scll_admin_enqueue_scripts');

// Add custom logo and background color to the login page
function scll_logo_and_background() {
    $logo_url = get_option('scll_logo');
    $background_color = get_option('scll_background_color', '#ffffff');
    $custom_css = "
        body.login {
            background-color: " . esc_attr($background_color) . ";
        }
        #login h1 a {
            background-image: url('" . esc_url($logo_url) . "');
            width: 100%;
            height: 100px;
            background-size: contain;
            background-repeat: no-repeat;
            margin-bottom: 20px;
        }
    ";
    wp_enqueue_style('login');
    wp_add_inline_style('login', $custom_css);
}
add_action('login_enqueue_scripts', 'scll_logo_and_background');

// Add settings to the WordPress Settings menu
function scll_menu() {
    add_options_page(
        'Simple Custom Login Logo',
        'Custom Login Logo',
        'manage_options',
        'simple-custom-login-logo',
        'scll_settings_page'
    );
}
add_action('admin_menu', 'scll_menu');

// Settings page content
function scll_settings_page() {
    ?>
    <div class="wrap">
        <h1>Custom Login Logo</h1>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <?php
            settings_fields('scll_settings');
            do_settings_sections('simple-custom-login-logo');
            wp_nonce_field('scll_nonce_action', 'scll_nonce');
            echo '<input type="hidden" name="action" value="scll_save_settings" />';
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Initialize settings
function scll_settings_init() {
    register_setting('scll_settings', 'scll_logo', array(
        'sanitize_callback' => 'esc_url_raw'
    ));
    register_setting('scll_settings', 'scll_background_color', array(
        'sanitize_callback' => 'sanitize_hex_color'
    ));

    add_settings_section(
        'scll_logo_section',
        '',
        null,
        'simple-custom-login-logo'
    );

    add_settings_field(
        'scll_logo',
        'Upload Custom Login Logo',
        'scll_field_callback',
        'simple-custom-login-logo',
        'scll_logo_section'
    );

    add_settings_field(
        'scll_background_color',
        'Select Background Color',
        'scll_background_color_field_callback',
        'simple-custom-login-logo',
        'scll_logo_section'
    );
}
add_action('admin_init', 'scll_settings_init');

// Callback for the media uploader (logo upload)
function scll_field_callback() {
    $logo_url = get_option('scll_logo');
    ?>
    <div>
        <input type="hidden" id="scll_logo" name="scll_logo" value="<?php echo esc_url($logo_url); ?>" />
        <img id="scll_logo_preview" src="<?php echo esc_url($logo_url); ?>" style="max-height: 100px; width: auto;" />
        <br><br>
        <input type="button" id="scll_logo_button" class="button button-secondary" value="Select or Upload Logo" />
        <input type="button" id="scll_logo_remove" class="button button-secondary" value="Remove Logo" />
    </div>
    <?php
}

// Callback for the background color field (color picker)
function scll_background_color_field_callback() {
    $background_color = get_option('scll_background_color', '#ffffff');
    ?>
    <input type="text" id="scll_background_color" name="scll_background_color" value="<?php echo esc_attr($background_color); ?>" class="color-field" data-default-color="#ffffff" />
    <?php
}

// Process the form data when settings are saved
function scll_save_settings() {
    if (isset($_POST['scll_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['scll_nonce'])), 'scll_nonce_action')) {
        $logo_url = isset($_POST['scll_logo']) ? esc_url_raw(wp_unslash($_POST['scll_logo'])) : '';
        $background_color = isset($_POST['scll_background_color']) ? sanitize_hex_color(wp_unslash($_POST['scll_background_color'])) : '';

        update_option('scll_logo', $logo_url);
        update_option('scll_background_color', $background_color);

        wp_redirect(admin_url('options-general.php?page=simple-custom-login-logo'));
        exit;
    } else {
        wp_die(esc_html__('Nonce verification failed or missing. Please try again.', 'simple-custom-login-logo'));
    }
}
add_action('admin_post_scll_save_settings', 'scll_save_settings');

// Custom logo URL
function scll_logo_url() {
    return home_url();
}
add_filter('login_headerurl', 'scll_logo_url');
