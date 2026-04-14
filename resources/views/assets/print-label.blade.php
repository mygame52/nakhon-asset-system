<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์ป้ายครุภัณฑ์ - {{ $asset->asset_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; margin: 0; padding: 20px; }
        .label-container {
            width: 8cm;
            height: 4cm;
            border: 1px solid #ccc;
            padding: 10px;
            display: flex;
            gap: 15px;
            align-items: center;
            background: white;
            box-sizing: border-box;
            position: relative;
        }
        .qr-code { width: 100px; height: 100px; }
        .info { flex: 1; }
        .title { font-weight: 600; font-size: 0.9rem; color: #4a148c; margin-bottom: 5px; }
        .asset-name { font-weight: 400; font-size: 0.8rem; margin-bottom: 5px; border-bottom: 1px solid #eee; padding-bottom: 5px; }
        .asset-code { font-weight: 600; font-size: 0.9rem; color: #333; }
        .footer { position: absolute; bottom: 5px; right: 10px; font-size: 0.6rem; color: #999; }
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
            .label-container { border: 1px solid #000; }
        }
    </style>
</head>
<body>
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 20px; background: #4a148c; color: white; border: none; border-radius: 5px; cursor: pointer;">พิมพ์ป้ายนี้</button>
        <button onclick="window.close()" style="padding: 10px 20px; background: #eee; border: none; border-radius: 5px; cursor: pointer; margin-left: 10px;">ปิดหน้าต่าง</button>
    </div>

    <div class="label-container">
        <div class="qr-code">
            {!! $qrCode !!}
        </div>
        <div class="info">
            <div class="title">สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช</div>
            <div class="asset-name">{{ $asset->name }}</div>
            <div class="asset-code">{{ $asset->asset_code }}</div>
        </div>
        <div class="footer">ระบบบริหารจัดการพัสดุ</div>
    </div>

    <script>
        // Auto-open print dialog in some browsers if desired
        // window.onload = function() { window.print(); }
    </script>
</body>
</html>
