<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์ป้ายครุภัณฑ์ - {{ $asset->asset_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/asset-labels.css') }}">
</head>
<body class="label-print">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4a148c; color: white; border: none; border-radius: 8px; font-weight: bold; cursor: pointer;">🖨️ พิมพ์ป้ายนี้</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #e2e8f0; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; margin-left: 10px;">ปิดหน้าต่าง</button>
    </div>

    <div class="label-container">
        <div class="qr-code">
            {!! $qrCode !!}
        </div>
        <div class="info">
            <div class="title">สำนักงาน สกร.ประจำจังหวัดนครศรีฯ</div>
            <div class="asset-name">{{ $asset->name }}</div>
            <div class="asset-code">{{ $asset->asset_code }}</div>
        </div>
        <div class="label-footer">ระบบบริหารจัดการพัสดุ NAKHON ASSET</div>
    </div>
</body>
</html>
