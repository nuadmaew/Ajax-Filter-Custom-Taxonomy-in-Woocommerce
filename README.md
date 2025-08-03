# Custom Taxonomy Filter Plugin

A WordPress plugin for filtering and displaying WooCommerce products with hierarchical taxonomy support and AJAX functionality.

## 📋 Overview

This plugin provides an interactive filter system for WooCommerce products, designed to work with any custom hierarchical taxonomy. Users can filter products by brand, model, and year range (or any 3-level hierarchy) to find compatible products.

## 🚀 Features

- **AJAX-powered filtering** - No page reloads
- **Hierarchical taxonomy support** - Parent → Child → Grandchild filtering  
- **Product modal display** - Shows product details in a popup
- **Mobile responsive** - Works on all devices
- **Price calculation** - Displays component + total pricing
- **Debug system** - Built-in testing and debugging tools
- **Generic & reusable** - Works with any custom taxonomy

## 📁 File Structure

```
custom-taxonomy-filter/
├── custom-taxonomy-filter.php        # Main plugin file
├── includes/
│   ├── config.php                    # Configuration constants
│   ├── helpers.php                   # Utility functions  
│   ├── ajax-handlers.php             # AJAX backend logic
│   ├── shortcode.php                 # Frontend HTML structure
│   └── debug.php                     # Debug & testing functions
├── assets/
│   ├── css/
│   │   └── custom-taxonomy-filter.css # Plugin styles
│   └── js/
│       └── custom-taxonomy-filter.js  # JavaScript functionality
└── README.md                         # This file
```

## 🔧 Requirements

- **WordPress** 5.0+
- **WooCommerce** 3.0+
- **Advanced Custom Fields (ACF)** 5.0+
- **PHP** 7.4+

## ⚙️ Installation

1. **Upload the plugin** to `/wp-content/plugins/custom-taxonomy-filter/`
2. **Activate the plugin** through WordPress admin
3. **Set up taxonomy and fields** (see Configuration section)
4. **Add the shortcode** `[ctf_filter]` to any page

## 🛠️ Configuration

### 1. Create Custom Hierarchy Taxonomy

The plugin expects a custom hierarchical taxonomy. Default: `car-brand` with:
- **Parent terms** = Brands (Toyota, Honda, etc.)
- **Child terms** = Models (Camry, Civic, etc.)

To use different taxonomy, edit `CTF_TAXONOMY` in `includes/config.php`:
```php
define('CTF_TAXONOMY', 'your-taxonomy-name');
```

### 2. Set up ACF Fields

Add these custom fields to your WooCommerce products:

| Field Name | Field Type | Description |
|------------|------------|-------------|
| `year_start` | Number | Starting year for compatibility |
| `year_end` | Number | Ending year for compatibility |
| `towbar_price` | Number | Price of towbar component |
| `electrical_price` | Number | Price of electrical component |
| `rating_kg` | Number | Towing capacity in kg |

### 3. Configure Products

1. **Assign car brands/models** to products using the `car-brand` taxonomy
2. **Fill in ACF fields** for each product
3. **Add product images** (featured image + gallery)

## 📝 Usage

### Basic Shortcode

```php
[ctf_filter]
```

### With Custom Title

```php
[ctf_filter title="Find Your Perfect Product" class="my-custom-class"]
```

### Manual Function Call

```php
<?php echo do_shortcode('[ctf_filter]'); ?>
```

## 🔌 AJAX Endpoints

The plugin registers these AJAX actions:

| Action | Description | Parameters |
|--------|-------------|------------|
| `get_car_brands` | Get all car brands | `nonce` |
| `get_models_by_brand` | Get models for a brand | `nonce`, `brand_id` |
| `get_years_by_model` | Get years for a model | `nonce`, `model_id` |
| `get_product_details` | Get product info for modal | `nonce`, `brand_id`, `model_id`, `year_start`, `year_end` |

## 🧪 Testing & Debug

### System Test

Add `?test_ctf=1` to any URL (admin users only) to run comprehensive tests:

```
https://yoursite.com/?test_ctf=1
```

This will check:
- ✅ Plugin constants and file structure
- ✅ Required plugins (WooCommerce, ACF)
- ✅ Taxonomy and terms setup
- ✅ Products and ACF fields
- ✅ AJAX endpoints registration
- ✅ Asset files existence

### Debug Logging

When `WP_DEBUG` is enabled, the plugin logs:
- AJAX requests and responses
- Error messages
- Performance data

View logs in `/wp-content/debug.log`

## 🎨 Customization

### CSS Customization

Override styles by adding to your theme's CSS:

```css
.towbar-filter {
    /* Your custom styles */
}

.towbar-modal .modal-content {
    /* Custom modal styles */
}
```

### JavaScript Hooks

```javascript
// Reload brands programmatically
window.towbarReloadBrands();

// Custom event handling
jQuery(document).on('towbar:modal:opened', function(e, data) {
    console.log('Modal opened with data:', data);
});
```

### PHP Filters

```php
// Customize default currency symbol
add_filter('towbar_currency_symbol', function($symbol) {
    return '; // Change from ฿ to $
});

// Modify AJAX response data
add_filter('towbar_product_details', function($data, $product_id) {
    $data['custom_field'] = get_post_meta($product_id, 'custom_meta', true);
    return $data;
}, 10, 2);
```

## 🎯 Use Cases & Examples

This plugin is flexible and can be used for various product filtering scenarios:

### **Automotive Parts**
- Brand → Model → Year
- Example: Toyota → Camry → 2018-2022

### **Electronics**  
- Brand → Series → Model
- Example: Apple → iPhone → iPhone 14

### **Fashion**
- Brand → Category → Size
- Example: Nike → Sneakers → Size 42

### **Real Estate**
- Location → Type → Price Range
- Example: Bangkok → Condo → 2-5M THB

### **Software/Licenses**
- Vendor → Product → Version
- Example: Microsoft → Office → 365

## 🚀 Performance

- **Conditional loading** - Assets only load on relevant pages
- **Efficient queries** - Optimized database queries
- **Caching friendly** - Works with WordPress caching plugins
- **Minified assets** - Compressed CSS/JS for faster loading

## 🐛 Troubleshooting

### Common Issues

**Problem:** "Security check failed" error
**Solution:** Clear cache and ensure AJAX URL is correct

**Problem:** No brands loading
**Solution:** Check if your custom taxonomy exists and has parent terms

**Problem:** Modal not opening  
**Solution:** Check browser console for JavaScript errors

**Problem:** Styling issues
**Solution:** Check for CSS conflicts with theme

### Debug Steps

1. **Enable WP_DEBUG** in wp-config.php
2. **Run system test** with `?test_ctf=1`
3. **Check error logs** in `/wp-content/debug.log`
4. **Test AJAX endpoints** directly via browser
5. **Verify database** - Check products, terms, and meta fields

## 📈 Development

### Local Development

```bash
# Clone repository
git clone [your-repo-url] custom-taxonomy-filter

# Install in WordPress
cp -r custom-taxonomy-filter /path/to/wordpress/wp-content/plugins/

# Activate plugin
wp plugin activate custom-taxonomy-filter
```

### File Modification Workflow

1. **Backend changes** - Edit `includes/ajax-handlers.php`
2. **Frontend changes** - Edit `assets/js/custom-taxonomy-filter.js`
3. **Styling changes** - Edit `assets/css/custom-taxonomy-filter.css`
4. **Structure changes** - Edit `includes/shortcode.php`

### Git Workflow

```bash
git add includes/ajax-handlers.php     # Backend changes
git add assets/js/custom-taxonomy-filter.js     # Frontend changes  
git add assets/css/custom-taxonomy-filter.css   # Style changes
git commit -m "Update AJAX handlers and styling"
git push origin main
```

## 📄 License

This project is licensed under the GPL v2 or later.

## 👨‍💻 Author

**Your Name**  
📧 your.email@example.com  
🌐 [Your Website](https://yourwebsite.com)

## 🤝 Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Test thoroughly
5. Submit a pull request

## 📚 Changelog

### Version 1.0.0
- Initial release
- AJAX filtering system
- Modal product display
- Responsive design
- Debug system
- Generic taxonomy support

---

**Need help?** Check the debug system first with `?test_ctf=1` or review the troubleshooting section above.
