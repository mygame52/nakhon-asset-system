@extends('layouts.app')

@section('page_title', 'ประวัติและรายการรับวัสดุเข้าคลัง')
@section('page_description', 'ตรวจสอบ แก้ไข หรือยกเลิกรายการรับวัสดุเข้า พร้อมบันทึกร่องรอยผู้รับผิดชอบและเหตุผลอย่างสมบูรณ์')

@section('content')
<div class="space-y-6" x-data="{
    editModalOpen: false,
    deleteModalOpen: false,
    activeReceipt: {},
    editForm: {
        id: null,
        transaction_date: '',
        quantity: 1,
        unit_price: 0,
        party_name: '',
        reference_doc: '',
        note: '',
        edit_reason: ''
    },
    deleteForm: {
        id: null,
        material_name: '',
        quantity: 0,
        unit: '',
        delete_reason: ''
    },
    openEditModal(receipt) {
        this.activeReceipt = receipt;
        this.editForm.id = receipt.id;
        this.editForm.transaction_date = receipt.transaction_date ? receipt.transaction_date.substring(0, 10) : '';
        this.editForm.quantity = receipt.quantity;
        this.editForm.unit_price = receipt.unit_price || 0;
        this.editForm.party_name = receipt.party_name || '';
        this.editForm.reference_doc = receipt.reference_doc || '';
        this.editForm.note = receipt.note || '';
        this.editForm.edit_reason = '';
        this.editModalOpen = true;
    },
    openDeleteModal(receipt) {
        this.activeReceipt = receipt;
        this.deleteForm.id = receipt.id;
        this.deleteForm.material_name = receipt.material ? receipt.material.name : 'วัสดุ #' + receipt.item_id;
        this.deleteForm.quantity = receipt.quantity;
        this.deleteForm.unit = receipt.material ? receipt.material.unit : 'หน่วย';
        this.deleteForm.delete_reason = '';
        this.deleteModalOpen = true;
    }
}">

    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100">
        <a href="{{ route('materials.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-boxes"></i> รายการวัสดุคงคลัง
        </a>
        <a href="{{ route('requisitions.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-file-signature"></i> ใบขอเบิกวัสดุ
        </a>
        @hasanyrole('admin|procurement')
        <a href="{{ route('stock-cards.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-book"></i> สมุดบัญชีวัสดุ
        </a>
        <a href="{{ route('material-receipts.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm rounded-xl transition-all flex items-center justify-center gap-2 bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-md">
            <i class="fas fa-file-import"></i> รายการรับวัสดุเข้า
        </a>
        @endhasanyrole
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center justify-between shadow-sm">
            <div class="flex items-center font-bold">
                <i class="fas fa-check-circle mr-2 text-emerald-600 text-lg"></i> {{ session('success') }}
            </div>
            <button @click="$el.parentElement.remove()" class="text-emerald-500 hover:text-emerald-700">
                <i class="fas fa-times"></i>
            </button>
        </div>
    @endif

    @if($errors->any())
        <div class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
            <div class="flex items-center font-bold mb-2">
                <i class="fas fa-exclamation-circle mr-2"></i> ข้อผิดพลาดในการบันทึกข้อมูล:
            </div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Header Title Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-emerald-700 to-teal-600 flex items-center">
                <i class="fas fa-file-import mr-3 text-emerald-600"></i> รายการรับวัสดุเข้าคลังและประวัติการปรับปรุง
            </h2>
            <p class="text-sm text-gray-500 mt-1">บันทึกคุมการรับวัสดุเข้าสต็อก รองรับการแก้ไขและยกเลิกรายการพร้อมการบันทึกร่องรอยผู้ดำเนินการ (Audit Trail)</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('stock-cards.index') }}" class="px-4 py-2.5 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white font-bold rounded-xl text-sm transition-all shadow-md shadow-emerald-500/20 flex items-center">
                <i class="fas fa-plus-circle mr-2"></i> บันทึกรับเข้าสต็อกใหม่
            </a>
        </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Inward Value -->
        <div class="bg-gradient-to-br from-emerald-50 to-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">มูลค่ารับเข้ารวมทั้งหมด</p>
                <h3 class="text-2xl font-extrabold text-emerald-950 mt-1">฿{{ number_format($stats['total_value'], 2) }}</h3>
                <p class="text-xs text-emerald-600 mt-1 font-medium">รวมรายการรับเข้าที่มีผลสมบูรณ์</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-500/20">
                <i class="fas fa-coins"></i>
            </div>
        </div>

        <!-- Receipts This Month -->
        <div class="bg-gradient-to-br from-indigo-50 to-white p-5 rounded-2xl border border-indigo-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider">รายการรับเข้าเดือนนี้</p>
                <h3 class="text-2xl font-extrabold text-indigo-950 mt-1">{{ number_format($stats['this_month_receipts']) }}</h3>
                <p class="text-xs text-indigo-500 mt-1">ครั้งบันทึกรับเข้าคลังประจำเดือน</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-md shadow-indigo-500/20">
                <i class="fas fa-calendar-check"></i>
            </div>
        </div>

        <!-- Total Active -->
        <div class="bg-gradient-to-br from-blue-50 to-white p-5 rounded-2xl border border-blue-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-blue-600 uppercase tracking-wider">รายการรับเข้าปกติ (Active)</p>
                <h3 class="text-2xl font-extrabold text-blue-950 mt-1">{{ number_format($stats['total_active']) }}</h3>
                <p class="text-xs text-blue-500 mt-1">รายการพร้อมสมุดคุมวัสดุ</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white flex items-center justify-center text-xl shadow-md shadow-blue-500/20">
                <i class="fas fa-check-double"></i>
            </div>
        </div>

        <!-- Total Deleted / Cancelled -->
        <div class="bg-gradient-to-br from-rose-50 to-white p-5 rounded-2xl border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-rose-600 uppercase tracking-wider">รายการที่ถูกยกเลิก/ลบ</p>
                <h3 class="text-2xl font-extrabold text-rose-950 mt-1">{{ number_format($stats['total_deleted']) }}</h3>
                <p class="text-xs text-rose-500 mt-1">รายการลบพร้อมร่องรอยประวัติ</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-600 text-white flex items-center justify-center text-xl shadow-md shadow-rose-500/20">
                <i class="fas fa-trash-alt"></i>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('material-receipts.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            
            <!-- Material Filter -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">รายการวัสดุ</label>
                <select name="material_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <option value="">-- พัสดุทั้งหมด --</option>
                    @foreach($materials as $m)
                        <option value="{{ $m->id }}" {{ request('material_id') == $m->id ? 'selected' : '' }}>
                            [{{ $m->material_code ?? 'MAT-' . sprintf('%04d', $m->id) }}] {{ $m->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">สถานะรายการ</label>
                <select name="status_filter" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none font-medium">
                    <option value="all" {{ request('status_filter', 'all') == 'all' ? 'selected' : '' }}>แสดงรายการทั้งหมด (รวมที่ลบ)</option>
                    <option value="active" {{ request('status_filter') == 'active' ? 'selected' : '' }}>เฉพาะรายการปกติ (Active)</option>
                    <option value="deleted" {{ request('status_filter') == 'deleted' ? 'selected' : '' }}>เฉพาะรายการที่ยกเลิก/ลบ (Deleted)</option>
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">ตั้งแต่วันที่</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">ถึงวันที่</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
            </div>

            <!-- Search Input Group -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">ค้นหาเลขที่/ร้านค้า/เหตุผล</label>
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="คำค้นหา..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-emerald-500 outline-none">
                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-md flex items-center shrink-0">
                        <i class="fas fa-search mr-1"></i> กรอง
                    </button>
                    @if(request()->hasAny(['material_id', 'status_filter', 'search', 'date_from', 'date_to']))
                        <a href="{{ route('material-receipts.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-xl text-sm transition-all flex items-center shrink-0" title="ล้างการกรอง">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Main Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-list-alt text-emerald-600 mr-2"></i> ประวัติรายการรับวัสดุเข้าคลังทั้งหมด ({{ number_format($receipts->total()) }} รายการ)
            </h3>
            <span class="text-xs text-gray-400 font-medium">เรียงตามวันที่รับเข้าล่าสุด</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">วันที่รับเข้า</th>
                        <th class="py-3.5 px-4">รหัส & รายการวัสดุ</th>
                        <th class="py-3.5 px-4 text-center">จำนวนรับเข้า</th>
                        <th class="py-3.5 px-4 text-right">ราคาต่อหน่วย (บาท)</th>
                        <th class="py-3.5 px-4 text-right">มูลค่ารวม (บาท)</th>
                        <th class="py-3.5 px-4">ผู้จัดส่ง / ร้านค้า / เอกสาร</th>
                        <th class="py-3.5 px-6">ร่องรอยการบันทึก & ผู้แก้ไข/ลบ</th>
                        <th class="py-3.5 px-6 text-center">จัดการ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($receipts as $rc)
                        @php
                            $isDeleted = $rc->trashed();
                            $material = $rc->material;
                        @endphp
                        <tr class="hover:bg-gray-50/50 transition-colors {{ $isDeleted ? 'bg-rose-50/40 text-gray-400' : '' }}">
                            
                            <!-- Date -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="font-bold {{ $isDeleted ? 'line-through text-gray-400' : 'text-gray-900' }}">
                                    {{ $rc->transaction_date ? $rc->transaction_date->addYears(543)->format('d/m/Y') : '-' }}
                                </div>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">
                                    ID: #{{ $rc->id }}
                                </div>
                            </td>

                            <!-- Material Info -->
                            <td class="py-4 px-4">
                                @if($material)
                                    <div class="font-bold {{ $isDeleted ? 'line-through text-gray-500' : 'text-gray-900' }}">
                                        {{ $material->name }}
                                    </div>
                                    <div class="text-xs text-purple-700 font-mono font-semibold">
                                        [{{ $material->material_code ?? 'MAT-' . sprintf('%04d', $material->id) }}]
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">วัสดุ #{{ $rc->item_id }} (ถูกลบจากระบบ)</span>
                                @endif
                            </td>

                            <!-- Quantity -->
                            <td class="py-4 px-4 whitespace-nowrap text-center">
                                <span class="px-3 py-1 rounded-full font-extrabold text-sm {{ $isDeleted ? 'bg-gray-200 text-gray-500 line-through' : 'bg-emerald-100 text-emerald-800' }}">
                                    +{{ number_format($rc->quantity) }} {{ $material->unit ?? 'หน่วย' }}
                                </span>
                            </td>

                            <!-- Unit Price -->
                            <td class="py-4 px-4 whitespace-nowrap text-right font-mono font-semibold">
                                ฿{{ number_format($rc->unit_price, 2) }}
                            </td>

                            <!-- Total Price -->
                            <td class="py-4 px-4 whitespace-nowrap text-right font-mono font-bold text-emerald-700">
                                ฿{{ number_format($rc->quantity * ($rc->unit_price ?? 0), 2) }}
                            </td>

                            <!-- Vendor & Reference -->
                            <td class="py-4 px-4 leading-snug">
                                <div class="font-semibold text-gray-800">
                                    <i class="fas fa-store text-gray-400 mr-1"></i> {{ $rc->party_name ?? '-' }}
                                </div>
                                <div class="text-xs text-gray-500 font-mono mt-0.5">
                                    <i class="fas fa-file-alt text-gray-400 mr-1"></i> {{ $rc->reference_doc ?? 'รับเข้าสต็อก' }}
                                </div>
                            </td>

                            <!-- Audit Trace & User Logs -->
                            <td class="py-4 px-6 leading-relaxed">
                                <!-- Recorded By -->
                                <div class="text-xs text-gray-600">
                                    <i class="fas fa-user-edit text-gray-400 mr-1"></i> บันทึกโดย: 
                                    <span class="font-semibold text-gray-800">{{ $rc->user->name ?? 'ผู้ดูแลระบบ' }}</span>
                                    <span class="text-[11px] text-gray-400">({{ $rc->created_at ? $rc->created_at->addYears(543)->format('d/m/Y H:i') : '-' }})</span>
                                </div>

                                <!-- Edited Trace Badge -->
                                @if($rc->edited_at && $rc->editor)
                                    <div class="mt-1.5 p-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                                        <div class="font-bold flex items-center text-amber-800">
                                            <i class="fas fa-pen text-amber-600 mr-1"></i> 📝 แก้ไขรายการแล้ว
                                        </div>
                                        <div>
                                            โดย: <span class="font-semibold">{{ $rc->editor->name }}</span> (ID: {{ $rc->editor->id }}) 
                                            เมื่อ: <span class="font-mono text-[11px]">{{ $rc->edited_at->addYears(543)->format('d/m/Y H:i:s') }} น.</span>
                                        </div>
                                        <div class="text-amber-800 mt-0.5 italic">
                                            "เหตุผล: {{ $rc->edit_reason }}"
                                        </div>
                                    </div>
                                @endif

                                <!-- Deleted Trace Badge -->
                                @if($isDeleted)
                                    <div class="mt-1.5 p-2.5 rounded-xl bg-rose-100/80 border border-rose-300 text-rose-900 text-xs shadow-sm">
                                        <div class="font-extrabold flex items-center text-rose-800 text-xs">
                                            <i class="fas fa-ban text-rose-600 mr-1"></i> ❌ ยกเลิก/ลบรายการแล้ว (Audit Trail)
                                        </div>
                                        <div class="mt-0.5">
                                            ลบโดย: <span class="font-bold underline">{{ $rc->deleter->name ?? 'ไม่ระบุ' }}</span> 
                                            (User ID: <span class="font-mono font-bold">{{ $rc->deleted_by ?? '-' }}</span>)
                                        </div>
                                        <div class="font-mono text-[11px] text-rose-700 mt-0.5">
                                            <i class="far fa-clock mr-1"></i> {{ $rc->deleted_at->addYears(543)->format('d/m/Y') }} เวลา {{ $rc->deleted_at->format('H:i:s') }} น.
                                        </div>
                                        <div class="mt-1 font-semibold text-rose-900 bg-white/70 p-1.5 rounded-lg border border-rose-200">
                                            💬 เหตุผลที่ลบ: "{{ $rc->delete_reason }}"
                                        </div>
                                    </div>
                                @endif
                            </td>

                            <!-- Action Buttons -->
                            <td class="py-4 px-6 whitespace-nowrap text-center">
                                @if(!$isDeleted)
                                    <div class="flex items-center justify-center gap-1.5">
                                        <!-- Edit Button -->
                                        <button type="button" @click="openEditModal({{ json_encode($rc) }})" class="px-3 py-1.5 bg-amber-50 text-amber-700 hover:bg-amber-100 border border-amber-200 font-bold text-xs rounded-xl transition-all flex items-center gap-1">
                                            <i class="fas fa-edit"></i> แก้ไข
                                        </button>

                                        <!-- Delete Button -->
                                        <button type="button" @click="openDeleteModal({{ json_encode($rc) }})" class="px-3 py-1.5 bg-rose-50 text-rose-700 hover:bg-rose-100 border border-rose-200 font-bold text-xs rounded-xl transition-all flex items-center gap-1">
                                            <i class="fas fa-trash-alt"></i> ลบรายการ
                                        </button>
                                    </div>
                                @else
                                    <span class="px-2.5 py-1 bg-gray-100 text-gray-500 rounded-lg text-xs font-bold border border-gray-200">
                                        <i class="fas fa-lock mr-1"></i> ถูกลบแล้ว
                                    </span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-12 text-center text-gray-400">
                                <i class="fas fa-box-open text-4xl mb-3 text-gray-200 block"></i>
                                ไม่พบข้อมูลรายการรับวัสดุเข้าคลังตามเงื่อนไขที่เลือก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($receipts->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $receipts->links() }}
            </div>
        @endif
    </div>

    <!-- EDIT MODAL -->
    <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="editModalOpen = false" class="bg-white w-full max-w-xl rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-amber-500 to-orange-500 text-white flex justify-between items-center">
                <h3 class="font-bold text-base flex items-center">
                    <i class="fas fa-edit mr-2"></i> แก้ไขข้อมูลรายการรับวัสดุเข้าคลัง
                </h3>
                <button @click="editModalOpen = false" class="text-white/80 hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'{{ url('material-receipts') }}/' + editForm.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('PUT')

                <div class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 text-xs">
                    <i class="fas fa-exclamation-triangle mr-1 text-amber-600"></i>
                    <strong>คำเตือน:</strong> การแก้ไขจำนวนรับเข้า จะทำการปรับปรุงยอดคงเหลือสะสมในสต็อกของพัสดุรายการนี้ให้อัตโนมัติ และระบบจำเป็นต้องให้คุณระบุเหตุผลในการแก้ไขข้อมูล
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Date -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">วันที่รับเข้า <span class="text-red-500">*</span></label>
                        <input type="date" name="transaction_date" x-model="editForm.transaction_date" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <!-- Quantity -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">จำนวนรับเข้า <span class="text-red-500">*</span></label>
                        <input type="number" min="1" name="quantity" x-model="editForm.quantity" required class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold text-emerald-800 focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <!-- Unit Price -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ราคาต่อหน่วย (บาท)</label>
                        <input type="number" step="0.01" min="0" name="unit_price" x-model="editForm.unit_price" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm font-bold focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>

                    <!-- Party Name / Vendor -->
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">ผู้จัดส่ง / ร้านค้า <span class="text-red-500">*</span></label>
                        <input type="text" name="party_name" x-model="editForm.party_name" required placeholder="เช่น บริษัท เอ็มเอสไอ จำกัด" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <!-- Reference Doc & Note -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">เลขที่เอกสารอ้างอิง</label>
                        <input type="text" name="reference_doc" x-model="editForm.reference_doc" placeholder="เช่น ใบส่งของ #1024" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 mb-1">หมายเหตุเดิม</label>
                        <input type="text" name="note" x-model="editForm.note" placeholder="รายละเอียดเพิ่มเติม" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                    </div>
                </div>

                <!-- EDIT REASON (REQUIRED) -->
                <div class="pt-2 border-t border-gray-100">
                    <label class="block text-xs font-extrabold text-amber-900 mb-1">
                        เหตุผลในการแก้ไขข้อมูล <span class="text-red-500">* (บังคับระบุอย่างน้อย 5 ตัวอักษร)</span>
                    </label>
                    <textarea name="edit_reason" x-model="editForm.edit_reason" required minlength="5" rows="2" placeholder="ระบุเหตุผลในการแก้ไข เช่น แก้ไขจำนวนที่คีย์ผิดพลาดจากใบส่งของ, ปรับราคาต่อหน่วยตามใบเสร็จรับเงิน..." class="w-full p-3 bg-amber-50/50 border border-amber-300 rounded-xl focus:ring-2 focus:ring-amber-500 text-sm leading-relaxed outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="editModalOpen = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-all">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold rounded-xl text-sm transition-all shadow-md flex items-center">
                        <i class="fas fa-save mr-1.5"></i> บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div x-show="deleteModalOpen" x-transition.opacity class="fixed inset-0 bg-black/60 backdrop-blur-sm z-50 flex items-center justify-center p-4" style="display: none;">
        <div @click.away="deleteModalOpen = false" class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-gray-100 overflow-hidden flex flex-col">
            <div class="px-6 py-4 bg-gradient-to-r from-rose-600 to-red-600 text-white flex justify-between items-center">
                <h3 class="font-bold text-base flex items-center">
                    <i class="fas fa-trash-alt mr-2"></i> ยกเลิก/ลบรายการรับวัสดุเข้า
                </h3>
                <button @click="deleteModalOpen = false" class="text-white/80 hover:text-white text-lg">
                    <i class="fas fa-times"></i>
                </button>
            </div>

            <form :action="'{{ url('material-receipts') }}/' + deleteForm.id" method="POST" class="p-6 space-y-4">
                @csrf
                @method('DELETE')

                <div class="p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-900 text-sm leading-relaxed">
                    <div class="font-extrabold text-rose-800 mb-1 flex items-center">
                        <i class="fas fa-exclamation-triangle mr-1 text-rose-600"></i> ยืนยันการยกเลิกรายการ #<span x-text="deleteForm.id"></span>
                    </div>
                    คุณกำลังจะยกเลิกรายการรับเข้าพัสดุ: <strong x-text="deleteForm.material_name"></strong> จำนวน <strong x-text="deleteForm.quantity"></strong> <span x-text="deleteForm.unit"></span>
                    <p class="text-xs text-rose-700 mt-2 font-medium">
                        * ยอดจำนวนนี้จะถูกหักออกจากคลังพัสดุคงเหลือ และระบบจะทำการบันทึกร่องรอย (ผู้ลบ, วันที่, เวลา, และเหตุผล) ไว้อย่างถาวร
                    </p>
                </div>

                <!-- DELETE REASON (REQUIRED) -->
                <div>
                    <label class="block text-xs font-extrabold text-rose-900 mb-1">
                        เหตุผลในการยกเลิก/ลบรายการ <span class="text-red-500">* (บังคับระบุอย่างน้อย 5 ตัวอักษร)</span>
                    </label>
                    <textarea name="delete_reason" x-model="deleteForm.delete_reason" required minlength="5" rows="3" placeholder="ระบุเหตุผล เช่น ซ้ำซ้อนกับใบรับเข้าเลขที่ #102, คีย์รายการผิดพลาด, คืนสินค้าซัพพลายเออร์..." class="w-full p-3 bg-rose-50/50 border border-rose-300 rounded-xl focus:ring-2 focus:ring-rose-500 text-sm leading-relaxed outline-none"></textarea>
                </div>

                <div class="flex justify-end gap-2 pt-4 border-t border-gray-100">
                    <button type="button" @click="deleteModalOpen = false" class="px-5 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-sm font-semibold transition-all">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-rose-600 to-red-600 hover:from-rose-700 hover:to-red-700 text-white font-bold rounded-xl text-sm transition-all shadow-md flex items-center">
                        <i class="fas fa-trash-alt mr-1.5"></i> ยืนยันการลบรายการ
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
