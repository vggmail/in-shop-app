<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * This migration runs on the CENTRAL (mysql) database — the tenants table.
     * enabled_menus stores a JSON array of menu keys the store admin can see.
     * null = all menus enabled (backward-compatible default for existing tenants).
     */
    protected $connection = 'mysql';

    public function up(): void
    {
        Schema::connection('mysql')->table('tenants', function (Blueprint $table) {
            $table->json('enabled_menus')->nullable()->after('floor_plans');
        });
    }

    public function down(): void
    {
        Schema::connection('mysql')->table('tenants', function (Blueprint $table) {
            $table->dropColumn('enabled_menus');
        });
    }
};
