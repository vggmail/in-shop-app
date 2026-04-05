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
        if (!Schema::hasTable('payment_attempts')) {
            Schema::create('payment_attempts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->onDelete('cascade');
                $table->string('txnid')->index();
                $table->string('mihpayid')->nullable()->index();
                $table->string('status');
                $table->decimal('amount', 10, 2);
                $table->text('hash_string')->nullable();
                $table->string('calculated_hash')->nullable();
                $table->string('received_hash')->nullable();
                $table->json('request_data')->nullable();
                $table->json('response_data')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_attempts');
    }
};
