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
        if (!Schema::connection('mysql')->hasTable('payment_gateways')) {
            Schema::connection('mysql')->create('payment_gateways', function (Blueprint $table) {
                $table->id();
                $table->foreignId('tenant_id')->constrained('tenants')->onDelete('cascade');
                $table->string('gateway_name'); // e.g., 'PayU', 'Razorpay'
                $table->json('settings')->nullable(); // Store key, salt, mode, etc.
                $table->boolean('is_active')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mysql')->dropIfExists('payment_gateways');
    }
};
