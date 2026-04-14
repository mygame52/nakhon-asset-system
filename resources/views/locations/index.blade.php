@extends('layouts.app')

@section('page_title', 'สถานที่จัดเก็บ')
@section('page_description', 'จัดการข้อมูลอาคาร ห้อง หรือสถานที่สำหรับเก็บรักษาครุภัณฑ์')

@section('content')
<div style="display: grid; grid-template-columns: 1fr 2fr; gap: 25px;">
    <!-- Add Form -->
    <div class="card">
        <h4 style="margin-bottom: 20px; color: var(--primary-color);">เพิ่มสถานที่ใหม่</h4>
        <form action="{{ route('locations.store') }}" method="POST">
            @csrf
            <div style="margin-bottom: 15px;">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px;">ชื่ออาคาร/สถานที่</label>
                <input type="text" name="name" required placeholder="เช่น อาคารอำนวยการ" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
            <div style="margin-bottom: 20px;">
                <label style="display: block; font-size: 0.9rem; margin-bottom: 8px;">เลขห้อง (ถ้ามี)</label>
                <input type="text" name="room_number" placeholder="เช่น 101" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; outline: none;">
            </div>
            <button type="submit" class="btn btn-primary" style="width: 100%;">บันทึกข้อมูล</button>
        </form>
    </div>

    <!-- List -->
    <div class="card">
        <table style="width: 100%; border-collapse: collapse;">
            <thead>
                <tr style="text-align: left; border-bottom: 2px solid #eee;">
                    <th style="padding: 12px; font-weight: 600;">ชื่อสถานที่</th>
                    <th style="padding: 12px; font-weight: 600;">เลขห้อง</th>
                    <th style="padding: 12px; font-weight: 600;">จัดการ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($locations as $location)
                <tr style="border-bottom: 1px solid #eee;">
                    <td style="padding: 12px;">{{ $location->name }}</td>
                    <td style="padding: 12px;">{{ $location->room_number ?? '-' }}</td>
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
