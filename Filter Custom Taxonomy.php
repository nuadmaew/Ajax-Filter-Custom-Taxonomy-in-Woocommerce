<?php
/**
 * Plugin Name: Filter Custom Taxonomy
 * Description: Car towbar filter with AJAX functionality for WooCommerce
 * Version: 1.0.0
 * Author: Your Name
 * Text Domain: towbar-filter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('TOWBAR_FILTER_VERSION', '1.0.0');
define('TOWBAR_FILTER_PATH', plugin_dir_path(__FILE__));
define('TOWBAR_FILTER_URL', plugin_dir_url(__FILE__));

// Include configuration
require_once TOWBAR_FILTER_PATH . 'includes/config.php';

// Include core files
require_once TOWBAR_FILTER_PATH . 'includes/helpers.php';
require_once TOWBAR_FILTER_PATH . 'includes/ajax-handlers.php';
require_once TOWBAR_FILTER_PATH . 'includes/shortcode.php';

// Include debug functions (only for development)
if (defined('WP_DEBUG') && WP_DEBUG) {
    require_once TOWBAR_FILTER_PATH . 'includes/debug.php';
}

/**
 * Enqueue plugin assets (CSS & JavaScript)
 */
function towbar_filter_enqueue_assets() {
    // Only load on pages that need it
    if (is_page() || is_front_page() || is_shop()) {
        
        // Enqueue CSS
        wp_enqueue_style(
            'towbar-filter-css', 
            TOWBAR_FILTER_URL . 'assets/css/towbar-filter.css',
            array(),
            TOWBAR_FILTER_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'towbar-filter-js', 
            TOWBAR_FILTER_URL . 'assets/js/towbar-filter.js', 
            array('jquery'), 
            TOWBAR_FILTER_VERSION, 
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('towbar-filter-js', 'towbar_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('towbar_filter_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'towbar_filter_enqueue_assets');

/**
 * Plugin activation hook
 */
function towbar_filter_activate() {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('This plugin requires WooCommerce to be installed and active.');
    }
    
    // Check if ACF is active
    if (!function_exists('get_field')) {
        deactivate_plugins(plugin_basename(__FILE__));
        wp_die('This plugin requires Advanced Custom Fields (ACF) to be installed and active.');
    }
    
    // Flush rewrite rules
    flush_rewrite_rules();
}
register_activation_hook(__FILE__, 'towbar_filter_activate');

/**
 * Plugin deactivation hook
 */
function towbar_filter_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'towbar_filter_deactivate');
