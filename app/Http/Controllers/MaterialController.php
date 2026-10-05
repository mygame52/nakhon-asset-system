<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Illuminate\Database\UniqueConstraintViolationException;

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
            'material_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('materials', 'material_code')->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'specs' => 'nullable|string|max:1000',
            'unit' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock_qty' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location_name' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ], [
            'material_code.unique' => 'รหัสพัสดุนี้ถูกใช้งานในระบบแล้ว โปรดระบุรหัสอื่น หรือเว้นว่างไว้เพื่อให้ระบบสร้างรหัสใหม่อัตโนมัติ',
            'name.required' => 'กรุณากรอกชื่อหรือชนิดวัสดุ',
            'unit.required' => 'กรุณาระบุหน่วยนับ',
            'stock_qty.required' => 'กรุณาระบุจำนวนยกมาเริ่มต้น',
        ]);

        if (empty($validated['location_name'])) {
            $validated['location_name'] = 'ห้องพัสดุ';
        }
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['max_stock'] = $validated['max_stock'] ?? 0;

        try {
            DB::transaction(function () use ($validated, &$material) {
                // Auto generate material code if empty (guaranteed collision-free)
                if (empty($validated['material_code'])) {
                    $category = !empty($validated['category_id']) ? Category::find($validated['category_id']) : null;
                    $prefix = ($category && $category->code_prefix) ? $category->code_prefix : '5510';
                    
                    $seq = 1;
                    do {
                        $candidate = $prefix . '-' . sprintf('%03d', $seq);
                        if (!Material::withTrashed()->where('material_code', $candidate)->exists()) {
                            $validated['material_code'] = $candidate;
                            break;
                        }
                        $seq++;
                    } while ($seq <= 99999);
                } else {
                    // Release code if held by a soft-deleted item
                    $trashed = Material::onlyTrashed()->where('material_code', $validated['material_code'])->first();
                    if ($trashed) {
                        $trashed->material_code = $trashed->material_code . '_del_' . $trashed->id;
                        $trashed->save();
                    }
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
        } catch (UniqueConstraintViolationException $e) {
            return back()->withInput()->withErrors([
                'material_code' => 'รหัสพัสดุ ' . ($validated['material_code'] ?? '') . ' ถูกใช้งานในระบบแล้ว โปรดระบุรหัสอื่น หรือเว้นว่างไว้เพื่อให้ระบบสร้างรหัสใหม่อัตโนมัติ'
            ]);
        }

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
            'material_code' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('materials', 'material_code')->ignore($material->id)->whereNull('deleted_at'),
            ],
            'name' => 'required|string|max:255',
            'specs' => 'nullable|string|max:1000',
            'unit' => 'required|string|max:100',
            'category_id' => 'nullable|exists:categories,id',
            'stock_qty' => 'required|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'location_name' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
        ], [
            'material_code.unique' => 'รหัสพัสดุนี้ถูกใช้งานในระบบแล้ว โปรดระบุรหัสอื่น',
            'name.required' => 'กรุณากรอกชื่อหรือชนิดวัสดุ',
            'unit.required' => 'กรุณาระบุหน่วยนับ',
            'stock_qty.required' => 'กรุณาระบุจำนวนคงเหลือ',
        ]);

        try {
            // Release code if held by another soft-deleted item
            if (!empty($validated['material_code'])) {
                $trashed = Material::onlyTrashed()->where('material_code', $validated['material_code'])->where('id', '!=', $material->id)->first();
                if ($trashed) {
                    $trashed->material_code = $trashed->material_code . '_del_' . $trashed->id;
                    $trashed->save();
                }
            }

            $material->update($validated);
        } catch (UniqueConstraintViolationException $e) {
            return back()->withInput()->withErrors([
                'material_code' => 'รหัสพัสดุนี้ถูกใช้งานในระบบแล้ว โปรดระบุรหัสอื่น'
            ]);
        }

        return redirect()->route('materials.index')->with('success', 'ปรับปรุงข้อมูลรายการวัสดุเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Material $material)
    {
        $code = $material->material_code;
        if (!empty($code)) {
            $material->material_code = $code . '_del_' . $material->id;
            $material->save();
        }
        $material->delete();
        return redirect()->route('materials.index')->with('success', 'ลบรายการวัสดุเรียบร้อยแล้ว');
    }
}
