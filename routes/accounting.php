<?php

use App\Http\Controllers\AccountingPortalController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Accounting Portal Routes
|--------------------------------------------------------------------------
|
| Here is where we register routes for the accounting portal functionality.
| These routes are loaded within a "web" middleware group which
| includes authentication to ensure users are logged in.
|
| Additionally, these routes use middleware to ensure only users with
| Administrator or Accountant roles can access them.
|
*/

// Define middleware for accounting portal access
Route::middleware(['auth', 'role:Administrator,Accountant'])->group(function () {

    // Accounting Portal Dashboard
    Route::get('/accounting', [AccountingPortalController::class, 'index'])->name('accounting.index');
    
    // Accounting Options
    Route::get('/accounting/options', [AccountingPortalController::class, 'options'])->name('accounting.options');
    
    // Accounting Reports
    Route::get('/accounting/income-statement', [AccountingPortalController::class, 'incomeStatement'])->name('accounting.income-statement');
    Route::get('/accounting/account-card', [AccountingPortalController::class, 'accountCard'])->name('accounting.account-card');
    Route::get('/accounting/vat-payments', [AccountingPortalController::class, 'vatPayments'])->name('accounting.vat-payments');
    Route::get('/accounting/profit-loss', [AccountingPortalController::class, 'profitLoss'])->name('accounting.profit-loss');
    Route::get('/accounting/advanced-payments', [AccountingPortalController::class, 'advancedPayments'])->name('accounting.advanced-payments');
    Route::get('/accounting/centralized-card', [AccountingPortalController::class, 'centralizedCard'])->name('accounting.centralized-card');
    
    // Accounting Export Actions
    Route::post('/accounting/download-materials', [AccountingPortalController::class, 'downloadMaterials'])->name('accounting.download-materials');
    Route::post('/accounting/export-to-account', [AccountingPortalController::class, 'exportToAccount'])->name('accounting.export-to-account');
    Route::get('/accounting/account-card-index', [AccountingPortalController::class, 'accountCardIndex'])->name('accounting.account-card-index');
    
    // Accounting Settings
    Route::get('/accounting/settings', [AccountingPortalController::class, 'settings'])->name('accounting.settings');
    Route::post('/accounting/settings', [AccountingPortalController::class, 'updateSettings'])->name('accounting.update-settings');
});
