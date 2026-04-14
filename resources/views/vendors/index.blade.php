@extends('layouts.app')

@section('page_title', 'ร้านค้า / ผู้จัดจำหน่าย')
@section('page_description', 'จัดการข้อมูลร้านค้าคู่สัญญาและผู้ติดต่อ')

@section('content')
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
        <h4 style="color: var(--primary-color);">รายชื่อร้านค้าทั้งหมด</h4>
        <a href="#" class="btn btn-primary" onclick="document.getElementById('add-vendor-form').style.display='block'">
            <i class="fas fa-plus"></i> เพิ่มร้านค้าใหม่
        </a>
    </div>

    <!-- Simple Quick Add Form (Hidden by default) -->
    <div id="add-vendor-form" class="card" style="display: none; background: #fafafa; margin-bottom: 30px; border: 1px dashed var(--primary-color);">
        <form action="{{ route('vendors.store') }}" method="POST">
            @csrf
            <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 20px;">
                <div>
                    <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">ชื่อร้านค้า</label>
                    <input type="text" name="name" required style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div>
                    <label style="display: block; font-size: 0.8rem; margin-bottom: 5px;">เบอร์โทรศัพท์</label>
                    <input type="text" name="phone" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 5px;">
                </div>
                <div style="display: flex; align-items: flex-end; gap: 10px;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">บันทึก</button>
                    <button type="button" class="btn" style="background: #eee;" onclick="document.getElementById('add-vendor-form').style.display='none'">ยกเลิก</button>
                </div>
            </div>
        </form>
    </div>

    <table style="width: 100%; border-collapse: collapse;">
        <thead>
            <tr style="text-align: left; border-bottom: 2px solid #eee;">
                <th style="padding: 12px; font-weight: 600;">ชื่อร้านค้า</th>
                <th style="padding: 12px; font-weight: 600;">ผู้ติดต่อ</th>
                <th style="padding: 12px; font-weight: 600;">เบอร์โทรศัพท์</th>
                <th style="padding: 12px; font-weight: 600;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($vendors as $vendor)
            <tr style="border-bottom: 1px solid #eee;">
                <td style="padding: 12px; font-weight: 500;">{{ $vendor->name }}</td>
                <td style="padding: 12px;">{{ $vendor->contact_person ?? '-' }}</td>
                <td style="padding: 12px;">{{ $vendor->phone ?? '-' }}</td>
                <td style="padding: 12px;">
                    <button class="btn" style="padding: 5px 10px; color: #ff4d4d;"><i class="fas fa-trash"></i></button>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
