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
        // We'll create all tables directly here in the proper order to avoid foreign key issues
        
        // First, drop any existing tables in reverse dependency order
        Schema::dropIfExists('customers');
        Schema::dropIfExists('businesses');
        
        // Now create the businesses table first
        Schema::create('businesses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('logo_path')->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('website')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('registration_number')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->string('currency', 3)->default('USD');
            $table->string('date_format')->default('m/d/Y');
            $table->string('fiscal_year_start')->default('01-01');
            $table->decimal('default_tax_rate', 5, 2)->default(0);
            $table->text('invoice_notes')->nullable();
            $table->text('invoice_terms')->nullable();
            $table->text('invoice_footer')->nullable();
            $table->string('invoice_prefix')->default('INV-');
            $table->integer('next_invoice_number')->default(1);
            $table->string('time_zone')->default('UTC');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
        
        // Then create the customers table
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('company')->nullable();
            $table->string('tax_number')->nullable();
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('country')->nullable();
            $table->text('notes')->nullable();
            $table->string('website')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order of dependencies
        Schema::dropIfExists('customers');
        Schema::dropIfExists('businesses');
    }
};
