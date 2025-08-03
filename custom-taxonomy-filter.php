<?php
/**
 * Plugin Name: Custom Taxonomy Filter
 * Description: Hierarchical product filter with AJAX functionality for WooCommerce
 * Version: 1.0.0
 * Author: Nuadmaew
 * Text Domain: custom-taxonomy-filter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('CTF_VERSION', '1.0.0');
define('CTF_PATH', plugin_dir_path(__FILE__));
define('CTF_URL', plugin_dir_url(__FILE__));

// Include configuration
require_once CTF_PATH . 'includes/config.php';

// Include core files
require_once CTF_PATH . 'includes/helpers.php';
require_once CTF_PATH . 'includes/ajax-handlers.php';
require_once CTF_PATH . 'includes/shortcode.php';

// Include debug functions (only for development)
if (defined('WP_DEBUG') && WP_DEBUG) {
    require_once CTF_PATH . 'includes/debug.php';
}

/**
 * Enqueue plugin assets (CSS & JavaScript)
 */
function ctf_enqueue_assets() {
    // Only load on pages that need it
    if (is_page() || is_front_page() || is_shop()) {
        
        // Enqueue CSS
        wp_enqueue_style(
            'ctf-css', 
            CTF_URL . 'assets/css/custom-taxonomy-filter.css',
            array(),
            CTF_VERSION
        );
        
        // Enqueue JavaScript
        wp_enqueue_script(
            'ctf-js', 
            CTF_URL . 'assets/js/custom-taxonomy-filter.js', 
            array('jquery'), 
            CTF_VERSION, 
            true
        );
        
        // Localize script with AJAX data
        wp_localize_script('ctf-js', 'ctf_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ctf_nonce')
        ));
    }
}
add_action('wp_enqueue_scripts', 'ctf_enqueue_assets');

/**
 * Plugin activation hook
 */
function ctf_activate() {
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
register_activation_hook(__FILE__, 'ctf_activate');

/**
 * Plugin deactivation hook
 */
function ctf_deactivate() {
    flush_rewrite_rules();
}
register_deactivation_hook(__FILE__, 'ctf_deactivate');
