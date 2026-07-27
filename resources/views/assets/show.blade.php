@extends('layouts.app')

@section('page_title', 'รายละเอียดครุภัณฑ์')
@section('page_description', 'ข้อมูลทางเทคนิค ประวัติ และสถานะของครุภัณฑ์')

@section('content')
<div x-data="{ statusModal: false }">
    <div class="mb-5">
        <a href="{{ route('assets.index') }}" class="text-gray-600 hover:text-gray-800 flex items-center transition-colors font-medium">
            <i class="fas fa-arrow-left mr-2"></i> กลับไปหน้าจดทะเบียน
        </a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-24 md:mb-6">
        <!-- Asset Profile & QR -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 text-center">
            <div class="w-32 h-32 md:w-40 md:h-40 bg-gray-100 rounded-2xl mx-auto flex justify-center items-center text-gray-400 mb-6 shadow-inner overflow-hidden">
                @if($asset->image)
                    <img src="{{ asset('storage/' . $asset->image) }}" class="object-cover w-full h-full" alt="{{ $asset->name }}">
                @else
                    <i class="fas fa-image fa-3x"></i>
                @endif
            </div>
            
            <h3 class="text-xl font-bold text-gray-800 mb-2 leading-tight">{{ $asset->name }}</h3>
            <p class="text-purple-600 font-semibold text-lg mb-4 tracking-wide">{{ $asset->asset_code }}</p>
            <div class="mb-6">
                <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider 
                    {{ $asset->status == 'ใช้งานปกติ' || $asset->status == 'ใช้งาน' ? 'bg-emerald-50 text-emerald-600 border border-emerald-200' : 
                       ($asset->status == 'ชำรุด' || $asset->status == 'จำหน่ายออก' ? 'bg-red-50 text-red-600 border border-red-200' : 'bg-amber-50 text-amber-600 border border-amber-200') }}">
                    <i class="fas {{ $asset->status == 'ใช้งานปกติ' || $asset->status == 'ใช้งาน' ? 'fa-check-circle' : 'fa-exclamation-circle' }} mr-1.5"></i>
                    {{ $asset->status }}
                </span>
            </div>
            
            <div class="p-5 bg-gray-50 rounded-xl mb-6 shadow-inner border border-gray-100">
                <p class="text-xs text-gray-500 mb-3 font-semibold uppercase tracking-wider">รหัส QR สำหรับตรวจสอบ</p>
                <div class="w-36 h-36 bg-white border border-gray-200 mx-auto flex justify-center items-center p-3 rounded-xl shadow-sm mb-4 transition-transform hover:scale-105 duration-300">
                    {!! $qrCode !!}
                </div>
                <a href="{{ route('assets.print', $asset->id) }}" target="_blank" class="w-full flex items-center justify-center bg-white border border-purple-200 text-purple-700 hover:bg-purple-50 py-2.5 px-4 rounded-xl text-sm transition-colors shadow-sm font-semibold">
                    <i class="fas fa-print mr-2"></i> พิมพ์สติ๊กเกอร์
                </a>
            </div>

            <div class="flex flex-col gap-3">
                @role('admin')
                <a href="{{ route('assets.edit', $asset->id) }}" class="w-full bg-amber-500 hover:bg-amber-600 text-white font-medium py-2.5 px-4 rounded-xl transition-colors shadow-sm flex items-center justify-center text-sm">
                    <i class="fas fa-edit mr-2"></i> แก้ไขข้อมูลทั้งหมด
                </a>
                @endrole
                
                <!-- Quick Actions Group -->
                <div class="flex gap-3 w-full">
                    <form action="{{ route('assets.update-status', $asset->id) }}" method="POST" class="flex-1">
                        @csrf
                        <input type="hidden" name="status" value="รอซ่อม">
                        <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-100 hover:bg-red-100 hover:border-red-200 font-semibold py-2 rounded-xl transition-all shadow-sm text-sm flex items-center justify-center">
                            <i class="fas fa-wrench mr-2"></i> แจ้งซ่อม
                        </button>
                    </form>
                    <button @click="statusModal = true" class="flex-1 bg-purple-600 text-white shadow-md shadow-purple-200 hover:bg-purple-700 font-semibold py-2 rounded-xl transition-all text-sm flex items-center justify-center">
                        <i class="fas fa-sync-alt mr-2"></i> อัปเดตสถานะ
                    </button>
                </div>
            </div>
        </div>

        <!-- Details -->
        <div class="md:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 sm:p-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-8">
                    <!-- ข้อมูลทั่วไป -->
                    <div>
                        <h4 class="text-base font-bold text-gray-800 border-b-2 border-purple-200 pb-2 mb-6 inline-flex items-center">
                            <i class="fas fa-info-circle text-purple-500 mr-2"></i> ข้อมูลทั่วไป
                        </h4>
                        
                        <div class="space-y-4">
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">ประเภท</span>
                                <span class="text-gray-800 font-medium">{{ $asset->category->name ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">รุ่น/แบบ</span>
                                <span class="text-gray-800 font-medium">{{ $asset->model ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">ลักษณะ/คุณสมบัติ</span>
                                <span class="text-gray-700 leading-relaxed text-sm">{{ $asset->specs ?? '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">สถานที่จัดเก็บ</span>
                                <span class="text-gray-800 font-medium">
                                    <span class="bg-gray-100 text-gray-700 px-2.5 py-1 rounded-md text-sm border border-gray-200 inline-flex items-center">
                                        <i class="fas fa-map-marker-alt text-gray-400 mr-1.5"></i> 
                                        {{ $asset->location->name ?? '-' }} {{ $asset->location->room_number ?? '' }}
                                    </span>
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- ข้อมูลการเงิน -->
                    <div>
                        <h4 class="text-base font-bold text-gray-800 border-b-2 border-green-200 pb-2 mb-6 inline-flex items-center">
                            <i class="fas fa-money-bill-wave text-green-500 mr-2"></i> ข้อมูลการได้มา
                        </h4>
                        
                        <div class="space-y-4">
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">ราคาต่อหน่วย</span>
                                <span class="text-green-600 font-bold text-2xl tracking-tight">{{ number_format($asset->unit_price, 2) }} <span class="text-sm font-medium text-gray-500">บาท</span></span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">วันที่ได้รับ</span>
                                <span class="text-gray-800 font-medium">{{ $asset->purchase_date ? \Carbon\Carbon::parse($asset->purchase_date)->addYears(543)->format('d/m/Y') : '-' }}</span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">วิธีการได้มา</span>
                                <span class="text-gray-800 font-medium">
                                    <span class="bg-purple-50 text-purple-700 px-2.5 py-1 rounded-md text-sm border border-purple-100">
                                        {{ $asset->acquisition_type ?? 'ไม่ระบุ' }}
                                    </span>
                                </span>
                            </div>
                            <div>
                                <span class="block text-[11px] font-bold text-gray-400 tracking-wider uppercase mb-1">เจ้าของ/หน่วยงาน</span>
                                <span class="text-gray-800 font-medium">{{ $asset->department->name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- หมายเหตุ -->
                <div class="mt-8 pt-6 border-t border-gray-100">
                    <h4 class="text-sm font-bold text-gray-800 mb-3 flex items-center">
                        <i class="fas fa-sticky-note text-amber-500 mr-2"></i> หมายเหตุ / บันทึกเพิ่มเติม
                    </h4>
                    <div class="p-4 bg-amber-50/50 rounded-xl text-gray-600 text-sm leading-relaxed border border-amber-100/50">
                        {{ $asset->note ?? 'ไม่มีบันทึกเพิ่มเติม' }}
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Update Modal -->
    <template x-teleport="body">
        <div x-show="statusModal" class="fixed inset-0 z-50 overflow-y-auto" style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="statusModal" @click="statusModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm transition-opacity"></div>
                
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="statusModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-[51]">
                    <form action="{{ route('assets.update-status', $asset->id) }}" method="POST">
                        @csrf
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4 border-b border-gray-100">
                            <div class="sm:flex sm:items-start">
                                <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-purple-50 sm:mx-0 sm:h-10 sm:w-10">
                                    <i class="fas fa-sync-alt text-purple-600 text-sm"></i>
                                </div>
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-bold text-gray-900">อัปเดตสถานะครุภัณฑ์</h3>
                                    <div class="mt-4">
                                        <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">เลือกสถานะปัจจุบัน</label>
                                        <select name="status" class="w-full px-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm font-medium appearance-none">
                                            <option value="ใช้งานปกติ" {{ $asset->status == 'ใช้งานปกติ' ? 'selected' : '' }}>ใช้งานปกติ</option>
                                            <option value="รอซ่อม" {{ $asset->status == 'รอซ่อม' ? 'selected' : '' }}>รอซ่อม</option>
                                            <option value="ชำรุด" {{ $asset->status == 'ชำรุด' ? 'selected' : '' }}>ชำรุด</option>
                                            <option value="เสื่อมสภาพ" {{ $asset->status == 'เสื่อมสภาพ' ? 'selected' : '' }}>เสื่อมสภาพ</option>
                                            <option value="จำหน่ายออก" {{ $asset->status == 'จำหน่ายออก' ? 'selected' : '' }}>จำหน่ายออก</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse border-t border-gray-100">
                            <button type="submit" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-6 py-2.5 bg-gradient-to-r from-purple-600 to-indigo-600 text-base font-medium text-white hover:from-purple-700 hover:to-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 sm:ml-3 sm:w-auto sm:text-sm transition-all active:scale-95">
                                บันทึกสถานะ
                            </button>
                            <button type="button" @click="statusModal = false" class="mt-3 w-full inline-flex justify-center rounded-xl border border-gray-300 shadow-sm px-4 py-2.5 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-200 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm transition-all">
                                ยกเลิก
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>

    <!-- Mobile Responsive Action Button Group at Bottom (Fixed) -->
    <div class="fixed bottom-0 left-0 right-0 p-4 bg-white/90 backdrop-blur-md border-t border-gray-200 shadow-[0_-10px_15px_-3px_rgba(0,0,0,0.05)] md:hidden z-50">
        <div class="flex gap-3 max-w-md mx-auto">
            <form action="{{ route('assets.update-status', $asset->id) }}" method="POST" class="flex-1">
                @csrf
                <input type="hidden" name="status" value="รอซ่อม">
                <button type="submit" class="w-full bg-red-50 text-red-600 border border-red-200 hover:bg-red-100 font-semibold py-3.5 rounded-xl transition-all active:scale-95 flex flex-col items-center justify-center gap-1">
                    <i class="fas fa-wrench text-lg"></i>
                    <span class="text-xs">แจ้งซ่อม</span>
                </button>
            </form>
            <button @click="statusModal = true" class="flex-1 bg-purple-600 text-white shadow-lg shadow-purple-600/30 hover:bg-purple-700 font-semibold py-3.5 rounded-xl transition-all active:scale-95 flex flex-col items-center justify-center gap-1">
                <i class="fas fa-sync-alt text-lg"></i>
                <span class="text-xs">อัปเดตสถานะ</span>
            </button>
        </div>
    </div>
</div>
@endsection
