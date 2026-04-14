@extends('layouts.app')

@section('page_title', 'ประเภทพัสดุ')
@section('page_description', 'จัดการกลุ่มประเภทของครุภัณฑ์และวัสดุ')

@section('styles')
<style>
    .badge {
        font-size: 0.8rem;
        padding: 3px 8px;
        border-radius: 5px;
        font-weight: 500;
    }
    .badge-asset {
        background: rgba(74, 20, 140, 0.1);
        color: var(--primary-color);
    }
    .badge-material {
        background: rgba(0, 200, 83, 0.1);
        color: #00c853;
    }
</style>
@endsection

@section('content')
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
    <!-- Add Form -->
    <div class="card">
        <h4 style="margin-bottom: 20px; color: var(--primary-color);">เพิ่มประเภทใหม่</h4>
        <form action="{{ route('categories.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px;">ชื่อประเภท</label>
                <input type="text" name="name" required placeholder="เช่น ครุภัณฑ์สำนักงาน" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px;">ใช้สำหรับ</label>
                <select name="type" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
                    <option value="asset">ครุภัณฑ์ (Assets)</option>
                    <option value="material">วัสดุ (Materials)</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">บันทึกข้อมูล</button>
        </form>
    </div>

    <!-- List -->
    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #eee;">
                    <th style="padding: 12px; font-weight: 600;">ชื่อประเภท</th>
                    <th style="padding: 12px; font-weight: 600;">การใช้งาน</th>
                    <th style="padding: 12px; font-weight: 600;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($categories as $category)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">{{ $category->name }}</td>
                    <td style="padding: 12px;">
                        <span class="badge {{ $category->type == 'asset' ? 'badge-asset' : 'badge-material' }}">
                            {{ $category->type == 'asset' ? 'ครุภัณฑ์' : 'วัสดุ' }}
                        </span>
                    </td>
                    <td style="padding: 12px;">
                        <button class="btn" style="padding: 5px 10px; color: #ff4d4d;"><i class="fas fa-trash"></i></button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
