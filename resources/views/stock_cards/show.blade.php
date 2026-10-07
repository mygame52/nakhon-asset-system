@extends('layouts.app')

@section('page_title', 'สมุดบัญชีวัสดุ: ' . $material->name)
@section('page_description', 'แบบฟอร์มคุมบัญชีรับ-จ่าย-คงเหลือวัสดุ ตามระเบียบกระทรวงการคลัง และข้อบังคับพัสดุทางราชการ')

@section('content')
<div class="max-w-6xl mx-auto space-y-6" x-data="{ stockInModal: false, settingsModal: false, showRulesModal: false }">
    <!-- Top Action Bar & Fiscal Year Filter -->
    <div class="flex flex-col md:flex-row justify-between items-stretch md:items-center gap-4">
        <a href="{{ route('stock-cards.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center transition-colors font-semibold text-sm">
            <i class="fas fa-arrow-left mr-2"></i> ย้อนกลับไปดัชนีบัญชีวัสดุ
        </a>

        <!-- Fiscal Year Dropdown (ข้อ 1: สรุปจัดทำตามปีงบประมาณ) -->
        <div class="flex flex-wrap items-center gap-2">
            <form action="{{ route('stock-cards.show', $material->id) }}" method="GET" class="flex items-center gap-2">
                <span class="text-xs font-bold text-gray-500 uppercase tracking-wider">ปีงบประมาณ:</span>
                <select name="fiscal_year" onchange="this.form.submit()" class="px-3 py-1.5 bg-white border border-gray-300 rounded-xl font-bold text-sm text-purple-900 shadow-sm focus:ring-2 focus:ring-purple-500 outline-none">
                    @foreach($availableYears as $year)
                        <option value="{{ $year }}" {{ $fiscalYearBE == $year ? 'selected' : '' }}>พ.ศ. {{ $year }}</option>
                    @endforeach
                </select>
            </form>

            <button type="button" @click="showRulesModal = true" class="px-3.5 py-2 bg-amber-50 border border-amber-200 text-amber-800 font-bold rounded-xl text-xs shadow-sm hover:bg-amber-100 transition-all flex items-center">
                <i class="fas fa-info-circle mr-1.5 text-amber-600"></i> ข้อควรทราบเกี่ยวกับบัญชีวัสดุ
            </button>

            <button type="button" @click="settingsModal = true" class="px-3.5 py-2 bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold rounded-xl text-xs shadow-sm transition-all flex items-center">
                <i class="fas fa-cog mr-1.5 text-purple-600"></i> ตั้งค่าการคุมคลัง (Min/Max/ที่เก็บ)
            </button>
            
            <button type="button" @click="stockInModal = true" class="px-3.5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center">
                <i class="fas fa-plus-circle mr-1.5"></i> บันทึกรับวัสดุเข้า
            </button>
            
            <a href="{{ route('stock-cards.print', ['material' => $material->id, 'fiscal_year' => $fiscalYearBE]) }}" target="_blank" class="px-4 py-2 bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-800 hover:to-indigo-800 text-white font-bold rounded-xl text-xs shadow-md transition-all flex items-center">
                <i class="fas fa-print mr-1.5 text-amber-300"></i> พิมพ์บัญชีวัสดุ A4
            </a>
        </div>
    </div>

    <!-- Official Document Preview Card (Match Exact Image Layout) -->
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 p-8 md:p-12 relative overflow-hidden" style="font-family: 'Sarabun', sans-serif;">
        <!-- Fiscal Year Badge -->
        <div class="absolute top-6 right-8 bg-purple-50 text-purple-800 border border-purple-200 font-bold text-xs px-3.5 py-1.5 rounded-full shadow-sm">
            ปีงบประมาณ พ.ศ. {{ $fiscalYearBE }} (1 ต.ค. {{ $fiscalYearBE - 1 }} - 30 ก.ย. {{ $fiscalYearBE }})
        </div>

        <!-- Header Title -->
        <h2 class="text-2xl font-bold text-center text-gray-900 tracking-wide mb-8">บัญชีวัสดุ</h2>

        <!-- Top Header Info Grid (Match Sample Image 100%) -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-3 text-sm text-gray-900 leading-relaxed mb-8">
            <!-- Left Side Info -->
            <div class="space-y-2.5">
                <div>
                    <span>แผ่นที่</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[80px] text-center">1</span>
                    <span class="text-xs text-gray-400 ml-1">(1)</span>
                </div>
                <div>
                    <span>ประเภท</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[120px] text-center">{{ $material->category->name ?? 'ไม่ระบุ' }}</span>
                    <span class="text-xs text-gray-400 ml-1">(3)</span>
                    <span class="ml-4">ชื่อหรือชนิดวัสดุ</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-3 inline-block min-w-[140px] text-center">{{ $material->name }}</span>
                    <span class="text-xs text-gray-400 ml-1">(4)</span>
                </div>
                <div>
                    <span>ขนาดหรือลักษณะ</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[240px] text-center">{{ $material->specs ?? '-' }}</span>
                </div>
                <div>
                    <span>หน่วยที่นับ</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[100px] text-center">{{ $material->unit }}</span>
                    <span class="text-xs text-gray-400 ml-1">(6)</span>
                    <span class="ml-4">ที่เก็บ</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[140px] text-center">{{ $material->location_name ?? '-' }}</span>
                </div>
            </div>

            <!-- Right Side Info -->
            <div class="space-y-2.5">
                <div>
                    <span>ส่วนราชการ</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[220px] text-center">สำนักงาน สกร.ประจำจังหวัดนครศรีฯ</span>
                </div>
                <div>
                    <span>หน่วยงาน</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[220px] text-center">งานพัสดุ / สำนักงาน</span>
                    <span class="text-xs text-gray-400 ml-1">(2)</span>
                </div>
                <div>
                    <span>รหัส</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[220px] text-center font-mono">{{ $material->material_code ?? 'MAT-' . sprintf('%04d', $material->id) }}</span>
                    <span class="text-xs text-gray-400 ml-1">(5)</span>
                </div>
                <div>
                    <span>จำนวนอย่างสูง</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[90px] text-center">{{ number_format($material->max_stock ?? 0) }}</span>
                    <span class="text-xs text-gray-400 ml-1">(6)</span>
                    <span class="ml-3">จำนวนอย่างต่ำ</span>
                    <span class="font-bold border-b border-dotted border-gray-400 px-4 inline-block min-w-[90px] text-center">{{ number_format($material->min_stock ?? 0) }}</span>
                    <span class="text-xs text-gray-400 ml-1">(7)</span>
                </div>
            </div>
        </div>

        <!-- Official Material Ledger Table (Match Image Columns Exact Structure) -->
        <div class="border border-gray-900 rounded-lg overflow-hidden mb-6">
            <table class="w-full text-left border-collapse text-xs md:text-sm">
                <thead>
                    <tr class="bg-gray-100 border-b border-gray-900 font-bold text-gray-900 text-center">
                        <th class="p-2.5 border-r border-gray-900 w-28" rowspan="2">วัน เดือน ปี</th>
                        <th class="p-2.5 border-r border-gray-900" rowspan="2">รับจาก / จ่ายให้</th>
                        <th class="p-2.5 border-r border-gray-900 w-32" rowspan="2">
                            เลขที่เอกสาร
                            <div class="text-[10px] font-normal text-gray-500">(8)</div>
                        </th>
                        <th class="p-2.5 border-r border-gray-900 w-28" rowspan="2">ราคาต่อหน่วย<br>บาท (รวม VAT)</th>
                        <th class="p-1 border-b border-r border-gray-900" colspan="3">จำนวน</th>
                        <th class="p-2.5 w-32" rowspan="2">หมายเหตุ</th>
                    </tr>
                    <tr class="bg-gray-100 border-b border-gray-900 font-bold text-gray-900 text-center">
                        <th class="p-2 border-r border-gray-900 w-20">รับ</th>
                        <th class="p-2 border-r border-gray-900 w-20">จ่าย</th>
                        <th class="p-2 border-r border-gray-900 w-24">คงเหลือ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    @forelse($entries as $index => $row)
                    @php
                        $dateObj = \Carbon\Carbon::parse($row->date);
                        $thMonths = [1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.', 5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.', 9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'];
                        $formattedDate = $dateObj->format('j') . ' ' . ($thMonths[$dateObj->month] ?? '') . ' ' . ($dateObj->year + 543);
                        $isOpening = ($row->reference_doc === 'ยอดยกมา');
                    @endphp
                    <tr class="text-gray-900 {{ $isOpening ? 'bg-amber-50/70 font-semibold' : 'hover:bg-purple-50/30' }} transition-colors">
                        <td class="p-2.5 border-r border-gray-900 text-center font-medium">{{ $formattedDate }}</td>
                        <td class="p-2.5 border-r border-gray-900 font-medium">
                            @if($isOpening)
                                <span class="text-amber-900 font-bold"><i class="fas fa-level-up-alt mr-1"></i> {{ $row->party_name }}</span>
                            @else
                                {{ $row->party_name }}
                            @endif
                        </td>
                        <td class="p-2.5 border-r border-gray-900 text-center font-mono text-xs font-semibold">{{ $row->reference_doc }}</td>
                        <td class="p-2.5 border-r border-gray-900 text-right font-medium">
                            {{ $row->unit_price ? number_format($row->unit_price, 2) : '-' }}
                        </td>
                        <td class="p-2.5 border-r border-gray-900 text-center font-bold text-emerald-700">
                            {{ $row->in_qty ? number_format($row->in_qty) : '' }}
                        </td>
                        <td class="p-2.5 border-r border-gray-900 text-center font-bold text-purple-700">
                            {{ $row->out_qty ? number_format($row->out_qty) : '' }}
                        </td>
                        <td class="p-2.5 border-r border-gray-900 text-center font-bold text-gray-900 bg-gray-50/50">
                            {{ number_format($row->balance_qty) }}
                        </td>
                        <td class="p-2.5 text-center text-xs text-gray-600">{{ $row->note }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-12 text-center text-gray-400 font-medium">
                            ยังไม่มีรายการรับ-จ่ายในสมุดบัญชีวัสดุประจำปีงบประมาณ พ.ศ. {{ $fiscalYearBE }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-between items-center text-xs text-gray-500 pt-2 border-t border-gray-200">
            <div>ยอดคงเหลือ ณ ปัจจุบัน: <span class="font-bold text-purple-700 text-sm">{{ number_format($material->stock_qty) }} {{ $material->unit }}</span> (ราคารับเข้าครั้งหลังสุด: ฿{{ number_format($material->unit_price ?? 0, 2) }})</div>
            <div>แบบฟอร์มคุมบัญชีวัสดุมาตรฐาน NAKHON ASSET (ปีงบประมาณ พ.ศ. {{ $fiscalYearBE }})</div>
        </div>
    </div>

    <!-- Modal ข้อควรทราบเกี่ยวกับบัญชีวัสดุ (8 ข้อ) -->
    <template x-teleport="body">
        <div x-show="showRulesModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="showRulesModal" @click="showRulesModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="showRulesModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full relative z-50">
                    <div class="bg-gradient-to-r from-amber-600 to-orange-600 px-6 py-4 text-white flex justify-between items-center">
                        <h3 class="font-bold text-lg flex items-center">
                            <i class="fas fa-book-reader mr-2"></i> ข้อควรทราบเกี่ยวกับบัญชีวัสดุ (ตามระเบียบพัสดุ)
                        </h3>
                        <button type="button" @click="showRulesModal = false" class="text-white/60 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                    </div>

                    <div class="p-6 space-y-3.5 text-sm text-gray-700 max-h-[70vh] overflow-y-auto custom-scrollbar leading-relaxed">
                        <div class="p-3 bg-amber-50 border-l-4 border-amber-500 rounded-r-xl">
                            <strong class="text-amber-900">1. การปิดบัญชีและการยกยอด:</strong> บัญชีวัสดุให้จัดทำแต่ละปีงบประมาณ (1 ต.ค. - 30 ก.ย.) เมื่อขึ้นปีงบประมาณใหม่ให้ขึ้นแผ่นใหม่ทุกครั้ง หากมีวัสดุคงเหลือให้ยกยอดคงเหลือจากปีก่อนเป็นยอดยกมาในปีปัจจุบัน
                        </div>
                        <div class="p-3 bg-gray-50 border-l-4 border-gray-400 rounded-r-xl">
                            <strong class="text-gray-900">2. การควบคุม 1 รายการ/บัญชี:</strong> บัญชีวัสดุแต่ละบัญชี (แต่ละประเภท/ชนิด) ให้ควบคุมวัสดุ 1 รายการ/ประเภท/ชนิด เท่านั้น
                        </div>
                        <div class="p-3 bg-gray-50 border-l-4 border-gray-400 rounded-r-xl">
                            <strong class="text-gray-900">3. การลงบัญชีตามระเบียบ:</strong> การลงบัญชีวัสดุ ให้ลงทุกครั้งที่มีการรับ หรือจ่ายตามระเบียบสำนักนายกรัฐมนตรีว่าด้วยการพัสดุ พ.ศ. 2535 และแก้ไขเพิ่มเติม ข้อ 152 – 154
                        </div>
                        <div class="p-3 bg-emerald-50 border-l-4 border-emerald-500 rounded-r-xl">
                            <strong class="text-emerald-900">4. ราคาต่อหน่วยรวม VAT:</strong> ราคาต่อหน่วย จะต้องเป็นราคาที่รวมภาษีมูลค่าเพิ่ม (VAT 7%) แล้ว
                        </div>
                        <div class="p-3 bg-gray-50 border-l-4 border-gray-400 rounded-r-xl">
                            <strong class="text-gray-900">5. การกำหนดหน่วยนับ:</strong> การกำหนดหน่วยนับของวัสดุ ควรพิจารณาให้เหมาะสมกับการเบิกจ่ายวัสดุของหน่วยงาน เช่น ดินสอ สามารถกำหนดหน่วยนับเป็นโหลหรือแท่งก็ได้ขึ้นอยู่กับจำนวนสั่งจ่ายของหน่วยงาน
                        </div>
                        <div class="p-3 bg-gray-50 border-l-4 border-gray-400 rounded-r-xl">
                            <strong class="text-gray-900">6. ความรอบคอบและเป็นปัจจุบัน:</strong> การลงบัญชีวัสดุ จะต้องกระทำด้วยความละเอียดรอบคอบ จำเป็นต้องรวดเร็ว ทันเวลา เพื่อให้ยอดวัสดุคงเหลือถูกต้องตามจริง
                        </div>
                        <div class="p-3 bg-purple-50 border-l-4 border-purple-500 rounded-r-xl">
                            <strong class="text-purple-900">7. หลักการตัดจ่าย FIFO และราคาคงเหลือสิ้นปี:</strong> กรณีที่ซื้อวัสดุชนิดเดียวกันในเวลาต่างกัน ราคาอาจไม่เท่ากัน เมื่อลงบัญชีจ่าย ให้ใช้ราคาวัสดุที่ซื้อมาก่อนตัดออกจากบัญชีก่อน (FIFO) ราคาวัสดุคงเหลือ ณ วันสิ้นปีงบประมาณจะเป็นราคาที่มีการจัดซื้อครั้งหลังสุด
                        </div>
                        <div class="p-3 bg-blue-50 border-l-4 border-blue-500 rounded-r-xl">
                            <strong class="text-blue-900">8. หลักการควบคุมภายใน (Segregation of Duties):</strong> กรณีมีบุคลากรเพียงพอ ควรแบ่งแยกหน้าที่ระหว่างผู้บันทึกบัญชีวัสดุ และผู้ควบคุมคลังพัสดุเป็นคนละคนกัน ตามหลักการควบคุมภายในที่ดี
                        </div>
                    </div>

                    <div class="bg-gray-50 px-6 py-3 border-t border-gray-100 flex justify-end">
                        <button type="button" @click="showRulesModal = false" class="px-5 py-2 bg-amber-600 text-white font-bold rounded-xl text-xs hover:bg-amber-700 transition-all">เข้าใจแล้ว ปิดหน้าต่าง</button>
                    </div>
                </div>
            </div>
        </div>
    </template>

    <!-- Stock-In Modal -->
    <template x-teleport="body">
        <div x-show="stockInModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="stockInModal" @click="stockInModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="stockInModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                    <form action="{{ route('stock-cards.stock-in', $material->id) }}" method="POST">
                        @csrf
                        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 text-white flex justify-between items-center">
                            <h3 class="font-bold text-lg flex items-center">
                                <i class="fas fa-plus-circle mr-2"></i> บันทึกรับวัสดุเข้าสต็อก
                            </h3>
                            <button type="button" @click="stockInModal = false" class="text-white/60 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="p-3 bg-emerald-50 text-emerald-800 rounded-xl text-xs font-medium border border-emerald-200">
                                💡 <span class="font-bold">ข้อสังเกต:</span> ราคาต่อหน่วยต้องเป็นราคารวมภาษีมูลค่าเพิ่ม (VAT 7%) แล้วตามข้อกำหนดที่ 4
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">วันที่รับเข้า <span class="text-red-500">*</span></label>
                                <input type="date" name="transaction_date" value="{{ date('Y-m-d') }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm font-semibold outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">รับจาก (บริษัท / ร้านค้า / ผู้ส่งมอบ) <span class="text-red-500">*</span></label>
                                <input type="text" name="party_name" required placeholder="ตัวอย่าง: ร้านสมใจการค้า หรือ บริษัท เอ็มพาร์ท จำกัด" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm font-semibold outline-none">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">เลขที่เอกสาร (PO / ใบส่งของ)</label>
                                    <input type="text" name="reference_doc" placeholder="เช่น PO-69001" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm font-mono font-semibold outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ราคาต่อหน่วย (บาท รวม VAT)</label>
                                    <input type="number" step="0.01" name="unit_price" value="{{ $material->unit_price }}" placeholder="0.00" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm font-bold text-right outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">จำนวนที่รับเข้า ({{ $material->unit }}) <span class="text-red-500">*</span></label>
                                <input type="number" name="quantity" min="1" required placeholder="กรอกจำนวน..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-base font-bold text-emerald-700 outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">หมายเหตุ</label>
                                <input type="text" name="note" placeholder="ระบุเพิ่มเติม (ถ้ามี)..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm outline-none">
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="stockInModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-semibold text-sm hover:bg-gray-100">ยกเลิก</button>
                            <button type="submit" class="px-6 py-2.5 bg-emerald-600 text-white font-bold rounded-xl text-sm shadow-md hover:bg-emerald-700 transition-all">ยืนยันบันทึกรับเข้า</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Card Settings Modal -->
    <template x-teleport="body">
        <div x-show="settingsModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="settingsModal" @click="settingsModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="settingsModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                    <form action="{{ route('stock-cards.update-settings', $material->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-gradient-to-r from-purple-700 to-indigo-700 px-6 py-4 text-white flex justify-between items-center">
                            <h3 class="font-bold text-lg flex items-center">
                                <i class="fas fa-cog mr-2"></i> ตั้งค่าการคุมบัญชีวัสดุ
                            </h3>
                            <button type="button" @click="settingsModal = false" class="text-white/60 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">รหัสพัสดุ (Material Code)</label>
                                    <input type="text" name="material_code" value="{{ $material->material_code }}" placeholder="เช่น 5520-001" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-mono font-semibold outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">สถานที่เก็บ (Bin / Shelf)</label>
                                    <input type="text" name="location_name" value="{{ $material->location_name }}" placeholder="เช่น ตู้ 1 ชั้น 2" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none">
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">จำนวนอย่างต่ำ (Min Stock)</label>
                                    <input type="number" name="min_stock" value="{{ $material->min_stock }}" min="0" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-bold text-center outline-none">
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">จำนวนอย่างสูง (Max Stock)</label>
                                    <input type="number" name="max_stock" value="{{ $material->max_stock }}" min="0" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-bold text-center outline-none">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ราคาต่อหน่วยมาตรฐาน (บาท รวม VAT)</label>
                                <input type="number" step="0.01" name="unit_price" value="{{ $material->unit_price }}" placeholder="0.00" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-bold text-right outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ขนาดหรือลักษณะ / สเปก</label>
                                <textarea name="specs" rows="2" placeholder="เช่น ขนาด A4 80 แกรม, บรรจุ 500 แผ่น/รีม..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none resize-none">{{ $material->specs }}</textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="settingsModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-semibold text-sm hover:bg-gray-100">ยกเลิก</button>
                            <button type="submit" class="px-6 py-2.5 bg-purple-700 text-white font-bold rounded-xl text-sm shadow-md hover:bg-purple-800 transition-all">บันทึกการตั้งค่า</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
