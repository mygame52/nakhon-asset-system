<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการพัสดุและครุภัณฑ์ - NAKHON ASSET</title>
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@400;500;600;700&family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.13.8/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="{{ asset('css/theme.css') }}">
    @yield('styles')
</head>
<body class="bg-gray-50 text-gray-800 flex min-h-screen antialiased selection:bg-purple-200">

    <!-- Mobile Overlay handled by Alpine -->
    <div x-data="{ sidebarOpen: false }" class="flex w-full h-full relative">
        
        <!-- Sidebar -->
        <aside class="fixed inset-y-0 left-0 bg-gradient-to-b from-[#2a0845] to-[#12001c] text-white w-64 shadow-2xl z-50 flex flex-col transition-transform duration-300 md:relative md:translate-x-0"
               :class="{'translate-x-0': sidebarOpen, '-translate-x-full': !sidebarOpen}" x-cloak>
            
            <div class="p-6 text-center border-b border-white/10 relative overflow-hidden">
                <div class="absolute inset-0 bg-white/5 transform -skew-y-12 scale-150 origin-top-left"></div>
                <div class="relative z-10 flex justify-center mb-4">
                    <img src="{{ asset('images/logo.png') }}" class="w-24 h-24 drop-shadow-xl hover:scale-105 transition-transform" alt="Logo">
                </div>
                <h2 class="text-xl font-bold uppercase tracking-widest text-transparent bg-clip-text bg-gradient-to-r from-amber-200 to-amber-500 relative z-10">Nakhon Asset</h2>
                <p class="text-[11px] text-white/60 mt-1 relative z-10 font-medium">สำนักงาน สกร.ประจำจังหวัดนครศรีฯ</p>
            </div>
            
            <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto custom-scrollbar">
                <a href="{{ route('dashboard') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('dashboard') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-th-large w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">แดชบอร์ด</span>
                </a>
                <a href="{{ route('assets.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('assets.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-box w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">ทะเบียนครุภัณฑ์ (พด.1)</span>
                </a>
                <a href="{{ route('materials.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('materials.*') || request()->routeIs('requisitions.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-boxes w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">วัสดุคงคลัง & ขอเบิก</span>
                </a>

                @hasanyrole('admin|procurement')
                <a href="{{ route('stock-cards.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('stock-cards.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-book w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">สมุดบัญชีวัสดุ</span>
                </a>
                @endhasanyrole

                @hasanyrole('admin|procurement')
                <div class="pt-2 mt-2 border-t border-white/10"></div>
                <h3 class="px-4 text-[10px] font-bold text-white/30 uppercase tracking-widest mb-1 mt-2">จัดการข้อมูลหลัก</h3>
                <a href="{{ route('categories.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('categories.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-tags w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">ประเภทพัสดุ</span>
                </a>
                <a href="{{ route('locations.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('locations.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-map-marker-alt w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">สถานที่จัดเก็บ</span>
                </a>
                <a href="{{ route('departments.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('departments.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-users w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">กลุ่ม/หน่วยงาน</span>
                </a>
                <a href="{{ route('vendors.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('vendors.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-store w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">ร้านค้า</span>
                </a>
                @endhasanyrole

                @role('admin')
                <div class="pt-2 mt-2 border-t border-white/10"></div>
                <h3 class="px-4 text-[10px] font-bold text-white/30 uppercase tracking-widest mb-1 mt-2">สำหรับผู้ดูแลระบบ</h3>
                <a href="{{ route('users.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('users.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-users-cog w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">จัดการผู้ใช้งาน</span>
                </a>
                <a href="{{ route('activity-logs.index') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('activity-logs.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-history w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">ประวัติการใช้งานระบบ</span>
                </a>
                @endrole

                <div class="pt-2 mt-2 border-t border-white/10"></div>
                <a href="{{ route('profile.edit') }}" class="flex items-center px-4 py-3.5 rounded-xl transition-all duration-300 group {{ request()->routeIs('profile.*') ? 'bg-white/15 text-amber-400 shadow-[0_4px_12px_rgba(0,0,0,0.3)] border border-white/10' : 'text-white/60 hover:bg-white/5 hover:text-white' }}">
                    <i class="fas fa-user-cog w-7 text-center group-hover:scale-110 transition-transform"></i>
                    <span class="font-medium text-sm">โปรไฟล์ & ลายเซ็น</span>
                </a>
            </nav>

            <div class="px-4 py-4 border-t border-white/10">
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="w-full flex items-center px-4 py-3.5 text-white/60 hover:bg-gradient-to-r hover:from-red-500/20 hover:to-transparent hover:text-red-400 hover:border-l-4 hover:border-red-500 rounded-lg transition-all group font-medium text-sm">
                        <i class="fas fa-sign-out-alt w-7 text-center group-hover:-translate-x-1 transition-transform"></i>
                        <span>ออกจากระบบ</span>
                    </button>
                </form>
            </div>
            
            <div class="p-4 text-center text-[10px] text-white/20">
                v1.1.0 NFE 2026
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 flex flex-col min-w-0 transition-all duration-300 relative h-screen overflow-y-auto bg-[#F8F9FA]">
            
            <!-- Desktop Header -->
            <header class="sticky top-0 z-40 bg-white/80 backdrop-blur-xl border-b border-gray-100/80 shadow-sm px-6 py-4 flex justify-between items-center hidden md:flex">
                <div class="animate-fade-in-up">
                    <h1 class="text-2xl font-bold bg-clip-text text-transparent bg-gradient-to-br from-indigo-900 to-purple-600 drop-shadow-sm">
                        @yield('page_title', 'Dashboard')
                    </h1>
                    <p class="text-[13px] text-gray-500 mt-1 font-medium tracking-wide">@yield('page_description', 'จัดการพัสดุและครุภัณฑ์')</p>
                </div>
                
                <div class="flex items-center space-x-4">
                    @php
                        $unreadNotifications = \App\Models\Notification::where('user_id', auth()->id())
                            ->where('is_read', false)
                            ->latest()
                            ->take(5)
                            ->get();
                        $unreadCount = \App\Models\Notification::where('user_id', auth()->id())
                            ->where('is_read', false)
                            ->count();
                    @endphp

                    <!-- Notification Dropdown -->
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="w-10 h-10 rounded-full bg-gray-50 text-gray-500 hover:bg-purple-50 hover:text-purple-600 transition-colors flex items-center justify-center relative focus:outline-none">
                            <i class="fas fa-bell"></i>
                            @if($unreadCount > 0)
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full animate-pulse shadow-sm">
                                    {{ $unreadCount > 99 ? '99+' : $unreadCount }}
                                </span>
                            @endif
                        </button>

                        <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 mt-3 w-80 sm:w-96 bg-white rounded-2xl shadow-xl border border-gray-100 py-3 z-50" style="display: none;">
                            <div class="px-4 pb-3 border-b border-gray-100 flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-bell text-purple-600"></i>
                                    <h3 class="font-bold text-gray-800 text-sm">การแจ้งเตือน</h3>
                                    @if($unreadCount > 0)
                                        <span class="bg-purple-100 text-purple-700 text-xs px-2 py-0.5 rounded-full font-semibold">{{ $unreadCount }} ใหม่</span>
                                    @endif
                                </div>
                                @if($unreadCount > 0)
                                    <form action="{{ route('notifications.read-all') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs text-purple-600 hover:text-purple-800 font-medium transition-colors">
                                            อ่านแล้วทั้งหมด
                                        </button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-80 overflow-y-auto divide-y divide-gray-50">
                                @forelse($unreadNotifications as $notif)
                                    <a href="{{ route('notifications.read', $notif->id) }}" class="block px-4 py-3 hover:bg-purple-50/50 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-8 h-8 rounded-full bg-purple-100 text-purple-600 flex items-center justify-center flex-shrink-0 mt-0.5">
                                                <i class="fas fa-info-circle text-xs"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-bold text-gray-800 leading-snug">{{ $notif->title }}</p>
                                                <p class="text-xs text-gray-600 mt-0.5 line-clamp-2 leading-relaxed">{{ $notif->message }}</p>
                                                <span class="text-[10px] text-gray-400 mt-1 block">{{ $notif->created_at->diffForHumans() }}</span>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="p-6 text-center text-gray-400">
                                        <i class="far fa-bell-slash text-2xl mb-2 block text-gray-300"></i>
                                        <p class="text-xs">ไม่มีการแจ้งเตือนใหม่</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                    <div class="h-8 w-[1px] bg-gray-200"></div>
                    <div class="flex items-center gap-3">
                        <div class="text-right">
                            <p class="font-bold text-gray-800 text-sm leading-tight">{{ auth()->user()->name ?? 'เจ้าหน้าที่' }}</p>
                            <p class="text-[11px] text-purple-700 font-semibold bg-purple-50/80 border border-purple-100 px-2 py-0.5 rounded-md inline-block mt-1">{{ auth()->user()->department->name ?? 'ระบบงานพัสดุ' }}</p>
                        </div>
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white font-bold text-lg shadow-md shadow-purple-500/20">
                            {{ mb_substr(auth()->user()->name ?? 'ผ', 0, 1) }}
                        </div>
                    </div>
                </div>
            </header>

            <!-- Mobile Header -->
            <header class="md:hidden sticky top-0 z-40 bg-white/90 backdrop-blur-md shadow-sm border-b border-purple-100 px-4 py-3 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="text-purple-900 focus:outline-none w-10 h-10 rounded-xl bg-purple-50 flex items-center justify-center border border-purple-100">
                        <i class="fas fa-bars text-lg"></i>
                    </button>
                    <h1 class="text-lg font-bold bg-clip-text text-transparent bg-gradient-to-r from-purple-800 to-indigo-600 truncate">@yield('page_title', '')</h1>
                </div>
                <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-purple-600 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                    {{ mb_substr(auth()->user()->name ?? 'ผ', 0, 1) }}
                </div>
            </header>

            <!-- Mobile Sidebar Backdrop -->
            <div x-show="sidebarOpen" @click="sidebarOpen = false" x-transition.opacity 
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm z-40 md:hidden" x-cloak></div>

            <!-- Page Content -->
            <main class="p-4 md:p-8 flex-1 w-full pb-[100px] md:pb-8">
                @if(session('success'))
                    <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 flex items-center shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                        <i class="fas fa-check-circle text-xl mr-3 text-emerald-500"></i>
                        <span class="flex-1 font-medium text-sm">{{ session('success') }}</span>
                        <button @click="show = false" class="text-emerald-500 hover:text-emerald-700 ml-4"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-center shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                        <i class="fas fa-exclamation-triangle text-xl mr-3 text-rose-500"></i>
                        <span class="flex-1 font-medium text-sm">{{ session('error') }}</span>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700 ml-4"><i class="fas fa-times"></i></button>
                    </div>
                @endif
                @if($errors->any())
                    <div class="mb-6 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 flex items-start shadow-sm" x-data="{ show: true }" x-show="show" x-transition>
                        <i class="fas fa-exclamation-circle text-xl mr-3 text-rose-500 mt-0.5"></i>
                        <div class="flex-1 font-medium text-sm">
                            <ul class="list-disc pl-5 space-y-1 text-sm">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                        <button @click="show = false" class="text-rose-500 hover:text-rose-700 ml-4"><i class="fas fa-times"></i></button>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    @yield('scripts')
</body>
</html>
