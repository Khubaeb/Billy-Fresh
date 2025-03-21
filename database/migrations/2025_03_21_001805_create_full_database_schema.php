<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Create users table
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        // Create roles table
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->json('permissions')->nullable();
            $table->timestamps();
        });

        // Create businesses table
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('business_id')->nullable();
            $table->string('logo')->nullable();
            $table->text('address')->nullable();
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('website')->nullable();
            $table->json('settings')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('logo_path')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create user_roles table (pivot table)
        Schema::create('user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });

        // Create customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('identification_number')->nullable();
            $table->string('company_name')->nullable();
            $table->text('address')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->string('website')->nullable();
            $table->string('status')->default('active');
            $table->date('next_contact_date')->nullable();
            $table->string('category')->nullable();
            $table->string('tax_number')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create payment_methods table
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('card_last_four')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('holder_name')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Create services table
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('default_price', 15, 2);
            $table->decimal('cost', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('unit')->nullable()->default('hour'); // hour, day, item, etc.
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->string('category')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('sku')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create tax_rates table
        Schema::create('tax_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->decimal('percentage', 5, 2);
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Create invoices table
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('invoice_number');
            $table->date('invoice_date');
            $table->date('due_date');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->text('terms')->nullable();
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_amount', 15, 2);
            $table->decimal('amount_paid', 15, 2)->default(0);
            $table->decimal('amount_due', 15, 2);
            $table->string('status')->default('draft'); // draft, sent, paid, overdue, partially_paid, cancelled
            $table->string('currency', 3)->default('USD');
            $table->string('tax_type')->nullable(); // percentage or fixed
            $table->decimal('tax_rate', 5, 2)->nullable();
            $table->string('discount_type')->nullable(); // percentage or fixed
            $table->decimal('discount_rate', 5, 2)->nullable();
            $table->string('document_path')->nullable();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->timestamps();
            $table->softDeletes();
        });

        // Create invoice items table
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('quantity', 10, 2);
            $table->decimal('unit_price', 15, 2);
            $table->decimal('tax_rate', 5, 2)->default(0);
            $table->decimal('discount_rate', 5, 2)->default(0);
            $table->decimal('subtotal', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->decimal('discount_amount', 15, 2)->default(0);
            $table->decimal('total_price', 15, 2);
            $table->timestamps();
        });

        // Create invoice payments table
        Schema::create('invoice_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('payment_date');
            $table->decimal('amount', 15, 2);
            $table->string('payment_method');
            $table->string('reference')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Create expense categories table
        Schema::create('expense_categories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('color', 7)->nullable(); // hex color code
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Create expenses table
        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('expense_categories')->nullOnDelete();
            $table->text('description');
            $table->decimal('amount', 15, 2);
            $table->decimal('tax_amount', 15, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->date('expense_date');
            $table->string('vendor')->nullable();
            $table->string('reference')->nullable();
            $table->boolean('is_recurring')->default(false);
            $table->boolean('is_billable')->default(false);
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_method')->nullable();
            $table->string('status')->default('completed'); // completed, pending, cancelled
            $table->string('document_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create recurring billings table
        Schema::create('recurring_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('frequency'); // daily, weekly, monthly, quarterly, yearly
            $table->integer('interval')->default(1); // every X days/weeks/months
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_billing_date');
            $table->date('last_billed_date')->nullable();
            $table->string('status')->default('active'); // active, paused, completed, cancelled
            $table->integer('billing_count')->default(0); // number of times billed
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });

        // Create documents table
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->morphs('documentable');
            $table->string('name');
            $table->string('path');
            $table->string('type');
            $table->bigInteger('size');
            $table->timestamps();
        });

        // Create document_templates table
        Schema::create('document_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('type');
            $table->text('content');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });

        // Create activity_logs table
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->bigInteger('entity_id');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Create notifications table
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->json('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // Create settings table
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->morphs('settable');
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });

        // Create password reset tokens table
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        // Create failed jobs table
        Schema::create('failed_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->text('connection');
            $table->text('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();
        });

        // Create test user
        User::create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Log creation
        echo "Test user created: test@example.com / password123" . PHP_EOL;
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('notifications');
        Schema::dropIfExists('activity_logs');
        Schema::dropIfExists('document_templates');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('recurring_billings');
        Schema::dropIfExists('expenses');
        Schema::dropIfExists('expense_categories');
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('tax_rates');
        Schema::dropIfExists('services');
        Schema::dropIfExists('payment_methods');
        Schema::dropIfExists('customers');
        Schema::dropIfExists('user_roles');
        Schema::dropIfExists('businesses');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
