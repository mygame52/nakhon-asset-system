<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetTemplateExport implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    public function array(): array
    {
        return [
            [
                '01/10/2566',
                '1234-567-8901',
                'คอมพิวเตอร์',
                'Dell Optiplex 7000',
                'Core i7, RAM 16GB, SSD 512GB',
                '25000',
                'ตกลงราคา',
                'งานบริหารทั่วไป',
                'ห้องคอมพิวเตอร์ 1',
                'ใช้งานปกติ'
            ]
        ];
    }

    public function headings(): array
    {
        return [
            'วัน เดือน ปี',
            'รหัสทะเบียน',
            'ประเภทครุภัณฑ์',
            'ยี่ห้อ',
            'ชนิด/เครื่อง/ขนาด/ตัวถัง',
            'ราคา',
            'วิธีการได้มา',
            'ผู้เบิกไปใช้/เลขที่รับ',
            'สถานที่',
            'สถานภาพ'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
