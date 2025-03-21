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
        // Create accounting_exports table to track export jobs
        Schema::create('accounting_exports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('export_type'); // income_statement, account_card, vat_payments, profit_loss, etc.
            $table->date('start_date');
            $table->date('end_date');
            $table->string('period_type')->default('month'); // month, quarter, year, custom, etc.
            $table->string('format')->default('pdf'); // pdf, csv, excel, etc.
            $table->string('status')->default('pending'); // pending, processing, completed, failed
            $table->string('file_path')->nullable(); // Path to the exported file
            $table->json('parameters')->nullable(); // Additional export parameters
            $table->string('download_token')->nullable(); // Token for secure download
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });

        // Create accounting_settings table for configuring accounting integration
        Schema::create('accounting_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('accounting_office_name')->nullable();
            $table->string('accounting_contact_number')->nullable();
            $table->string('accounting_email')->nullable();
            $table->string('accounting_software')->nullable(); // The external accounting software used
            $table->string('export_format_preference')->default('pdf');
            $table->boolean('include_attachments')->default(true);
            $table->boolean('auto_export_enabled')->default(false);
            $table->string('auto_export_frequency')->nullable(); // daily, weekly, monthly
            $table->json('auto_export_settings')->nullable();
            $table->json('account_code_mapping')->nullable(); // Mapping of internal accounts to accounting software codes
            $table->timestamps();
        });

        // Create export_templates table
        Schema::create('export_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('export_type');
            $table->json('settings');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
        
        // Create accounting_documents table for tracking accounting-specific documents
        Schema::create('accounting_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('document_type'); // invoice, receipt, bank_statement, etc.
            $table->string('name');
            $table->string('file_path');
            $table->date('document_date');
            $table->string('reference_number')->nullable();
            $table->string('source_type')->nullable(); // invoice, expense, etc.
            $table->bigInteger('source_id')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->boolean('is_expense')->default(false);
            $table->string('category')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounting_documents');
        Schema::dropIfExists('export_templates');
        Schema::dropIfExists('accounting_settings');
        Schema::dropIfExists('accounting_exports');
    }
};
