@extends('layouts.app')

@section('page_title', 'ใบเบิกพัสดุ')
@section('page_description', 'รายละเอียดพิจารณาอนุมัติทีละรายการ และเอกสารใบเบิกพัสดุทางการ (ขนาด A4)')

@section('content')
<div class="max-w-4xl mx-auto" x-data="{ approveModal: false, rejectModal: false, selectedItem: null, approvedQty: 1, adminNote: '' }">
    <div class="mb-5 flex justify-between items-center">
        <a href="{{ route('requisitions.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center transition-colors font-medium text-sm">
            <i class="fas fa-arrow-left mr-2"></i> ย้อนกลับไปรายการขอเบิก
        </a>
        <a href="{{ route('requisitions.print', $requisition->id) }}" target="_blank" class="bg-gradient-to-r from-emerald-600 to-teal-600 text-white hover:from-emerald-700 hover:to-teal-700 px-5 py-2.5 rounded-xl shadow-md font-bold text-sm flex items-center transition-all">
            <i class="fas fa-print mr-2"></i> พิมพ์ใบเบิกพัสดุ A4 (พิมพ์ตามแบบมาตรฐาน)
        </a>
    </div>

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

        $approvedAt = $requisition->approved_at;
        $appDay = '';
        $appMonth = '';
        $appYear = '';
        if ($approvedAt) {
            $appDate = \Carbon\Carbon::parse($approvedAt);
            $appDay = $appDate->format('j');
            $appMonth = $thMonths[$appDate->month] ?? '';
            $appYear = $appDate->year + 543;
        }
    @endphp

    <!-- Official Document Preview Card (Exact A4 Form Design) -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 md:p-14 relative overflow-hidden" style="font-family: 'Sarabun', sans-serif;">
        <!-- Header status badge -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-6 mb-6">
            <div>
                <h2 class="text-2xl font-bold text-gray-900 tracking-wide">ใบเบิกวัสดุ</h2>
                <p class="text-xs text-gray-500 mt-1">สำนักงาน สกร.ประจำจังหวัดนครศรีฯ</p>
            </div>
            <div class="text-right">
                <div class="text-sm font-bold text-purple-700">เลขที่ {{ $requisition->requisition_code }}</div>
                <div class="mt-2">
                    @if($requisition->status == 'pending')
                        <span class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-xs font-bold border border-amber-200">⏳ รอพิจารณา</span>
                    @elseif($requisition->status == 'partial')
                        <span class="bg-blue-50 text-blue-600 px-3 py-1 rounded-full text-xs font-bold border border-blue-200">🔄 อนุมัติแล้วบางส่วน</span>
                    @elseif($requisition->status == 'approved')
                        <span class="bg-emerald-50 text-emerald-600 px-3 py-1 rounded-full text-xs font-bold border border-emerald-200">✅ อนุมัติครบถ้วน</span>
                    @else
                        <span class="bg-rose-50 text-rose-600 px-3 py-1 rounded-full text-xs font-bold border border-rose-200">❌ ไม่อนุมัติ</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Form Top Info -->
        <div class="space-y-3 text-sm text-gray-800 mb-8 leading-relaxed">
            <div class="flex flex-wrap items-baseline gap-2">
                <span class="font-medium text-gray-600">ข้าพเจ้าขอเบิกสิ่งของตามรายการต่อไปนี้ เพื่อใช้ในการ:</span>
                <span class="font-bold border-b border-dotted border-gray-400 px-2 flex-1">{{ $requisition->reason_for_request ?? 'การปฏิบัติงานตามภารกิจหน่วยงาน' }}</span>
            </div>
        </div>

        <!-- Official Form Table (Multi-Item with Individual Approval Controls) -->
        <div class="border border-gray-900 rounded-lg overflow-hidden mb-8">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-900 text-xs font-bold text-gray-800 text-center">
                        <th class="p-2.5 border-r border-gray-900 w-12">ลำดับ</th>
                        <th class="p-2.5 border-r border-gray-900">รายการวัสดุ</th>
                        <th class="p-2.5 border-r border-gray-900 w-24">ขอเบิก</th>
                        <th class="p-2.5 border-r border-gray-900 w-28">จ่ายจริง</th>
                        <th class="p-2.5 border-r border-gray-900 w-28">สถานะรายการ</th>
                        <th class="p-2.5 w-44">หมายเหตุ / การดำเนินการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-300">
                    @foreach($requisition->items as $index => $item)
                    <tr class="text-gray-800 font-medium">
                        <td class="p-3 border-r border-gray-900 text-center font-bold">{{ $index + 1 }}</td>
                        <td class="p-3 border-r border-gray-900 font-bold text-gray-900">
                            {{ $item->material->name ?? '-' }}
                            <div class="text-[11px] font-normal text-gray-400">คงเหลือสต็อก: {{ number_format($item->material->stock_qty ?? 0) }} {{ $item->material->unit ?? '' }}</div>
                        </td>
                        <td class="p-3 border-r border-gray-900 text-center font-bold text-purple-700">
                            {{ number_format($item->requested_qty) }} <span class="text-xs font-normal text-gray-500">{{ $item->material->unit ?? '' }}</span>
                        </td>
                        <td class="p-3 border-r border-gray-900 text-center font-bold">
                            @if($item->status == 'approved')
                                <span class="text-emerald-600 text-base">{{ number_format($item->approved_qty) }}</span>
                                <span class="text-xs font-normal text-gray-500">{{ $item->material->unit ?? '' }}</span>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="p-3 border-r border-gray-900 text-center whitespace-nowrap">
                            @if($item->status == 'pending')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-200">⏳ รอพิจารณา</span>
                            @elseif($item->status == 'approved')
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-200">✅ อนุมัติแล้ว</span>
                            @else
                                <span class="px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200">❌ ปฏิเสธ</span>
                            @endif
                        </td>
                        <td class="p-3 text-center text-xs">
                            @if($item->status == 'pending')
                                @hasanyrole('admin|procurement')
                                <div class="flex items-center justify-center gap-1">
                                    <button type="button" @click="selectedItem = {{ json_encode($item) }}; approvedQty = {{ $item->requested_qty }}; adminNote = ''; approveModal = true;" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm transition-all flex items-center">
                                        <i class="fas fa-check mr-1"></i> อนุมัติ
                                    </button>
                                    <button type="button" @click="selectedItem = {{ json_encode($item) }}; adminNote = ''; rejectModal = true;" class="px-2.5 py-1 rounded-lg bg-rose-600 hover:bg-rose-700 text-white font-bold text-xs shadow-sm transition-all flex items-center">
                                        <i class="fas fa-times mr-1"></i> ปฏิเสธ
                                    </button>
                                </div>
                                @else
                                <span class="text-gray-400">รอเจ้าหน้าที่พิจารณา</span>
                                @endhasanyrole
                            @else
                                <span class="text-gray-600">{{ $item->admin_note ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Bottom Signatures Grid -->
        <div class="pt-6 border-t border-gray-200 text-xs text-gray-800 grid grid-cols-2 gap-8 leading-relaxed">
            <div class="space-y-2 flex flex-col items-start">
                <div style="margin-left: 20px;">มีเบิกให้ <span class="font-bold border-b border-dotted border-gray-400 px-3">{{ $requisition->items->where('status', 'approved')->count() }}</span> รายการ</div>
                <div style="margin-left: 20px;">ค้างเบิก <span class="font-bold border-b border-dotted border-gray-400 px-3">{{ $requisition->items->where('status', '!=', 'approved')->count() ?: '-' }}</span> รายการ</div>
                
                <!-- เจ้าหน้าที่จ่าย (Centered Block - No Position) -->
                <div class="mt-4 flex flex-col items-center w-72 text-center leading-tight">
                    <div>
                        (ลงชื่อ) <span style="position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 140px; vertical-align: bottom;" class="font-bold border-b border-dotted border-gray-400">
                            @if($requisition->approver && $requisition->approver->signature)
                                <img src="{{ asset('storage/' . $requisition->approver->signature) }}" style="position: absolute; bottom: 2px; height: 42px; max-width: 120px; object-fit: contain; pointer-events: none;" alt="Signature">
                            @endif
                            &nbsp;
                        </span> เจ้าหน้าที่จ่าย
                    </div>
                    <div class="mt-0.5 w-full text-center">( {{ $requisition->approver->name ?? '........................................................' }} )</div>
                </div>

                <div class="pt-6 pl-5 font-bold text-gray-900">อนุญาตให้เบิกได้</div>
                
                <!-- ผู้สั่งจ่าย (Centered Block) -->
                <div class="mt-2 flex flex-col items-center w-72 text-center leading-tight">
                    <div>(ลงชื่อ) ........................................................ ผู้สั่งจ่าย</div>
                    <div class="mt-0.5 w-full text-center">( ........................................................ )</div>
                    <div class="mt-0.5 w-full text-center">ตำแหน่ง <span class="font-bold border-b border-dotted border-gray-400 px-4">หัวหน้าเจ้าหน้าที่</span></div>
                </div>
                
                <div class="text-gray-500 pl-5 mt-3">วันที่ {{ $appDay ?: '.....' }} เดือน {{ $appMonth ?: '...................' }} พ.ศ. {{ $appYear ?: '..........' }}</div>
            </div>

            <div class="space-y-2 flex flex-col items-end">
                <div class="flex flex-col items-start mr-5">
                    <!-- ผู้เบิก (Centered Block + มอบให้ผู้รับแทน) -->
                    <div class="flex flex-col items-center w-72 text-center leading-tight">
                        <div>
                            (ลงชื่อ) <span style="position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 180px; vertical-align: bottom;" class="font-bold border-b border-dotted border-gray-400">
                                @if($requisition->user && $requisition->user->signature)
                                    <img src="{{ asset('storage/' . $requisition->user->signature) }}" style="position: absolute; bottom: 2px; height: 42px; max-width: 160px; object-fit: contain; pointer-events: none;" alt="Signature">
                                @endif
                                &nbsp;
                            </span> ผู้เบิก
                        </div>
                        <div class="mt-0.5 w-full text-center">( {{ $requisition->user->name ?? '........................................................' }} )</div>
                        <div class="mt-0.5 w-full text-center">ตำแหน่ง ........................................................</div>
                        <div class="mt-1 w-full text-center">ได้มอบให้ <span class="font-bold border-b border-dotted border-gray-400 px-3 inline-block" style="min-width: 170px;"></span> เป็นผู้รับของแทน</div>
                        <div class="mt-0.5 w-full text-center">(ลงชื่อ) <span class="font-bold border-b border-dotted border-gray-400 px-4">........................................................</span> ผู้รับมอบ</div>
                    </div>

                    <div class="pt-5 pl-5 font-bold text-gray-900">ได้รับของครบถ้วนถูกต้องแล้ว</div>
                    
                    <!-- ผู้รับของ (Centered Block) -->
                    <div class="mt-2 flex flex-col items-center w-72 text-center leading-tight">
                        <div>
                            (ลงชื่อ) <span style="position: relative; display: inline-flex; align-items: center; justify-content: center; min-width: 180px; vertical-align: bottom;" class="font-bold border-b border-dotted border-gray-400">
                                @if($requisition->user && $requisition->user->signature && in_array($requisition->status, ['approved', 'partial']))
                                    <img src="{{ asset('storage/' . $requisition->user->signature) }}" style="position: absolute; bottom: 2px; height: 42px; max-width: 160px; object-fit: contain; pointer-events: none;" alt="Signature">
                                @endif
                                &nbsp;
                            </span> ผู้รับของ
                        </div>
                        <div class="mt-0.5 w-full text-center">( {{ $requisition->user->name ?? '........................................................' }} )</div>
                        <div class="mt-0.5 w-full text-center">ตำแหน่ง ........................................................</div>
                    </div>
                    
                    <div class="text-gray-500 pl-5 mt-3">วันที่ {{ $appDay ?: $reqDay }} เดือน {{ $appMonth ?: $reqMonth }} พ.ศ. {{ $appYear ?: $reqYear }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Item Approve Modal -->
    <template x-teleport="body">
        <div x-show="approveModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="approveModal" @click="approveModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="approveModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                    <form x-bind:action="'/requisitions/items/' + (selectedItem ? selectedItem.id : '') + '/approve'" method="POST">
                        @csrf
                        <div class="bg-gradient-to-r from-emerald-600 to-teal-600 px-6 py-4 text-white flex justify-between items-center">
                            <h3 class="font-bold text-lg flex items-center">
                                <i class="fas fa-check-circle mr-2"></i> อนุมัติรายการวัสดุ
                            </h3>
                            <button type="button" @click="approveModal = false" class="text-white/60 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="p-4 bg-emerald-50 border border-emerald-100 rounded-xl">
                                <div class="text-xs text-emerald-800 font-bold uppercase mb-1">รายการวัสดุที่พิจารณา</div>
                                <div class="text-sm font-bold text-gray-800" x-text="selectedItem && selectedItem.material ? selectedItem.material.name : ''"></div>
                                <div class="text-xs text-gray-600 mt-1">
                                    จำนวนที่ขอเบิก: <span class="font-bold text-purple-700 text-sm" x-text="selectedItem ? selectedItem.requested_qty : ''"></span>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">จำนวนที่อนุมัติให้เบิกจริง <span class="text-red-500">*</span></label>
                                <input type="number" name="approved_qty" x-model="approvedQty" min="1" required class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-base font-bold text-emerald-700 outline-none">
                                <p class="text-[11px] text-gray-500 mt-1">* หากปรับลดจำนวน จำเป็นต้องระบุเหตุผลในช่องหมายเหตุ</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">หมายเหตุ / เหตุผลการอนุมัติ</label>
                                <textarea name="admin_note" x-model="adminNote" rows="3" placeholder="ระบุเหตุผลเพิ่มเติม หรือเหตุผลในการปรับลดจำนวนอนุมัติ..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:bg-white text-sm font-medium outline-none resize-none"></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="approveModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-semibold text-sm hover:bg-gray-100">ยกเลิก</button>
                            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 text-white font-bold rounded-xl text-sm shadow-md hover:from-emerald-700 hover:to-teal-700">ยืนยันการอนุมัติรายการนี้</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Item Reject Modal -->
    <template x-teleport="body">
        <div x-show="rejectModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="rejectModal" @click="rejectModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="rejectModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                    <form x-bind:action="'/requisitions/items/' + (selectedItem ? selectedItem.id : '') + '/reject'" method="POST">
                        @csrf
                        <div class="bg-gradient-to-r from-rose-600 to-red-600 px-6 py-4 text-white flex justify-between items-center">
                            <h3 class="font-bold text-lg flex items-center">
                                <i class="fas fa-times-circle mr-2"></i> ปฏิเสธรายการวัสดุ
                            </h3>
                            <button type="button" @click="rejectModal = false" class="text-white/60 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div class="p-4 bg-rose-50 border border-rose-100 rounded-xl">
                                <div class="text-xs text-rose-800 font-bold uppercase mb-1">รายการวัสดุที่ปฏิเสธ</div>
                                <div class="text-sm font-bold text-gray-800" x-text="selectedItem && selectedItem.material ? selectedItem.material.name : ''"></div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ระบุเหตุผลที่ไม่สามารถอนุมัติได้ <span class="text-red-500">*</span></label>
                                <textarea name="admin_note" x-model="adminNote" rows="3" required placeholder="เช่น สินค้าหมดสต็อกชั่วคราว, วัตถุประสงค์ไม่สอดคล้อง..." class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-rose-500 focus:bg-white text-sm font-medium outline-none resize-none"></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="rejectModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-semibold text-sm hover:bg-gray-100">ยกเลิก</button>
                            <button type="submit" class="px-6 py-2.5 bg-rose-600 text-white font-bold rounded-xl text-sm shadow-md hover:bg-rose-700">ยืนยันปฏิเสธรายการนี้</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
