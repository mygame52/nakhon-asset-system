<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with(['department', 'roles'])->latest()->paginate(15);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        $departments = Department::all();
        $roles = Role::all();
        return view('users.create', compact('departments', 'roles'));
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', Password::defaults()],
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name']
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'username' => $validatedData['username'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'department_id' => $validatedData['department_id'] ?? null,
        ]);

        if (isset($validatedData['roles'])) {
            $user->assignRole($validatedData['roles']);
        } else {
            $user->assignRole('user');
        }

        return redirect()->route('users.index')->with('success', 'สร้างบัญชีผู้ใช้งานใหม่สำเร็จ');
    }

    public function edit(User $user)
    {
        $departments = Department::all();
        $roles = Role::all();
        $userRoles = $user->roles->pluck('name')->toArray();
        return view('users.edit', compact('user', 'departments', 'roles', 'userRoles'));
    }

    public function update(Request $request, User $user)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:users,username,'.$user->id],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', Password::defaults()],
            'department_id' => ['nullable', 'exists:departments,id'],
            'roles' => ['nullable', 'array'],
            'roles.*' => ['exists:roles,name']
        ]);

        $user->name = $validatedData['name'];
        $user->username = $validatedData['username'];
        $user->email = $validatedData['email'];
        $user->department_id = $validatedData['department_id'] ?? null;
        
        if (!empty($validatedData['password'])) {
            $user->password = Hash::make($validatedData['password']);
        }

        $user->save();

        if (isset($validatedData['roles'])) {
            $user->syncRoles($validatedData['roles']);
        } else {
            $user->syncRoles([]);
        }

        return redirect()->route('users.index')->with('success', 'อัปเดตข้อมูลผู้ใช้งานเรียบร้อยแล้ว');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->with('error', 'คุณไม่สามารถลบบัญชีที่คุณกำลังล็อกอินเพื่อใช้งานอยู่ได้');
        }
        
        $user->delete();
        return redirect()->route('users.index')->with('success', 'ทำการลบบัญชีผู้ใช้งานออกจากระบบสำเร็จ');
    }
}
