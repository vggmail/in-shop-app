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
        Schema::table('items', function (Blueprint $table) {
            $table->decimal('mrp', 10, 2)->nullable()->after('price');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->string('coupon_type')->default('Customer')->after('min_bill_amount');
            $table->boolean('show_on_home')->default(false)->after('coupon_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('mrp');
        });

        Schema::table('coupons', function (Blueprint $table) {
            $table->dropColumn(['coupon_type', 'show_on_home']);
        });
    }
};
