@extends('layouts.app')

@section('page_title', 'แก้ไขข้อมูลครุภัณฑ์')
@section('page_description', 'ปรับปรุงรายละเอียดของครุภัณฑ์ ' . $asset->asset_code)

@section('content')
<div class="card animate-fade-in">
    <form action="{{ route('assets.update', $asset->id) }}" method="POST">
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

                <div style="margin-bottom: 20px;">
                    <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">สถานที่จัดเก็บ</label>
                    <select name="location_id" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                        @foreach($locations as $location)
                        <option value="{{ $location->id }}" {{ $asset->location_id == $location->id ? 'selected' : '' }}>{{ $location->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div style="margin-bottom: 20px;">
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
