<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Material;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialReceiptController extends Controller
{
    /**
     * Display a listing of inward material receipts (รายการรับวัสดุเข้าคลัง).
     */
    public function index(Request $request)
    {
        $query = Transaction::withTrashed()
            ->with(['material', 'user', 'deleter', 'editor'])
            ->where('transaction_type', 'in')
            ->where('item_type', 'material')
            ->latest('transaction_date')
            ->latest('id');

        // Filter by Status (all, active, deleted)
        $statusFilter = $request->input('status_filter', 'all');
        if ($statusFilter === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($statusFilter === 'deleted') {
            $query->onlyTrashed();
        }

        // Filter by Material
        if ($request->filled('material_id')) {
            $query->where('item_id', $request->input('material_id'));
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('transaction_date', '>=', $request->input('date_from'));
        }
        if ($request->filled('date_to')) {
            $query->whereDate('transaction_date', '<=', $request->input('date_to'));
        }

        // Search Keyword
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('reference_doc', 'LIKE', "%{$search}%")
                  ->orWhere('party_name', 'LIKE', "%{$search}%")
                  ->orWhere('note', 'LIKE', "%{$search}%")
                  ->orWhere('edit_reason', 'LIKE', "%{$search}%")
                  ->orWhere('delete_reason', 'LIKE', "%{$search}%")
                  ->orWhereHas('material', function ($mq) use ($search) {
                      $mq->where('name', 'LIKE', "%{$search}%")
                        ->orWhere('material_code', 'LIKE', "%{$search}%");
                  });
            });
        }

        $receipts = $query->paginate(15)->withQueryString();

        // Statistics Summary
        $stats = [
            'total_active'        => Transaction::where('transaction_type', 'in')->where('item_type', 'material')->whereNull('deleted_at')->count(),
            'total_value'         => Transaction::where('transaction_type', 'in')->where('item_type', 'material')->whereNull('deleted_at')->select(DB::raw('SUM(quantity * COALESCE(unit_price, 0)) as total'))->value('total') ?? 0,
            'total_deleted'       => Transaction::onlyTrashed()->where('transaction_type', 'in')->where('item_type', 'material')->count(),
            'this_month_receipts' => Transaction::where('transaction_type', 'in')->where('item_type', 'material')->whereNull('deleted_at')->whereMonth('transaction_date', now()->month)->whereYear('transaction_date', now()->year)->count(),
        ];

        $materials = Material::orderBy('name')->get();

        return view('material_receipts.index', compact('receipts', 'stats', 'materials'));
    }

    /**
     * Update an inward material receipt record (แก้ไขรายการรับเข้า - ต้องระบุเหตุผล).
     */
    public function update(Request $request, Transaction $material_receipt)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'quantity'         => 'required|integer|min:1',
            'unit_price'       => 'nullable|numeric|min:0',
            'party_name'       => 'required|string|max:255',
            'reference_doc'    => 'nullable|string|max:255',
            'note'             => 'nullable|string|max:1000',
            'edit_reason'      => 'required|string|min:5|max:1000',
        ], [
            'edit_reason.required' => 'จำเป็นต้องระบุเหตุผลในการแก้ไขข้อมูลเสมอ',
            'edit_reason.min'      => 'โปรดระบุเหตุผลในการแก้ไขข้อมูลให้ชัดเจน (อย่างน้อย 5 ตัวอักษร)',
        ]);

        $material = Material::findOrFail($material_receipt->item_id);

        DB::transaction(function () use ($material_receipt, $material, $validated) {
            $oldQty = $material_receipt->quantity;
            $newQty = $validated['quantity'];
            $qtyDelta = $newQty - $oldQty;

            // Update Material Stock Quantity according to the delta
            $material->stock_qty = max(0, $material->stock_qty + $qtyDelta);
            if (!empty($validated['unit_price'])) {
                $material->unit_price = $validated['unit_price'];
            }
            $material->save();

            // Update Transaction Record
            $material_receipt->update([
                'transaction_date' => $validated['transaction_date'],
                'quantity'         => $validated['quantity'],
                'unit_price'       => $validated['unit_price'] ?? $material_receipt->unit_price,
                'party_name'       => $validated['party_name'],
                'reference_doc'    => $validated['reference_doc'] ?? $material_receipt->reference_doc,
                'note'             => $validated['note'] ?? $material_receipt->note,
                'edited_by'        => Auth::id(),
                'edited_at'        => now(),
                'edit_reason'      => $validated['edit_reason'],
            ]);

            // Log Audit Activity
            ActivityLog::log(
                'stock_in_edit',
                'material_stock',
                "แก้ไขรายการรับวัสดุเข้าคลัง #{$material_receipt->id} ({$material->name}): เปลี่ยนจำนวนจาก {$oldQty} เป็น {$newQty} {$material->unit} | เหตุผล: {$validated['edit_reason']}",
                $material_receipt
            );
        });

        return back()->with('success', 'แก้ไขรายการรับวัสดุเข้าและปรับปรุงยอดคงเหลือในสต็อกเรียบร้อยแล้ว');
    }

    /**
     * Remove / Cancel an inward material receipt record (ลบรายการรับเข้า - ต้องระบุเหตุผลและเก็บบันทึกร่องรอย).
     */
    public function destroy(Request $request, Transaction $material_receipt)
    {
        $validated = $request->validate([
            'delete_reason' => 'required|string|min:5|max:1000',
        ], [
            'delete_reason.required' => 'จำเป็นต้องระบุเหตุผลในการลบรายการรับเข้าเสมอ',
            'delete_reason.min'      => 'โปรดระบุเหตุผลในการลบให้ชัดเจน (อย่างน้อย 5 ตัวอักษร)',
        ]);

        $material = Material::findOrFail($material_receipt->item_id);

        DB::transaction(function () use ($material_receipt, $material, $validated) {
            $deductQty = $material_receipt->quantity;

            // Deduct stock quantity
            $material->stock_qty = max(0, $material->stock_qty - $deductQty);
            $material->save();

            // Record auditor info and soft delete
            $material_receipt->update([
                'status'        => 'cancelled',
                'deleted_by'    => Auth::id(),
                'delete_reason' => $validated['delete_reason'],
            ]);

            $material_receipt->delete(); // Soft Delete

            // Log Audit Activity
            $user = Auth::user();
            ActivityLog::log(
                'stock_in_delete',
                'material_stock',
                "ยกเลิก/ลบรายการรับวัสดุเข้าคลัง #{$material_receipt->id} ({$material->name}) จำนวน {$deductQty} {$material->unit} | โดย: {$user->name} (ID: {$user->id}) | เหตุผล: {$validated['delete_reason']}",
                $material_receipt
            );
        });

        return back()->with('success', 'ยกเลิก/ลบรายการรับวัสดุเข้าคลังเรียบร้อยแล้ว (ตัดยอดคงเหลือออกจากคลังและบันทึกร่องรอยในระบบแล้ว)');
    }
}
