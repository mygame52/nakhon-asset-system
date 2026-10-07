@extends('layouts.app')

@section('page_title', 'เพิ่มรายการวัสดุใหม่')
@section('page_description', 'ลงทะเบียนเปิดบัญชีคุมพัสดุและระบุเกณฑ์การจัดเก็บตามระเบียบกระทรวงการคลัง')

@section('content')
<div class="w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6" x-data="{
    name: '{{ old('name') }}',
    unit: '{{ old('unit') }}',
    specs: '{{ old('specs') }}',
    aiGenerating: false,
    isSubmitting: false,
    generateAiSpecs() {
        if (!this.name.trim()) {
            alert('กรุณากรอกชื่อรายการวัสดุก่อนใช้งาน AI แนะนำสเปก');
            return;
        }
        this.aiGenerating = true;
        
        fetch('{{ route('materials.generate-ai-specs') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ name: this.name, unit: this.unit })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success && data.specs) {
                this.specs = data.specs;
                if (data.unit && !this.unit) {
                    this.unit = data.unit;
                }
            } else {
                let fallback = getSmartMaterialSpecs(this.name, this.unit);
                this.specs = fallback.specs;
                if (fallback.unit && !this.unit) this.unit = fallback.unit;
            }
            this.aiGenerating = false;
        })
        .catch(err => {
            console.warn('AI spec fetch failed, using smart engine fallback:', err);
            let fallback = getSmartMaterialSpecs(this.name, this.unit);
            this.specs = fallback.specs;
            if (fallback.unit && !this.unit) this.unit = fallback.unit;
            this.aiGenerating = false;
        });
    }
}">
    <!-- Header Title Bar -->
    <div class="border-b border-gray-100 pb-4 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-700 to-indigo-600 flex items-center">
                <i class="fas fa-box-open mr-3 text-purple-500"></i> ลงทะเบียนรายการวัสดุใหม่
            </h2>
            <p class="text-sm text-gray-500 mt-2">กรอกรายละเอียดเพื่อเปิดสมุดบัญชีคุมรับ-จ่าย-คงเหลือวัสดุประจำปีงบประมาณ</p>
        </div>
        <a href="{{ route('materials.index') }}" class="text-gray-500 hover:text-purple-600 transition-colors bg-gray-50 hover:bg-purple-50 px-4 py-2 rounded-xl text-sm font-semibold flex items-center border border-gray-200">
            <i class="fas fa-arrow-left mr-2"></i> ย้อนกลับ
        </a>
    </div>

    @if ($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
            <div class="flex items-center font-bold mb-2">
                <i class="fas fa-exclamation-circle mr-2"></i> ข้อผิดพลาดในการกรอกข้อมูล:
            </div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('materials.store') }}" method="POST" class="space-y-6" @submit="if(isSubmitting) { $event.preventDefault(); return false; } isSubmitting = true;">
        @csrf

        <!-- Section 1: ข้อมูลทั่วไปประจำรายการ -->
        <div>
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                1. ข้อมูลกำกับพัสดุและหมวดหมู่
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Category -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">หมวดหมู่วัสดุ <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-tags text-gray-400"></i>
                        </div>
                        <select name="category_id" required class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none appearance-none text-sm font-medium">
                            <option value="">-- เลือกหมวดหมู่วัสดุ --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                    [{{ $category->code_prefix ?? '5510' }}] {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Material Code -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">รหัสพัสดุ (Material Code)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-barcode text-gray-400"></i>
                        </div>
                        <input type="text" name="material_code" value="{{ old('material_code') }}" placeholder="เช่น 5510-001 (สร้างให้อัตโนมัติ)" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-mono font-bold text-purple-700">
                    </div>
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">หน่วยที่นับ <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-cube text-gray-400"></i>
                        </div>
                        <input type="text" name="unit" x-model="unit" required placeholder="เช่น รีม, ด้าม, เล่ม, กล่อง" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-semibold">
                    </div>
                </div>
            </div>

            <!-- Material Name -->
            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">ชื่อหรือชนิดวัสดุ <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-pen-alt text-gray-400"></i>
                    </div>
                    <input type="text" name="name" x-model="name" @keyup.debounce.400ms="if(!specs) generateAiSpecs()" required placeholder="ตัวอย่าง: สมุดลงเวลา หรือ กรรไกร 8 นิ้ว" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-gray-900">
                </div>
            </div>

            <!-- Specs & AI Specs Button -->
            <div class="mt-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-gray-700">ขนาดหรือลักษณะ / สเปกรายละเอียด</label>
                    <button type="button" @click="generateAiSpecs()" class="px-3.5 py-1.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition-all flex items-center gap-1.5 active:scale-95">
                        <span x-show="!aiGenerating" class="flex items-center"><i class="fas fa-magic mr-1 text-amber-300"></i> ✨ AI แนะนำสเปกอัตโนมัติ (Gemini)</span>
                        <span x-show="aiGenerating" style="display:none;" class="flex items-center"><i class="fas fa-spinner fa-spin mr-1"></i> กำลังวิเคราะห์ข้อมูลสเปก...</span>
                    </button>
                </div>
                <textarea name="specs" x-model="specs" rows="3" placeholder="ระบุขนาด สเปกรายละเอียด หรือกดปุ่ม ✨ AI แนะนำสเปกอัตโนมัติ (สามารถแก้ไขเพิ่มเติมได้อิสระ)..." class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm leading-relaxed resize-none"></textarea>
                <p class="text-xs text-gray-400 mt-1">💡 สเปกเบื้องต้นถูกสร้างให้อัตโนมัติจากชื่อวัสดุ คุณสามารถพิมพ์แก้ไข เพิ่มเติม หรือลบได้ตามต้องการ</p>
            </div>
        </div>

        <!-- Section 2: การคุมคลังและราคาสต็อก -->
        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                2. การคุมคลัง ราคา และเกณฑ์เก็บสำรอง (Min/Max)
            </h3>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <!-- Location Name -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">สถานที่จัดเก็บเริ่มต้น</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                        </div>
                        <input type="text" name="location_name" value="{{ old('location_name', 'ห้องพัสดุ') }}" placeholder="เช่น ห้องพัสดุ หรือ ตู้ 1 ชั้น 2" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-semibold text-purple-900">
                    </div>
                </div>

                <!-- Unit Price -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">ราคาต่อหน่วย (บาท รวม VAT)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-baht-sign text-gray-400"></i>
                        </div>
                        <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price') }}" placeholder="0.00" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-right">
                    </div>
                </div>

                <!-- Min Stock -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">จำนวนอย่างต่ำ <span class="text-gray-400 font-normal">(ไม่บังคับ)</span></label>
                    <input type="number" name="min_stock" value="{{ old('min_stock') }}" min="0" placeholder="0" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-center">
                </div>

                <!-- Max Stock -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">จำนวนอย่างสูง <span class="text-gray-400 font-normal">(ไม่บังคับ)</span></label>
                    <input type="number" name="max_stock" value="{{ old('max_stock') }}" min="0" placeholder="0" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-center">
                </div>
            </div>

            <div class="mt-6 p-6 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl shadow-sm space-y-4" x-data="{ openingChoice: 'default', customOpeningDate: '' }">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                    <div>
                        <label class="block text-sm font-extrabold text-emerald-900 uppercase tracking-wider">
                            ยอดคงเหลือยกมาเริ่มต้น (Opening Balance) <span class="text-red-500">*</span>
                        </label>
                        <p class="text-xs text-emerald-700 mt-1 font-medium">
                            ระบุยอดคงเหลือยกมาจากปีก่อนหน้าตามระเบียบพัสดุ (ระบบจะตั้งต้นเป็นวันที่ 1 ต.ค. ให้อัตโนมัติ)
                        </p>
                    </div>
                    <div class="w-full md:w-48">
                        <input type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" min="0" required class="w-full px-4 py-3 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-xl font-extrabold text-center text-emerald-900 outline-none shadow-sm">
                    </div>
                </div>

                <!-- Fiscal Year of Opening Balance Selection -->
                <div class="pt-4 border-t border-emerald-200/60 grid grid-cols-1 md:grid-cols-2 gap-4 items-center">
                    <div>
                        <label class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-1">
                            <i class="fas fa-calendar-alt mr-1 text-emerald-600"></i> ปีงบประมาณที่เริ่มต้นยอดยกมา
                        </label>
                        <select x-model="openingChoice" class="w-full px-3 py-2.5 bg-white border border-emerald-300 rounded-xl text-xs font-bold text-emerald-900 focus:ring-2 focus:ring-emerald-500 outline-none shadow-sm">
                            <option value="default">ปีงบประมาณ 2570 (ยอดยกมา ณ 1 ต.ค. 2569 - ปีปัจจุบัน)</option>
                            <option value="2569">ปีงบประมาณ 2569 (ยอดยกมา ณ 1 ต.ค. 2568 - ย้อนหลัง 1 ปี)</option>
                            <option value="2568">ปีงบประมาณ 2568 (ยอดยกมา ณ 1 ต.ค. 2567 - ย้อนหลัง 2 ปี)</option>
                            <option value="2567">ปีงบประมาณ 2567 (ยอดยกมา ณ 1 ต.ค. 2566 - ย้อนหลัง 3 ปี)</option>
                            <option value="custom">กำหนดวันที่ยอดยกมาเอง...</option>
                        </select>
                    </div>

                    <!-- Hidden/Calculated or Custom Date Input -->
                    <div>
                        <template x-if="openingChoice === 'custom'">
                            <div>
                                <label class="block text-xs font-bold text-emerald-900 uppercase tracking-wider mb-1">
                                    ระบุวันที่ยอดยกมาเอง
                                </label>
                                <input type="date" name="opening_balance_date" x-model="customOpeningDate" class="w-full px-3 py-2 bg-white border border-emerald-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-emerald-500 outline-none">
                            </div>
                        </template>
                        <template x-if="openingChoice !== 'custom'">
                            <input type="hidden" name="opening_balance_date" :value="
                                openingChoice === '2569' ? '2025-09-30' :
                                openingChoice === '2568' ? '2024-09-30' :
                                openingChoice === '2567' ? '2023-09-30' : '2026-09-30'
                            ">
                        </template>
                        <p class="text-[11px] text-emerald-700 mt-1 italic" x-show="openingChoice !== 'custom'">
                            * ระบบจะนำยอดนี้ไปแสดงที่แถวที่ 1 (ยอดยกมาจากปีงบประมาณก่อนหน้า) โดยอัตโนมัติ
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('materials.index') }}" class="px-6 py-3 bg-gray-100 text-gray-600 rounded-xl border border-gray-200 hover:bg-gray-200 transition-colors font-semibold text-sm">
                ยกเลิก
            </a>
            <button type="submit" :disabled="isSubmitting" :class="isSubmitting ? 'opacity-70 cursor-not-allowed' : ''" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-purple-500/30 transition-all active:scale-95 flex items-center">
                <span x-show="!isSubmitting" class="flex items-center">
                    <i class="fas fa-save mr-2"></i> บันทึกข้อมูลและเปิดสมุดบัญชี
                </span>
                <span x-show="isSubmitting" class="flex items-center" style="display: none;">
                    <i class="fas fa-spinner fa-spin mr-2"></i> กำลังบันทึกข้อมูล...
                </span>
            </button>
        </div>
    </form>
</div>

<script>
function getSmartMaterialSpecs(materialName, currentUnit) {
    let name = (materialName || '').trim();
    if (!name) return { specs: '', unit: currentUnit };

    let n = name.toLowerCase();
    
    // Dynamic Size Extractions
    let inchMatch = name.match(/(\d+(?:\.\d+)?)\s*(?:นิ้ว|")/i);
    let sizeInch = inchMatch ? inchMatch[1] + ' นิ้ว' : null;

    let gsmMatch = name.match(/(\d+)\s*แกรม/i);
    let sizeGsm = gsmMatch ? gsmMatch[1] + ' แกรม' : null;

    let mmMatch = name.match(/(\d+(?:\.\d+)?)\s*มม\.?/i);
    let sizeMm = mmMatch ? mmMatch[1] + ' มม.' : null;

    let mlMatch = name.match(/(\d+)\s*(?:มล\.?|มิลลิลิตร|ml)/i);
    let sizeMl = mlMatch ? mlMatch[1] + ' มล.' : null;

    let resultSpecs = '';
    let autoUnit = currentUnit || '';

    // 1. สมุดทุกชนิด
    if (n.includes('สมุด')) {
        if (n.includes('ลงเวลา') || n.includes('ลงนาม')) {
            resultSpecs = 'ขนาด A4 (210 x 297 มม.) ปกแข็งหุ้มอย่างดี สมุดลงเวลาปฏิบัติราชการของข้าราชการและบุคลากร ด้านในมีตารางระบุวันที่ ชื่อ-นามสกุล เวลามา-เวลากลับ และช่องลงลายมือชื่อ';
            if (!autoUnit) autoUnit = 'เล่ม';
        } else if (n.includes('ส่งหนังสือ') || n.includes('รับหนังสือ') || n.includes('ทะเบียน')) {
            resultSpecs = 'ขนาด 210 x 330 มม. ปกแข็งอย่างดี พิมพ์ตารางลงทะเบียนรับ-ส่งหนังสือราชการคมชัด กระดาษปอนด์ 70 แกรม บรรจุ 80 แผ่น/เล่ม';
            if (!autoUnit) autoUnit = 'เล่ม';
        } else if (n.includes('เบิกพัสดุ') || n.includes('คุมพัสดุ') || n.includes('บัญชีพัสดุ')) {
            resultSpecs = 'ขนาด 210 x 330 มม. ปกแข็งหุ้มอย่างดี พิมพ์ตารางคุมรับ-จ่าย-คงเหลือพัสดุตามระเบียบกระทรวงการคลัง บรรจุ 100 แผ่น/เล่ม';
            if (!autoUnit) autoUnit = 'เล่ม';
        } else if (n.includes('บัญชี') || n.includes('เงินสด') || n.includes('รายวัน')) {
            resultSpecs = 'ขนาด A4 ปกแข็งอย่างดี พิมพ์ตารางบัญชีการเงินคมชัด กระดาษปอนด์ 70 แกรม บรรจุ 100 แผ่น/เล่ม';
            if (!autoUnit) autoUnit = 'เล่ม';
        } else {
            resultSpecs = 'ขนาด A4 (210 x 297 มม.) ปกแข็งอย่างดี เนื้อกระดาษปอนด์ขาว 70 แกรม พิมพ์เส้นบรรทัดชัดเจน บรรจุ 80 แผ่น/เล่ม';
            if (!autoUnit) autoUnit = 'เล่ม';
        }
    }
    // 2. กรรไกร
    else if (n.includes('กรรไกร')) {
        let sz = sizeInch || '8 นิ้ว';
        resultSpecs = `ขนาด ${sz} ใบมีดสแตนเลสคุณภาพสูง คมทนทาน ด้ามจับหุ้มยางนุ่มกระชับมือ ตัดง่าย เหมาะสำหรับงานตัดกระดาษและเอกสารสำนักงาน`;
        if (!autoUnit) autoUnit = 'เล่ม';
    }
    // 3. ปากกา
    else if (n.includes('ปากกา')) {
        let tip = sizeMm || '0.5 มม.';
        if (n.includes('เน้นข้อความ') || n.includes('ไฮไลท์')) {
            resultSpecs = 'หัวหมึกชนิดตัด ขนาดเส้น 2-5 มม. หมึกสีสดใส ไม่ซีดจาง ไม่ไร้รอยซึมหลังกระดาษ';
            if (!autoUnit) autoUnit = 'ด้าม';
        } else if (n.includes('ไวท์บอร์ด')) {
            resultSpecs = 'หัวลบได้ กลิ่นไม่ฉุน เขียนลื่น ลบง่าย ไม่ทิ้งคราบสกปรกบนกระดานไวท์บอร์ด';
            if (!autoUnit) autoUnit = 'ด้าม';
        } else if (n.includes('เคมี') || n.includes('เมจิก')) {
            resultSpecs = 'หมึกกันน้ำ กลิ่นไม่ฉุน เขียนได้บนทุกพื้นผิว แห้งเร็ว สีเข้มคมชัด';
            if (!autoUnit) autoUnit = 'ด้าม';
        } else {
            resultSpecs = `ขนาดหัวเขียน ${tip} หมึกไหลสม่ำเสมอ เขียนลื่น แห้งไว ไม่เลอะเทอะ ด้ามจับกระชับมือ`;
            if (!autoUnit) autoUnit = 'ด้าม';
        }
    }
    // 4. ดินสอ / ยางลบ
    else if (n.includes('ดินสอ')) {
        if (n.includes('กด')) {
            resultSpecs = `ขนาดไส้ ${sizeMm || '0.5 มม.'} ด้ามจับกระชับมือ มีคลิปหนีบ ปลายหัวเป็นเหล็กแข็งแรง`;
            if (!autoUnit) autoUnit = 'ด้าม';
        } else {
            resultSpecs = 'ความเข้มไส้ดินสอ 2B เหลาง่าย ไส้ไม่แตกหักง่าย เหมาะสำหรับทำข้อสอบและเขียนทั่วไป';
            if (!autoUnit) autoUnit = 'แท่ง';
        }
    } else if (n.includes('ยางลบ')) {
        resultSpecs = 'ทำจากพลาสติก PVC คุณภาพดี ลบสะอาด ไม่ทำลายเนื้อกระดาษ ขยะยางลบเกาะตัวเป็นก้อน';
        if (!autoUnit) autoUnit = 'ก้อน';
    }
    // 5. คัตเตอร์
    else if (n.includes('คัตเตอร์')) {
        if (n.includes('ใบมีด')) {
            resultSpecs = `ขนาด ${sizeMm || '18 มม.'} ทำจากเหล็กกล้าเกรดพรีเมียม ทำมุม 45 องศา คมทนทาน บรรจุ 10 ใบ/กล่อง`;
            if (!autoUnit) autoUnit = 'กล่อง';
        } else {
            resultSpecs = 'ขนาดใหญ่ ด้ามสแตนเลสหุ้มพลาสติกแข็ง มีระบบล็อกใบมีดอัตโนมัติ ใบมีดทำจากเหล็กกล้าคมทนทาน';
            if (!autoUnit) autoUnit = 'ด้าม';
        }
    }
    // 6. กระดาษ
    else if (n.includes('กระดาษ')) {
        let gsm = sizeGsm || (n.includes('80') ? '80 แกรม' : '70 แกรม');
        if (n.includes('a4') || n.includes('เอ4')) {
            resultSpecs = `ขนาด 210 x 297 มม. (A4) หนา ${gsm} บรรจุ 500 แผ่น/รีม เนื้อกระดาษขาวเรียบลื่น ถนอมสายตา พิมพ์ได้ 2 หน้า มาตรฐานทางราชการ`;
            if (!autoUnit) autoUnit = 'รีม';
        } elseif (n.includes('โพสต์อิท') || n.includes('โน้ต')) {
            resultSpecs = 'ขนาด 3 x 3 นิ้ว แถบกาวติดแน่น ลอกออกได้ไม่ทิ้งคราบกาว บรรจุ 100 แผ่น/เล่ม';
            if (!autoUnit) autoUnit = 'เล่ม';
        } else {
            resultSpecs = `เนื้อกระดาษคุณภาพดี หนา ${gsm} เหมาะสำหรับงานพิมพ์เอกสารและใช้งานในสำนักงาน`;
            if (!autoUnit) autoUnit = 'รีม';
        }
    }
    // 7. แฟ้มเอกสาร
    else if (n.includes('แฟ้ม')) {
        let sp = sizeInch || '2 นิ้ว';
        if (n.includes('ห่วง') || n.includes('สันหนา')) {
            resultSpecs = `ขนาด A4 สันกว้าง ${sp} ปกแข็งหุ้มตราช้าง/พีวีซี คลิปเหล็กแข็งแรง ล็อกแน่น สันแฟ้มมีป้ายชื่อ`;
            if (!autoUnit) autoUnit = 'เล่ม';
        } else {
            resultSpecs = `ขนาด A4 สันกว้าง ${sp} ปกแข็งอย่างดี ถนอมเอกสารได้ดีเยี่ยม`;
            if (!autoUnit) autoUnit = 'เล่ม';
        }
    }
    // 8. เทป / กาว
    else if (n.includes('เทป')) {
        let width = sizeInch || '1 นิ้ว';
        resultSpecs = `แกน 3 นิ้ว หน้ากว้าง ${width} ความยาว 36 หลา กาวอะคริลิกเหนียวแน่น ไม่เหลืองกรอบ`;
        if (!autoUnit) autoUnit = 'ม้วน';
    }
    // 9. ซองเอกสาร
    else if (n.includes('ซอง')) {
        if (n.includes('น้ำตาล') || n.includes('ขยายข้าง') || n.includes('a4')) {
            resultSpecs = 'ขนาด 9 x 12 นิ้ว (A4) กระดาษคราฟท์สีน้ำตาลอย่างหนา 110 แกรม พิมพ์ตราครุฑทางการ แถบกาวติดแน่น';
            if (!autoUnit) autoUnit = 'ซอง';
        } else {
            resultSpecs = 'ขนาด 4.5 x 7 นิ้ว กระดาษปอนด์ขาวคุณภาพดี พิมพ์ตราครุฑทางการ บรรจุ 50 ซอง/แพ็ค';
            if (!autoUnit) autoUnit = 'แพ็ค';
        }
    }
    // 10. ตรายาง / ประทับ
    else if (n.includes('ตรายาง') || n.includes('ตราปั๊ม')) {
        resultSpecs = 'ทำจากยางพาราคุณภาพดี ตัวอักษรคมชัด ด้ามจับพลาสติกแข็งทนทาน ทนต่อแรงกดใช้งาน';
        if (!autoUnit) autoUnit = 'อัน';
    } else if (n.includes('ประทับ') || n.includes('ตลับชาด')) {
        resultSpecs = 'เบอร์ 2 (7 x 11 ซม.) ตลับโลหะแข็งแรง หมึกสีน้ำเงิน/แดง คมชัด แห้งเร็ว ไม่ซึมเลอะเทอะ';
        if (!autoUnit) autoUnit = 'ตลับ';
    }
    // 11. หมึกพิมพ์
    else if (n.includes('หมึก') || n.includes('toner')) {
        resultSpecs = 'ตลับผงหมึกพิมพ์เลเซอร์คุณภาพสูง ให้งานพิมพ์สีดำเข้ม คมชัด พิมพ์ได้ประมาณ 1,500-2,000 หน้า';
        if (!autoUnit) autoUnit = 'ตลับ';
    }
    // 12. ถ่านไฟฉาย
    else if (n.includes('ถ่าน') || n.includes('แบตเตอรี่')) {
        let type = n.includes('aaa') ? 'AAA' : (n.includes('aa') ? 'AA' : 'AA/AAA');
        resultSpecs = `ขนาด ${type} กำลังไฟ 1.5V อัลคาไลน์ ให้พลังงานยาวนาน ปราศจากสารปรอทและแคดเมียม บรรจุ 4 ก้อน/แพ็ค`;
        if (!autoUnit) autoUnit = 'แพ็ค';
    }
    // 13. โต๊ะ / เก้าอี้ / ตู้
    else if (n.includes('เก้าอี้')) {
        resultSpecs = 'พนักพิงและเบาะนั่งบุฟองน้ำฉีดขึ้นรูป หุ้มผ้า/หนัง พีวีซี ขาเหล็กชุบโครเมี่ยมพร้อมล้อเลื่อน แข็งแรงทนทาน';
        if (!autoUnit) autoUnit = 'ตัว';
    } else if (n.includes('โต๊ะ')) {
        resultSpecs = 'โครงสร้างเหล็ก/ไม้เนื้อแข็ง แข็งแรงทนทาน หน้าโต๊ะปิดผิวลามิเนตกันน้ำและรอยขีดข่วน มีลิ้นชักล็อกได้';
        if (!autoUnit) autoUnit = 'ตัว';
    } else if (n.includes('ตู้')) {
        resultSpecs = 'โครงสร้างเหล็กพ่นสีกันสนิมอย่างดี มือจับแบบกดล็อก แข็งแรง ทนทาน ได้มาตรฐานงานพัสดุราชการ';
        if (!autoUnit) autoUnit = 'ตู้';
    } else {
        resultSpecs = 'ขนาดมาตรฐาน ผลิตจากวัสดุคุณภาพดี ได้มาตรฐาน เหมาะสำหรับงานพัสดุสำนักงานและใช้งานทั่วไป';
    }

    return { specs: resultSpecs, unit: autoUnit };
}
</script>
@endsection
