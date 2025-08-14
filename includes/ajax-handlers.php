<?php
/**
 * AJAX handlers for Custom Taxonomy Filter Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX Handler: Get all car brands (parent terms only)
 */
function ajax_get_car_brands() {
    // Verify nonce
    ctf_verify_nonce($_POST['nonce']);
    
    // Get parent terms only (brands)
    $brands = ctf_get_terms(array('parent' => 0));
    
    if (is_wp_error($brands)) {
        ctf_debug_log('Error fetching brands', $brands->get_error_message());
        wp_send_json_error('Error fetching brands: ' . $brands->get_error_message());
    }
    
    $brand_data = array();
    foreach ($brands as $brand) {
        $brand_data[] = array(
            'id' => $brand->term_id,
            'name' => $brand->name,
            'slug' => $brand->slug
        );
    }
    
    ctf_debug_log('Brands loaded', count($brand_data) . ' brands found');
    wp_send_json_success($brand_data);
}
add_action('wp_ajax_get_car_brands', 'ajax_get_car_brands');
add_action('wp_ajax_nopriv_get_car_brands', 'ajax_get_car_brands');

/**
 * AJAX Handler: Get models by brand
 */
function ajax_get_models_by_brand() {
    // Verify nonce
    ctf_verify_nonce($_POST['nonce']);
    
    $brand_id = intval($_POST['brand_id']);
    
    if (!$brand_id) {
        wp_send_json_error('Brand ID is required');
    }
    
    // Get child terms (models) of the selected brand
    $models = ctf_get_terms(array('parent' => $brand_id));
    
    if (is_wp_error($models)) {
        ctf_debug_log('Error fetching models for brand ' . $brand_id, $models->get_error_message());
        wp_send_json_error('Error fetching models: ' . $models->get_error_message());
    }
    
    $model_data = array();
    foreach ($models as $model) {
        $model_data[] = array(
            'id' => $model->term_id,
            'name' => $model->name,
            'slug' => $model->slug
        );
    }
    
    ctf_debug_log('Models loaded for brand ' . $brand_id, count($model_data) . ' models found');
    wp_send_json_success($model_data);
}
add_action('wp_ajax_get_models_by_brand', 'ajax_get_models_by_brand');
add_action('wp_ajax_nopriv_get_models_by_brand', 'ajax_get_models_by_brand');

/**
 * AJAX Handler: Get years by model
 */
function ajax_get_years_by_model() {
    // Verify nonce
    ctf_verify_nonce($_POST['nonce']);
    
    $model_id = intval($_POST['model_id']);
    
    if (!$model_id) {
        wp_send_json_error('Model ID is required');
    }
    
    // Get products that have this car model (taxonomy term)
    $products = ctf_get_products(array(
        'tax_query' => array(
            array(
                'taxonomy' => CTF_TAXONOMY,
                'field' => 'term_id',
                'terms' => $model_id,
            ),
        ),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => CTF_FIELD_YEAR_START,
                'compare' => 'EXISTS'
            ),
            array(
                'key' => CTF_FIELD_YEAR_END,
                'compare' => 'EXISTS'
            )
        )
    ));
    
    if (empty($products)) {
        wp_send_json_error('No products found for this model');
    }
    
    $year_ranges = array();
    foreach ($products as $product) {
        $year_start = get_field(CTF_FIELD_YEAR_START, $product->ID);
        $year_end = get_field(CTF_FIELD_YEAR_END, $product->ID);
        
        if ($year_start && $year_end) {
            $year_display = $year_start . '-' . $year_end;
            $year_ranges[$year_display] = array(
                'display' => $year_display,
                'start' => $year_start,
                'end' => $year_end,
                'product_id' => $product->ID
            );
        }
    }
    
    // Remove duplicates and sort
    $unique_years = array_values($year_ranges);
    usort($unique_years, function($a, $b) {
        return $a['start'] - $b['start'];
    });
    
    ctf_debug_log('Years loaded for model ' . $model_id, count($unique_years) . ' year ranges found');
    wp_send_json_success($unique_years);
}
add_action('wp_ajax_get_years_by_model', 'ajax_get_years_by_model');
add_action('wp_ajax_nopriv_get_years_by_model', 'ajax_get_years_by_model');

/**
 * AJAX Handler: Get product details for modal
 */
function ajax_get_product_details() {
    // Verify nonce
    ctf_verify_nonce($_POST['nonce']);
    
    // Validate required parameters
    $required_params = array('brand_id', 'model_id', 'year_start', 'year_end');
    if (!ctf_validate_required_params($_POST, $required_params)) {
        wp_send_json_error('All parameters are required');
    }
    
    $brand_id = intval($_POST['brand_id']);
    $model_id = intval($_POST['model_id']);
    $year_start = intval($_POST['year_start']);
    $year_end = intval($_POST['year_end']);
    
    // Find product that matches the criteria
    $products = ctf_get_products(array(
        'posts_per_page' => 1,
        'tax_query' => array(
            array(
                'taxonomy' => CTF_TAXONOMY,
                'field' => 'term_id',
                'terms' => $model_id,
            ),
        ),
        'meta_query' => array(
            'relation' => 'AND',
            array(
                'key' => CTF_FIELD_YEAR_START,
                'value' => $year_start,
                'compare' => '='
            ),
            array(
                'key' => CTF_FIELD_YEAR_END,
                'value' => $year_end,
                'compare' => '='
            )
        )
    ));
    
    if (empty($products)) {
        wp_send_json_error('No matching product found');
    }
    
    $product = $products[0];
    $product_id = $product->ID;
    
    // Get brand and model names
    $brand_term = get_term($brand_id, CTF_TAXONOMY);
    $model_term = get_term($model_id, CTF_TAXONOMY);
    
    // Get product images
    $product_images = ctf_get_product_images($product_id);
    
    // Get ACF field values
    $towbar_price = get_field(CTF_FIELD_TOWBAR_PRICE, $product_id) ?: 0;
    $electrical_price = get_field(CTF_FIELD_ELECTRICAL_PRICE, $product_id) ?: 0;
    $rating_kg = get_field(CTF_FIELD_RATING_KG, $product_id) ?: 0;
    $total_price = $towbar_price + $electrical_price;
    
    // Prepare response data
    $response_data = array(
        'product_id' => $product_id,
        'product_name' => $product->post_title,
        'brand_name' => $brand_term ? $brand_term->name : '',
        'model_name' => $model_term ? $model_term->name : '',
        'year_range' => $year_start . '-' . $year_end,
        'car_images' => $product_images,
        'car_image' => !empty($product_images) ? $product_images[0] : CTF_DEFAULT_CAR_IMAGE,
        'towbar_price' => $towbar_price,
        'electrical_price' => $electrical_price,
        'total_price' => $total_price,
        'rating_kg' => $rating_kg,
        'towbar_image' => CTF_DEFAULT_TOWBAR_IMAGE,
        'product_url' => get_permalink($product_id)
    );
    
    ctf_debug_log('Product details loaded', 'Product ID: ' . $product_id);
    wp_send_json_success($response_data);
}
add_action('wp_ajax_get_product_details', 'ajax_get_product_details');
add_action('wp_ajax_nopriv_get_product_details', 'ajax_get_product_details');
