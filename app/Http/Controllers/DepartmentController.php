<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Department::whereNotNull('code')
            ->where('code', '!=', '')
            ->withCount(['users', 'assets']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $totalDepartments = (clone $query)->count();
        $departments = $query->orderBy('code', 'asc')->paginate(12);

        return view('departments.index', compact('departments', 'totalDepartments'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        Department::create($validated);

        return redirect()->route('departments.index')->with('success', 'บันทึกข้อมูลกลุ่มงาน/หน่วยงานเรียบร้อยแล้ว');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:50',
            'description' => 'nullable|string|max:1000',
        ]);

        $department = Department::findOrFail($id);
        $department->update($validated);

        return redirect()->route('departments.index')->with('success', 'ปรับปรุงข้อมูลกลุ่มงาน/หน่วยงานเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $department = Department::findOrFail($id);
        
        if ($department->assets()->count() > 0 || $department->users()->count() > 0) {
            return redirect()->route('departments.index')->with('error', 'ไม่สามารถลบกลุ่มงานได้เนื่องจากมีบุคลากรหรือครุภัณฑ์ผูกอยู่อย่างน้อย 1 รายการ');
        }

        $department->delete();
        return redirect()->route('departments.index')->with('success', 'ลบกลุ่มงาน/หน่วยงานเรียบร้อยแล้ว');
    }
}
