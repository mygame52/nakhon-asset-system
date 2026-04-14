@extends('layouts.app')

@section('page_title', 'หน่วยงาน / กลุ่มงาน')
@section('page_description', 'จัดการข้อมูลโครงสร้างหน่วยงานภายใน สกร. นครศรีฯ')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
    <!-- Add Form -->
    <div class="card">
        <h4 style="margin-bottom: 20px; color: var(--primary-color);">เพิ่มหน่วยงานใหม่</h4>
        <form action="{{ route('departments.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px;">ชื่อหน่วยงาน/กลุ่มงาน</label>
                <input type="text" name="name" required placeholder="เช่น กลุ่มงานพัสดุและสินทรัพย์" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px;">รหัสหน่วยงาน (ถ้ามี)</label>
                <input type="text" name="code" placeholder="เช่น FIN-01" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">บันทึกข้อมูล</button>
        </form>
    </div>

    <!-- List -->
    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #eee;">
                    <th style="padding: 12px; font-weight: 600;">ชื่อหน่วยงาน</th>
                    <th style="padding: 12px; font-weight: 600;">รหัส</th>
                    <th style="padding: 12px; font-weight: 600;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($departments as $dept)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">{{ $dept->name }}</td>
                    <td style="padding: 12px;">{{ $dept->code ?? '-' }}</td>
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
