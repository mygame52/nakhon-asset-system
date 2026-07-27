<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_type' => 'required|in:in,out',
            'material_id' => 'required|exists:materials,id',
            'request_qty' => 'required|integer|min:1',
            'note' => 'nullable|string',
        ]);

        $material = Material::findOrFail($validated['material_id']);

        if ($validated['transaction_type'] === 'out') {
            if ($material->stock_qty < $validated['request_qty']) {
                return back()->withErrors(['request_qty' => 'จำนวนสต็อกไม่เพียงพอสำหรับการเบิก']);
            }
            $material->stock_qty -= $validated['request_qty'];
        } elseif ($validated['transaction_type'] === 'in') {
            $material->stock_qty += $validated['request_qty'];
        }

        DB::transaction(function () use ($material, $validated) {
            $material->save();

            Transaction::create([
                'transaction_type' => $validated['transaction_type'],
                'item_type' => 'material',
                'item_id' => $material->id,
                'quantity' => $validated['request_qty'],
                'user_id' => Auth::id() ?: 1, // fallback to 1 if testing without auth
                'note' => $validated['note'] ?? null,
                'transaction_date' => now()->toDateString(),
                'status' => 'completed',
            ]);
        });

        return back()->with('success', 'บันทึกการทำธุรกรรมเรียบร้อยแล้ว');
    }
}
