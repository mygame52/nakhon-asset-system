@extends('layouts.app')

@section('page_title', 'สถานที่จัดเก็บ')
@section('page_description', 'จัดการข้อมูลอาคาร ห้อง หรือพิกัดสำหรับคุมคลังและเก็บรักษาครุภัณฑ์/วัสดุ')

@section('content')
<div class="space-y-6" x-data="{ 
    editModal: false, 
    editLocation: null,
    openEdit(loc) {
        this.editLocation = {
            id: loc.id,
            name: loc.name,
            room_number: loc.room_number || '',
            building: loc.building || '',
            floor: loc.floor || '',
            description: loc.description || ''
        };
        this.editModal = true;
    }
}">
    <!-- Summary Header Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-purple-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">สถานที่จัดเก็บทั้งหมด</div>
                <div class="text-2xl font-extrabold text-purple-900 mt-1">{{ number_format($totalLocations ?? count($locations)) }} <span class="text-sm font-normal text-gray-500">แห่ง/ห้อง</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold border border-purple-100">
                <i class="fas fa-map-marker-alt"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-indigo-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-indigo-600 uppercase tracking-wider">ระบบคุมสถานที่จัดเก็บ (Bin Location)</div>
                <div class="text-sm font-semibold text-gray-700 mt-1">กำกับพิกัดจัดเก็บครุภัณฑ์และวัสดุคงคลัง</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold border border-indigo-100">
                <i class="fas fa-warehouse"></i>
            </div>
        </div>
    </div>

    <!-- Main 2-Column Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Left: Add Location Form Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 self-start space-y-4">
            <h3 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-indigo-600 border-b border-gray-100 pb-3 flex items-center">
                <i class="fas fa-plus-circle mr-2 text-purple-500"></i> เพิ่มสถานที่จัดเก็บใหม่
            </h3>

            <form action="{{ route('locations.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        ชื่ออาคาร / สถานที่ <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-building"></i>
                        </div>
                        <input type="text" name="name" required placeholder="เช่น อาคารอำนวยการ หรือ ห้องพัสดุ" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        เลขห้อง / หมายเลขพิกัด
                    </label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <i class="fas fa-door-open"></i>
                        </div>
                        <input type="text" name="room_number" placeholder="เช่น 101 หรือ ตู้ 1 ชั้น 2" class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">
                        คำอธิบาย / รายละเอียดเพิ่มเติม
                    </label>
                    <textarea name="description" rows="2" placeholder="ระบุรายละเอียดสถานที่เพิ่มเติม (ถ้ามี)..." class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none resize-none transition-all"></textarea>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-lg shadow-purple-500/20 transition-all active:scale-95 flex items-center justify-center text-sm">
                    <i class="fas fa-save mr-2"></i> บันทึกข้อมูลสถานที่
                </button>
            </form>
        </div>

        <!-- Right: Locations List Table Card -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Search Toolbar -->
            <div class="p-5 border-b border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row justify-between items-center gap-4">
                <form action="{{ route('locations.index') }}" method="GET" class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-gray-400">
                        <i class="fas fa-search"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อสถานที่ หรือเลขห้อง..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm outline-none">
                </form>
                <div class="text-xs text-gray-400 font-medium">
                    แสดง {{ count($locations) }} รายการ
                </div>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                            <th class="p-4 px-6">ชื่อสถานที่ / อาคาร</th>
                            <th class="p-4 w-36">เลขห้อง / พิกัด</th>
                            <th class="p-4 w-36 text-center">พัสดุผูกอยู่</th>
                            <th class="p-4 w-28 text-right pr-6">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100/80 text-sm">
                        @forelse($locations as $location)
                        <tr class="hover:bg-purple-50/30 transition-colors group">
                            <td class="p-4 px-6 font-bold text-gray-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs border border-purple-100 shrink-0">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div>
                                        <div class="font-bold text-gray-900">{{ $location->name }}</div>
                                        @if($location->description)
                                            <div class="text-xs text-gray-400 font-normal mt-0.5">{{ $location->description }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @if($location->room_number)
                                    <span class="text-xs font-semibold text-gray-700 bg-gray-100 px-2.5 py-1 rounded-lg border border-gray-200">
                                        {{ $location->room_number }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-400 italic">-</span>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="badge badge-purple" title="จำนวนครุภัณฑ์">
                                        <i class="fas fa-laptop mr-1 text-[10px]"></i> {{ $location->assets_count ?? 0 }}
                                    </span>
                                    <span class="badge badge-emerald" title="จำนวนวัสดุ">
                                        <i class="fas fa-box mr-1 text-[10px]"></i> {{ $location->materials_count ?? 0 }}
                                    </span>
                                </div>
                            </td>
                            <td class="p-4 text-right pr-6">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEdit({{ json_encode($location) }})" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center text-xs" title="แก้ไข">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <form action="{{ route('locations.destroy', $location->id) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบสถานที่จัดเก็บนี้?');">
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
                                    <i class="fas fa-map-marker-alt text-2xl text-purple-300"></i>
                                </div>
                                <h4 class="text-base font-bold text-gray-700 mb-1">ไม่พบสถานที่จัดเก็บ</h4>
                                <p class="text-xs text-gray-400">ระบุชื่อสถานที่ฝั่งซ้ายเพื่อบันทึกเพิ่มเข้าสู่ระบบ</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if(isset($locations) && method_exists($locations, 'hasPages') && $locations->hasPages())
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                {{ $locations->links() }}
            </div>
            @endif
        </div>
    </div>

    <!-- Edit Location Modal -->
    <div x-show="editModal" x-cloak style="display: none;" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-50 flex items-center justify-center p-4">
        <div @click.away="editModal = false" class="bg-white rounded-2xl max-w-md w-full p-6 shadow-2xl border border-gray-100 animate-fade-in-up space-y-4">
            <div class="flex justify-between items-center border-b border-gray-100 pb-3">
                <h3 class="text-lg font-bold text-purple-900 flex items-center">
                    <i class="fas fa-edit mr-2 text-amber-500"></i> แก้ไขข้อมูลสถานที่จัดเก็บ
                </h3>
                <button type="button" @click="editModal = false" class="text-gray-400 hover:text-gray-600 p-1">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <form :action="'{{ route('locations.index') }}/' + editLocation?.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ชื่ออาคาร / สถานที่ <span class="text-red-500">*</span></label>
                    <input type="text" name="name" x-model="editLocation.name" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">เลขห้อง / หมายเลขพิกัด</label>
                    <input type="text" name="room_number" x-model="editLocation.room_number" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm font-semibold outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">คำอธิบายรายละเอียดเพิ่มเติม</label>
                    <textarea name="description" x-model="editLocation.description" rows="2" class="w-full p-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 text-sm outline-none resize-none"></textarea>
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
