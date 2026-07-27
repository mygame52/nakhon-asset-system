@extends('layouts.app')

@section('page_title', 'แก้ไขข้อมูลผู้ใช้งาน')
@section('page_description', 'ปรับปรุงรายละเอียดบัญชี หรือเปลี่ยนระดับสิทธิ์ Role')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-4xl mx-auto">
    <div class="mb-8 border-b border-gray-100 pb-4 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-amber-500 to-orange-500 flex items-center">
                <i class="fas fa-user-edit mr-3 text-amber-500"></i> แก้ไขข้อมูล: {{ $user->name }}
            </h2>
            <p class="text-sm text-gray-500 mt-2">หากไม่ต้องการเปลี่ยนรหัสผ่าน ให้เว้นช่องรหัสผ่านไว้ว่างๆ</p>
        </div>
        <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-amber-600 transition-colors bg-gray-50 hover:bg-amber-50 px-4 py-2 rounded-xl text-sm font-semibold flex items-center border border-gray-200">
            <i class="fas fa-arrow-left mr-2"></i> ย้อนกลับ
        </a>
    </div>

    @if ($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-50 border border-red-200 text-red-700">
            <div class="flex items-center font-bold mb-2">
                <i class="fas fa-exclamation-circle mr-2"></i> ข้อผิดพลาด:
            </div>
            <ul class="list-disc pl-5 text-sm space-y-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('users.update', $user->id) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-id-card text-gray-400"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Department -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">กลุ่มงาน / สังกัด</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-building text-gray-400"></i>
                    </div>
                    <select name="department_id" class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all outline-none appearance-none text-sm">
                        <option value="">-- ไม่ระบุ --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id', $user->department_id) == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>

            <div class="md:col-span-2 border-t border-gray-100 my-2 pt-6">
                <h3 class="text-sm font-bold text-gray-800 mb-4 uppercase tracking-wider">ข้อมูลการเข้าใช้งาน (Login)</h3>
            </div>

            <!-- Username -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">Username <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user-circle text-gray-400"></i>
                    </div>
                    <input type="text" name="username" value="{{ old('username', $user->username) }}" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">อีเมล (Email) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">รหัสผ่านใหม่ <span class="text-gray-400 font-normal ml-2">(เว้นว่างไว้เพื่อใช้รหัสผ่านเดิม)</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-key text-gray-400"></i>
                    </div>
                    <input type="text" name="password" placeholder="พิมพ์รหัสผ่านใหม่หากต้องการเปลี่ยน..." class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">ระดับสิทธิ์การทำงาน (Role) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-shield-alt text-gray-400"></i>
                    </div>
                    <select name="roles[]" required class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-amber-500 focus:border-amber-500 focus:bg-white transition-all outline-none appearance-none text-sm font-semibold text-amber-700">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ in_array($role->name, old('roles', $userRoles)) ? 'selected' : '' }}>
                                @switch($role->name)
                                    @case('admin') ⚙️ หัวหน้าเจ้าหน้าที่ (Admin) @break
                                    @case('procurement') 📦 เจ้าหน้าที่จ่าย (Procurement Officer) @break
                                    @case('user') 👤 ผู้ขอเบิก (User) @break
                                    @default {{ $role->name }}
                                @endswitch
                            </option>
                        @endforeach
                    </select>
                    <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end">
            <button type="submit" class="bg-gradient-to-r from-amber-500 to-orange-500 hover:from-amber-600 hover:to-orange-600 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-amber-500/30 transition-all active:scale-95 flex items-center">
                <i class="fas fa-save mr-2"></i> อัปเดตข้อมูลบัญชี
            </button>
        </div>
    </form>
</div>
@endsection
