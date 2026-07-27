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
        Schema::table('materials', function (Blueprint $table) {
            $table->renameColumn('balance', 'stock_qty');
            $table->integer('min_stock')->default(0)->after('balance'); // after works contextually, but after stock_qty depends on renaming order. just omitting after is fine.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->renameColumn('stock_qty', 'balance');
            $table->dropColumn('min_stock');
        });
    }
};
