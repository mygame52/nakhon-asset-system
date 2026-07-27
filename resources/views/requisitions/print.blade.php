<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ใบเบิกพัสดุ - {{ $requisition->requisition_code }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/official-form.css') }}">
</head>
<body class="print-document">

    @php
        $thMonths = [
            1 => 'มกราคม', 2 => 'กุมภาพันธ์', 3 => 'มีนาคม', 4 => 'เมษายน',
            5 => 'พฤษภาคม', 6 => 'มิถุนายน', 7 => 'กรกฎาคม', 8 => 'สิงหาคม',
            9 => 'กันยายน', 10 => 'ตลุาคม', 11 => 'พฤศจิกายน', 12 => 'ธันวาคม'
        ];
        $reqDate = \Carbon\Carbon::parse($requisition->created_at);
        $reqDay = $reqDate->format('j');
        $reqMonth = $thMonths[$reqDate->month] ?? '';
        $reqYear = $reqDate->year + 543;

        $appDay = '';
        $appMonth = '';
        $appYear = '';
        if ($requisition->approved_at) {
            $appDate = \Carbon\Carbon::parse($requisition->approved_at);
            $appDay = $appDate->format('j');
            $appMonth = $thMonths[$appDate->month] ?? '';
            $appYear = $appDate->year + 543;
        }
    @endphp

    <div class="no-print-bar">
        <a href="{{ route('requisitions.index') }}" class="btn-back">← ย้อนกลับไปรายการขอเบิก</a>
        <div>
            <span style="font-size: 13px; margin-right: 15px; color: #94a3b8;">แบบฟอร์มใบเบิกพัสดุ สำนักงาน สกร.ประจำจังหวัดนครศรีฯ (A4)</span>
            <button onclick="window.print()" class="btn-print">🖨️ พิมพ์ใบเบิกพัสดุ</button>
        </div>
    </div>

    <div class="paper">
        <!-- Header Title -->
        <div class="title">ใบเบิกพัสดุ</div>

        <!-- Top Header Info Block -->
        <div style="margin-bottom: 15px; font-size: 16pt; line-height: 1.7;">
            <!-- Line 1 & 2: เลขที่ & ส่วนราชการ (ชิดขวา) -->
            <div style="text-align: right;">
                <div>เลขที่<span class="dots" style="min-width: 80px; margin-left: 4px; padding: 0 4px;">{{ $requisition->requisition_code }}</span></div>
                <div>ส่วนราชการ สำนักงาน สกร.ประจำจังหวัดนครศรีฯ</div>
            </div>
            <!-- Line 3: วันที่ (อยู่กลาง) -->
            <div style="text-align: center; margin-top: 4px;">
                วันที่<span class="dots" style="min-width: 35px; text-align: center;">{{ $reqDay }}</span>เดือน<span class="dots" style="min-width: 90px; text-align: center;">{{ $reqMonth }}</span>พ.ศ.<span class="dots" style="min-width: 50px; text-align: center;">{{ $reqYear }}</span>
            </div>
        </div>

        <!-- Objective Line -->
        <div class="form-row">
            <span style="white-space: nowrap;">ข้าพเจ้าขอเบิกสิ่งของตามรายการต่อไปนี้ เพื่อใช้ในการ</span>
            <span class="flex-fill">{{ $requisition->reason_for_request ?? 'การปฏิบัติงานตามภารกิจหน่วยงาน' }}</span>
        </div>

        <!-- 4 Columns Table -->
        <table class="official-table">
            <thead>
                <tr>
                    <th style="width: 10%;">ลำดับที่</th>
                    <th style="width: 52%;">รายการ</th>
                    <th style="width: 18%;">จำนวน</th>
                    <th style="width: 20%;">หมายเหตุ</th>
                </tr>
            </thead>
            <tbody>
                @foreach($requisition->items as $index => $item)
                <tr class="row-item">
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $item->material->name ?? '-' }}</td>
                    <td class="text-center">
                        {{ number_format($item->approved_qty ?? $item->requested_qty) }} {{ $item->material->unit ?? '' }}
                    </td>
                    <td class="text-center">{{ $item->admin_note ?? ($item->status == 'rejected' ? 'ไม่อนุมัติ' : '') }}</td>
                </tr>
                @endforeach

                @for($i = count($requisition->items) + 1; $i <= max(10, count($requisition->items)); $i++)
                <tr class="row-item">
                    <td class="text-center"></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                @endfor
            </tbody>
        </table>

        <!-- Signatures & Status Footer (2 Columns) -->
        <div class="footer-section">
            <!-- Left Side -->
            <div class="footer-left" style="display: flex; flex-direction: column; align-items: flex-start;">
                <div style="margin-left: 20px;">มีเบิกให้<span class="dots" style="min-width: 90px;">{{ $requisition->items->where('status', 'approved')->count() }}</span>รายการ</div>
                <div style="margin-top: 10px; margin-left: 20px;">ค้างเบิก<span class="dots" style="min-width: 90px;">{{ $requisition->items->where('status', '!=', 'approved')->count() ?: '-' }}</span>รายการ</div>
                
                <!-- เจ้าหน้าที่จ่าย (Centered Block - No Position) -->
                <div style="margin-top: 15px; display: inline-flex; flex-direction: column; align-items: center; width: 280px; text-align: center; line-height: 1.3;">
                    <div>
                        (ลงชื่อ)<span style="position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 140px; vertical-align: bottom;" class="dots">
                            @if($requisition->approver && $requisition->approver->signature)
                                <img src="{{ asset('storage/' . $requisition->approver->signature) }}" style="position: absolute; bottom: 2px; height: 52px; max-width: 120px; object-fit: contain; pointer-events: none;" alt="Signature">
                            @endif
                            &nbsp;
                        </span>เจ้าหน้าที่จ่าย
                    </div>
                    <div style="margin-top: 1px; width: 100%; text-align: center;">( {{ $requisition->approver->name ?? '........................................................' }} )</div>
                </div>

                <div style="margin-top: 25px; margin-left: 20px;" class="bold-text">อนุญาตให้เบิกได้</div>
                
                <!-- ผู้สั่งจ่าย (Centered Block) -->
                <div style="margin-top: 10px; display: inline-flex; flex-direction: column; align-items: center; width: 280px; text-align: center; line-height: 1.3;">
                    <div>(ลงชื่อ)<span class="dots" style="min-width: 170px;"></span>ผู้สั่งจ่าย</div>
                    <div style="margin-top: 1px; width: 100%; text-align: center;">( ........................................................ )</div>
                    <div style="margin-top: 1px; width: 100%; text-align: center;">ตำแหน่ง<span class="dots" style="min-width: 170px; text-align: center;">หัวหน้าเจ้าหน้าที่</span></div>
                </div>
                
                <div style="margin-top: 15px; margin-left: 20px;">วันที่<span class="dots" style="min-width: 35px;">{{ $appDay }}</span>เดือน<span class="dots" style="min-width: 90px;">{{ $appMonth }}</span>พ.ศ.<span class="dots" style="min-width: 50px;">{{ $appYear }}</span></div>
            </div>

            <!-- Right Side -->
            <div class="footer-right" style="display: flex; flex-direction: column; align-items: flex-end;">
                <div style="margin-right: 20px; display: flex; flex-direction: column; align-items: flex-start;">
                    <!-- ผู้เบิก (Centered Block + มอบให้ผู้รับแทน) -->
                    <div style="display: inline-flex; flex-direction: column; align-items: center; width: 280px; text-align: center; line-height: 1.3;">
                        <div>
                            (ลงชื่อ)<span style="position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 180px; vertical-align: bottom;" class="dots">
                                @if($requisition->user && $requisition->user->signature)
                                    <img src="{{ asset('storage/' . $requisition->user->signature) }}" style="position: absolute; bottom: 2px; height: 52px; max-width: 160px; object-fit: contain; pointer-events: none;" alt="Signature">
                                @endif
                                &nbsp;
                            </span>ผู้เบิก
                        </div>
                        <div style="margin-top: 1px; width: 100%; text-align: center;">( {{ $requisition->user->name ?? '........................................................' }} )</div>
                        <div style="margin-top: 1px; width: 100%; text-align: center;">ตำแหน่ง<span class="dots" style="min-width: 180px;"></span></div>
                        <div style="margin-top: 2px; width: 100%; text-align: center; font-size: 14pt;">ได้มอบให้<span class="dots" style="min-width: 170px;"></span>เป็นผู้รับของแทน</div>
                        <div style="margin-top: 1px; width: 100%; text-align: center; font-size: 14pt;">(ลงชื่อ)<span class="dots" style="min-width: 140px;"></span>ผู้รับมอบ</div>
                    </div>

                    <div style="margin-top: 20px; margin-left: 20px;" class="bold-text">ได้รับของครบถ้วนถูกต้องแล้ว</div>
                    
                    <!-- ผู้รับของ (Centered Block) -->
                    <div style="margin-top: 8px; display: inline-flex; flex-direction: column; align-items: center; width: 280px; text-align: center; line-height: 1.3;">
                        <div>
                            (ลงชื่อ)<span style="position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 180px; vertical-align: bottom;" class="dots">
                                @if($requisition->user && $requisition->user->signature && in_array($requisition->status, ['approved', 'partial']))
                                    <img src="{{ asset('storage/' . $requisition->user->signature) }}" style="position: absolute; bottom: 2px; height: 52px; max-width: 160px; object-fit: contain; pointer-events: none;" alt="Signature">
                                @endif
                                &nbsp;
                            </span>ผู้รับของ
                        </div>
                        <div style="margin-top: 1px; width: 100%; text-align: center;">( {{ $requisition->user->name ?? '........................................................' }} )</div>
                        <div style="margin-top: 1px; width: 100%; text-align: center;">ตำแหน่ง<span class="dots" style="min-width: 180px;"></span></div>
                    </div>
                    
                    <div style="margin-top: 12px; margin-left: 20px;">วันที่<span class="dots" style="min-width: 35px;">{{ $appDay ?: $reqDay }}</span>เดือน<span class="dots" style="min-width: 90px;">{{ $appMonth ?: $reqMonth }}</span>พ.ศ.<span class="dots" style="min-width: 50px;">{{ $appYear ?: $reqYear }}</span></div>
                </div>
            </div>
        </div>
    </div>

</body>
</html>
