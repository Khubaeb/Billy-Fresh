<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
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
    return redirect()->route('dashboard');
});

Route::middleware('auth')->group(function () {
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Customer Management
    Route::resource('customers', \App\Http\Controllers\CustomerController::class);

    // Invoice Management
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    
    // Service Management
    Route::resource('services', \App\Http\Controllers\ServiceController::class);
    
    // Expense Management
    Route::resource('expenses', \App\Http\Controllers\ExpenseController::class);
    
    // Recurring Billing
    Route::resource('recurring', \App\Http\Controllers\RecurringBillingController::class);
    
    // Reports
    Route::get('/reports', [\App\Http\Controllers\ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/income', [\App\Http\Controllers\ReportController::class, 'income'])->name('reports.income');
    Route::get('/reports/expenses', [\App\Http\Controllers\ReportController::class, 'expenses'])->name('reports.expenses');
    Route::get('/reports/customers', [\App\Http\Controllers\ReportController::class, 'customers'])->name('reports.customers');
    Route::get('/reports/tax', [\App\Http\Controllers\ReportController::class, 'tax'])->name('reports.tax');
    
    // User Management
    Route::resource('users', \App\Http\Controllers\UserController::class);
    
    // System Settings
    Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
    Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
});

require __DIR__.'/auth.php';
