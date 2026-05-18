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
        if (Schema::hasTable('ingredients')) {
            Schema::table('ingredients', function (Blueprint $table) {
                if (!Schema::hasColumn('ingredients', 'is_alcohol')) {
                    $table->boolean('is_alcohol')->default(false)->after('is_active');
                }
                if (!Schema::hasColumn('ingredients', 'bottle_size_ml')) {
                    $table->decimal('bottle_size_ml', 8, 2)->nullable()->after('is_alcohol');
                }
            });
        }

        if (!Schema::hasTable('bar_wastages')) {
            Schema::create('bar_wastages', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('tenant_id')->nullable();
                $table->unsignedBigInteger('ingredient_id');
                $table->string('type'); // 'Breakage', 'Spill', 'Free Pour', 'Complimentary'
                $table->decimal('quantity', 8, 3); // in bottles/units
                $table->decimal('volume_ml', 8, 2)->nullable(); // equivalent ml
                $table->unsignedBigInteger('logged_by')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();

                $table->foreign('ingredient_id')->references('id')->on('ingredients')->onDelete('cascade');
                $table->foreign('logged_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bar_wastages');

        if (Schema::hasTable('ingredients')) {
            Schema::table('ingredients', function (Blueprint $table) {
                if (Schema::hasColumn('ingredients', 'is_alcohol')) {
                    $table->dropColumn('is_alcohol');
                }
                if (Schema::hasColumn('ingredients', 'bottle_size_ml')) {
                    $table->dropColumn('bottle_size_ml');
                }
            });
        }
    }
};
