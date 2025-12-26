<?php
/**
 * FFA - SHRMS Payroll Integration
 * Handle payroll display and payment in FFA
 * 
 * @package FFA
 * @subpackage SHRMS Integration
 */

if (!defined('ABSPATH')) exit;

class FFA_SHRMS_Payroll {
    
    /**
     * Initialize
     */
    public static function init() {
        // Only if SHRMS is active
        if (!class_exists('SHRMS_Core')) {
            return;
        }
        
        // AJAX handlers
        add_action('wp_ajax_ffa_pay_shrms_salary', [__CLASS__, 'ajax_pay_salary']);
    }
    
    /**
     * AJAX: Pay SHRMS salary from FFA
     */
    public static function ajax_pay_salary() {
        check_ajax_referer('ffa_pay_salary', 'nonce');
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => 'غير مصرح']);
        }
        
        global $wpdb;
        
        $salary_id = intval($_POST['salary_id']);
        $vault_id = intval($_POST['vault_id']);
        $employee_id = intval($_POST['employee_id']);
        
        if (!$salary_id || !$vault_id || !$employee_id) {
            wp_send_json_error(['message' => 'بيانات ناقصة']);
        }
        
        // Get salary from SHRMS
        $salary = $wpdb->get_row($wpdb->prepare(
            "SELECT s.*, e.name 
             FROM {$wpdb->prefix}shrms_salaries s
             JOIN {$wpdb->prefix}shrms_employees e ON s.employee_id = e.id
             WHERE s.id = %d",
            $salary_id
        ));
        
        if (!$salary) {
            wp_send_json_error(['message' => 'الراتب غير موجود']);
        }
        
        if ($salary->status === 'paid') {
            wp_send_json_error(['message' => 'الراتب مدفوع مسبقاً']);
        }
        
        // Get vault from FFA
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d",
            $vault_id
        ));
        
        if (!$vault) {
            wp_send_json_error(['message' => 'الخزينة غير موجودة']);
        }
        
        // Calculate commission
        $commission_rate = floatval($vault->commission_rate);
        $commission_amount = ($salary->final_salary * $commission_rate) / 100;
        $total_deduction = $salary->final_salary + $commission_amount;
        
        // Check balance
        if (floatval($vault->balance) < $total_deduction) {
            wp_send_json_error(['message' => 'رصيد الخزينة غير كافٍ']);
        }
        
        // Start transaction
        $wpdb->query('START TRANSACTION');
        
        try {
            // 1. Update SHRMS salary status
            $wpdb->update(
                $wpdb->prefix . 'shrms_salaries',
                [
                    'status' => 'paid',
                    'paid_at' => current_time('mysql')
                ],
                ['id' => $salary_id]
            );
            
            // 2. Record salary cashflow in FFA
            $salary_category = $wpdb->get_var("SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE name = 'Salaries' LIMIT 1");
            // 2. Record salary cashflow in FFA
            $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
                'type' => 'expense',
                'category_id' => $salary_category ?: null,  // ✅ هنا
                'amount' => $salary->final_salary,
                'description' => sprintf('راتب %s - %s', $salary->name, $salary->month),
                'related_id' => $salary_id,
                'related_type' => 'shrms_salary',
                'warehouse' => null,
                'payment_method' => $vault->payment_method,
                'vault_id' => $vault_id,
                'employee_id' => $employee_id,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id()
            ]);

            
            $cashflow_salary_id = $wpdb->insert_id;
            
            // 3. Record commission (if > 0)
            if ($commission_amount > 0) {
                $commission_category = $wpdb->get_var(
                    "SELECT id FROM {$wpdb->prefix}ffa_expense_categories WHERE name = 'Commission' LIMIT 1"
                );
                
                // Commission cashflow
                $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
                    'type' => 'expense',
                    'category_id' => $commission_category,
                    'amount' => $commission_amount,
                    'description' => sprintf('عمولة راتب %s (%s%%)', $salary->name, $commission_rate),
                    'related_id' => $salary_id,
                    'related_type' => 'salary_commission',
                    'warehouse' => null,
                    'payment_method' => $vault->payment_method,
                    'vault_id' => $vault_id,
                    'employee_id' => $employee_id,
                    'created_at' => current_time('mysql'),
                    'created_by' => get_current_user_id()
                ]);
                
                // Commission expense
                $wpdb->insert($wpdb->prefix . 'ffa_expenses', [
                    'type' => 'variable',
                    'category_id' => $commission_category,
                    'amount' => $commission_amount,
                    'description' => sprintf('عمولة راتب %s', $salary->name),
                    'warehouse' => null,
                    'vault_id' => $vault_id,
                    'employee_id' => $employee_id,
                    'created_at' => current_time('mysql'),
                    'created_by' => get_current_user_id()
                ]);
            }
            
            // 4. Update vault balance
            $new_balance = floatval($vault->balance) - $total_deduction;
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $new_balance],
                ['id' => $vault_id]
            );
            
            // 5. Record salary expense
            $wpdb->insert($wpdb->prefix . 'ffa_expenses', [
                'type' => 'fixed',
                'category_id' => null,
                'amount' => $salary->final_salary,
                'description' => sprintf('راتب %s - %s', $salary->name, $salary->month),
                'warehouse' => null,
                'vault_id' => $vault_id,
                'employee_id' => $employee_id,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id()
            ]);
            
            // Commit
            $wpdb->query('COMMIT');
            
            // Clear cache
            if (method_exists('FFA_Database', 'clear_cache')) {
                FFA_Database::clear_cache();
            }
            
            wp_send_json_success([
                'message' => 'تم دفع الراتب بنجاح',
                'employee' => $salary->name,
                'amount' => $salary->final_salary,
                'commission' => $commission_amount,
                'total' => $total_deduction,
                'new_balance' => $new_balance
            ]);
            
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            wp_send_json_error(['message' => 'خطأ في قاعدة البيانات: ' . $e->getMessage()]);
        }
    }
}

// Initialize
add_action('init', ['FFA_SHRMS_Payroll', 'init'], 20);
