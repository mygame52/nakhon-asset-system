<?php

namespace App\Imports;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Department;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AssetImport implements ToCollection, WithStartRow
{
    /**
     * Start reading from row 2 to ignore the header array
     */
    public function startRow(): int
    {
        return 2;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $row) {
            // Index Mapping based on พด. 1 template:
            // 0: วัน เดือน ปี (Date)
            // 1: รหัสทะเบียน (Registration Code)
            // 2: ยี่ห้อ (Brand)
            // 3: ชนิด/เครื่อง/ขนาด/ตัวถัง (Name/Type/Specs)
            // 4: ราคา (Price)
            // 5: วิธีการได้มา (Acquisition Type)
            // 6: ผู้เบิกไปใช้/เลขที่รับ (Department/User)
            // 7: สถานที่ (Location)
            // 8: สถานภาพ (Status)

            $purchaseDateRaw = $row[0] ?? null;
            $assetCode = $row[1] ?? null;
            $model = $row[2] ?? null;
            $name = $row[3] ?? null;
            $unitPriceRaw = $row[4] ?? 0;
            $acquisitionType = $row[5] ?? null;
            $departmentName = $row[6] ?? null;
            $locationName = $row[7] ?? null;
            $status = $row[8] ?? 'ใช้งาน';

            // Skip rows that don't have basic required info
            if (empty($assetCode) || empty($name)) {
                continue;
            }

            $purchaseDate = null;
            $rawDate = trim($purchaseDateRaw);
            if (!empty($rawDate) && $rawDate !== '-') {
                try {
                    if (is_numeric($rawDate)) {
                        $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate);
                        if ((int)$dateObj->format('Y') > 2500) {
                            $dateObj->modify('-543 years');
                        }
                        $purchaseDate = $dateObj->format('Y-m-d');
                    } else {
                        preg_match_all('/\d+/', $rawDate, $matches);
                        $numbers = $matches[0];
                        if (count($numbers) > 0) {
                            $day = 1; $month = 1; $year = 0;
                            if (count($numbers) >= 3) {
                                if ((int)$numbers[0] > 1000) {
                                    $year = (int)$numbers[0];
                                    $month = (int)$numbers[1];
                                    $day = (int)$numbers[2];
                                } else {
                                    $day = (int)$numbers[0];
                                    $month = (int)$numbers[1];
                                    $year = (int)$numbers[2];
                                }
                            } elseif (count($numbers) == 2) {
                                $month = (int)$numbers[0];
                                $year = (int)$numbers[1];
                            } else {
                                $year = (int)$numbers[0];
                            }

                            if ($day < 1 || $day > 31) $day = 1;
                            if ($month < 1 || $month > 12) $month = 1;
                            
                            if ($year > 0 && $year < 100) {
                                $year += ($year >= 50) ? 2500 : 2000;
                            }
                            if ($year > 2400) {
                                $year -= 543;
                            }

                            if ($year > 1900 && $year < 2200) {
                                $purchaseDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $purchaseDate = null;
                }
            }

            // Resolve Relationships Dynamically
            $locationId = null;
            if (!empty($locationName) && $locationName !== '-') {
                $loc = Location::firstOrCreate(['name' => trim($locationName)]);
                $locationId = $loc->id;
            }

            $departmentId = null;
            if (!empty($departmentName) && $departmentName !== '-') {
                $dept = Department::firstOrCreate(['name' => trim($departmentName)]);
                $departmentId = $dept->id;
            }

            // Sync with database: Handle soft deletes to prevent UNIQUE constraint violations
            $asset = Asset::withTrashed()->firstOrNew(['asset_code' => $assetCode]);
            
            $asset->fill([
                'name'             => $name,
                'model'            => $model,
                'purchase_date'    => $purchaseDate,
                'acquisition_type' => $acquisitionType,
                'location_id'      => $locationId,
                'department_id'    => $departmentId,
                'unit_price'       => is_numeric($unitPriceRaw) ? floatval($unitPriceRaw) : 0,
                'status'           => $status,
            ]);

            // Restore the asset if it was previously soft-deleted
            if ($asset->trashed()) {
                $asset->restore();
            }

            $asset->save();
        }
    }
}
