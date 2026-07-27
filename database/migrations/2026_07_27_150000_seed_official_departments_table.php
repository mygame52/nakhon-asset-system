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
        $officialGroups = [
            'ADM-01'  => 'กลุ่มอำนวยการ',
            'STR-02'  => 'กลุ่มยุทธศาสตร์และการพัฒนา',
            'EDU-03'  => 'กลุ่มส่งเสริมการเรียนรู้เพื่อคุณวุฒิตามระดับ',
            'SELF-04' => 'กลุ่มส่งเสริมการเรียนรู้เพื่อการพัฒนาตนเอง',
            'LIFE-05' => 'กลุ่มส่งเสริมการเรียนรู้ตลอดชีวิต',
            'SUP-06'  => 'กลุ่มนิเทศ ติดตามและประเมินผลการจัดการศึกษา',
        ];

        foreach ($officialGroups as $code => $name) {
            $existing = DB::table('departments')->where('code', $code)->first();
            if (!$existing) {
                // Check if name exists without code
                $nameMatch = DB::table('departments')->where('name', $name)->first();
                if ($nameMatch) {
                    DB::table('departments')->where('id', $nameMatch->id)->update([
                        'code' => $code,
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('departments')->insert([
                        'code' => $code,
                        'name' => $name,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Keep department records intact to prevent data loss
    }
};
