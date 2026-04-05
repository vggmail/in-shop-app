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
            if (!Schema::hasColumn('tenants', 'dine_in_enabled')) {
                $table->boolean('dine_in_enabled')->default(true);
            }
            if (!Schema::hasColumn('tenants', 'takeaway_enabled')) {
                $table->boolean('takeaway_enabled')->default(true);
            }
            if (!Schema::hasColumn('tenants', 'home_delivery_enabled')) {
                $table->boolean('home_delivery_enabled')->default(true);
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
            if (Schema::hasColumn('tenants', 'dine_in_enabled')) $columnsToDrop[] = 'dine_in_enabled';
            if (Schema::hasColumn('tenants', 'takeaway_enabled')) $columnsToDrop[] = 'takeaway_enabled';
            if (Schema::hasColumn('tenants', 'home_delivery_enabled')) $columnsToDrop[] = 'home_delivery_enabled';
            
            if (!empty($columnsToDrop)) {
                $table->dropColumn($columnsToDrop);
            }
        });
    }
};
