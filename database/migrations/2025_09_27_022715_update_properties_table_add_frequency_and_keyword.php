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
        Schema::table('properties', function (Blueprint $table) {
            // Change account_id to bank_statement_keyword
            $table->renameColumn('account_id', 'bank_statement_keyword');

            // Change rent_due_day to store day of week (0-6, Sunday-Saturday)
            $table->renameColumn('rent_due_day', 'rent_due_day_of_week');

            // Add rent frequency field
            $table->enum('rent_frequency', ['weekly', 'fortnightly', 'monthly'])->default('monthly');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('properties', function (Blueprint $table) {
            // Reverse the changes
            $table->renameColumn('bank_statement_keyword', 'account_id');
            $table->renameColumn('rent_due_day_of_week', 'rent_due_day');
            $table->dropColumn('rent_frequency');
        });
    }
};
