<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ระบบบริหารจัดการพัสดุและครุภัณฑ์ - สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช</title>
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary-color: #4a148c; /* Deep Purple */
            --secondary-color: #7b1fa2;
            --accent-color: #ffd700; /* Gold */
            --bg-color: #f4f7f6;
            --sidebar-bg: #2c003e;
            --text-color: #333;
            --glass-bg: rgba(255, 255, 255, 0.8);
            --shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.15);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Sarabun', sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar Styles */
        .sidebar {
            width: 260px;
            background: linear-gradient(180deg, var(--sidebar-bg), #1a0026);
            color: white;
            padding: 20px;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            transition: 0.3s;
            z-index: 1000;
        }

        .sidebar-header {
            padding: 20px 0;
            text-align: center;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 20px;
        }

        .sidebar-header h2 {
            font-size: 1.2rem;
            color: var(--accent-color);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: rgba(255, 255, 255, 0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: 0.3s;
        }

        .nav-link i {
            margin-right: 15px;
            width: 20px;
            text-align: center;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: var(--accent-color);
            transform: translateX(5px);
        }

        /* Main Content Styles */
        .main-content {
            flex-grow: 1;
            margin-left: 260px;
            padding: 30px;
            width: calc(100% - 260px);
        }

        header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            background: var(--glass-bg);
            backdrop-filter: blur(4px);
            padding: 20px 30px;
            border-radius: 15px;
            box-shadow: var(--shadow);
            border: 1px solid rgba(255, 255, 255, 0.18);
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: var(--primary-color);
            color: white;
            border-radius: 50%;
            display: flex;
            justify-content: center;
            align-items: center;
            font-weight: bold;
        }

        /* Card Styles */
        .card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: var(--shadow);
            border: none;
            transition: 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .btn {
            padding: 10px 20px;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            font-weight: 500;
            transition: 0.3s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
            box-shadow: 0 4px 12px rgba(74, 20, 140, 0.3);
        }

        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in {
            animation: fadeIn 0.5s ease-out forwards;
        }
    </style>
    @yield('styles')
</head>
<body>
    <div class="sidebar">
        <div class="sidebar-header">
            <i class="fas fa-landmark fa-2x" style="color: var(--accent-color); margin-bottom: 10px;"></i>
            <h2>Nakhon Asset</h2>
            <p style="font-size: 0.7rem; opacity: 0.6;">สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช</p>
        </div>
        <ul class="nav-links">
            <li class="nav-item">
                <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i> แดชบอร์ด
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('assets.index') }}" class="nav-link {{ request()->routeIs('assets.*') ? 'active' : '' }}">
                    <i class="fas fa-box"></i> ทะเบียนครุภัณฑ์ (พด. 1)
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('materials.index') }}" class="nav-link {{ request()->routeIs('materials.*') ? 'active' : '' }}">
                    <i class="fas fa-file-invoice"></i> บัญชีวัสดุ
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                    <i class="fas fa-tags"></i> ประเภทพัสดุ
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('locations.index') }}" class="nav-link {{ request()->routeIs('locations.*') ? 'active' : '' }}">
                    <i class="fas fa-map-marker-alt"></i> สถานที่จัดเก็บ
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('departments.index') }}" class="nav-link {{ request()->routeIs('departments.*') ? 'active' : '' }}">
                    <i class="fas fa-users"></i> กลุ่มงาน/หน่วยงาน
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('vendors.index') }}" class="nav-link {{ request()->routeIs('vendors.*') ? 'active' : '' }}">
                    <i class="fas fa-store"></i> ร้านค้า/ผู้จัดจำหน่าย
                </a>
            </li>
        </ul>
        <div style="margin-top: auto; padding-top: 20px;">
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="nav-link" style="width: 100%; background: none; border: none; color: rgba(255, 255, 255, 0.7); cursor: pointer; text-align: left;">
                    <i class="fas fa-sign-out-alt"></i> ออกจากระบบ
                </button>
            </form>
        </div>
        <div style="margin-top: 20px; padding: 15px; text-align: center; font-size: 0.7rem; color: rgba(255,255,255,0.3); border-top: 1px solid rgba(255,255,255,0.05);">
            &copy; 2026 สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช <br>
            พัฒนาโดย นายวิทวัส ธานีรัตน์
        </div>
    </div>

    <div class="main-content">
        <header>
            <div class="page-title">
                <h1 style="font-size: 1.5rem; font-weight: 600;">@yield('page_title', 'ยินดีต้อนรับ')</h1>
                <p style="font-size: 0.85rem; color: #666;">@yield('page_description', 'ระบบบริหารจัดการพัสดุและครุภัณฑ์')</p>
            </div>
            <div class="user-profile">
                <div style="text-align: right;">
                    <p style="font-weight: 600; font-size: 0.9rem;">เขมชาติ</p>
                    <p style="font-size: 0.75rem; color: #666;">ผู้จัดการระบบ (Admin)</p>
                </div>
                <div class="user-avatar">ข</div>
            </div>
        </header>

        <main class="animate-fade-in">
            @yield('content')
        </main>
    </div>

    @yield('scripts')
</body>
</html>
