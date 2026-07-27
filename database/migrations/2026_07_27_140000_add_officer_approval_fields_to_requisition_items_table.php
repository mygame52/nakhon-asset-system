<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requisition_items', function (Blueprint $table) {
            $table->foreignId('officer_approved_by')->nullable()->after('status')->constrained('users')->onDelete('set null');
            $table->timestamp('officer_approved_at')->nullable()->after('officer_approved_by');
            $table->text('officer_note')->nullable()->after('officer_approved_at');
        });
    }

    public function down(): void
    {
        Schema::table('requisition_items', function (Blueprint $table) {
            $table->dropForeign(['officer_approved_by']);
            $table->dropColumn(['officer_approved_by', 'officer_approved_at', 'officer_note']);
        });
    }
};
