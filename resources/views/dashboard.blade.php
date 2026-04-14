@extends('layouts.app')

@section('page_title', 'แดชบอร์ดสรุปผล')
@section('page_description', 'ภาพรวมพัสดุและครุภัณฑ์ สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช')

@section('content')
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 25px; margin-bottom: 30px;">
    <!-- Stat Cards -->
    <div class="card" style="border-left: 5px solid var(--primary-color);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">ครุภัณฑ์ทั้งหมด (ชุด)</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: var(--primary-color);">{{ number_format($totalAssets) }}</h3>
            </div>
            <div style="width: 50px; height: 50px; background: rgba(74, 20, 140, 0.1); border-radius: 12px; display: flex; justify-content: center; align-items: center; color: var(--primary-color);">
                <i class="fas fa-laptop-code fa-lg"></i>
            </div>
        </div>
    </div>

    <div class="card" style="border-left: 5px solid #00c853;">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">วัสดุสำนักงานคงคลัง</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: #00c853;">{{ number_format($totalMaterials) }}</h3>
            </div>
            <div style="width: 50px; height: 50px; background: rgba(0, 200, 83, 0.1); border-radius: 12px; display: flex; justify-content: center; align-items: center; color: #00c853;">
                <i class="fas fa-boxes fa-lg"></i>
            </div>
        </div>
    </div>

    <div class="card" style="border-left: 5px solid var(--accent-color);">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <p style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">รอการตรวจสอบ</p>
                <h3 style="font-size: 1.8rem; font-weight: 700; color: #f9a825;">0</h3>
            </div>
            <div style="width: 50px; height: 50px; background: rgba(255, 215, 0, 0.1); border-radius: 12px; display: flex; justify-content: center; align-items: center; color: #f9a825;">
                <i class="fas fa-clipboard-check fa-lg"></i>
            </div>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 25px;">
    <!-- Recent Assets Table -->
    <div class="card">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h4 style="font-weight: 600;">ครุภัณฑ์ที่ลงทะเบียนล่าสุด</h4>
            <a href="{{ route('assets.index') }}" style="font-size: 0.85rem; color: var(--primary-color); text-decoration: none;">ดูทั้งหมด <i class="fas fa-arrow-right"></i></a>
        </div>
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; background: #fafafa; border-bottom: 1px solid #eee;">
                    <th style="padding: 12px; font-weight: 500; font-size: 0.85rem; color: #666;">รหัสครุภัณฑ์</th>
                    <th style="padding: 12px; font-weight: 500; font-size: 0.85rem; color: #666;">ชื่อรายการ</th>
                    <th style="padding: 12px; font-weight: 500; font-size: 0.85rem; color: #666;">สถานที่</th>
                    <th style="padding: 12px; font-weight: 500; font-size: 0.85rem; color: #666;">สถานะ</th>
                </tr>
            </thead>
            <tbody>
                @forelse($recentAssets as $asset)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px; font-size: 0.9rem;">{{ $asset->asset_code }}</td>
                    <td style="padding: 12px; font-size: 0.9rem; font-weight: 500;">{{ $asset->name }}</td>
                    <td style="padding: 12px; font-size: 0.9rem; color: #666;">{{ $asset->location->name ?? '-' }}</td>
                    <td style="padding: 12px;">
                        <span style="background: rgba(0, 200, 83, 0.1); color: #00c853; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">{{ $asset->status }}</span>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" style="padding: 30px; text-align: center; color: #999;">ยังไม่มีข้อมูลครุภัณฑ์ในระบบ</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Quick Actions / Categories Stats -->
    <div class="card" style="background: linear-gradient(135deg, var(--primary-color), var(--sidebar-bg)); color: white;">
        <h4 style="font-weight: 600; margin-bottom: 20px; color: var(--accent-color);">ทางลัดด่วน</h4>
        <div style="display: grid; grid-template-columns: 1fr; gap: 15px;">
            <a href="{{ route('assets.create') }}" style="display: flex; align-items: center; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; text-decoration: none; color: white; transition: 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <i class="fas fa-plus-circle" style="margin-right: 15px; font-size: 1.2rem;"></i>
                <span>ลงทะเบียนครุภัณฑ์ใหม่</span>
            </a>
            <a href="{{ route('assets.batch-print') }}" style="display: flex; align-items: center; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; text-decoration: none; color: white; transition: 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <i class="fas fa-print" style="margin-right: 15px; font-size: 1.2rem;"></i>
                <span>พิมพ์รหัส QR Code</span>
            </a>
            <a href="#" style="display: flex; align-items: center; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 12px; text-decoration: none; color: white; transition: 0.3s;" onmouseover="this.style.background='rgba(255,255,255,0.2)'" onmouseout="this.style.background='rgba(255,255,255,0.1)'">
                <i class="fas fa-file-export" style="margin-right: 15px; font-size: 1.2rem;"></i>
                <span>ส่งออกรายงานครุภัณฑ์</span>
            </a>
        </div>
    </div>
</div>
@endsection
