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
        Schema::table('transactions', function (Blueprint $table) {
            if (!Schema::hasColumn('transactions', 'deleted_at')) {
                $table->softDeletes();
            }
            if (!Schema::hasColumn('transactions', 'deleted_by')) {
                $table->foreignId('deleted_by')->nullable()->after('deleted_at')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'delete_reason')) {
                $table->text('delete_reason')->nullable()->after('deleted_by');
            }
            if (!Schema::hasColumn('transactions', 'edited_by')) {
                $table->foreignId('edited_by')->nullable()->after('delete_reason')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('transactions', 'edited_at')) {
                $table->timestamp('edited_at')->nullable()->after('edited_by');
            }
            if (!Schema::hasColumn('transactions', 'edit_reason')) {
                $table->text('edit_reason')->nullable()->after('edited_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transactions', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn(['deleted_by', 'delete_reason', 'edited_by', 'edited_at', 'edit_reason']);
        });
    }
};
