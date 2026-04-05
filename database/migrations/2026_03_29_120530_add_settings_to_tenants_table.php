<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            if (!Schema::hasColumn('tenants', 'logo')) {
                $table->string('logo')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'tagline')) {
                $table->string('tagline')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'address')) {
                $table->text('address')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'city')) {
                $table->string('city')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'state')) {
                $table->string('state')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'pincode')) {
                $table->string('pincode')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'phone')) {
                $table->string('phone')->nullable();
            }
            if (!Schema::hasColumn('tenants', 'gst_number')) {
                $table->string('gst_number')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropColumn([
                'logo', 'tagline', 'address', 'city', 'state', 'pincode', 'phone', 'gst_number'
            ]);
        });
    }
};
