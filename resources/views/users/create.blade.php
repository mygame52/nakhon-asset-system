@extends('layouts.app')

@section('page_title', 'เพิ่มผู้ใช้งานใหม่')
@section('page_description', 'สร้างบัญชีเข้าใช้งานระบบสำหรับบุคลากรใหม่')

@section('content')
<div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 md:p-8 max-w-4xl mx-auto">
    <div class="mb-8 border-b border-gray-100 pb-4 flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-700 to-indigo-600 flex items-center">
                <i class="fas fa-user-plus mr-3 text-purple-500"></i> ข้อมูลบัญชีผู้ใช้งาน
            </h2>
            <p class="text-sm text-gray-500 mt-2">กรุณากรอกข้อมูลส่วนตัวและกำหนดสิทธิ์ให้ครบถ้วน</p>
        </div>
        <a href="{{ route('users.index') }}" class="text-gray-500 hover:text-purple-600 transition-colors bg-gray-50 hover:bg-purple-50 px-4 py-2 rounded-xl text-sm font-semibold flex items-center border border-gray-200">
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

    <form action="{{ route('users.store') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Name -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-id-card text-gray-400"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name') }}" required placeholder="ตัวอย่าง: สมหมาย สกร.นคร" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Department -->
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-gray-700 mb-2">กลุ่มงาน / สังกัด</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-building text-gray-400"></i>
                    </div>
                    <select name="department_id" class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none appearance-none text-sm">
                        <option value="">-- ไม่ระบุ --</option>
                        @foreach($departments as $dept)
                            <option value="{{ $dept->id }}" {{ old('department_id') == $dept->id ? 'selected' : '' }}>{{ $dept->name }}</option>
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
                    <input type="text" name="username" value="{{ old('username') }}" required placeholder="ตั้งชื่อผู้ใช้ (ภาษาอังกฤษ/ตัวเลข)" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Email -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">อีเมล (Email) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-envelope text-gray-400"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email') }}" required placeholder="example@nakhon.go.th" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Password -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">รหัสผ่าน <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="text" name="password" required placeholder="รหัสผ่านเริ่มต้น 8+ ตัวอักษร" class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none text-sm">
                </div>
            </div>

            <!-- Role Selection -->
            <div>
                <label class="block text-sm font-semibold text-gray-700 mb-2">ระดับสิทธิ์การทำงาน (Role) <span class="text-red-500">*</span></label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-shield-alt text-gray-400"></i>
                    </div>
                    <select name="roles[]" required class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-purple-500 focus:bg-white transition-all outline-none appearance-none text-sm font-semibold text-purple-700">
                        @foreach($roles as $role)
                            <option value="{{ $role->name }}" {{ old('roles') && in_array($role->name, old('roles')) ? 'selected' : '' }}>
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
            <button type="submit" class="bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-purple-500/30 transition-all active:scale-95 flex items-center">
                <i class="fas fa-save mr-2"></i> บันทึกข้อมูลบัญชี
            </button>
        </div>
    </form>
</div>
@endsection
