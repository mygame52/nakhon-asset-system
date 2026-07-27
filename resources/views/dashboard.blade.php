@extends('layouts.app')

@section('page_title', 'แดชบอร์ดสรุปผล')
@section('page_description', 'ภาพรวมพัสดุและครุภัณฑ์ สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช')

@section('content')
<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
    <!-- Stat Cards -->
    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:scale-110 transition-transform duration-500">
            <i class="fas fa-laptop-code text-8xl text-purple-600"></i>
        </div>
        <div class="flex justify-between items-center relative z-10">
            <div>
                <p class="text-[13px] font-bold text-gray-500 mb-1 tracking-wider uppercase">ครุภัณฑ์ทั้งหมด (ชุด)</p>
                <h3 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-indigo-600">{{ number_format($totalAssets ?? 0) }}</h3>
            </div>
            <div class="w-14 h-14 bg-gradient-to-br from-purple-50 to-indigo-50 border border-purple-100 rounded-2xl flex justify-center items-center text-purple-600 shadow-inner">
                <i class="fas fa-laptop-code text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs font-semibold text-emerald-600">
            <i class="fas fa-arrow-up mr-1 text-[10px]"></i> เพิ่มเติมสัปดาห์นี้
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:scale-110 transition-transform duration-500">
            <i class="fas fa-boxes text-8xl text-emerald-500"></i>
        </div>
        <div class="flex justify-between items-center relative z-10">
            <div>
                <p class="text-[13px] font-bold text-gray-500 mb-1 tracking-wider uppercase">รายการวัสดุ</p>
                <h3 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-emerald-600 to-teal-500">{{ number_format($totalMaterials ?? 0) }}</h3>
            </div>
            <div class="w-14 h-14 bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-100 rounded-2xl flex justify-center items-center text-emerald-600 shadow-inner">
                <i class="fas fa-boxes text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs font-semibold text-emerald-600">
            <i class="fas fa-check-circle mr-1 text-[10px]"></i> สต็อกปกติ
        </div>
    </div>

    <div class="bg-white rounded-2xl p-6 shadow-sm border border-gray-100 hover:shadow-md transition-shadow relative overflow-hidden group">
        <div class="absolute top-0 right-0 p-6 opacity-5 group-hover:scale-110 transition-transform duration-500">
            <i class="fas fa-clipboard-check text-8xl text-amber-500"></i>
        </div>
        <div class="flex justify-between items-center relative z-10">
            <div>
                <p class="text-[13px] font-bold text-gray-500 mb-1 tracking-wider uppercase">รอตรวจสอบ</p>
                <h3 class="text-4xl font-bold text-transparent bg-clip-text bg-gradient-to-r from-amber-500 to-orange-500">0</h3>
            </div>
            <div class="w-14 h-14 bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-100 rounded-2xl flex justify-center items-center text-amber-500 shadow-inner">
                <i class="fas fa-clipboard-check text-xl"></i>
            </div>
        </div>
        <div class="mt-4 pt-4 border-t border-gray-50 flex items-center text-xs font-semibold text-gray-400">
            <i class="fas fa-minus mr-1 text-[10px]"></i> ไม่มีคำขอใหม่
        </div>
    </div>
</div>

<!-- Status Distribution -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-emerald-50 border border-emerald-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all text-center group">
        <p class="text-[10px] font-bold text-emerald-600 uppercase tracking-widest mb-1">ใช้งานปกติ</p>
        <h4 class="text-2xl font-bold text-emerald-700">{{ number_format($statusCounts['normal'] ?? 0) }}</h4>
        <div class="text-[10px] text-emerald-500 font-semibold mt-1 opacity-0 group-hover:opacity-100 transition-opacity italic">พร้อมใช้งาน</div>
    </div>
    <div class="bg-amber-50 border border-amber-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all text-center group">
        <p class="text-[10px] font-bold text-amber-600 uppercase tracking-widest mb-1">รอซ่อม</p>
        <h4 class="text-2xl font-bold text-amber-700">{{ number_format($statusCounts['repair'] ?? 0) }}</h4>
        <div class="text-[10px] text-amber-500 font-semibold mt-1 opacity-0 group-hover:opacity-100 transition-opacity italic">ต้องดำเนินการ</div>
    </div>
    <div class="bg-orange-50 border border-orange-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all text-center group">
        <p class="text-[10px] font-bold text-orange-600 uppercase tracking-widest mb-1">ชำรุด</p>
        <h4 class="text-2xl font-bold text-orange-700">{{ number_format($statusCounts['broken'] ?? 0) }}</h4>
        <div class="text-[10px] text-orange-500 font-semibold mt-1 opacity-0 group-hover:opacity-100 transition-opacity italic">ไม่สามารถใช้ได้</div>
    </div>
    <div class="bg-rose-50 border border-rose-100 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all text-center group">
        <p class="text-[10px] font-bold text-rose-600 uppercase tracking-widest mb-1">เสื่อมสภาพ</p>
        <h4 class="text-2xl font-bold text-rose-700">{{ number_format($statusCounts['deteriorated'] ?? 0) }}</h4>
        <div class="text-[10px] text-rose-500 font-semibold mt-1 opacity-0 group-hover:opacity-100 transition-opacity italic">หมดอายุการใช้งาน</div>
    </div>
    <div class="bg-gray-100 border border-gray-200 rounded-2xl p-4 shadow-sm hover:shadow-md transition-all text-center group md:col-span-1 col-span-2">
        <p class="text-[10px] font-bold text-gray-500 uppercase tracking-widest mb-1">จำหน่ายออก</p>
        <h4 class="text-2xl font-bold text-gray-700">{{ number_format($statusCounts['disposed'] ?? 0) }}</h4>
        <div class="text-[10px] text-gray-400 font-semibold mt-1 opacity-0 group-hover:opacity-100 transition-opacity italic">ตัดออกจากระบบ</div>
    </div>
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <!-- Recent Assets Table -->
    <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-6 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h4 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-history text-purple-500 mr-2"></i> ครุภัณฑ์ล่าสุด
            </h4>
            <a href="{{ route('assets.index') }}" class="text-xs font-semibold text-purple-600 hover:text-purple-800 bg-purple-50 hover:bg-purple-100 px-3 py-1.5 rounded-lg transition-colors">
                ดูทั้งหมด <i class="fas fa-arrow-right ml-1"></i>
            </a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse min-w-[600px]">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-[11px] uppercase tracking-wider text-gray-500 font-bold">
                        <th class="p-4 px-6 rounded-tl-xl whitespace-nowrap">รหัสครุภัณฑ์</th>
                        <th class="p-4">ชื่อรายการ</th>
                        <th class="p-4">สถานที่</th>
                        <th class="p-4 text-right pr-6">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50/80 text-sm">
                    @forelse($recentAssets ?? [] as $asset)
                    <tr class="hover:bg-purple-50/30 transition-colors group">
                        <td class="p-4 px-6 font-semibold text-purple-700 whitespace-nowrap">{{ $asset->asset_code }}</td>
                        <td class="p-4 font-medium text-gray-800">
                            {{ $asset->name }}
                        </td>
                        <td class="p-4 text-gray-600 flex items-center">
                            @if($asset->location)
                                <i class="fas fa-map-marker-alt text-gray-400 text-[10px] mr-1.5"></i> {{ $asset->location->name }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-4 text-right pr-6">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-200">
                                {{ $asset->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="p-12 text-center text-gray-400">
                            <div class="bg-gray-50 w-16 h-16 rounded-2xl flex items-center justify-center mx-auto mb-3">
                                <i class="fas fa-box-open text-2xl text-gray-300"></i>
                            </div>
                            <p class="font-medium text-gray-500">ยังไม่มีข้อมูลครุภัณฑ์ในระบบ</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gradient-to-br from-[#2a0845] to-[#12001c] rounded-2xl shadow-xl shadow-purple-900/10 border border-purple-900/50 p-6 flex flex-col relative overflow-hidden">
        <div class="absolute -right-10 -top-10 w-40 h-40 bg-purple-500 opacity-20 blur-3xl rounded-full pointer-events-none"></div>
        <div class="absolute -left-10 -bottom-10 w-40 h-40 bg-amber-500 opacity-10 blur-3xl rounded-full pointer-events-none"></div>
        
        <h4 class="font-bold text-amber-400 mb-6 flex items-center tracking-wide relative z-10">
            <i class="fas fa-bolt text-amber-300 mr-2"></i> ทางลัดด่วน
        </h4>
        
        <div class="flex flex-col gap-3 flex-1 relative z-10">
            @hasanyrole('admin|procurement')
            <a href="{{ route('assets.create') }}" class="flex items-center p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-300 group hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <i class="fas fa-plus"></i>
                </div>
                <div class="ml-4">
                    <span class="block text-white font-medium">ลงทะเบียนครุภัณฑ์</span>
                    <span class="block text-xs text-white/50">พด. 1 ใหม่</span>
                </div>
                <i class="fas fa-chevron-right ml-auto text-white/20 group-hover:text-white/70 transition-colors"></i>
            </a>
            
            <a href="{{ route('assets.batch-print') }}" class="flex items-center p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-300 group hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-blue-500/30 group-hover:scale-110 transition-transform">
                    <i class="fas fa-print"></i>
                </div>
                <div class="ml-4">
                    <span class="block text-white font-medium">พิมพ์รหัส QR Code</span>
                    <span class="block text-xs text-white/50">สำหรับติดครุภัณฑ์</span>
                </div>
                <i class="fas fa-chevron-right ml-auto text-white/20 group-hover:text-white/70 transition-colors"></i>
            </a>
            
            <a href="{{ route('assets.export') }}" class="flex items-center p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-300 group hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-white shadow-lg shadow-orange-500/30 group-hover:scale-110 transition-transform">
                    <i class="fas fa-file-excel"></i>
                </div>
                <div class="ml-4">
                    <span class="block text-white font-medium">รายงานสรุป</span>
                    <span class="block text-xs text-white/50">ส่งออก Excel ทะเบียนคุม</span>
                </div>
                <i class="fas fa-chevron-right ml-auto text-white/20 group-hover:text-white/70 transition-colors"></i>
            </a>
            @else
            <a href="{{ route('assets.index') }}" class="flex items-center p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-300 group hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-purple-400 to-indigo-600 flex items-center justify-center text-white shadow-lg shadow-purple-500/30 group-hover:scale-110 transition-transform">
                    <i class="fas fa-search"></i>
                </div>
                <div class="ml-4">
                    <span class="block text-white font-medium">ค้นหาครุภัณฑ์ & แจ้งซ่อม</span>
                    <span class="block text-xs text-white/50">ดูรายการและอัปเดตสถานะ</span>
                </div>
                <i class="fas fa-chevron-right ml-auto text-white/20 group-hover:text-white/70 transition-colors"></i>
            </a>

            <a href="{{ route('materials.index') }}" class="flex items-center p-4 rounded-xl bg-white/5 border border-white/10 hover:bg-white/10 hover:border-white/20 transition-all duration-300 group hover:-translate-y-1">
                <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-emerald-400 to-teal-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 group-hover:scale-110 transition-transform">
                    <i class="fas fa-boxes"></i>
                </div>
                <div class="ml-4">
                    <span class="block text-white font-medium">เบิกจ่ายพัสดุ/วัสดุ</span>
                    <span class="block text-xs text-white/50">ตรวจสอบสต็อกและบันทึกการเบิก</span>
                </div>
                <i class="fas fa-chevron-right ml-auto text-white/20 group-hover:text-white/70 transition-colors"></i>
            </a>
            @endhasanyrole
        </div>
    </div>
</div>
@endsection
