<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์ป้ายครุภัณฑ์ (หลายรายการ)</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/asset-labels.css') }}">
    <style>
        .print-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            width: 17.5cm;
            margin: 0 auto;
        }
        .no-print-toolbar { 
            margin-bottom: 20px; 
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
        }
        @media print {
            .no-print-toolbar { display: none !important; }
            .print-grid { gap: 0; width: 100%; }
        }
    </style>
</head>
<body class="label-print">
    <div class="no-print-toolbar">
        <h2 style="margin-top: 0; font-family: 'Sarabun', sans-serif;">ตรวจสอบรายการก่อนพิมพ์</h2>
        <p>คุณกำลังจะพิมพ์ป้ายสติ๊กเกอร์ทั้งหมด <strong>{{ count($labels) }}</strong> รายการ</p>
        <button onclick="window.print()" style="padding: 12px 24px; background: #4a148c; color: white; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; font-weight: 600;">
            🖨️ เริ่มพิมพ์ตอนนี้
        </button>
        <button onclick="window.close()" style="padding: 12px 24px; background: #e2e8f0; border: none; border-radius: 8px; cursor: pointer; margin-left: 10px; font-family: inherit;">
            ยกเลิก
        </button>
    </div>

    <div class="print-grid">
        @foreach($labels as $item)
        <div class="label-container">
            <div class="qr-code">
                {!! $item['qrCode'] !!}
            </div>
            <div class="info">
                <div class="title">สำนักงาน สกร.ประจำจังหวัดนครศรีฯ</div>
                <div class="asset-name">{{ $item['asset']->name }}</div>
                <div class="asset-code">{{ $item['asset']->asset_code }}</div>
            </div>
            <div class="label-footer">ระบบบริหารจัดการพัสดุ NAKHON ASSET</div>
        </div>
        @endforeach
    </div>
</body>
</html>
