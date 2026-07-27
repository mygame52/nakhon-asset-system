<?php

namespace App\Exports;

use App\Models\Asset;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class AssetExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected array $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    public function collection()
    {
        return Asset::with(['category', 'location', 'department'])
            ->when($this->filters['search'] ?? null, function ($query, $search) {
                $query->where(function($q) use ($search) {
                    $q->where('asset_code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhereHas('category', function($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($this->filters['category_id'] ?? null, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($this->filters['location_id'] ?? null, function ($query, $locationId) {
                $query->where('location_id', $locationId);
            })
            ->when($this->filters['department_id'] ?? null, function ($query, $departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($this->filters['status'] ?? null, function ($query, $status) {
                $query->where('status', $status);
            })
            ->latest()
            ->get();
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

    public function map($asset): array
    {
        return [
            $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->addYears(543)->format('d/m/Y') : '-',
            $asset->asset_code,
            $asset->category->name ?? '-',
            $asset->model ?? '-',
            $asset->name,
            $asset->unit_price,
            $asset->acquisition_type ?? '-',
            $asset->department->name ?? '-',
            $asset->location->name ?? '-',
            $asset->status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1    => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4A148C']]],
        ];
    }
}
