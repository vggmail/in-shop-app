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
        Schema::table('categories', function (Blueprint $table) {
            if (!Schema::hasColumn('categories', 'deleted_at')) { $table->softDeletes(); }
        });
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'deleted_at')) { $table->softDeletes(); }
        });
        Schema::table('customers', function (Blueprint $table) {
            if (!Schema::hasColumn('customers', 'deleted_at')) { $table->softDeletes(); }
        });
        Schema::table('coupons', function (Blueprint $table) {
            if (!Schema::hasColumn('coupons', 'deleted_at')) { $table->softDeletes(); }
        });
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'deleted_at')) { $table->softDeletes(); }
        });
        Schema::table('expenses', function (Blueprint $table) {
            if (!Schema::hasColumn('expenses', 'deleted_at')) { $table->softDeletes(); }
        });
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'deleted_at')) { $table->softDeletes(); }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('items', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('customers', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('coupons', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('orders', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('expenses', function (Blueprint $table) { $table->dropSoftDeletes(); });
        Schema::table('users', function (Blueprint $table) { $table->dropSoftDeletes(); });
    }
};
