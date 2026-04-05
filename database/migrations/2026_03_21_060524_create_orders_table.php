<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends \Illuminate\Database\Migrations\Migration
{
    public function up()
    {
        if (!Schema::hasTable('orders')) {
            Schema::create('orders', function (Blueprint $table) {
                $table->id();
                $table->string('order_number')->unique();
                $table->foreignId('customer_id')->nullable()->constrained()->onDelete('set null');
                $table->string('order_type');
                $table->string('table_number')->nullable();
                $table->decimal('total_amount', 10, 2);
                $table->decimal('discount_amount', 10, 2)->default(0);
                $table->decimal('grand_total', 10, 2);
                $table->string('payment_method');
                $table->string('payment_status')->default('Paid');
                $table->string('status')->default('Preparing');
                $table->text('note')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
};
