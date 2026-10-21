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
use Carbon\Carbon;

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
            'stock_qty' => 'nullable|integer|min:0',
            'receipt_qty' => 'nullable|integer|min:1',
            'initial_stock_type' => 'nullable|in:opening_balance,first_receipt,none',
            'receipt_date' => 'nullable|date',
            'receipt_party_name' => 'nullable|string|max:255',
            'receipt_ref_doc' => 'nullable|string|max:255',
            'unit_price' => 'nullable|numeric|min:0',
            'location_name' => 'nullable|string|max:255',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'opening_balance_date' => 'nullable|date',
        ], [
            'material_code.unique' => 'รหัสพัสดุนี้ถูกใช้งานในระบบแล้ว โปรดระบุรหัสอื่น หรือเว้นว่างไว้เพื่อให้ระบบสร้างรหัสใหม่อัตโนมัติ',
            'name.required' => 'กรุณากรอกชื่อหรือชนิดวัสดุ',
            'unit.required' => 'กรุณาระบุหน่วยนับ',
        ]);

        if (empty($validated['location_name'])) {
            $validated['location_name'] = 'ห้องพัสดุ';
        }
        $validated['min_stock'] = $validated['min_stock'] ?? 0;
        $validated['max_stock'] = $validated['max_stock'] ?? 0;

        $stockType = $request->input('initial_stock_type', 'opening_balance');
        if ($stockType === 'first_receipt') {
            $validated['stock_qty'] = intval($request->input('receipt_qty', 1));
        } elseif ($stockType === 'none') {
            $validated['stock_qty'] = 0;
        } else {
            $validated['stock_qty'] = intval($request->input('stock_qty', 0));
        }

        try {
            DB::transaction(function () use ($validated, $stockType, $request, &$material) {
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

                // Record Initial Transaction if stock_qty > 0
                if ($material->stock_qty > 0) {
                    if ($stockType === 'first_receipt') {
                        $receiptDate = $request->input('receipt_date', now()->toDateString());
                        $partyName = trim($request->input('receipt_party_name', ''));
                        $refDoc = trim($request->input('receipt_ref_doc', ''));

                        Transaction::create([
                            'transaction_type' => 'in',
                            'item_type' => 'material',
                            'item_id' => $material->id,
                            'quantity' => $material->stock_qty,
                            'unit_price' => $material->unit_price,
                            'user_id' => Auth::id(),
                            'party_name' => !empty($partyName) ? $partyName : 'รับเข้าพัสดุครั้งแรก',
                            'reference_doc' => !empty($refDoc) ? $refDoc : 'รับเข้าสต็อก',
                            'note' => 'รับเข้าพัสดุครั้งแรก',
                            'transaction_date' => $receiptDate,
                            'status' => 'completed',
                        ]);
                    } else {
                        // Opening Balance (rolls over to 1 Oct of selected FY)
                        $openingDate = $validated['opening_balance_date'] ?? null;
                        if (empty($openingDate)) {
                            $now = now();
                            $currentFyAD = $now->month >= 10 ? $now->year + 1 : $now->year;
                            $openingDate = Carbon::createFromDate($currentFyAD - 1, 9, 30)->toDateString();
                        } else {
                            $parsed = Carbon::parse($openingDate);
                            if ($parsed->month == 10 && $parsed->day == 1) {
                                $openingDate = $parsed->copy()->subDay()->toDateString();
                            } else {
                                $openingDate = $parsed->toDateString();
                            }
                        }

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
                            'transaction_date' => $openingDate,
                            'status' => 'completed',
                        ]);
                    }
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
        $inTransactions = Transaction::where('item_type', 'material')
            ->where('item_id', $material->id)
            ->where('transaction_type', 'in')
            ->orderBy('transaction_date', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // เลือกรายการรับเข้าจริง (ที่ไม่ใช่ยอดยกมาเริ่มต้น) เป็นค่าเริ่มต้นในการแก้ไข เพื่อป้องกันการไปดึงยอดยกมาที่ซ่อนอยู่มาสร้างซ้ำ
        $initialTx = $inTransactions->first(function ($tx) {
            return $tx->reference_doc !== 'ยอดยกมา' && $tx->party_name !== 'ยอดยกมาเริ่มต้น';
        }) ?? $inTransactions->first();

        return view('materials.edit', compact('material', 'categories', 'initialTx', 'inTransactions'));
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
            'transaction_date' => 'nullable|date',
            'initial_stock_type' => 'nullable|string|in:opening_balance,first_receipt',
            'receipt_party_name' => 'nullable|string|max:255',
            'receipt_ref_doc' => 'nullable|string|max:255',
            'target_transaction_id' => 'nullable|integer',
            'replace_all_in_transactions' => 'nullable|boolean',
        ], [
            'material_code.unique' => 'รหัสพัสดุนี้ถูกใช้งานในระบบแล้ว โปรดระบุรหัสอื่น',
            'name.required' => 'กรุณากรอกชื่อหรือชนิดวัสดุ',
            'unit.required' => 'กรุณาระบุหน่วยนับ',
            'stock_qty.required' => 'กรุณาระบุจำนวนคงเหลือ',
        ]);

        try {
            DB::transaction(function () use ($material, $validated, $request) {
                // Release code if held by another soft-deleted item
                if (!empty($validated['material_code'])) {
                    $trashed = Material::onlyTrashed()->where('material_code', $validated['material_code'])->where('id', '!=', $material->id)->first();
                    if ($trashed) {
                        $trashed->material_code = $trashed->material_code . '_del_' . $trashed->id;
                        $trashed->save();
                    }
                }

                $materialData = collect($validated)->except([
                    'transaction_date', 'initial_stock_type', 'receipt_party_name', 'receipt_ref_doc',
                    'target_transaction_id', 'replace_all_in_transactions'
                ])->all();

                $material->update($materialData);

                // Update or Replace Initial Stock Transaction (ลบรายการเดิมและสร้างใหม่ตามที่แก้ไข)
                $txDate = $request->input('transaction_date');
                if (!empty($txDate)) {
                    $stockType = $request->input('initial_stock_type', 'first_receipt');
                    $parsedDate = Carbon::parse($txDate);

                    if ($stockType === 'opening_balance') {
                        if ($parsedDate->month == 10 && $parsedDate->day == 1) {
                            $parsedDate = $parsedDate->copy()->subDay();
                        }
                        $partyName = 'ยอดยกมาเริ่มต้น';
                        $refDoc = 'ยอดยกมา';
                        $note = 'ลงทะเบียนยอดยกมาเริ่มต้นคุมบัญชีวัสดุ';
                    } else {
                        // หากเลือกรับเข้าพัสดุ ให้ลบรายการ 'ยอดยกมาเริ่มต้น' ที่อาจค้างอยู่ออกอย่างถาวร เพื่อไม่ให้ถูกแปลงเป็นแถวซ้ำซ้อน
                        Transaction::where('item_type', 'material')
                            ->where('item_id', $material->id)
                            ->where('transaction_type', 'in')
                            ->where(function ($q) {
                                $q->where('reference_doc', 'ยอดยกมา')
                                  ->orWhere('party_name', 'ยอดยกมาเริ่มต้น');
                            })
                            ->forceDelete();

                        $partyName = trim($request->input('receipt_party_name', ''));
                        $refDoc = trim($request->input('receipt_ref_doc', ''));
                        $partyName = !empty($partyName) ? $partyName : 'รับเข้าพัสดุเริ่มต้น';
                        $refDoc = !empty($refDoc) ? $refDoc : 'รับเข้าสต็อก';
                        $note = 'รับเข้าพัสดุครั้งแรก';
                    }

                    $targetTxId = $request->input('target_transaction_id');
                    $targetTx = null;

                    if (!empty($targetTxId)) {
                        $targetTx = Transaction::where('item_type', 'material')
                            ->where('item_id', $material->id)
                            ->where('transaction_type', 'in')
                            ->find($targetTxId);
                    }

                    if (!$targetTx) {
                        $targetTx = Transaction::where('item_type', 'material')
                            ->where('item_id', $material->id)
                            ->where('transaction_type', 'in')
                            ->orderBy('transaction_date', 'asc')
                            ->orderBy('id', 'asc')
                            ->first();
                    }

                    $hasOut = Transaction::where('item_type', 'material')
                        ->where('item_id', $material->id)
                        ->where('transaction_type', 'out')
                        ->exists();

                    $replaceAllIn = $request->boolean('replace_all_in_transactions');

                    if ($replaceAllIn) {
                        // ลบรายการรับเข้าเดิมทั้งหมดของวัสดุนี้ แล้วสร้างใหม่เพียงรายการเดียวตามที่แก้ไข
                        $preservedQty = $targetTx ? $targetTx->quantity : $validated['stock_qty'];
                        if (!$hasOut) {
                            $preservedQty = $validated['stock_qty'];
                        }
                        Transaction::where('item_type', 'material')
                            ->where('item_id', $material->id)
                            ->where('transaction_type', 'in')
                            ->forceDelete();

                        Transaction::create([
                            'transaction_type' => 'in',
                            'item_type' => 'material',
                            'item_id' => $material->id,
                            'quantity' => $preservedQty,
                            'unit_price' => $validated['unit_price'] ?? $material->unit_price,
                            'user_id' => Auth::id() ?? 1,
                            'party_name' => $partyName,
                            'reference_doc' => $refDoc,
                            'note' => $note,
                            'transaction_date' => $parsedDate->toDateString(),
                            'status' => 'completed',
                        ]);

                        $totalOut = Transaction::where('item_type', 'material')
                            ->where('item_id', $material->id)
                            ->where('transaction_type', 'out')
                            ->sum('quantity');
                        $material->stock_qty = max(0, (int) $preservedQty - (int) $totalOut);
                        $material->save();
                    } elseif ($targetTx) {
                        // ลบรายการรับเข้าเดิมทิ้งอย่างถาวร (ไม่ให้เลขที่เอกสารเดิมค้าง) แล้วสร้างรายการใหม่ตามที่แก้ไข
                        $txQty = $hasOut ? $targetTx->quantity : $validated['stock_qty'];
                        $targetTx->forceDelete();

                        Transaction::create([
                            'transaction_type' => 'in',
                            'item_type' => 'material',
                            'item_id' => $material->id,
                            'quantity' => $txQty,
                            'unit_price' => $validated['unit_price'] ?? $material->unit_price,
                            'user_id' => Auth::id() ?? 1,
                            'party_name' => $partyName,
                            'reference_doc' => $refDoc,
                            'note' => $note,
                            'transaction_date' => $parsedDate->toDateString(),
                            'status' => 'completed',
                        ]);
                    } elseif ($validated['stock_qty'] > 0) {
                        Transaction::create([
                            'transaction_type' => 'in',
                            'item_type' => 'material',
                            'item_id' => $material->id,
                            'quantity' => $validated['stock_qty'],
                            'unit_price' => $validated['unit_price'] ?? $material->unit_price,
                            'user_id' => Auth::id() ?? 1,
                            'party_name' => $partyName,
                            'reference_doc' => $refDoc,
                            'note' => $note,
                            'transaction_date' => $parsedDate->toDateString(),
                            'status' => 'completed',
                        ]);
                    }
                }
            });
        } catch (UniqueConstraintViolationException $e) {
            return back()->withInput()->withErrors([
                'material_code' => 'รหัสพัสดุนี้ถูกใช้งานในระบบแล้ว โปรดระบุรหัสอื่น'
            ]);
        }

        return redirect()->route('materials.index')->with('success', 'ปรับปรุงข้อมูลรายการวัสดุและวันที่คุมสต็อกเรียบร้อยแล้ว');
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
