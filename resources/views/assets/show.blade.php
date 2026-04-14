@extends('layouts.app')

@section('page_title', 'รายละเอียดครุภัณฑ์')
@section('page_description', 'ข้อมูลทางเทคนิค ประวัติ และสถานะของครุภัณฑ์')

@section('content')
<div style="margin-bottom: 20px;">
    <a href="{{ route('assets.index') }}" style="text-decoration: none; color: #666;"><i class="fas fa-arrow-left"></i> กลับไปหน้าจดทะเบียน</a>
</div>

<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 30px;">
    <!-- Asset Profile & QR -->
    <div class="card" style="text-align: center;">
        <div style="width: 150px; height: 150px; background: #eee; border-radius: 15px; margin: 0 auto 20px; display: flex; justify-content: center; align-items: center; color: #999;">
            <i class="fas fa-image fa-3x"></i>
        </div>
        <h3 style="margin-bottom: 5px;">{{ $asset->name }}</h3>
        <p style="color: var(--primary-color); font-weight: 500; font-size: 1.1rem; margin-bottom: 20px;">{{ $asset->asset_code }}</p>
        
        <div style="padding: 15px; background: #f9f9f9; border-radius: 10px; margin-bottom: 20px;">
            <p style="font-size: 0.8rem; color: #999; margin-bottom: 10px;">รหัส QR สำหรับตรวจสอบ</p>
            <div style="width: 150px; height: 150px; background: white; border: 1px solid #ddd; margin: 0 auto; display: flex; justify-content: center; align-items: center; padding: 10px;">
                {!! $qrCode !!}
            </div>
            <a href="{{ route('assets.print', $asset->id) }}" target="_blank" class="btn" style="margin-top: 15px; font-size: 0.8rem; background: var(--primary-color); color: white; width: 100%; text-align: center; text-decoration: none; display: block;">
                <i class="fas fa-print"></i> พิมพ์สติ๊กเกอร์
            </a>
        </div>

        <div style="display: flex; gap: 10px;">
            <a href="{{ route('assets.edit', $asset->id) }}" class="btn" style="flex: 1; background: #f9a825; color: white; text-decoration: none;">แก้ไขข้อมูล</a>
        </div>
    </div>

    <!-- Details -->
    <div class="card">
        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 40px;">
            <div>
                <h4 style="border-bottom: 2px solid var(--accent-color); display: inline-block; margin-bottom: 20px; padding-bottom: 5px; color: var(--primary-color);">ข้อมูลทั่วไป</h4>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">ประเภท</span>
                    <span style="font-weight: 500;">{{ $asset->category->name ?? '-' }}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">รุ่น/แบบ</span>
                    <span style="font-weight: 500;">{{ $asset->model ?? '-' }}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">ลักษณะ/คุณสมบัติ</span>
                    <span style="font-weight: 400;">{{ $asset->specs ?? '-' }}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">สถานที่จัดเก็บ</span>
                    <span style="font-weight: 500;">{{ $asset->location->name ?? '-' }} {{ $asset->location->room_number ?? '' }}</span>
                </div>
            </div>

            <div>
                <h4 style="border-bottom: 2px solid var(--accent-color); display: inline-block; margin-bottom: 20px; padding-bottom: 5px; color: var(--primary-color);">ข้อมูลการเงิน</h4>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">ราคาต่อหน่วย</span>
                    <span style="font-weight: 600; font-size: 1.1rem; color: #00c853;">{{ number_format($asset->unit_price, 2) }} บาท</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">วันที่ได้รับ</span>
                    <span style="font-weight: 500;">{{ $asset->purchase_date ? $asset->purchase_date->format('d/m/Y') : '-' }}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">วิธีการได้มา</span>
                    <span style="font-weight: 500;">{{ $asset->acquisition_type ?? '-' }}</span>
                </div>
                <div style="margin-bottom: 15px;">
                    <span style="display: block; font-size: 0.8rem; color: #999;">เจ้าของ/หน่วยงาน</span>
                    <span style="font-weight: 500;">{{ $asset->department->name ?? '-' }}</span>
                </div>
            </div>
        </div>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <h4 style="margin-bottom: 15px;">หมายเหตุ / บันทึกเพิ่มเติม</h4>
            <div style="padding: 15px; background: #fafafa; border-radius: 8px; color: #666; font-size: 0.9rem;">
                {{ $asset->note ?? 'ไม่มีบันทึกเพิ่มเติม' }}
            </div>
        </div>
    </div>
</div>
@endsection
