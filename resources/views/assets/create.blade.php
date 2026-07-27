@extends('layouts.app')

@section('page_title', 'ลงทะเบียนครุภัณฑ์ใหม่')
@section('page_description', 'กรอกรายละเอียดตามแบบ พด. 1 เพื่อเพิ่มข้อมูลลงในระบบ')

@section('content')
<div class="card animate-fade-in">
    <form action="{{ route('assets.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Left Column -->
            <div>
                <h4 style="border-bottom: 2px solid var(--accent-color); display: inline-block; margin-bottom: 20px; padding-bottom: 5px; color: var(--primary-color);">ข้อมูลพื้นฐาน</h4>
                
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">รหัสครุภัณฑ์ <span style="color: red;">*</span></label>
                    <input type="text" name="asset_code" required placeholder="เช่น 7440-001-0001/2566" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary-color)'">
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ชื่อรายการ/ชื่อครุภัณฑ์ <span style="color: red;">*</span></label>
                    <input type="text" name="name" required placeholder="ระบุชื่อรายการครุภัณฑ์" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; transition: 0.3s;" onfocus="this.style.borderColor='var(--primary-color)'">
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ประเภทครุภัณฑ์</label>
                        <select name="category_id" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                            <option value="">เลือกประเภท</option>
                            @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ยี่ห้อ/รุ่น/แบบ</label>
                        <input type="text" name="model" placeholder="เช่น RAM 16GB, Core i7" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ลักษณะ/คุณสมบัติ</label>
                    <textarea name="specs" rows="3" placeholder="ระบุรายละเอียดทางเทคนิค" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;"></textarea>
                </div>
            </div>

            <!-- Right Column -->
            <div>
                <h4 style="border-bottom: 2px solid var(--accent-color); display: inline-block; margin-bottom: 20px; padding-bottom: 5px; color: var(--primary-color);">ข้อมูลการจัดซื้อและการเงิน</h4>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ราคาต่อหน่วย (บาท)</label>
                        <input type="number" name="unit_price" step="0.01" placeholder="0.00" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">วันที่ได้มา</label>
                        <input type="date" name="purchase_date" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    </div>
                </div>

                <div style="margin-bottom: 20px;" x-data="{ isNew: false, selected: '' }" x-init="$watch('selected', val => { if(val === 'NEW') isNew = true })">
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
                        <button type="button" @click="isNew = false; selected = ''" class="px-4 bg-gray-100 text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-200">ยกเลิก</button>
                    </div>
                </div>

                <div style="margin-bottom: 20px;" x-data="{ isNew: false, selected: '' }" x-init="$watch('selected', val => { if(val === 'NEW') isNew = true })">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">หน่วยงานเจ้าของ</label>
                    <select name="department_id" x-show="!isNew" x-model="selected" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; background: white;">
                        <option value="">เลือกหน่วยงาน</option>
                        @foreach($departments as $dept)
                        <option value="{{ $dept->id }}">{{ $dept->name }}</option>
                        @endforeach
                        <option value="NEW" style="color: purple; font-weight: bold;">+ พิมพ์หน่วยงานใหม่</option>
                    </select>
                    
                    <div x-show="isNew" style="display: none;" class="flex gap-2">
                        <input type="text" name="department_name" placeholder="พิมพ์ชื่อหน่วยงานใหม่..." style="flex: 1; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;" :required="isNew">
                        <button type="button" @click="isNew = false; selected = ''" class="px-4 bg-gray-100 text-gray-600 rounded-lg border border-gray-200 hover:bg-gray-200">ยกเลิก</button>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">วิธีการได้มา</label>
                        <select name="acquisition_type" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                            <option value="ซื้อ">ซื้อ</option>
                            <option value="จ้าง">จ้าง</option>
                            <option value="บริจาค">ได้รับบริจาค</option>
                            <option value="อื่นๆ">อื่นๆ</option>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">สถานะเริ่มต้น</label>
                        <select name="status" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                            <option value="ใช้งานปกติ">ใช้งานปกติ</option>
                            <option value="รอซ่อม">รอซ่อม</option>
                            <option value="ชำรุด">ชำรุด</option>
                            <option value="เสื่อมสภาพ">เสื่อมสภาพ</option>
                            <option value="จำหน่ายออก">จำหน่ายออก</option>
                        </select>
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">รูปภาพครุภัณฑ์ <span style="font-size: 0.8rem; color: #888;">(รองรับ JPG, PNG, GIF ขนาดไม่เกิน 2MB)</span></label>
                    <input type="file" name="image" accept="image/jpeg,image/png,image/gif" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none; background: white; cursor: pointer;">
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="{{ route('assets.index') }}" class="btn" style="background: #f0f0f0; color: #666; text-decoration: none;">ยกเลิก</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                <i class="fas fa-save" style="margin-right: 8px;"></i> บันทึกข้อมูลครุภัณฑ์
            </button>
        </div>
    </form>
</div>
@endsection
