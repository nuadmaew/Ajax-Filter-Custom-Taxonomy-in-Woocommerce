<?php
/**
 * Configuration file for Custom Taxonomy Filter Plugin
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Plugin settings and constants
define('CTF_TAXONOMY', 'car-brand');
define('CTF_DEFAULT_TOWBAR_IMAGE', CTF_URL . 'assets/images/default-towbar-image.jpg');
define('CTF_DEFAULT_CAR_IMAGE', CTF_URL . 'assets/images/default-car-image.jpg');
define('CTF_POST_TYPE', 'product');

// ACF field names
define('CTF_FIELD_YEAR_START', 'year_start');
define('CTF_FIELD_YEAR_END', 'year_end');
define('CTF_FIELD_TOWBAR_PRICE', 'towbar_price');
define('CTF_FIELD_ELECTRICAL_PRICE', 'electrical_price');
define('CTF_FIELD_RATING_KG', 'rating_kg');

// Default settings
$ctf_defaults = array(
    'posts_per_page' => -1,
    'hide_empty_terms' => false,
    'orderby' => 'name',
    'order' => 'ASC'
);

// Currency settings (for Thailand)
$ctf_currency_settings = array(
    'symbol' => '฿',
    'position' => 'before', // before or after
    'thousands_sep' => ',',
    'decimal_sep' => '.'
);
