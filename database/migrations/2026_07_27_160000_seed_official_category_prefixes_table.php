<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $categories = [
            'วัสดุสำนักงาน' => '5510',
            'วัสดุคอมพิวเตอร์' => '5520',
            'วัสดุไฟฟ้าและวิทยุ' => '5530',
            'วัสดุงานบ้านงานครัว' => '5540',
            'วัสดุยานพาหนะและขนส่ง' => '5550',
            'วัสดุการเกษตร' => '5560',
            'วัสดุก่อสร้าง' => '5570',
            'วัสดุเชื้อเพลิงและหล่อลื่น' => '5580',
            'วัสดุโฆษณาและเผยแพร่' => '5590',
        ];

        foreach ($categories as $name => $prefix) {
            DB::table('categories')
                ->where('name', 'LIKE', "%{$name}%")
                ->whereNull('code_prefix')
                ->update(['code_prefix' => $prefix, 'updated_at' => now()]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep data intact
    }
};
