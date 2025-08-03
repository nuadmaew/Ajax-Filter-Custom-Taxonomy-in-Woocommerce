/**
 * Custom Taxonomy Filter JavaScript
 * Version: 1.0.0
 */

jQuery(document).ready(function($) {
    'use strict';
    
    console.log('Custom Taxonomy Filter script loaded');
    console.log('AJAX URL:', ctf_ajax.ajax_url);
    
    // Cache DOM elements
    const $brandFilter = $('#brand-filter');
    const $modelFilter = $('#model-filter');
    const $yearFilter = $('#year-filter');
    const $searchButton = $('#search-product');
    const $loading = $('#loading');
    const $errorMessage = $('#error-message');
    const $modal = $('#ctf-modal');
    const $modalBody = $('#modal-body');
    const $closeBtn = $('.close');
    
    // Initialize the filter
    init();
    
    /**
     * Initialize the taxonomy filter
     */
    function init() {
        bindEvents();
        loadBrands();
    }
    
    /**
     * Bind all event handlers
     */
    function bindEvents() {
        $brandFilter.on('change', handleBrandChange);
        $modelFilter.on('change', handleModelChange);
        $yearFilter.on('change', handleYearChange);
        $searchButton.on('click', handleSearch);
        $closeBtn.on('click', closeModal);
        $modal.on('click', handleModalBackdropClick);
        
        // Keyboard accessibility
        $(document).on('keydown', handleKeydown);
    }
    
    /**
     * Load all car brands on page load
     */
    function loadBrands() {
        showLoading(true);
        hideError();
        
        makeAjaxRequest('get_car_brands', {}, function(response) {
            if (response.success) {
                populateBrandDropdown(response.data);
                $brandFilter.prop('disabled', false);
            } else {
                showError('Failed to load brands: ' + (response.data || 'Unknown error'));
            }
            showLoading(false);
        });
    }
    
    /**
     * Handle brand selection change
     */
    function handleBrandChange() {
        const brandId = $brandFilter.val();
        
        resetDependentDropdowns(['model', 'year']);
        disableElements([$modelFilter, $yearFilter, $searchButton]);
        
        if (brandId) {
            showLoading(true);
            hideError();
            
            makeAjaxRequest('get_models_by_brand', { brand_id: brandId }, function(response) {
                if (response.success) {
                    populateModelDropdown(response.data);
                    $modelFilter.prop('disabled', false);
                } else {
                    showError('Failed to load models: ' + (response.data || 'Unknown error'));
                }
                showLoading(false);
            });
        }
    }
    
    /**
     * Handle model selection change
     */
    function handleModelChange() {
        const modelId = $modelFilter.val();
        
        resetDependentDropdowns(['year']);
        disableElements([$yearFilter, $searchButton]);
        
        if (modelId) {
            showLoading(true);
            hideError();
            
            makeAjaxRequest('get_years_by_model', { model_id: modelId }, function(response) {
                if (response.success) {
                    populateYearDropdown(response.data);
                    $yearFilter.prop('disabled', false);
                } else {
                    showError('Failed to load years: ' + (response.data || 'Unknown error'));
                }
                showLoading(false);
            });
        }
    }
    
    /**
     * Handle year selection change
     */
    function handleYearChange() {
        const yearRange = $yearFilter.val();
        $searchButton.prop('disabled', !yearRange);
    }
    
    /**
     * Handle search button click
     */
    function handleSearch() {
        const brandId = $brandFilter.val();
        const modelId = $modelFilter.val();
        const yearRange = $yearFilter.val();
        
        // Validate selections
        if (!brandId || !modelId || !yearRange) {
            showError('Please select all options before searching');
            return;
        }
        
        const years = yearRange.split('-');
        if (years.length !== 2) {
            showError('Invalid year range format');
            return;
        }
        
        const yearStart = parseInt(years[0]);
        const yearEnd = parseInt(years[1]);
        
        if (isNaN(yearStart) || isNaN(yearEnd)) {
            showError('Invalid year values');
            return;
        }
        
        showLoading(true);
        hideError();
        
        const searchData = {
            brand_id: brandId,
            model_id: modelId,
            year_start: yearStart,
            year_end: yearEnd
        };
        
        makeAjaxRequest('get_product_details', searchData, function(response) {
            showLoading(false);
            
            if (response.success) {
                showModal(response.data);
            } else {
                showError('Product not found: ' + (response.data || 'No matching product available'));
            }
        });
    }
    
    /**
     * Make AJAX request with error handling
     */
    function makeAjaxRequest(action, data, callback) {
        const requestData = {
            action: action,
            nonce: ctf_ajax.nonce,
            ...data
        };
        
        $.post(ctf_ajax.ajax_url, requestData)
            .done(function(response) {
                callback(response);
            })
            .fail(function(xhr, status, error) {
                console.error('AJAX request failed:', { action, status, error, xhr });
                showError('Connection error: ' + error);
                showLoading(false);
            });
    }
    
    /**
     * Populate brand dropdown
     */
    function populateBrandDropdown(brands) {
        $brandFilter.empty().append('<option value="">Select Brand</option>');
        
        $.each(brands, function(index, brand) {
            $brandFilter.append(`<option value="${brand.id}">${escapeHtml(brand.name)}</option>`);
        });
        
        console.log(`Loaded ${brands.length} brands`);
    }
    
    /**
     * Populate model dropdown
     */
    function populateModelDropdown(models) {
        $modelFilter.empty().append('<option value="">Select Model</option>');
        
        $.each(models, function(index, model) {
            $modelFilter.append(`<option value="${model.id}">${escapeHtml(model.name)}</option>`);
        });
        
        console.log(`Loaded ${models.length} models`);
    }
    
    /**
     * Populate year dropdown
     */
    function populateYearDropdown(years) {
        $yearFilter.empty().append('<option value="">Select Year</option>');
        
        $.each(years, function(index, year) {
            const value = `${year.start}-${year.end}`;
            $yearFilter.append(`<option value="${value}">${escapeHtml(year.display)}</option>`);
        });
        
        console.log(`Loaded ${years.length} year ranges`);
    }
    
    /**
     * Reset dependent dropdowns
     */
    function resetDependentDropdowns(dropdowns) {
        const resetOptions = {
            model: 'Select Brand First',
            year: 'Select Model First'
        };
        
        dropdowns.forEach(function(dropdown) {
            const $dropdown = dropdown === 'model' ? $modelFilter : $yearFilter;
            const optionText = resetOptions[dropdown];
            $dropdown.empty().append(`<option value="">${optionText}</option>`);
        });
    }
    
    /**
     * Disable multiple elements
     */
    function disableElements(elements) {
        elements.forEach(function($element) {
            $element.prop('disabled', true);
        });
    }
    
    /**
     * Show/hide loading indicator
     */
    function showLoading(show) {
        $loading.toggle(show);
    }
    
    /**
     * Show error message
     */
    function showError(message) {
        $errorMessage.text(message).show();
    }
    
    /**
     * Hide error message
     */
    function hideError() {
        $errorMessage.hide();
    }
    
    /**
     * Show modal with product data
     */
    function showModal(data) {
        const modalHtml = generateModalContent(data);
        $modalBody.html(modalHtml);
        $modal.show();
        
        // Focus management for accessibility
        $modal.focus();
        
        console.log('Modal opened for product:', data.product_id);
    }
    
    /**
     * Generate modal content HTML
     */
    function generateModalContent(data) {
        let html = `<div class="modal-header">
            <h3>${escapeHtml(data.brand_name)} ${escapeHtml(data.model_name)} (${escapeHtml(data.year_range)})</h3>
        </div>`;
        
        // Images section
        html += '<div class="modal-images">';
        
        if (data.car_image) {
            html += `<div class="car-image">
                <img src="${escapeHtml(data.car_image)}" alt="Car Image" class="modal-car-image">
            </div>`;
        }
        
        if (data.towbar_image) {
            html += `<div class="product-image">
                <img src="${escapeHtml(data.towbar_image)}" alt="Product" class="modal-product-image">
            </div>`;
        }
        
        html += '</div>';
        
        // Details section
        html += '<div class="modal-details">';
        
        // Price section
        html += `<div class="price-section">
            <div class="price-item">
                <span class="price-label">Towbar Price:</span>
                <span class="price-value">฿${formatNumber(data.towbar_price)}</span>
            </div>
            <div class="price-item">
                <span class="price-label">Electrical Price:</span>
                <span class="price-value">฿${formatNumber(data.electrical_price)}</span>
            </div>
            <div class="price-item total-price">
                <span class="price-label">Total Price:</span>
                <span class="price-value">฿${formatNumber(data.total_price)}</span>
            </div>
        </div>`;
        
        // Specifications
        if (data.rating_kg > 0) {
            html += `<div class="specifications">
                <div class="spec-item">
                    <span class="spec-label">Towing Capacity:</span>
                    <span class="spec-value">${escapeHtml(data.rating_kg)} kg</span>
                </div>
            </div>`;
        }
        
        // Actions
        html += `<div class="modal-actions">
            <a href="${escapeHtml(data.product_url)}" class="view-product-btn" target="_blank">
                View Product Details
            </a>
        </div>`;
        
        html += '</div>';
        
        return html;
    }
    
    /**
     * Close modal
     */
    function closeModal() {
        $modal.hide();
        console.log('Modal closed');
    }
    
    /**
     * Handle modal backdrop click
     */
    function handleModalBackdropClick(e) {
        if (e.target === this) {
            closeModal();
        }
    }
    
    /**
     * Handle keyboard events
     */
    function handleKeydown(e) {
        // Close modal with Escape key
        if (e.key === 'Escape' && $modal.is(':visible')) {
            closeModal();
        }
    }
    
    /**
     * Utility: Escape HTML to prevent XSS
     */
    function escapeHtml(text) {
        if (typeof text !== 'string') return text;
        
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
    
    /**
     * Utility: Format number with thousands separator
     */
    function formatNumber(num) {
        if (typeof num !== 'number') return '0';
        return num.toLocaleString();
    }
});

/**
 * Global function to reload brands (for external use)
 */
window.ctfReloadBrands = function() {
    jQuery('#brand-filter').trigger('ctf:reload');
};
