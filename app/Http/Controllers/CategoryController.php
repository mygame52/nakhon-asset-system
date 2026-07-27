<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $type = $request->input('type');
        $search = $request->input('search');

        $query = Category::query();

        if ($type) {
            $query->where('type', $type);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code_prefix', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $categories = $query->orderBy('type', 'asc')->orderBy('code_prefix', 'asc')->get();

        $assetCount = Category::where('type', 'asset')->count();
        $materialCount = Category::where('type', 'material')->count();

        return view('categories.index', compact('categories', 'assetCount', 'materialCount'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,material',
            'code_prefix' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
        ]);

        Category::create($validated);

        return redirect()->route('categories.index')->with('success', 'บันทึกประเภทพัสดุมาตรฐานเรียบร้อยแล้ว');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:asset,material',
            'code_prefix' => 'nullable|string|max:20',
            'description' => 'nullable|string|max:1000',
        ]);

        $category->update($validated);

        return redirect()->route('categories.index')->with('success', 'แก้ไขข้อมูลประเภทพัสดุเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Category $category)
    {
        if ($category->assets()->exists() || $category->materials()->exists()) {
            return redirect()->route('categories.index')->with('error', 'ไม่สามารถลบได้ เนื่องจากมีข้อมูลครุภัณฑ์หรือวัสดุผูกติดกับประเภทนี้อยู่');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'ลบประเภทพัสดุเรียบร้อยแล้ว');
    }
}
