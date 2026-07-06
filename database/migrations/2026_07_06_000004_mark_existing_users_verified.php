<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Accounts created before email verification existed are grandfathered
     * in as verified so the new 'verified' middleware doesn't lock them out.
     */
    public function up(): void
    {
        DB::table('users')
            ->whereNull('email_verified_at')
            ->update(['email_verified_at' => now()]);
    }

    public function down(): void
    {
        // Intentionally irreversible - we can't know which users were grandfathered
    }
};
