# FFA - Frozen Factory Accounting

![Version](https://img.shields.io/badge/version-3.0.0-blue.svg)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-green.svg)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-purple.svg)
![License](https://img.shields.io/badge/license-GPL--2.0%2B-red.svg)

High-performance accounting and financial management system for WordPress with WooCommerce integration, multi-vault cashflow management, and SHRMS payroll integration.

---

## 📋 Table of Contents

- [Features](#-features)
- [Installation](#-installation)
- [Database Structure](#-database-structure)
- [Core Modules](#-core-modules)
- [API Endpoints](#-api-endpoints)
- [Integration](#-integration)
- [Workflow Examples](#-workflow-examples)
- [Performance](#-performance)
- [Requirements](#-requirements)
- [Developer Guide](#-developer-guide)
- [Changelog](#-changelog)

---

## ✨ Features

### Financial Management
- 💰 **Multi-Vault System**: Manage multiple payment vaults with automatic commission handling
- 📈 **Real-time Cashflow Tracking**: Complete income and expense monitoring
- 📊 **Dynamic Profit Margin Calculation**: Real-time profit analysis with optimized queries
- 🏭 **Multi-Warehouse Support**: Separate financial tracking per warehouse
- 💳 **Flexible Payment Methods**: Support for any WooCommerce payment gateway

### Expense Management
- 📝 **Fixed & Variable Expenses**: Categorized expense tracking
- 📅 **Expense Categories**: Customizable expense classification
- 💵 **Commission Tracking**: Automatic commission calculation and recording
- 👥 **Employee-Linked Expenses**: Associate expenses with specific employees

### Vendor Management
- 👤 **Vendor Profiles**: Complete vendor information and history
- 📦 **Purchase Management**: Track raw material purchases
- 💸 **Vendor Balance Tracking**: Monitor accounts payable
- 📧 **Multiple Payment Methods**: Per-vendor payment flexibility

### Loan Management
- 🏦 **Company Loans**: Manage loans taken by the company
- 👨‍💼 **Employee Loans**: Employee advance and loan tracking
- 📅 **Installment Tracking**: Automatic payment schedule management
- 💵 **Salary Integration**: Auto-deduct loan payments from salaries
- 🔔 **Payment Reminders**: Next payment date tracking

### WooCommerce Integration
- 🛒 **Automatic Order Sync**: Real-time order-to-cashflow conversion
- 📦 **Warehouse Selection**: Order-level warehouse assignment
- 💳 **Payment Gateway Mapping**: Automatic vault assignment per payment method
- 📉 **Sales Reports**: Detailed sales analytics by warehouse

### SHRMS Integration
- 👥 **Payroll Sync**: Automatic salary payment recording
- 💼 **Employee Expense Tracking**: Link expenses to SHRMS employees
- 💰 **Loan Deductions**: Auto-deduct loans from monthly salaries
- 🔄 **Two-way Data Sync**: Seamless integration with HR system

### Performance & Caching
- ⚡ **Optimized Queries**: Single-query reports for maximum speed
- 🗄️ **Smart Caching**: Transient and object caching for frequently accessed data
- 🚀 **Lazy Loading**: Load data only when needed
- 📊 **Indexed Tables**: All database tables optimized with proper indexes

### API & Authentication
- 🔒 **JWT Authentication**: Secure token-based API access
- 🔗 **RESTful API**: Complete API for external integrations
- 📱 **Mobile-Ready**: Designed for mobile POS applications
- 🔐 **Role-Based Access**: Fine-grained permission control

---

## 🚀 Installation

### Method 1: Manual Installation

1. Download the plugin files
2. Upload to `/wp-content/plugins/accounting_plugin` directory
3. Activate through WordPress admin panel

```bash
cd wp-content/plugins/
git clone https://github.com/abdulrahmanroston/accounting_plugin.git
```

### Method 2: WordPress Admin

1. Go to **Plugins → Add New**
2. Click **Upload Plugin**
3. Choose the ZIP file
4. Click **Install Now** → **Activate**

### Post-Installation

After activation, the plugin will automatically:
- ✅ Create 9 database tables with optimized indexes
- ✅ Insert default expense categories
- ✅ Initialize REST API endpoints at `/wp-json/ffa/v1/*`
- ✅ Create default vaults for detected payment methods

### Required Plugins

- **WooCommerce**: For e-commerce integration
- **SHRMS Plugin** (Optional): For HR and payroll integration

---

## 🗄️ Database Structure

### Tables Overview

The plugin creates 9 highly optimized tables:

#### 1. `wp_ffa_cashflow`
Central table for all financial transactions.

```sql
Columns:
- id (BIGINT, Primary Key)
- type (ENUM: revenue, expense)
- category_id (BIGINT)
- amount (DECIMAL 15,2)
- description (TEXT)
- related_id (BIGINT, Links to source record)
- related_type (VARCHAR 50, e.g., 'order', 'expense', 'purchase')
- warehouse (VARCHAR 50)
- payment_method (VARCHAR 50)
- vault_id (BIGINT, Foreign Key to vaults)
- employee_id (BIGINT)
- order_id (INT, WooCommerce order)
- warehouse_id (VARCHAR 100)
- previous_status (VARCHAR 50)
- current_status (VARCHAR 50)
- created_at (DATETIME)
- created_by (BIGINT)

Indexes:
- PRIMARY KEY (id)
- KEY (type, vault_id, order_id, created_at, employee_id)
```

#### 2. `wp_ffa_expenses`
Detailed expense records.

```sql
Columns:
- id (BIGINT, Primary Key)
- type (ENUM: fixed, variable)
- category_id (BIGINT)
- amount (DECIMAL 15,2)
- description (TEXT)
- warehouse (VARCHAR 50)
- vault_id (BIGINT)
- employee_id (BIGINT)
- created_at (DATETIME)
- created_by (BIGINT)

Indexes:
- PRIMARY KEY (id)
- KEY (category_id, vault_id, created_at)
```

#### 3. `wp_ffa_expense_categories`
Expense classification system.

```sql
Columns:
- id (BIGINT, Primary Key)
- name (VARCHAR 100, UNIQUE)
- description (TEXT)
- created_at (DATETIME)

Default Categories:
- Raw Materials
- Utilities
- Rent
- Salaries
- Marketing
- Transportation
- Maintenance
- Office Supplies
- Commission
```

#### 4. `wp_ffa_vaults`
Payment vault management with commission.

```sql
Columns:
- id (BIGINT, Primary Key)
- name (VARCHAR 100)
- payment_method (VARCHAR 50)
- balance (DECIMAL 15,2)
- commission_rate (DECIMAL 5,2, Percentage)
- default_warehouse (VARCHAR 100)
- employees (TEXT, JSON array)
- is_default (TINYINT 1)
- created_at (DATETIME)

Indexes:
- PRIMARY KEY (id)
- UNIQUE KEY (default_warehouse, payment_method)
- KEY (payment_method)
```

#### 5. `wp_ffa_vendors`
Supplier management.

```sql
Columns:
- id (BIGINT, Primary Key)
- name (VARCHAR 100)
- phone (VARCHAR 20)
- address (TEXT)
- material_ids (TEXT, JSON array)
- payment_methods (TEXT, JSON array)
- balance (DECIMAL 15,2)
- created_at (DATETIME)

Indexes:
- PRIMARY KEY (id)
- KEY (name)
```

#### 6. `wp_ffa_purchases`
Raw material purchase tracking.

```sql
Columns:
- id (BIGINT, Primary Key)
- material_id (BIGINT)
- vendor_id (BIGINT)
- quantity (INT)
- unit_cost (DECIMAL 15,2)
- total_cost (DECIMAL 15,2)
- vault_id (BIGINT)
- employee_id (BIGINT)
- payment_status (ENUM: paid, pending)
- created_at (DATETIME)
- created_by (BIGINT)

Indexes:
- PRIMARY KEY (id)
- KEY (vendor_id, material_id, created_at)
```

#### 7. `wp_ffa_company_loans`
Loans taken by the company.

```sql
Columns:
- id (BIGINT, Primary Key)
- lender_name (VARCHAR 100)
- receiver_employee_id (BIGINT)
- vault_id (BIGINT)
- loan_amount (DECIMAL 15,2)
- repayment_type (ENUM: lump_sum, installments)
- installment_amount (DECIMAL 15,2)
- installment_period (INT)
- installment_frequency (ENUM: daily, weekly, monthly)
- total_paid (DECIMAL 15,2)
- remaining_balance (DECIMAL 15,2)
- loan_date (DATE)
- due_date (DATE)
- next_payment_date (DATE)
- reason (TEXT)
- status (ENUM: active, completed, defaulted)
- created_at (DATETIME)
- created_by (BIGINT)

Indexes:
- PRIMARY KEY (id)
- KEY (status, receiver_employee_id, next_payment_date)
```

#### 8. `wp_ffa_employee_loans`
Loans given to employees.

```sql
Columns:
- id (BIGINT, Primary Key)
- employee_id (BIGINT)
- vault_id (BIGINT)
- loan_amount (DECIMAL 15,2)
- repayment_type (ENUM: lump_sum, installments)
- installment_amount (DECIMAL 15,2)
- installment_period (INT)
- installment_frequency (ENUM: daily, weekly, monthly)
- auto_deduct_from_salary (TINYINT 1)
- total_paid (DECIMAL 15,2)
- remaining_balance (DECIMAL 15,2)
- loan_date (DATE)
- due_date (DATE)
- next_payment_date (DATE)
- reason (TEXT)
- status (ENUM: active, completed, suspended)
- created_at (DATETIME)
- created_by (BIGINT)

Indexes:
- PRIMARY KEY (id)
- KEY (employee_id, status, auto_deduct_from_salary, next_payment_date)
```

#### 9. `wp_ffa_loan_payments`
Loan payment history.

```sql
Columns:
- id (BIGINT, Primary Key)
- loan_id (BIGINT)
- loan_type (ENUM: company, employee)
- payment_amount (DECIMAL 15,2)
- payment_date (DATE)
- vault_id (BIGINT)
- employee_id (BIGINT)
- is_auto_deducted (TINYINT 1)
- salary_month (VARCHAR 7, Format: YYYY-MM)
- notes (TEXT)
- created_at (DATETIME)
- created_by (BIGINT)

Indexes:
- PRIMARY KEY (id)
- KEY (loan_id, loan_type, payment_date, salary_month)
```

---

## 🔧 Core Modules

### 1. Database Module (`FFA_Database`)

#### Smart Caching System
```php
// Get vaults with automatic caching
$vaults = FFA_Database::get_vaults(); // Cached for 1 hour
$vaults = FFA_Database::get_vaults(true); // Force refresh

// Get employees (from SHRMS)
$employees = FFA_Database::get_employees();

// Get expense categories
$categories = FFA_Database::get_categories(); // Cached for 1 day

// Clear all caches
FFA_Database::clear_cache();
```

#### Vault Management
```php
// Automatic vault detection or creation
$vault = FFA_Database::find_vault('warehouse_1', 'cash');

// Update vault balance with commission
$final_amount = FFA_Database::update_vault_balance(
    $vault_id,
    1000,           // Amount
    'Order payment',
    123,            // Order ID
    'order',        // Related type
    'warehouse_1',
    $employee_id,
    true            // Apply commission
);
```

#### Cashflow Recording
```php
// Record revenue
$cashflow_id = FFA_Database::record_cashflow(
    'revenue',           // Type
    null,                // Category (optional for revenue)
    1000,                // Amount
    'Order #123',        // Description
    123,                 // Related ID
    'order',             // Related type
    'warehouse_1',       // Warehouse
    'cash',              // Payment method
    $vault_id,
    $employee_id
);

// Record expense
$cashflow_id = FFA_Database::record_cashflow(
    'expense',
    $category_id,
    500,
    'Office rent',
    $expense_id,
    'expense',
    'main',
    'bank_transfer',
    $vault_id,
    null
);
```

#### Performance Analytics
```php
// Calculate profit margin (cached for 5 minutes)
$profit = FFA_Database::calculate_profit_margin('month'); // day, week, month
echo "Profit Margin: {$profit}%";

// Get sales report
$report = FFA_Database::get_sales_report('month');
echo "Total Sales: " . $report['total'];

foreach ($report['by_warehouse'] as $warehouse) {
    echo "{$warehouse->warehouse}: {$warehouse->total}";
}
```

---

### 2. API Module (`FFA_API`)

#### Authentication
```php
// Generate JWT token
$token = FFA_Database::generate_token($employee_id, 'admin');

// Validate token
$payload = FFA_Database::validate_token($token);
if (is_wp_error($payload)) {
    // Handle error
    echo $payload->get_error_message();
} else {
    // Access granted
    $employee_id = $payload->sub;
    $role = $payload->role;
}
```

#### API Endpoints

**Authentication**
```
POST /wp-json/ffa/v1/login
Body: {"phone": "01234567890", "password": "secret"}
Response: {"token": "...", "employee": {...}}
```

**Cashflow**
```
GET  /wp-json/ffa/v1/cashflow
GET  /wp-json/ffa/v1/cashflow/{id}
POST /wp-json/ffa/v1/cashflow
```

**Expenses**
```
GET  /wp-json/ffa/v1/expenses
GET  /wp-json/ffa/v1/expenses/{id}
POST /wp-json/ffa/v1/expenses
PUT  /wp-json/ffa/v1/expenses/{id}
```

**Vaults**
```
GET  /wp-json/ffa/v1/vaults
GET  /wp-json/ffa/v1/vaults/{id}
POST /wp-json/ffa/v1/vaults
PUT  /wp-json/ffa/v1/vaults/{id}
GET  /wp-json/ffa/v1/vaults/{id}/balance
```

**Vendors**
```
GET  /wp-json/ffa/v1/vendors
GET  /wp-json/ffa/v1/vendors/{id}
POST /wp-json/ffa/v1/vendors
PUT  /wp-json/ffa/v1/vendors/{id}
```

**Purchases**
```
GET  /wp-json/ffa/v1/purchases
POST /wp-json/ffa/v1/purchases
```

**Loans**
```
GET  /wp-json/ffa/v1/loans/company
GET  /wp-json/ffa/v1/loans/employee
POST /wp-json/ffa/v1/loans/company
POST /wp-json/ffa/v1/loans/employee
POST /wp-json/ffa/v1/loans/payment
```

**Reports**
```
GET /wp-json/ffa/v1/reports/profit-margin?period=month
GET /wp-json/ffa/v1/reports/sales?period=week
GET /wp-json/ffa/v1/reports/cashflow?start=2025-01-01&end=2025-01-31
```

---

### 3. WooCommerce Integration (`FFA_WooCommerce`)

#### Automatic Order Processing

When WooCommerce order status changes to `completed`:

1. ✅ Extract order details (amount, warehouse, payment method)
2. ✅ Find or create appropriate vault
3. ✅ Calculate commission (if applicable)
4. ✅ Update vault balance
5. ✅ Record cashflow entry
6. ✅ Add order meta for tracking

```php
// Triggered automatically on order completion
add_action('woocommerce_order_status_completed', function($order_id) {
    FFA_WooCommerce::record_order_payment($order_id);
});
```

#### Custom Order Fields

- **Warehouse Selection**: Dropdown to select warehouse per order
- **Employee Assignment**: Link order to specific employee
- **Accounting Status**: Track if order is synced to accounting

---

### 4. SHRMS Integration (`FFA_SHRMS_Payroll`)

#### Payroll Sync

```php
// Hook into SHRMS salary payment
add_action('shrms_salary_paid', function($employee_id, $salary_data, $month) {
    FFA_SHRMS_Payroll::record_salary_payment(
        $employee_id,
        $salary_data['final_salary'],
        $month
    );
});
```

#### Automatic Loan Deductions

```php
// During salary calculation
add_filter('shrms_calculated_salary_data', function($salary_data, $employee_id) {
    // Get active employee loans with auto-deduct enabled
    $loans = FFA_SHRMS_Payroll::get_employee_active_loans($employee_id);
    
    foreach ($loans as $loan) {
        if ($loan->auto_deduct_from_salary) {
            // Deduct installment
            $salary_data['deductions'] += $loan->installment_amount;
            
            // Record payment
            FFA_SHRMS_Payroll::record_loan_payment(
                $loan->id,
                $loan->installment_amount,
                $employee_id,
                $salary_data['month']
            );
        }
    }
    
    return $salary_data;
}, 10, 2);
```

---

## 🔗 Integration

### With SHRMS Plugin

FFA automatically detects and integrates with SHRMS:

```php
if (class_exists('SHRMS_Core')) {
    require_once FFA_PATH . 'includes/class-ffa-shrms-payroll.php';
    FFA_SHRMS_Payroll::init();
}
```

**Features:**
- ✅ Automatic salary expense recording
- ✅ Employee loan auto-deduction
- ✅ Advance payment tracking
- ✅ Bonus/deduction sync

### With WooCommerce

Automatic integration when WooCommerce is active:

```php
if (class_exists('WooCommerce')) {
    FFA_WooCommerce::init();
}
```

**Features:**
- ✅ Order completion → Cashflow recording
- ✅ Refund → Negative cashflow entry
- ✅ Warehouse-based accounting
- ✅ Commission calculation

### Custom Integrations

```php
// Hook into cashflow recording
add_action('ffa_cashflow_recorded', function($cashflow_id, $type, $amount) {
    // Your custom logic
    my_external_accounting_sync($cashflow_id);
}, 10, 3);

// Hook into vault balance update
add_action('ffa_vault_balance_updated', function($vault_id, $old_balance, $new_balance) {
    // Notification or external sync
}, 10, 3);
```

---

## 📋 Workflow Examples

### Example 1: Complete Order Processing

```php
// 1. Customer places order in WooCommerce
$order = wc_get_order(123);

// 2. Order is completed
$order->update_status('completed');

// 3. FFA automatically:
// - Gets order total: $1000
// - Gets warehouse: 'main_warehouse'
// - Gets payment method: 'cash'
// - Finds vault: Vault #1 (Cash - Main)
// - Commission rate: 2%
// - Calculates commission: $20
// - Records commission expense
// - Updates vault balance: +$980
// - Records cashflow: Revenue $1000
// - Links: order_id = 123
```

### Example 2: Recording Manual Expense

```php
// Admin records rent expense
$expense_data = [
    'type' => 'fixed',
    'category_id' => 3, // Rent
    'amount' => 5000,
    'description' => 'Monthly rent for main office',
    'warehouse' => 'main',
    'vault_id' => 2, // Bank transfer vault
    'employee_id' => null
];

// Via API or Admin panel
FFA_API::create_expense($expense_data);

// Result:
// - Expense record created
// - Vault balance decreased by $5000
// - Cashflow recorded as expense
```

### Example 3: Employee Loan with Auto-Deduction

```php
// 1. Create employee loan
$loan_data = [
    'employee_id' => 25,
    'vault_id' => 1,
    'loan_amount' => 10000,
    'repayment_type' => 'installments',
    'installment_amount' => 1000,
    'installment_period' => 10,
    'installment_frequency' => 'monthly',
    'auto_deduct_from_salary' => true,
    'loan_date' => '2025-01-01',
    'reason' => 'Personal emergency'
];

FFA_API::create_employee_loan($loan_data);

// 2. Each month when salary is calculated:
// SHRMS calculates salary
// FFA checks for active loans
// Deducts $1000 from salary
// Records loan payment
// Updates remaining balance
// Updates next_payment_date

// 3. After 10 months:
// Loan status → 'completed'
// No more deductions
```

---

## ⚡ Performance

### Optimization Techniques

#### 1. Smart Caching
```php
// Vaults cached for 1 hour
$vaults = FFA_Database::get_vaults();

// Categories cached for 1 day
$categories = FFA_Database::get_categories();

// Reports cached for 5 minutes
$profit = FFA_Database::calculate_profit_margin('month');
```

#### 2. Single-Query Reports
```php
// ONE query for profit margin calculation
// Instead of multiple queries for sales, costs, etc.
$profit_margin = FFA_Database::calculate_profit_margin('day');
```

#### 3. Indexed Tables
All tables have proper indexes for:
- Foreign keys
- Frequently queried columns
- Date columns
- Status columns

#### 4. Lazy Loading
```php
// Load SHRMS integration only if SHRMS is active
if (class_exists('SHRMS_Core')) {
    require_once FFA_PATH . 'includes/class-ffa-shrms-payroll.php';
}
```

### Performance Benchmarks

- **Cashflow Recording**: < 50ms
- **Vault Balance Update**: < 30ms
- **Profit Margin Calculation**: < 100ms (first time), < 5ms (cached)
- **Sales Report**: < 80ms (first time), < 5ms (cached)

---

## 📦 Requirements

### Minimum Requirements

- **WordPress:** 5.8 or higher
- **PHP:** 7.4 or higher
- **MySQL:** 5.6 or higher
- **WooCommerce:** 5.0 or higher (for e-commerce features)

### Recommended

- **PHP:** 8.0 or higher
- **MySQL:** 8.0 or higher
- **WordPress:** Latest stable version
- **Memory Limit:** 256MB+
- **Max Execution Time:** 60 seconds
- **Object Caching:** Redis or Memcached

### Optional Plugins

- **SHRMS Plugin**: For payroll integration
- **Warehouse Plugin**: For inventory management

---

## 👨‍💻 Developer Guide

### Architecture

```
FFA Plugin
    ├── FFA_Database (Core)
    │   ├── Table management
    │   ├── Caching system
    │   ├── Helper functions
    │   └── JWT authentication
    │
    ├── FFA_API (REST API)
    │   ├── Authentication endpoints
    │   ├── CRUD operations
    │   ├── Reports
    │   └── Permissions
    │
    ├── FFA_Admin (Admin Interface)
    │   ├── Dashboard
    │   ├── Settings pages
    │   ├── Reports UI
    │   └── Forms
    │
    ├── FFA_WooCommerce (Integration)
    │   ├── Order hooks
    │   ├── Custom fields
    │   └── Payment sync
    │
    └── FFA_SHRMS_Payroll (Integration)
        ├── Salary sync
        ├── Loan management
        └── Employee expenses
```

### Hooks & Filters

#### Actions
```php
// Cashflow recorded
do_action('ffa_cashflow_recorded', $cashflow_id, $type, $amount);

// Vault balance updated
do_action('ffa_vault_balance_updated', $vault_id, $old_balance, $new_balance);

// Expense created
do_action('ffa_expense_created', $expense_id, $expense_data);

// Loan payment recorded
do_action('ffa_loan_payment_recorded', $payment_id, $loan_id, $amount);
```

#### Filters
```php
// Modify vault finder logic
apply_filters('ffa_vault_finder', $vault, $warehouse, $payment_method);

// Modify commission calculation
apply_filters('ffa_commission_amount', $commission, $amount, $vault);

// Modify cashflow data before insert
apply_filters('ffa_cashflow_data', $data);
```

### Custom Development Examples

#### Example 1: Custom Commission Logic
```php
add_filter('ffa_commission_amount', function($commission, $amount, $vault) {
    // Apply discount for high-value transactions
    if ($amount > 10000) {
        $commission *= 0.5; // 50% discount
    }
    return $commission;
}, 10, 3);
```

#### Example 2: External API Sync
```php
add_action('ffa_cashflow_recorded', function($cashflow_id, $type, $amount) {
    // Sync with external accounting software
    $api = new MyAccountingAPI();
    $api->sync_transaction([
        'id' => $cashflow_id,
        'type' => $type,
        'amount' => $amount
    ]);
}, 10, 3);
```

#### Example 3: Custom Vault Selection
```php
add_filter('ffa_vault_finder', function($vault, $warehouse, $payment_method) {
    // Use specific vault for VIP customers
    if (is_vip_order()) {
        return get_vip_vault();
    }
    return $vault;
}, 10, 3);
```

---

## 📝 Changelog

### Version 3.0.0 (Current)
**Release Date:** December 2025

#### Added
- ✅ Complete loan management system (company & employee)
- ✅ Automatic loan payment tracking with installments
- ✅ Salary-linked loan deductions
- ✅ Commission tracking per vault
- ✅ Dynamic WooCommerce payment method detection
- ✅ Enhanced caching system (transients + object cache)
- ✅ Single-query profit margin calculation
- ✅ JWT authentication for API
- ✅ SHRMS payroll integration

#### Improved
- 📈 Database performance with strategic indexes
- 📈 Report generation speed (5x faster)
- 📈 API response times
- 📈 Memory usage optimization

#### Fixed
- 🐛 Fixed vault balance calculation edge cases
- 🐛 Fixed commission not recording for certain payment methods
- 🐛 Fixed cashflow duplicate entries
- 🐛 Fixed timezone issues in date queries

### Version 2.x
- Basic cashflow tracking
- Expense management
- Vault system
- WooCommerce integration

---

## 📄 License

This plugin is licensed under the **GNU General Public License v2.0 or later**.

---
## 👤 Author

**Abdulrahman Roston**

- 🌐 Website: [abdulrahmanroston.com](https://abdulrahmanroston.com)
- 📧 Email: support@abdulrahmanroston.com
- 🐙 GitHub: [@abdulrahmanroston](https://github.com/abdulrahmanroston)

---

## 🤝 Contributing

Contributions are welcome! Please:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

---

## 📞 Support

For support, bug reports, or feature requests:

- 🐛 Issues: [GitHub Issues](https://github.com/abdulrahmanroston/accounting_plugin/issues)
- 📧 Email: support@abdulrahmanroston.com

---

## ⭐ Show Your Support

If you find this plugin useful:

- ⭐ Star the repository
- 🐛 Report bugs
- 💡 Suggest features
- 📢 Share with others

---

**Made with ❤️ in Egypt 🇪🇬**

---

© 2025 Abdulrahman Roston. All rights reserved.