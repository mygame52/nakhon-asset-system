@extends('layouts.app')

@section('page_title', 'หมวดหมู่พัสดุ (วัสดุ & ครุภัณฑ์)')
@section('page_description', 'จำแนกประเภทวัสดุและครุภัณฑ์ ตามหลักเกณฑ์มาตรฐานกรมบัญชีกลาง กระทรวงการคลัง')

@section('content')
<div class="space-y-6" x-data="{ 
    editModal: false, 
    editId: null, 
    editName: '', 
    editType: 'asset',
    editCodePrefix: '',
    editDescription: '',
    openEdit(category) {
        this.editId = category.id;
        this.editName = category.name;
        this.editType = category.type;
        this.editCodePrefix = category.code_prefix || '';
        this.editDescription = category.description || '';
        this.editModal = true;
    }
}">
    <!-- Top Filter Bar & Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        <a href="{{ route('categories.index') }}" class="bg-white rounded-2xl p-5 border border-purple-100 shadow-sm flex items-center justify-between hover:shadow-md transition-all group">
            <div>
                <div class="text-xs font-bold text-gray-400 uppercase tracking-wider">หมวดหมู่พัสดุทั้งหมด</div>
                <div class="text-2xl font-bold text-purple-900 mt-1">{{ number_format(count($categories)) }} <span class="text-sm font-normal text-gray-500">หมวด</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-purple-50 text-purple-600 flex items-center justify-center text-xl font-bold border border-purple-100 group-hover:scale-110 transition-transform">
                <i class="fas fa-tags"></i>
            </div>
        </a>

        <a href="{{ route('categories.index', ['type' => 'material']) }}" class="bg-white rounded-2xl p-5 border border-emerald-100 shadow-sm flex items-center justify-between hover:shadow-md transition-all group">
            <div>
                <div class="text-xs font-bold text-emerald-600 uppercase tracking-wider">📦 หมวดหมู่วัสดุ (Materials)</div>
                <div class="text-2xl font-bold text-emerald-700 mt-1">{{ number_format($materialCount) }} <span class="text-sm font-normal text-gray-500">หมวดมาตรฐาน</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-xl font-bold border border-emerald-100 group-hover:scale-110 transition-transform">
                <i class="fas fa-box"></i>
            </div>
        </a>

        <a href="{{ route('categories.index', ['type' => 'asset']) }}" class="bg-white rounded-2xl p-5 border border-indigo-100 shadow-sm flex items-center justify-between hover:shadow-md transition-all group">
            <div>
                <div class="text-xs font-bold text-indigo-600 uppercase tracking-wider">🖥️ หมวดหมู่ครุภัณฑ์ (Assets พด.1)</div>
                <div class="text-2xl font-bold text-indigo-700 mt-1">{{ number_format($assetCount) }} <span class="text-sm font-normal text-gray-500">หมวดมาตรฐาน</span></div>
            </div>
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-xl font-bold border border-indigo-100 group-hover:scale-110 transition-transform">
                <i class="fas fa-laptop"></i>
            </div>
        </a>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Add Category Form -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 self-start">
            <h4 class="text-lg font-bold text-transparent bg-clip-text bg-gradient-to-r from-purple-700 to-indigo-600 mb-6 flex items-center border-b border-gray-100 pb-3">
                <i class="fas fa-plus-circle mr-2 text-purple-500"></i> เพิ่มหมวดหมู่พัสดุใหม่
            </h4>
            <form action="{{ route('categories.store') }}" method="POST" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ประเภทพัสดุหลัก <span class="text-red-500">*</span></label>
                    <select name="type" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-bold outline-none">
                        <option value="material">📦 วัสดุ (Materials - สิ้นเปลือง/ใช้หมดไป)</option>
                        <option value="asset">🖥️ ครุภัณฑ์ (Assets - ทรัพย์สินคงทน/พด.1)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ชื่อหมวดหมู่พัสดุ <span class="text-red-500">*</span></label>
                    <input type="text" name="name" required placeholder="เช่น วัสดุสำนักงาน หรือ ครุภัณฑ์คอมพิวเตอร์" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">รหัสจำแนกหมวดหมู่ (Code Prefix)</label>
                    <input type="text" name="code_prefix" placeholder="เช่น 5510 หรือ 7440" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-mono font-bold text-purple-700 outline-none">
                    <p class="text-[11px] text-gray-400 mt-1">รหัสมาตรฐานประจำหมวด เช่น 5510 (วัสดุสำนักงาน), 7440 (ครุภัณฑ์คอมพิวเตอร์)</p>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">คำอธิบายและรายการตัวอย่างพัสดุ</label>
                    <textarea name="description" rows="3" placeholder="ระบุตัวอย่างรายการพัสดุในหมวดนี้..." class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none resize-none"></textarea>
                </div>

                <button type="submit" class="w-full bg-gradient-to-r from-purple-700 to-indigo-700 hover:from-purple-800 hover:to-indigo-800 text-white font-bold py-3 px-4 rounded-xl shadow-md transition-all active:scale-95 flex items-center justify-center text-sm">
                    <i class="fas fa-save mr-2"></i> บันทึกหมวดหมู่พัสดุ
                </button>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Toolbar Tabs & Filter -->
            <div class="p-5 border-b border-gray-100 bg-gray-50/50 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-2">
                    <a href="{{ route('categories.index') }}" class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ !request('type') ? 'bg-purple-700 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        ทั้งหมด ({{ count($categories) }})
                    </a>
                    <a href="{{ route('categories.index', ['type' => 'material']) }}" class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('type') == 'material' ? 'bg-emerald-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        📦 วัสดุ ({{ $materialCount }})
                    </a>
                    <a href="{{ route('categories.index', ['type' => 'asset']) }}" class="px-3.5 py-1.5 rounded-xl font-bold text-xs transition-all {{ request('type') == 'asset' ? 'bg-indigo-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        🖥️ ครุภัณฑ์ ({{ $assetCount }})
                    </a>
                </div>

                <!-- Search Input -->
                <form action="{{ route('categories.index') }}" method="GET" class="relative w-full sm:w-64">
                    @if(request('type'))
                        <input type="hidden" name="type" value="{{ request('type') }}">
                    @endif
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-search text-gray-400"></i>
                    </div>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาหมวดหมู่/รหัส..." class="w-full pl-9 pr-3 py-1.5 bg-white border border-gray-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-purple-500">
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse min-w-[600px] text-sm">
                    <thead>
                        <tr class="bg-gray-100/70 border-b border-gray-200 text-xs font-bold uppercase text-gray-600">
                            <th class="p-4 pl-6 w-24">รหัสหมวด</th>
                            <th class="p-4">ชื่อหมวดหมู่ & รายการพัสดุตัวอย่าง</th>
                            <th class="p-4 w-32 text-center">ประเภท</th>
                            <th class="p-4 pr-6 w-28 text-right">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        @forelse($categories as $category)
                        <tr class="hover:bg-purple-50/20 transition-colors">
                            <td class="p-4 pl-6 font-mono font-bold text-purple-800 text-xs">
                                {{ $category->code_prefix ?? '-' }}
                            </td>
                            <td class="p-4">
                                <div class="font-bold text-gray-900 flex items-center">
                                    @if($category->type == 'asset')
                                        <i class="fas fa-laptop text-indigo-500 mr-2"></i>
                                    @else
                                        <i class="fas fa-box text-emerald-500 mr-2"></i>
                                    @endif
                                    {{ $category->name }}
                                </div>
                                @if($category->description)
                                    <div class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $category->description }}</div>
                                @endif
                            </td>
                            <td class="p-4 text-center">
                                @if($category->type == 'asset')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-indigo-50 text-indigo-700 border border-indigo-200">
                                        🖥️ ครุภัณฑ์
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                        📦 วัสดุ
                                    </span>
                                @endif
                            </td>
                            <td class="p-4 pr-6 text-right whitespace-nowrap">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button type="button" @click="openEdit({{ $category->toJson() }})" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-600 hover:text-white border border-blue-200 transition-colors flex items-center justify-center" title="แก้ไข">
                                        <i class="fas fa-edit text-xs"></i>
                                    </button>
                                    <form action="{{ route('categories.destroy', $category->id) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบหมวดหมู่นี้?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-8 h-8 rounded-lg bg-rose-50 text-rose-600 hover:bg-rose-600 hover:text-white border border-rose-200 transition-colors flex items-center justify-center" title="ลบ">
                                            <i class="fas fa-trash-alt text-xs"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="p-12 text-center text-gray-400 font-medium">
                                <i class="fas fa-tags text-4xl text-gray-300 mb-3 block"></i>
                                ไม่พบหมวดหมู่พัสดุในระบบ
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Edit Category Modal -->
    <template x-teleport="body">
        <div x-show="editModal" style="display: none;" class="fixed inset-0 z-50 overflow-y-auto" role="dialog" aria-modal="true">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="editModal" @click="editModal = false" x-transition.opacity class="fixed inset-0 bg-gray-900 bg-opacity-75 backdrop-blur-sm"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

                <div x-show="editModal" x-transition.scale class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full relative z-50">
                    <form :action="'{{ url('categories') }}/' + editId" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="bg-gradient-to-r from-purple-700 to-indigo-700 px-6 py-4 text-white flex justify-between items-center">
                            <h3 class="font-bold text-lg flex items-center">
                                <i class="fas fa-edit mr-2"></i> แก้ไขหมวดหมู่พัสดุ
                            </h3>
                            <button type="button" @click="editModal = false" class="text-white/60 hover:text-white"><i class="fas fa-times text-lg"></i></button>
                        </div>

                        <div class="p-6 space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ประเภทพัสดุหลัก <span class="text-red-500">*</span></label>
                                <select name="type" x-model="editType" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-bold outline-none">
                                    <option value="material">📦 วัสดุ (Materials - สิ้นเปลือง/ใช้หมดไป)</option>
                                    <option value="asset">🖥️ ครุภัณฑ์ (Assets - ทรัพย์สินคงทน/พด.1)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ชื่อหมวดหมู่พัสดุ <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="editName" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">รหัสจำแนกหมวดหมู่ (Code Prefix)</label>
                                <input type="text" name="code_prefix" x-model="editCodePrefix" placeholder="เช่น 5510 หรือ 7440" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-mono font-bold text-purple-700 outline-none">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">คำอธิบายและรายการตัวอย่างพัสดุ</label>
                                <textarea name="description" x-model="editDescription" rows="3" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm outline-none resize-none"></textarea>
                            </div>
                        </div>

                        <div class="bg-gray-50 px-6 py-4 border-t border-gray-100 flex justify-end gap-3">
                            <button type="button" @click="editModal = false" class="px-5 py-2.5 bg-white border border-gray-300 rounded-xl text-gray-700 font-semibold text-sm hover:bg-gray-100">ยกเลิก</button>
                            <button type="submit" class="px-6 py-2.5 bg-purple-700 text-white font-bold rounded-xl text-sm shadow-md hover:bg-purple-800 transition-all">บันทึกการแก้ไข</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </template>
</div>
@endsection
