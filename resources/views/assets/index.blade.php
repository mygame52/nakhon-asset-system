@extends('layouts.app')

@section('page_title', 'ทะเบียนครุภัณฑ์ (พด. 1)')
@section('page_description', 'จัดการและติดตามรายการครุภัณฑ์ทั้งหมดของหน่วยงาน')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div style="display: flex; gap: 10px;">
            <form action="{{ route('assets.index') }}" method="GET" style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 12px; color: #999;"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหารหัส หรือชื่อครุภัณฑ์..." style="padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #ddd; width: 300px; outline: none;">
            </form>
            <button class="btn" style="background: #f0f0f0;"><i class="fas fa-filter"></i> ตัวกรอง</button>
        </div>
        <a href="{{ route('assets.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> ลงทะเบียนครุภัณฑ์ใหม่
        </a>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; background: #fafafa; border-bottom: 2px solid #eee;">
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">รหัสครุภัณฑ์</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">รายการ</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">ประเภท</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">ราคาต่อหน่วย</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">สถานะ</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($assets as $asset)
            <tr style="border-bottom: 1px solid #eee; transition: 0.2s;" onmouseover="this.style.background='#fcfcfc'" onmouseout="this.style.background='transparent'">
                <td style="padding: 15px; color: var(--primary-color); font-weight: 500;">{{ $asset->asset_code }}</td>
                <td style="padding: 15px;">
                    <div style="font-weight: 500;">{{ $asset->name }}</div>
                    <div style="font-size: 0.75rem; color: #999;">{{ $asset->model ?? '-' }}</div>
                </td>
                <td style="padding: 15px; font-size: 0.9rem;">{{ $asset->category->name ?? '-' }}</td>
                <td style="padding: 15px; font-size: 0.9rem;">{{ number_format($asset->unit_price, 2) }} ฿</td>
                <td style="padding: 15px;">
                    <span style="background: rgba(0, 200, 83, 0.1); color: #00c853; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 500;">
                        {{ $asset->status }}
                    </span>
                </td>
                <td style="padding: 15px;">
                    <div style="display: flex; gap: 8px;">
                        <a href="{{ route('assets.show', $asset->id) }}" class="btn" style="padding: 5px 10px; background: rgba(74, 20, 140, 0.05); color: var(--primary-color);"><i class="fas fa-eye"></i></a>
                        <a href="{{ route('assets.edit', $asset->id) }}" class="btn" style="padding: 5px 10px; background: rgba(255, 215, 0, 0.05); color: #f9a825;"><i class="fas fa-edit"></i></a>
                        <button class="btn" style="padding: 5px 10px; background: rgba(255, 0, 0, 0.05); color: #ff4d4d;"><i class="fas fa-trash"></i></button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 50px; text-align: center;">
                    <i class="fas fa-box-open fa-3x" style="color: #ddd; margin-bottom: 15px; display: block;"></i>
                    <p style="color: #999;">ไม่พบข้อมูลครุภัณฑ์ที่ค้นหา</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 25px;">
        {{ $assets->links() }}
    </div>
</div>
@endsection
