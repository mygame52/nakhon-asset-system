<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - NAKHON ASSET SYSTEM</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Prompt:wght@500;600;700&family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/login.css') }}">
</head>
<body class="login-page">
    <div class="login-card">
        <div class="logo-container">
            <img src="{{ asset('images/logo.png') }}" alt="Logo">
        </div>
        <h2>สำนักงานส่งเสริมการเรียนรู้ประจำจังหวัดนครศรีธรรมราช</h2>
        <p class="subtitle">ระบบบริหารจัดการพัสดุและครุภัณฑ์</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>ชื่อผู้ใช้งาน (Username)</label>
                <input type="text" name="username" value="{{ old('username') }}" class="input-control" required autofocus placeholder="กรอกชื่อผู้ใช้...">
                @error('username') <div class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>รหัสผ่าน (Password)</label>
                <input type="password" name="password" class="input-control" required placeholder="กรอกรหัสผ่าน...">
                @error('password') <div class="error-text"><i class="fas fa-exclamation-circle mr-1"></i> {{ $message }}</div> @enderror
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt mr-2"></i> เข้าสู่ระบบ</button>
        </form>
        
        <div style="margin-top: 28px; font-size: 0.8rem; color: #94a3b8; line-height: 1.5;">
            &copy; 2026 สำนักงาน สกร.ประจำจังหวัดนครศรีฯ <br>
            <span style="font-size: 0.75rem; color: #cbd5e1;">พัฒนาโดย นายวิทวัส ธานีรัตน์</span>
        </div>
    </div>
</body>
</html>
