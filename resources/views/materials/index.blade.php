@extends('layouts.app')

@section('page_title', 'บัญชีวัสดุ')
@section('page_description', 'จัดการรายการวัสดุสิ้นเปลือง และตรวจเช็คยอดคงเหลือ')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <div style="display: flex; gap: 10px;">
            <form action="{{ route('materials.index') }}" method="GET" style="position: relative;">
                <i class="fas fa-search" style="position: absolute; left: 12px; top: 12px; color: #999;"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อวัสดุ..." style="padding: 10px 15px 10px 40px; border-radius: 10px; border: 1px solid #ddd; width: 300px; outline: none;">
            </form>
        </div>
        <a href="{{ route('materials.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> เพิ่มรายการวัสดุใหม่
        </a>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; background: #fafafa; border-bottom: 2px solid #eee;">
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">ชื่อรายการวัสดุ</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">ประเภท</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">ยอดคงเหลือ</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">หน่วยนับ</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">สถานะสต็อก</th>
                <th style="padding: 15px; font-weight: 600; font-size: 0.9rem; color: #555;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @forelse($materials as $material)
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 15px; font-weight: 500;">{{ $material->name }}</td>
                <td style="padding: 15px; font-size: 0.9rem;">{{ $material->category->name ?? '-' }}</td>
                <td style="padding: 15px; font-weight: 600; font-size: 1.1rem; color: var(--primary-color);">{{ number_format($material->balance) }}</td>
                <td style="padding: 15px; font-size: 0.9rem; color: #666;">{{ $material->unit }}</td>
                <td style="padding: 15px;">
                    @if($material->balance > 10)
                        <span style="background: rgba(0, 200, 83, 0.1); color: #00c853; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">พร้อมจ่าย</span>
                    @elseif($material->balance > 0)
                        <span style="background: rgba(255, 215, 0, 0.1); color: #f9a825; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">ใกล้หมด</span>
                    @else
                        <span style="background: rgba(255, 0, 0, 0.1); color: #ff4d4d; padding: 4px 10px; border-radius: 20px; font-size: 0.8rem;">หมด</span>
                    @endif
                </td>
                <td style="padding: 15px; text-align: right;">
                    <a href="{{ route('materials.edit', $material->id) }}" class="btn" style="background: rgba(74, 20, 140, 0.1); color: var(--primary-color); padding: 5px 12px; font-size: 0.8rem; text-decoration: none;">
                        <i class="fas fa-edit"></i> แก้ไข
                    </a>
                    <form action="{{ route('materials.destroy', $material->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn" style="background: rgba(255, 0, 0, 0.05); color: red; padding: 5px 12px; font-size: 0.8rem; border: none; cursor: pointer;">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" style="padding: 50px; text-align: center; color: #999;">ยังไม่มีข้อมูลวัสดุในระบบ</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div style="margin-top: 25px;">
        {{ $materials->links() }}
    </div>
</div>
@endsection
