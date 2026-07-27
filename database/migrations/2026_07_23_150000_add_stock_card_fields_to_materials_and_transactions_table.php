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
            if (!Schema::hasColumn('materials', 'location_name')) {
                $table->string('location_name')->nullable()->after('unit');
            }
            if (!Schema::hasColumn('materials', 'max_stock')) {
                $table->integer('max_stock')->nullable()->default(0)->after('min_stock');
            }
        });

        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'party_name')) {
                $table->string('party_name')->nullable()->after('user_id');
            }
            if (!Schema::hasColumn('transactions', 'unit_price')) {
                $table->decimal('unit_price', 15, 2)->nullable()->after('quantity');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('materials', function (Blueprint $table) {
            $table->dropColumn(['location_name', 'max_stock']);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn(['party_name', 'unit_price']);
        });
    }
};
