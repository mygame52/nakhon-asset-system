@extends('layouts.app')

@section('page_title', 'วัสดุคงคลัง & การเบิกพัสดุ')
@section('page_description', 'จัดการรายการวัสดุสิ้นเปลือง ตรวจเช็คยอดคงเหลือ และดำเนินการจัดสรรเบิกจ่าย')

@section('content')
<!-- Tabs Navigation -->
<div class="flex border-b border-gray-200 mb-6 bg-white rounded-2xl p-1.5 shadow-sm border border-gray-100">
    <a href="{{ route('materials.index') }}" class="flex-1 py-3 px-6 text-center font-bold text-sm rounded-xl transition-all flex items-center justify-center gap-2 bg-gradient-to-r from-purple-700 to-indigo-700 text-white shadow-md">
        <i class="fas fa-boxes"></i> รายการวัสดุคงคลัง
    </a>
    <a href="{{ route('requisitions.index') }}" class="flex-1 py-3 px-6 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
        <i class="fas fa-file-signature"></i> ใบขอเบิกวัสดุ
    </a>
    @hasanyrole('admin|procurement')
    <a href="{{ route('stock-cards.index') }}" class="flex-1 py-3 px-6 text-center font-bold text-sm text-gray-500 hover:text-purple-700 rounded-xl transition-all flex items-center justify-center gap-2 hover:bg-purple-50/50">
        <i class="fas fa-book"></i> สมุดบัญชีวัสดุ (Stock Cards)
    </a>
    @endhasanyrole
</div>

<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Toolbar -->
    <div class="p-6 border-b border-gray-100 bg-gray-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form action="{{ route('materials.index') }}" method="GET" class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อวัสดุ หรือรหัสพัสดุ..." class="w-full pl-10 pr-4 py-2.5 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500/20 focus:border-purple-600 transition-all outline-none text-sm font-medium shadow-sm">
        </form>
        
        <div class="flex items-center gap-3 w-full sm:w-auto">
            @hasanyrole('admin|procurement')
            <a href="{{ route('materials.create') }}" class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-lg shadow-purple-500/20 transition-all active:scale-95 flex items-center justify-center text-sm">
                <i class="fas fa-plus-circle mr-2 text-amber-300"></i> เพิ่มรายการวัสดุใหม่
            </a>
            @endhasanyrole
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[800px]">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                    <th class="p-4 px-6">ชื่อรายการวัสดุ / รหัสพัสดุ</th>
                    <th class="p-4 w-48">หมวดหมู่วัสดุ</th>
                    <th class="p-4 w-32 text-right">ยอดคงเหลือ</th>
                    <th class="p-4 w-36">สถานะสต็อก</th>
                    <th class="p-4 w-32 text-right pr-6">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100/80 text-sm">
                @forelse($materials as $material)
                <tr class="hover:bg-purple-50/30 transition-colors group">
                    <td class="p-4 px-6 font-medium text-gray-900">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-xs border border-purple-100 shrink-0">
                                <i class="fas fa-box"></i>
                            </div>
                            <div>
                                <div class="font-bold text-gray-900">{{ $material->name }}</div>
                                <div class="text-xs text-gray-400 font-mono mt-0.5">
                                    <span class="text-purple-700 font-semibold">{{ $material->material_code ?? 'MAT-'.$material->id }}</span> | หน่วยนับ: {{ $material->unit }}
                                </div>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <span class="text-xs font-semibold text-purple-800 bg-purple-50 px-2.5 py-1 rounded-lg border border-purple-100 whitespace-nowrap">
                            [{{ $material->category->code_prefix ?? '5510' }}] {{ $material->category->name ?? 'วัสดุทั่วไป' }}
                        </span>
                    </td>
                    <td class="p-4 text-right">
                        <span class="font-extrabold text-lg {{ $material->stock_qty > 0 ? 'text-emerald-600' : 'text-rose-500' }}">{{ number_format($material->stock_qty ?? 0) }}</span>
                    </td>
                    <td class="p-4">
                        @if(($material->stock_qty ?? 0) > ($material->min_stock ?? 5))
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <i class="fas fa-check-circle mr-1"></i> พร้อมจ่าย
                            </span>
                        @elseif(($material->stock_qty ?? 0) > 0)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-amber-50 text-amber-700 border border-amber-200">
                                <i class="fas fa-exclamation-triangle mr-1"></i> ใกล้หมด
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold uppercase tracking-wider bg-rose-50 text-rose-700 border border-rose-200">
                                <i class="fas fa-times-circle mr-1"></i> หมดสต็อก
                            </span>
                        @endif
                    </td>
                    <td class="p-4 text-right pr-6">
                        <div class="flex items-center justify-end gap-1.5 opacity-90 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('stock-cards.show', $material->id) }}" class="w-8 h-8 rounded-lg bg-purple-50 text-purple-700 hover:bg-purple-600 hover:text-white transition-all flex items-center justify-center text-xs" title="ดูสมุดบัญชีสต็อก">
                                <i class="fas fa-book"></i>
                            </a>
                            @hasanyrole('admin|procurement')
                            <a href="{{ route('materials.edit', $material->id) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-all flex items-center justify-center text-xs" title="แก้ไข">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('materials.destroy', $material->id) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบรายการวัสดุนี้?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white transition-all flex items-center justify-center text-xs" title="ลบ">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                            @endhasanyrole
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-16 text-center text-gray-400">
                        <div class="bg-purple-50/50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-purple-100">
                            <i class="fas fa-box-open text-3xl text-purple-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-gray-700 mb-1">ไม่พบรายการวัสดุ</h3>
                        <p class="text-sm text-gray-500">ยังไม่มีรายการวัสดุสิ้นเปลืองในระบบ</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if(isset($materials) && method_exists($materials, 'hasPages') && $materials->hasPages())
    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
        {{ $materials->links() }}
    </div>
    @endif
</div>
@endsection
