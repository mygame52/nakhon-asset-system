@extends('layouts.app')

@section('page_title', 'ทะเบียนครุภัณฑ์ (พด. 1)')
@section('page_description', 'จัดการและติดตามรายการครุภัณฑ์ทั้งหมดของหน่วยงาน')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100" x-data="{ importModal: false, showFilters: false, deleteBulkModal: false, confirmCode: '' }">

    <!-- Toolbar -->
    <div class="p-6 border-b border-gray-100 bg-gray-50/30 rounded-t-2xl flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3 w-full sm:w-auto relative">
            <form action="{{ route('assets.index') }}" method="GET" class="flex items-center gap-3 w-full">
                <div class="relative w-full sm:w-80">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหารหัส หรือชื่อครุภัณฑ์..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none text-sm shadow-sm">
                </div>
                
                <button type="button" @click="showFilters = !showFilters" class="bg-white border border-gray-200 text-gray-700 hover:bg-gray-50 hover:text-purple-600 px-4 py-2 rounded-xl transition-colors shadow-sm hidden md:flex items-center whitespace-nowrap text-sm font-medium relative z-10">
                    <i class="fas fa-filter mr-2"></i> ตัวกรอง
                </button>

                <!-- Filter Dropdown -->
                <div x-show="showFilters" @click.away="showFilters = false" style="display: none;" class="absolute top-full left-0 sm:left-auto sm:right-0 mt-2 w-full sm:w-80 bg-white rounded-xl shadow-xl border border-gray-100 p-4 z-50">
                    <h4 class="text-sm font-bold text-gray-800 mb-3 border-b pb-2">กรองข้อมูล</h4>
                    
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">ประเภทพัสดุ</label>
                            <select name="category_id" class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500 focus:border-purple-500 py-2 px-3 outline-none border">
                                <option value="">ทั้งหมด</option>
                                @foreach($categories ?? [] as $cat)
                                <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">สถานที่จัดเก็บ</label>
                            <select name="location_id" class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500 focus:border-purple-500 py-2 px-3 outline-none border">
                                <option value="">ทั้งหมด</option>
                                @foreach($locations ?? [] as $loc)
                                <option value="{{ $loc->id }}" {{ request('location_id') == $loc->id ? 'selected' : '' }}>{{ $loc->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        
                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">แผนก/หน่วยงาน</label>
                            <select name="department_id" class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500 focus:border-purple-500 py-2 px-3 outline-none border">
                                <option value="">ทั้งหมด</option>
                                @foreach($departments ?? [] as $dep)
                                <option value="{{ $dep->id }}" {{ request('department_id') == $dep->id ? 'selected' : '' }}>{{ $dep->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-600 mb-1">สถานะ</label>
                            <select name="status" class="w-full text-sm border-gray-200 rounded-lg focus:ring-purple-500 focus:border-purple-500 py-2 px-3 outline-none border">
                                <option value="">ทั้งหมด</option>
                                <option value="ใช้งานปกติ" {{ request('status') == 'ใช้งานปกติ' ? 'selected' : '' }}>ใช้งานปกติ</option>
                                <option value="รอซ่อม" {{ request('status') == 'รอซ่อม' ? 'selected' : '' }}>รอซ่อม</option>
                                <option value="ชำรุด" {{ request('status') == 'ชำรุด' ? 'selected' : '' }}>ชำรุด</option>
                                <option value="เสื่อมสภาพ" {{ request('status') == 'เสื่อมสภาพ' ? 'selected' : '' }}>เสื่อมสภาพ</option>
                                <option value="จำหน่ายออก" {{ request('status') == 'จำหน่ายออก' ? 'selected' : '' }}>จำหน่ายออก</option>
                            </select>
                        </div>

                        <div class="pt-3 flex gap-2">
                            <a href="{{ route('assets.index') }}" class="flex-1 px-3 py-2 bg-gray-100 text-gray-600 text-center text-xs font-medium rounded-lg hover:bg-gray-200 transition-colors">ล้างค่า</a>
                            <button type="submit" class="flex-1 px-3 py-2 bg-purple-600 text-white text-center text-xs font-medium rounded-lg hover:bg-purple-700 transition-colors shadow-sm">นำไปใช้</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <div class="flex items-center gap-2 flex-wrap sm:flex-nowrap w-full sm:w-auto">
            @hasanyrole('admin|procurement')
            <a href="{{ route('assets.export', request()->query()) }}" class="flex-1 sm:flex-none bg-white border border-green-200 text-green-700 hover:bg-green-50 px-4 py-2 rounded-xl transition-colors shadow-sm flex items-center justify-center text-sm font-semibold">
                <i class="fas fa-file-excel mr-2"></i> ส่งออก
            </a>
            <button type="button" @click.prevent="importModal = true" class="flex-1 sm:flex-none bg-white border border-blue-200 text-blue-700 hover:bg-blue-50 px-4 py-2 rounded-xl transition-colors shadow-sm flex items-center justify-center text-sm font-semibold">
                <i class="fas fa-file-import mr-2"></i> นำเข้า
            </button>
            <a href="{{ route('assets.create') }}" class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold py-2 px-5 rounded-xl shadow-md shadow-purple-500/20 transition-all active:scale-95 flex items-center justify-center text-sm">
                <i class="fas fa-plus-circle mr-2 text-purple-200"></i> ลงทะเบียน
            </a>
            @endhasanyrole

            @role('admin')
            <button type="button" @click="deleteBulkModal = true" class="flex-1 sm:flex-none bg-red-600 text-white hover:bg-red-700 px-4 py-2 rounded-xl transition-colors shadow-sm flex items-center justify-center text-sm font-semibold">
                <i class="fas fa-trash mr-2"></i> ลบทั้งหมด
            </button>
            @endrole
        </div>
    </div>

    @role('admin')
    <!-- Import Modal -->
    <template x-teleport="body">
        <div x-show="importModal" style="display: none;" class="fixed inset-0 z-[150] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="importModal" @click="importModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity" aria-hidden="true"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="importModal" x-transition.scale @click.stop class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-[151]">
                <form action="{{ route('assets.import.preview') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-50 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fas fa-file-excel text-blue-600 text-lg"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                                <h3 class="text-lg leading-6 font-bold text-gray-900" id="modal-title">นำเข้าข้อมูลครุภัณฑ์</h3>
                                <div class="mt-2 text-sm text-gray-500">
                                    <p class="mb-2">อัปโหลดไฟล์ Excel (.xlsx, .csv) เพื่อนำเข้าทะเบียนคุมทรพย์สิน</p>
                                    <ul class="list-disc pl-5 text-[11px] text-gray-400 space-y-1">
                                        <li>แถวที่ 1 จะถูกมองข้าม (ถือว่าเป็นหัวตาราง)</li>
                                        <li>ข้อบังคับคอลัมน์ A (รหัส) และ B (ชื่อรายการ)</li>
                                        <li>หากรหัสมีอยู่ระบบจะข้ามรายการนั้น</li>
                                    </ul>
                                    <div class="mt-4 p-3 bg-blue-50/50 border border-blue-100 rounded-xl flex items-center justify-between">
                                        <div class="flex items-center">
                                            <i class="fas fa-file-download text-blue-500 mr-2 text-lg"></i>
                                            <span class="text-[11px] font-bold text-blue-700 uppercase tracking-wider">ไฟล์ตัวอย่างนำเข้า</span>
                                        </div>
                                        <a href="{{ route('assets.download-template') }}" class="text-[11px] bg-blue-600 hover:bg-blue-700 text-white font-bold py-1.5 px-3 rounded-lg shadow-sm transition-all flex items-center">
                                            <i class="fas fa-download mr-1.5"></i> ดาวน์โหลด
                                        </a>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <input type="file" name="file" required accept=".xlsx,.xls,.csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 transition-colors border border-gray-200 rounded-xl p-2 cursor-pointer">
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                        <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-600 text-base font-medium text-white hover:from-blue-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm transition-all active:scale-95">
                            <i class="fas fa-cloud-upload-alt mr-2 mt-0.5"></i> เริ่มการนำเข้า
                        </button>
                        <button type="button" @click="importModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                            ยกเลิก
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    </template>

<!-- Bulk Delete Modal -->
<template x-teleport="body">
    <div x-show="deleteBulkModal" style="display: none;" class="fixed inset-0 z-[210] overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-center justify-center min-h-screen p-4">
            <div x-show="deleteBulkModal" @click.away="deleteBulkModal = false" class="bg-white rounded-2xl p-6 shadow-xl w-full max-w-md">
                <h3 class="text-xl font-bold mb-4">ยืนยันการลบทั้งหมด</h3>
                
                <form action="{{ route('assets.bulk-delete') }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <input type="text" name="confirm_code" x-model="confirmCode" class="w-full p-2 border rounded mb-4" required>
                    <div class="flex justify-end gap-3">
                        <button type="button" @click="deleteBulkModal = false" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">ยกเลิก</button>
                        <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700">ลบทั้งหมด</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
    @endrole
</div>

<!-- Table -->
<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse min-w-[900px]">
        <thead>
            <tr class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                <th class="p-4 px-6 min-w-[220px]">รหัสฯ / วันที่ได้มา</th>
                <th class="p-4">รายการ</th>
                <th class="p-4 w-48">สถานที่ / แผนก</th>
                <th class="p-4 w-32 text-right">ราคาต่อหน่วย</th>
                <th class="p-4 w-28 text-center">สถานะ</th>
                <th class="p-4 w-32 text-right pr-6">จัดการ</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-gray-100/80 text-sm">
            @forelse($assets as $asset)
            <tr class="hover:bg-purple-50/30 transition-colors group">
                <td class="p-4 px-6">
                    <div class="font-bold text-purple-700 break-words">{{ $asset->asset_code }}</div>
                    @if($asset->purchase_date)
                    <div class="text-[11px] text-gray-500 mt-0.5"><i class="fas fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::parse($asset->purchase_date)->addYears(543)->format('d/m/Y') }}</div>
                    @endif
                </td>
                <td class="p-4">
                    <div class="text-[10px] text-purple-600 font-bold uppercase tracking-wider mb-0.5">{{ $asset->category->name ?? 'ไม่ระบุประเภท' }}</div>
                    <div class="font-medium text-gray-800">{{ $asset->name }}</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">{{ $asset->model ?? 'ไม่ระบุรุ่น' }}</div>
                </td>
                <td class="p-4">
                    <div class="text-xs font-semibold text-gray-700 bg-gray-100 px-2 py-1 rounded-md border border-gray-200 shadow-sm inline-block mb-1">
                        <i class="fas fa-map-marker-alt text-gray-400 mr-1"></i> {{ $asset->location->name ?? '-' }}
                    </div>
                    <div class="text-[10px] text-gray-500">
                        <i class="fas fa-sitemap text-gray-300 mr-1"></i> {{ $asset->department->name ?? '-' }}
                    </div>
                </td>
                <td class="p-4 text-right font-medium text-emerald-600">
                    {{ number_format($asset->unit_price, 2) }} <span class="text-[10px] text-gray-400 font-normal">฿</span>
                </td>
                <td class="p-4 text-center">
                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-200 whitespace-nowrap">
                        {{ $asset->status }}
                    </span>
                </td>
                <td class="p-4 text-right pr-6">
                    <div class="flex items-center justify-end gap-2 opacity-0 md:opacity-100 group-hover:opacity-100 transition-opacity">
                        <a href="{{ route('assets.show', $asset->id) }}" class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-600 hover:bg-indigo-600 hover:text-white transition-colors flex items-center justify-center tooltip" title="ดูรายละเอียด">
                            <i class="fas fa-eye text-[11px]"></i>
                        </a>
                        @hasanyrole('admin|procurement')
                        <a href="{{ route('assets.edit', $asset->id) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-colors flex items-center justify-center tooltip" title="แก้ไข">
                            <i class="fas fa-edit text-[11px]"></i>
                        </a>
                        <form action="{{ route('assets.destroy', $asset->id) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบรายการนี้?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center tooltip" title="ลบ">
                                <i class="fas fa-trash-alt text-[11px]"></i>
                            </button>
                        </form>
                        @endhasanyrole
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="p-16 text-center text-gray-400">
                    <div class="bg-gray-50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                        <i class="fas fa-box-open text-3xl text-gray-300"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-700 mb-1">ไม่พบข้อมูล</h3>
                    <p class="text-sm text-gray-500">ยังไม่มีข้อมูลครุภัณฑ์ที่ค้นหา</p>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

<!-- Pagination -->
@if($assets->hasPages())
<div class="p-4 border-t border-gray-100 bg-gray-50/50">
    {{ $assets->links() }}
</div>
@endif
</div>
@endsection