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
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('email_notifications_enabled')->default(true);
            $table->boolean('email_on_rent_received')->default(true);
            $table->boolean('email_on_rent_late')->default(true);
            $table->boolean('email_on_rent_partial')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email_notifications_enabled',
                'email_on_rent_received',
                'email_on_rent_late',
                'email_on_rent_partial'
            ]);
        });
    }
};
