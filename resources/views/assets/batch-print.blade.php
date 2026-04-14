@extends('layouts.app')

@section('page_title', 'พิมพ์รหัส QR Code (Batch)')
@section('page_description', 'เลือกครุภัณฑ์ที่ต้องการสั่งพิมพ์ป้ายสติ๊กเกอร์')

@section('content')
<div class="card">
    <form action="{{ route('assets.print-labels') }}" method="POST" target="_blank">
        @csrf
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
            <div style="display: flex; gap: 15px; align-items: center;">
                <label style="display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" id="select-all" style="width: 18px; height: 18px; margin-right: 8px;">
                    <span style="font-weight: 500;">เลือกทั้งหมด</span>
                </label>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-print"></i> พิมพ์ที่เลือก
                </button>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 15px;">
            @foreach($assets as $asset)
            <div style="border: 1px solid #eee; border-radius: 10px; padding: 15px; display: flex; align-items: center; background: #fff; transition: 0.3s;" onmouseover="this.style.borderColor='var(--primary-color)'" onmouseout="this.style.borderColor='#eee'">
                <input type="checkbox" name="ids[]" value="{{ $asset->id }}" class="asset-checkbox" style="width: 20px; height: 20px; margin-right: 15px;">
                <div style="flex: 1;">
                    <p style="font-weight: 600; font-size: 0.95rem; margin-bottom: 3px;">{{ $asset->name }}</p>
                    <p style="font-size: 0.8rem; color: #666;">{{ $asset->asset_code }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </form>
</div>

@section('scripts')
<script>
    document.getElementById('select-all').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.asset-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
    });
</script>
@endsection
@endsection
