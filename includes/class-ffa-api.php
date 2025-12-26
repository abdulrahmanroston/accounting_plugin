<?php
/**
 * FFA REST API - High Performance
 * All API endpoints optimized for speed
 */

class FFA_API {
    
    /**
     * Initialize API
     */
    public static function init() {
        add_action('rest_api_init', [__CLASS__, 'register_routes']);
    }
    
    /**
     * Register all API routes
     */
    public static function register_routes() {
        $namespace = 'ffa/v1';
        
        // ============================================
        // AUTHENTICATION
        // ============================================
        register_rest_route($namespace, '/auth/login', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'login'],
            'permission_callback' => '__return_true',
        ]);
        
        // ============================================
        // DASHBOARD
        // ============================================
        register_rest_route($namespace, '/dashboard', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_dashboard'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
        
        // ============================================
        // SETTINGS
        // ============================================
        register_rest_route($namespace, '/settings', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_settings'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'update_settings'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // EXPENSES
        // ============================================
        register_rest_route($namespace, '/expenses', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_expenses'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_expense'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/expenses/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_expense'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [__CLASS__, 'update_expense'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_expense'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // EXPENSE CATEGORIES
        // ============================================
        register_rest_route($namespace, '/expense-categories', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_categories'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_category'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/expense-categories/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_category'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [__CLASS__, 'update_category'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_category'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // VAULTS
        // ============================================
        register_rest_route($namespace, '/vaults', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_vaults'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_vault'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/vaults/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_vault'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [__CLASS__, 'update_vault'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_vault'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/vaults/transfer', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'transfer_vault'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        register_rest_route($namespace, '/vaults/add-funds', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'add_funds_to_vault'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        
        // ============================================
        // VENDORS
        // ============================================
        register_rest_route($namespace, '/vendors', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_vendors'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_vendor'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/vendors/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_vendor'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [__CLASS__, 'update_vendor'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_vendor'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // PURCHASES
        // ============================================
        register_rest_route($namespace, '/purchases', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_purchases'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_purchase'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/purchases/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_purchase'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => ['PUT', 'PATCH'],
                'callback' => [__CLASS__, 'update_purchase'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_purchase'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // PAYROLL
        // ============================================
        register_rest_route($namespace, '/payroll', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_payroll'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
        
        register_rest_route($namespace, '/payroll/pay', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'pay_salary'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
        
        // ============================================
        // REPORTS
        // ============================================
        register_rest_route($namespace, '/reports', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_reports'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
        
        // ============================================
        // CASHFLOW
        // ============================================
        register_rest_route($namespace, '/cashflow', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_cashflow'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);
        
        // ============================================
        // COMPANY LOANS
        // ============================================
        register_rest_route($namespace, '/company-loans', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_company_loans'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_company_loan'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/company-loans/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_company_loan'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_company_loan'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // EMPLOYEE LOANS
        // ============================================
        register_rest_route($namespace, '/employee-loans', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_employee_loans'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_employee_loan'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        register_rest_route($namespace, '/employee-loans/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_employee_loan'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'DELETE',
                'callback' => [__CLASS__, 'delete_employee_loan'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // LOAN PAYMENTS
        // ============================================
        register_rest_route($namespace, '/loan-payments', [
            [
                'methods' => 'GET',
                'callback' => [__CLASS__, 'get_loan_payments'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
            [
                'methods' => 'POST',
                'callback' => [__CLASS__, 'create_loan_payment'],
                'permission_callback' => [__CLASS__, 'check_permission'],
            ],
        ]);
        
        // ============================================
        // SALARY DEDUCTIONS (Auto)
        // ============================================
        register_rest_route($namespace, '/process-salary-deductions', [
            'methods' => 'POST',
            'callback' => [__CLASS__, 'process_salary_deductions'],
            'permission_callback' => [__CLASS__, 'check_permission'],
        ]);

        // Add this with other routes
        register_rest_route($namespace, '/payment-methods', [
            'methods' => 'GET',
            'callback' => [__CLASS__, 'get_payment_methods'],
            'permission_callback' => '__return_true', // Public endpoint
        ]);

    }
    
    // ============================================
    // PERMISSION CHECK
    // ============================================
    public static function check_permission($request) {
        $token = $request->get_header('authorization');
        if (!$token) {
            return new WP_Error('missing_token', 'Authorization required', ['status' => 401]);
        }
        
        $token = str_replace('Bearer ', '', $token);
        $user = FFA_Database::validate_token($token);
        
        if (is_wp_error($user)) {
            return $user;
        }
        
        $allowed_roles = ['admin', 'super_admin', 'superadmin', 'super admin'];
        if (!in_array(strtolower($user->role), $allowed_roles)) {
            return new WP_Error('insufficient_permissions', 'Admin access required', ['status' => 403]);
        }
        
        return true;
    }
    
    // ============================================
    // AUTHENTICATION
    // ============================================
    public static function login($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $phone = sanitize_text_field($params['phone'] ?? '');
        $password = $params['password'] ?? '';
        
        if (empty($phone) || empty($password)) {
            return new WP_Error('invalid_input', 'Phone and password required', ['status' => 400]);
        }
        
        $table = $wpdb->prefix . 'shrms_employees';
        $employee = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table WHERE phone = %s", $phone));
        
        if (!$employee) {
            return new WP_Error('invalid_credentials', 'Invalid credentials', ['status' => 401]);
        }
        
        // Verify password (both methods)
        $password_verified = password_verify($password, $employee->password) || 
                           (function_exists('wp_check_password') && wp_check_password($password, $employee->password));
        
        if (!$password_verified) {
            return new WP_Error('invalid_credentials', 'Invalid credentials', ['status' => 401]);
        }
        
        // Check permissions
        $allowed_roles = ['admin', 'super_admin', 'superadmin', 'super admin'];
        if (!in_array(strtolower($employee->role), $allowed_roles)) {
            return new WP_Error('insufficient_permissions', 'Insufficient permissions', ['status' => 403]);
        }
        
        $token = FFA_Database::generate_token($employee->id, $employee->role);
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Login successful',
            'data' => [
                'token' => $token,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'phone' => $employee->phone,
                    'role' => $employee->role
                ]
            ]
        ], 200);
    }
    
    // ============================================
    // DASHBOARD
    // ============================================
    public static function get_dashboard($request) {
        global $wpdb;
        
        // Get main vault balance
        $main_vault_id = get_option('ffa_main_vault', 0);
        $cash_balance = 0;
        if ($main_vault_id > 0) {
            $cash_balance = $wpdb->get_var($wpdb->prepare(
                "SELECT balance FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $main_vault_id
            )) ?: 0;
        }
        
        // Get sales reports (cached)
        $daily_sales = FFA_Database::get_sales_report('day');
        $weekly_sales = FFA_Database::get_sales_report('week');
        $monthly_sales = FFA_Database::get_sales_report('month');
        
        // Get profit margin (cached)
        $profit_margin = FFA_Database::calculate_profit_margin('month');
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => [
                'cash_balance' => floatval($cash_balance),
                'daily_sales' => $daily_sales,
                'weekly_sales' => $weekly_sales,
                'monthly_sales' => $monthly_sales,
                'profit_margin' => $profit_margin,
                'currency' => get_option('ffa_currency', 'EGP')
            ]
        ], 200);
    }
    
    // ============================================
    // SETTINGS
    // ============================================
    public static function get_settings($request) {
        $vaults = FFA_Database::get_vaults();
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => [
                'currency' => get_option('ffa_currency', 'EGP'),
                'main_vault' => get_option('ffa_main_vault', 0),
                'report_emails' => get_option('ffa_report_emails', []),
                'report_types' => get_option('ffa_report_types', []),
                'available_vaults' => $vaults
            ]
        ], 200);
    }
    
    public static function update_settings($request) {
        $params = $request->get_json_params();
        
        if (isset($params['currency'])) {
            update_option('ffa_currency', sanitize_text_field($params['currency']));
        }
        
        if (isset($params['main_vault'])) {
            update_option('ffa_main_vault', intval($params['main_vault']));
        }
        
        if (isset($params['report_emails'])) {
            $emails = array_map('sanitize_email', array_filter($params['report_emails']));
            update_option('ffa_report_emails', $emails);
        }
        
        if (isset($params['report_types'])) {
            $types = array_map('sanitize_text_field', $params['report_types']);
            update_option('ffa_report_types', $types);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Settings updated successfully'
        ], 200);
    }
    
    // ============================================
    // EXPENSES - CRUD
    // ============================================
    public static function get_expenses($request) {
        global $wpdb;
        
        $page = max(1, intval($request->get_param('page') ?: 1));
        $per_page = max(1, min(100, intval($request->get_param('per_page') ?: 20)));
        $offset = ($page - 1) * $per_page;
        
        $expenses = $wpdb->get_results($wpdb->prepare(
            "SELECT e.*, c.name AS category_name, v.name AS vault_name, emp.name AS employee_name 
             FROM {$wpdb->prefix}ffa_expenses e 
             LEFT JOIN {$wpdb->prefix}ffa_expense_categories c ON e.category_id = c.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON e.vault_id = v.id 
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON e.employee_id = emp.id 
             ORDER BY e.created_at DESC 
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ffa_expenses");
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $expenses,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }
    
    public static function create_expense($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $token = str_replace('Bearer ', '', $request->get_header('authorization'));
        $user = FFA_Database::validate_token($token);
        
        // Validate required fields
        $required = ['type', 'category_id', 'amount', 'vault_id', 'employee_id'];
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
            }
        }
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", intval($params['vault_id'])
        ));
        
        if (!$vault) {
            return new WP_Error('invalid_vault', 'Vault not found', ['status' => 404]);
        }
        
        $amount = floatval($params['amount']);
        $allow_negative = isset($params['allow_negative_balance']) && $params['allow_negative_balance'] === true;
        
        if (!$allow_negative && $vault->balance < $amount) {
            return new WP_Error('insufficient_balance', 'Insufficient vault balance', ['status' => 400]);
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            $data = [
                'type' => sanitize_text_field($params['type']),
                'category_id' => intval($params['category_id']),
                'amount' => $amount,
                'description' => sanitize_textarea_field($params['description'] ?? ''),
                'warehouse' => sanitize_text_field($params['warehouse'] ?? ''),
                'vault_id' => intval($params['vault_id']),
                'employee_id' => intval($params['employee_id']),
                'created_at' => current_time('mysql'),
                'created_by' => $user->sub,
            ];
            
            $result = $wpdb->insert($wpdb->prefix . 'ffa_expenses', $data);
            if ($result === false) {
                throw new Exception('Failed to create expense');
            }
            
            $expense_id = $wpdb->insert_id;
            
            // Update vault balance
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $vault->balance - $amount],
                ['id' => $vault->id]
            );
            
            // Record cashflow
            FFA_Database::record_cashflow(
                'expense',
                $data['category_id'],
                $data['amount'],
                $data['description'],
                $expense_id,
                'expense',
                $data['warehouse'],
                $vault->payment_method,
                $vault->id,
                $data['employee_id']
            );
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Expense created successfully',
                'data' => ['id' => $expense_id]
            ], 201);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public static function get_expense($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $expense = $wpdb->get_row($wpdb->prepare(
            "SELECT e.*, c.name AS category_name, v.name AS vault_name, emp.name AS employee_name 
             FROM {$wpdb->prefix}ffa_expenses e 
             LEFT JOIN {$wpdb->prefix}ffa_expense_categories c ON e.category_id = c.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON e.vault_id = v.id 
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON e.employee_id = emp.id 
             WHERE e.id = %d",
            $id
        ));
        
        if (!$expense) {
            return new WP_Error('not_found', 'Expense not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $expense
        ], 200);
    }
    
    public static function update_expense($request) {
        global $wpdb;
        $id = intval($request['id']);
        $params = $request->get_json_params();
        
        $data = [];
        if (isset($params['type'])) $data['type'] = sanitize_text_field($params['type']);
        if (isset($params['category_id'])) $data['category_id'] = intval($params['category_id']);
        if (isset($params['amount'])) $data['amount'] = floatval($params['amount']);
        if (isset($params['description'])) $data['description'] = sanitize_textarea_field($params['description']);
        if (isset($params['warehouse'])) $data['warehouse'] = sanitize_text_field($params['warehouse']);
        
        if (empty($data)) {
            return new WP_Error('no_data', 'No data to update', ['status' => 400]);
        }
        
        $result = $wpdb->update($wpdb->prefix . 'ffa_expenses', $data, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update expense', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Expense not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Expense updated successfully'
        ], 200);
    }
    
    public static function delete_expense($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $result = $wpdb->delete($wpdb->prefix . 'ffa_expenses', ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete expense', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Expense not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Expense deleted successfully'
        ], 200);
    }
    // ============================================
    // EXPENSE CATEGORIES - CRUD
    // ============================================
    public static function get_categories($request) {
        $categories = FFA_Database::get_categories();
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }
    
    public static function create_category($request) {
        global $wpdb;
        $params = $request->get_json_params();
        
        if (empty($params['name'])) {
            return new WP_Error('missing_name', 'Category name is required', ['status' => 400]);
        }
        
        $data = [
            'name' => sanitize_text_field($params['name']),
            'description' => sanitize_textarea_field($params['description'] ?? ''),
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($wpdb->prefix . 'ffa_expense_categories', $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create category', ['status' => 500]);
        }
        
        FFA_Database::clear_cache();
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Category created successfully',
            'data' => ['id' => $wpdb->insert_id]
        ], 201);
    }
    
    public static function get_category($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $category = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_expense_categories WHERE id = %d", $id
        ));
        
        if (!$category) {
            return new WP_Error('not_found', 'Category not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $category
        ], 200);
    }
    
    public static function update_category($request) {
        global $wpdb;
        $id = intval($request['id']);
        $params = $request->get_json_params();
        
        $data = [];
        if (isset($params['name'])) $data['name'] = sanitize_text_field($params['name']);
        if (isset($params['description'])) $data['description'] = sanitize_textarea_field($params['description']);
        
        if (empty($data)) {
            return new WP_Error('no_data', 'No data to update', ['status' => 400]);
        }
        
        $result = $wpdb->update($wpdb->prefix . 'ffa_expense_categories', $data, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update category', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Category not found', ['status' => 404]);
        }
        
        FFA_Database::clear_cache();
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Category updated successfully'
        ], 200);
    }
    
    public static function delete_category($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $result = $wpdb->delete($wpdb->prefix . 'ffa_expense_categories', ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete category', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Category not found', ['status' => 404]);
        }
        
        FFA_Database::clear_cache();
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Category deleted successfully'
        ], 200);
    }
    
    // ============================================
    // VAULTS - CRUD + TRANSFER
    // ============================================
    public static function get_vaults($request) {
        $vaults = FFA_Database::get_vaults();
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $vaults
        ], 200);
    }
    
    public static function create_vault($request) {
        global $wpdb;
        $params = $request->get_json_params();
        
        $required = ['name', 'payment_method'];
        foreach ($required as $field) {
            if (empty($params[$field])) {
                return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
            }
        }
        
        $data = [
            'name' => sanitize_text_field($params['name']),
            'payment_method' => sanitize_text_field($params['payment_method']),
            'balance' => floatval($params['balance'] ?? 0),
            'commission_rate' => floatval($params['commission_rate'] ?? 0),
            'default_warehouse' => sanitize_text_field($params['default_warehouse'] ?? ''),
            'employees' => json_encode($params['employees'] ?? []),
            'is_default' => intval($params['is_default'] ?? 0),
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($wpdb->prefix . 'ffa_vaults', $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create vault', ['status' => 500]);
        }
        
        FFA_Database::clear_cache();
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Vault created successfully',
            'data' => ['id' => $wpdb->insert_id]
        ], 201);
    }
    
    public static function get_vault($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $id
        ));
        
        if (!$vault) {
            return new WP_Error('not_found', 'Vault not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $vault
        ], 200);
    }
    
    public static function update_vault($request) {
        global $wpdb;
        $id = intval($request['id']);
        $params = $request->get_json_params();
        
        $data = [];
        if (isset($params['name'])) $data['name'] = sanitize_text_field($params['name']);
        if (isset($params['payment_method'])) $data['payment_method'] = sanitize_text_field($params['payment_method']);
        if (isset($params['balance'])) $data['balance'] = floatval($params['balance']);
        if (isset($params['commission_rate'])) $data['commission_rate'] = floatval($params['commission_rate']);
        if (isset($params['default_warehouse'])) $data['default_warehouse'] = sanitize_text_field($params['default_warehouse']);
        if (isset($params['employees'])) $data['employees'] = json_encode($params['employees']);
        if (isset($params['is_default'])) $data['is_default'] = intval($params['is_default']);
        
        if (empty($data)) {
            return new WP_Error('no_data', 'No data to update', ['status' => 400]);
        }
        
        $result = $wpdb->update($wpdb->prefix . 'ffa_vaults', $data, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update vault', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Vault not found', ['status' => 404]);
        }
        
        FFA_Database::clear_cache();
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Vault updated successfully'
        ], 200);
    }
    
    public static function delete_vault($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $result = $wpdb->delete($wpdb->prefix . 'ffa_vaults', ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete vault', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Vault not found', ['status' => 404]);
        }
        
        FFA_Database::clear_cache();
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Vault deleted successfully'
        ], 200);
    }
    

            /**
         * Get available payment methods
         */
        public static function get_payment_methods($request) {
            $payment_methods = FFA_Database::get_wc_payment_methods();
            
            return new WP_REST_Response([
                'status' => 'success',
                'data' => $payment_methods
            ], 200);
        }


    public static function transfer_vault($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $token = str_replace('Bearer ', '', $request->get_header('authorization'));
        $user = FFA_Database::validate_token($token);
        
        $required = ['from_vault_id', 'to_vault_id', 'amount', 'employee_id'];
        foreach ($required as $field) {
            if (!isset($params[$field])) {
                return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
            }
        }
        
        $from_vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", intval($params['from_vault_id'])
        ));
        
        $to_vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", intval($params['to_vault_id'])
        ));
        
        if (!$from_vault || !$to_vault) {
            return new WP_Error('invalid_vault', 'One or both vaults not found', ['status' => 404]);
        }
        
        $amount = floatval($params['amount']);
        $allow_negative = isset($params['allow_negative_balance']) && $params['allow_negative_balance'] === true;
        
        if (!$allow_negative && $from_vault->balance < $amount) {
            return new WP_Error('insufficient_balance', 'Insufficient balance', ['status' => 400]);
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Update from vault
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $from_vault->balance - $amount],
                ['id' => $from_vault->id]
            );
            
            // Update to vault
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $to_vault->balance + $amount],
                ['id' => $to_vault->id]
            );
            
            // Record cashflow - from
            FFA_Database::record_cashflow(
                'expense',
                null,
                $amount,
                "Transfer to {$to_vault->name}",
                $from_vault->id,
                'vault_transfer',
                null,
                $from_vault->payment_method,
                $from_vault->id,
                intval($params['employee_id'])
            );
            
            // Record cashflow - to
            FFA_Database::record_cashflow(
                'revenue',
                null,
                $amount,
                "Received from {$from_vault->name}",
                $to_vault->id,
                'vault_transfer',
                null,
                $to_vault->payment_method,
                $to_vault->id,
                intval($params['employee_id'])
            );
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Transfer completed successfully'
            ], 200);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }

    public static function add_funds_to_vault($request) {
    global $wpdb;

    $params = $request->get_json_params();
    $token = str_replace('Bearer ', '', $request->get_header('authorization'));
    $user = FFA_Database::validate_token($token);

    // Required fields
    $required = ['vault_id', 'amount', 'employee_id', 'note'];
    foreach ($required as $field) {
        if (!isset($params[$field])) {
            return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
        }
    }

    $vault_id = intval($params['vault_id']);
    $amount = floatval($params['amount']);

    if ($amount <= 0) {
        return new WP_Error('invalid_amount', 'Amount must be greater than zero', ['status' => 400]);
    }

    // Get vault
    $vault = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $vault_id
    ));

    if (!$vault) {
        return new WP_Error('not_found', 'Vault not found', ['status' => 404]);
    }

    $wpdb->query('START TRANSACTION');

    try {
        // Update balance
        $wpdb->update(
            $wpdb->prefix . 'ffa_vaults',
            ['balance' => $vault->balance + $amount],
            ['id' => $vault_id]
        );

        // Record in cashflow as revenue
        FFA_Database::record_cashflow(
            'revenue',                  // type
            null,                       // category_id
            $amount,                    // amount
            sanitize_text_field($params['note']), // description
            $vault_id,                  // vault_id
            'vault_topup',              // related_type
            null,                       // related_id
            $vault->payment_method,     // payment_method
            $vault_id,                  // vault_id again
            intval($params['employee_id']) // employee_id
        );

        $wpdb->query('COMMIT');
        FFA_Database::clear_cache();

        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Funds added successfully'
        ], 200);

    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
    }
}

    
    // ============================================
    // VENDORS - CRUD
    // ============================================
    public static function get_vendors($request) {
        global $wpdb;
        
        $vendors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_vendors ORDER BY name ASC");
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $vendors
        ], 200);
    }
    
    public static function create_vendor($request) {
        global $wpdb;
        $params = $request->get_json_params();
        
        if (empty($params['name'])) {
            return new WP_Error('missing_name', 'Vendor name is required', ['status' => 400]);
        }
        
        $data = [
            'name' => sanitize_text_field($params['name']),
            'phone' => sanitize_text_field($params['phone'] ?? ''),
            'address' => sanitize_textarea_field($params['address'] ?? ''),
            'material_ids' => json_encode($params['material_ids'] ?? []),
            'payment_methods' => json_encode($params['payment_methods'] ?? []),
            'balance' => floatval($params['balance'] ?? 0),
            'created_at' => current_time('mysql')
        ];
        
        $result = $wpdb->insert($wpdb->prefix . 'ffa_vendors', $data);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to create vendor', ['status' => 500]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Vendor created successfully',
            'data' => ['id' => $wpdb->insert_id]
        ], 201);
    }
    
    public static function get_vendor($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $vendor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vendors WHERE id = %d", $id
        ));
        
        if (!$vendor) {
            return new WP_Error('not_found', 'Vendor not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $vendor
        ], 200);
    }
    
    public static function update_vendor($request) {
        global $wpdb;
        $id = intval($request['id']);
        $params = $request->get_json_params();
        
        $data = [];
        if (isset($params['name'])) $data['name'] = sanitize_text_field($params['name']);
        if (isset($params['phone'])) $data['phone'] = sanitize_text_field($params['phone']);
        if (isset($params['address'])) $data['address'] = sanitize_textarea_field($params['address']);
        if (isset($params['material_ids'])) $data['material_ids'] = json_encode($params['material_ids']);
        if (isset($params['payment_methods'])) $data['payment_methods'] = json_encode($params['payment_methods']);
        if (isset($params['balance'])) $data['balance'] = floatval($params['balance']);
        
        if (empty($data)) {
            return new WP_Error('no_data', 'No data to update', ['status' => 400]);
        }
        
        $result = $wpdb->update($wpdb->prefix . 'ffa_vendors', $data, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update vendor', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Vendor not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Vendor updated successfully'
        ], 200);
    }
    
    public static function delete_vendor($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $result = $wpdb->delete($wpdb->prefix . 'ffa_vendors', ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete vendor', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Vendor not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Vendor deleted successfully'
        ], 200);
    }
    
    // ============================================
    // PURCHASES - CRUD
    // ============================================
    public static function get_purchases($request) {
        global $wpdb;
        
        $page = max(1, intval($request->get_param('page') ?: 1));
        $per_page = max(1, min(100, intval($request->get_param('per_page') ?: 20)));
        $offset = ($page - 1) * $per_page;
        
        $purchases = $wpdb->get_results($wpdb->prepare(
            "SELECT p.*, m.post_title AS material_name, v.name AS vendor_name, 
                    va.name AS vault_name, emp.name AS employee_name 
             FROM {$wpdb->prefix}ffa_purchases p 
             JOIN {$wpdb->prefix}posts m ON p.material_id = m.ID 
             JOIN {$wpdb->prefix}ffa_vendors v ON p.vendor_id = v.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults va ON p.vault_id = va.id 
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON p.employee_id = emp.id 
             ORDER BY p.created_at DESC 
             LIMIT %d OFFSET %d",
            $per_page, $offset
        ));
        
        $total = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}ffa_purchases");
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $purchases,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }
    
    public static function create_purchase($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $token = str_replace('Bearer ', '', $request->get_header('authorization'));
        $user = FFA_Database::validate_token($token);
        
        $required = ['material_id', 'vendor_id', 'quantity', 'unit_cost', 'vault_id', 'employee_id'];
        foreach ($required as $field) {
            if (!isset($params[$field])) {
                return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
            }
        }
        
        $quantity = intval($params['quantity']);
        $unit_cost = floatval($params['unit_cost']);
        $total_cost = $quantity * $unit_cost;
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", intval($params['vault_id'])
        ));
        
        $vendor = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vendors WHERE id = %d", intval($params['vendor_id'])
        ));
        
        if (!$vault || !$vendor) {
            return new WP_Error('invalid_data', 'Vault or vendor not found', ['status' => 404]);
        }
        
        $allow_negative = isset($params['allow_negative_balance']) && $params['allow_negative_balance'] === true;
        
        if (!$allow_negative && $vault->balance < $total_cost) {
            return new WP_Error('insufficient_balance', 'Insufficient vault balance', ['status' => 400]);
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            $data = [
                'material_id' => intval($params['material_id']),
                'vendor_id' => intval($params['vendor_id']),
                'quantity' => $quantity,
                'unit_cost' => $unit_cost,
                'total_cost' => $total_cost,
                'vault_id' => intval($params['vault_id']),
                'employee_id' => intval($params['employee_id']),
                'payment_status' => 'paid',
                'created_at' => current_time('mysql'),
                'created_by' => $user->sub,
            ];
            
            $result = $wpdb->insert($wpdb->prefix . 'ffa_purchases', $data);
            if ($result === false) {
                throw new Exception('Failed to create purchase');
            }
            
            $purchase_id = $wpdb->insert_id;
            
            // Update vault balance
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $vault->balance - $total_cost],
                ['id' => $vault->id]
            );
            
            // Update vendor balance
            $wpdb->update(
                $wpdb->prefix . 'ffa_vendors',
                ['balance' => $vendor->balance + $total_cost],
                ['id' => $vendor->id]
            );
            
            // Update material stock (FMS integration)
            $current_stock = get_post_meta($data['material_id'], '_fms_current_stock', true) ?: 0;
            update_post_meta($data['material_id'], '_fms_current_stock', $current_stock + $quantity);
            
            // Record cashflow
            $category_id = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE name = 'Raw Materials'");
            FFA_Database::record_cashflow(
                'expense',
                $category_id,
                $total_cost,
                "Purchased $quantity of material #{$data['material_id']} from vendor #{$data['vendor_id']}",
                $purchase_id,
                'purchase',
                null,
                $vault->payment_method,
                $vault->id,
                $data['employee_id']
            );
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Purchase created successfully',
                'data' => ['id' => $purchase_id]
            ], 201);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public static function get_purchase($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $purchase = $wpdb->get_row($wpdb->prepare(
            "SELECT p.*, m.post_title AS material_name, v.name AS vendor_name, 
                    va.name AS vault_name, emp.name AS employee_name 
             FROM {$wpdb->prefix}ffa_purchases p 
             JOIN {$wpdb->prefix}posts m ON p.material_id = m.ID 
             JOIN {$wpdb->prefix}ffa_vendors v ON p.vendor_id = v.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults va ON p.vault_id = va.id 
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON p.employee_id = emp.id 
             WHERE p.id = %d",
            $id
        ));
        
        if (!$purchase) {
            return new WP_Error('not_found', 'Purchase not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $purchase
        ], 200);
    }
    
    public static function update_purchase($request) {
        global $wpdb;
        $id = intval($request['id']);
        $params = $request->get_json_params();
        
        $data = [];
        if (isset($params['quantity'])) $data['quantity'] = intval($params['quantity']);
        if (isset($params['unit_cost'])) $data['unit_cost'] = floatval($params['unit_cost']);
        if (isset($params['payment_status'])) $data['payment_status'] = sanitize_text_field($params['payment_status']);
        
        if (isset($data['quantity']) && isset($data['unit_cost'])) {
            $data['total_cost'] = $data['quantity'] * $data['unit_cost'];
        }
        
        if (empty($data)) {
            return new WP_Error('no_data', 'No data to update', ['status' => 400]);
        }
        
        $result = $wpdb->update($wpdb->prefix . 'ffa_purchases', $data, ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to update purchase', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Purchase not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Purchase updated successfully'
        ], 200);
    }
    
    public static function delete_purchase($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $result = $wpdb->delete($wpdb->prefix . 'ffa_purchases', ['id' => $id]);
        
        if ($result === false) {
            return new WP_Error('db_error', 'Failed to delete purchase', ['status' => 500]);
        }
        
        if ($result === 0) {
            return new WP_Error('not_found', 'Purchase not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Purchase deleted successfully'
        ], 200);
    }
    
    // ============================================
    // PAYROLL
    // ============================================
    public static function get_payroll($request) {
        global $wpdb;

        $month = $request->get_param('month') ?: date('Y-m');

        // 1) Ensure SHRMS salaries exist for this month (lazy init)
        if (class_exists('SHRMS_Core')) {
            // Get all active employees from SHRMS
            $employees = SHRMS_Core::get_employees('active');

            foreach ($employees as $emp) {
                // Check if this employee already has a salary row for the given month
                $exists = $wpdb->get_var($wpdb->prepare(
                    "SELECT id FROM {$wpdb->prefix}shrms_salaries
                    WHERE employee_id = %d AND month = %s",
                    $emp->id,
                    $month
                ));

                if (!$exists) {
                    // Create initial snapshot for this employee and month
                    // This will:
                    //  - use base_salary from shrms_employees
                    //  - add/subtract any approved requests (bonus/deduction/advance) for this month
                    //  - respect 'paid' status inside the method (it will not modify paid rows)
                    SHRMS_Core::recalculate_salary_for_employee_month($emp->id, $month);
                }
            }
        }

        // 2) Check that SHRMS salaries table exists
        $table_salaries  = $wpdb->prefix . 'shrms_salaries';
        $table_employees = $wpdb->prefix . 'shrms_employees';
        $table_exists    = $wpdb->get_var("SHOW TABLES LIKE '$table_salaries'") === $table_salaries;

        if (!$table_exists) {
            return new WP_Error('shrms_not_found', 'SHRMS plugin is not active', ['status' => 500]);
        }

        // 3) Fetch all salaries for this month (after lazy init)
        $salaries = $wpdb->get_results($wpdb->prepare(
            "SELECT s.*, e.name 
            FROM $table_salaries s 
            JOIN $table_employees e ON s.employee_id = e.id 
            WHERE s.month = %s 
            ORDER BY e.name",
            $month
        ));

        return new WP_REST_Response([
            'status' => 'success',
            'data'   => $salaries
        ], 200);
    }
 
    public static function pay_salary($request) {
    global $wpdb;
    $params = $request->get_json_params();
    $token = str_replace('Bearer ', '', $request->get_header('authorization'));
    $user = FFA_Database::validate_token($token);
    
    $required = ['salary_id', 'vault_id', 'employee_id'];
    foreach ($required as $field) {
        if (!isset($params[$field])) {
            return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
        }
    }
    
    $table_salaries = $wpdb->prefix . 'shrms_salaries';
    $table_employees = $wpdb->prefix . 'shrms_employees';
    $table_vaults = $wpdb->prefix . 'ffa_vaults';
    
    $salary_id = intval($params['salary_id']);
    $vault_id = intval($params['vault_id']);
    $employee_id = intval($params['employee_id']);
    
    // Get salary
    $salary = $wpdb->get_row($wpdb->prepare(
        "SELECT s.*, e.name, e.phone FROM $table_salaries s 
         JOIN $table_employees e ON s.employee_id = e.id 
         WHERE s.id = %d",
        $salary_id
    ));
    
    // Get vault
    $vault = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_vaults WHERE id = %d", $vault_id
    ));
    
    if (!$salary || !$vault) {
        return new WP_Error('invalid_data', 'Salary or vault not found', ['status' => 404]);
    }
    
    if ($salary->status === 'paid') {
        return new WP_Error('already_paid', 'Salary already paid', ['status' => 400]);
    }
    
    // Calculate commission
    $commission_rate = floatval($vault->commission_rate);
    $commission_amount = ($salary->final_salary * $commission_rate) / 100;
    $total_deduction = $salary->final_salary + $commission_amount;
    
    // Check balance
    $allow_negative = isset($params['allow_negative_balance']) && $params['allow_negative_balance'] === true;
    
    if (!$allow_negative && floatval($vault->balance) < $total_deduction) {
        return new WP_Error('insufficient_balance', 
            sprintf('Insufficient vault balance. Required: %s, Available: %s', 
                number_format($total_deduction, 2), 
                number_format($vault->balance, 2)
            ), 
            ['status' => 400]
        );
    }
    
    $wpdb->query('START TRANSACTION');
    
    try {
        // 1. Record salary cashflow
        $salary_category = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE name = 'Salaries' LIMIT 1");

        $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
            'type' => 'expense',
            'category_id' => $salary_category ?: null,
            'amount' => $salary->final_salary,
            'description' => "Salary payment for {$salary->name} ({$salary->month}) - API",
            'related_id' => $salary_id,
            'related_type' => 'shrms_salary',
            'warehouse' => null,
            'payment_method' => $vault->payment_method,
            'vault_id' => $vault_id,
            'employee_id' => $employee_id,
            'order_id' => null,
            'created_at' => current_time('mysql'),
            'created_by' => $user->sub
        ]);

        
        $cashflow_id_salary = $wpdb->insert_id;
        
        // 2. Record commission cashflow (if > 0)
        $cashflow_id_commission = null;
        if ($commission_amount > 0) {
            $commission_category = $wpdb->get_var(
                "SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE name = 'Commission' LIMIT 1"
            );
            
            $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
                'type' => 'expense',
                'category_id' => $commission_category,
                'amount' => $commission_amount,
                'description' => "Commission for salary payment {$salary->name} ({$commission_rate}%)",
                'related_id' => $salary_id,
                'related_type' => 'salary_commission',
                'warehouse' => null,
                'payment_method' => $vault->payment_method,
                'vault_id' => $vault_id,
                'employee_id' => $employee_id,
                'order_id' => null,
                'created_at' => current_time('mysql'),
                'created_by' => $user->sub
            ]);
            
            $cashflow_id_commission = $wpdb->insert_id;
            
            // 3. Record commission expense
            $wpdb->insert($wpdb->prefix . 'ffa_expenses', [
                'type' => 'variable',
                'category_id' => $commission_category,
                'amount' => $commission_amount,
                'description' => "Commission: Salary payment {$salary->name}",
                'warehouse' => null,
                'vault_id' => $vault_id,
                'employee_id' => $employee_id,
                'created_at' => current_time('mysql'),
                'created_by' => $user->sub
            ]);
        }
        
        // 4. Update vault balance (total deduction)
        $new_balance = floatval($vault->balance) - $total_deduction;
        $wpdb->update(
            $table_vaults,
            ['balance' => $new_balance],
            ['id' => $vault_id],
            ['%f'],
            ['%d']
        );
        
        // 5. Record salary expense
        $wpdb->insert($wpdb->prefix . 'ffa_expenses', [
            'type' => 'fixed',
            'category_id' => null,
            'amount' => $salary->final_salary,
            'description' => "Salary: {$salary->name} - {$salary->month}",
            'warehouse' => null,
            'vault_id' => $vault_id,
            'employee_id' => $employee_id,
            'created_at' => current_time('mysql'),
            'created_by' => $user->sub
        ]);
        
        // 6. Update SHRMS salary status
        $wpdb->update(
            $table_salaries, 
            [
                'status' => 'paid',
                'paid_at' => current_time('mysql')
            ], 
            ['id' => $salary_id]
        );
        
        $wpdb->query('COMMIT');
        FFA_Database::clear_cache();
        
        // 7. Trigger action (for SHRMS integration)
        do_action('ffa_salary_paid_api', $salary_id, $vault_id, $total_deduction);
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => 'Salary paid successfully',
            'data' => [
                'salary_id' => $salary_id,
                'employee_name' => $salary->name,
                'salary_amount' => floatval($salary->final_salary),
                'commission_rate' => $commission_rate,
                'commission_amount' => $commission_amount,
                'total_deducted' => $total_deduction,
                'vault_id' => $vault_id,
                'vault_name' => $vault->name,
                'vault_balance_before' => floatval($vault->balance),
                'vault_balance_after' => $new_balance,
                'cashflow_ids' => array_filter([
                    'salary' => $cashflow_id_salary,
                    'commission' => $cashflow_id_commission
                ]),
                'paid_at' => current_time('mysql')
            ]
        ], 200);
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        error_log('[FFA API] Salary payment failed: ' . $e->getMessage());
        return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
    }
}


    // ============================================
    // REPORTS
    // ============================================
    public static function get_reports($request) {
        $period = $request->get_param('period') ?: 'month';
        
        $sales_report = FFA_Database::get_sales_report($period);
        $profit_margin = FFA_Database::calculate_profit_margin($period);
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => [
                'period' => $period,
                'sales_report' => $sales_report,
                'profit_margin' => $profit_margin,
                'currency' => get_option('ffa_currency', 'EGP')
            ]
        ], 200);
    }
    
    // ============================================
    // CASHFLOW
    // ============================================
    public static function get_cashflow($request) {
        global $wpdb;
        
        $page = max(1, intval($request->get_param('page') ?: 1));
        $per_page = max(1, min(100, intval($request->get_param('per_page') ?: 20)));
        $offset = ($page - 1) * $per_page;
        $type = $request->get_param('type');
        
        $where_clause = "WHERE 1=1";
        $params = [];
        
        if ($type && in_array($type, ['revenue', 'expense'])) {
            $where_clause .= " AND c.type = %s";
            $params[] = $type;
        }
        
        $params[] = $per_page;
        $params[] = $offset;
        
        $cashflows = $wpdb->get_results($wpdb->prepare(
            "SELECT c.*, cat.name AS category_name, v.name AS vault_name, emp.name AS employee_name 
             FROM {$wpdb->prefix}ffa_cashflow c 
             LEFT JOIN {$wpdb->prefix}ffa_expense_categories cat ON c.category_id = cat.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON c.vault_id = v.id 
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON c.employee_id = emp.id 
             $where_clause 
             ORDER BY c.created_at DESC 
             LIMIT %d OFFSET %d",
            ...$params
        ));
        
        $total_params = array_slice($params, 0, -2);
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_cashflow c $where_clause";
        $total = empty($total_params) ? 
            $wpdb->get_var($total_query) : 
            $wpdb->get_var($wpdb->prepare($total_query, ...$total_params));
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $cashflows,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }
    
    // ============================================
    // COMPANY LOANS
    // ============================================
    public static function get_company_loans($request) {
        global $wpdb;
        
        $page = max(1, intval($request->get_param('page') ?: 1));
        $per_page = max(1, min(100, intval($request->get_param('per_page') ?: 20)));
        $offset = ($page - 1) * $per_page;
        $status = $request->get_param('status');
        
        $where_clause = "WHERE 1=1";
        $params = [];
        
        if ($status && in_array($status, ['active', 'completed', 'defaulted'])) {
            $where_clause .= " AND cl.status = %s";
            $params[] = $status;
        }
        
        $params[] = $per_page;
        $params[] = $offset;
        
        $loans = $wpdb->get_results($wpdb->prepare(
            "SELECT cl.*, e.name AS receiver_name, v.name AS vault_name
             FROM {$wpdb->prefix}ffa_company_loans cl 
             JOIN {$wpdb->prefix}shrms_employees e ON cl.receiver_employee_id = e.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON cl.vault_id = v.id
             $where_clause 
             ORDER BY cl.created_at DESC 
             LIMIT %d OFFSET %d",
            ...$params
        ));
        
        $total_params = array_slice($params, 0, -2);
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_company_loans cl $where_clause";
        $total = empty($total_params) ? 
            $wpdb->get_var($total_query) : 
            $wpdb->get_var($wpdb->prepare($total_query, ...$total_params));
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $loans,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }
    
    public static function create_company_loan($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $token = str_replace('Bearer ', '', $request->get_header('authorization'));
        $user = FFA_Database::validate_token($token);
        
        $required = ['lender_name', 'receiver_employee_id', 'vault_id', 'loan_amount', 'repayment_type', 'loan_date', 'reason'];
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
            }
        }
        
        $loan_amount = floatval($params['loan_amount']);
        $vault_id = intval($params['vault_id']);
        $repayment_type = sanitize_text_field($params['repayment_type']);
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $vault_id
        ));
        
        if (!$vault) {
            return new WP_Error('invalid_vault', 'Vault not found', ['status' => 404]);
        }
        
        // Calculate installment details
        $installment_amount = 0;
        $installment_period = 0;
        $installment_frequency = 'monthly';
        $due_date = null;
        $next_payment_date = null;
        
        if ($repayment_type === 'installments') {
            $installment_amount = floatval($params['installment_amount'] ?? 0);
            $installment_period = intval($params['installment_period'] ?? 0);
            $installment_frequency = sanitize_text_field($params['installment_frequency'] ?? 'monthly');
            
            if ($installment_amount <= 0 || $installment_period <= 0) {
                return new WP_Error('invalid_installment', 'Installment details required', ['status' => 400]);
            }
            
            $frequency_days = ['daily' => 1, 'weekly' => 7, 'monthly' => 30];
            $total_days = $installment_period * $frequency_days[$installment_frequency];
            $due_date = date('Y-m-d', strtotime($params['loan_date'] . ' + ' . $total_days . ' days'));
            $next_payment_date = date('Y-m-d', strtotime($params['loan_date'] . ' + ' . $frequency_days[$installment_frequency] . ' days'));
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            $loan_data = [
                'lender_name' => sanitize_text_field($params['lender_name']),
                'receiver_employee_id' => intval($params['receiver_employee_id']),
                'vault_id' => $vault_id,
                'loan_amount' => $loan_amount,
                'repayment_type' => $repayment_type,
                'installment_amount' => $installment_amount,
                'installment_period' => $installment_period,
                'installment_frequency' => $installment_frequency,
                'remaining_balance' => $loan_amount,
                'loan_date' => sanitize_text_field($params['loan_date']),
                'due_date' => $due_date,
                'next_payment_date' => $next_payment_date,
                'reason' => sanitize_textarea_field($params['reason']),
                'created_at' => current_time('mysql'),
                'created_by' => $user->sub,
            ];
            
            $result = $wpdb->insert($wpdb->prefix . 'ffa_company_loans', $loan_data);
            if ($result === false) {
                throw new Exception('Failed to create company loan');
            }
            
            $loan_id = $wpdb->insert_id;
            
            // Update vault balance (add loan amount)
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $vault->balance + $loan_amount],
                ['id' => $vault_id]
            );
            
            // Record cashflow (loan received)
            FFA_Database::record_cashflow(
                'revenue',
                null,
                $loan_amount,
                "Company loan received from {$loan_data['lender_name']} - Loan ID: $loan_id",
                $loan_id,
                'company_loan_received',
                null,
                $vault->payment_method,
                $vault_id,
                $loan_data['receiver_employee_id']
            );
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Company loan created successfully',
                'data' => ['id' => $loan_id]
            ], 201);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public static function get_company_loan($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $loan = $wpdb->get_row($wpdb->prepare(
            "SELECT cl.*, e.name AS receiver_name, v.name AS vault_name
             FROM {$wpdb->prefix}ffa_company_loans cl 
             JOIN {$wpdb->prefix}shrms_employees e ON cl.receiver_employee_id = e.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON cl.vault_id = v.id
             WHERE cl.id = %d",
            $id
        ));
        
        if (!$loan) {
            return new WP_Error('not_found', 'Loan not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $loan
        ], 200);
    }
    
    public static function delete_company_loan($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $loan = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_company_loans WHERE id = %d", $id
        ));
        
        if (!$loan) {
            return new WP_Error('not_found', 'Loan not found', ['status' => 404]);
        }
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $loan->vault_id
        ));
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Reverse vault balance
            if ($vault) {
                $wpdb->update(
                    $wpdb->prefix . 'ffa_vaults',
                    ['balance' => $vault->balance - $loan->loan_amount],
                    ['id' => $vault->id]
                );
            }
            
            // Delete payments
            $wpdb->delete($wpdb->prefix . 'ffa_loan_payments', [
                'loan_id' => $id,
                'loan_type' => 'company'
            ]);
            
            // Delete loan
            $wpdb->delete($wpdb->prefix . 'ffa_company_loans', ['id' => $id]);
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Loan deleted successfully'
            ], 200);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    // ============================================
    // EMPLOYEE LOANS
    // ============================================
    public static function get_employee_loans($request) {
        global $wpdb;
        
        $page = max(1, intval($request->get_param('page') ?: 1));
        $per_page = max(1, min(100, intval($request->get_param('per_page') ?: 20)));
        $offset = ($page - 1) * $per_page;
        $status = $request->get_param('status');
        $employee_id = $request->get_param('employee_id');
        
        $where_clause = "WHERE 1=1";
        $params = [];
        
        if ($status && in_array($status, ['active', 'completed', 'suspended'])) {
            $where_clause .= " AND el.status = %s";
            $params[] = $status;
        }
        
        if ($employee_id) {
            $where_clause .= " AND el.employee_id = %d";
            $params[] = intval($employee_id);
        }
        
        $params[] = $per_page;
        $params[] = $offset;
        
        $loans = $wpdb->get_results($wpdb->prepare(
            "SELECT el.*, e.name AS employee_name, e.salary AS employee_salary, v.name AS vault_name
             FROM {$wpdb->prefix}ffa_employee_loans el 
             JOIN {$wpdb->prefix}shrms_employees e ON el.employee_id = e.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON el.vault_id = v.id
             $where_clause 
             ORDER BY el.created_at DESC 
             LIMIT %d OFFSET %d",
            ...$params
        ));
        
        $total_params = array_slice($params, 0, -2);
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_employee_loans el $where_clause";
        $total = empty($total_params) ? 
            $wpdb->get_var($total_query) : 
            $wpdb->get_var($wpdb->prepare($total_query, ...$total_params));
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $loans,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }
    
    public static function create_employee_loan($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $token = str_replace('Bearer ', '', $request->get_header('authorization'));
        $user = FFA_Database::validate_token($token);
        
        $required = ['employee_id', 'vault_id', 'loan_amount', 'repayment_type', 'loan_date', 'reason'];
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
            }
        }
        
        $employee_id = intval($params['employee_id']);
        $loan_amount = floatval($params['loan_amount']);
        $vault_id = intval($params['vault_id']);
        $repayment_type = sanitize_text_field($params['repayment_type']);
        $auto_deduct = intval($params['auto_deduct_from_salary'] ?? 1);
        
        $employee = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}shrms_employees WHERE id = %d", $employee_id
        ));
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $vault_id
        ));
        
        if (!$employee || !$vault) {
            return new WP_Error('invalid_data', 'Employee or vault not found', ['status' => 404]);
        }
        
        if ($vault->balance < $loan_amount) {
            return new WP_Error('insufficient_balance', 'Insufficient vault balance', ['status' => 400]);
        }
        
        // Calculate installment details
        $installment_amount = 0;
        $installment_period = 0;
        $installment_frequency = 'monthly';
        $due_date = null;
        $next_payment_date = null;
        
        if ($repayment_type === 'installments') {
            $installment_amount = floatval($params['installment_amount'] ?? 0);
            $installment_period = intval($params['installment_period'] ?? 0);
            $installment_frequency = sanitize_text_field($params['installment_frequency'] ?? 'monthly');
            
            if ($installment_amount <= 0 || $installment_period <= 0) {
                return new WP_Error('invalid_installment', 'Installment details required', ['status' => 400]);
            }
            
            // Check 50% limit for auto-deduct
            if ($auto_deduct && $installment_amount > ($employee->salary * 0.5)) {
                return new WP_Error('excessive_installment', 'Installment cannot exceed 50% of salary', ['status' => 400]);
            }
            
            $frequency_days = ['daily' => 1, 'weekly' => 7, 'monthly' => 30];
            $total_days = $installment_period * $frequency_days[$installment_frequency];
            $due_date = date('Y-m-d', strtotime($params['loan_date'] . ' + ' . $total_days . ' days'));
            
            if ($auto_deduct && $installment_frequency === 'monthly') {
                $next_payment_date = date('Y-m-01', strtotime($params['loan_date'] . ' +1 month'));
            } else {
                $next_payment_date = date('Y-m-d', strtotime($params['loan_date'] . ' + ' . $frequency_days[$installment_frequency] . ' days'));
            }
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            $loan_data = [
                'employee_id' => $employee_id,
                'vault_id' => $vault_id,
                'loan_amount' => $loan_amount,
                'repayment_type' => $repayment_type,
                'installment_amount' => $installment_amount,
                'installment_period' => $installment_period,
                'installment_frequency' => $installment_frequency,
                'auto_deduct_from_salary' => $auto_deduct,
                'remaining_balance' => $loan_amount,
                'loan_date' => sanitize_text_field($params['loan_date']),
                'due_date' => $due_date,
                'next_payment_date' => $next_payment_date,
                'reason' => sanitize_textarea_field($params['reason']),
                'created_at' => current_time('mysql'),
                'created_by' => $user->sub,
            ];
            
            $result = $wpdb->insert($wpdb->prefix . 'ffa_employee_loans', $loan_data);
            if ($result === false) {
                throw new Exception('Failed to create employee loan');
            }
            
            $loan_id = $wpdb->insert_id;
            
            // Update vault balance (subtract loan amount)
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $vault->balance - $loan_amount],
                ['id' => $vault_id]
            );
            
            // Record cashflow (loan given)
            FFA_Database::record_cashflow(
                'expense',
                null,
                $loan_amount,
                "Employee loan given to {$employee->name} - Loan ID: $loan_id",
                $loan_id,
                'employee_loan_given',
                null,
                $vault->payment_method,
                $vault_id,
                $employee_id
            );
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Employee loan created successfully',
                'data' => ['id' => $loan_id]
            ], 201);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    public static function get_employee_loan($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $loan = $wpdb->get_row($wpdb->prepare(
            "SELECT el.*, e.name AS employee_name, e.salary AS employee_salary, v.name AS vault_name
             FROM {$wpdb->prefix}ffa_employee_loans el 
             JOIN {$wpdb->prefix}shrms_employees e ON el.employee_id = e.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON el.vault_id = v.id
             WHERE el.id = %d",
            $id
        ));
        
        if (!$loan) {
            return new WP_Error('not_found', 'Loan not found', ['status' => 404]);
        }
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $loan
        ], 200);
    }
    
    public static function delete_employee_loan($request) {
        global $wpdb;
        $id = intval($request['id']);
        
        $loan = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_employee_loans WHERE id = %d", $id
        ));
        
        if (!$loan) {
            return new WP_Error('not_found', 'Loan not found', ['status' => 404]);
        }
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $loan->vault_id
        ));
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Reverse vault balance
            if ($vault) {
                $wpdb->update(
                    $wpdb->prefix . 'ffa_vaults',
                    ['balance' => $vault->balance + $loan->loan_amount],
                    ['id' => $vault->id]
                );
            }
            
            // Delete payments
            $wpdb->delete($wpdb->prefix . 'ffa_loan_payments', [
                'loan_id' => $id,
                'loan_type' => 'employee'
            ]);
            
            // Delete loan
            $wpdb->delete($wpdb->prefix . 'ffa_employee_loans', ['id' => $id]);
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Loan deleted successfully'
            ], 200);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    // ============================================
    // LOAN PAYMENTS
    // ============================================
    public static function get_loan_payments($request) {
        global $wpdb;
        
        $page = max(1, intval($request->get_param('page') ?: 1));
        $per_page = max(1, min(100, intval($request->get_param('per_page') ?: 20)));
        $offset = ($page - 1) * $per_page;
        $loan_type = $request->get_param('loan_type');
        $loan_id = $request->get_param('loan_id');
        
        $where_clause = "WHERE 1=1";
        $params = [];
        
        if ($loan_type && in_array($loan_type, ['company', 'employee'])) {
            $where_clause .= " AND lp.loan_type = %s";
            $params[] = $loan_type;
        }
        
        if ($loan_id) {
            $where_clause .= " AND lp.loan_id = %d";
            $params[] = intval($loan_id);
        }
        
        $params[] = $per_page;
        $params[] = $offset;
        
        $payments = $wpdb->get_results($wpdb->prepare(
            "SELECT lp.*, 
                    CASE 
                        WHEN lp.loan_type = 'company' THEN cl.lender_name
                        WHEN lp.loan_type = 'employee' THEN e.name
                    END AS loan_party,
                    v.name AS vault_name,
                    emp.name AS employee_name
             FROM {$wpdb->prefix}ffa_loan_payments lp
             LEFT JOIN {$wpdb->prefix}ffa_company_loans cl ON lp.loan_id = cl.id AND lp.loan_type = 'company'
             LEFT JOIN {$wpdb->prefix}ffa_employee_loans el ON lp.loan_id = el.id AND lp.loan_type = 'employee'
             LEFT JOIN {$wpdb->prefix}shrms_employees e ON el.employee_id = e.id
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON lp.vault_id = v.id
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON lp.employee_id = emp.id
             $where_clause 
             ORDER BY lp.payment_date DESC 
             LIMIT %d OFFSET %d",
            ...$params
        ));
        
        $total_params = array_slice($params, 0, -2);
        $total_query = "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_loan_payments lp $where_clause";
        $total = empty($total_params) ? 
            $wpdb->get_var($total_query) : 
            $wpdb->get_var($wpdb->prepare($total_query, ...$total_params));
        
        return new WP_REST_Response([
            'status' => 'success',
            'data' => $payments,
            'pagination' => [
                'page' => $page,
                'per_page' => $per_page,
                'total' => intval($total),
                'total_pages' => ceil($total / $per_page)
            ]
        ], 200);
    }
    
    public static function create_loan_payment($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $token = str_replace('Bearer ', '', $request->get_header('authorization'));
        $user = FFA_Database::validate_token($token);
        
        $required = ['loan_id', 'loan_type', 'payment_amount', 'payment_date', 'vault_id', 'employee_id'];
        foreach ($required as $field) {
            if (!isset($params[$field]) || empty($params[$field])) {
                return new WP_Error('missing_field', "Field $field is required", ['status' => 400]);
            }
        }
        
        $loan_id = intval($params['loan_id']);
        $loan_type = sanitize_text_field($params['loan_type']);
        $payment_amount = floatval($params['payment_amount']);
        $vault_id = intval($params['vault_id']);
        $employee_id = intval($params['employee_id']);
        
        if (!in_array($loan_type, ['company', 'employee'])) {
            return new WP_Error('invalid_loan_type', 'Invalid loan type', ['status' => 400]);
        }
        
        if ($payment_amount <= 0) {
            return new WP_Error('invalid_amount', 'Amount must be greater than zero', ['status' => 400]);
        }
        
        // Get loan
        if ($loan_type === 'company') {
            $loan = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_company_loans WHERE id = %d", $loan_id
            ));
        } else {
            $loan = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_employee_loans WHERE id = %d", $loan_id
            ));
        }
        
        if (!$loan) {
            return new WP_Error('loan_not_found', 'Loan not found', ['status' => 404]);
        }
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $vault_id
        ));
        
        if (!$vault) {
            return new WP_Error('vault_not_found', 'Vault not found', ['status' => 404]);
        }
        
        if ($loan->status !== 'active') {
            return new WP_Error('inactive_loan', 'Loan is not active', ['status' => 400]);
        }
        
        if ($loan->remaining_balance <= 0) {
            return new WP_Error('loan_completed', 'Loan already fully paid', ['status' => 400]);
        }
        
        // Limit payment to remaining balance
        if ($payment_amount > $loan->remaining_balance) {
            $payment_amount = $loan->remaining_balance;
        }
        
        // Check vault balance for company loans
        if ($loan_type === 'company' && $vault->balance < $payment_amount) {
            return new WP_Error('insufficient_balance', 'Insufficient vault balance', ['status' => 400]);
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Insert payment
            $payment_data = [
                'loan_id' => $loan_id,
                'loan_type' => $loan_type,
                'payment_amount' => $payment_amount,
                'payment_date' => sanitize_text_field($params['payment_date']),
                'vault_id' => $vault_id,
                'employee_id' => $employee_id,
                'is_auto_deducted' => 0,
                'salary_month' => sanitize_text_field($params['salary_month'] ?? ''),
                'notes' => sanitize_textarea_field($params['notes'] ?? ''),
                'created_at' => current_time('mysql'),
                'created_by' => $user->sub,
            ];
            
            $result = $wpdb->insert($wpdb->prefix . 'ffa_loan_payments', $payment_data);
            if ($result === false) {
                throw new Exception('Failed to create payment');
            }
            
            $payment_id = $wpdb->insert_id;
            
            // Update loan
            $new_balance = $loan->remaining_balance - $payment_amount;
            $new_total_paid = ($loan->total_paid ?? 0) + $payment_amount;
            $new_status = $new_balance <= 0 ? 'completed' : 'active';
            
            $next_payment_date = null;
            if ($loan->repayment_type === 'installments' && $new_status === 'active') {
                $frequency_days = ['daily' => 1, 'weekly' => 7, 'monthly' => 30];
                $days = $frequency_days[$loan->installment_frequency ?? 'monthly'] ?? 30;
                $next_payment_date = date('Y-m-d', strtotime(($loan->next_payment_date ?? date('Y-m-d')) . ' + ' . $days . ' days'));
            }
            
            $sub_table = $loan_type === 'company' ? 
                $wpdb->prefix . 'ffa_company_loans' : 
                $wpdb->prefix . 'ffa_employee_loans';
            
            $update_data = [
                'remaining_balance' => $new_balance,
                'total_paid' => $new_total_paid,
                'status' => $new_status
            ];
            
            if ($next_payment_date) {
                $update_data['next_payment_date'] = $next_payment_date;
            }
            
            $wpdb->update($sub_table, $update_data, ['id' => $loan_id]);
            
            // Update vault
            if ($loan_type === 'company') {
                // Company pays - decrease vault
                $wpdb->update(
                    $wpdb->prefix . 'ffa_vaults',
                    ['balance' => $vault->balance - $payment_amount],
                    ['id' => $vault_id]
                );
                
                FFA_Database::record_cashflow(
                    'expense',
                    null,
                    $payment_amount,
                    "Company loan payment - Loan ID: $loan_id",
                    $payment_id,
                    'company_loan_payment',
                    null,
                    $vault->payment_method,
                    $vault_id,
                    $employee_id
                );
            } else {
                // Employee pays - increase vault
                $wpdb->update(
                    $wpdb->prefix . 'ffa_vaults',
                    ['balance' => $vault->balance + $payment_amount],
                    ['id' => $vault_id]
                );
                
                FFA_Database::record_cashflow(
                    'revenue',
                    null,
                    $payment_amount,
                    "Employee loan payment - Loan ID: $loan_id",
                    $payment_id,
                    'employee_loan_payment',
                    null,
                    $vault->payment_method,
                    $vault_id,
                    $employee_id
                );
            }
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            return new WP_REST_Response([
                'status' => 'success',
                'message' => 'Payment recorded successfully',
                'data' => [
                    'payment_id' => $payment_id,
                    'remaining_balance' => $new_balance,
                    'loan_status' => $new_status
                ]
            ], 201);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            return new WP_Error('db_error', $e->getMessage(), ['status' => 500]);
        }
    }
    
    // ============================================
    // AUTO SALARY DEDUCTIONS
    // ============================================
public static function process_salary_deductions($request) {
        global $wpdb;
        $params = $request->get_json_params();
        $salary_month = sanitize_text_field($params['salary_month'] ?? date('Y-m'));
        
        // ✅ أضف السطر ده هنا
        $salary_category = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE name = 'Salaries' LIMIT 1");
        
        $table_employee_loans = $wpdb->prefix . 'ffa_employee_loans';
        $table_loan_payments = $wpdb->prefix . 'ffa_loan_payments';
        $table_vaults = $wpdb->prefix . 'ffa_vaults';
        $table_salaries = $wpdb->prefix . 'shrms_salaries';
        
        // Get due loans
        $due_loans = $wpdb->get_results($wpdb->prepare("
            SELECT el.*, e.name AS employee_name
            FROM $table_employee_loans el 
            JOIN {$wpdb->prefix}shrms_employees e ON el.employee_id = e.id
            WHERE el.status = 'active' 
            AND el.remaining_balance > 0
            AND el.auto_deduct_from_salary = 1
            AND el.repayment_type = 'installments'
            AND el.installment_frequency = 'monthly'
            AND DATE_FORMAT(el.next_payment_date, '%%Y-%%m') <= %s
        ", $salary_month));
        
        $processed = 0;
        $errors = [];
        
        foreach ($due_loans as $loan) {
            try {
                // Check if already processed
                $existing = $wpdb->get_var($wpdb->prepare("
                    SELECT id FROM $table_loan_payments 
                    WHERE loan_id = %d AND loan_type = 'employee' 
                    AND salary_month = %s AND is_auto_deducted = 1
                ", $loan->id, $salary_month));
                
                if ($existing) continue;
                
                $vault = $wpdb->get_row($wpdb->prepare(
                    "SELECT * FROM $table_vaults WHERE id = %d", $loan->vault_id
                ));
                
                if (!$vault) {
                    $errors[] = "Vault not found for loan {$loan->id}";
                    continue;
                }
                
                $deduction = min($loan->installment_amount, $loan->remaining_balance);
                
                $wpdb->query('START TRANSACTION');
                
                // Insert payment
                $wpdb->insert($table_loan_payments, [
                    'loan_id' => $loan->id,
                    'loan_type' => 'employee',
                    'payment_amount' => $deduction,
                    'payment_date' => current_time('mysql', true),
                    'vault_id' => $loan->vault_id,
                    'employee_id' => $loan->employee_id,
                    'is_auto_deducted' => 1,
                    'salary_month' => $salary_month,
                    'notes' => 'Automatic salary deduction',
                    'created_at' => current_time('mysql'),
                    'created_by' => get_current_user_id() ?: 1,
                ]);
                
                // Update loan
                $new_balance = $loan->remaining_balance - $deduction;
                $new_status = $new_balance <= 0 ? 'completed' : 'active';
                $next_payment = $new_status === 'completed' ? null : 
                    date('Y-m-01', strtotime($loan->next_payment_date . ' +1 month'));
                
                $wpdb->update($table_employee_loans, [
                    'remaining_balance' => $new_balance,
                    'total_paid' => $loan->total_paid + $deduction,
                    'status' => $new_status,
                    'next_payment_date' => $next_payment
                ], ['id' => $loan->id]);
                
                // Update vault
                $wpdb->update($table_vaults, 
                    ['balance' => $vault->balance + $deduction], 
                    ['id' => $loan->vault_id]
                );
                
                // Update salary
                $wpdb->query($wpdb->prepare("
                    UPDATE $table_salaries 
                    SET deductions = deductions + %f,
                        final_salary = base_salary + bonuses - (deductions + %f) - advances
                    WHERE employee_id = %d AND month = %s
                ", $deduction, $deduction, $loan->employee_id, $salary_month));
                
                // Record cashflow
                FFA_Database::record_cashflow(
                    'revenue',
                    $salary_category ?: null,  // ✅ غيّر null لده
                    $deduction,
                    "Auto deduction from {$loan->employee_name} - Loan {$loan->id}",
                    $loan->id,
                    'auto_loan_deduction',
                    null,
                    $vault->payment_method,
                    $loan->vault_id,
                    $loan->employee_id
                );
                
                $wpdb->query('COMMIT');
                $processed++;
                
            } catch (Exception $e) {
                $wpdb->query('ROLLBACK');
                $errors[] = $e->getMessage();
            }
        }
        
        FFA_Database::clear_cache();
        
        return new WP_REST_Response([
            'status' => 'success',
            'message' => "Processed $processed deductions",
            'data' => [
                'processed' => $processed,
                'errors' => $errors
            ]
        ], 200);
    }
}


/**
 * Enable CORS for FFA API
 */
add_action('rest_api_init', function() {
    remove_filter('rest_pre_serve_request', 'rest_send_cors_headers');
    
    add_filter('rest_pre_serve_request', function($value) {
        // Allow all origins (for development)
        header('Access-Control-Allow-Origin: *');
        
        // Or specify your domain:
        // header('Access-Control-Allow-Origin: https://yourdomain.com');
        
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS, PATCH');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Headers: Authorization, Content-Type, X-WP-Nonce');
        header('Access-Control-Expose-Headers: X-WP-Total, X-WP-TotalPages');
        
        // Handle preflight requests
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            status_header(200);
            exit();
        }
        
        return $value;
    });
}, 15);
