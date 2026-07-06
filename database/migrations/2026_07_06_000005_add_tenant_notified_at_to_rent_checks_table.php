<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tracks whether the tenant has been emailed about this missed payment,
     * so re-checks never send duplicate notifications and grace-period
     * delays survive across check runs.
     */
    public function up(): void
    {
        Schema::table('rent_checks', function (Blueprint $table) {
            $table->timestamp('tenant_notified_at')->nullable()->after('checked_at');
        });
    }

    public function down(): void
    {
        Schema::table('rent_checks', function (Blueprint $table) {
            $table->dropColumn('tenant_notified_at');
        });
    }
};
