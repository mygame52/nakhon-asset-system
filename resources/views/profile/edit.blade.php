@extends('layouts.app')

@section('page_title', 'ข้อมูลส่วนตัว & ลายเซ็นดิจิทัล')
@section('page_description', 'จัดการข้อมูลส่วนตัว อัปเดตรหัสผ่าน และบันทึกภาพลายเซ็นอิเล็กทรอนิกส์')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    @if(session('success'))
    <div class="p-4 bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-xl flex items-center shadow-sm">
        <i class="fas fa-check-circle mr-3 text-lg text-emerald-500"></i>
        <span class="font-semibold text-sm">{{ session('success') }}</span>
    </div>
    @endif

    @if(session('error'))
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl flex items-center shadow-sm">
        <i class="fas fa-exclamation-circle mr-3 text-lg text-rose-500"></i>
        <span class="font-semibold text-sm">{{ session('error') }}</span>
    </div>
    @endif

    @if($errors->any())
    <div class="p-4 bg-rose-50 border border-rose-200 text-rose-800 rounded-xl shadow-sm space-y-1">
        <div class="font-bold text-sm flex items-center"><i class="fas fa-exclamation-triangle mr-2"></i> กรุณาตรวจสอบข้อมูล:</div>
        <ul class="list-disc pl-5 text-xs">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <!-- Left Side: Profile & Signature Info Card -->
        <div class="md:col-span-1 space-y-6">
            <div class="bg-gradient-to-br from-[#2a0845] to-[#12001c] rounded-2xl shadow-xl border border-purple-950 p-6 text-center text-white relative overflow-hidden">
                <div class="absolute -right-10 -top-10 w-32 h-32 bg-purple-500 opacity-20 blur-2xl rounded-full"></div>
                <div class="relative z-10">
                    <div class="w-20 h-20 rounded-full bg-white/10 border-2 border-amber-400 mx-auto flex items-center justify-center text-3xl font-bold text-amber-400 shadow-lg">
                        {{ mb_substr($user->name, 0, 1) }}
                    </div>
                    <h3 class="mt-4 font-bold text-lg text-white">{{ $user->name }}</h3>
                    <p class="text-xs text-white/50 font-medium">ชื่อผู้ใช้งาน: {{ $user->username }}</p>
                    
                    <div class="mt-2 inline-flex items-center px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider bg-white/10 text-amber-300 border border-white/5">
                        @forelse($user->roles as $r)
                            {{ $r->name === 'admin' ? '⚙️ หัวหน้าเจ้าหน้าที่' : ($r->name === 'procurement' ? '📦 เจ้าหน้าที่จ่าย' : '👤 ผู้ขอเบิก') }}
                        @empty
                            👤 General User
                        @endforelse
                    </div>

                    @if($user->department)
                    <div class="mt-3 text-xs text-white/70">
                        <i class="fas fa-sitemap mr-1 text-white/40"></i> {{ $user->department->name }}
                    </div>
                    @endif
                </div>
            </div>

            <!-- Signature Preview Box -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 flex flex-col items-center">
                <h4 class="font-bold text-gray-800 text-sm mb-4 self-start flex items-center">
                    <i class="fas fa-signature text-purple-600 mr-2"></i> ลายเซ็นปัจจุบันของคุณ
                </h4>

                @if($user->signature)
                    <div class="relative w-full aspect-[4/2] rounded-xl overflow-hidden border border-gray-200 shadow-inner flex items-center justify-center p-4 bg-checkered">
                        <img src="{{ asset('storage/' . $user->signature) }}" alt="ลายเซ็น" class="max-h-full max-w-full object-contain relative z-10 transition-transform duration-300 hover:scale-105">
                    </div>
                    <div class="mt-3 text-center">
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-600 border border-emerald-100">
                            <i class="fas fa-shield-alt mr-1"></i> ลบพื้นหลังอัตโนมัติแล้ว
                        </span>
                        <p class="text-[11px] text-gray-400 mt-1">ไฟล์โปร่งใสพร้อมใช้งานในเอกสาร</p>
                    </div>
                @else
                    <div class="w-full aspect-[4/2] rounded-xl border-2 border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-400 bg-gray-50/50">
                        <i class="fas fa-file-signature text-3xl mb-2 text-gray-300"></i>
                        <span class="text-xs font-semibold">ยังไม่มีลายเซ็นในระบบ</span>
                    </div>
                @endif
            </div>
        </div>

        <!-- Right Side: Edit Form -->
        <div class="md:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h3 class="text-lg font-bold text-gray-800 flex items-center">
                    <i class="fas fa-user-edit text-purple-600 mr-2"></i> แก้ไขข้อมูลโปรไฟล์
                </h3>
            </div>

            <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ชื่อ-นามสกุล <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">อีเมลติดต่อ <span class="text-red-500">*</span></label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}" required class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h4 class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-4 flex items-center">
                        <i class="fas fa-key mr-2"></i> เปลี่ยนรหัสผ่านใหม่ (ปล่อยว่างหากไม่ต้องการเปลี่ยน)
                    </h4>
                    
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">รหัสผ่านใหม่</label>
                            <input type="password" name="password" placeholder="อย่างน้อย 8 ตัวอักษร" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-xs font-bold text-gray-700 uppercase tracking-wider mb-2">ยืนยันรหัสผ่านใหม่</label>
                            <input type="password" name="password_confirmation" placeholder="กรอกรหัสผ่านใหม่อีกครั้ง" class="w-full px-4 py-2.5 bg-gray-50 border border-gray-200 rounded-xl focus:ring-2 focus:ring-purple-500 focus:bg-white text-sm font-semibold outline-none transition-all">
                        </div>
                    </div>
                </div>

                <div class="border-t border-gray-100 pt-6">
                    <h4 class="text-xs font-bold text-purple-700 uppercase tracking-wider mb-4 flex items-center">
                        <i class="fas fa-camera mr-2"></i> อัปโหลดลายเซ็นใหม่
                    </h4>
                    
                    <div class="p-4 bg-purple-50/50 border border-purple-100 rounded-xl mb-4 text-xs text-purple-800 space-y-1.5 leading-relaxed">
                        <p class="font-bold"><i class="fas fa-info-circle mr-1"></i> คำแนะนำเพื่อความคมชัดสูงสุด:</p>
                        <ul class="list-disc pl-5 space-y-1">
                            <li>ใช้ปากกาลูกลื่นหรือปากกาเคมี (หมึกสีดำหรือสีน้ำเงินเข้ม) เขียนลายเซ็นลงบน **กระดาษขาวเรียบเปล่า**</li>
                            <li>ถ่ายภาพในที่ที่มี **แสงสว่างเพียงพอ** เพื่อไม่ให้มีเงาบังลายเซ็น</li>
                            <li>ถ่ายภาพให้ขนานกับกระดาษ และตัดครอปภาพให้เหลือเฉพาะส่วนของลายเซ็น</li>
                            <li class="font-bold text-indigo-700">ระบบจะทำการประมวลผลลบพื้นหลังขาว/เทาออกอัตโนมัติให้กลายเป็นภาพโปร่งใสทันทีเมื่อบันทึก</li>
                        </ul>
                    </div>

                    <input type="file" name="signature" accept="image/*" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-purple-700 hover:file:bg-purple-100 transition-colors border border-gray-200 rounded-xl p-2 cursor-pointer">
                </div>

                <div class="border-t border-gray-100 pt-6 flex justify-end">
                    <button type="submit" class="w-full sm:w-auto bg-gradient-to-r from-purple-600 to-indigo-600 hover:from-purple-700 hover:to-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-purple-500/20 transition-all active:scale-95 text-sm flex items-center justify-center">
                        <i class="fas fa-save mr-2"></i> บันทึกข้อมูลและลายเซ็น
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
