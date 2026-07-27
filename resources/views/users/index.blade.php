@extends('layouts.app')

@section('page_title', 'ระบบจัดการผู้ใช้งาน')
@section('page_description', 'กำหนดสิทธิ์ Role และสังกัดแผนกให้กับบุคลากรในหน่วยงาน')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
    <!-- Toolbar -->
    <div class="p-6 border-b border-gray-100 bg-gray-50/30 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <form action="{{ route('users.index') }}" method="GET" class="relative w-full sm:w-80">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <i class="fas fa-search text-gray-400"></i>
            </div>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="ค้นหาชื่อ, Username หรือ Email..." class="w-full pl-10 pr-4 py-2 bg-white border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 transition-all outline-none text-sm shadow-sm">
        </form>
        
        <a href="{{ route('users.create') }}" class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold py-2 px-5 rounded-xl shadow-md shadow-purple-500/20 transition-all active:scale-95 flex items-center justify-center text-sm">
            <i class="fas fa-user-plus mr-2 text-purple-200"></i> เพิ่มผู้ใช้งานใหม่
        </a>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse min-w-[900px]">
            <thead>
                <tr class="bg-gray-50/80 border-b border-gray-200 text-xs uppercase tracking-wider text-gray-500 font-bold">
                    <th class="p-4 px-6">ชื่อ-นามสกุล</th>
                    <th class="p-4 w-48">รายละเอียดล็อกอิน</th>
                    <th class="p-4 w-40">กลุ่มงาน/หน่วยงาน</th>
                    <th class="p-4 w-32 text-center">บทบาท (Role)</th>
                    <th class="p-4 w-28 text-right pr-6">จัดการ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100/80 text-sm">
                @forelse($users as $user)
                <tr class="hover:bg-purple-50/30 transition-colors group">
                    <td class="p-4 px-6">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-indigo-100 to-purple-100 border border-purple-200 flex items-center justify-center text-purple-700 font-bold text-sm shadow-sm">
                                {{ mb_substr($user->name, 0, 1) }}
                            </div>
                            <div>
                                <div class="font-bold text-gray-800">{{ $user->name }}</div>
                                <div class="text-[11px] text-gray-400 mt-0.5">ลงทะเบียน: {{ $user->created_at->format('d/m/Y') }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="p-4">
                        <div class="font-medium text-purple-700 font-mono text-xs mb-1"><i class="fas fa-user-circle text-gray-400 mr-1"></i> {{ $user->username }}</div>
                        <div class="text-[11px] text-gray-500"><i class="fas fa-envelope text-gray-400 mr-1"></i> {{ $user->email }}</div>
                    </td>
                    <td class="p-4">
                        <span class="text-xs font-semibold text-gray-600 bg-gray-100 px-2.5 py-1 rounded-md border border-gray-200 shadow-sm whitespace-nowrap">
                            {{ $user->department->name ?? 'ไม่ได้ระบุฝ่าย' }}
                        </span>
                    </td>
                    <td class="p-4 text-center whitespace-nowrap">
                        @forelse($user->roles as $r)
                            @switch($r->name)
                                @case('admin')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200 shadow-sm">
                                        ⚙️ หัวหน้าเจ้าหน้าที่
                                    </span>
                                    @break
                                @case('procurement')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-blue-50 text-blue-700 border border-blue-200 shadow-sm">
                                        📦 เจ้าหน้าที่จ่าย
                                    </span>
                                    @break
                                @case('user')
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm">
                                        👤 ผู้ขอเบิก (User)
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-gray-50 text-gray-600 border border-gray-200 shadow-sm">
                                        {{ $r->name }}
                                    </span>
                            @endswitch
                        @empty
                            <span class="inline-flex items-center px-2.5 py-1 rounded-md text-[10px] font-bold uppercase tracking-wider bg-gray-100 text-gray-500 border border-gray-200">
                                User
                            </span>
                        @endforelse
                    </td>
                    <td class="p-4 text-right pr-6">
                        <div class="flex items-center justify-end gap-2 opacity-0 md:opacity-100 group-hover:opacity-100 transition-opacity">
                            <a href="{{ route('users.edit', $user->id) }}" class="w-8 h-8 rounded-lg bg-amber-50 text-amber-500 hover:bg-amber-500 hover:text-white transition-colors flex items-center justify-center tooltip" title="แก้ไข">
                                <i class="fas fa-edit text-[11px]"></i>
                            </a>
                            @if(auth()->id() !== $user->id)
                            <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('ยืนยันการลบบัญชีผู้ใช้งานนี้? การกระทำนี้ไม่สามารถย้อนกลับได้')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors flex items-center justify-center tooltip" title="ลบ">
                                    <i class="fas fa-trash-alt text-[11px]"></i>
                                </button>
                            </form>
                            @else
                            <button type="button" class="w-8 h-8 rounded-lg bg-gray-100 text-gray-300 cursor-not-allowed flex items-center justify-center" title="ไม่สามารถลบบัญชีตัวเองได้">
                                <i class="fas fa-trash-alt text-[11px]"></i>
                            </button>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="p-16 text-center text-gray-400">
                        <div class="bg-gray-50 w-20 h-20 rounded-3xl flex items-center justify-center mx-auto mb-4 border border-gray-100">
                            <i class="fas fa-users-slash text-3xl text-gray-300"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-700 mb-1">ไม่พบข้อมูล</h3>
                        <p class="text-sm text-gray-500">เพิ่งเริ่มระบบใช่ไหม? สร้างผู้ใช้คนแรกกันเลย</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <!-- Pagination -->
    @if($users->hasPages())
    <div class="p-4 border-t border-gray-100 bg-gray-50/50">
        {{ $users->links() }}
    </div>
    @endif
</div>
@endsection
