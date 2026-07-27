@extends('layouts.app')

@section('page_title', 'ร้านค้า / ผู้จัดจำหน่าย')
@section('page_description', 'จัดการข้อมูลร้านค้าคู่สัญญา ผู้ค้าประจำ และผู้ติดต่อประสานงานจัดซื้อจัดจ้าง')

@section('content')
<div class="space-y-6" x-data="{ 
    editModal: false, 
    editVendor: null,
    openEdit(vendor) {
        this.editVendor = {
            id: vendor.id,
            name: vendor.name,
            contact_person: vendor.contact_person || '',
            phone: vendor.phone || '',
            tax_id: vendor.tax_id || '',
            address: vendor.address || ''
        };
        this.editModal = true;
    }
}">
    <!-- Summary Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-purple-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">ร้านค้าคู่สัญญาทั้งหมด</div>
                <div class="text-2xl font-extrabold text-purple-900 mt-1">{{ number_format($totalVendors ?? count($vendors)) }} <span class="text-sm font-normal text-gray-500">ร้านค้า/ผู้ขาย</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold border border-purple-100">
                <i class="fas fa-store"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-amber-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-amber-600 uppercase tracking-wider">ฐานข้อมูลผู้จัดจำหน่าย (Vendor Database)</div>
                <div class="text-sm font-semibold text-gray-700 mt-1">คุมข้อมูลร้านค้า เลขผู้เสียภาษี และเบอร์ผู้ติดต่อประจำ</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-50 text-amber-600 flex items-center justify-center text-xl font-bold border border-amber-100">
                <i class="fas fa-handshake"></i>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Add Vendor Form Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 self-start space-y-4">
            <h3 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-indigo-600 border-b border-gray-100 pb-3 flex items-center">
                <i class="fas fa-plus-circle mr-2 text-purple-500"></i> เพิ่มร้านค้า / ผู้จัดจำหน่ายใหม่
            </h3>

            <form action="{{ route('vendors.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        ชื่อร้านค้า / บริษัท / ห้างหุ้นส่วน <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-store"></i>
                        </div>
                        <input type="text" name="name" required placeholder="เช่น บจก. นครภัณฑ์ หรือ ร้านเครื่องเขียนสยาม" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        ชื่อผู้ติดต่อประจำ
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-user-tie"></i>
                        </div>
                        <input type="text" name="contact_person" placeholder="เช่น คุณสมชาย หรือ ฝ่ายขาย" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        เบอร์โทรศัพท์ติดต่อ
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-phone"></i>
                        </div>
                        <input type="text" name="phone" placeholder="เช่น 075-345678 หรือ 081-2345678" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        เลขประจำตัวผู้เสียภาษี (Tax ID)
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-receipt"></i>
                        </div>
                        <input type="text" name="tax_id" placeholder="เช่น 0805560001234" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-mono font-bold text-purple-700 outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        ที่อยู่ / สถานประกอบการ
                    </label>
                    <textarea name="address" rows="2" placeholder="ระบุที่อยู่หรือสถานที่ติดต่อของร้านค้า..." class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none resize-none transition-all"></textarea>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-purple-500/20 transition-all active:scale-95 flex items-center justify-center text-sm">
                    <i class="fas fa-save mr-2"></i> บันทึกข้อมูลร้านค้า
                </button>
            </form>
        </div>

        <!-- Right: Vendors List Table Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Search Toolbar -->
            <div class="p-5 border-b border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row justify-between items-center gap-4">
                <form action="{{ route('vendors.index') }}" method="GET" class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อร้านค้า ผู้ติดต่อ หรือเบอร์โทร..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm outline-none">
                </form>
                <div class="text-xs text-gray-400 font-medium">
                    แสดง {{ count($vendors) }} รายการ
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <th class="p-4 px-6">ชื่อร้านค้า / บริษัท</th>
                            <th class="p-4 w-40">ผู้ติดต่อ</th>
                            <th class="p-4 w-36">เบอร์โทรศัพท์</th>
                            <th class="p-4 w-28 text-right pr-6">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/80 text-sm">
                        @forelse($vendors as $vendor)
                        <tr class="hover:bg-purple-50/30 transition-colors group">
                            <td class="p-4 px-6 font-bold text-gray-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center font-bold text-xs border border-amber-100 shrink-0">
                                        <i class="fas fa-store"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $vendor->name }}</div>
                                        @if($vendor->tax_id)
                                            <div class="text-xs text-purple-700 font-mono mt-0.5"><i class="fas fa-receipt text-[10px] mr-1"></i> Tax ID: {{ $vendor->tax_id }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @if($vendor->contact_person)
                                    <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2.5 py-1 rounded-lg border border-gray-200">
                                        <i class="fas fa-user mr-1 text-[10px] text-gray-400"></i> {{ $vendor->contact_person }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="p-4">
                                @if($vendor->phone)
                                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-lg border border-emerald-200">
                                        <i class="fas fa-phone mr-1 text-[10px]"></i> {{ $vendor->phone }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEdit({{ json_encode($vendor) }})" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center text-xs" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('vendors.destroy', $vendor->id) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบข้อมูลร้านค้านี้?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-xs" title="ลบ">
                                            <i class="fas fa-trash-alt"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center text-gray-400">
                                <div class="bg-purple-50/50 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-3 border border-purple-100">
                                    <i class="fas fa-store text-2xl text-purple-300"></i>
                                </div>
                                <h4 class="text-base font-bold text-gray-700 mb-1">ไม่พบข้อมูลร้านค้า</h4>
                                <p class="text-xs text-gray-400">ระบุชื่อร้านค้าฝั่งซ้ายเพื่อบันทึกเพิ่มเข้าสู่ระบบ</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($vendors) && method_exists($vendors, 'hasPages') && $vendors->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $vendors->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Edit Vendor Modal -->
    <div x-show="editModal" x-cloak style="display: none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="editModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-fade-in-up space-y-4">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                <h3 class="text-lg font-bold text-purple-900 flex items-center">
                    <i class="fas fa-edit mr-2 text-amber-500"></i> แก้ไขข้อมูลร้านค้า / ผู้จัดจำหน่าย
                </h3>
                <button type="button" @click="editModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form :action="'{{ route('vendors.index') }}/' + editVendor?.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ชื่อร้านค้า / บริษัท <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editVendor.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ชื่อผู้ติดต่อประจำ</label>
                    <input type="text" name="contact_person" x-model="editVendor.contact_person" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">เบอร์โทรศัพท์ติดต่อ</label>
                    <input type="text" name="phone" x-model="editVendor.phone" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">เลขประจำตัวผู้เสียภาษี (Tax ID)</label>
                    <input type="text" name="tax_id" x-model="editVendor.tax_id" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-mono font-bold text-purple-700 outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ที่อยู่ / สถานประกอบการ</label>
                    <textarea name="address" x-model="editVendor.address" rows="2" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm outline-none resize-none"></textarea>
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                    <button type="button" @click="editModal = false" class="px-5 py-2.5 bg-gray-100 text-gray-700 rounded-xl font-semibold text-xs hover:bg-gray-200">
                        ยกเลิก
                    </button>
                    <button type="submit" class="px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-white rounded-xl font-bold text-xs shadow-md hover:from-purple-700 hover:to-indigo-700">
                        บันทึกการแก้ไข
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
