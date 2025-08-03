<?php
/**
 * Debug and testing functions for Custom Taxonomy Filter Plugin
 * Only loaded when WP_DEBUG is enabled
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Debug function to test everything
 * Usage: Add ?test_ctf=1 to any URL (admin only)
 */
function test_ctf_system() {
    // Check if we're on the right page and user is admin
    if (!isset($_GET['test_ctf']) || !current_user_can('manage_options')) {
        return;
    }
    
    echo '<div style="background: white; padding: 20px; margin: 20px; border: 1px solid #ddd; font-family: Arial, sans-serif;">';
    echo '<h2>🧪 Testing Custom Taxonomy Filter System</h2>';
    echo '<p><strong>Debug Mode:</strong> ' . (defined('WP_DEBUG') && WP_DEBUG ? '✅ Enabled' : '❌ Disabled') . '</p>';
    
    // Test 1: Check plugin constants
    echo '<h3>1. Plugin Constants Check:</h3>';
    $constants = array(
        'CTF_VERSION' => CTF_VERSION,
        'CTF_PATH' => CTF_PATH,
        'CTF_URL' => CTF_URL,
        'CTF_TAXONOMY' => CTF_TAXONOMY,
        'CTF_POST_TYPE' => CTF_POST_TYPE
    );
    
    foreach ($constants as $name => $value) {
        echo "✅ {$name}: {$value}<br>";
    }
    
    // Test 2: Check required plugins
    echo '<h3>2. Required Plugins Check:</h3>';
    
    // WooCommerce check
    if (class_exists('WooCommerce')) {
        echo '✅ WooCommerce is active<br>';
        echo "&nbsp;&nbsp;↳ Version: " . WC()->version . "<br>";
    } else {
        echo '❌ WooCommerce is NOT active<br>';
    }
    
    // ACF check
    if (function_exists('get_field')) {
        echo '✅ ACF is active<br>';
        if (defined('ACF_VERSION')) {
            echo "&nbsp;&nbsp;↳ Version: " . ACF_VERSION . "<br>";
        }
    } else {
        echo '❌ ACF plugin is NOT active<br>';
    }
    
    // Test 3: Check taxonomy
    echo '<h3>3. Custom Taxonomy Check:</h3>';
    $taxonomy_exists = taxonomy_exists(CTF_TAXONOMY);
    if ($taxonomy_exists) {
        echo '✅ ' . CTF_TAXONOMY . ' taxonomy exists<br>';
        
        // Get brands (parent terms)
        $brands = ctf_get_terms(array('parent' => 0));
        echo "📊 Found " . count($brands) . " brands:<br>";
        
        foreach ($brands as $brand) {
            echo "- {$brand->name} (ID: {$brand->term_id})<br>";
            
            // Get models for this brand
            $models = ctf_get_terms(array('parent' => $brand->term_id));
            if (!empty($models)) {
                echo "&nbsp;&nbsp;↳ " . count($models) . " models: ";
                $model_names = array();
                foreach ($models as $model) {
                    $model_names[] = $model->name;
                }
                echo implode(', ', array_slice($model_names, 0, 5));
                if (count($model_names) > 5) {
                    echo ' (and ' . (count($model_names) - 5) . ' more)';
                }
                echo "<br>";
            }
        }
    } else {
        echo '❌ ' . CTF_TAXONOMY . ' taxonomy does not exist<br>';
    }
    
    // Test 4: Check ACF fields and products
    echo '<h3>4. Products & ACF Fields Check:</h3>';
    if (function_exists('get_field')) {
        // Find products with ACF fields
        $products = ctf_get_products(array(
            'posts_per_page' => 5,
            'meta_query' => array(
                array(
                    'key' => CTF_FIELD_YEAR_START,
                    'compare' => 'EXISTS'
                )
            )
        ));
        
        echo "📊 Found " . count($products) . " products with ACF fields:<br>";
        
        foreach ($products as $product) {
            $year_start = get_field(CTF_FIELD_YEAR_START, $product->ID);
            $year_end = get_field(CTF_FIELD_YEAR_END, $product->ID);
            $towbar_price = get_field(CTF_FIELD_TOWBAR_PRICE, $product->ID);
            $electrical_price = get_field(CTF_FIELD_ELECTRICAL_PRICE, $product->ID);
            
            echo "- {$product->post_title}: {$year_start}-{$year_end}, ";
            echo "Towbar: " . ctf_format_price($towbar_price) . ", ";
            echo "Electrical: " . ctf_format_price($electrical_price) . "<br>";
        }
        
        // Test ACF fields existence
        echo '<h4>ACF Fields Test:</h4>';
        $test_fields = array(
            CTF_FIELD_YEAR_START,
            CTF_FIELD_YEAR_END,
            CTF_FIELD_TOWBAR_PRICE,
            CTF_FIELD_ELECTRICAL_PRICE,
            CTF_FIELD_RATING_KG
        );
        
        foreach ($test_fields as $field) {
            echo "- {$field}: ";
            // Check if field exists in any product
            $has_field = false;
            foreach ($products as $product) {
                if (get_field($field, $product->ID)) {
                    $has_field = true;
                    break;
                }
            }
            echo $has_field ? '✅ Found' : '❌ Not found';
            echo "<br>";
        }
    }
    
    // Test 5: AJAX endpoints
    echo '<h3>5. AJAX Endpoints Check:</h3>';
    $ajax_actions = array(
        'get_car_brands',
        'get_models_by_brand', 
        'get_years_by_model',
        'get_product_details'
    );
    
    foreach ($ajax_actions as $action) {
        $registered = has_action("wp_ajax_{$action}") && has_action("wp_ajax_nopriv_{$action}");
        echo ($registered ? '✅' : '❌') . " {$action}" . ($registered ? ' is registered' : ' is NOT registered') . "<br>";
    }
    
    // Test 6: Asset files
    echo '<h3>6. Asset Files Check:</h3>';
    $asset_files = array(
        'CSS' => CTF_PATH . 'assets/css/custom-taxonomy-filter.css',
        'JavaScript' => CTF_PATH . 'assets/js/custom-taxonomy-filter.js'
    );
    
    foreach ($asset_files as $type => $file) {
        $exists = file_exists($file);
        echo ($exists ? '✅' : '❌') . " {$type}: ";
        echo $exists ? 'File exists' : 'File NOT found';
        echo " ({$file})<br>";
    }
    
    // Test 7: Shortcode
    echo '<h3>7. Shortcode Check:</h3>';
    $shortcode_exists = shortcode_exists('ctf_filter');
    echo ($shortcode_exists ? '✅' : '❌') . ' [ctf_filter] shortcode ';
    echo $shortcode_exists ? 'is registered' : 'is NOT registered';
    echo "<br>";
    
    // Test 8: Helper functions
    echo '<h3>8. Helper Functions Check:</h3>';
    $helper_functions = array(
        'ctf_verify_nonce',
        'ctf_get_product_images',
        'ctf_format_price',
        'ctf_get_terms',
        'ctf_get_products'
    );
    
    foreach ($helper_functions as $function) {
        $exists = function_exists($function);
        echo ($exists ? '✅' : '❌') . " {$function}()";
        echo $exists ? ' exists' : ' does NOT exist';
        echo "<br>";
    }
    
    // Test URLs
    echo '<h3>9. Test URLs:</h3>';
    echo '<p><strong>Add this shortcode to any page to test the filter:</strong></p>';
    echo '<code>[ctf_filter]</code><br><br>';
    
    echo '<p><strong>Test AJAX directly:</strong></p>';
    echo '<a href="' . admin_url('admin-ajax.php?action=get_car_brands&nonce=' . wp_create_nonce('ctf_nonce')) . '" target="_blank">Test get_car_brands</a><br>';
    
    // Performance info
    echo '<h3>10. Performance Info:</h3>';
    echo '<p>Memory usage: ' . size_format(memory_get_usage(true)) . '</p>';
    echo '<p>Peak memory: ' . size_format(memory_get_peak_usage(true)) . '</p>';
    echo '<p>Execution time: ' . (microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) . ' seconds</p>';
    
    echo '</div>';
    exit;
}
add_action('init', 'test_ctf_system');

/**
 * Log AJAX requests for debugging
 */
function ctf_debug_ajax_requests() {
    if (!defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    $ajax_actions = array('get_car_brands', 'get_models_by_brand', 'get_years_by_model', 'get_product_details');
    
    foreach ($ajax_actions as $action) {
        add_action("wp_ajax_{$action}", function() use ($action) {
            ctf_debug_log("AJAX Request: {$action}", $_POST);
        }, 1);
        
        add_action("wp_ajax_nopriv_{$action}", function() use ($action) {
            ctf_debug_log("AJAX Request (nopriv): {$action}", $_POST);
        }, 1);
    }
}
add_action('init', 'ctf_debug_ajax_requests');

/**
 * Add debug information to admin footer
 */
function ctf_admin_footer_debug() {
    if (!current_user_can('manage_options') || !defined('WP_DEBUG') || !WP_DEBUG) {
        return;
    }
    
    echo '<div style="margin-top: 20px; padding: 10px; background: #f0f0f1; border-left: 4px solid #72aee6;">';
    echo '<strong>Custom Taxonomy Filter Debug:</strong> ';
    echo '<a href="' . add_query_arg('test_ctf', '1') . '" target="_blank">Run System Test</a>';
    echo '</div>';
}
add_action('admin_footer', 'ctf_admin_footer_debug');
