<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('items', function (Blueprint $table) {
            if (!Schema::hasColumn('items', 'stock_quantity')) {
                $table->integer('stock_quantity')->default(100);
            }
            if (!Schema::hasColumn('items', 'low_stock_limit')) {
                $table->integer('low_stock_limit')->default(10);
            }
        });
    }

    public function down()
    {
        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn(['stock_quantity', 'low_stock_limit']);
        });
    }
};
