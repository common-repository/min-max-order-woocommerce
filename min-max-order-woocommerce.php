<?php
/*
Plugin Name: Minimum and Maximum Order Value for WooCommerce
Description: Sets a minimum and maximum order value for WooCommerce and displays custom messages on the cart and checkout pages.
Author: Diego de Guindos
Author URI: https://diegoguindos.com
Version: 1.1.0
License: GPLv3
Requires Plugins: woocommerce
*/

defined('ABSPATH') or die('Hey, what are you doing? STOP!');

include_once(ABSPATH . 'wp-admin/includes/plugin.php');

// WooCommerce is not active, do not execute the plugin
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

// Enqueue admin scripts and styles
add_action('admin_enqueue_scripts', 'mmow_enqueue_admin_scripts');
function mmow_enqueue_admin_scripts() {
    wp_enqueue_style('mmow-admin-style', plugin_dir_url(__FILE__) . 'assets/css/admin-style.css');
    wp_enqueue_script('mmow-admin-script', plugin_dir_url(__FILE__) . 'assets/js/admin-script.js', array('jquery'), false, true);
}

// Add a settings page in WooCommerce
add_action('admin_menu', 'mmow_add_menu');
function mmow_add_menu() {
    add_submenu_page(
        'woocommerce',
        'Min/Max Order Value', // Page title
        'Min/Max Order Value', // Menu title
        'manage_options',      // Capability
        'min-max-order-value-settings', // Menu slug
        'mmow_render_settings_page'  // Callback function
    );
}

// Render the settings page in the admin panel
function mmow_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Minimum and Maximum Order Value Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields('mmow_order_value_settings');
            do_settings_sections('min-max-order-value-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register the settings
add_action('admin_init', 'mmow_register_settings');
function mmow_register_settings() {
    add_settings_section(
        'mmow_order_value_section',
        'Order Value Options',
        'mmow_order_value_section_description',
        'min-max-order-value-settings'
    );

    // Checkbox to enable minimum order value
    add_settings_field(
        'mmow_enable_minimum_order_value',
        'Enable minimum order value',
        'mmow_enable_minimum_order_value_callback',
        'min-max-order-value-settings',
        'mmow_order_value_section'
    );
    register_setting('mmow_order_value_settings', 'mmow_enable_minimum_order_value', 'sanitize_text_field');

    // Minimum order fields (hidden by default)
    add_settings_field(
        'mmow_minimum_order_fields',
        '',
        'mmow_minimum_order_fields_callback',
        'min-max-order-value-settings',
        'mmow_order_value_section'
    );
    register_setting('mmow_order_value_settings', 'mmow_minimum_order_value', 'sanitize_text_field');
    register_setting('mmow_order_value_settings', 'mmow_cart_error_message_minimum', 'sanitize_text_field');

    // Checkbox to enable maximum order value
    add_settings_field(
        'mmow_enable_maximum_order_value',
        'Enable maximum order value',
        'mmow_enable_maximum_order_value_callback',
        'min-max-order-value-settings',
        'mmow_order_value_section'
    );
    register_setting('mmow_order_value_settings', 'mmow_enable_maximum_order_value', 'sanitize_text_field');

    // Maximum order fields (hidden by default)
    add_settings_field(
        'mmow_maximum_order_fields',
        '',
        'mmow_maximum_order_fields_callback',
        'min-max-order-value-settings',
        'mmow_order_value_section'
    );
    register_setting('mmow_order_value_settings', 'mmow_maximum_order_value', 'sanitize_text_field');
    register_setting('mmow_order_value_settings', 'mmow_cart_error_message_maximum', 'sanitize_text_field');
}

// Description of the settings section
function mmow_order_value_section_description() {
    echo '<p>Configure the minimum and maximum order value for WooCommerce and the corresponding error messages that will be shown in the Cart and Checkout pages accordingly.</p>';
}

// Callback to enable minimum order value
function mmow_enable_minimum_order_value_callback() {
    $enable_minimum = get_option('mmow_enable_minimum_order_value');
    echo '<input type="checkbox" id="mmow_enable_minimum_order_value" name="mmow_enable_minimum_order_value" value="1" ' . checked(1, $enable_minimum, false) . ' />';
}

// Callback to enable maximum order value
function mmow_enable_maximum_order_value_callback() {
    $enable_maximum = get_option('mmow_enable_maximum_order_value');
    echo '<input type="checkbox" id="mmow_enable_maximum_order_value" name="mmow_enable_maximum_order_value" value="1" ' . checked(1, $enable_maximum, false) . ' />';
}

// Callback to show the minimum order fields
function mmow_minimum_order_fields_callback() {
    $minimum_order_value = get_option('mmow_minimum_order_value', 450);
    $cart_error_message_minimum = get_option('mmow_cart_error_message_minimum', 'Your order must be at least %s to proceed.');

    echo '<div id="mmow_minimum_order_fields" style="display: none;">';
    echo '<label for="mmow_minimum_order_value">Minimum order value: </label><br>';
    echo '<input type="number" name="mmow_minimum_order_value" value="' . esc_attr($minimum_order_value) . '" /><br><br>';

    echo '<label for="mmow_cart_error_message_minimum">Minimum order error message (displayed in Cart & Checkout): </label><br>';
    echo '<textarea name="mmow_cart_error_message_minimum" rows="3" cols="50">' . esc_textarea($cart_error_message_minimum) . '</textarea><br><br>';
    echo '</div>';
}

// Callback to show the maximum order fields
function mmow_maximum_order_fields_callback() {
    $maximum_order_value = get_option('mmow_maximum_order_value', 1000);
    $cart_error_message_maximum = get_option('mmow_cart_error_message_maximum', 'Your order exceeds the maximum allowed value of %s.');

    echo '<div id="mmow_maximum_order_fields" style="display: none;">';
    echo '<label for="mmow_maximum_order_value">Maximum order value: </label><br>';
    echo '<input type="number" name="mmow_maximum_order_value" value="' . esc_attr($maximum_order_value) . '" /><br><br>';

    echo '<label for="mmow_cart_error_message_maximum">Maximum order error message (displayed in Cart & Checkout): </label><br>';
    echo '<textarea name="mmow_cart_error_message_maximum" rows="3" cols="50">' . esc_textarea($cart_error_message_maximum) . '</textarea><br><br>';
    echo '</div>';
}

// Validate the minimum/maximum order value in the cart and checkout
add_action('woocommerce_checkout_process', 'mmow_validate_order_value');
add_action('woocommerce_before_cart', 'mmow_validate_order_value');
function mmow_validate_order_value() {
    $enable_minimum = get_option('mmow_enable_minimum_order_value');
    $enable_maximum = get_option('mmow_enable_maximum_order_value');

    $minimum = get_option('mmow_minimum_order_value', 450); // Default minimum value
    $maximum = get_option('mmow_maximum_order_value', 1000); // Default maximum value

    $cart_error_message_minimum = get_option('mmow_cart_error_message_minimum', 'Your order must be at least %s to proceed.');
    $cart_error_message_maximum = get_option('mmow_cart_error_message_maximum', 'Your order exceeds the maximum allowed value of %s.');

    $has_error = false; // Variable to track if there's an error

    // Validate if the cart total is below the minimum
    if ($enable_minimum && WC()->cart->total < $minimum) {
        wc_add_notice(sprintf($cart_error_message_minimum, wc_price($minimum)), 'error');
        $has_error = true; // Indicate that there's an error
    }

    // Validate if the cart total exceeds the maximum
    if ($enable_maximum && WC()->cart->total > $maximum) {
        wc_add_notice(sprintf($cart_error_message_maximum, wc_price($maximum)), 'error');
        $has_error = true; // Indicate that there's an error
    }

    // Disable the checkout button if there's an error
    if ($has_error) {
        add_action('wp_footer', 'mmow_disable_checkout_button');
    }
}

// Function to disable the checkout button via action hook
function mmow_disable_checkout_button() {
    echo '<style>
            .woocommerce-checkout .button.checkout {
                pointer-events: none;
                opacity: 0.5;
            }
          </style>';
}

// Add settings link on the plugins page
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'mmow_add_settings_link');
function mmow_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('admin.php?page=min-max-order-value-settings') . '">Settings</a>';
    array_unshift($links, $settings_link);
    return $links;
}
