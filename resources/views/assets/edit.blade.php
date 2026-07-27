@extends('layouts.app')

@section('page_title', 'แก้ไขข้อมูลครุภัณฑ์')
@section('page_description', 'ปรับปรุงรายละเอียดของครุภัณฑ์ ' . $asset->asset_code)

@section('content')
<div class="card animate-fade-in">
    <form action="{{ route('assets.update', $asset->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Left Column -->
            <div>
                <h4 style="border-bottom: 2px solid var(--accent-color); display: inline-block; margin-bottom: 20px; padding-bottom: 5px; color: var(--primary-color);">ข้อมูลพื้นฐาน</h4>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">รหัสครุภัณฑ์ <span style="color: red;">*</span></label>
                    <input type="text" name="asset_code" required value="{{ $asset->asset_code }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ชื่อรายการ/ชื่อครุภัณฑ์ <span style="color: red;">*</span></label>
                    <input type="text" name="name" required value="{{ $asset->name }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ประเภทครุภัณฑ์</label>
                        <select name="category_id" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $asset->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ยี่ห้อ/รุ่น/แบบ</label>
                        <input type="text" name="model" value="{{ $asset->model }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ลักษณะ/คุณสมบัติ</label>
                    <textarea name="specs" rows="3" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">{{ $asset->specs }}</textarea>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <h4 style="border-bottom: 2px solid var(--accent-color); display: inline-block; margin-bottom: 20px; padding-bottom: 5px; color: var(--primary-color);">ข้อมูลการเงิน</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ราคาต่อหน่วย (บาท)</label>
                        <input type="number" name="unit_price" step="0.01" value="{{ $asset->unit_price }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">วันที่ได้มา</label>
                        <input type="date" name="purchase_date" value="{{ $asset->purchase_date ? $asset->purchase_date->format('Y-m-d') : '' }}" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;" x-data="{ isNew: false, selected: '{{ $asset->location_id ?? '' }}' }" x-init="$watch('selected', val => { if(val === 'NEW') isNew = true })">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">สถานที่จัดเก็บ</label>
                    <select name="location_id" x-show="!isNew" x-model="selected" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; background: white;">
                        <option value="">เลือกสถานที่</option>
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}">{{ $location->name }} {{ $location->room_number ? '('.$location->room_number.')' : '' }}</option>
                        @endforeach
                        <option value="NEW" style="color: purple; font-weight: bold;">+ พิมพ์สถานที่ใหม่</option>
                    </select>
                    
                    <div x-show="isNew" style="display: none;" class="flex gap-2">
                        <input type="text" name="location_name" placeholder="พิมพ์ชื่อสถานที่ใหม่..." style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;" :required="isNew">
                        <button type="button" @click="isNew = false; selected = '{{ $asset->location_id ?? '' }}'" class="px-4 bg-gray-100 text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-200">ยกเลิก</button>
                    </div>
                </div>

                <div style="margin-bottom: 20px;" x-data="{ isNew: false, selected: '{{ $asset->department_id ?? '' }}' }" x-init="$watch('selected', val => { if(val === 'NEW') isNew = true })">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">หน่วยงาน/แผนกที่รับผิดชอบ</label>
                    <select name="department_id" x-show="!isNew" x-model="selected" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; background: white;">
                        <option value="">เลือกหน่วยงาน</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                        <option value="NEW" style="color: purple; font-weight: bold;">+ พิมพ์หน่วยงานใหม่</option>
                    </select>
                    
                    <div x-show="isNew" style="display: none;" class="flex gap-2">
                        <input type="text" name="department_name" placeholder="พิมพ์ชื่อหน่วยงานใหม่..." style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;" :required="isNew">
                        <button type="button" @click="isNew = false; selected = '{{ $asset->department_id ?? '' }}'" class="px-4 bg-gray-100 text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-200">ยกเลิก</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">วิธีการได้มา</label>
                        <select name="acquisition_type" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                            <option value="">เลือกวิธีการได้มา</option>
                            <option value="ซื้อ" {{ $asset->acquisition_type == 'ซื้อ' ? 'selected' : '' }}>ซื้อ</option>
                            <option value="จ้าง" {{ $asset->acquisition_type == 'จ้าง' ? 'selected' : '' }}>จ้าง</option>
                            <option value="บริจาค" {{ $asset->acquisition_type == 'บริจาค' ? 'selected' : '' }}>ได้รับบริจาค</option>
                            <option value="อื่นๆ" {{ (!in_array($asset->acquisition_type, ['ซื้อ', 'จ้าง', 'บริจาค']) && !empty($asset->acquisition_type)) || $asset->acquisition_type == 'อื่นๆ' ? 'selected' : '' }}>อื่นๆ</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">สถานะปัจจุบัน</label>
                        <select name="status" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                            <option value="ใช้งานปกติ" {{ $asset->status == 'ใช้งานปกติ' ? 'selected' : '' }}>ใช้งานปกติ</option>
                            <option value="รอซ่อม" {{ $asset->status == 'รอซ่อม' ? 'selected' : '' }}>รอซ่อม</option>
                            <option value="ชำรุด" {{ $asset->status == 'ชำรุด' ? 'selected' : '' }}>ชำรุด</option>
                            <option value="เสื่อมสภาพ" {{ $asset->status == 'เสื่อมสภาพ' ? 'selected' : '' }}>เสื่อมสภาพ</option>
                            <option value="จำหน่ายออก" {{ $asset->status == 'จำหน่ายออก' ? 'selected' : '' }}>จำหน่ายออก</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">รูปภาพครุภัณฑ์ <span style="font-size: 0.8rem; color: #888;">(รองรับ JPG, PNG, GIF ขนาดไม่เกิน 2MB)</span></label>
                    
                    @if($asset->image)
                        <div class="mb-4 flex items-start gap-4 p-4 border border-gray-200 rounded-xl bg-gray-50">
                            <div class="w-24 h-24 rounded-lg overflow-hidden border border-gray-300 shadow-sm flex-shrink-0 bg-white">
                                <img src="{{ asset('storage/' . $asset->image) }}" class="w-full h-full object-cover" alt="รูปภาพปัจจุบัน">
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-700 mb-1">รูปภาพปัจจุบัน</p>
                                <p class="text-xs text-gray-500">หากอัปโหลดไฟล์ใหม่ รูปภาพเดิมจะถูกลบและแทนที่ทันที</p>
                            </div>
                        </div>
                    @endif
                    
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; background: white; cursor: pointer;">
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="{{ route('assets.show', $asset->id) }}" class="btn" style="background: #f0f0f0; color: #666; text-decoration: none;">ยกเลิก</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                <i class="fas fa-save" style="margin-right: 8px;"></i> บันทึกการแก้ไข
            </button>
        </div>
    </form>
</div>
@endsection
