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
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'cash_enabled')) {
                $table->boolean('cash_enabled')->default(true);
            }
            if (!Schema::hasColumn('tenants', 'online_enabled')) {
                $table->boolean('online_enabled')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $columnsToDrop = [];
            if (Schema::hasColumn('tenants', 'cash_enabled')) $columnsToDrop[] = 'cash_enabled';
            if (Schema::hasColumn('tenants', 'online_enabled')) $columnsToDrop[] = 'online_enabled';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
