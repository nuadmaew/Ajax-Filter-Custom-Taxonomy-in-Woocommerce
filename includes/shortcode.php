<?php
/**
 * Shortcode for Custom Taxonomy Filter Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create shortcode for the taxonomy filter
 * Usage: [ctf_filter]
 */
function ctf_filter_shortcode($atts) {
    // Parse shortcode attributes
    $atts = shortcode_atts(array(
        'title' => '🔍 Find Your Product',
        'class' => 'ctf-filter'
    ), $atts);
    
    // Start output buffering
    ob_start();
    ?>
    <div class="<?php echo esc_attr($atts['class']); ?>">
        <h3><?php echo esc_html($atts['title']); ?></h3>
        
        <div class="filter-row">
            <select id="brand-filter" class="filter-select">
                <option value="">Loading brands...</option>
            </select>
            
            <select id="model-filter" class="filter-select" disabled>
                <option value="">Select brand first</option>
            </select>
            
            <select id="year-filter" class="filter-select" disabled>
                <option value="">Select model first</option>
            </select>
            
            <button id="search-product" class="search-button" disabled>
                🔍 Find Product
            </button>
        </div>
        
        <div id="loading" class="loading-indicator" style="display:none;">
            ⏳ Searching...
        </div>
        
        <div id="error-message" class="error-message" style="display:none;"></div>
    </div>
    
    <?php echo ctf_render_modal(); ?>
    
    <?php
    return ob_get_clean();
}
add_shortcode('ctf_filter', 'ctf_filter_shortcode');

/**
 * Render the modal HTML structure
 */
function ctf_render_modal() {
    ob_start();
    ?>
    <!-- Product Modal -->
    <div id="ctf-modal" class="ctf-modal">
        <div class="modal-content">
            <span class="close">&times;</span>
            <div id="modal-body" class="modal-body">
                <!-- Modal content will be populated by JavaScript -->
            </div>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Generate modal content HTML (called by JavaScript)
 */
function ctf_generate_modal_content($data) {
    ob_start();
    ?>
    <div class="modal-header">
        <h3><?php echo esc_html($data['brand_name'] . ' ' . $data['model_name'] . ' (' . $data['year_range'] . ')'); ?></h3>
    </div>
    
    <div class="modal-images">
        <?php if (!empty($data['car_image'])): ?>
            <div class="car-image">
                <img src="<?php echo esc_url($data['car_image']); ?>" alt="Car Image" class="modal-car-image">
            </div>
        <?php endif; ?>
        
        <div class="product-image">
            <img src="<?php echo esc_url($data['towbar_image']); ?>" alt="Product" class="modal-product-image">
        </div>
    </div>
    
    <div class="modal-details">
        <div class="price-section">
            <div class="price-item">
                <span class="price-label">Towbar Price:</span>
                <span class="price-value"><?php echo ctf_format_price($data['towbar_price']); ?></span>
            </div>
            
            <div class="price-item">
                <span class="price-label">Electrical Price:</span>
                <span class="price-value"><?php echo ctf_format_price($data['electrical_price']); ?></span>
            </div>
            
            <div class="price-item total-price">
                <span class="price-label">Total Price:</span>
                <span class="price-value"><?php echo ctf_format_price($data['total_price']); ?></span>
            </div>
        </div>
        
        <?php if ($data['rating_kg'] > 0): ?>
            <div class="specifications">
                <div class="spec-item">
                    <span class="spec-label">Towing Capacity:</span>
                    <span class="spec-value"><?php echo esc_html($data['rating_kg']); ?> kg</span>
                </div>
            </div>
        <?php endif; ?>
        
        <div class="modal-actions">
            <a href="<?php echo esc_url($data['product_url']); ?>" class="view-product-btn" target="_blank">
                View Product Details
            </a>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
