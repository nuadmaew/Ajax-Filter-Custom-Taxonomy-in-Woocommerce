<?php
/**
 * Helper functions for Custom Taxonomy Filter Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Verify AJAX nonce for security
 */
function ctf_verify_nonce($nonce) {
    if (!isset($nonce) || !wp_verify_nonce($nonce, 'ctf_nonce')) {
        wp_send_json_error('Security check failed');
        return false;
    }
    return true;
}

/**
 * Get product images (featured + gallery)
 */
function ctf_get_product_images($product_id) {
    $product_images = array();
    
    // Featured image first
    $featured_image_id = get_post_thumbnail_id($product_id);
    if ($featured_image_id) {
        $product_images[] = wp_get_attachment_image_url($featured_image_id, 'medium');
    }
    
    // Gallery images
    $attachment_ids = get_post_meta($product_id, '_product_image_gallery', true);
    if ($attachment_ids) {
        $attachment_ids = explode(',', $attachment_ids);
        foreach ($attachment_ids as $attachment_id) {
            $image_url = wp_get_attachment_image_url($attachment_id, 'medium');
            if ($image_url) {
                $product_images[] = $image_url;
            }
        }
    }
    
    return $product_images;
}

/**
 * Format price with currency symbol
 */
function ctf_format_price($price) {
    global $ctf_currency_settings;
    
    if (!$price) return '0';
    
    $formatted_price = number_format(
        $price, 
        0, 
        $ctf_currency_settings['decimal_sep'], 
        $ctf_currency_settings['thousands_sep']
    );
    
    if ($ctf_currency_settings['position'] === 'before') {
        return $ctf_currency_settings['symbol'] . $formatted_price;
    } else {
        return $formatted_price . $ctf_currency_settings['symbol'];
    }
}

/**
 * Get terms with default parameters
 */
function ctf_get_terms($args = array()) {
    global $ctf_defaults;
    
    $default_args = array(
        'taxonomy' => CTF_TAXONOMY,
        'hide_empty' => $ctf_defaults['hide_empty_terms'],
        'orderby' => $ctf_defaults['orderby'],
        'order' => $ctf_defaults['order']
    );
    
    $args = wp_parse_args($args, $default_args);
    
    return get_terms($args);
}

/**
 * Get products with default parameters
 */
function ctf_get_products($args = array()) {
    global $ctf_defaults;
    
    $default_args = array(
        'post_type' => CTF_POST_TYPE,
        'posts_per_page' => $ctf_defaults['posts_per_page'],
        'post_status' => 'publish'
    );
    
    $args = wp_parse_args($args, $default_args);
    
    return get_posts($args);
}

/**
 * Validate required parameters
 */
function ctf_validate_required_params($params, $required_keys) {
    foreach ($required_keys as $key) {
        if (!isset($params[$key]) || empty($params[$key])) {
            return false;
        }
    }
    return true;
}

/**
 * Log debug messages (only when WP_DEBUG is enabled)
 */
function ctf_debug_log($message, $data = null) {
    if (defined('WP_DEBUG') && WP_DEBUG) {
        error_log('[Custom Taxonomy Filter] ' . $message);
        if ($data !== null) {
            error_log('[Custom Taxonomy Filter Data] ' . print_r($data, true));
        }
    }
}
