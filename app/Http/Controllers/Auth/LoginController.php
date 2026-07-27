<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => ['required', 'string'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            $user = Auth::user();
            if ($user && $user->roles->isEmpty()) {
                $user->assignRole('user');
            }
            \App\Models\ActivityLog::log('login', 'auth', "ผู้ใช้งาน {$user->name} ({$user->username}) เข้าสู่ระบบสำเร็จ", $user);
            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'username' => 'ชื่อผู้ใช้หรือรหัสผ่านไม่ถูกต้อง',
        ])->onlyInput('username');
    }

    public function logout(Request $request)
    {
        $user = Auth::user();
        if ($user) {
            \App\Models\ActivityLog::log('logout', 'auth', "ผู้ใช้งาน {$user->name} ({$user->username}) ออกจากระบบ", $user);
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
