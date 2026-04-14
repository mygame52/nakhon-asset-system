@extends('layouts.app')

@section('page_title', 'แก้ไขข้อมูลวัสดุ')
@section('page_description', 'ปรับปรุงรายละเอียดของวัสดุ: ' . $material->name)

@section('content')
<div style="max-width: 800px; margin: 0 auto;">
    <div class="card">
        <form action="{{ route('materials.update', $material->id) }}" method="POST">
            @csrf
            @method('PUT')
            
            <div style="margin-bottom: 20px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ชื่อวัสดุ <span style="color: red;">*</span></label>
                <input type="text" name="name" value="{{ old('name', $material->name) }}" required style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                @error('name') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">ประเภทวัสดุ</label>
                    <select name="category_id" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none; background: #fff;">
                        <option value="">-- เลือกประเภท --</option>
                        @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id', $material->category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 500;">หน่วยนับ <span style="color: red;">*</span></label>
                    <input type="text" name="unit" value="{{ old('unit', $material->unit) }}" required placeholder="เช่น รีม, ด้าม, ชิ้น" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    @error('unit') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
                </div>
            </div>

            <div style="margin-bottom: 30px;">
                <label style="display: block; margin-bottom: 8px; font-weight: 500;">ยอดคงเหลือ (ยกยอดมา) <span style="color: red;">*</span></label>
                <input type="number" name="balance" value="{{ old('balance', $material->balance) }}" required min="0" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                @error('balance') <span style="color: red; font-size: 0.8rem;">{{ $message }}</span> @enderror
            </div>

            <div style="display: flex; gap: 10px; border-top: 1px solid #eee; padding-top: 25px;">
                <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                    <i class="fas fa-save"></i> บันทึกการแก้ไข
                </button>
                <a href="{{ route('materials.index') }}" class="btn" style="background: #f0f0f0; padding: 12px 30px; text-decoration: none; color: #333;">
                    ยกเลิก
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
