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
            $table->integer('starting_token')->default(100)->after('disable_home_page');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->integer('token_number')->nullable()->after('order_number');
            $table->index(['token_number', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['token_number', 'status']);
            $table->dropColumn('token_number');
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn('starting_token');
        });
    }
};
