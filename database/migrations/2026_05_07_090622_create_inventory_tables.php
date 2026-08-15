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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit'); // kg, gm, ltr, ml, pcs, pkt
            $table->decimal('stock_quantity', 15, 3)->default(0);
            $table->decimal('min_stock_level', 15, 3)->default(0);
            $table->decimal('cost_per_unit', 15, 2)->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('item_ingredients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained()->onDelete('cascade');
            $table->foreignId('variant_id')->nullable()->constrained('item_variants')->onDelete('cascade');
            $table->foreignId('ingredient_id')->constrained()->onDelete('cascade');
            $table->decimal('quantity', 15, 3); // Quantity of ingredient used per item
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('item_ingredients');
        Schema::dropIfExists('ingredients');
    }
};
