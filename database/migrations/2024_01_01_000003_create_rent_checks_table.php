<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rent_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('property_id')->constrained()->onDelete('cascade');
            $table->date('due_date');
            $table->decimal('expected_amount', 10, 2);
            $table->decimal('received_amount', 10, 2)->nullable();
            $table->enum('status', ['pending', 'received', 'late', 'partial'])->default('pending');
            $table->string('transaction_id')->nullable();
            $table->timestamp('received_at')->nullable();
            $table->timestamp('checked_at')->nullable();
            $table->json('matching_transactions')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rent_checks');
    }
};