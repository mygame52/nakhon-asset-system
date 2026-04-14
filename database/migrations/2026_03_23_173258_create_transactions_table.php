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
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->enum('transaction_type', ['in', 'out', 'repair', 'write_off']);
            $table->enum('item_type', ['asset', 'material']);
            $table->unsignedBigInteger('item_id');
            $table->integer('quantity')->default(1);
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('reference_doc')->nullable();
            $table->text('note')->nullable();
            $table->date('transaction_date');
            $table->string('status')->default('completed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
