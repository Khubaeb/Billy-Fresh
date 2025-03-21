<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create chart of accounts table
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->references('id')->on('accounts')->nullOnDelete();
            $table->string('account_type'); // asset, liability, equity, income, expense
            $table->string('account_number')->nullable();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('balance', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_system')->default(false); // System accounts cannot be deleted
            $table->integer('display_order')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create journal entries table
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('entry_date');
            $table->string('reference')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('posted'); // draft, approved, posted, rejected
            $table->boolean('is_recurring')->default(false);
            $table->json('recurrence_pattern')->nullable();
            $table->foreignId('document_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('approved_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->string('entry_type')->default('manual'); // manual, system, recurring, imported
            $table->string('source_type')->nullable(); // invoice, expense, payment, etc.
            $table->bigInteger('source_id')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create ledger entries table
        Schema::create('ledger_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('journal_entry_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete();
            $table->decimal('debit_amount', 15, 2)->default(0);
            $table->decimal('credit_amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // Create bank accounts table
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('account_id')->constrained()->cascadeOnDelete(); // Links to chart of accounts
            $table->string('bank_name');
            $table->string('account_number')->nullable();
            $table->string('account_name');
            $table->string('currency', 3)->default('USD');
            $table->decimal('opening_balance', 15, 2)->default(0);
            $table->decimal('current_balance', 15, 2)->default(0);
            $table->date('last_reconciled_date')->nullable();
            $table->string('account_type')->default('checking'); // checking, savings, credit, etc.
            $table->string('routing_number')->nullable();
            $table->string('iban')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create bank transactions table
        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->date('transaction_date');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->boolean('is_debit')->default(true);
            $table->string('reference')->nullable();
            $table->string('category')->nullable();
            $table->string('status')->default('pending'); // pending, cleared, reconciled, void
            $table->boolean('is_reconciled')->default(false);
            $table->date('reconciled_date')->nullable();
            $table->foreignId('reconciled_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->foreignId('journal_entry_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payee')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create fiscal periods table
        Schema::create('fiscal_periods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->date('start_date');
            $table->date('end_date');
            $table->string('name');
            $table->string('period_type')->default('month'); // month, quarter, year
            $table->boolean('is_closed')->default(false);
            $table->foreignId('closed_by')->nullable()->references('id')->on('users')->nullOnDelete();
            $table->timestamp('closed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Create financial reports table
        Schema::create('financial_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('report_type'); // balance_sheet, income_statement, cash_flow, etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->string('name');
            $table->json('parameters')->nullable(); // Additional report parameters
            $table->json('filters')->nullable();
            $table->json('display_options')->nullable(); // Formatting options
            $table->string('status')->default('generated');
            $table->string('file_path')->nullable(); // Path to stored report file if any
            $table->timestamps();
        });

        // Create bank reconciliations table
        Schema::create('bank_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('statement_date');
            $table->decimal('statement_balance', 15, 2);
            $table->decimal('bank_balance', 15, 2); // Balance per accounting system
            $table->decimal('reconciled_balance', 15, 2);
            $table->decimal('difference', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->string('status')->default('in_progress'); // in_progress, completed, cancelled
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Create bank reconciliation items table
        Schema::create('bank_reconciliation_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reconciliation_id')->constrained('bank_reconciliations')->cascadeOnDelete();
            $table->foreignId('transaction_id')->constrained('bank_transactions')->cascadeOnDelete();
            $table->boolean('is_cleared')->default(false);
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_reconciliation_items');
        Schema::dropIfExists('bank_reconciliations');
        Schema::dropIfExists('financial_reports');
        Schema::dropIfExists('fiscal_periods');
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
        Schema::dropIfExists('ledger_entries');
        Schema::dropIfExists('journal_entries');
        Schema::dropIfExists('accounts');
    }
};
