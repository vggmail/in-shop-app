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
            try { $table->index('is_available'); } catch (\Exception $e) {}
        });

        Schema::table('orders', function (Blueprint $table) {
            try { $table->index('status'); } catch (\Exception $e) {}
            try { $table->index('payment_status'); } catch (\Exception $e) {}
            try { $table->index('order_type'); } catch (\Exception $e) {}
            try { $table->index('created_at'); } catch (\Exception $e) {}
        });

        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'parent_id')) {
                try { $table->index('parent_id'); } catch (\Exception $e) {}
            }
        });

        Schema::table('tenants', function (Blueprint $table) {
            try { $table->index('is_active'); } catch (\Exception $e) {}
            try { $table->index('subdomain'); } catch (\Exception $e) {}
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
