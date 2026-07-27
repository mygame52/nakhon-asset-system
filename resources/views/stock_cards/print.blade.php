<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>บัญชีวัสดุ - {{ $material->name }} ({{ $material->material_code ?? 'MAT-' . sprintf('%04d', $material->id) }})</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 10mm 5mm 10mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'TH Sarabun PSK', 'TH Sarabun New', 'Sarabun', sans-serif;
            font-size: 13.5pt;
            line-height: 1.25;
            color: #000;
            background-color: #fff;
            margin: 0;
            padding: 0;
        }

        .paper {
            width: 277mm;
            margin: 0 auto;
            padding: 6mm 0 2mm 0;
            background: #fff;
            box-sizing: border-box;
        }

        .title {
            text-align: center;
            font-size: 18pt;
            font-weight: bold;
            margin-top: 4px;
            margin-bottom: 10px;
        }

        .top-info-grid {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            font-size: 13pt;
            line-height: 1.45;
        }

        .info-col-left {
            width: 58%;
        }

        .info-col-right {
            width: 40%;
        }

        .dots {
            border-bottom: 1px dotted #000;
            display: inline-block;
            padding: 0 4px;
            text-align: center;
            font-weight: 600;
        }

        /* 8 Columns Table */
        .stock-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
        }

        .stock-table th, 
        .stock-table td {
            border: 1px solid #000;
            padding: 2px 4px;
            font-size: 12.5pt;
            vertical-align: middle;
        }

        .stock-table th {
            text-align: center;
            font-weight: bold;
            background-color: #fff;
        }

        .stock-table tr.row-item td {
            height: 20px;
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }

        .signature-section {
            margin-top: 6px;
            display: flex;
            justify-content: flex-end;
            font-size: 12.5pt;
            page-break-inside: avoid;
        }

        /* Screen Navigation Bar */
        @media screen {
            body {
                background-color: #f3f4f6;
                padding: 15px 0;
            }
            .paper {
                box-shadow: 0 10px 25px rgba(0,0,0,0.1);
                border-radius: 8px;
                padding: 12mm 10mm;
            }
            .no-print-bar {
                max-width: 277mm;
                margin: 0 auto 12px auto;
                display: flex;
                justify-content: space-between;
                align-items: center;
                background: #1e293b;
                color: #fff;
                padding: 10px 18px;
                border-radius: 8px;
            }
            .btn-print {
                background: #10b981;
                color: #fff;
                border: none;
                padding: 8px 18px;
                border-radius: 6px;
                font-weight: bold;
                cursor: pointer;
                font-family: inherit;
                font-size: 14px;
            }
            .btn-back {
                color: #cbd5e1;
                text-decoration: none;
                font-size: 14px;
            }
        }

        @media print {
            .no-print-bar { display: none !important; }
            body { background: transparent; padding: 0; }
            .paper { padding: 0; margin: 0; shadow: none; width: 100%; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <a href="{{ route('stock-cards.show', $material->id) }}" class="btn-back">← ย้อนกลับไปสมุดบัญชีวัสดุ</a>
        <div>
            <span style="font-size: 13px; margin-right: 15px; color: #94a3b8;">แบบฟอร์มบัญชีวัสดุตามมาตรฐานทางราชการ (A4 แนวนอน)</span>
            <button onclick="window.print()" class="btn-print">🖨️ พิมพ์บัญชีวัสดุ</button>
        </div>
    </div>

    <div class="paper">
        <!-- Title -->
        <div class="title">บัญชีวัสดุ <span style="font-size: 14pt; font-weight: normal;">(ประจำปีงบประมาณ พ.ศ. {{ $fiscalYearBE }})</span></div>

        <!-- Header Information Grid (Exact Image Layout) -->
        <div class="top-info-grid">
            <!-- Left Info -->
            <div class="info-col-left">
                <div>
                    แผ่นที่<span class="dots" style="min-width: 70px;">1</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(1)</span>
                </div>
                <div>
                    ประเภท<span class="dots" style="min-width: 130px;">{{ $material->category->name ?? 'ไม่ระบุ' }}</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(3)</span>
                    <span style="margin-left: 12px;">ชื่อหรือชนิดวัสดุ</span><span class="dots" style="min-width: 170px;">{{ $material->name }}</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(4)</span>
                </div>
                <div>
                    ขนาดหรือลักษณะ<span class="dots" style="min-width: 300px;">{{ $material->specs ?? '-' }}</span>
                </div>
                <div>
                    หน่วยที่นับ<span class="dots" style="min-width: 110px;">{{ $material->unit }}</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(6)</span>
                    <span style="margin-left: 12px;">ที่เก็บ</span><span class="dots" style="min-width: 150px;">{{ $material->location_name ?? '-' }}</span>
                </div>
            </div>

            <!-- Right Info -->
            <div class="info-col-right">
                <div>
                    ส่วนราชการ<span class="dots" style="min-width: 230px;">สำนักงาน สกร.ประจำจังหวัดนครศรีฯ</span>
                </div>
                <div>
                    หน่วยงาน<span class="dots" style="min-width: 230px;">งานพัสดุ / สำนักงาน</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(2)</span>
                </div>
                <div>
                    รหัส<span class="dots" style="min-width: 230px;">{{ $material->material_code ?? 'MAT-' . sprintf('%04d', $material->id) }}</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(5)</span>
                </div>
                <div>
                    จำนวนอย่างสูง<span class="dots" style="min-width: 85px;">{{ number_format($material->max_stock ?? 0) }}</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(6)</span>
                    <span style="margin-left: 8px;">จำนวนอย่างต่ำ</span><span class="dots" style="min-width: 85px;">{{ number_format($material->min_stock ?? 0) }}</span><span style="font-size: 10pt; color: #444; margin-left: 2px;">(7)</span>
                </div>
            </div>
        </div>

        <!-- Official Material Ledger Table -->
        <table class="stock-table">
            <thead>
                <tr>
                    <th style="width: 12%;" rowspan="2">วัน เดือน ปี</th>
                    <th style="width: 30%;" rowspan="2">รับจาก / จ่ายให้</th>
                    <th style="width: 14%;" rowspan="2">
                        เลขที่เอกสาร
                        <div style="font-weight: normal; font-size: 10pt;">(8)</div>
                    </th>
                    <th style="width: 12%;" rowspan="2">ราคาต่อหน่วย<br>บาท</th>
                    <th style="width: 20%;" colspan="3">จำนวน</th>
                    <th style="width: 12%;" rowspan="2">หมายเหตุ</th>
                </tr>
                <tr>
                    <th style="width: 6.5%;">รับ</th>
                    <th style="width: 6.5%;">จ่าย</th>
                    <th style="width: 7%;">คงเหลือ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($entries as $index => $row)
                @php
                    $dateObj = \Carbon\Carbon::parse($row->date);
                    $thMonths = [1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'];
                    $formattedDate = $dateObj->format('j') . ' ' . ($thMonths[$dateObj->month] ?? '') . ' ' . ($dateObj->year + 543);
                @endphp
                <tr class="row-item">
                    <td class="text-center">{{ $formattedDate }}</td>
                    <td>{{ $row->party_name }}</td>
                    <td class="text-center">{{ $row->reference_doc }}</td>
                    <td class="text-right">{{ $row->unit_price ? number_format($row->unit_price, 2) : '-' }}</td>
                    <td class="text-center">{{ $row->in_qty ? number_format($row->in_qty) : '' }}</td>
                    <td class="text-center">{{ $row->out_qty ? number_format($row->out_qty) : '' }}</td>
                    <td class="text-center" style="font-weight: bold;">{{ number_format($row->balance_qty) }}</td>
                    <td class="text-center">{{ $row->note }}</td>
                </tr>
                @endforeach

                <!-- Fill remaining blank rows up to 16 total rows -->
                @for($i = count($entries) + 1; $i <= max(16, count($entries)); $i++)
                <tr class="row-item">
                    <td class="text-center"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Signature Footer Section -->
        <div class="signature-section">
            <div style="display: flex; flex-direction: column; align-items: center; width: 250px; text-align: center; line-height: 1.25;">
                <div>(ลงชื่อ)<span class="dots" style="min-width: 140px;"></span>ผู้คุมบัญชีวัสดุ</div>
                <div style="margin-top: 2px; width: 100%; text-align: center;">( ........................................................ )</div>
                <div style="margin-top: 2px; width: 100%; text-align: center;">ตำแหน่ง<span class="dots" style="min-width: 140px; text-align: center;">เจ้าหน้าที่พัสดุ</span></div>
            </div>
        </div>
    </div>

</body>
</html>
