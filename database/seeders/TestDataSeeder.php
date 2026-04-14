<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Asset;
use App\Models\Material;
use App\Models\Category;
use App\Models\Location;
use App\Models\Department;
use App\Models\Vendor;
use Illuminate\Support\Str;

class TestDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or Create required relations
        $categoryAsset = Category::where('type', 'asset')->first() ?? Category::create(['name' => 'ครุภัณฑ์คอมพิวเตอร์', 'type' => 'asset']);
        $categoryMaterial = Category::where('type', 'material')->first() ?? Category::create(['name' => 'วัสดุสำนักงาน', 'type' => 'material']);
        
        $location = Location::first() ?? Location::create(['name' => 'ห้องทำงานพัสดุ', 'room_number' => '101']);
        $department = Department::first() ?? Department::create(['name' => 'กลุ่มงานการเงินและพัสดุ']);
        $vendor = Vendor::first() ?? Vendor::create(['name' => 'บริษัท นครคอมพิวเตอร์ จำกัด', 'contact_person' => 'คุณมานะ', 'phone' => '075-123456']);

        // Generate 10 Assets
        for ($i = 1; $i <= 10; $i++) {
            Asset::create([
                'asset_code' => '7440-001-' . str_pad($i, 4, '0', STR_PAD_LEFT) . '/2566',
                'name' => 'เครื่องคอมพิวเตอร์แบบตั้งโต๊ะ ชุดที่ ' . $i,
                'category_id' => $categoryAsset->id,
                'model' => 'Dell Optiplex 7010',
                'specs' => 'Core i7, RAM 16GB, SSD 512GB, จอ 24 นิ้ว',
                'unit_price' => rand(22000, 28000),
                'purchase_date' => now()->subDays(rand(10, 500)),
                'location_id' => $location->id,
                'department_id' => $department->id,
                'vendor_id' => $vendor->id,
                'status' => 'ใช้งานปกติ',
                'acquisition_type' => 'ซื้อ',
            ]);
        }

        // Generate 10 Materials
        $materialData = [
            ['name' => 'กระดาษ A4 80 แกรม (Double A)', 'unit' => 'รีม'],
            ['name' => 'ปากกาลูกลื่น สีน้ำเงิน (Linc)', 'unit' => 'ด้าม'],
            ['name' => 'ลวดเย็บกระดาษ No.10', 'unit' => 'กล่อง'],
            ['name' => 'แฟ้มเสนอเซ็นหนังเทียม', 'unit' => 'เล่ม'],
            ['name' => 'เทปใส 1 นิ้ว', 'unit' => 'ม้วน'],
            ['name' => 'กาวแท่ง (UHU)', 'unit' => 'แท่ง'],
            ['name' => 'ซองจดหมายขาว (50 ซอง/แพ็ค)', 'unit' => 'แพ็ค'],
            ['name' => 'ถ่านไฟฉาย AA (Panasonic)', 'unit' => 'แพ็ค'],
            ['name' => 'น้ำยาลบคำผิด (Pentel)', 'unit' => 'ขวด'],
            ['name' => 'คลิปหนีบกระดาษ 32 มม.', 'unit' => 'กล่อง'],
        ];

        foreach ($materialData as $item) {
            Material::create([
                'name' => $item['name'],
                'category_id' => $categoryMaterial->id,
                'unit' => $item['unit'],
                'balance' => rand(10, 100),
                'unit_price' => rand(15, 350),
            ]);
        }
    }
}
