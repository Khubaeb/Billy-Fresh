# Database Schema for Logicstudio Clone

This document outlines the database schema for the Logicstudio clone system. It includes all tables, relationships, and key fields.

## Core Tables

### users
- `id` bigint PK
- `name` varchar
- `email` varchar
- `password` varchar
- `remember_token` varchar
- `email_verified_at` timestamp
- `created_at` timestamp
- `updated_at` timestamp

### user_roles
- `id` bigint PK
- `user_id` bigint FK
- `business_id` bigint FK
- `role_id` bigint FK
- `created_at` timestamp
- `updated_at` timestamp

### roles
- `id` bigint PK
- `name` varchar
- `permissions` json
- `created_at` timestamp
- `updated_at` timestamp

### businesses
- `id` bigint PK
- `name` varchar
- `business_id` varchar
- `logo` varchar
- `address` text
- `phone` varchar
- `email` varchar
- `tax_number` varchar
- `settings` json
- `created_at` timestamp
- `updated_at` timestamp

## Customer Management

### customers
- `id` bigint PK
- `business_id` bigint FK
- `full_name` varchar
- `email` varchar
- `phone` varchar
- `identification_number` varchar
- `company_name` varchar
- `address` text
- `notes` text
- `status` varchar
- `next_contact_date` date
- `category` varchar
- `created_at` timestamp
- `updated_at` timestamp

## Invoice Management

### invoices
- `id` bigint PK
- `business_id` bigint FK
- `customer_id` bigint FK
- `invoice_number` varchar
- `invoice_date` date
- `due_date` date
- `total_amount` decimal
- `tax_amount` decimal
- `status` varchar
- `notes` text
- `payment_method_id` bigint FK
- `document_path` varchar
- `created_at` timestamp
- `updated_at` timestamp

### invoice_items
- `id` bigint PK
- `invoice_id` bigint FK
- `service_id` bigint FK
- `description` text
- `quantity` decimal
- `unit_price` decimal
- `tax_rate` decimal
- `total_price` decimal
- `created_at` timestamp
- `updated_at` timestamp

## Service Management

### services
- `id` bigint PK
- `business_id` bigint FK
- `name` varchar
- `description` text
- `default_price` decimal
- `category` varchar
- `is_active` boolean
- `created_at` timestamp
- `updated_at` timestamp

## Expense Management

### expenses
- `id` bigint PK
- `business_id` bigint FK
- `category_id` bigint FK
- `description` text
- `amount` decimal
- `tax_amount` decimal
- `expense_date` date
- `vendor` varchar
- `document_path` varchar
- `created_at` timestamp
- `updated_at` timestamp

### expense_categories
- `id` bigint PK
- `business_id` bigint FK
- `name` varchar
- `description` text
- `created_at` timestamp
- `updated_at` timestamp

## Recurring Billing

### recurring_billings
- `id` bigint PK
- `business_id` bigint FK
- `customer_id` bigint FK
- `service_id` bigint FK
- `description` text
- `amount` decimal
- `frequency` varchar
- `start_date` date
- `end_date` date
- `next_billing_date` date
- `is_active` boolean
- `payment_method_id` bigint FK
- `created_at` timestamp
- `updated_at` timestamp

## Payment Processing

### payment_methods
- `id` bigint PK
- `business_id` bigint FK
- `customer_id` bigint FK
- `type` varchar
- `card_last_four` varchar
- `expiry_date` date
- `holder_name` varchar
- `is_default` boolean
- `created_at` timestamp
- `updated_at` timestamp

## Document Management

### documents
- `id` bigint PK
- `business_id` bigint FK
- `documentable_id` bigint
- `documentable_type` varchar
- `name` varchar
- `path` varchar
- `type` varchar
- `size` bigint
- `created_at` timestamp
- `updated_at` timestamp

### document_templates
- `id` bigint PK
- `business_id` bigint FK
- `name` varchar
- `type` varchar
- `content` text
- `is_default` boolean
- `created_at` timestamp
- `updated_at` timestamp

## Accounting Portal

### accounting_exports
- `id` bigint PK
- `business_id` bigint FK
- `user_id` bigint FK
- `export_type` varchar
- `start_date` date
- `end_date` date
- `period_type` varchar
- `format` varchar
- `status` varchar
- `file_path` varchar
- `parameters` json
- `download_token` varchar
- `completed_at` timestamp
- `created_at` timestamp
- `updated_at` timestamp

### accounting_settings
- `id` bigint PK
- `business_id` bigint FK
- `accounting_office_name` varchar
- `accounting_contact_number` varchar
- `accounting_email` varchar
- `accounting_software` varchar
- `export_format_preference` varchar
- `include_attachments` boolean
- `auto_export_enabled` boolean
- `auto_export_frequency` varchar
- `auto_export_settings` json
- `account_code_mapping` json
- `created_at` timestamp
- `updated_at` timestamp

### export_templates
- `id` bigint PK
- `business_id` bigint FK
- `name` varchar
- `export_type` varchar
- `settings` json
- `is_default` boolean
- `created_at` timestamp
- `updated_at` timestamp

### accounting_documents
- `id` bigint PK
- `business_id` bigint FK
- `user_id` bigint FK
- `document_type` varchar
- `name` varchar
- `file_path` varchar
- `document_date` date
- `reference_number` varchar
- `source_type` varchar
- `source_id` bigint
- `amount` decimal
- `is_expense` boolean
- `category` varchar
- `notes` text
- `created_at` timestamp
- `updated_at` timestamp

## System Features

### activity_logs
- `id` bigint PK
- `user_id` bigint FK
- `business_id` bigint FK
- `action` varchar
- `entity_type` varchar
- `entity_id` bigint
- `metadata` json
- `created_at` timestamp

### notifications
- `id` bigint PK
- `user_id` bigint FK
- `business_id` bigint FK
- `type` varchar
- `data` json
- `read_at` timestamp
- `created_at` timestamp

### settings
- `id` bigint PK
- `settable_type` varchar
- `settable_id` bigint
- `key` varchar
- `value` text
- `created_at` timestamp
- `updated_at` timestamp

### tax_rates
- `id` bigint PK
- `business_id` bigint FK
- `name` varchar
- `percentage` decimal
- `is_default` boolean
- `created_at` timestamp
- `updated_at` timestamp

## Relationships

1. **Users & Businesses**: Users can access multiple businesses through user_roles
2. **Businesses & Customers**: A business has many customers
3. **Customers & Invoices**: A customer can have many invoices
4. **Invoices & Items**: An invoice has many line items
5. **Services & Invoice Items**: Services are used in invoice items
6. **Expenses & Categories**: Expenses belong to categories
7. **Customers & Recurring Billings**: Customers can have recurring billing agreements
8. **Businesses & Documents**: Businesses have various documents
9. **Polymorphic Relationships**: Documents can belong to various entities through documentable
10. **Settings**: Polymorphic relationship through settable
11. **Accounting Portal**: Businesses have accounting settings, exports, templates, and documents
