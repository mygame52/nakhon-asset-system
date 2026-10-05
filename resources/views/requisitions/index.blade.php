@extends('layouts.app')

@section('page_title', 'ขอเบิกวัสดุ')
@section('page_description', 'ยื่นคำขอเบิกวัสดุหลายรายการ ติดตามสถานะ และกรองส่งออกรายงาน')

@section('content')
<div x-data="{ 
    createModal: false, 
    reqItems: [{ material_id: '', requested_qty: 1 }],
    addItem() {
        if (this.reqItems.length < 10) {
            this.reqItems.push({ material_id: '', requested_qty: 1 });
        } else {
            alert('ใบเบิกพัสดุ 1 ใบ สามารถขอเบิกได้สูงสุด 10 รายการเท่านั้น');
        }
    },
    removeItem(index) {
        if (this.reqItems.length > 1) {
            this.reqItems.splice(index, 1);
        }
    }
}">
    
    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 mb-6 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100">
        <a href="{{ route('materials.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-boxes"></i> รายการวัสดุคงคลัง
        </a>
        <a href="{{ route('requisitions.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm rounded-xl transition-all flex items-center justify-center gap-2 bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-md">
            <i class="fas fa-file-signature"></i> ใบขอเบิกวัสดุ
        </a>
        @hasanyrole('admin|procurement')
        <a href="{{ route('stock-cards.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-book"></i> สมุดบัญชีวัสดุ
        </a>
        <a href="{{ route('material-receipts.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-file-import"></i> รายการรับวัสดุเข้า
        </a>
        @endhasanyrole
    </div>
    
    <!-- Advanced Filters Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form action="{{ route('requisitions.index') }}" method="GET" class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <!-- Search -->
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">ค้นหาคำสำคัญ</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-search text-gray-400"></i>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="รหัสใบเบิก, ชื่อผู้ขอ, ชื่อวัสดุ..." class="w-full pl-10 pr-4 py-2 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white transition-all outline-none text-sm">
                    </div>
                </div>

                <!-- Status Filter -->
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">สถานะใบเบิก</label>
                    <select name="status" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 px-3 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                        <option value="">-- ทุกสถานะ --</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>⏳ รออนุมัติ</option>
                        <option value="partial" {{ request('status') == 'partial' ? 'selected' : '' }}>🔄 อนุมัติแล้วบางส่วน</option>
                        <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>✅ อนุมัติครบถ้วน</option>
                        <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>❌ ปฏิเสธ</option>
                    </select>
                </div>

                <!-- Department Filter (Admin & Procurement) -->
                @hasanyrole('admin|procurement')
                <div>
                    <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">แผนก / หน่วยงาน</label>
                    <select name="department_id" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 px-3 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500 font-medium">
                        <option value="">-- ทุกหน่วยงาน --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ request('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                </div>
                @endhasanyrole

                <!-- Date Range -->
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">ตั้งแต่วันที่</label>
                        <input type="date" name="start_date" value="{{ request('start_date') }}" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 px-2 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">ถึงวันที่</label>
                        <input type="date" name="end_date" value="{{ request('end_date') }}" class="w-full bg-gray-50 border border-gray-200 text-gray-700 py-2 px-2 rounded-xl text-sm outline-none focus:ring-2 focus:ring-purple-500">
                    </div>
                </div>
            </div>

            <!-- Filter Buttons Bar -->
            <div class="flex flex-wrap items-center justify-between gap-3 pt-2 border-t border-gray-100">
                <div class="flex items-center gap-2">
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white font-bold py-2 px-5 rounded-xl shadow-md transition-all text-sm flex items-center">
                        <i class="fas fa-filter mr-1.5"></i> กรองข้อมูล
                    </button>
                    @if(request()->anyFilled(['search', 'status', 'department_id', 'start_date', 'end_date']))
                        <a href="{{ route('requisitions.index') }}" class="bg-gray-100 hover:bg-gray-200 text-gray-600 font-semibold py-2 px-4 rounded-xl transition-all text-sm">
                            <i class="fas fa-undo mr-1"></i> ล้างการกรอง
                        </a>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    <a href="{{ route('requisitions.export-csv', request()->query()) }}" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-2 px-4 rounded-xl shadow-md transition-all text-sm flex items-center">
                        <i class="fas fa-file-excel mr-1.5"></i> ส่งออก CSV (Excel)
                    </a>
                    <button type="button" @click="createModal = true" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2 px-5 rounded-xl shadow-lg shadow-purple-500/20 transition-all active:scale-95 flex items-center text-sm">
                        <i class="fas fa-plus-circle mr-2"></i> ยื่นขอเบิกหลายรายการ
                    </button>
                </div>
            </div>
        </form>
    </div>

    <!-- Requisitions Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[950px]">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                        <th class="p-4 px-6 w-40">เลขที่ใบเบิก</th>
                        <th class="p-4 px-4 w-52">ผู้ขอเบิก / แผนก</th>
                        <th class="p-4 px-4">รายการวัสดุที่ขอเบิก</th>
                        <th class="p-4 text-center w-36">สถานะภาพรวม</th>
                        <th class="p-4 text-right pr-6 w-32">รายละเอียด</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100/80 text-sm">
                    @forelse($requisitions as $req)
                    <tr class="hover:bg-purple-50/30 transition-colors group">
                        <td class="p-4 px-6 font-bold text-purple-700 whitespace-nowrap">
                            {{ $req->requisition_code }}
                            <div class="text-[11px] font-normal text-gray-400 mt-0.5">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="p-4 px-4">
                            <div class="font-bold text-gray-800">{{ $req->user->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $req->department->name ?? '-' }}</div>
                        </td>
                        <td class="p-4 px-4">
                            <div class="space-y-1">
                                @foreach($req->items as $item)
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="font-semibold text-gray-800">• {{ $item->material->name ?? '-' }}</span>
                                        <span class="text-purple-700 font-bold">x{{ number_format($item->requested_qty) }} {{ $item->material->unit ?? '' }}</span>
                                        @if($item->status == 'approved')
                                            <span class="text-[10px] bg-emerald-50 text-emerald-600 border border-emerald-200 px-1.5 py-0.5 rounded-md font-bold">✓ อนุมัติแล้ว ({{ number_format($item->approved_qty) }})</span>
                                        @elseif($item->status == 'officer_approved')
                                            <span class="text-[10px] bg-blue-50 text-blue-700 border border-blue-200 px-1.5 py-0.5 rounded-md font-bold">🟦 รอหัวหน้าพัสดุอนุมัติ</span>
                                        @elseif($item->status == 'rejected')
                                            <span class="text-[10px] bg-rose-50 text-rose-600 border border-rose-200 px-1.5 py-0.5 rounded-md font-bold">✕ ไม่อนุมัติ</span>
                                        @else
                                            <span class="text-[10px] bg-amber-50 text-amber-600 border border-amber-200 px-1.5 py-0.5 rounded-md font-bold">⏳ รอเจ้าหน้าที่พัสดุ</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                            @if($req->reason_for_request)
                                <div class="text-[11px] text-gray-500 mt-2 italic bg-gray-50 px-2.5 py-1 rounded-lg inline-block border border-gray-100">
                                    <i class="fas fa-comment-alt text-gray-400 mr-1"></i> {{ $req->reason_for_request }}
                                </div>
                            @endif
                        </td>
                        <td class="p-4 text-center whitespace-nowrap">
                            @if($req->status == 'pending')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-600 border border-amber-200 shadow-sm animate-pulse">
                                    <i class="fas fa-clock mr-1.5"></i> รอเจ้าหน้าที่พัสดุ
                                </span>
                            @elseif($req->status == 'officer_approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                                    <i class="fas fa-user-shield mr-1.5"></i> รอหัวหน้าพัสดุอนุมัติ
                                </span>
                            @elseif($req->status == 'partial')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-indigo-50 text-indigo-700 border border-indigo-200 shadow-sm">
                                    <i class="fas fa-spinner mr-1.5"></i> อนุมัติแล้วบางส่วน
                                </span>
                            @elseif($req->status == 'approved')
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm">
                                    <i class="fas fa-check-circle mr-1.5"></i> อนุมัติครบถ้วน
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200 shadow-sm">
                                    <i class="fas fa-times-circle mr-1.5"></i> ปฏิเสธทั้งหมด
                                </span>
                            @endif
                        </td>
                        <td class="p-4 text-right pr-6 whitespace-nowrap">
                            <a href="{{ route('requisitions.show', $req->id) }}" class="p-2.5 px-3 rounded-xl bg-purple-50 text-purple-600 hover:bg-purple-600 hover:text-white transition-all shadow-sm font-semibold text-xs inline-flex items-center">
                                <i class="fas fa-eye mr-1.5"></i> ดูใบเบิก
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-16 text-center text-gray-400">
                            <div class="bg-gray-50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                                <i class="fas fa-inbox text-3xl text-gray-300"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-1">ยังไม่มีรายการขอเบิก</h3>
                            <p class="text-sm text-gray-500">คุณสามารถคลิกปุ่ม "ยื่นขอเบิกหลายรายการ" ด้านบนเพื่อทำรายการ</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requisitions->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $requisitions->links() }}
        </div>
        @endif
    </div>

    <!-- Create Multi-Item Requisition Modal -->
    <template x-teleport="body">
        <div x-show="createModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="createModal" @click="createModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="createModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-2xl w-full relative z-50">
                    <form action="{{ route('requisitions.store') }}" method="POST">
                        @csrf
                        <div class="bg-gradient-to-r from-purple-700 to-indigo-700 px-6 py-4 text-white flex justify-between items-center">
                            <h3 class="font-bold text-lg flex items-center">
                                <i class="fas fa-file-signature mr-2"></i> ยื่นคำขอเบิกวัสดุ (หลายรายการ)
                            </h3>
                            <button type="button" @click="createModal = false" class="text-white/60 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                        </div>

                        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">
                            <!-- Purpose -->
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">วัตถุประสงค์ / เหตุผลในการขอเบิก <span class="text-red-500">*</span></label>
                                <textarea name="reason_for_request" rows="2" required placeholder="ระบุรายละเอียดงาน โครงการ หรือวัตถุประสงค์การนำพัสดุไปใช้งาน..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-medium outline-none resize-none"></textarea>
                            </div>

                            <!-- Items Section -->
                            <div>
                                <div class="flex items-center justify-between mb-3">
                                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        รายการวัสดุที่ต้องการขอเบิก <span class="text-red-500">*</span>
                                        <span class="text-[11px] font-normal text-purple-600 ml-2" x-text="'(' + reqItems.length + '/10 รายการ)'"></span>
                                    </label>
                                    <button type="button" @click="addItem()" :disabled="reqItems.length >= 10" :class="{'opacity-50 cursor-not-allowed': reqItems.length >= 10}" class="text-xs font-bold text-purple-600 hover:text-purple-800 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-xl transition-all flex items-center">
                                        <i class="fas fa-plus mr-1"></i> เพิ่มรายการวัสดุ
                                    </button>
                                </div>

                                <div class="space-y-3">
                                    <template x-for="(item, index) in reqItems" :key="index">
                                        <div class="p-3 bg-gray-50 border border-gray-200 rounded-xl flex items-center gap-3">
                                            <div class="flex-1">
                                                <select :name="'items[' + index + '][material_id]'" x-model="item.material_id" required class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-medium outline-none focus:ring-2 focus:ring-purple-500">
                                                    <option value="">-- เลือกวัสดุ --</option>
                                                    @foreach($materials as $mat)
                                                        <option value="{{ $mat->id }}">
                                                            {{ $mat->name }} (คงเหลือ: {{ number_format($mat->stock_qty) }} {{ $mat->unit }})
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="w-28">
                                                <input type="number" :name="'items[' + index + '][requested_qty]'" x-model="item.requested_qty" min="1" required placeholder="จำนวน" class="w-full px-3 py-2 bg-white border border-gray-200 rounded-lg text-sm font-bold text-center outline-none focus:ring-2 focus:ring-purple-500">
                                            </div>

                                            <button type="button" @click="removeItem(index)" :disabled="reqItems.length === 1" class="text-gray-400 hover:text-rose-600 p-2 rounded-lg transition-colors disabled:opacity-30 disabled:cursor-not-allowed">
                                                <i class="fas fa-trash-alt"></i>
                                            </button>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="createModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-semibold text-sm hover:bg-gray-100">ยกเลิก</button>
                            <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white font-bold rounded-xl text-sm shadow-md hover:from-purple-700 hover:to-indigo-700 flex items-center">
                                <i class="fas fa-paper-plane mr-2"></i> ส่งคำขอเบิกพัสดุ
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

</div>
@endsection
