<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create administrator role with access to all modules
        Role::create([
            'name' => 'Administrator',
            'permissions' => [
                'customers.*',   // Full access to customer module
                'invoices.*',    // Full access to invoice module
                'services.*',    // Full access to service module
                'expenses.*',    // Full access to expense module
                'reports.*',     // Full access to reports module
                'businesses.*',  // Full access to business settings
                'taxrates.*',    // Full access to tax rates
                'settings.*',    // Full access to settings
                'users.*',       // Full access to user management
                'documents.*',   // Full access to document management
                'recurrings.*',  // Full access to recurring billing
                '*'              // Full system access
            ]
        ]);

        // Create manager role with access to most modules but limited admin capabilities
        Role::create([
            'name' => 'Manager',
            'permissions' => [
                'customers.*',   // Full access to customer module
                'invoices.*',    // Full access to invoice module
                'services.*',    // Full access to service module
                'expenses.*',    // Full access to expense module
                'reports.view',  // View reports
                'businesses.view', // View business settings
                'taxrates.*',    // Full access to tax rates
                'documents.*',   // Full access to document management
                'recurrings.*'   // Full access to recurring billing
            ]
        ]);

        // Create sales role with limited access to customer and invoice related modules
        Role::create([
            'name' => 'Sales',
            'permissions' => [
                'customers.*',    // Full access to customer module
                'invoices.create', // Create invoices
                'invoices.view',   // View invoices
                'services.view',   // View services
                'reports.income.view' // View income reports
            ]
        ]);

        // Create accountant role with access to bookkeeping modules
        Role::create([
            'name' => 'Accountant',
            'permissions' => [
                'customers.view',  // View customers only
                'invoices.view',   // View invoices
                'expenses.*',      // Full access to expenses
                'reports.*',       // Full access to all reports
                'taxrates.view'    // View tax rates
            ]
        ]);

        // Create viewer role with read-only access
        Role::create([
            'name' => 'Viewer',
            'permissions' => [
                'customers.view',  // View customers
                'invoices.view',   // View invoices
                'services.view',   // View services
                'expenses.view',   // View expenses
                'reports.view'     // View reports
            ]
        ]);
    }
}
