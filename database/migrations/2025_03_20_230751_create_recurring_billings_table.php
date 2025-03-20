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
        Schema::create('recurring_billings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('business_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('USD');
            $table->string('frequency'); // weekly, monthly, quarterly, annually
            $table->integer('interval')->default(1); // every 1 month, every 2 weeks, etc.
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_date');
            $table->integer('occurrences')->nullable(); // total number of times to bill
            $table->integer('occurrences_remaining')->nullable(); 
            $table->string('status')->default('active'); // active, paused, completed, cancelled
            $table->text('notes')->nullable();
            $table->timestamp('last_billed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recurring_billings');
    }
};
