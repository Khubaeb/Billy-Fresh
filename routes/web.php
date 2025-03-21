<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\RecurringBillingController;
use App\Http\Controllers\ReportController;
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

    // The following routes are commented out as controllers are not implemented yet
    /*
    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class);

    // System Settings
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
    */
});

require __DIR__.'/auth.php';
