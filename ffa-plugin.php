<?php
/**
 * Plugin Name: Frozen Factory Accounting (FFA) - Optimized
 * Description: High-performance accounting system with API, integrated with WooCommerce & SHRMS
 * Version: 3.0.0
 * Author: Abdulrahman Roston
 * Text Domain: ffa
 */

if (!defined('ABSPATH')) exit;

// Define constants
define('FFA_VERSION', '3.0.0');
define('FFA_PATH', plugin_dir_path(__FILE__));
define('FFA_URL', plugin_dir_url(__FILE__));
define('FFA_API_SECRET', 'ffa-secret-key-2025-v3');

// Autoload classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'FFA_') === 0) {
        $file = FFA_PATH . 'includes/class-' . strtolower(str_replace('_', '-', $class)) . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});

// Load core files
require_once FFA_PATH . 'includes/class-ffa-database.php';
require_once FFA_PATH . 'includes/class-ffa-admin.php';
require_once FFA_PATH . 'includes/class-ffa-api.php';
require_once FFA_PATH . 'includes/class-ffa-woocommerce.php';

// Load SHRMS Payroll integration (if SHRMS is active)
if (class_exists('SHRMS_Core')) {
    require_once FFA_PATH . 'includes/class-ffa-shrms-payroll.php';
}

// Activation hook
register_activation_hook(__FILE__, ['FFA_Database', 'activate']);

// Initialize plugin
add_action('plugins_loaded', function() {
    // Load text domain
    load_plugin_textdomain('ffa', false, dirname(plugin_basename(__FILE__)) . '/languages');
    
    // Initialize core classes
    FFA_Database::init();
    FFA_API::init();
    FFA_Admin::init();
    FFA_WooCommerce::init();
    
    // Initialize SHRMS integration (if loaded)
    if (class_exists('FFA_SHRMS_Payroll')) {
        FFA_SHRMS_Payroll::init();
    }
}, 10);
