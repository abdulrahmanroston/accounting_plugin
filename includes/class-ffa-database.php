<?php
/**
 * FFA Database Management & Core Functions
 * Handles all database operations, caching, and helper functions
 */

class FFA_Database {
    
    private static $cache = [];
    
    /**
     * Initialize
     */
    public static function init() {
        // Nothing to run on every load
    }
    
    /**
     * Plugin activation - Create tables ONCE
     */
    public static function activate() {
        global $wpdb;
        $charset_collate = $wpdb->get_charset_collate();
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        
        // Cashflow table with indexes
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_cashflow (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type ENUM('revenue','expense') NOT NULL,
            category_id BIGINT(20) UNSIGNED,
            amount DECIMAL(15,2) NOT NULL,
            description TEXT,
            related_id BIGINT(20) UNSIGNED,
            related_type VARCHAR(50),
            warehouse VARCHAR(50),
            payment_method VARCHAR(50),
            vault_id BIGINT(20) UNSIGNED,
            employee_id BIGINT(20) UNSIGNED,
            order_id INT,
            warehouse_id VARCHAR(100),
            previous_status VARCHAR(50),
            current_status VARCHAR(50),
            created_at DATETIME NOT NULL,
            created_by BIGINT(20) UNSIGNED,
            INDEX idx_type (type),
            INDEX idx_vault (vault_id),
            INDEX idx_order (order_id),
            INDEX idx_created (created_at),
            INDEX idx_employee (employee_id)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Expenses table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_expenses (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            type ENUM('fixed','variable') NOT NULL,
            category_id BIGINT(20) UNSIGNED,
            amount DECIMAL(15,2) NOT NULL,
            description TEXT,
            warehouse VARCHAR(50),
            vault_id BIGINT(20) UNSIGNED,
            employee_id BIGINT(20) UNSIGNED,
            created_at DATETIME NOT NULL,
            created_by BIGINT(20) UNSIGNED,
            INDEX idx_category (category_id),
            INDEX idx_vault (vault_id),
            INDEX idx_created (created_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Expense Categories
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_expense_categories (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            description TEXT,
            created_at DATETIME NOT NULL,
            UNIQUE KEY unique_name (name)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Vaults table with commission
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_vaults (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            payment_method VARCHAR(50) NOT NULL,
            balance DECIMAL(15,2) NOT NULL DEFAULT 0,
            commission_rate DECIMAL(5,2) DEFAULT 0.00,
            default_warehouse VARCHAR(100),
            employees TEXT,
            is_default TINYINT(1) DEFAULT 0,
            created_at DATETIME NOT NULL,
            UNIQUE KEY unique_warehouse_payment (default_warehouse, payment_method),
            INDEX idx_payment_method (payment_method)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Vendors table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_vendors (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            name VARCHAR(100) NOT NULL,
            phone VARCHAR(20),
            address TEXT,
            material_ids TEXT,
            payment_methods TEXT,
            balance DECIMAL(15,2) DEFAULT 0,
            created_at DATETIME NOT NULL,
            INDEX idx_name (name)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Purchases table
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_purchases (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            material_id BIGINT(20) UNSIGNED NOT NULL,
            vendor_id BIGINT(20) UNSIGNED NOT NULL,
            quantity INT NOT NULL,
            unit_cost DECIMAL(15,2) NOT NULL,
            total_cost DECIMAL(15,2) NOT NULL,
            vault_id BIGINT(20) UNSIGNED,
            employee_id BIGINT(20) UNSIGNED,
            payment_status ENUM('paid','pending') DEFAULT 'pending',
            created_at DATETIME NOT NULL,
            created_by BIGINT(20) UNSIGNED,
            INDEX idx_vendor (vendor_id),
            INDEX idx_material (material_id),
            INDEX idx_created (created_at)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Company Loans
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_company_loans (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            lender_name VARCHAR(100) NOT NULL,
            receiver_employee_id BIGINT(20) UNSIGNED NOT NULL,
            vault_id BIGINT(20) UNSIGNED NOT NULL,
            loan_amount DECIMAL(15,2) NOT NULL,
            repayment_type ENUM('lump_sum','installments') NOT NULL,
            installment_amount DECIMAL(15,2) DEFAULT 0,
            installment_period INT DEFAULT 0,
            installment_frequency ENUM('daily','weekly','monthly') DEFAULT 'monthly',
            total_paid DECIMAL(15,2) DEFAULT 0,
            remaining_balance DECIMAL(15,2) NOT NULL,
            loan_date DATE NOT NULL,
            due_date DATE,
            next_payment_date DATE,
            reason TEXT NOT NULL,
            status ENUM('active','completed','defaulted') DEFAULT 'active',
            created_at DATETIME NOT NULL,
            created_by BIGINT(20) UNSIGNED,
            INDEX idx_status (status),
            INDEX idx_receiver (receiver_employee_id),
            INDEX idx_next_payment (next_payment_date)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Employee Loans
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_employee_loans (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            employee_id BIGINT(20) UNSIGNED NOT NULL,
            vault_id BIGINT(20) UNSIGNED NOT NULL,
            loan_amount DECIMAL(15,2) NOT NULL,
            repayment_type ENUM('lump_sum','installments') NOT NULL,
            installment_amount DECIMAL(15,2) DEFAULT 0,
            installment_period INT DEFAULT 0,
            installment_frequency ENUM('daily','weekly','monthly') DEFAULT 'monthly',
            auto_deduct_from_salary TINYINT(1) DEFAULT 1,
            total_paid DECIMAL(15,2) DEFAULT 0,
            remaining_balance DECIMAL(15,2) NOT NULL,
            loan_date DATE NOT NULL,
            due_date DATE,
            next_payment_date DATE,
            reason TEXT NOT NULL,
            status ENUM('active','completed','suspended') DEFAULT 'active',
            created_at DATETIME NOT NULL,
            created_by BIGINT(20) UNSIGNED,
            INDEX idx_employee (employee_id),
            INDEX idx_status (status),
            INDEX idx_auto_deduct (auto_deduct_from_salary),
            INDEX idx_next_payment (next_payment_date)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Loan Payments
        $sql = "CREATE TABLE IF NOT EXISTS {$wpdb->prefix}ffa_loan_payments (
            id BIGINT(20) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            loan_id BIGINT(20) UNSIGNED NOT NULL,
            loan_type ENUM('company','employee') NOT NULL,
            payment_amount DECIMAL(15,2) NOT NULL,
            payment_date DATE NOT NULL,
            vault_id BIGINT(20) UNSIGNED,
            employee_id BIGINT(20) UNSIGNED,
            is_auto_deducted TINYINT(1) DEFAULT 0,
            salary_month VARCHAR(7),
            notes TEXT,
            created_at DATETIME NOT NULL,
            created_by BIGINT(20) UNSIGNED,
            INDEX idx_loan (loan_id, loan_type),
            INDEX idx_payment_date (payment_date),
            INDEX idx_salary_month (salary_month)
        ) $charset_collate;";
        dbDelta($sql);
        
        // Insert default categories
        self::insert_default_categories();
        
        // Set version
        update_option('ffa_db_version', FFA_VERSION);
    }
    
    /**
     * Insert default expense categories
     */
    private static function insert_default_categories() {
        global $wpdb;
        $table = $wpdb->prefix . 'ffa_expense_categories';
        
        $categories = ['Raw Materials', 'Utilities', 'Rent', 'Salaries', 'Marketing', 
                      'Transportation', 'Maintenance', 'Office Supplies', 'Commission'];
        
        foreach ($categories as $cat) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM $table WHERE name = %s", $cat
            ));
            
            if (!$exists) {
                $wpdb->insert($table, [
                    'name' => $cat,
                    'description' => "Default category: $cat",
                    'created_at' => current_time('mysql')
                ]);
            }
        }
    }
    
    // ============================================
    // CACHED GETTERS - للسرعة العالية
    // ============================================
    
    /**
     * Get all vaults (cached)
     */
    public static function get_vaults($force_refresh = false) {
        $cache_key = 'ffa_vaults_all';
        
        if (!$force_refresh && isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }
        
        $cached = get_transient($cache_key);
        if (false !== $cached && !$force_refresh) {
            self::$cache[$cache_key] = $cached;
            return $cached;
        }
        
        global $wpdb;
        $vaults = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_vaults ORDER BY name ASC");
        
        set_transient($cache_key, $vaults, HOUR_IN_SECONDS);
        self::$cache[$cache_key] = $vaults;
        
        return $vaults;
    }
    
    /**
     * Get employees (cached)
     */
    public static function get_employees($force_refresh = false) {
        $cache_key = 'ffa_employees_active';
        
        if (!$force_refresh && isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }
        
        $cached = get_transient($cache_key);
        if (false !== $cached && !$force_refresh) {
            self::$cache[$cache_key] = $cached;
            return $cached;
        }
        
        global $wpdb;
        $employees = $wpdb->get_results(
            "SELECT id, name, salary, role, phone FROM {$wpdb->prefix}shrms_employees 
             WHERE status = 'active' ORDER BY name ASC"
        );
        
        set_transient($cache_key, $employees, HOUR_IN_SECONDS);
        self::$cache[$cache_key] = $employees;
        
        return $employees;
    }
    
    /**
     * Get expense categories (cached)
     */
    public static function get_categories($force_refresh = false) {
        $cache_key = 'ffa_categories_all';
        
        if (!$force_refresh && isset(self::$cache[$cache_key])) {
            return self::$cache[$cache_key];
        }
        
        $cached = get_transient($cache_key);
        if (false !== $cached && !$force_refresh) {
            self::$cache[$cache_key] = $cached;
            return $cached;
        }
        
        global $wpdb;
        $categories = $wpdb->get_results(
            "SELECT * FROM {$wpdb->prefix}ffa_expense_categories ORDER BY name ASC"
        );
        
        set_transient($cache_key, $categories, DAY_IN_SECONDS);
        self::$cache[$cache_key] = $categories;
        
        return $categories;
    }
    
    /**
     * Clear all caches
     */
    public static function clear_cache() {
        delete_transient('ffa_vaults_all');
        delete_transient('ffa_employees_active');
        delete_transient('ffa_categories_all');
        self::$cache = [];
    }
    
    // ============================================
    // CORE FUNCTIONS - محسّنة للسرعة
    // ============================================
    
    /**
     * Find or create vault for warehouse/payment method
     */
    public static function find_vault($warehouse, $payment_method) {
        global $wpdb;
        $table = $wpdb->prefix . 'ffa_vaults';
        
        // Try exact match
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table 
             WHERE default_warehouse = %s AND payment_method = %s 
             ORDER BY is_default DESC LIMIT 1",
            $warehouse, $payment_method
        ));
        
        // Try default for payment method
        if (!$vault) {
            $vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table 
                 WHERE payment_method = %s AND is_default = 1 LIMIT 1",
                $payment_method
            ));
        }
        
        // Get any vault for payment method
        if (!$vault) {
            $vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM $table WHERE payment_method = %s LIMIT 1",
                $payment_method
            ));
        }
        
        // Create new vault
        if (!$vault) {
            $wpdb->insert($table, [
                'name' => ucfirst(str_replace('_', ' ', $payment_method)) . ' Vault',
                'payment_method' => $payment_method,
                'balance' => 0,
                'default_warehouse' => $warehouse,
                'employees' => json_encode([]),
                'is_default' => 1,
                'created_at' => current_time('mysql')
            ]);
            
            $vault = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE id = %d", $wpdb->insert_id));
            self::clear_cache(); // Clear cache when new vault created
        }
        
        return $vault;
    }
    
    /**
     * Update vault balance with commission (OPTIMIZED)
     */
    public static function update_vault_balance($vault_id, $amount, $description, $related_id, $related_type, $warehouse, $employee_id, $apply_commission = true) {
        global $wpdb;
        $table_vaults = $wpdb->prefix . 'ffa_vaults';
        
        $vault = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_vaults WHERE id = %d", $vault_id));
        if (!$vault) return false;
        
        $final_amount = $amount;
        
        // Apply commission only on positive amounts
        if ($apply_commission && $amount > 0 && $vault->commission_rate > 0) {
            $commission = ($amount * $vault->commission_rate) / 100;
            $final_amount = $amount - $commission;
            
            // Record commission
            self::record_commission($vault_id, $commission, $description, $related_id, $warehouse, $employee_id, $vault->payment_method);
        }
        
        // Update vault balance
        $new_balance = $vault->balance + $final_amount;
        $wpdb->update($table_vaults, ['balance' => $new_balance], ['id' => $vault_id]);
        
        self::clear_cache(); // Clear cache after balance update
        
        return $final_amount;
    }
    
    /**
     * Record commission as expense
     */
    private static function record_commission($vault_id, $commission, $description, $related_id, $warehouse, $employee_id, $payment_method) {
        global $wpdb;
        
        // Get commission category
        $cat_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE name = 'Commission'");
        
        // Insert expense
        $wpdb->insert($wpdb->prefix . 'ffa_expenses', [
            'type' => 'variable',
            'category_id' => $cat_id,
            'amount' => $commission,
            'description' => "Commission for: $description",
            'warehouse' => $warehouse,
            'vault_id' => $vault_id,
            'employee_id' => $employee_id,
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id() ?: 1
        ]);
        
        $expense_id = $wpdb->insert_id;
        
        // Record cashflow
        self::record_cashflow('expense', $cat_id, $commission, "Commission: $description", 
                            $expense_id, 'commission', $warehouse, $payment_method, $vault_id, $employee_id);
    }
    
    /**
     * Record cashflow (OPTIMIZED - no exceptions)
     */
    public static function record_cashflow($type, $category_id, $amount, $description, $related_id, $related_type, $warehouse, $payment_method, $vault_id, $employee_id) {
        global $wpdb;
        
        $result = $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
            'type' => $type,
            'category_id' => $category_id,
            'amount' => $amount,
            'description' => $description,
            'related_id' => $related_id,
            'related_type' => $related_type,
            'warehouse' => $warehouse,
            'payment_method' => $payment_method,
            'vault_id' => $vault_id,
            'employee_id' => $employee_id,
            'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id() ?: 1
        ]);
        
        if ($result === false) {
            error_log('FFA Cashflow Error: ' . $wpdb->last_error);
            return false;
        }
        
        return $wpdb->insert_id;
    }
    
    /**
     * Get profit margin (OPTIMIZED - one query only!)
     */
    public static function calculate_profit_margin($period = 'month') {
        global $wpdb;
        
        // Cache key
        $cache_key = "ffa_profit_margin_$period";
        $cached = get_transient($cache_key);
        if (false !== $cached) {
            return $cached;
        }
        
        // Date condition
        $date_query = $period == 'day' ? "AND DATE(p.post_date) = CURDATE()" : 
                     ($period == 'week' ? "AND YEARWEEK(p.post_date) = YEARWEEK(CURDATE())" : 
                     "AND DATE_FORMAT(p.post_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
        
        // ONE QUERY ONLY!
        $result = $wpdb->get_row("
            SELECT 
                SUM(pm_total.meta_value) as total_sales,
                SUM(COALESCE(pm_cost.meta_value, 0) * COALESCE(oim_qty.meta_value, 0)) as total_cost
            FROM {$wpdb->prefix}posts p
            JOIN {$wpdb->prefix}postmeta pm_total ON p.ID = pm_total.post_id AND pm_total.meta_key = '_order_total'
            JOIN {$wpdb->prefix}woocommerce_order_items oi ON p.ID = oi.order_id AND oi.order_item_type = 'line_item'
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_prod ON oi.order_item_id = oim_prod.order_item_id AND oim_prod.meta_key = '_product_id'
            LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta oim_qty ON oi.order_item_id = oim_qty.order_item_id AND oim_qty.meta_key = '_qty'
            LEFT JOIN {$wpdb->prefix}postmeta pm_cost ON oim_prod.meta_value = pm_cost.post_id AND pm_cost.meta_key = '_production_cost'
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            $date_query
        ");
        
        $profit_margin = $result->total_sales > 0 ? 
            ((($result->total_sales - $result->total_cost) / $result->total_sales) * 100) : 0;
        
        // Cache for 5 minutes
        set_transient($cache_key, $profit_margin, 5 * MINUTE_IN_SECONDS);
        
        return $profit_margin;
    }
    
    /**
     * Get sales report (OPTIMIZED)
     */
    public static function get_sales_report($period = 'month') {
        global $wpdb;
        
        $cache_key = "ffa_sales_report_$period";
        $cached = get_transient($cache_key);
        if (false !== $cached) {
            return $cached;
        }
        
        $date_query = $period == 'day' ? "AND DATE(p.post_date) = CURDATE()" : 
                     ($period == 'week' ? "AND YEARWEEK(p.post_date) = YEARWEEK(CURDATE())" : 
                     "AND DATE_FORMAT(p.post_date, '%Y-%m') = DATE_FORMAT(NOW(), '%Y-%m')");
        
        $by_warehouse = $wpdb->get_results("
            SELECT pm.meta_value AS warehouse, SUM(pm2.meta_value) AS total
            FROM {$wpdb->prefix}posts p
            JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_selected_warehouse'
            JOIN {$wpdb->prefix}postmeta pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            $date_query
            GROUP BY pm.meta_value
        ");
        
        $total = $wpdb->get_var("
            SELECT SUM(pm.meta_value)
            FROM {$wpdb->prefix}posts p
            JOIN {$wpdb->prefix}postmeta pm ON p.ID = pm.post_id AND pm.meta_key = '_order_total'
            WHERE p.post_type = 'shop_order'
            AND p.post_status = 'wc-completed'
            $date_query
        ") ?: 0;
        
        $result = [
            'by_warehouse' => $by_warehouse ?: [],
            'total' => floatval($total)
        ];
        
        set_transient($cache_key, $result, 5 * MINUTE_IN_SECONDS);
        
        return $result;
    }
    
    /**
     * JWT Token generation
     */
    public static function generate_token($employee_id, $role) {
        $payload = [
            'sub' => $employee_id,
            'role' => $role,
            'iat' => time(),
            'exp' => time() + (60 * 60 * 24 * 90) // 30 days
        ];
        $payload_str = base64_encode(json_encode($payload));
        $signature = hash_hmac('sha256', $payload_str, FFA_API_SECRET);
        
        return $payload_str . '.' . $signature;
    }
    
    /**
     * JWT Token validation
     */
    public static function validate_token($token) {
        if (empty($token)) {
            return new WP_Error('missing_token', 'Token missing', ['status' => 401]);
        }
        
        $parts = explode('.', $token);
        if (count($parts) !== 2) {
            return new WP_Error('invalid_token', 'Invalid format', ['status' => 403]);
        }
        
        list($payload_str, $signature) = $parts;
        $expected = hash_hmac('sha256', $payload_str, FFA_API_SECRET);
        
        if ($signature !== $expected) {
            return new WP_Error('invalid_token', 'Invalid signature', ['status' => 403]);
        }
        
        $payload = json_decode(base64_decode($payload_str));
        if (!$payload || !isset($payload->sub) || !isset($payload->exp) || !isset($payload->role)) {
            return new WP_Error('invalid_token', 'Invalid data', ['status' => 403]);
        }
        
        if ($payload->exp < time()) {
            return new WP_Error('expired_token', 'Token expired', ['status' => 403]);
        }
        
        return $payload;
    }

    /**
     * Get available WooCommerce payment methods (dynamic)
     * @return array
     */
    public static function get_wc_payment_methods() {
        // Check if WooCommerce is active
        if (!function_exists('WC')) {
            // Fallback if WooCommerce not active
            return [
                'cash' => 'Cash',
                'bank_transfer' => 'Bank Transfer',
            ];
        }
        
        $payment_methods = [];
        
        // Get all available payment gateways
        $gateways = WC()->payment_gateways->get_available_payment_gateways();
        
        if (!empty($gateways)) {
            foreach ($gateways as $gateway) {
                // Use gateway ID as value and title as label
                $payment_methods[$gateway->id] = $gateway->get_title();
            }
        }
        
        // If no gateways found, return defaults
        if (empty($payment_methods)) {
            $payment_methods = [
                'cod' => 'Cash on Delivery',
                'bacs' => 'Bank Transfer',
                'cheque' => 'Check',
            ];
        }
        
        return $payment_methods;
    }



}
