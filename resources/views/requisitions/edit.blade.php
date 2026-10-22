@extends('layouts.app')

@section('page_title', 'แก้ไขใบเบิกวัสดุ - ' . $requisition->requisition_code)
@section('page_description', 'แก้ไขข้อมูลใบเบิกวัสดุ รายการวัสดุ จำนวน และปรับปรุงยอดสต็อก/สมุดบัญชีวัสดุอัตโนมัติ')

@section('content')
@php
    // Calculate how much stock this requisition has already deducted per material
    $deductedMap = [];
    foreach ($requisition->items as $it) {
        if ($it->status === 'approved' && $it->approved_qty > 0) {
            $deductedMap[$it->material_id] = ($deductedMap[$it->material_id] ?? 0) + (int) $it->approved_qty;
        }
    }

    $initialItems = old('items');
    if (!$initialItems) {
        $initialItems = $requisition->items->values()->map(function ($it, $idx) {
            return [
                '_uid' => 'item_' . $it->id . '_' . $idx,
                'material_id' => (string) $it->material_id,
                'requested_qty' => (int) $it->requested_qty,
                'approved_qty' => $it->approved_qty !== null ? (int) $it->approved_qty : (int) $it->requested_qty,
                'status' => $it->status,
                'note' => $it->admin_note ?? $it->officer_note ?? '',
            ];
        })->toArray();
    } else {
        $initialItems = collect($initialItems)->values()->map(function ($it, $idx) {
            $it['_uid'] = 'old_' . $idx . '_' . uniqid();
            $it['material_id'] = (string) ($it['material_id'] ?? '');
            return $it;
        })->toArray();
    }

    $defaultItemStatus = $requisition->status === 'approved' ? 'approved' : 'pending';
@endphp

<div class="max-w-5xl mx-auto space-y-6" x-data="{
    items: @js($initialItems),
    defaultStatus: '{{ $defaultItemStatus }}',
    addItem() {
        if (this.items.length < 100) {
            this.items.push({
                _uid: 'new_' + Date.now() + '_' + Math.random().toString(36).slice(2, 7),
                material_id: '',
                requested_qty: 1,
                approved_qty: 1,
                status: this.defaultStatus,
                note: ''
            });
        } else {
            alert('สามารถเพิ่มรายการวัสดุได้สูงสุด 100 รายการต่อ 1 ใบเบิก');
        }
    },
    removeItem(index) {
        if (this.items.length > 1) {
            this.items.splice(index, 1);
        }
    },
    syncQty(item) {
        if (item.status === 'approved' || item.status === 'officer_approved') {
            item.approved_qty = item.requested_qty;
        }
    }
}">
    <!-- Top Navigation & Delete Bar -->
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
        <div class="flex items-center gap-3">
            <a href="{{ route('requisitions.show', $requisition->id) }}" class="text-gray-600 hover:text-gray-900 flex items-center transition-colors font-medium text-sm bg-white px-4 py-2 rounded-xl border border-gray-200 shadow-sm">
                <i class="fas fa-arrow-left mr-2"></i> ย้อนกลับไปหน้าใบเบิก
            </a>
            <a href="{{ route('requisitions.index') }}" class="text-gray-500 hover:text-purple-700 text-sm font-medium">
                รายการใบขอเบิกทั้งหมด
            </a>
        </div>

        <form action="{{ route('requisitions.destroy', $requisition->id) }}" method="POST" onsubmit="return confirm('ยืนยันการลบใบเบิกวัสดุเลขที่ {{ $requisition->requisition_code }} ?\n\nหากใบเบิกนี้มีการตัดจ่ายสต็อกไปแล้ว ระบบจะทำการคืนยอดวัสดุกลับเข้าคลังและยกเลิกรายการในสมุดบัญชีวัสดุให้อัตโนมัติ');">
            @csrf
            @method('DELETE')
            <button type="submit" class="bg-rose-50 hover:bg-rose-600 text-rose-600 hover:text-white border border-rose-200 px-4 py-2 rounded-xl font-bold text-xs transition-all flex items-center shadow-sm">
                <i class="fas fa-trash-alt mr-2"></i> ลบใบเบิกนี้ (คืนสต็อกอัตโนมัติ)
            </button>
        </form>
    </div>

    <!-- Main Edit Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-100 bg-gradient-to-r from-purple-900 to-indigo-900 text-white flex justify-between items-center">
            <div>
                <h2 class="text-lg font-bold flex items-center">
                    <i class="fas fa-edit mr-2.5 text-amber-400"></i> แก้ไขใบเบิกวัสดุ ({{ $requisition->requisition_code }})
                </h2>
                <p class="text-xs text-purple-200 mt-0.5">
                    เมื่อบันทึกการแก้ไข ระบบจะคำนวณปรับยอดสต็อกคงคลังและอัปเดตสมุดบัญชีวัสดุให้ตรงกันโดยอัตโนมัติ
                </p>
            </div>
            <span class="px-3 py-1 rounded-full text-xs font-bold bg-white/15 border border-white/20">
                @if($requisition->status === 'approved')
                    อนุมัติครบถ้วนแล้ว
                @elseif($requisition->status === 'officer_approved')
                    รอหัวหน้าพัสดุอนุมัติ
                @elseif($requisition->status === 'partial')
                    อนุมัติบางส่วน
                @elseif($requisition->status === 'rejected')
                    ปฏิเสธ
                @else
                    รออนุมัติ
                @endif
            </span>
        </div>

        <form action="{{ route('requisitions.update', $requisition->id) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Section 1: Header Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 bg-purple-50/40 p-5 rounded-2xl border border-purple-100">
                @if($isStaff)
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            เลขที่ใบเบิก <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="requisition_code" value="{{ old('requisition_code', $requisition->requisition_code) }}" required
                               class="w-full px-3.5 py-2.5 bg-white border border-purple-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-bold text-purple-800 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            วันที่ขอเบิก / วันที่ตัดจ่าย <span class="text-rose-500">*</span>
                        </label>
                        <input type="date" name="requisition_date" value="{{ old('requisition_date', $requisition->created_at->format('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold text-gray-700 outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            ชื่อผู้ขอเบิก <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="requester_name" list="edit_requesters_list"
                               value="{{ old('requester_name', $requisition->requester_display_name !== '-' ? $requisition->requester_display_name : '') }}"
                               required autocomplete="off" placeholder="พิมพ์ชื่อ หรือเลือกจากรายการ..."
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold text-gray-800 outline-none">
                        <datalist id="edit_requesters_list">
                            @foreach($pastRequesters as $reqName)
                                <option value="{{ $reqName }}"></option>
                            @endforeach
                        </datalist>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">
                            กลุ่มงาน / หน่วยงาน
                        </label>
                        <select name="department_id" class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold text-gray-700 outline-none">
                            <option value="">-- ไม่ระบุกลุ่มงาน --</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept->id }}" {{ old('department_id', $requisition->department_id) == $dept->id ? 'selected' : '' }}>
                                    {{ $dept->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @else
                    <div>
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">เลขที่ใบเบิก</label>
                        <div class="px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm font-bold text-purple-800">
                            {{ $requisition->requisition_code }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1.5">วันที่ขอเบิก <span class="text-rose-500">*</span></label>
                        <input type="date" name="requisition_date" value="{{ old('requisition_date', $requisition->created_at->format('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold text-gray-700 outline-none">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-xs font-bold text-gray-500 mb-1.5">ผู้ขอเบิก / กลุ่มงาน</label>
                        <div class="px-3.5 py-2.5 bg-gray-100 border border-gray-200 rounded-xl text-sm font-semibold text-gray-700">
                            {{ $requisition->requester_display_name }} ({{ $requisition->department->name ?? '-' }})
                        </div>
                    </div>
                @endif

                <div class="md:col-span-2 lg:col-span-4">
                    <label class="block text-xs font-bold text-gray-700 mb-1.5">
                        เหตุผลความจำเป็นในการขอเบิก {{ !$isStaff ? '*' : '' }}
                    </label>
                    <input type="text" name="reason_for_request" value="{{ old('reason_for_request', $requisition->reason_for_request) }}"
                           {{ !$isStaff ? 'required' : '' }}
                           placeholder="เช่น เพื่อใช้ในการปฏิบัติงานตามภารกิจของกลุ่มงาน..."
                           class="w-full px-3.5 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm outline-none">
                </div>
            </div>

            <!-- Section 2: Items Table -->
            <div class="space-y-3">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2">
                    <div>
                        <h3 class="text-sm font-bold text-gray-800 flex items-center">
                            <i class="fas fa-boxes text-purple-600 mr-2"></i> รายการวัสดุในใบเบิก (<span x-text="items.length"></span> รายการ)
                        </h3>
                        <p class="text-xs text-gray-500">สามารถเปลี่ยนชนิดวัสดุ แก้ไขจำนวน เพิ่มรายการใหม่ หรือลบรายการออกได้</p>
                    </div>
                    <button type="button" @click="addItem()"
                            class="bg-purple-50 hover:bg-purple-100 text-purple-700 border border-purple-200 px-3.5 py-2 rounded-xl text-xs font-bold transition-all flex items-center shadow-sm">
                        <i class="fas fa-plus mr-1.5"></i> เพิ่มรายการวัสดุ
                    </button>
                </div>

                <div class="space-y-3">
                    <template x-for="(item, index) in items" :key="item._uid">
                        <div class="p-4 bg-gray-50/80 border border-gray-200 rounded-2xl space-y-3 hover:border-purple-200 transition-all">
                            <div class="grid grid-cols-1 md:grid-cols-12 gap-3 items-end">
                                <!-- Material Select -->
                                <div class="{{ $isStaff ? 'md:col-span-5' : 'md:col-span-8' }}">
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">
                                        รายการวัสดุที่ <span x-text="index + 1"></span> <span class="text-rose-500">*</span>
                                    </label>
                                    <select :name="'items[' + index + '][material_id]'" x-model="item.material_id" required
                                            class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-xs font-semibold text-gray-800 outline-none">
                                        <option value="">-- เลือกวัสดุ --</option>
                                        @foreach($materials as $mat)
                                            @php
                                                $alreadyDeducted = $deductedMap[$mat->id] ?? 0;
                                                $maxAvail = $mat->stock_qty + $alreadyDeducted;
                                            @endphp
                                            <option value="{{ $mat->id }}" :selected="String(item.material_id) === '{{ $mat->id }}'">
                                                [{{ $mat->material_code }}] {{ $mat->name }} (คงเหลือในคลัง: {{ number_format($mat->stock_qty) }}{{ $alreadyDeducted > 0 ? ' + เดิมในใบนี้ ' . number_format($alreadyDeducted) . ' = เบิกได้สูงสุด ' . number_format($maxAvail) : '' }} {{ $mat->unit }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Requested Qty -->
                                <div class="{{ $isStaff ? 'md:col-span-2' : 'md:col-span-3' }}">
                                    <label class="block text-[11px] font-bold text-gray-600 mb-1">จำนวนขอเบิก <span class="text-rose-500">*</span></label>
                                    <input type="number" :name="'items[' + index + '][requested_qty]'" x-model.number="item.requested_qty" @input="syncQty(item)" min="1" required
                                           class="w-full px-3 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-xs font-bold text-center text-gray-800 outline-none">
                                </div>

                                @if($isStaff)
                                    <!-- Approved Qty -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-emerald-700 mb-1">จำนวนอนุมัติ/จ่ายจริง</label>
                                        <input type="number" :name="'items[' + index + '][approved_qty]'" x-model.number="item.approved_qty" min="0"
                                               :disabled="item.status === 'rejected' || item.status === 'pending'"
                                               class="w-full px-3 py-2 bg-white border border-emerald-200 rounded-xl focus:ring-2 focus:ring-emerald-500 text-xs font-bold text-center text-emerald-700 outline-none disabled:bg-gray-100 disabled:text-gray-400">
                                    </div>

                                    <!-- Status -->
                                    <div class="md:col-span-2">
                                        <label class="block text-[11px] font-bold text-gray-600 mb-1">สถานะรายการ</label>
                                        <select :name="'items[' + index + '][status]'" x-model="item.status" @change="syncQty(item)"
                                                class="w-full px-2.5 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-xs font-bold outline-none">
                                            <option value="approved">✓ อนุมัติจ่าย (ตัดสต็อก)</option>
                                            <option value="officer_approved">🟦 รอหัวหน้าอนุมัติ</option>
                                            <option value="pending">⏳ รอเจ้าหน้าที่พัสดุ</option>
                                            <option value="rejected">✕ ไม่อนุมัติ</option>
                                        </select>
                                    </div>
                                @endif

                                <!-- Remove Button -->
                                <div class="md:col-span-1 flex justify-end">
                                    <button type="button" @click="removeItem(index)" x-show="items.length > 1"
                                            class="w-9 h-9 rounded-xl bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-all flex items-center justify-center border border-rose-100" title="ลบรายการนี้">
                                        <i class="fas fa-trash-alt text-xs"></i>
                                    </button>
                                </div>
                            </div>

                            @if($isStaff)
                                <div>
                                    <input type="text" :name="'items[' + index + '][note]'" x-model="item.note"
                                           placeholder="หมายเหตุรายการ (ถ้ามี เช่น เหตุผลปรับลดจำนวน หรือบันทึกเพิ่มเติม)..."
                                           class="w-full px-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs text-gray-600 outline-none focus:ring-1 focus:ring-purple-400">
                                </div>
                            @endif
                        </div>
                    </template>
                </div>

                <div class="pt-1 flex justify-center">
                    <button type="button" @click="addItem()" :disabled="items.length >= 100" :class="{'opacity-50 cursor-not-allowed': items.length >= 100}"
                            class="w-full py-2.5 border-2 border-dashed border-purple-200 hover:border-purple-400 bg-purple-50/40 hover:bg-purple-50 text-purple-700 rounded-xl text-xs font-bold transition-all flex items-center justify-center">
                        <i class="fas fa-plus mr-1.5"></i> เพิ่มรายการวัสดุแถวถัดไป (<span x-text="items.length"></span>/100 รายการ)
                    </button>
                </div>
            </div>

            <!-- Submit Footer -->
            <div class="pt-4 border-t border-gray-100 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="text-xs text-gray-500 flex items-center">
                    <i class="fas fa-info-circle text-purple-500 mr-2 text-sm"></i>
                    <span>รายการที่มีสถานะ <strong>"อนุมัติจ่าย (ตัดสต็อก)"</strong> จะถูกนำไปตัดยอดคงเหลือและบันทึกลงสมุดบัญชีวัสดุอัตโนมัติ</span>
                </div>

                <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                    <a href="{{ route('requisitions.show', $requisition->id) }}" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl font-bold text-xs transition-all">
                        ยกเลิก
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white rounded-xl font-bold text-xs shadow-md shadow-purple-500/20 transition-all flex items-center">
                        <i class="fas fa-save mr-2"></i> บันทึกการแก้ไขใบเบิก
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection
