<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\RecurringBillingController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\Exports\ReportExportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('login');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', function () {
        return view('dashboard.temp-index');
    })->name('dashboard');

    // Business Management
    Route::resource('businesses', BusinessController::class);
    Route::put('/businesses/{business}/settings', [BusinessController::class, 'updateSettings'])->name('businesses.update-settings');
    
    // Tax Rate Management
    Route::resource('tax-rates', TaxRateController::class);
    Route::post('/tax-rates/{taxRate}/set-default', [TaxRateController::class, 'setDefault'])->name('tax-rates.set-default');
    
    // Document Management
    Route::resource('documents', DocumentController::class);
    Route::get('/documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');
    Route::post('/documents/batch-upload', [DocumentController::class, 'batchUpload'])->name('documents.batch-upload');
    Route::get('/entity/{entityType}/{entityId}/documents', [DocumentController::class, 'listByEntity'])->name('documents.by-entity');
    
    // Settings Management
    Route::get('/settings/business/{businessId?}', [SettingsController::class, 'business'])->name('settings.business');
    Route::put('/settings/business/{businessId}', [SettingsController::class, 'updateBusiness'])->name('settings.business.update');
    Route::get('/settings/user', [SettingsController::class, 'user'])->name('settings.user');
    Route::put('/settings/user', [SettingsController::class, 'updateUser'])->name('settings.user.update');
    Route::get('/settings/system', [SettingsController::class, 'system'])->name('settings.system');
    Route::put('/settings/system', [SettingsController::class, 'updateSystem'])->name('settings.system.update');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer Management
    Route::resource('customers', CustomerController::class);

    // Invoice Management
    Route::resource('invoices', InvoiceController::class);
    Route::put('/invoices/{invoice}/mark-as-sent', [InvoiceController::class, 'markAsSent'])->name('invoices.mark-as-sent');
    Route::post('/invoices/{invoice}/record-payment', [InvoiceController::class, 'recordPayment'])->name('invoices.record-payment');

    // Service Management
    Route::resource('services', ServiceController::class);

    // Expense Management
    Route::resource('expenses', ExpenseController::class);

    // Recurring Billing Management
    Route::resource('recurring', RecurringBillingController::class);
    Route::put('/recurring/{recurring}/update-status', [RecurringBillingController::class, 'updateStatus'])->name('recurring.update-status');
    Route::post('/recurring/{recurring}/generate-invoice', [RecurringBillingController::class, 'generateInvoice'])->name('recurring.generate-invoice');

    // Reports
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/income', [ReportController::class, 'income'])->name('reports.income');
    Route::get('/reports/expenses', [ReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('/reports/customers', [ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/tax', [ReportController::class, 'tax'])->name('reports.tax');

    // Report Exports (PDF only)
    Route::prefix('exports')->name('exports.')->group(function () {
        // Income exports
        Route::get('/income/pdf', [ReportExportController::class, 'incomeAsPdf'])->name('income.pdf');
        
        // Expense exports
        Route::get('/expenses/pdf', [ReportExportController::class, 'expensesAsPdf'])->name('expenses.pdf');
        
        // Tax exports
        Route::get('/tax/pdf', [ReportExportController::class, 'taxAsPdf'])->name('tax.pdf');
        
        // Customer exports
        Route::get('/customers/pdf', [ReportExportController::class, 'customersAsPdf'])->name('customers.pdf');
    });

    // The following routes are commented out as controllers are not implemented yet
    /*
    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // System Settings
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    */
});

// Include accounting portal routes
require __DIR__.'/accounting.php';
require __DIR__.'/auth.php';
