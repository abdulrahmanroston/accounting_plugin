<?php
/**
 * FFA Data Migration Tool
 * Import data from old FFA plugin version to new optimized version
 * 
 * USAGE:
 * 1. Upload this file to: wp-content/plugins/frozen-factory-accounting/
 * 2. Access: yourdomain.com/wp-content/plugins/frozen-factory-accounting/ffa-migration.php
 * 3. Click "Start Migration"
 * 4. DELETE this file after successful migration for security
 */

// Security check
define('FFA_MIGRATION', true);

// Load WordPress
$wp_load = dirname(dirname(dirname(__DIR__))) . '/wp-load.php';
if (!file_exists($wp_load)) {
    die('WordPress not found. Please check file path.');
}
require_once($wp_load);

// Check if user is admin
if (!current_user_can('manage_options')) {
    die('Unauthorized access. Admin privileges required.');
}

global $wpdb;

// Handle migration
$migration_result = null;
if (isset($_POST['start_migration']) && check_admin_referer('ffa_migration')) {
    $migration_result = ffa_migrate_data();
}

/**
 * Main migration function
 */
function ffa_migrate_data() {
    global $wpdb;
    
    $results = [
        'success' => true,
        'messages' => [],
        'errors' => [],
        'stats' => [
            'expenses' => 0,
            'vaults' => 0,
            'vendors' => 0,
            'purchases' => 0,
            'cashflow' => 0,
            'company_loans' => 0,
            'employee_loans' => 0,
            'loan_payments' => 0,
        ]
    ];
    
    $wpdb->query('START TRANSACTION');
    
    try {
        // 1. Migrate Expenses
        $old_expenses = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_expenses");
        foreach ($old_expenses as $expense) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_expenses WHERE id = %d", $expense->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_expenses', (array)$expense);
                $results['stats']['expenses']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['expenses']} expenses";
        
        // 2. Migrate Expense Categories
        $old_categories = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_expense_categories");
        foreach ($old_categories as $category) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE id = %d", $category->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_expense_categories', (array)$category);
            }
        }
        $results['messages'][] = "✓ Migrated " . count($old_categories) . " expense categories";
        
        // 3. Migrate Vaults
        $old_vaults = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_vaults");
        foreach ($old_vaults as $vault) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_vaults WHERE id = %d", $vault->id
            ));
            
            if (!$exists) {
                $data = (array)$vault;
                // Ensure commission_rate exists
                if (!isset($data['commission_rate'])) {
                    $data['commission_rate'] = 0;
                }
                $wpdb->insert($wpdb->prefix . 'ffa_vaults', $data);
                $results['stats']['vaults']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['vaults']} vaults";
        
        // 4. Migrate Vendors
        $old_vendors = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_vendors");
        foreach ($old_vendors as $vendor) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_vendors WHERE id = %d", $vendor->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_vendors', (array)$vendor);
                $results['stats']['vendors']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['vendors']} vendors";
        
        // 5. Migrate Purchases
        $old_purchases = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_purchases");
        foreach ($old_purchases as $purchase) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_purchases WHERE id = %d", $purchase->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_purchases', (array)$purchase);
                $results['stats']['purchases']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['purchases']} purchases";
        
        // 6. Migrate Cashflow
        $old_cashflow = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_cashflow");
        foreach ($old_cashflow as $flow) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_cashflow WHERE id = %d", $flow->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_cashflow', (array)$flow);
                $results['stats']['cashflow']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['cashflow']} cashflow records";
        
        // 7. Migrate Company Loans
        $old_company_loans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_company_loans");
        foreach ($old_company_loans as $loan) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_company_loans WHERE id = %d", $loan->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_company_loans', (array)$loan);
                $results['stats']['company_loans']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['company_loans']} company loans";
        
        // 8. Migrate Employee Loans
        $old_employee_loans = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_employee_loans");
        foreach ($old_employee_loans as $loan) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_employee_loans WHERE id = %d", $loan->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_employee_loans', (array)$loan);
                $results['stats']['employee_loans']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['employee_loans']} employee loans";
        
        // 9. Migrate Loan Payments
        $old_loan_payments = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}ffa_loan_payments");
        foreach ($old_loan_payments as $payment) {
            $exists = $wpdb->get_var($wpdb->prepare(
                "SELECT id FROM {$wpdb->prefix}ffa_loan_payments WHERE id = %d", $payment->id
            ));
            
            if (!$exists) {
                $wpdb->insert($wpdb->prefix . 'ffa_loan_payments', (array)$payment);
                $results['stats']['loan_payments']++;
            }
        }
        $results['messages'][] = "✓ Migrated {$results['stats']['loan_payments']} loan payments";
        
        // 10. Migrate Settings
        $old_settings = [
            'ffa_currency',
            'ffa_main_vault',
            'ffa_report_emails',
            'ffa_report_types'
        ];
        
        foreach ($old_settings as $setting) {
            $value = get_option($setting);
            if ($value !== false) {
                update_option($setting, $value);
            }
        }
        $results['messages'][] = "✓ Migrated settings";
        
        $wpdb->query('COMMIT');
        
        // Clear cache
        if (class_exists('FFA_Database')) {
            FFA_Database::clear_cache();
        }
        
        $results['messages'][] = "<strong>✓ Migration completed successfully!</strong>";
        
    } catch (Exception $e) {
        $wpdb->query('ROLLBACK');
        $results['success'] = false;
        $results['errors'][] = "Migration failed: " . $e->getMessage();
        error_log('FFA Migration Error: ' . $e->getMessage());
    }
    
    return $results;
}

/**
 * Check if old data exists
 */
function ffa_check_old_data() {
    global $wpdb;
    
    $tables = [
        'ffa_expenses',
        'ffa_expense_categories',
        'ffa_vaults',
        'ffa_vendors',
        'ffa_purchases',
        'ffa_cashflow',
        'ffa_company_loans',
        'ffa_employee_loans',
        'ffa_loan_payments'
    ];
    
    $counts = [];
    foreach ($tables as $table) {
        $full_table = $wpdb->prefix . $table;
        if ($wpdb->get_var("SHOW TABLES LIKE '$full_table'") === $full_table) {
            $count = $wpdb->get_var("SELECT COUNT(*) FROM $full_table");
            $counts[$table] = intval($count);
        } else {
            $counts[$table] = 0;
        }
    }
    
    return $counts;
}

$old_data_counts = ffa_check_old_data();
$has_old_data = array_sum($old_data_counts) > 0;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FFA Data Migration</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f0f0f1; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); padding: 30px; }
        h1 { color: #1d2327; margin-bottom: 10px; }
        h2 { color: #1d2327; margin-top: 30px; margin-bottom: 15px; font-size: 20px; }
        .warning { background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; padding: 15px; margin: 20px 0; color: #856404; }
        .success { background: #d4edda; border: 1px solid #28a745; border-radius: 4px; padding: 15px; margin: 20px 0; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #dc3545; border-radius: 4px; padding: 15px; margin: 20px 0; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #17a2b8; border-radius: 4px; padding: 15px; margin: 20px 0; color: #0c5460; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-card { background: #f8f9fa; border-radius: 4px; padding: 15px; text-align: center; }
        .stat-value { font-size: 32px; font-weight: bold; color: #2271b1; }
        .stat-label { font-size: 12px; color: #666; text-transform: uppercase; margin-top: 5px; }
        .btn { display: inline-block; padding: 12px 24px; background: #2271b1; color: white; text-decoration: none; border-radius: 4px; border: none; cursor: pointer; font-size: 16px; transition: all 0.3s; }
        .btn:hover { background: #135e96; }
        .btn-danger { background: #dc3545; }
        .btn-danger:hover { background: #c82333; }
        .btn:disabled { background: #ccc; cursor: not-allowed; }
        ul { margin: 10px 0 10px 20px; }
        li { margin: 5px 0; }
        .step { background: #f8f9fa; border-left: 4px solid #2271b1; padding: 15px; margin: 10px 0; }
        .step-number { display: inline-block; width: 30px; height: 30px; background: #2271b1; color: white; border-radius: 50%; text-align: center; line-height: 30px; font-weight: bold; margin-right: 10px; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; color: #d63638; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔄 FFA Data Migration Tool</h1>
        <p style="color: #666; margin-bottom: 20px;">Migrate your data from old FFA version to the new optimized system</p>
        
        <?php if ($migration_result): ?>
            <?php if ($migration_result['success']): ?>
                <div class="success">
                    <h3 style="margin-bottom: 10px;">✓ Migration Successful!</h3>
                    <ul>
                        <?php foreach ($migration_result['messages'] as $message): ?>
                            <li><?php echo $message; ?></li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <h4 style="margin-top: 20px; margin-bottom: 10px;">Migration Statistics:</h4>
                    <div class="stats">
                        <?php foreach ($migration_result['stats'] as $type => $count): ?>
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $count; ?></div>
                                <div class="stat-label"><?php echo ucwords(str_replace('_', ' ', $type)); ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="warning" style="margin-top: 20px;">
                        <strong>⚠️ Important:</strong> Please delete this file (<code>ffa-migration.php</code>) from your server for security reasons!
                    </div>
                    
                    <a href="<?php echo admin_url('admin.php?page=ffa-dashboard'); ?>" class="btn">Go to FFA Dashboard</a>
                </div>
            <?php else: ?>
                <div class="error">
                    <h3 style="margin-bottom: 10px;">✗ Migration Failed</h3>
                    <ul>
                        <?php foreach ($migration_result['errors'] as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php else: ?>
            
            <h2>📊 Current Data Overview</h2>
            <?php if ($has_old_data): ?>
                <div class="info">
                    <strong>✓ Old data detected!</strong> Ready to migrate.
                </div>
                
                <div class="stats">
                    <?php foreach ($old_data_counts as $table => $count): ?>
                        <?php if ($count > 0): ?>
                            <div class="stat-card">
                                <div class="stat-value"><?php echo $count; ?></div>
                                <div class="stat-label"><?php echo ucwords(str_replace(['ffa_', '_'], ['', ' '], $table)); ?></div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="warning">
                    <strong>⚠️ No old data found!</strong> Either:
                    <ul>
                        <li>This is a fresh installation</li>
                        <li>Data has already been migrated</li>
                        <li>Old tables don't exist</li>
                    </ul>
                </div>
            <?php endif; ?>
            
            <h2>📝 Migration Instructions</h2>
            
            <div class="step">
                <span class="step-number">1</span>
                <strong>Backup Your Database</strong><br>
                Before proceeding, create a complete database backup using phpMyAdmin or your hosting control panel.
            </div>
            
            <div class="step">
                <span class="step-number">2</span>
                <strong>Deactivate Old Plugin</strong><br>
                Go to <code>Plugins → Installed Plugins</code> and deactivate the old FFA plugin (don't delete yet).
            </div>
            
            <div class="step">
                <span class="step-number">3</span>
                <strong>Activate New Plugin</strong><br>
                Activate the new optimized FFA plugin. This will create the new database structure.
            </div>
            
            <div class="step">
                <span class="step-number">4</span>
                <strong>Start Migration</strong><br>
                Click the button below to migrate all your data to the new system.
            </div>
            
            <div class="step">
                <span class="step-number">5</span>
                <strong>Verify Data</strong><br>
                After migration, verify your data in the FFA Dashboard and check all modules.
            </div>
            
            <div class="step">
                <span class="step-number">6</span>
                <strong>Delete Old Plugin</strong><br>
                Once verified, you can safely delete the old plugin and this migration file.
            </div>
            
            <div class="warning" style="margin-top: 30px;">
                <strong>⚠️ Important Notes:</strong>
                <ul>
                    <li>Migration will not duplicate existing data</li>
                    <li>All data will be preserved (no deletions)</li>
                    <li>Process may take a few minutes depending on data size</li>
                    <li>Do not close this page during migration</li>
                </ul>
            </div>
            
            <form method="post" onsubmit="return confirm('Are you sure you want to start the migration? Make sure you have a database backup!');">
                <?php wp_nonce_field('ffa_migration'); ?>
                <button type="submit" name="start_migration" class="btn" <?php echo !$has_old_data ? 'disabled' : ''; ?>>
                    🚀 Start Migration
                </button>
            </form>
            
        <?php endif; ?>
    </div>
</body>
</html>
