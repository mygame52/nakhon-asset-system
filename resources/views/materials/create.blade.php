@extends('layouts.app')

@section('page_title', 'เพิ่มรายการวัสดุใหม่')
@section('page_description', 'ลงทะเบียนเปิดบัญชีคุมพัสดุและระบุเกณฑ์การจัดเก็บตามระเบียบกระทรวงการคลัง')

@section('content')
<div class="w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6" x-data="{
    name: '{{ old('name') }}',
    unit: '{{ old('unit') }}',
    specs: '{{ old('specs') }}',
    aiGenerating: false,
    generateAiSpecs() {
        if (!this.name.trim()) {
            alert('กรุณากรอกชื่อรายการวัสดุก่อนใช้งาน AI แนะนำสเปก');
            return;
        }
        this.aiGenerating = true;
        setTimeout(() => {
            let n = this.name.toLowerCase();
            if (n.includes('กระดาษ') && n.includes('a4')) {
                this.specs = 'ขนาด 210 x 297 มม. หนา 80 แกรม ถนอมสายตา บรรจุ 500 แผ่น/รีม มาตรฐานทางราชการ';
                if (!this.unit) this.unit = 'รีม';
            } else if (n.includes('ปากกา')) {
                this.specs = 'ขนาดหัวเขียน 0.5 มม. หมึกไหลสม่ำเสมอ เขียนลื่น แห้งไว ไม่เลอะเทอะ ด้ามจับกระชับมือ';
                if (!this.unit) this.unit = 'ด้าม';
            } else if (n.includes('หมึก') || n.includes('toner') || n.includes('ink')) {
                this.specs = 'ตลับหมึกพิมพ์เลเซอร์คุณภาพสูง ให้งานพิมพ์คมชัด รองรับปริมาณการพิมพ์ประมาณ 1,500 หน้า';
                if (!this.unit) this.unit = 'ตลับ';
            } else if (n.includes('แฟ้ม')) {
                this.specs = 'ขนาด A4 สันหนา 2 นิ้ว ปกแข็งหุ้มอย่างดี สันแฟ้มมีช่องสำหรับใส่ป้ายชื่อแยกหมวดหมู่';
                if (!this.unit) this.unit = 'เล่ม';
            } else if (n.includes('ลวดเย็บ')) {
                this.specs = 'เบอร์ 10 (27/4.8) ผลิตจากเหล็กกล้าแข็งแรง บรรจุ 1,000 เข็ม/กล่อง';
                if (!this.unit) this.unit = 'กล่อง';
            } else if (n.includes('ผงซักฟอก') || n.includes('ล้างจาน') || n.includes('น้ำยา')) {
                this.specs = 'ขนาดบรรจุ 1,000 มล. ใช้สำหรับขจัดคราบและทำความสะอาดทั่วไป';
                if (!this.unit) this.unit = 'ขวด';
            } else if (n.includes('เทป')) {
                this.specs = 'แกน 3 นิ้ว หน้ากว้าง 1 นิ้ว ความยาว 36 หลา กาวเหนียวแน่นติดทนนาน';
                if (!this.unit) this.unit = 'ม้วน';
            } else if (n.includes('ซอง')) {
                this.specs = 'ขนาด 4.5 x 7 นิ้ว กระดาษปอนด์ขาวคุณภาพดี บรรจุ 50 ซอง/แพ็ค';
                if (!this.unit) this.unit = 'แพ็ค';
            } else if (n.includes('คลิป')) {
                this.specs = 'ขนาด 32 มม. ทำจากโลหะชุบนิกเกิลไม่เป็นสนิม บรรจุ 50 ตัว/กล่อง';
                if (!this.unit) this.unit = 'กล่อง';
            } else if (n.includes('ถ่าน')) {
                this.specs = 'ขนาด AA กำลังไฟ 1.5V ปราศจากสารปรอทและแคดเมียม บรรจุ 4 ก้อน/แพ็ค';
                if (!this.unit) this.unit = 'แพ็ค';
            } else {
                this.specs = 'ขนาดมาตรฐาน ผลิตจากวัสดุคุณภาพดี เหมาะสำหรับงานพัสดุสำนักงานและใช้งานทั่วไป';
            }
            this.aiGenerating = false;
        }, 300);
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

    <form action="{{ route('materials.store') }}" method="POST" class="space-y-6">
        @csrf

        <!-- Section 1: ข้อมูลทั่วไปประจำรายการ -->
        <div>
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                ๑. ข้อมูลกำกับพัสดุและหมวดหมู่
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
                    <input type="text" name="name" x-model="name" @keyup.debounce.400ms="if(!specs) generateAiSpecs()" required placeholder="ตัวอย่าง: กระดาษ A4 80 แกรม (Double A) หรือ ปากกาลูกลื่น สีน้ำเงิน" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-gray-900">
                </div>
            </div>

            <!-- Specs & AI Specs Button -->
            <div class="mt-4">
                <div class="flex justify-between items-center mb-2">
                    <label class="block text-sm font-semibold text-gray-700">ขนาดหรือลักษณะ / สเปกรายละเอียด</label>
                    <button type="button" @click="generateAiSpecs()" class="px-3.5 py-1.5 bg-purple-50 hover:bg-purple-100 text-purple-700 font-bold text-xs rounded-xl border border-purple-200 transition-all flex items-center gap-1.5 shadow-sm active:scale-95">
                        <span x-show="!aiGenerating">✨ AI แนะนำสเปกอัตโนมัติ</span>
                        <span x-show="aiGenerating" style="display:none;" class="flex items-center"><i class="fas fa-spinner fa-spin mr-1"></i> กำลังเจน...</span>
                    </button>
                </div>
                <textarea name="specs" x-model="specs" rows="3" placeholder="ระบุขนาด สเปกรายละเอียด หรือกดปุ่ม ✨ AI แนะนำสเปกอัตโนมัติ (สามารถแก้ไขเพิ่มเติมได้อิสระ)..." class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm leading-relaxed resize-none"></textarea>
                <p class="text-xs text-gray-400 mt-1">💡 สเปกเบื้องต้นถูกสร้างให้อัตโนมัติจากชื่อวัสดุ คุณสามารถพิมพ์แก้ไข เพิ่มเติม หรือลบได้ตามต้องการ</p>
            </div>
        </div>

        <!-- Section 2: การคุมคลังและราคาสต็อก -->
        <div class="pt-4 border-t border-gray-100">
            <h3 class="text-sm font-bold text-gray-800 uppercase tracking-wider mb-4 border-b border-gray-100 pb-2">
                ๒. การคุมคลัง ราคา และเกณฑ์เก็บสำรอง (Min/Max)
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

            <div class="mt-6 p-5 bg-gradient-to-r from-emerald-50 to-teal-50 border border-emerald-200 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4 shadow-sm">
                <div>
                    <label class="block text-sm font-extrabold text-emerald-900 uppercase tracking-wider">
                        ยอดคงเหลือยกมาเริ่มต้น (Opening Balance) <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-emerald-700 mt-1 font-medium">ระบุยอดคงเหลือยกมาจากปีก่อน หรือยอดยกมาเริ่มต้นคุมคลัง</p>
                </div>
                <div class="w-full md:w-48">
                    <input type="number" name="stock_qty" value="{{ old('stock_qty', 0) }}" min="0" required class="w-full px-4 py-3 bg-white border border-emerald-300 rounded-xl focus:ring-2 focus:ring-emerald-500 text-xl font-extrabold text-center text-emerald-900 outline-none shadow-sm">
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('materials.index') }}" class="px-6 py-3 bg-gray-100 text-gray-600 rounded-xl border border-gray-200 hover:bg-gray-200 transition-colors font-semibold text-sm">
                ยกเลิก
            </a>
            <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-purple-500/30 transition-all active:scale-95 flex items-center">
                <i class="fas fa-save mr-2"></i> บันทึกข้อมูลและเปิดสมุดบัญชี
            </button>
        </div>
    </form>
</div>
@endsection
