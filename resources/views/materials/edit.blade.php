@extends('layouts.app')

@section('page_title', 'แก้ไขข้อมูลวัสดุ: ' . $material->name)
@section('page_description', 'ปรับปรุงข้อมูลการคุมคลังและสเปกวัสดุตามระเบียบกระทรวงการคลัง')

@section('content')
<div class="w-full bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 space-y-6">
    <!-- Header Title Bar -->
    <div class="border-b border-gray-100 pb-4 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-700 to-indigo-600 flex items-center">
                <i class="fas fa-edit mr-3 text-purple-500"></i> แก้ไขข้อมูลรายการวัสดุ
            </h2>
            <p class="text-sm text-gray-500 mt-2">รหัสพัสดุ: {{ $material->material_code ?? 'MAT-' . sprintf('%04d', $material->id) }} | ปรับปรุงข้อมูลการคุมคลังและสเปกวัสดุ</p>
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

    <form action="{{ route('materials.update', $material->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

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
                                <option value="{{ $category->id }}" {{ old('category_id', $material->category_id) == $category->id ? 'selected' : '' }}>
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
                        <input type="text" name="material_code" value="{{ old('material_code', $material->material_code) }}" placeholder="เช่น 5510-001" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-mono font-bold text-purple-700">
                    </div>
                </div>

                <!-- Unit -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">หน่วยที่นับ <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-cube text-gray-400"></i>
                        </div>
                        <input type="text" name="unit" value="{{ old('unit', $material->unit) }}" required placeholder="เช่น รีม, ด้าม, เล่ม, กล่อง" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-semibold">
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
                    <input type="text" name="name" value="{{ old('name', $material->name) }}" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-gray-900">
                </div>
            </div>

            <!-- Specs -->
            <div class="mt-4">
                <label class="block text-sm font-semibold text-gray-700 mb-2">ขนาดหรือลักษณะ / สเปกรายละเอียด</label>
                <textarea name="specs" rows="3" placeholder="เช่น ขนาด 210 x 297 มม., บรรจุ 500 แผ่น/รีม..." class="w-full p-4 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm leading-relaxed resize-none">{{ old('specs', $material->specs) }}</textarea>
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
                    <label class="block text-sm font-semibold text-gray-700 mb-2">สถานที่จัดเก็บ (Bin/Shelf)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-map-marker-alt text-gray-400"></i>
                        </div>
                        <input type="text" name="location_name" value="{{ old('location_name', $material->location_name) }}" placeholder="เช่น ห้องพัสดุ หรือ ตู้ 1 ชั้น 2" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-semibold text-purple-900">
                    </div>
                </div>

                <!-- Unit Price -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">ราคาต่อหน่วย (บาท รวม VAT)</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-baht-sign text-gray-400"></i>
                        </div>
                        <input type="number" step="0.01" name="unit_price" value="{{ old('unit_price', $material->unit_price) }}" placeholder="0.00" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-right">
                    </div>
                </div>

                <!-- Min Stock -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">จำนวนอย่างต่ำ <span class="text-gray-400 font-normal">(ไม่บังคับ)</span></label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', $material->min_stock) }}" min="0" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-center">
                </div>

                <!-- Max Stock -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">จำนวนอย่างสูง <span class="text-gray-400 font-normal">(ไม่บังคับ)</span></label>
                    <input type="number" name="max_stock" value="{{ old('max_stock', $material->max_stock) }}" min="0" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-bold text-center">
                </div>
            </div>

            <div class="mt-6 p-5 bg-gradient-to-r from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl flex flex-col md:flex-row justify-between items-center gap-4 shadow-sm">
                <div>
                    <label class="block text-sm font-extrabold text-purple-900 uppercase tracking-wider">
                        จำนวนคงเหลือปัจจุบันในสต็อก <span class="text-red-500">*</span>
                    </label>
                    <p class="text-xs text-purple-700 mt-1 font-medium">ยอดคงเหลือปรับปรุงในคลังวัสดุ</p>
                </div>
                <div class="w-full md:w-48">
                    <input type="number" name="stock_qty" value="{{ old('stock_qty', $material->stock_qty) }}" min="0" required class="w-full px-4 py-3 bg-white border border-purple-300 rounded-xl focus:ring-2 focus:ring-purple-500 text-xl font-extrabold text-center text-purple-900 outline-none shadow-sm">
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
            <a href="{{ route('materials.index') }}" class="px-6 py-3 bg-gray-100 text-gray-600 rounded-xl border border-gray-200 hover:bg-gray-200 transition-colors font-semibold text-sm">
                ยกเลิก
            </a>
            <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-purple-500/30 transition-all active:scale-95 flex items-center">
                <i class="fas fa-save mr-2"></i> บันทึกการแก้ไขข้อมูล
            </button>
        </div>
    </form>
</div>
@endsection
