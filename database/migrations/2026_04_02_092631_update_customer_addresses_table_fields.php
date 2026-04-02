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
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->string('street_address')->after('customer_id');
            $table->string('city')->after('street_address');
            $table->string('state')->after('city');
            $table->string('pincode')->after('state');
            $table->dropColumn('address');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customer_addresses', function (Blueprint $table) {
            $table->text('address')->after('customer_id');
            $table->dropColumn(['street_address', 'city', 'state', 'pincode']);
        });
    }
};
