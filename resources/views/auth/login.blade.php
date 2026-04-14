<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>เข้าสู่ระบบ - ระบบบริหารจัดการพัสดุ สกร. นครศรีฯ</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Sarabun:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4a148c;
            --accent-color: #ffd700;
        }
        body {
            font-family: 'Sarabun', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            height: 100vh;
            margin: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.1);
            width: 100%;
            max-width: 400px;
            text-align: center;
        }
        .logo {
            width: 80px;
            height: 80px;
            background: var(--primary-color);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: white;
            font-size: 2rem;
            box-shadow: 0 5px 15px rgba(74, 20, 140, 0.3);
        }
        h2 { color: var(--primary-color); margin-bottom: 5px; }
        p { color: #666; font-size: 0.9rem; margin-bottom: 30px; }
        .form-group { text-align: left; margin-bottom: 20px; }
        label { display: block; font-size: 0.85rem; margin-bottom: 8px; color: #555; }
        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 10px;
            outline: none;
            box-sizing: border-box;
            transition: 0.3s;
        }
        input:focus { border-color: var(--primary-color); box-shadow: 0 0 0 3px rgba(74, 20, 140, 0.1); }
        .btn-login {
            background: var(--primary-color);
            color: white;
            border: none;
            width: 100%;
            padding: 12px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: 0.3s;
            margin-top: 10px;
        }
        .btn-login:hover { background: #310d5e; transform: translateY(-2px); }
        .error { color: #f44336; font-size: 0.8rem; margin-top: 5px; }
    </style>
</head>
<body>
    <div class="login-card">
        <div class="logo">
            <i class="fas fa-landmark"></i>
        </div>
        <h2>สกร. นครศรีธรรมราช</h2>
        <p>ระบบบริหารจัดการพัสดุและครุภัณฑ์</p>

        <form action="{{ route('login') }}" method="POST">
            @csrf
            <div class="form-group">
                <label>ชื่อผู้ใช้งาน</label>
                <input type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="กรอกชื่อผู้ใช้">
                @error('username') <div class="error">{{ $message }}</div> @enderror
            </div>
            <div class="form-group">
                <label>รหัสผ่าน</label>
                <input type="password" name="password" required placeholder="กรอกรหัสผ่าน">
            </div>
            <button type="submit" class="btn-login">เข้าสู่ระบบ</button>
        </form>
        
        <div style="margin-top: 30px; font-size: 0.8rem; color: #999;">
            &copy; 2026 สำนักงาน สกร. ประจำจังหวัดนครศรีธรรมราช <br>
            พัฒนาโดย นายวิทวัส ธานีรัตน์
        </div>
    </div>
</body>
</html>
