@extends('layouts.app')

@section('page_title', 'เพิ่มรายการวัสดุใหม่')
@section('page_description', 'ระบุชื่อและหน่วยนับของวัสดุเพื่อลงทะเบียนในคลังคุมวัสดุ')

@section('content')
<div class="card animate-fade-in" style="max-width: 800px; margin: 0 auto;">
    <form action="{{ route('materials.store') }}" method="POST">
        @csrf
        
        <div style="margin-bottom: 25px;">
            <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ชื่อรายการวัสดุ <span style="color: red;">*</span></label>
            <input type="text" name="name" required placeholder="เช่น กระดาษ A4 80 แกรม" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
            <div>
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ประเภทวัสดุ</label>
                <select name="category_id" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    <option value="">เลือกประเภท</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">หน่วยนับ <span style="color: red;">*</span></label>
                <input type="text" name="unit" required placeholder="เช่น รีม, กล่อง, ชิ้น" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 25px;">
            <div>
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ราคาต่อหน่วย (ประมาณการ)</label>
                <input type="number" name="unit_price" step="0.01" placeholder="0.00" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
            <div>
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ยอดคงเหลือยกมา <span style="color: red;">*</span></label>
                <input type="number" name="balance" required value="0" style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
        </div>

        <div style="margin-bottom: 25px;">
            <label style="display: block; font-size: 0.9rem; margin-bottom: 8px; font-weight: 500;">ขนาด/ลักษณะเพิ่มเติม</label>
            <input type="text" name="specs" placeholder="เช่น 210 x 297 มม." style="width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
        </div>

        <div style="margin-top: 30px; display: flex; justify-content: flex-end; gap: 15px; border-top: 1px solid #eee; padding-top: 20px;">
            <a href="{{ route('materials.index') }}" class="btn" style="background: #f0f0f0; color: #666; text-decoration: none;">ยกเลิก</a>
            <button type="submit" class="btn btn-primary" style="padding: 12px 30px;">
                <i class="fas fa-save" style="margin-right: 8px;"></i> บันทึกข้อมูลวัสดุ
            </button>
        </div>
    </form>
</div>
@endsection
