<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations to add performance-enhancing indexes to frequently queried columns.
     */
    public function up(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->index('is_available');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
            $table->index('order_type');
            $table->index('created_at');
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->index('parent_id');
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('subdomain'); // subdomain is unique but explicit index for prefix search if needed
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropIndex(['is_available']);
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['order_type']);
            $table->dropIndex(['created_at']);
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                $table->dropIndex(['parent_id']);
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['subdomain']);
        });
    }
};
