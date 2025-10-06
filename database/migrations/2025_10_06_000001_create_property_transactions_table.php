<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('property_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->foreignId('rent_check_id')->nullable()->constrained()->onDelete('set null');
            $table->date('transaction_date');
            $table->decimal('amount', 10, 2); // Positive = credit (payment in), Negative = debit (rent due)
            $table->enum('type', ['rent_due', 'rent_payment', 'manual_payment', 'adjustment']);
            $table->text('description')->nullable();
            $table->enum('source', ['system', 'manual', 'akahu'])->default('system');
            $table->string('akahu_transaction_id')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Indexes for common queries
            $table->index('property_id');
            $table->index('transaction_date');
            $table->index('type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('property_transactions');
    }
};
