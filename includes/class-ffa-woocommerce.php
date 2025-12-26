<?php
/**
 * FFA WooCommerce Integration
 * Handles all WooCommerce order status changes and automatic vault updates
 */

class FFA_WooCommerce {
    
    /**
     * Initialize
     */
 // استبدل كل الـ hooks القديمة بهذا:
public static function init() {
    // Dynamic state machine
    add_action('woocommerce_order_status_changed', ['FFA_WooCommerce_Dynamic', 'handle_status_change'], 10, 3);
    add_action('woocommerce_order_refunded', ['FFA_WooCommerce_Dynamic', 'handle_refund'], 10, 2);
    
    // Auto deductions
    add_action('shrms_salary_calculated', [__CLASS__, 'auto_process_loan_deductions'], 10, 2);
}


    
    /**
     * Handle order completion
     */
    public static function handle_order_completed($order_id) {
        global $wpdb;
        
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log("FFA: Invalid order ID $order_id");
            return;
        }
        
        // Check if already processed
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}ffa_cashflow 
             WHERE order_id = %d AND current_status = 'completed' AND type = 'revenue'",
            $order_id
        ));
        
        if ($existing) {
            return; // Already processed
        }
        
        // Get warehouse
        $warehouse = get_post_meta($order_id, '_selected_warehouse', true);
        if (!$warehouse) {
            $warehouse = 'default';
        }
        
        // Get payment method
        $payment_method = $order->get_payment_method();
        if (!$payment_method) {
            error_log("FFA: No payment method for order $order_id");
            return;
        }
        
        // Find appropriate vault
        $vault = FFA_Database::find_vault($warehouse, $payment_method);
        if (!$vault) {
            error_log("FFA: No vault found for order $order_id");
            return;
        }
        
        $amount = $order->get_total();
        $employee_id = get_current_user_id() ?: 1;
        $description = "WooCommerce Order #$order_id from $warehouse warehouse";
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Update vault with commission
            $net_amount = FFA_Database::update_vault_balance(
                $vault->id,
                $amount,
                $description,
                $order_id,
                'order',
                $warehouse,
                $employee_id,
                true // apply commission
            );
            
            if ($net_amount === false) {
                throw new Exception('Failed to update vault balance');
            }
            
            // Record cashflow
            $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
                'type' => 'revenue',
                'category_id' => null,
                'amount' => $net_amount,
                'description' => $description,
                'related_id' => $order_id,
                'related_type' => 'order',
                'warehouse' => $warehouse,
                'payment_method' => $payment_method,
                'vault_id' => $vault->id,
                'employee_id' => $employee_id,
                'order_id' => $order_id,
                'warehouse_id' => $warehouse,
                'previous_status' => 'processing',
                'current_status' => 'completed',
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id() ?: 1,
            ]);
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            error_log("FFA: Order #$order_id completed. Net: $net_amount to vault {$vault->name}");
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('FFA Order Completion Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle order cancellation
     */
    public static function handle_order_cancelled($order_id) {
        self::reverse_order_transaction($order_id, 'cancelled');
    }
    
    /**
     * Handle order refund
     */
    public static function handle_order_refunded($order_id) {
        self::reverse_order_transaction($order_id, 'refunded');
    }
    
    /**
     * Handle order failure
     */
    public static function handle_order_failed($order_id) {
        self::reverse_order_transaction($order_id, 'failed');
    }
    
    /**
     * Reverse order transaction
     */
    private static function reverse_order_transaction($order_id, $reason) {
        global $wpdb;
        
        $order = wc_get_order($order_id);
        if (!$order) {
            error_log("FFA: Invalid order ID $order_id for reversal");
            return;
        }
        
        // Check for existing revenue transaction
        $existing_transaction = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_cashflow 
             WHERE order_id = %d AND current_status = 'completed' AND type = 'revenue'",
            $order_id
        ));
        
        if (!$existing_transaction) {
            return; // No transaction to reverse
        }
        
        // Check if already reversed
        $existing_reversal = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$wpdb->prefix}ffa_cashflow 
             WHERE order_id = %d AND current_status = %s AND type = 'expense'",
            $order_id, $reason
        ));
        
        if ($existing_reversal) {
            return; // Already reversed
        }
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}ffa_vaults WHERE id = %d",
            $existing_transaction->vault_id
        ));
        
        if (!$vault) {
            error_log("FFA: Vault not found for order reversal $order_id");
            return;
        }
        
        $amount = $existing_transaction->amount;
        $warehouse = $existing_transaction->warehouse_id;
        $payment_method = $existing_transaction->payment_method;
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Record reversal
            $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
                'type' => 'expense',
                'category_id' => null,
                'amount' => $amount,
                'description' => "Order #$order_id $reason - reversing revenue",
                'related_id' => $order_id,
                'related_type' => "order_$reason",
                'warehouse' => $warehouse,
                'payment_method' => $payment_method,
                'vault_id' => $vault->id,
                'employee_id' => get_current_user_id() ?: 1,
                'order_id' => $order_id,
                'warehouse_id' => $warehouse,
                'previous_status' => 'completed',
                'current_status' => $reason,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id() ?: 1,
            ]);
            
            // Update vault (allow negative)
            $new_balance = $vault->balance - $amount;
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $new_balance],
                ['id' => $vault->id]
            );
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            error_log("FFA: Order #$order_id $reason. Amount $amount removed from vault {$vault->name}");
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('FFA Order Reversal Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Handle partial refund
     */
    public static function handle_partial_refund($order_id, $refund_id) {
        global $wpdb;
        
        $refund = wc_get_order($refund_id);
        $order = wc_get_order($order_id);
        
        if (!$refund || !$order) {
            return;
        }
        
        $refund_amount = abs($refund->get_amount());
        $payment_method = $order->get_payment_method();
        $warehouse = get_post_meta($order_id, '_selected_warehouse', true) ?: 'default';
        
        $vault = FFA_Database::find_vault($warehouse, $payment_method);
        
        if (!$vault || $refund_amount <= 0) {
            return;
        }
        
        $wpdb->query('START TRANSACTION');
        
        try {
            // Record partial refund
            $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
                'type' => 'expense',
                'category_id' => null,
                'amount' => $refund_amount,
                'description' => "Partial refund for order #$order_id (Refund #$refund_id)",
                'related_id' => $refund_id,
                'related_type' => 'partial_refund',
                'warehouse' => $warehouse,
                'payment_method' => $payment_method,
                'vault_id' => $vault->id,
                'employee_id' => get_current_user_id() ?: 1,
                'order_id' => $order_id,
                'created_at' => current_time('mysql'),
                'created_by' => get_current_user_id() ?: 1,
            ]);
            
            // Update vault
            $new_balance = $vault->balance - $refund_amount;
            $wpdb->update(
                $wpdb->prefix . 'ffa_vaults',
                ['balance' => $new_balance],
                ['id' => $vault->id]
            );
            
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
            
            error_log("FFA: Partial refund for order #$order_id. Amount: $refund_amount");
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('FFA Partial Refund Error: ' . $e->getMessage());
        }
    }
    
    /**
     * Store previous status
     */
    public static function store_previous_status($order_id, $old_status, $new_status) {
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_meta_data('_ffa_previous_status', $old_status);
            $order->save();
        }
    }
    
    /**
     * Auto process loan deductions when salary calculated
     */
    public static function auto_process_loan_deductions($employee_id, $salary_month) {
        global $wpdb;
        
        $table_employee_loans = $wpdb->prefix . 'ffa_employee_loans';
        $table_loan_payments = $wpdb->prefix . 'ffa_loan_payments';
        $table_vaults = $wpdb->prefix . 'ffa_vaults';
        $table_salaries = $wpdb->prefix . 'shrms_salaries';
        
        // Get due loan for this employee
        $loan = $wpdb->get_row($wpdb->prepare("
            SELECT el.*, e.name AS employee_name
            FROM $table_employee_loans el 
            JOIN {$wpdb->prefix}shrms_employees e ON el.employee_id = e.id
            WHERE el.status = 'active' 
            AND el.remaining_balance > 0
            AND el.auto_deduct_from_salary = 1
            AND el.repayment_type = 'installments'
            AND el.installment_frequency = 'monthly'
            AND el.employee_id = %d
            AND DATE_FORMAT(el.next_payment_date, '%%Y-%%m') <= %s
        ", $employee_id, $salary_month));
        
        if (!$loan) {
            return;
        }
        
        // Check if already processed
        $existing = $wpdb->get_var($wpdb->prepare("
            SELECT id FROM $table_loan_payments 
            WHERE loan_id = %d AND loan_type = 'employee' 
            AND salary_month = %s AND is_auto_deducted = 1
        ", $loan->id, $salary_month));
        
        if ($existing) {
            return;
        }
        
        $vault = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_vaults WHERE id = %d", $loan->vault_id
        ));
        
        if (!$vault) {
            return;
        }
        
        $deduction = min($loan->installment_amount, $loan->remaining_balance);
        
        $wpdb->query('START TRANSACTION');
        
        try {
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
                null,
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
            FFA_Database::clear_cache();
            
            error_log("FFA: Auto-deducted $deduction for employee {$loan->employee_name}");
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('FFA Auto Deduction Error: ' . $e->getMessage());
        }
    }
}

// أضف هذا الكود كاملاً في نهاية الملف class-ffa-woocommerce.php

class FFA_WooCommerce_Dynamic {
    
    private static $order_states = [
        'revenue_states' => ['completed'],
        'void_states' => ['cancelled', 'failed', 'refunded']
    ];
    
    public static function init() {
        add_action('woocommerce_order_status_changed', [__CLASS__, 'handle_status_change'], 10, 3);
        add_action('woocommerce_order_refunded', [__CLASS__, 'handle_refund'], 10, 2);
    }
    
    public static function handle_status_change($order_id, $old_status, $new_status) {
        global $wpdb;
        
        $order = wc_get_order($order_id);
        if (!$order) return;
        
        $payment_method = $order->get_payment_method();
        $warehouse = get_post_meta($order_id, '_selected_warehouse', true) ?: 'default';
        if (!$payment_method) return;
        
        $vault = FFA_Database::find_vault($warehouse, $payment_method);
        if (!$vault) return;
        
        $amount = $order->get_total();
        $current_cf = self::get_order_current_cashflow($order_id);
        $action = self::calculate_action($current_cf, $old_status, $new_status, $amount);
        
        if (!$action) return;
        
        $wpdb->query('START TRANSACTION');
        try {
            if ($action['type'] === 'record_revenue') {
                self::record_revenue($order_id, $action['amount'], $vault, $warehouse, $payment_method);
            } else {
                self::reverse_revenue($order_id, $action['amount'], $vault, $warehouse, $payment_method, $action['reason']);
            }
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
            error_log('FFA Dynamic Error: ' . $e->getMessage());
        }
    }
    
    private static function calculate_action($current_cf, $old_status, $new_status, $amount) {
        $current_state = $current_cf ? $current_cf->current_status : null;
        $has_revenue = $current_cf && $current_cf->type === 'revenue';
        $requires_revenue = in_array($new_status, self::$order_states['revenue_states']);
        
        if ($requires_revenue && !$has_revenue) {
            return ['type' => 'record_revenue', 'amount' => $amount, 'reason' => "to $new_status"];
        }
        
        if (!$requires_revenue && $has_revenue) {
            return ['type' => 'reverse_revenue', 'amount' => $current_cf->amount, 'reason' => "to $new_status"];
        }
        
        return null;
    }
    
    private static function get_order_current_cashflow($order_id) {
        global $wpdb;
        return $wpdb->get_row($wpdb->prepare("
            SELECT * FROM {$wpdb->prefix}ffa_cashflow 
            WHERE order_id = %d ORDER BY created_at DESC LIMIT 1
        ", $order_id));
    }
    
    private static function record_revenue($order_id, $amount, $vault, $warehouse, $payment_method) {
        global $wpdb;
        $net_amount = FFA_Database::update_vault_balance(
            $vault->id, $amount, "Order #$order_id", $order_id, 
            'order', $warehouse, get_current_user_id(), true
        );
        
        $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
            'type' => 'revenue', 'amount' => $net_amount,
            'description' => "Order #$order_id - $warehouse",
            'related_id' => $order_id, 'related_type' => 'order',
            'warehouse' => $warehouse, 'payment_method' => $payment_method,
            'vault_id' => $vault->id, 'order_id' => $order_id,
            'warehouse_id' => $warehouse, 'previous_status' => 'none',
            'current_status' => 'revenue', 'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id() ?: 1
        ]);
    }
    
    private static function reverse_revenue($order_id, $amount, $vault, $warehouse, $payment_method, $reason) {
        global $wpdb;
        $new_balance = $vault->balance - $amount;
        $wpdb->update($wpdb->prefix . 'ffa_vaults', ['balance' => $new_balance], ['id' => $vault->id]);
        
        $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
            'type' => 'expense', 'amount' => $amount,
            'description' => "Order #$order_id reversal - $reason",
            'related_id' => $order_id, 'related_type' => 'order_reversal',
            'warehouse' => $warehouse, 'payment_method' => $payment_method,
            'vault_id' => $vault->id, 'order_id' => $order_id,
            'warehouse_id' => $warehouse, 'previous_status' => 'revenue',
            'current_status' => 'reversed', 'created_at' => current_time('mysql'),
            'created_by' => get_current_user_id() ?: 1
        ]);
    }
    
    public static function handle_refund($order_id, $refund_id) {
        // نفس الـ logic القديم للـ partial refunds
        global $wpdb;
        $refund = wc_get_order($refund_id);
        $order = wc_get_order($order_id);
        if (!$refund || !$order) return;
        
        $refund_amount = abs($refund->get_amount());
        $payment_method = $order->get_payment_method();
        $warehouse = get_post_meta($order_id, '_selected_warehouse', true) ?: 'default';
        $vault = FFA_Database::find_vault($warehouse, $payment_method);
        
        if (!$vault || $refund_amount <= 0) return;
        
        $wpdb->query('START TRANSACTION');
        try {
            $wpdb->insert($wpdb->prefix . 'ffa_cashflow', [
                'type' => 'expense', 'amount' => $refund_amount,
                'description' => "Partial refund Order #$order_id (Refund #$refund_id)",
                'related_id' => $refund_id, 'related_type' => 'partial_refund',
                'warehouse' => $warehouse, 'payment_method' => $payment_method,
                'vault_id' => $vault->id, 'order_id' => $order_id,
                'created_at' => current_time('mysql'), 'created_by' => get_current_user_id() ?: 1
            ]);
            
            $new_balance = $vault->balance - $refund_amount;
            $wpdb->update($wpdb->prefix . 'ffa_vaults', ['balance' => $new_balance], ['id' => $vault->id]);
            $wpdb->query('COMMIT');
            FFA_Database::clear_cache();
        } catch (Exception $e) {
            $wpdb->query('ROLLBACK');
        }
    }
}

// Initialize الجديد
FFA_WooCommerce_Dynamic::init();
