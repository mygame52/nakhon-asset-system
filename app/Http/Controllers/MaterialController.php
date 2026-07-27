<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class MaterialController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $materials = Material::with('category')
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('material_code', 'like', "%{$search}%")
                      ->orWhere('location_name', 'like', "%{$search}%")
                      ->orWhereHas('category', function($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->latest()
            ->paginate(12);

        return view('materials.index', compact('materials'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('type', 'material')->orderBy('code_prefix', 'asc')->get();
        return view('materials.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'material_code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'specs' => 'nullable|string|max:1000',
            'unit' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock_qty' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location_name' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['location_name'])) {
            $validated['location_name'] = 'ห้องพัสดุ';
        }
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['max_stock'] = $validated['max_stock'] ?? 0;

        DB::transaction(function () use ($validated, &$material) {
            // Auto generate material code if empty
            if (empty($validated['material_code'])) {
                $category = !empty($validated['category_id']) ? Category::find($validated['category_id']) : null;
                $prefix = ($category && $category->code_prefix) ? $category->code_prefix : '5510';
                $nextSeq = Material::where('category_id', $validated['category_id'])->count() + 1;
                $validated['material_code'] = $prefix . '-' . sprintf('%03d', $nextSeq);
            }

            $material = Material::create($validated);

            // Record Initial Balance Transaction if stock_qty > 0
            if ($material->stock_qty > 0) {
                Transaction::create([
                    'transaction_type' => 'in',
                    'item_type' => 'material',
                    'item_id' => $material->id,
                    'quantity' => $material->stock_qty,
                    'unit_price' => $material->unit_price,
                    'user_id' => Auth::id(),
                    'party_name' => 'ยอดยกมาเริ่มต้น',
                    'reference_doc' => 'ยอดยกมา',
                    'note' => 'ลงทะเบียนยอดยกมาเริ่มต้นคุมบัญชีวัสดุ',
                    'transaction_date' => now()->toDateString(),
                    'status' => 'completed',
                ]);
            }
        });

        return redirect()->route('materials.index')->with('success', 'บันทึกเปิดบัญชีคุมรายการวัสดุใหม่เรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Material $material)
    {
        return redirect()->route('stock-cards.show', $material->id);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Material $material)
    {
        $categories = Category::where('type', 'material')->orderBy('code_prefix', 'asc')->get();
        return view('materials.edit', compact('material', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Material $material)
    {
        $validated = $request->validate([
            'material_code' => 'nullable|string|max:100',
            'name' => 'required|string|max:255',
            'specs' => 'nullable|string|max:1000',
            'unit' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock_qty' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location_name' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ]);

        $material->update($validated);

        return redirect()->route('materials.index')->with('success', 'ปรับปรุงข้อมูลรายการวัสดุเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'ลบรายการวัสดุเรียบร้อยแล้ว');
    }
}
