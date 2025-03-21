# Bookkeeping Module Implementation Plan

This document outlines the plan for implementing a dedicated Bookkeeping module in the Billy financial management system to provide comprehensive accounting functionality.

## Current Status

Currently, our system includes several financial components spread across different modules:

- **Invoice Management** - Handles accounts receivable
- **Expense Management** - Handles accounts payable
- **Report Module** - Provides income, expense, and tax reports
- **Tax Handling** - Manages tax calculations and reporting

However, we lack dedicated bookkeeping features that would make this a complete financial management system.

## Required Bookkeeping Components

### 1. Chart of Accounts
- Standard account categories (assets, liabilities, equity, income, expenses)
- Customizable account structure
- Account hierarchy management

### 2. General Ledger
- Double-entry accounting system
- Transaction journal with complete history
- Audit trail for all financial entries

### 3. Journal Entries
- Manual journal entry creation
- Recurring journal entries
- Journal entry approval workflow
- Supporting document attachments

### 4. Bank Reconciliation
- Bank account connections/management
- Transaction matching
- Reconciliation reports
- Unreconciled transaction handling

### 5. Financial Statements
- Balance Sheet
- Profit & Loss Statement (Income Statement)
- Cash Flow Statement
- Statement of Changes in Equity

### 6. Fiscal Periods
- Period opening/closing
- Year-end processes
- Historical period viewing
- Period locking

## Database Schema Additions

### accounts
- `id` bigint PK
- `business_id` bigint FK
- `account_type` varchar (asset, liability, equity, income, expense)
- `account_number` varchar
- `name` varchar
- `description` text
- `parent_id` bigint FK (self-reference for hierarchy)
- `is_active` boolean
- `balance` decimal
- `created_at` timestamp
- `updated_at` timestamp

### journal_entries
- `id` bigint PK
- `business_id` bigint FK
- `user_id` bigint FK
- `entry_date` date
- `reference` varchar
- `description` text
- `status` varchar (draft, approved, posted)
- `is_recurring` boolean
- `recurrence_pattern` json
- `document_id` bigint FK
- `created_at` timestamp
- `updated_at` timestamp

### ledger_entries
- `id` bigint PK
- `journal_entry_id` bigint FK
- `account_id` bigint FK
- `debit_amount` decimal
- `credit_amount` decimal
- `description` text
- `created_at` timestamp
- `updated_at` timestamp

### bank_accounts
- `id` bigint PK
- `business_id` bigint FK
- `account_id` bigint FK (links to the chart of accounts)
- `bank_name` varchar
- `account_number` varchar
- `account_name` varchar
- `currency` varchar(3)
- `opening_balance` decimal
- `current_balance` decimal
- `last_reconciled_date` date
- `created_at` timestamp
- `updated_at` timestamp

### bank_transactions
- `id` bigint PK
- `bank_account_id` bigint FK
- `transaction_date` date
- `description` text
- `amount` decimal
- `is_debit` boolean
- `reference` varchar
- `is_reconciled` boolean
- `reconciled_date` date
- `created_at` timestamp
- `updated_at` timestamp

### fiscal_periods
- `id` bigint PK
- `business_id` bigint FK
- `start_date` date
- `end_date` date
- `name` varchar
- `is_closed` boolean
- `closed_by` bigint FK (user_id)
- `closed_at` timestamp
- `created_at` timestamp
- `updated_at` timestamp

## Controllers to Implement

1. **ChartOfAccountsController**
   - Account CRUD operations
   - Account hierarchy management
   - Account balance reporting

2. **JournalEntryController**
   - Journal entry CRUD
   - Recurring entry management
   - Entry approval workflow

3. **BankAccountController**
   - Bank account management
   - Transaction import
   - Statement upload

4. **ReconciliationController**
   - Bank reconciliation process
   - Match transactions
   - Reconciliation reports

5. **FinancialReportController**
   - Balance sheet generation
   - P&L statement generation
   - Cash flow statement
   - Custom report builder

6. **FiscalPeriodController**
   - Period management
   - Year-end closing procedures
   - Period locking/unlocking

## Views to Implement

1. **Chart of Accounts Views**
   - Account listing with hierarchy
   - Account creation/edit forms
   - Account detail with transaction history

2. **Journal Entry Views**
   - Journal entry listing
   - Journal entry creation form
   - Entry detail with ledger lines

3. **Bank Management Views**
   - Bank account listing
   - Bank transaction import
   - Bank reconciliation interface

4. **Financial Report Views**
   - Balance sheet with customizable periods
   - P&L statement with comparison options
   - Cash flow statement
   - Report customization interface

5. **Fiscal Period Views**
   - Period listing
   - Period closing interface
   - Year-end process wizard

## Integration Points

1. **Invoice Module Integration**
   - Automatic journal entries for invoices
   - Revenue recognition configuration
   - Customer payment application

2. **Expense Module Integration**
   - Automatic journal entries for expenses
   - Expense categorization to accounts
   - Vendor payment tracking

3. **Dashboard Integration**
   - Financial KPI widgets
   - Balance summary
   - Overdue account alerts

## Role-Based Access

The Accountant role will have extended permissions for the Bookkeeping module:

```php
// Accountant role bookkeeping permissions
[
    'bookkeeping.view',        // View bookkeeping data
    'bookkeeping.accounts.*',  // Full chart of accounts access
    'bookkeeping.journals.*',  // Full journal entry access
    'bookkeeping.bank.*',      // Full bank reconciliation access
    'bookkeeping.reports.*',   // Full financial report access
    'bookkeeping.periods.view' // View fiscal periods (but not close)
]
```

The Administrator role will have all permissions plus:

```php
[
    'bookkeeping.periods.*',   // Full fiscal period management
    'bookkeeping.settings.*'   // Accounting settings management
]
```

## Implementation Timeline

1. **Phase 1: Core Accounting Foundation** (3 weeks)
   - Chart of Accounts implementation
   - General Ledger structure
   - Basic journal entries

2. **Phase 2: Bank and Reconciliation** (2 weeks)
   - Bank account management
   - Transaction import
   - Basic reconciliation

3. **Phase 3: Financial Reporting** (2 weeks)
   - Balance Sheet implementation
   - P&L Statement implementation
   - Cash Flow Statement

4. **Phase 4: Period Management & Integration** (2 weeks)
   - Fiscal period management
   - Invoice/Expense integration
   - Year-end processes

Total estimated time: 9 weeks

## Conclusion

Adding this comprehensive Bookkeeping module will transform the Billy system from a business management tool to a complete financial management system. The implementation will follow accounting best practices and integrate seamlessly with existing modules, while providing the necessary tools for accountants to properly manage a company's books.
