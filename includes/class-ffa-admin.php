<?php
/**
 * FFA Admin Pages
 * All WordPress admin interface pages
 */

class FFA_Admin {
    
    /**
     * Initialize
     */
    public static function init() {
        add_action('admin_menu', [__CLASS__, 'add_menu']);
        add_action('admin_enqueue_scripts', [__CLASS__, 'enqueue_assets']);
    }
    
    /**
     * Add admin menu
     */
    public static function add_menu() {
        add_menu_page(
            'FFA Accounting',
            'FFA Accounting',
            'manage_options',
            'ffa-dashboard',
            [__CLASS__, 'dashboard_page'],
            'dashicons-calculator',
            1
        );
        
        add_submenu_page('ffa-dashboard', 'Dashboard', 'Dashboard', 'manage_options', 'ffa-dashboard', [__CLASS__, 'dashboard_page']);
        add_submenu_page('ffa-dashboard', 'Expenses', 'Expenses', 'manage_options', 'ffa-expenses', [__CLASS__, 'expenses_page']);
        add_submenu_page('ffa-dashboard', 'Categories', 'Categories', 'manage_options', 'ffa-categories', [__CLASS__, 'categories_page']);
        add_submenu_page('ffa-dashboard', 'Vaults', 'Vaults', 'manage_options', 'ffa-vaults', [__CLASS__, 'vaults_page']);
        add_submenu_page('ffa-dashboard', 'Vendors', 'Vendors', 'manage_options', 'ffa-vendors', [__CLASS__, 'vendors_page']);
        add_submenu_page('ffa-dashboard', 'Purchases', 'Purchases', 'manage_options', 'ffa-purchases', [__CLASS__, 'purchases_page']);
        add_submenu_page('ffa-dashboard', 'Loans', 'Loans', 'manage_options', 'ffa-loans', [__CLASS__, 'loans_page']);
        add_submenu_page('ffa-dashboard', 'Payroll', 'Payroll', 'manage_options', 'ffa-payroll', [__CLASS__, 'payroll_page']);
        add_submenu_page('ffa-dashboard', 'Reports', 'Reports', 'manage_options', 'ffa-reports', [__CLASS__, 'reports_page']);
        add_submenu_page('ffa-dashboard', 'Cashflow', 'Cashflow', 'manage_options', 'ffa-cashflow', [__CLASS__, 'cashflow_page']);
        add_submenu_page('ffa-dashboard', 'Settings', 'Settings', 'manage_options', 'ffa-settings', [__CLASS__, 'settings_page']);
    }
    
   /**
 * Enqueue assets
 */
public static function enqueue_assets($hook) {
    if (strpos($hook, 'ffa-') === false && strpos($hook, 'ffa_') === false) {
        return;
    }
    
    // Basic styles
    wp_add_inline_style('wp-admin', '
        .ffa-card { background: #fff; padding: 20px; margin-bottom: 20px; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }
        .ffa-stat { display: inline-block; padding: 15px 25px; margin: 10px; background: #f9f9f9; border-radius: 5px; }
        .ffa-stat-value { font-size: 24px; font-weight: bold; color: #2271b1; }
        .ffa-stat-label { font-size: 12px; color: #666; text-transform: uppercase; }
        .ffa-success { color: #46b450; }
        .ffa-danger { color: #dc3232; }
        .ffa-warning { color: #ffb900; }
        .ffa-table { width: 100%; border-collapse: collapse; }
        .ffa-table th, .ffa-table td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        .ffa-table th { background: #f9f9f9; font-weight: 600; }
        .ffa-table tr:hover { background: #f5f5f5; }
        .ffa-btn { display: inline-block; padding: 8px 16px; background: #2271b1; color: #fff; text-decoration: none; border-radius: 3px; border: none; cursor: pointer; }
        .ffa-btn:hover { background: #135e96; color: #fff; }
        .ffa-btn-danger { background: #dc3232; }
        .ffa-btn-danger:hover { background: #b32d2e; }
        .ffa-modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 9999; }
        .ffa-modal-content { position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); background: #fff; padding: 30px; border-radius: 8px; max-width: 600px; width: 90%; max-height: 90vh; overflow-y: auto; }
        .ffa-modal-close { position: absolute; top: 10px; right: 15px; font-size: 28px; cursor: pointer; }
    ');
    
    // Enqueue jQuery (already included in WordPress)
    wp_enqueue_script('jquery');
    
    // Basic scripts - FFA Payroll Integration
    wp_add_inline_script('jquery', "
        jQuery(document).ready(function($) {
            // Vault selection and commission calculation
            $(document).on('change', '.shrms-vault-selector', function() {
                var salaryId = $(this).data('salary-id');
                var vaultId = $(this).val();
                var salaryAmount = parseFloat($(this).data('salary-amount'));
                
                if (!vaultId) {
                    $('#vault-preview-' + salaryId).hide();
                    $('.ffa-pay-salary[data-salary-id=\"' + salaryId + '\"]').prop('disabled', true);
                    return;
                }
                
                // Get vault info
                $.post(ajaxurl, {
                    action: 'ffa_get_vault_info',
                    vault_id: vaultId,
                    salary_amount: salaryAmount,
                    nonce: '" . wp_create_nonce('shrms_vault_info') . "'
                }, function(response) {
                    if (response.success) {
                        var data = response.data;
                        var previewHtml = '<div style=\"margin:10px 0;padding:15px;background:#f0f9ff;border:1px solid #0284c7;border-radius:8px;\">';
                        previewHtml += '<strong style=\"color:#0369a1;\">💰 تفاصيل الدفع:</strong><br><br>';
                        previewHtml += '<table style=\"width:100%;font-size:14px;\">';
                        previewHtml += '<tr><td style=\"padding:5px 0;\">مبلغ الراتب:</td><td style=\"text-align:left;font-weight:bold;\">' + salaryAmount.toFixed(2) + ' EGP</td></tr>';
                        
                        if (data.commission > 0) {
                            previewHtml += '<tr><td style=\"padding:5px 0;\">العمولة (' + data.commission_rate + '%):</td><td style=\"text-align:left;color:red;font-weight:bold;\">+ ' + data.commission.toFixed(2) + ' EGP</td></tr>';
                        }
                        
                        previewHtml += '<tr style=\"border-top:2px solid #0369a1;\"><td style=\"padding:8px 0;\"><strong>الإجمالي المخصوم:</strong></td><td style=\"text-align:left;font-weight:bold;font-size:16px;color:#0369a1;\">' + data.total.toFixed(2) + ' EGP</td></tr>';
                        previewHtml += '<tr><td style=\"padding:5px 0;\">رصيد الخزينة:</td><td style=\"text-align:left;font-weight:bold;color:' + (data.vault_balance >= data.total ? '#16a34a' : '#dc2626') + ';\">' + data.vault_balance.toFixed(2) + ' EGP</td></tr>';
                        
                        if (data.vault_balance < data.total) {
                            previewHtml += '<tr><td colspan=\"2\" style=\"padding:10px;background:#fee2e2;color:#991b1b;border-radius:4px;margin-top:10px;\"><strong>⚠️ رصيد الخزينة غير كافٍ!</strong></td></tr>';
                        } else {
                            previewHtml += '<tr><td colspan=\"2\" style=\"padding:10px;background:#dcfce7;color:#166534;border-radius:4px;margin-top:10px;\"><strong>✓ الرصيد كافٍ</strong></td></tr>';
                        }
                        
                        previewHtml += '</table></div>';
                        
                        $('#vault-preview-' + salaryId).html(previewHtml).show();
                        
                        // Enable/disable pay button
                        var btn = $('.ffa-pay-salary[data-salary-id=\"' + salaryId + '\"]');
                        if (data.vault_balance >= data.total) {
                            btn.prop('disabled', false).css('opacity', '1');
                        } else {
                            btn.prop('disabled', true).css('opacity', '0.5');
                        }
                    } else {
                        alert('خطأ: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                }).fail(function() {
                    alert('خطأ في الاتصال بالخادم');
                });
            });
            
            // Pay salary button
            $(document).on('click', '.ffa-pay-salary', function(e) {
                e.preventDefault();
                var btn = $(this);
                var salaryId = btn.data('salary-id');
                var vaultId = $('.shrms-vault-selector[data-salary-id=\"' + salaryId + '\"]').val();
                var employeeId = btn.data('employee-id');
                var employeeName = btn.data('employee-name');
                var amount = btn.data('amount');
                
                if (!vaultId) {
                    alert('يرجى اختيار خزينة أولاً!');
                    return;
                }
                
                if (!confirm('هل تريد دفع راتب ' + employeeName + '؟\\n\\nالمبلغ: ' + amount + ' EGP')) {
                    return;
                }
                
                btn.prop('disabled', true).text('جاري الدفع...');
                
                $.post(ajaxurl, {
                    action: 'ffa_pay_shrms_salary',
                    salary_id: salaryId,
                    vault_id: vaultId,
                    employee_id: employeeId,
                    nonce: '" . wp_create_nonce('ffa_pay_salary') . "'
                }, function(response) {
                    if (response.success) {
                        alert('✅ ' + response.data.message);
                        location.reload();
                    } else {
                        alert('❌ خطأ: ' + response.data.message);
                        btn.prop('disabled', false).text('💳 دفع الراتب');
                    }
                }).fail(function() {
                    alert('❌ خطأ في الاتصال بالخادم');
                    btn.prop('disabled', false).text('💳 دفع الراتب');
                });
            });
        });
    ");
}

    
    // ============================================
    // DASHBOARD PAGE
    // ============================================
    public static function dashboard_page() {
        global $wpdb;
        
        // Get main vault balance
        $main_vault_id = get_option('ffa_main_vault', 0);
        $cash_balance = 0;
        if ($main_vault_id > 0) {
            $cash_balance = $wpdb->get_var($wpdb->prepare(
                "SELECT balance FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $main_vault_id
            )) ?: 0;
        }
        
        // Get sales reports
        $daily_sales = FFA_Database::get_sales_report('day');
        $weekly_sales = FFA_Database::get_sales_report('week');
        $monthly_sales = FFA_Database::get_sales_report('month');
        
        // Get profit margin
        $profit_margin = FFA_Database::calculate_profit_margin('month');
        
        $currency = get_option('ffa_currency', 'EGP');
        
        ?>
        <div class="wrap">
            <h1>FFA Dashboard</h1>
            
            <div class="ffa-card">
                <h2>Financial Overview</h2>
                <div style="display: flex; flex-wrap: wrap;">
                    <div class="ffa-stat">
                        <div class="ffa-stat-value"><?php echo $currency; ?> <?php echo number_format($cash_balance, 2); ?></div>
                        <div class="ffa-stat-label">Cash Balance</div>
                    </div>
                    <div class="ffa-stat">
                        <div class="ffa-stat-value ffa-success"><?php echo $currency; ?> <?php echo number_format($daily_sales['total'], 2); ?></div>
                        <div class="ffa-stat-label">Today's Sales</div>
                    </div>
                    <div class="ffa-stat">
                        <div class="ffa-stat-value ffa-success"><?php echo $currency; ?> <?php echo number_format($weekly_sales['total'], 2); ?></div>
                        <div class="ffa-stat-label">This Week</div>
                    </div>
                    <div class="ffa-stat">
                        <div class="ffa-stat-value ffa-success"><?php echo $currency; ?> <?php echo number_format($monthly_sales['total'], 2); ?></div>
                        <div class="ffa-stat-label">This Month</div>
                    </div>
                    <div class="ffa-stat">
                        <div class="ffa-stat-value"><?php echo number_format($profit_margin, 1); ?>%</div>
                        <div class="ffa-stat-label">Profit Margin</div>
                    </div>
                </div>
            </div>
            
            <div class="ffa-card">
                <h2>Sales by Warehouse (Monthly)</h2>
                <?php if (!empty($monthly_sales['by_warehouse'])): ?>
                    <table class="ffa-table">
                        <thead>
                            <tr>
                                <th>Warehouse</th>
                                <th>Total Sales</th>
                                <th>Percentage</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($monthly_sales['by_warehouse'] as $sale): ?>
                                <?php $percentage = $monthly_sales['total'] > 0 ? ($sale->total / $monthly_sales['total']) * 100 : 0; ?>
                                <tr>
                                    <td><?php echo esc_html($sale->warehouse ?: 'Unknown'); ?></td>
                                    <td><?php echo $currency; ?> <?php echo number_format($sale->total, 2); ?></td>
                                    <td><?php echo number_format($percentage, 1); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                        <tfoot>
                            <tr style="font-weight: bold;">
                                <td>Total</td>
                                <td><?php echo $currency; ?> <?php echo number_format($monthly_sales['total'], 2); ?></td>
                                <td>100%</td>
                            </tr>
                        </tfoot>
                    </table>
                <?php else: ?>
                    <p>No sales data available for this month.</p>
                <?php endif; ?>
            </div>
            
            <div class="ffa-card">
                <h2>Quick Actions</h2>
                <a href="<?php echo admin_url('admin.php?page=ffa-expenses'); ?>" class="ffa-btn">Add Expense</a>
                <a href="<?php echo admin_url('admin.php?page=ffa-vaults'); ?>" class="ffa-btn">Manage Vaults</a>
                <a href="<?php echo admin_url('admin.php?page=ffa-loans'); ?>" class="ffa-btn">Manage Loans</a>
                <a href="<?php echo admin_url('admin.php?page=ffa-reports'); ?>" class="ffa-btn">View Reports</a>
            </div>
        </div>
        <?php
    }
    
    // ============================================
    // SETTINGS PAGE
    // ============================================
    public static function settings_page() {
        // Handle form submission
        if (isset($_POST['ffa_update_settings']) && check_admin_referer('ffa_update_settings')) {
            if (isset($_POST['currency'])) {
                update_option('ffa_currency', sanitize_text_field($_POST['currency']));
            }
            
            if (isset($_POST['main_vault'])) {
                update_option('ffa_main_vault', intval($_POST['main_vault']));
            }
            
            if (isset($_POST['report_emails'])) {
                $emails = array_map('sanitize_email', array_filter(explode(',', $_POST['report_emails'])));
                update_option('ffa_report_emails', $emails);
            }
            
            if (isset($_POST['report_types'])) {
                update_option('ffa_report_types', array_map('sanitize_text_field', $_POST['report_types']));
            }
            
            echo '<div class="notice notice-success"><p>Settings updated successfully!</p></div>';
        }
        
        $currency = get_option('ffa_currency', 'EGP');
        $main_vault = get_option('ffa_main_vault', 0);
        $report_emails = get_option('ffa_report_emails', []);
        $report_types = get_option('ffa_report_types', []);
        
        $vaults = FFA_Database::get_vaults();
        
        ?>
        <div class="wrap">
            <h1>FFA Settings</h1>
            
            <div class="ffa-card">
                <form method="post">
                    <?php wp_nonce_field('ffa_update_settings'); ?>
                    
                    <table class="form-table">
                        <tr>
                            <th><label for="currency">Currency</label></th>
                            <td>
                                <select name="currency" id="currency">
                                    <option value="EGP" <?php selected($currency, 'EGP'); ?>>EGP (Egyptian Pound)</option>
                                    <option value="USD" <?php selected($currency, 'USD'); ?>>USD (US Dollar)</option>
                                    <option value="EUR" <?php selected($currency, 'EUR'); ?>>EUR (Euro)</option>
                                    <option value="GBP" <?php selected($currency, 'GBP'); ?>>GBP (British Pound)</option>
                                    <option value="SAR" <?php selected($currency, 'SAR'); ?>>SAR (Saudi Riyal)</option>
                                </select>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="main_vault">Main Vault</label></th>
                            <td>
                                <select name="main_vault" id="main_vault">
                                    <option value="0">Select Main Vault</option>
                                    <?php foreach ($vaults as $vault): ?>
                                        <option value="<?php echo $vault->id; ?>" <?php selected($main_vault, $vault->id); ?>>
                                            <?php echo esc_html($vault->name); ?> (<?php echo esc_html($vault->payment_method); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">This vault will be used for main cash balance calculations</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label for="report_emails">Report Emails</label></th>
                            <td>
                                <input type="text" name="report_emails" id="report_emails" class="regular-text" 
                                       value="<?php echo esc_attr(implode(', ', $report_emails)); ?>">
                                <p class="description">Comma-separated email addresses for automated reports</p>
                            </td>
                        </tr>
                        
                        <tr>
                            <th><label>Report Types</label></th>
                            <td>
                                <label><input type="checkbox" name="report_types[]" value="daily" <?php checked(in_array('daily', $report_types)); ?>> Daily Reports</label><br>
                                <label><input type="checkbox" name="report_types[]" value="weekly" <?php checked(in_array('weekly', $report_types)); ?>> Weekly Reports</label><br>
                                <label><input type="checkbox" name="report_types[]" value="monthly" <?php checked(in_array('monthly', $report_types)); ?>> Monthly Reports</label>
                            </td>
                        </tr>
                    </table>
                    
                    <p class="submit">
                        <input type="submit" name="ffa_update_settings" class="button button-primary" value="Save Settings">
                    </p>
                </form>
            </div>
            
            <div class="ffa-card">
                <h2>API Information</h2>
                <p><strong>API Base URL:</strong> <code><?php echo rest_url('ffa/v1'); ?></code></p>
                <p><strong>Authentication:</strong> Bearer Token (JWT)</p>
                <p>Use the login endpoint to get your token: <code>POST /ffa/v1/auth/login</code></p>
                <p>Include the token in all requests: <code>Authorization: Bearer YOUR_TOKEN</code></p>
            </div>
        </div>
        <?php
    }
    
    // ============================================
    // EXPENSES PAGE
    // ============================================
    public static function expenses_page() {
        global $wpdb;
        
        // Handle form submission
        if (isset($_POST['ffa_add_expense']) && check_admin_referer('ffa_add_expense')) {
            $vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", intval($_POST['vault_id'])
            ));
            
            $amount = floatval($_POST['amount']);
            
            if ($vault && $vault->balance >= $amount) {
                $wpdb->query('START TRANSACTION');
                
                try {
                    $data = [
                        'type' => sanitize_text_field($_POST['type']),
                        'category_id' => intval($_POST['category_id']),
                        'amount' => $amount,
                        'description' => sanitize_textarea_field($_POST['description']),
                        'warehouse' => sanitize_text_field($_POST['warehouse']),
                        'vault_id' => intval($_POST['vault_id']),
                        'employee_id' => intval($_POST['employee_id']),
                        'created_at' => current_time('mysql'),
                        'created_by' => get_current_user_id(),
                    ];
                    
                    $wpdb->insert($wpdb->prefix . 'ffa_expenses', $data);
                    $expense_id = $wpdb->insert_id;
                    
                    // Update vault
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
                    
                    echo '<div class="notice notice-success"><p>Expense added successfully!</p></div>';
                } catch (Exception $e) {
                    $wpdb->query('ROLLBACK');
                    echo '<div class="notice notice-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>Insufficient vault balance!</p></div>';
            }
        }
        
        // Handle delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && check_admin_referer('ffa_delete_expense')) {
            $wpdb->delete($wpdb->prefix . 'ffa_expenses', ['id' => intval($_GET['id'])]);
            echo '<div class="notice notice-success"><p>Expense deleted!</p></div>';
        }
        
        $expenses = $wpdb->get_results(
            "SELECT e.*, c.name AS category_name, v.name AS vault_name, emp.name AS employee_name 
             FROM {$wpdb->prefix}ffa_expenses e 
             LEFT JOIN {$wpdb->prefix}ffa_expense_categories c ON e.category_id = c.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON e.vault_id = v.id 
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON e.employee_id = emp.id 
             ORDER BY e.created_at DESC 
             LIMIT 50"
        );
        
        $categories = FFA_Database::get_categories();
        $vaults = FFA_Database::get_vaults();
        $employees = FFA_Database::get_employees();
        
        ?>
        <div class="wrap">
            <h1>Expenses Management</h1>
            
            <div class="ffa-card">
                <h2>Add New Expense</h2>
                <form method="post">
                    <?php wp_nonce_field('ffa_add_expense'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="type">Type</label></th>
                            <td>
                                <select name="type" id="type" required>
                                    <option value="fixed">Fixed</option>
                                    <option value="variable">Variable</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="category_id">Category</label></th>
                            <td>
                                <select name="category_id" id="category_id" required>
                                    <option value="">Select Category</option>
                                    <?php foreach ($categories as $cat): ?>
                                        <option value="<?php echo $cat->id; ?>"><?php echo esc_html($cat->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="amount">Amount</label></th>
                            <td><input type="number" name="amount" id="amount" step="0.01" min="0" required></td>
                        </tr>
                        <tr>
                            <th><label for="description">Description</label></th>
                            <td><textarea name="description" id="description" rows="3" required></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="warehouse">Warehouse</label></th>
                            <td>
                                <select name="warehouse" id="warehouse">
                                    <option value="">Select Warehouse</option>
                                    <option value="oraby">Oraby</option>
                                    <option value="giza">Giza</option>
                                    <option value="lavista">Lavista</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="vault_id">Vault</label></th>
                            <td>
                                <select name="vault_id" id="vault_id" required>
                                    <option value="">Select Vault</option>
                                    <?php foreach ($vaults as $vault): ?>
                                        <option value="<?php echo $vault->id; ?>">
                                            <?php echo esc_html($vault->name); ?> (Balance: <?php echo number_format($vault->balance, 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="employee_id">Employee</label></th>
                            <td>
                                <select name="employee_id" id="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo $emp->id; ?>"><?php echo esc_html($emp->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="ffa_add_expense" class="button button-primary" value="Add Expense">
                    </p>
                </form>
            </div>
            
            <div class="ffa-card">
                <h2>Recent Expenses</h2>
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Vault</th>
                            <th>Employee</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($expenses as $expense): ?>
                            <tr>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($expense->created_at))); ?></td>
                                <td><?php echo esc_html(ucfirst($expense->type)); ?></td>
                                <td><?php echo esc_html($expense->category_name); ?></td>
                                <td class="ffa-danger"><?php echo number_format($expense->amount, 2); ?></td>
                                <td><?php echo esc_html($expense->description); ?></td>
                                <td><?php echo esc_html($expense->vault_name); ?></td>
                                <td><?php echo esc_html($expense->employee_name); ?></td>
                                <td>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ffa-expenses&action=delete&id=' . $expense->id), 'ffa_delete_expense'); ?>" 
                                       class="ffa-delete" style="color: #dc3232;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ============================================
    // VAULTS PAGE
    // ============================================
    public static function vaults_page() {
        global $wpdb;
        
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'list';
        
        // Handle add vault
        if (isset($_POST['ffa_add_vault']) && check_admin_referer('ffa_add_vault')) {
            $employees = array_map('intval', $_POST['employees'] ?? []);
            $is_default = isset($_POST['is_default']) ? 1 : 0;
            
            if ($is_default) {
                $wpdb->update(
                    $wpdb->prefix . 'ffa_vaults',
                    ['is_default' => 0],
                    ['payment_method' => sanitize_text_field($_POST['payment_method'])]
                );
            }
            
            $wpdb->insert($wpdb->prefix . 'ffa_vaults', [
                'name' => sanitize_text_field($_POST['name']),
                'payment_method' => sanitize_text_field($_POST['payment_method']),
                'balance' => floatval($_POST['balance']),
                'commission_rate' => floatval($_POST['commission_rate'] ?? 0),
                'default_warehouse' => sanitize_text_field($_POST['default_warehouse'] ?? ''),
                'employees' => json_encode($employees),
                'is_default' => $is_default,
                'created_at' => current_time('mysql'),
            ]);
            
            FFA_Database::clear_cache();
            echo '<div class="notice notice-success"><p>Vault added successfully!</p></div>';
        }
        
        // Handle vault transfer
        if (isset($_POST['ffa_transfer_vault']) && check_admin_referer('ffa_transfer_vault')) {
            $from_vault_id = intval($_POST['from_vault_id']);
            $to_vault_id = intval($_POST['to_vault_id']);
            $amount = floatval($_POST['amount']);
            $employee_id = intval($_POST['employee_id']);
            
            $from_vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $from_vault_id
            ));
            $to_vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $to_vault_id
            ));
            
            if ($from_vault && $to_vault && $from_vault->balance >= $amount && $from_vault_id !== $to_vault_id) {
                $wpdb->query('START TRANSACTION');
                
                try {
                    $wpdb->update(
                        $wpdb->prefix . 'ffa_vaults',
                        ['balance' => $from_vault->balance - $amount],
                        ['id' => $from_vault_id]
                    );
                    
                    $wpdb->update(
                        $wpdb->prefix . 'ffa_vaults',
                        ['balance' => $to_vault->balance + $amount],
                        ['id' => $to_vault_id]
                    );
                    
                    FFA_Database::record_cashflow(
                        'expense',
                        null,
                        $amount,
                        "Transfer from {$from_vault->name} to {$to_vault->name}",
                        $from_vault_id,
                        'vault_transfer',
                        null,
                        $from_vault->payment_method,
                        $from_vault_id,
                        $employee_id
                    );
                    
                    FFA_Database::record_cashflow(
                        'revenue',
                        null,
                        $amount,
                        "Received transfer from {$from_vault->name}",
                        $to_vault_id,
                        'vault_transfer',
                        null,
                        $to_vault->payment_method,
                        $to_vault_id,
                        $employee_id
                    );
                    
                    $wpdb->query('COMMIT');
                    FFA_Database::clear_cache();
                    
                    echo '<div class="notice notice-success"><p>Transfer completed successfully!</p></div>';
                } catch (Exception $e) {
                    $wpdb->query('ROLLBACK');
                    echo '<div class="notice notice-error"><p>Transfer failed!</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>Invalid transfer!</p></div>';
            }
        }
        
        // Handle delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && check_admin_referer('ffa_delete_vault')) {
            $wpdb->delete($wpdb->prefix . 'ffa_vaults', ['id' => intval($_GET['id'])]);
            FFA_Database::clear_cache();
            echo '<div class="notice notice-success"><p>Vault deleted!</p></div>';
        }
        
        $vaults = FFA_Database::get_vaults(true);
        $employees = FFA_Database::get_employees();
        
        // Get dynamic payment methods from WooCommerce
        $payment_methods = FFA_Database::get_wc_payment_methods();

        
        ?>
        <div class="wrap">
            <h1>Vaults Management</h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=ffa-vaults&tab=list" class="nav-tab <?php echo $tab === 'list' ? 'nav-tab-active' : ''; ?>">Vaults List</a>
                <a href="?page=ffa-vaults&tab=add" class="nav-tab <?php echo $tab === 'add' ? 'nav-tab-active' : ''; ?>">Add Vault</a>
                <a href="?page=ffa-vaults&tab=transfer" class="nav-tab <?php echo $tab === 'transfer' ? 'nav-tab-active' : ''; ?>">Transfer Money</a>
            </h2>
            
            <?php if ($tab === 'add'): ?>
                <div class="ffa-card">
                    <h2>Add New Vault</h2>
                    <form method="post">
                        <?php wp_nonce_field('ffa_add_vault'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="name">Vault Name</label></th>
                                <td><input type="text" name="name" id="name" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><label for="payment_method">Payment Method</label></th>
                                <td>
                                    <select name="payment_method" id="payment_method" required>
                                        <?php foreach ($payment_methods as $value => $label): ?>
                                            <option value="<?php echo $value; ?>"><?php echo $label; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="balance">Initial Balance</label></th>
                                <td><input type="number" name="balance" id="balance" step="0.01" min="0" value="0"></td>
                            </tr>
                            <tr>
                                <th><label for="commission_rate">Commission Rate (%)</label></th>
                                <td>
                                    <input type="number" name="commission_rate" id="commission_rate" step="0.01" min="0" max="100" value="0">
                                    <p class="description">Percentage deducted from incoming amounts (e.g., 2.5 for 2.5%)</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="default_warehouse">Default Warehouse</label></th>
                                <td>
                                    <select name="default_warehouse" id="default_warehouse">
                                        <option value="">Select Warehouse</option>
                                        <option value="oraby">Oraby</option>
                                        <option value="giza">Giza</option>
                                        <option value="lavista">Lavista</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="employees">Authorized Employees</label></th>
                                <td>
                                    <select name="employees[]" id="employees" multiple style="min-height: 100px;">
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?php echo $emp->id; ?>"><?php echo esc_html($emp->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description">Hold Ctrl/Cmd to select multiple</p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="is_default">Default for Payment Method</label></th>
                                <td><input type="checkbox" name="is_default" id="is_default" value="1"></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="ffa_add_vault" class="button button-primary" value="Add Vault">
                        </p>
                    </form>
                </div>
                
            <?php elseif ($tab === 'transfer'): ?>
                <div class="ffa-card">
                    <h2>Transfer Money Between Vaults</h2>
                    <form method="post">
                        <?php wp_nonce_field('ffa_transfer_vault'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="from_vault_id">From Vault</label></th>
                                <td>
                                    <select name="from_vault_id" id="from_vault_id" required>
                                        <option value="">Select Source Vault</option>
                                        <?php foreach ($vaults as $vault): ?>
                                            <option value="<?php echo $vault->id; ?>">
                                                <?php echo esc_html($vault->name); ?> (Balance: <?php echo number_format($vault->balance, 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="to_vault_id">To Vault</label></th>
                                <td>
                                    <select name="to_vault_id" id="to_vault_id" required>
                                        <option value="">Select Destination Vault</option>
                                        <?php foreach ($vaults as $vault): ?>
                                            <option value="<?php echo $vault->id; ?>">
                                                <?php echo esc_html($vault->name); ?> (Balance: <?php echo number_format($vault->balance, 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="amount">Amount</label></th>
                                <td><input type="number" name="amount" id="amount" step="0.01" min="0.01" required></td>
                            </tr>
                            <tr>
                                <th><label for="employee_id">Employee</label></th>
                                <td>
                                    <select name="employee_id" id="employee_id" required>
                                        <option value="">Select Employee</option>
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?php echo $emp->id; ?>"><?php echo esc_html($emp->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="ffa_transfer_vault" class="button button-primary" value="Transfer Money">
                        </p>
                    </form>
                </div>
                
            <?php else: ?>
                <div class="ffa-card">
                    <h2>Vaults List</h2>
                    <table class="ffa-table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Payment Method</th>
                                <th>Balance</th>
                                <th>Commission</th>
                                <th>Warehouse</th>
                                <th>Default</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($vaults as $vault): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($vault->name); ?></strong></td>
                                    <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $vault->payment_method))); ?></td>
                                    <td><strong><?php echo number_format($vault->balance, 2); ?></strong></td>
                                    <td><?php echo esc_html($vault->commission_rate ?? 0); ?>%</td>
                                    <td><?php echo esc_html($vault->default_warehouse ?: '-'); ?></td>
                                    <td><?php echo $vault->is_default ? '✓' : ''; ?></td>
                                    <td>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ffa-vaults&action=delete&id=' . $vault->id), 'ffa_delete_vault'); ?>" 
                                           class="ffa-delete" style="color: #dc3232;">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        <?php
    }
    
    // ============================================
    // VENDORS PAGE
    // ============================================
    public static function vendors_page() {
        global $wpdb;
        
        // Handle add vendor
        if (isset($_POST['ffa_add_vendor']) && check_admin_referer('ffa_add_vendor')) {
            $material_ids = array_map('intval', $_POST['material_ids'] ?? []);
            $payment_methods = array_map('sanitize_text_field', $_POST['payment_methods'] ?? []);
            
            $wpdb->insert($wpdb->prefix . 'ffa_vendors', [
                'name' => sanitize_text_field($_POST['name']),
                'phone' => sanitize_text_field($_POST['phone']),
                'address' => sanitize_textarea_field($_POST['address']),
                'material_ids' => json_encode($material_ids),
                'payment_methods' => json_encode($payment_methods),
                'balance' => 0,
                'created_at' => current_time('mysql'),
            ]);
            
            echo '<div class="notice notice-success"><p>Vendor added successfully!</p></div>';
        }
        
        // Handle delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && check_admin_referer('ffa_delete_vendor')) {
            $vendor_id = intval($_GET['id']);
            $transactions = $wpdb->get_var($wpdb->prepare(
                "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_purchases WHERE vendor_id = %d", $vendor_id
            ));
            
            if ($transactions > 0) {
                echo '<div class="notice notice-error"><p>Cannot delete vendor with transactions!</p></div>';
            } else {
                $wpdb->delete($wpdb->prefix . 'ffa_vendors', ['id' => $vendor_id]);
                echo '<div class="notice notice-success"><p>Vendor deleted!</p></div>';
            }
        }
        
        $vendors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_vendors ORDER BY name ASC");
        $materials = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'fms_material' AND post_status = 'publish'");
        
        ?>
        <div class="wrap">
            <h1>Vendors Management</h1>
            
            <div class="ffa-card">
                <h2>Add New Vendor</h2>
                <form method="post">
                    <?php wp_nonce_field('ffa_add_vendor'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="name">Vendor Name</label></th>
                            <td><input type="text" name="name" id="name" class="regular-text" required></td>
                        </tr>
                        <tr>
                            <th><label for="phone">Phone</label></th>
                            <td><input type="text" name="phone" id="phone" class="regular-text"></td>
                        </tr>
                        <tr>
                            <th><label for="address">Address</label></th>
                            <td><textarea name="address" id="address" rows="3" class="large-text"></textarea></td>
                        </tr>
                        <tr>
                            <th><label for="material_ids">Materials Supplied</label></th>
                            <td>
                                <select name="material_ids[]" id="material_ids" multiple style="min-height: 100px;">
                                    <?php foreach ($materials as $material): ?>
                                        <option value="<?php echo $material->ID; ?>"><?php echo esc_html($material->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <p class="description">Hold Ctrl/Cmd to select multiple</p>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="payment_methods">Payment Methods</label></th>
                            <td>
                                <select name="payment_methods[]" id="payment_methods" multiple style="min-height: 80px;">
                                    <option value="cash">Cash</option>
                                    <option value="bank_transfer">Bank Transfer</option>
                                    <option value="check">Check</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="ffa_add_vendor" class="button button-primary" value="Add Vendor">
                    </p>
                </form>
            </div>
            
            <div class="ffa-card">
                <h2>Vendors List</h2>
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Phone</th>
                            <th>Address</th>
                            <th>Balance</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendors as $vendor): ?>
                            <tr>
                                <td><strong><?php echo esc_html($vendor->name); ?></strong></td>
                                <td><?php echo esc_html($vendor->phone ?: '-'); ?></td>
                                <td><?php echo esc_html($vendor->address ?: '-'); ?></td>
                                <td><?php echo number_format($vendor->balance, 2); ?></td>
                                <td>
                                    <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ffa-vendors&action=delete&id=' . $vendor->id), 'ffa_delete_vendor'); ?>" 
                                       class="ffa-delete" style="color: #dc3232;">Delete</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }
    
    // ============================================
    // PURCHASES PAGE
    // ============================================
    public static function purchases_page() {
        global $wpdb;
        
        // Handle add purchase
        if (isset($_POST['ffa_add_purchase']) && check_admin_referer('ffa_add_purchase')) {
            $quantity = intval($_POST['quantity']);
            $unit_cost = floatval($_POST['unit_cost']);
            $total_cost = $quantity * $unit_cost;
            
            $vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", intval($_POST['vault_id'])
            ));
            
            $vendor = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_vendors WHERE id = %d", intval($_POST['vendor_id'])
            ));
            
            if ($vault && $vendor && $vault->balance >= $total_cost) {
                $wpdb->query('START TRANSACTION');
                
                try {
                    $data = [
                        'material_id' => intval($_POST['material_id']),
                        'vendor_id' => intval($_POST['vendor_id']),
                        'quantity' => $quantity,
                        'unit_cost' => $unit_cost,
                        'total_cost' => $total_cost,
                        'vault_id' => intval($_POST['vault_id']),
                        'employee_id' => intval($_POST['employee_id']),
                        'payment_status' => 'paid',
                        'created_at' => current_time('mysql'),
                        'created_by' => get_current_user_id(),
                    ];
                    
                    $wpdb->insert($wpdb->prefix . 'ffa_purchases', $data);
                    $purchase_id = $wpdb->insert_id;
                    
                    // Update vault
                    $wpdb->update(
                        $wpdb->prefix . 'ffa_vaults',
                        ['balance' => $vault->balance - $total_cost],
                        ['id' => $vault->id]
                    );
                    
                    // Update vendor
                    $wpdb->update(
                        $wpdb->prefix . 'ffa_vendors',
                        ['balance' => $vendor->balance + $total_cost],
                        ['id' => $vendor->id]
                    );
                    
                    // Update material stock
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
                    
                    echo '<div class="notice notice-success"><p>Purchase added successfully!</p></div>';
                } catch (Exception $e) {
                    $wpdb->query('ROLLBACK');
                    echo '<div class="notice notice-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
                }
            } else {
                echo '<div class="notice notice-error"><p>Insufficient vault balance or invalid data!</p></div>';
            }
        }
        
        $purchases = $wpdb->get_results(
            "SELECT p.*, m.post_title AS material_name, v.name AS vendor_name, 
                    va.name AS vault_name, emp.name AS employee_name 
             FROM {$wpdb->prefix}ffa_purchases p 
             JOIN {$wpdb->prefix}posts m ON p.material_id = m.ID 
             JOIN {$wpdb->prefix}ffa_vendors v ON p.vendor_id = v.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults va ON p.vault_id = va.id 
             LEFT JOIN {$wpdb->prefix}shrms_employees emp ON p.employee_id = emp.id 
             ORDER BY p.created_at DESC 
             LIMIT 50"
        );
        
        $materials = $wpdb->get_results("SELECT ID, post_title FROM {$wpdb->posts} WHERE post_type = 'fms_material' AND post_status = 'publish'");
        $vendors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_vendors ORDER BY name ASC");
        $vaults = FFA_Database::get_vaults();
        $employees = FFA_Database::get_employees();
        
        ?>
        <div class="wrap">
            <h1>Purchases Management</h1>
            
            <div class="ffa-card">
                <h2>Add New Purchase</h2>
                <form method="post">
                    <?php wp_nonce_field('ffa_add_purchase'); ?>
                    <table class="form-table">
                        <tr>
                            <th><label for="material_id">Material</label></th>
                            <td>
                                <select name="material_id" id="material_id" required>
                                    <option value="">Select Material</option>
                                    <?php foreach ($materials as $material): ?>
                                        <option value="<?php echo $material->ID; ?>"><?php echo esc_html($material->post_title); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="vendor_id">Vendor</label></th>
                            <td>
                                <select name="vendor_id" id="vendor_id" required>
                                    <option value="">Select Vendor</option>
                                    <?php foreach ($vendors as $vendor): ?>
                                        <option value="<?php echo $vendor->id; ?>"><?php echo esc_html($vendor->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="quantity">Quantity</label></th>
                            <td><input type="number" name="quantity" id="quantity" min="1" required></td>
                        </tr>
                        <tr>
                            <th><label for="unit_cost">Unit Cost</label></th>
                            <td><input type="number" name="unit_cost" id="unit_cost" step="0.01" min="0" required></td>
                        </tr>
                        <tr>
                            <th><label for="vault_id">Vault</label></th>
                            <td>
                                <select name="vault_id" id="vault_id" required>
                                    <option value="">Select Vault</option>
                                    <?php foreach ($vaults as $vault): ?>
                                        <option value="<?php echo $vault->id; ?>">
                                            <?php echo esc_html($vault->name); ?> (Balance: <?php echo number_format($vault->balance, 2); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th><label for="employee_id">Employee</label></th>
                            <td>
                                <select name="employee_id" id="employee_id" required>
                                    <option value="">Select Employee</option>
                                    <?php foreach ($employees as $emp): ?>
                                        <option value="<?php echo $emp->id; ?>"><?php echo esc_html($emp->name); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                        </tr>
                    </table>
                    <p class="submit">
                        <input type="submit" name="ffa_add_purchase" class="button button-primary" value="Add Purchase">
                    </p>
                </form>
            </div>
            
            <div class="ffa-card">
                <h2>Recent Purchases</h2>
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Material</th>
                            <th>Vendor</th>
                            <th>Quantity</th>
                            <th>Unit Cost</th>
                            <th>Total Cost</th>
                            <th>Vault</th>
                            <th>Employee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($purchases as $purchase): ?>
                            <tr>
                                <td><?php echo esc_html(date('Y-m-d', strtotime($purchase->created_at))); ?></td>
                                <td><?php echo esc_html($purchase->material_name); ?></td>
                                <td><?php echo esc_html($purchase->vendor_name); ?></td>
                                <td><?php echo esc_html($purchase->quantity); ?></td>
                                <td><?php echo number_format($purchase->unit_cost, 2); ?></td>
                                <td class="ffa-danger"><strong><?php echo number_format($purchase->total_cost, 2); ?></strong></td>
                                <td><?php echo esc_html($purchase->vault_name); ?></td>
                                <td><?php echo esc_html($purchase->employee_name); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php
    }

    // ============================================
    // LOANS PAGE
    // ============================================
    public static function loans_page() {
        global $wpdb;
        
        $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'employee';
        
        // Handle add employee loan
        if (isset($_POST['ffa_add_employee_loan']) && check_admin_referer('ffa_add_employee_loan')) {
            $employee_id = intval($_POST['employee_id']);
            $loan_amount = floatval($_POST['loan_amount']);
            $vault_id = intval($_POST['vault_id']);
            
            $employee = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}shrms_employees WHERE id = %d", $employee_id
            ));
            $vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $vault_id
            ));
            
            if ($employee && $vault && $vault->balance >= $loan_amount) {
                $installment_amount = floatval($_POST['installment_amount'] ?? 0);
                
                // Check 50% limit
                if ($installment_amount > ($employee->salary * 0.5)) {
                    echo '<div class="notice notice-error"><p>Installment cannot exceed 50% of salary!</p></div>';
                } else {
                    $wpdb->query('START TRANSACTION');
                    
                    try {
                        $loan_data = [
                            'employee_id' => $employee_id,
                            'vault_id' => $vault_id,
                            'loan_amount' => $loan_amount,
                            'repayment_type' => sanitize_text_field($_POST['repayment_type']),
                            'installment_amount' => $installment_amount,
                            'installment_period' => intval($_POST['installment_period'] ?? 0),
                            'installment_frequency' => sanitize_text_field($_POST['installment_frequency'] ?? 'monthly'),
                            'auto_deduct_from_salary' => intval($_POST['auto_deduct'] ?? 1),
                            'remaining_balance' => $loan_amount,
                            'loan_date' => sanitize_text_field($_POST['loan_date']),
                            'reason' => sanitize_textarea_field($_POST['reason']),
                            'created_at' => current_time('mysql'),
                            'created_by' => get_current_user_id(),
                        ];
                        
                        if ($loan_data['repayment_type'] === 'installments') {
                            $loan_data['next_payment_date'] = date('Y-m-01', strtotime($loan_data['loan_date'] . ' +1 month'));
                        }
                        
                        $wpdb->insert($wpdb->prefix . 'ffa_employee_loans', $loan_data);
                        $loan_id = $wpdb->insert_id;
                        
                        // Update vault
                        $wpdb->update(
                            $wpdb->prefix . 'ffa_vaults',
                            ['balance' => $vault->balance - $loan_amount],
                            ['id' => $vault_id]
                        );
                        
                        // Record cashflow
                        FFA_Database::record_cashflow(
                            'expense',
                            null,
                            $loan_amount,
                            "Employee loan to {$employee->name} - Loan ID: $loan_id",
                            $loan_id,
                            'employee_loan_given',
                            null,
                            $vault->payment_method,
                            $vault_id,
                            $employee_id
                        );
                        
                        $wpdb->query('COMMIT');
                        FFA_Database::clear_cache();
                        
                        echo '<div class="notice notice-success"><p>Employee loan created successfully!</p></div>';
                    } catch (Exception $e) {
                        $wpdb->query('ROLLBACK');
                        echo '<div class="notice notice-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
                    }
                }
            } else {
                echo '<div class="notice notice-error"><p>Insufficient vault balance or invalid data!</p></div>';
            }
        }
        
        // Handle add company loan
        if (isset($_POST['ffa_add_company_loan']) && check_admin_referer('ffa_add_company_loan')) {
            $loan_amount = floatval($_POST['loan_amount']);
            $vault_id = intval($_POST['vault_id']);
            
            $vault = $wpdb->get_row($wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $vault_id
            ));
            
            if ($vault) {
                $wpdb->query('START TRANSACTION');
                
                try {
                    $loan_data = [
                        'lender_name' => sanitize_text_field($_POST['lender_name']),
                        'receiver_employee_id' => intval($_POST['receiver_employee_id']),
                        'vault_id' => $vault_id,
                        'loan_amount' => $loan_amount,
                        'repayment_type' => sanitize_text_field($_POST['repayment_type']),
                        'installment_amount' => floatval($_POST['installment_amount'] ?? 0),
                        'installment_period' => intval($_POST['installment_period'] ?? 0),
                        'installment_frequency' => sanitize_text_field($_POST['installment_frequency'] ?? 'monthly'),
                        'remaining_balance' => $loan_amount,
                        'loan_date' => sanitize_text_field($_POST['loan_date']),
                        'reason' => sanitize_textarea_field($_POST['reason']),
                        'created_at' => current_time('mysql'),
                        'created_by' => get_current_user_id(),
                    ];
                    
                    if ($loan_data['repayment_type'] === 'installments') {
                        $loan_data['next_payment_date'] = date('Y-m-d', strtotime($loan_data['loan_date'] . ' +1 month'));
                    }
                    
                    $wpdb->insert($wpdb->prefix . 'ffa_company_loans', $loan_data);
                    $loan_id = $wpdb->insert_id;
                    
                    // Update vault (add loan amount)
                    $wpdb->update(
                        $wpdb->prefix . 'ffa_vaults',
                        ['balance' => $vault->balance + $loan_amount],
                        ['id' => $vault_id]
                    );
                    
                    // Record cashflow
                    FFA_Database::record_cashflow(
                        'revenue',
                        null,
                        $loan_amount,
                        "Company loan from {$loan_data['lender_name']} - Loan ID: $loan_id",
                        $loan_id,
                        'company_loan_received',
                        null,
                        $vault->payment_method,
                        $vault_id,
                        $loan_data['receiver_employee_id']
                    );
                    
                    $wpdb->query('COMMIT');
                    FFA_Database::clear_cache();
                    
                    echo '<div class="notice notice-success"><p>Company loan created successfully!</p></div>';
                } catch (Exception $e) {
                    $wpdb->query('ROLLBACK');
                    echo '<div class="notice notice-error"><p>Error: ' . esc_html($e->getMessage()) . '</p></div>';
                }
            }
        }
        
        // Get loans
        $employee_loans = $wpdb->get_results(
            "SELECT el.*, e.name AS employee_name, e.salary, v.name AS vault_name
             FROM {$wpdb->prefix}ffa_employee_loans el 
             JOIN {$wpdb->prefix}shrms_employees e ON el.employee_id = e.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON el.vault_id = v.id
             ORDER BY el.created_at DESC LIMIT 50"
        );
        
        $company_loans = $wpdb->get_results(
            "SELECT cl.*, e.name AS receiver_name, v.name AS vault_name
             FROM {$wpdb->prefix}ffa_company_loans cl 
             JOIN {$wpdb->prefix}shrms_employees e ON cl.receiver_employee_id = e.id 
             LEFT JOIN {$wpdb->prefix}ffa_vaults v ON cl.vault_id = v.id
             ORDER BY cl.created_at DESC LIMIT 50"
        );
        
        $employees = FFA_Database::get_employees();
        $vaults = FFA_Database::get_vaults();
        
        ?>
        <div class="wrap">
            <h1>Loans Management</h1>
            
            <h2 class="nav-tab-wrapper">
                <a href="?page=ffa-loans&tab=employee" class="nav-tab <?php echo $tab === 'employee' ? 'nav-tab-active' : ''; ?>">Employee Loans</a>
                <a href="?page=ffa-loans&tab=company" class="nav-tab <?php echo $tab === 'company' ? 'nav-tab-active' : ''; ?>">Company Loans</a>
            </h2>
            
            <?php if ($tab === 'employee'): ?>
                <div class="ffa-card">
                    <h2>Add Employee Loan</h2>
                    <form method="post" id="employee-loan-form">
                        <?php wp_nonce_field('ffa_add_employee_loan'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="employee_id">Employee</label></th>
                                <td>
                                    <select name="employee_id" id="employee_id" required onchange="updateSalaryInfo(this)">
                                        <option value="">Select Employee</option>
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?php echo $emp->id; ?>" data-salary="<?php echo $emp->salary; ?>">
                                                <?php echo esc_html($emp->name); ?> (Salary: <?php echo number_format($emp->salary, 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <p class="description" id="salary-info"></p>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loan_amount">Loan Amount</label></th>
                                <td><input type="number" name="loan_amount" id="loan_amount" step="0.01" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="repayment_type">Repayment Type</label></th>
                                <td>
                                    <select name="repayment_type" id="repayment_type" onchange="toggleInstallments(this)" required>
                                        <option value="lump_sum">Lump Sum</option>
                                        <option value="installments">Installments</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="installment-field" style="display: none;">
                                <th><label for="installment_amount">Installment Amount</label></th>
                                <td>
                                    <input type="number" name="installment_amount" id="installment_amount" step="0.01" min="0" oninput="checkInstallmentLimit()">
                                    <p class="description" id="installment-warning"></p>
                                </td>
                            </tr>
                            <tr class="installment-field" style="display: none;">
                                <th><label for="installment_period">Number of Installments</label></th>
                                <td><input type="number" name="installment_period" id="installment_period" min="1"></td>
                            </tr>
                            <tr class="installment-field" style="display: none;">
                                <th><label for="installment_frequency">Frequency</label></th>
                                <td>
                                    <select name="installment_frequency" id="installment_frequency">
                                        <option value="monthly">Monthly</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="daily">Daily</option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="installment-field" style="display: none;">
                                <th><label for="auto_deduct">Auto Deduct from Salary</label></th>
                                <td><input type="checkbox" name="auto_deduct" id="auto_deduct" value="1" checked></td>
                            </tr>
                            <tr>
                                <th><label for="loan_date">Loan Date</label></th>
                                <td><input type="date" name="loan_date" id="loan_date" value="<?php echo date('Y-m-d'); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="vault_id">Vault</label></th>
                                <td>
                                    <select name="vault_id" id="vault_id" required>
                                        <option value="">Select Vault</option>
                                        <?php foreach ($vaults as $vault): ?>
                                            <option value="<?php echo $vault->id; ?>">
                                                <?php echo esc_html($vault->name); ?> (Balance: <?php echo number_format($vault->balance, 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="reason">Reason</label></th>
                                <td><textarea name="reason" id="reason" rows="3" required></textarea></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="ffa_add_employee_loan" class="button button-primary" value="Add Loan">
                        </p>
                    </form>
                </div>
                
                <div class="ffa-card">
                    <h2>Employee Loans List</h2>
                    <table class="ffa-table">
                        <thead>
                            <tr>
                                <th>Employee</th>
                                <th>Loan Amount</th>
                                <th>Remaining</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Loan Date</th>
                                <th>Next Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($employee_loans as $loan): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($loan->employee_name); ?></strong></td>
                                    <td><?php echo number_format($loan->loan_amount, 2); ?></td>
                                    <td class="ffa-danger"><strong><?php echo number_format($loan->remaining_balance, 2); ?></strong></td>
                                    <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $loan->repayment_type))); ?></td>
                                    <td>
                                        <span class="<?php echo $loan->status === 'completed' ? 'ffa-success' : ($loan->status === 'suspended' ? 'ffa-danger' : ''); ?>">
                                            <?php echo esc_html(ucfirst($loan->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($loan->loan_date); ?></td>
                                    <td><?php echo esc_html($loan->next_payment_date ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
            <?php else: ?>
                <div class="ffa-card">
                    <h2>Add Company Loan</h2>
                    <form method="post">
                        <?php wp_nonce_field('ffa_add_company_loan'); ?>
                        <table class="form-table">
                            <tr>
                                <th><label for="lender_name">Lender Name</label></th>
                                <td><input type="text" name="lender_name" id="lender_name" class="regular-text" required></td>
                            </tr>
                            <tr>
                                <th><label for="receiver_employee_id">Receiving Employee</label></th>
                                <td>
                                    <select name="receiver_employee_id" id="receiver_employee_id" required>
                                        <option value="">Select Employee</option>
                                        <?php foreach ($employees as $emp): ?>
                                            <option value="<?php echo $emp->id; ?>"><?php echo esc_html($emp->name); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loan_amount">Loan Amount</label></th>
                                <td><input type="number" name="loan_amount" id="loan_amount" step="0.01" min="0" required></td>
                            </tr>
                            <tr>
                                <th><label for="repayment_type">Repayment Type</label></th>
                                <td>
                                    <select name="repayment_type" id="repayment_type" required>
                                        <option value="lump_sum">Lump Sum</option>
                                        <option value="installments">Installments</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="loan_date">Loan Date</label></th>
                                <td><input type="date" name="loan_date" id="loan_date" value="<?php echo date('Y-m-d'); ?>" required></td>
                            </tr>
                            <tr>
                                <th><label for="vault_id">Vault</label></th>
                                <td>
                                    <select name="vault_id" id="vault_id" required>
                                        <option value="">Select Vault</option>
                                        <?php foreach ($vaults as $vault): ?>
                                            <option value="<?php echo $vault->id; ?>">
                                                <?php echo esc_html($vault->name); ?> (Balance: <?php echo number_format($vault->balance, 2); ?>)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <th><label for="reason">Reason</label></th>
                                <td><textarea name="reason" id="reason" rows="3" required></textarea></td>
                            </tr>
                        </table>
                        <p class="submit">
                            <input type="submit" name="ffa_add_company_loan" class="button button-primary" value="Add Loan">
                        </p>
                    </form>
                </div>
                
                <div class="ffa-card">
                    <h2>Company Loans List</h2>
                    <table class="ffa-table">
                        <thead>
                            <tr>
                                <th>Lender</th>
                                <th>Loan Amount</th>
                                <th>Remaining</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Loan Date</th>
                                <th>Next Payment</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($company_loans as $loan): ?>
                                <tr>
                                    <td><strong><?php echo esc_html($loan->lender_name); ?></strong></td>
                                    <td><?php echo number_format($loan->loan_amount, 2); ?></td>
                                    <td class="ffa-danger"><strong><?php echo number_format($loan->remaining_balance, 2); ?></strong></td>
                                    <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $loan->repayment_type))); ?></td>
                                    <td>
                                        <span class="<?php echo $loan->status === 'completed' ? 'ffa-success' : ($loan->status === 'defaulted' ? 'ffa-danger' : ''); ?>">
                                            <?php echo esc_html(ucfirst($loan->status)); ?>
                                        </span>
                                    </td>
                                    <td><?php echo esc_html($loan->loan_date); ?></td>
                                    <td><?php echo esc_html($loan->next_payment_date ?: '-'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
        
        <script>
        function toggleInstallments(select) {
            const fields = document.querySelectorAll('.installment-field');
            fields.forEach(field => {
                field.style.display = select.value === 'installments' ? 'table-row' : 'none';
            });
        }
        
        function updateSalaryInfo(select) {
            const salary = parseFloat(select.options[select.selectedIndex].getAttribute('data-salary')) || 0;
            const info = document.getElementById('salary-info');
            if (salary > 0) {
                info.textContent = 'Maximum recommended installment: ' + (salary * 0.5).toFixed(2) + ' (50% of salary)';
            } else {
                info.textContent = '';
            }
        }
        
        function checkInstallmentLimit() {
            const employeeSelect = document.getElementById('employee_id');
            const salary = parseFloat(employeeSelect.options[employeeSelect.selectedIndex].getAttribute('data-salary')) || 0;
            const installment = parseFloat(document.getElementById('installment_amount').value) || 0;
            const warning = document.getElementById('installment-warning');
            
            if (salary > 0 && installment > 0) {
                const percentage = (installment / salary * 100);
                if (percentage > 50) {
                    warning.textContent = '⚠️ Warning: Exceeds 50% limit! (' + percentage.toFixed(1) + '% of salary)';
                    warning.style.color = '#dc3232';
                } else {
                    warning.textContent = '✓ ' + percentage.toFixed(1) + '% of salary';
                    warning.style.color = '#46b450';
                }
            } else {
                warning.textContent = '';
            }
        }
        </script>
        <?php
    }
    

    // ============================================
// PAYROLL PAGE - ENHANCED WITH FFA INTEGRATION
// ============================================
public static function payroll_page() {
    global $wpdb;
    
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'unpaid';
    $month = isset($_GET['month']) ? sanitize_text_field($_GET['month']) : date('Y-m');
    
    // Check SHRMS
    $table_salaries = $wpdb->prefix . 'shrms_salaries';
    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$table_salaries'") === $table_salaries;
    
    if (!$table_exists) {
        echo '<div class="wrap"><h1>Payroll</h1>';
        echo '<div class="notice notice-error"><p><strong>SHRMS plugin is not active!</strong></p></div>';
        echo '</div>';
        return;
    }
    
    // Get integration status
    $ffa_active = class_exists('FFA_Database');
    $integration_enabled = class_exists('SHRMS_Integration');
    
    // Get salaries based on tab
    $status_filter = $tab === 'unpaid' ? " AND s.status = 'unpaid'" : ($tab === 'paid' ? " AND s.status = 'paid'" : "");
    
    $salaries = $wpdb->get_results($wpdb->prepare(
        "SELECT s.*, e.name, e.phone 
         FROM $table_salaries s 
         JOIN {$wpdb->prefix}shrms_employees e ON s.employee_id = e.id 
         WHERE s.month = %s $status_filter
         ORDER BY e.name",
        $month
    ));
    
    // Get vaults if FFA active
    $vaults = [];
    if ($ffa_active) {
        $vaults = FFA_Database::get_vaults();
    }
    
    // Calculate totals
    $total_payroll = array_sum(array_column($salaries, 'final_salary'));
    $paid_salaries = array_filter($salaries, function($s) { return $s->status === 'paid'; });
    $unpaid_salaries = array_filter($salaries, function($s) { return $s->status === 'unpaid'; });
    $paid_count = count($paid_salaries);
    $unpaid_count = count($unpaid_salaries);
    $paid_total = array_sum(array_column($paid_salaries, 'final_salary'));
    $unpaid_total = array_sum(array_column($unpaid_salaries, 'final_salary'));
    
    ?>
    <div class="wrap">
        <h1>💰 Payroll Management - FFA Integrated</h1>
        
        <?php if (!$ffa_active): ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ FFA Accounting plugin is not active!</strong> Financial integration disabled.</p>
            </div>
        <?php elseif (!$integration_enabled): ?>
            <div class="notice notice-warning">
                <p><strong>⚠️ SHRMS Integration class not loaded!</strong> Make sure class-shrms-integration.php is loaded.</p>
            </div>
        <?php else: ?>
            <div class="notice notice-success">
                <p><strong>✅ FFA Integration Active</strong> - All payments will be recorded in FFA accounting system.</p>
            </div>
        <?php endif; ?>
        
        <div class="ffa-card">
            <!-- Month Selector -->
            <form method="get" style="margin-bottom: 20px;">
                <input type="hidden" name="page" value="ffa-payroll">
                <input type="hidden" name="tab" value="<?php echo esc_attr($tab); ?>">
                <label for="month"><strong>Select Month:</strong></label>
                <input type="month" name="month" id="month" value="<?php echo $month; ?>" onchange="this.form.submit()">
            </form>
            
            <!-- Tabs -->
            <h2 class="nav-tab-wrapper">
                <a href="?page=ffa-payroll&tab=unpaid&month=<?php echo $month; ?>" 
                   class="nav-tab <?php echo $tab === 'unpaid' ? 'nav-tab-active' : ''; ?>">
                    Unpaid (<?php echo $unpaid_count; ?>)
                </a>
                <a href="?page=ffa-payroll&tab=paid&month=<?php echo $month; ?>" 
                   class="nav-tab <?php echo $tab === 'paid' ? 'nav-tab-active' : ''; ?>">
                    Paid (<?php echo $paid_count; ?>)
                </a>
                <a href="?page=ffa-payroll&tab=all&month=<?php echo $month; ?>" 
                   class="nav-tab <?php echo $tab === 'all' ? 'nav-tab-active' : ''; ?>">
                    All (<?php echo count($salaries); ?>)
                </a>
            </h2>
            
            <!-- Stats -->
            <div style="display: flex; gap: 20px; margin: 20px 0;">
                <div class="ffa-stat">
                    <div class="ffa-stat-value"><?php echo count($salaries); ?></div>
                    <div class="ffa-stat-label">Total Employees</div>
                </div>
                <div class="ffa-stat">
                    <div class="ffa-stat-value ffa-success"><?php echo $paid_count; ?></div>
                    <div class="ffa-stat-label">Paid (<?php echo number_format($paid_total, 2); ?> EGP)</div>
                </div>
                <div class="ffa-stat">
                    <div class="ffa-stat-value ffa-danger"><?php echo $unpaid_count; ?></div>
                    <div class="ffa-stat-label">Unpaid (<?php echo number_format($unpaid_total, 2); ?> EGP)</div>
                </div>
                <div class="ffa-stat">
                    <div class="ffa-stat-value"><?php echo number_format($total_payroll, 2); ?></div>
                    <div class="ffa-stat-label">Total Payroll</div>
                </div>
            </div>
            
            <?php if (empty($salaries)): ?>
                <p style="padding: 20px; text-align: center; color: #666;">
                    No salaries found for <?php echo date('F Y', strtotime($month . '-01')); ?>
                </p>
            <?php else: ?>
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Base Salary</th>
                            <th>Bonuses</th>
                            <th>Deductions</th>
                            <th>Advances</th>
                            <th>Final Salary</th>
                            <th>Status</th>
                            <?php if ($ffa_active && $tab === 'unpaid'): ?>
                                <th>Vault</th>
                                <th>Action</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($salaries as $salary): ?>
                            <tr>
                                <td>
                                    <strong><?php echo esc_html($salary->name); ?></strong><br>
                                    <small style="color: #666;"><?php echo esc_html($salary->phone); ?></small>
                                </td>
                                <td><?php echo number_format($salary->base_salary, 2); ?></td>
                                <td class="ffa-success"><?php echo number_format($salary->bonuses, 2); ?></td>
                                <td class="ffa-danger"><?php echo number_format($salary->deductions, 2); ?></td>
                                <td class="ffa-warning"><?php echo number_format($salary->advances, 2); ?></td>
                                <td><strong style="font-size: 16px;"><?php echo number_format($salary->final_salary, 2); ?></strong></td>
                                <td>
                                    <?php if ($salary->status === 'paid'): ?>
                                        <span class="ffa-success" style="font-weight: bold;">✓ Paid</span>
                                        <?php if ($salary->paid_at): ?>
                                            <br><small style="color: #666;"><?php echo date('Y-m-d H:i', strtotime($salary->paid_at)); ?></small>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <span class="ffa-warning" style="font-weight: bold;">⏳ Unpaid</span>
                                    <?php endif; ?>
                                </td>
                                
                                <?php if ($ffa_active && $salary->status === 'unpaid'): ?>
                                    <td>
                                        <select class="shrms-vault-selector" 
                                                data-salary-id="<?php echo $salary->id; ?>" 
                                                data-salary-amount="<?php echo $salary->final_salary; ?>"
                                                style="width: 100%; max-width: 200px;">
                                            <option value="">-- Select Vault --</option>
                                            <?php foreach ($vaults as $vault): ?>
                                                <option value="<?php echo $vault->id; ?>" 
                                                        data-balance="<?php echo $vault->balance; ?>"
                                                        data-commission="<?php echo $vault->commission_rate; ?>">
                                                    <?php echo esc_html($vault->name); ?>
                                                    (<?php echo number_format($vault->balance, 0); ?> EGP)
                                                    <?php if ($vault->commission_rate > 0): ?>
                                                        - <?php echo $vault->commission_rate; ?>% fee
                                                    <?php endif; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <div id="vault-preview-<?php echo $salary->id; ?>" style="display:none; margin-top: 10px;"></div>
                                    </td>
                                    <td>
                                        <button type="button" 
                                            class="ffa-btn ffa-pay-salary" 
                                            data-salary-id="<?php echo $salary->id; ?>"
                                            data-employee-id="<?php echo $salary->employee_id; ?>"
                                                disabled>
                                            💳 Pay Salary
                                        </button>
                                    </td>
                                <?php elseif ($salary->status === 'unpaid'): ?>
                                    <td colspan="2" style="color: #999; text-align: center;">
                                        <em>FFA not active</em>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold; background: #f9f9f9;">
                            <td>Total</td>
                            <td><?php echo number_format(array_sum(array_column($salaries, 'base_salary')), 2); ?></td>
                            <td class="ffa-success"><?php echo number_format(array_sum(array_column($salaries, 'bonuses')), 2); ?></td>
                            <td class="ffa-danger"><?php echo number_format(array_sum(array_column($salaries, 'deductions')), 2); ?></td>
                            <td class="ffa-warning"><?php echo number_format(array_sum(array_column($salaries, 'advances')), 2); ?></td>
                            <td><strong style="font-size: 18px;"><?php echo number_format($total_payroll, 2); ?></strong></td>
                            <td>-</td>
                            <?php if ($ffa_active && $tab === 'unpaid'): ?>
                                <td colspan="2">-</td>
                            <?php endif; ?>
                        </tr>
                    </tfoot>
                </table>
            <?php endif; ?>
        </div>
        
        <?php if ($ffa_active && !empty($vaults)): ?>
            <div class="ffa-card" style="margin-top: 20px;">
                <h3>📊 Available Vaults</h3>
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th>Vault Name</th>
                            <th>Payment Method</th>
                            <th>Current Balance</th>
                            <th>Commission Rate</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vaults as $vault): ?>
                            <tr>
                                <td><strong><?php echo esc_html($vault->name); ?></strong></td>
                                <td><?php echo esc_html(ucfirst(str_replace('_', ' ', $vault->payment_method))); ?></td>
                                <td><strong><?php echo number_format($vault->balance, 2); ?> EGP</strong></td>
                                <td><?php echo $vault->commission_rate; ?>%</td>
                                <td>
                                    <?php if ($vault->balance > 0): ?>
                                        <span class="ffa-success">✓ Active</span>
                                    <?php else: ?>
                                        <span class="ffa-danger">⚠️ Low Balance</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
    <?php
}


    // ============================================
    // REPORTS PAGE
    // ============================================
    public static function reports_page() {
        $period = isset($_GET['period']) ? sanitize_text_field($_GET['period']) : 'month';
        
        $sales_report = FFA_Database::get_sales_report($period);
        $profit_margin = FFA_Database::calculate_profit_margin($period);
        $currency = get_option('ffa_currency', 'EGP');
        
        ?>
        <div class="wrap">
            <h1>Financial Reports</h1>
            
            <div class="ffa-card">
                <form method="get" style="margin-bottom: 20px;">
                    <input type="hidden" name="page" value="ffa-reports">
                    <label for="period">Period:</label>
                    <select name="period" id="period" onchange="this.form.submit()">
                        <option value="day" <?php selected($period, 'day'); ?>>Daily</option>
                        <option value="week" <?php selected($period, 'week'); ?>>Weekly</option>
                        <option value="month" <?php selected($period, 'month'); ?>>Monthly</option>
                    </select>
                </form>
                
                <h2>Sales Report (<?php echo ucfirst($period); ?>)</h2>
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th>Warehouse</th>
                            <th>Total Sales</th>
                            <th>Percentage</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($sales_report['by_warehouse'])): ?>
                            <?php foreach ($sales_report['by_warehouse'] as $sale): ?>
                                <?php $percentage = $sales_report['total'] > 0 ? ($sale->total / $sales_report['total']) * 100 : 0; ?>
                                <tr>
                                    <td><?php echo esc_html($sale->warehouse ?: 'Unknown'); ?></td>
                                    <td class="ffa-success"><strong><?php echo $currency; ?> <?php echo number_format($sale->total, 2); ?></strong></td>
                                    <td><?php echo number_format($percentage, 1); ?>%</td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="3">No sales data available</td></tr>
                        <?php endif; ?>
                    </tbody>
                    <tfoot>
                        <tr style="font-weight: bold;">
                            <td>Total</td>
                            <td class="ffa-success"><?php echo $currency; ?> <?php echo number_format($sales_report['total'], 2); ?></td>
                            <td>100%</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
            
            <div class="ffa-card">
                <h2>Profit Analysis</h2>
                <div class="ffa-stat">
                    <div class="ffa-stat-value <?php echo $profit_margin > 0 ? 'ffa-success' : 'ffa-danger'; ?>">
                        <?php echo number_format($profit_margin, 1); ?>%
                    </div>
                    <div class="ffa-stat-label">Profit Margin</div>
                </div>
            </div>
        </div>
        <?php
    }
    
    // ============================================
    // CASHFLOW PAGE
    // ============================================
    public static function cashflow_page() {
        global $wpdb;
        
        $type = isset($_GET['type']) ? sanitize_text_field($_GET['type']) : 'all';
        $page = max(1, intval($_GET['paged'] ?? 1));
        $per_page = 50;
        $offset = ($page - 1) * $per_page;
        
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
        
        $total_pages = ceil($total / $per_page);
        
        $currency = get_option('ffa_currency', 'EGP');
        
        ?>
        <div class="wrap">
            <h1>Cashflow History</h1>
            
            <div class="ffa-card">
                <form method="get" style="margin-bottom: 20px;">
                    <input type="hidden" name="page" value="ffa-cashflow">
                    <label for="type">Filter by Type:</label>
                    <select name="type" id="type" onchange="this.form.submit()">
                        <option value="all" <?php selected($type, 'all'); ?>>All</option>
                        <option value="revenue" <?php selected($type, 'revenue'); ?>>Revenue</option>
                        <option value="expense" <?php selected($type, 'expense'); ?>>Expense</option>
                    </select>
                </form>
                
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Category</th>
                            <th>Amount</th>
                            <th>Description</th>
                            <th>Vault</th>
                            <th>Employee</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cashflows as $flow): ?>
                            <tr>
                                <td><?php echo esc_html(date('Y-m-d H:i', strtotime($flow->created_at))); ?></td>
                                <td>
                                    <span class="<?php echo $flow->type === 'revenue' ? 'ffa-success' : 'ffa-danger'; ?>">
                                        <?php echo esc_html(ucfirst($flow->type)); ?>
                                    </span>
                                </td>
                                <td><?php echo esc_html($flow->category_name ?: '-'); ?></td>
                                <td class="<?php echo $flow->type === 'revenue' ? 'ffa-success' : 'ffa-danger'; ?>">
                                    <strong><?php echo $flow->type === 'revenue' ? '+' : '-'; ?><?php echo $currency; ?> <?php echo number_format($flow->amount, 2); ?></strong>
                                </td>
                                <td><?php echo esc_html($flow->description); ?></td>
                                <td><?php echo esc_html($flow->vault_name ?: '-'); ?></td>
                                <td><?php echo esc_html($flow->employee_name ?: '-'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <?php if ($total_pages > 1): ?>
                    <div style="margin-top: 20px; text-align: center;">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=ffa-cashflow&type=<?php echo $type; ?>&paged=<?php echo $i; ?>" 
                               class="ffa-btn <?php echo $i === $page ? 'button-primary' : ''; ?>" 
                               style="margin: 0 2px;">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }


// ============================================
// CATEGORIES PAGE
// ============================================
public static function categories_page() {
    global $wpdb;
    
    $table = $wpdb->prefix . 'ffa_expense_categories';
    
    // Handle add category
    if (isset($_POST['ffa_add_category']) && check_admin_referer('ffa_add_category')) {
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        
        // Check if name exists
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE name = %s", $name
        ));
        
        if ($exists) {
            echo '<div class="notice notice-error"><p>Category name already exists!</p></div>';
        } else {
            $result = $wpdb->insert($table, [
                'name' => $name,
                'description' => $description,
                'created_at' => current_time('mysql')
            ]);
            
            if ($result) {
                FFA_Database::clear_cache();
                echo '<div class="notice notice-success"><p>Category added successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to add category!</p></div>';
            }
        }
    }
    
    // Handle edit category
    if (isset($_POST['ffa_edit_category']) && check_admin_referer('ffa_edit_category')) {
        $id = intval($_POST['category_id']);
        $name = sanitize_text_field($_POST['name']);
        $description = sanitize_textarea_field($_POST['description']);
        
        // Check if name exists (excluding current category)
        $exists = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE name = %s AND id != %d", $name, $id
        ));
        
        if ($exists) {
            echo '<div class="notice notice-error"><p>Category name already exists!</p></div>';
        } else {
            $result = $wpdb->update(
                $table,
                ['name' => $name, 'description' => $description],
                ['id' => $id]
            );
            
            if ($result !== false) {
                FFA_Database::clear_cache();
                echo '<div class="notice notice-success"><p>Category updated successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to update category!</p></div>';
            }
        }
    }
    
    // Handle delete category
    if (isset($_GET['action']) && $_GET['action'] === 'delete' && check_admin_referer('ffa_delete_category')) {
        $id = intval($_GET['id']);
        
        // Check if category is used
        $used_in_expenses = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_expenses WHERE category_id = %d", $id
        ));
        
        $used_in_cashflow = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_cashflow WHERE category_id = %d", $id
        ));
        
        if ($used_in_expenses > 0 || $used_in_cashflow > 0) {
            echo '<div class="notice notice-error"><p>Cannot delete category! It is used in ' . 
                 ($used_in_expenses + $used_in_cashflow) . ' transactions.</p></div>';
        } else {
            $result = $wpdb->delete($table, ['id' => $id]);
            
            if ($result) {
                FFA_Database::clear_cache();
                echo '<div class="notice notice-success"><p>Category deleted successfully!</p></div>';
            } else {
                echo '<div class="notice notice-error"><p>Failed to delete category!</p></div>';
            }
        }
    }
    
    // Get edit mode
    $edit_mode = isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id']);
    $edit_category = null;
    
    if ($edit_mode) {
        $edit_category = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table WHERE id = %d", intval($_GET['id'])
        ));
        
        if (!$edit_category) {
            $edit_mode = false;
        }
    }
    
    // Get all categories
    $categories = $wpdb->get_results("SELECT * FROM $table ORDER BY name ASC");
    
    // Get usage count for each category
    foreach ($categories as $category) {
        $category->expense_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_expenses WHERE category_id = %d", $category->id
        ));
        
        $category->cashflow_count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->prefix}ffa_cashflow WHERE category_id = %d", $category->id
        ));
        
        $category->total_usage = $category->expense_count + $category->cashflow_count;
    }
    
    ?>
    <div class="wrap">
        <h1>📂 Expense Categories Management</h1>
        
        <div class="ffa-card">
            <h2><?php echo $edit_mode ? '✏️ Edit Category' : '➕ Add New Category'; ?></h2>
            <form method="post">
                <?php 
                if ($edit_mode) {
                    wp_nonce_field('ffa_edit_category');
                    echo '<input type="hidden" name="category_id" value="' . $edit_category->id . '">';
                } else {
                    wp_nonce_field('ffa_add_category');
                }
                ?>
                <table class="form-table">
                    <tr>
                        <th><label for="name">Category Name *</label></th>
                        <td>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   class="regular-text" 
                                   value="<?php echo $edit_mode ? esc_attr($edit_category->name) : ''; ?>" 
                                   required>
                            <p class="description">e.g., Salaries, Rent, Utilities, Marketing</p>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="description">Description</label></th>
                        <td>
                            <textarea name="description" 
                                      id="description" 
                                      rows="3" 
                                      class="large-text"><?php echo $edit_mode ? esc_textarea($edit_category->description) : ''; ?></textarea>
                            <p class="description">Optional description for this category</p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <?php if ($edit_mode): ?>
                        <input type="submit" name="ffa_edit_category" class="button button-primary" value="Update Category">
                        <a href="?page=ffa-categories" class="button">Cancel</a>
                    <?php else: ?>
                        <input type="submit" name="ffa_add_category" class="button button-primary" value="Add Category">
                    <?php endif; ?>
                </p>
            </form>
        </div>
        
        <div class="ffa-card">
            <h2>📋 Categories List</h2>
            
            <?php if (empty($categories)): ?>
                <p style="padding: 20px; text-align: center; color: #666;">
                    No categories found. Add your first category above.
                </p>
            <?php else: ?>
                <table class="ffa-table">
                    <thead>
                        <tr>
                            <th style="width: 50px;">ID</th>
                            <th>Name</th>
                            <th>Description</th>
                            <th style="width: 100px; text-align: center;">Usage Count</th>
                            <th style="width: 150px;">Created</th>
                            <th style="width: 150px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($categories as $category): ?>
                            <tr>
                                <td><strong>#<?php echo $category->id; ?></strong></td>
                                <td>
                                    <strong style="font-size: 14px;"><?php echo esc_html($category->name); ?></strong>
                                </td>
                                <td><?php echo esc_html($category->description ?: '-'); ?></td>
                                <td style="text-align: center;">
                                    <?php if ($category->total_usage > 0): ?>
                                        <span class="ffa-success" style="font-weight: bold;">
                                            <?php echo number_format($category->total_usage); ?>
                                        </span>
                                    <?php else: ?>
                                        <span style="color: #999;">0</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('Y-m-d', strtotime($category->created_at)); ?></td>
                                <td style="text-align: center;">
                                    <a href="?page=ffa-categories&action=edit&id=<?php echo $category->id; ?>" 
                                       class="button button-small">
                                        ✏️ Edit
                                    </a>
                                    
                                    <?php if ($category->total_usage == 0): ?>
                                        <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=ffa-categories&action=delete&id=' . $category->id), 'ffa_delete_category'); ?>" 
                                           class="button button-small" 
                                           style="color: #dc3232;"
                                           onclick="return confirm('Are you sure you want to delete this category?');">
                                            🗑️ Delete
                                        </a>
                                    <?php else: ?>
                                        <span style="color: #999; font-size: 11px;">
                                            (In Use)
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div style="margin-top: 20px; padding: 15px; background: #f0f9ff; border-left: 4px solid #0284c7; border-radius: 4px;">
                    <strong style="color: #0369a1;">💡 Note:</strong>
                    <p style="margin: 5px 0 0 0; color: #666;">
                        Categories that are being used in expenses or cashflow records cannot be deleted. 
                        You can only edit their name and description.
                    </p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
}


}
