@extends('layouts.app')

@section('page_title', 'ระบบบัญชีวัสดุ (Material Stock Cards)')
@section('page_description', 'สมุดบัญชีคุมพัสดุ ตรวจสอบการรับ-จ่าย-คงเหลือ และออกรายงานตามแบบมาตรฐานทางราชการ')

@section('content')
<div class="space-y-6">
    <!-- Tabs Navigation -->
    <div class="flex border-b border-gray-200 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100">
        <a href="{{ route('materials.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-boxes"></i> รายการวัสดุคงคลัง
        </a>
        <a href="{{ route('requisitions.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-file-signature"></i> ใบขอเบิกวัสดุ
        </a>
        <a href="{{ route('stock-cards.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm rounded-xl transition-all flex items-center justify-center gap-2 bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-md">
            <i class="fas fa-book"></i> สมุดบัญชีวัสดุ
        </a>
        <a href="{{ route('material-receipts.index') }}" class="flex-1 py-3 px-4 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
            <i class="fas fa-file-import"></i> รายการรับวัสดุเข้า
        </a>
    </div>

    <!-- Summary KPI Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <div class="bg-white rounded-2xl p-5 border border-purple-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">รายการวัสดุคุมบัญชีทั้งหมด</div>
                <div class="text-2xl font-bold text-purple-900 mt-1">{{ number_format($totalMaterials) }} <span class="text-sm font-normal text-gray-500">รายการ</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold border border-purple-100">
                <i class="fas fa-boxes"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-rose-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-rose-500 uppercase tracking-wider">พัสดุสต็อกต่ำกว่าเกณฑ์ (Min)</div>
                <div class="text-2xl font-bold text-rose-600 mt-1">{{ number_format($lowStockCount) }} <span class="text-sm font-normal text-gray-500">รายการ</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-rose-50 text-rose-600 flex items-center justify-center text-xl font-bold border border-rose-100">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-5 border border-emerald-100 shadow-sm flex items-center justify-between">
            <div>
                <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">มูลค่าพัสดุในคลังรวม</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1">฿{{ number_format($totalInventoryValue, 2) }}</div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold border border-emerald-100">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
        <form action="{{ route('stock-cards.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Search Keyword -->
            <div class="md:col-span-2">
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">ค้นหาคำสำคัญ / รหัสพัสดุ / ที่เก็บ</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="พิมพ์ชื่อวัสดุ, รหัสพัสดุ หรือตู้เก็บ..." class="w-full pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none transition-all">
                </div>
            </div>

            <!-- Category Filter -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">หมวดหมู่วัสดุ</label>
                <select name="category_id" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none font-medium">
                    <option value="">-- ทั้งหมด --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Alert Filter -->
            <div>
                <label class="block text-xs font-bold text-gray-600 uppercase tracking-wider mb-1.5">สถานะระดับสต็อก</label>
                <select name="status" class="w-full px-3 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none font-medium">
                    <option value="">-- ทั้งหมด --</option>
                    <option value="low" {{ request('status') == 'low' ? 'selected' : '' }}>⚠️ ต่ำกว่าเกณฑ์ (Min)</option>
                    <option value="over" {{ request('status') == 'over' ? 'selected' : '' }}>📈 เกินเกณฑ์ (Max)</option>
                    <option value="normal" {{ request('status') == 'normal' ? 'selected' : '' }}>✅ ระดับปกติ</option>
                </select>
            </div>

            <div class="md:col-span-4 flex justify-end gap-2 pt-2 border-t border-gray-100">
                <a href="{{ route('stock-cards.index') }}" class="px-4 py-2 bg-gray-100 text-gray-600 rounded-xl font-semibold text-sm hover:bg-gray-200 transition-colors flex items-center">
                    <i class="fas fa-undo mr-1.5"></i> ล้างค่า
                </a>
                <button type="submit" class="px-6 py-2 bg-purple-700 hover:bg-purple-800 text-white font-bold rounded-xl text-sm shadow-md transition-all flex items-center">
                    <i class="fas fa-filter mr-1.5"></i> กรองข้อมูล
                </button>
            </div>
        </form>
    </div>

    <!-- Stock Cards Index Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50/50">
            <h3 class="font-bold text-gray-800 text-base flex items-center">
                <i class="fas fa-book text-purple-600 mr-2"></i> บัญชีคุมพัสดุแยกตามรายการ
            </h3>
            <span class="text-xs text-gray-500">แสดงผล {{ $materials->firstItem() ?? 0 }} - {{ $materials->lastItem() ?? 0 }} จาก {{ $materials->total() }} รายการ</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-sm">
                <thead>
                    <tr class="bg-gray-100/70 border-b border-gray-200 text-xs font-bold uppercase text-gray-600">
                        <th class="p-4 pl-6 w-32">รหัสวัสดุ</th>
                        <th class="p-4">ชื่อหรือชนิดวัสดุ / สเปก</th>
                        <th class="p-4 w-36">หมวดหมู่</th>
                        <th class="p-4 w-36">ที่เก็บ</th>
                        <th class="p-4 w-28 text-right">ราคา/หน่วย</th>
                        <th class="p-4 w-40 text-center">คงเหลือ / เกณฑ์</th>
                        <th class="p-4 w-32 text-center">สถานะ</th>
                        <th class="p-4 pr-6 w-44 text-right">เปิดดูบัญชีวัสดุ</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($materials as $mat)
                    <tr class="hover:bg-purple-50/20 transition-colors">
                        <td class="p-4 pl-6 font-mono font-bold text-purple-700 text-xs">
                            {{ $mat->material_code ?? 'MAT-' . sprintf('%04d', $mat->id) }}
                        </td>
                        <td class="p-4">
                            <div class="font-bold text-gray-900">{{ $mat->name }}</div>
                            @if($mat->specs)
                                <div class="text-xs text-gray-400 mt-0.5">{{ $mat->specs }}</div>
                            @endif
                        </td>
                        <td class="p-4 text-xs font-medium text-gray-600">
                            {{ $mat->category->name ?? 'ไม่ระบุหมวด' }}
                        </td>
                        <td class="p-4 text-xs font-semibold text-gray-700">
                            <i class="fas fa-map-marker-alt text-amber-500 mr-1"></i> {{ $mat->location_name ?? '-' }}
                        </td>
                        <td class="p-4 text-right font-bold text-gray-800">
                            {{ $mat->unit_price ? '฿' . number_format($mat->unit_price, 2) : '-' }}
                        </td>
                        <td class="p-4 text-center">
                            <div class="font-bold text-base text-gray-900">{{ number_format($mat->stock_qty) }} <span class="text-xs font-normal text-gray-500">{{ $mat->unit }}</span></div>
                            <div class="text-[11px] text-gray-400 mt-0.5">Min: {{ $mat->min_stock }} | Max: {{ $mat->max_stock ?: '-' }}</div>
                        </td>
                        <td class="p-4 text-center whitespace-nowrap">
                            @if($mat->min_stock > 0 && $mat->stock_qty <= $mat->min_stock)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-rose-50 text-rose-600 border border-rose-200 shadow-sm animate-pulse">
                                    ⚠️ ต่ำกว่า Min
                                </span>
                            @elseif($mat->max_stock > 0 && $mat->stock_qty >= $mat->max_stock)
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-amber-50 text-amber-600 border border-amber-200 shadow-sm">
                                    📈 เกิน Max
                                </span>
                            @else
                                <span class="px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm">
                                    ✅ สต็อกปกติ
                                </span>
                            @endif
                        </td>
                        <td class="p-4 pr-6 text-right whitespace-nowrap">
                            <a href="{{ route('stock-cards.show', $mat->id) }}" class="px-3.5 py-2 bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold text-xs rounded-xl shadow-md transition-all inline-flex items-center">
                                <i class="fas fa-book-open mr-1.5"></i> สมุดบัญชีวัสดุ
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="p-16 text-center text-gray-400">
                            <i class="fas fa-folder-open text-4xl text-gray-300 mb-3 block"></i>
                            ไม่พบรายการวัสดุตรงตามเงื่อนไข
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($materials->hasPages())
        <div class="p-4 border-t border-gray-100 bg-gray-50/50">
            {{ $materials->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
