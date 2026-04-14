<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <title>พิมพ์ป้ายครุภัณฑ์ (หลายรายการ)</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Sarabun', sans-serif; margin: 0; padding: 20px; background: #f0f0f0; }
        .print-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            width: 17.5cm; /* Approximately A4 width with margins */
            margin: 0 auto;
        }
        .label-container {
            width: 8.5cm;
            height: 4.5cm;
            border: 1px solid #ccc;
            padding: 12px;
            display: flex;
            gap: 15px;
            align-items: center;
            background: white;
            box-sizing: border-box;
            position: relative;
            page-break-inside: avoid;
        }
        .qr-code { width: 90px; height: 90px; }
        .info { flex: 1; }
        .title { font-weight: 600; font-size: 0.85rem; color: #4a148c; margin-bottom: 5px; }
        .asset-name { 
            font-weight: 400; 
            font-size: 0.75rem; 
            margin-bottom: 5px; 
            border-bottom: 1px solid #eee; 
            padding-bottom: 5px;
            height: 2.2em;
            overflow: hidden;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
        }
        .asset-code { font-weight: 600; font-size: 0.85rem; color: #333; }
        .footer { position: absolute; bottom: 5px; right: 10px; font-size: 0.55rem; color: #999; }
        
        .no-print { 
            margin-bottom: 20px; 
            text-align: center;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }
        
        @media print {
            body { background: white; padding: 0; }
            .no-print { display: none; }
            .print-grid { gap: 0; width: 100%; }
            .label-container { border: 0.5px solid #000; }
        }
    </style>
</head>
<body>
    <div class="no-print">
        <h2 style="margin-top: 0;">ตรวจสอบรายการก่อนพิมพ์</h2>
        <p>คุณกำลังจะพิมพ์ป้ายสติ๊กเกอร์ทั้งหมด <strong>{{ count($labels) }}</strong> รายการ</p>
        <button onclick="window.print()" style="padding: 12px 24px; background: #4a148c; color: white; border: none; border-radius: 8px; cursor: pointer; font-family: inherit; font-weight: 600;">
            <i class="fas fa-print"></i> เริ่มพิมพ์ตอนนี้
        </button>
        <button onclick="window.close()" style="padding: 12px 24px; background: #eee; border: none; border-radius: 8px; cursor: pointer; margin-left: 10px; font-family: inherit;">
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
                <div class="title">สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช</div>
                <div class="asset-name">{{ $item['asset']->name }}</div>
                <div class="asset-code">{{ $item['asset']->asset_code }}</div>
            </div>
            <div class="footer">ระบบบริหารจัดการพัสดุ</div>
        </div>
        @endforeach
    </div>
</body>
</html>
