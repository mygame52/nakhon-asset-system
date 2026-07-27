@extends('layouts.app')

@section('page_title', 'ประวัติการใช้งานระบบ (Activity Logs)')
@section('page_description', 'ตรวจสอบและบันทึกประวัติการปฏิบัติงานของผู้ใช้งานในระบบตามระเบียบพัสดุราชการ')

@section('content')
<div class="space-y-6">

    <!-- Header & Action Bar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
        <div>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-indigo-700 to-purple-600 flex items-center">
                <i class="fas fa-history mr-3 text-indigo-600"></i> ประวัติการใช้งานและการปฏิบัติงานระบบ
            </h2>
            <p class="text-sm text-gray-500 mt-1">บันทึก Log การเข้าใช้งาน การเบิกพัสดุ การอนุมัติ การปรับสต็อก และการจัดการข้อมูลครุภัณฑ์</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('activity-logs.index') }}" class="px-4 py-2.5 bg-gray-50 hover:bg-gray-100 text-gray-700 font-semibold rounded-xl text-sm border border-gray-200 transition-all flex items-center">
                <i class="fas fa-sync-alt mr-2 text-gray-500"></i> รีเฟรชข้อมูล
            </a>
        </div>
    </div>

    <!-- Summary Stat Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Today -->
        <div class="bg-gradient-to-br from-indigo-50 to-white p-5 rounded-2xl border border-indigo-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-indigo-600 uppercase tracking-wider">กิจกรรมทั้งหมดวันนี้</p>
                <h3 class="text-2xl font-extrabold text-indigo-950 mt-1">{{ number_format($stats['total_today']) }}</h3>
                <p class="text-xs text-indigo-500 mt-1">รายการปฏิบัติงานในระบบ</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-600 text-white flex items-center justify-center text-xl shadow-md shadow-indigo-500/20">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>

        <!-- Requisitions Today -->
        <div class="bg-gradient-to-br from-amber-50 to-white p-5 rounded-2xl border border-amber-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-amber-600 uppercase tracking-wider">การเบิก-อนุมัติวันนี้</p>
                <h3 class="text-2xl font-extrabold text-amber-950 mt-1">{{ number_format($stats['requisition_today']) }}</h3>
                <p class="text-xs text-amber-500 mt-1">รายการขอเบิกและอนุมัติ</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-xl shadow-md shadow-amber-500/20">
                <i class="fas fa-tasks"></i>
            </div>
        </div>

        <!-- Stock Today -->
        <div class="bg-gradient-to-br from-emerald-50 to-white p-5 rounded-2xl border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-emerald-600 uppercase tracking-wider">ปรับสต็อกคลังวันนี้</p>
                <h3 class="text-2xl font-extrabold text-emerald-950 mt-1">{{ number_format($stats['stock_today']) }}</h3>
                <p class="text-xs text-emerald-500 mt-1">บันทึกรับเข้า/ตัดจ่ายคลัง</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-600 text-white flex items-center justify-center text-xl shadow-md shadow-emerald-500/20">
                <i class="fas fa-boxes"></i>
            </div>
        </div>

        <!-- Logins Today -->
        <div class="bg-gradient-to-br from-purple-50 to-white p-5 rounded-2xl border border-purple-100 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs font-bold text-purple-600 uppercase tracking-wider">การเข้าสู่ระบบวันนี้</p>
                <h3 class="text-2xl font-extrabold text-purple-950 mt-1">{{ number_format($stats['auth_today']) }}</h3>
                <p class="text-xs text-purple-500 mt-1">ครั้งเข้าใช้งานสำเร็จ</p>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-600 text-white flex items-center justify-center text-xl shadow-md shadow-purple-500/20">
                <i class="fas fa-user-shield"></i>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
        <form method="GET" action="{{ route('activity-logs.index') }}" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-4">
            
            <!-- Module Filter -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">หมวดหมู่งาน (Module)</label>
                <select name="module" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">-- ทุกโมดูลงาน --</option>
                    @foreach($modules as $key => $label)
                        <option value="{{ $key }}" {{ request('module') == $key ? 'selected' : '' }}>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- User Filter -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">ผู้ปฏิบัติงาน (User)</label>
                <select name="user_id" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <option value="">-- บุคลากรทั้งหมด --</option>
                    @foreach($users as $u)
                        <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }} ({{ $u->username }})</option>
                    @endforeach
                </select>
            </div>

            <!-- Date From -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">ตั้งแต่วันที่</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <!-- Date To -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">ถึงวันที่</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
            </div>

            <!-- Search Button Group -->
            <div>
                <label class="block text-xs font-semibold text-gray-600 mb-1">ค้นหาข้อความ / IP</label>
                <div class="flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="คำค้นหา..." class="w-full bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm focus:ring-2 focus:ring-indigo-500 outline-none">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl text-sm transition-all shadow-md flex items-center shrink-0">
                        <i class="fas fa-search mr-1"></i> กรอง
                    </button>
                    @if(request()->hasAny(['module', 'user_id', 'search', 'date_from', 'date_to']))
                        <a href="{{ route('activity-logs.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-3 py-2 rounded-xl text-sm transition-all flex items-center shrink-0" title="ล้างการกรอง">
                            <i class="fas fa-times"></i>
                        </a>
                    @endif
                </div>
            </div>
        </form>
    </div>

    <!-- Activity Logs Table Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-100 flex items-center justify-between">
            <h3 class="font-bold text-gray-800 flex items-center">
                <i class="fas fa-list-ul text-indigo-500 mr-2"></i> บันทึกประวัติการใช้งานทั้งหมด ({{ number_format($logs->total()) }} รายการ)
            </h3>
            <span class="text-xs text-gray-400 font-medium">เรียงตามวันเวลาล่าสุด</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50/80 border-b border-gray-100 text-xs font-bold text-gray-500 uppercase tracking-wider">
                        <th class="py-3.5 px-6">วันเวลา (Timestamp)</th>
                        <th class="py-3.5 px-4">ผู้ปฏิบัติงาน</th>
                        <th class="py-3.5 px-4">โมดูลงาน / กิจกรรม</th>
                        <th class="py-3.5 px-6">รายละเอียดกิจกรรม</th>
                        <th class="py-3.5 px-4 text-right">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    @forelse($logs as $log)
                        <tr class="hover:bg-gray-50/50 transition-colors">
                            <!-- Date & Time -->
                            <td class="py-4 px-6 whitespace-nowrap">
                                <div class="font-bold text-gray-900">
                                    {{ $log->created_at->addYears(543)->format('d/m/Y') }}
                                </div>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">
                                    <i class="far fa-clock mr-1"></i>{{ $log->created_at->format('H:i:s') }} น.
                                </div>
                            </td>

                            <!-- User -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @if($log->user)
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-8 h-8 rounded-full bg-gradient-to-tr from-indigo-500 to-purple-600 text-white font-bold flex items-center justify-center text-xs shadow-sm">
                                            {{ mb_substr($log->user->name, 0, 1, 'UTF-8') }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-gray-900 text-sm">{{ $log->user->name }}</div>
                                            <div class="text-xs text-gray-400">@ {{ $log->user->username }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-gray-400 italic">ระบบอัตโนมัติ</span>
                                @endif
                            </td>

                            <!-- Module & Action Badge -->
                            <td class="py-4 px-4 whitespace-nowrap">
                                @php
                                    $modColor = match($log->module) {
                                        'requisition' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'material_stock' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'material' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        'asset' => 'bg-blue-50 text-blue-700 border-blue-200',
                                        'auth' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                        'user' => 'bg-pink-50 text-pink-700 border-pink-200',
                                        default => 'bg-gray-50 text-gray-700 border-gray-200',
                                    };
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg border text-xs font-bold inline-block mb-1 {{ $modColor }}">
                                    {{ $modules[$log->module] ?? $log->module }}
                                </span>
                                <div class="text-xs font-mono font-semibold text-gray-500">
                                    {{ $log->action }}
                                </div>
                            </td>

                            <!-- Description -->
                            <td class="py-4 px-6 leading-relaxed text-gray-800">
                                {{ $log->description }}
                            </td>

                            <!-- IP Address -->
                            <td class="py-4 px-4 whitespace-nowrap text-right font-mono text-xs text-gray-500">
                                {{ $log->ip_address ?? '127.0.0.1' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-12 text-center text-gray-400">
                                <i class="fas fa-history text-4xl mb-3 text-gray-200 block"></i>
                                ไม่พบข้อมูลประวัติการใช้งานตามเงื่อนไขที่เลือก
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($logs->hasPages())
            <div class="px-6 py-4 border-t border-gray-100 bg-gray-50/50">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
