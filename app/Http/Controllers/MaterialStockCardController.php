<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Category;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class MaterialStockCardController extends Controller
{
    /**
     * Display a listing of material stock cards.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $statusFilter = $request->input('status'); // low, over, normal

        $query = Material::with('category');

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('material_code', 'like', "%{$search}%")
                  ->orWhere('location_name', 'like', "%{$search}%");
            });
        }

        if ($categoryId) {
            $query->where('category_id', $categoryId);
        }

        if ($statusFilter === 'low') {
            $query->whereRaw('stock_qty <= min_stock AND min_stock > 0');
        } elseif ($statusFilter === 'over') {
            $query->whereRaw('stock_qty >= max_stock AND max_stock > 0');
        } elseif ($statusFilter === 'normal') {
            $query->whereRaw('(min_stock = 0 OR stock_qty > min_stock) AND (max_stock = 0 OR stock_qty < max_stock)');
        }

        $materials = $query->orderBy('name', 'asc')->paginate(15)->withQueryString();
        $categories = Category::where('type', 'material')->get();

        // Statistics Summary
        $totalMaterials = Material::count();
        $lowStockCount = Material::whereRaw('stock_qty <= min_stock AND min_stock > 0')->count();
        $totalInventoryValue = Material::select(DB::raw('SUM(stock_qty * COALESCE(unit_price, 0)) as total_val'))->value('total_val') ?? 0;

        return view('stock_cards.index', compact('materials', 'categories', 'totalMaterials', 'lowStockCount', 'totalInventoryValue'));
    }

    /**
     * Display the stock card ledger for a specific material.
     */
    public function show(Request $request, Material $material)
    {
        $material->load('category');

        $fiscalYearBE = $request->input('fiscal_year', $this->getCurrentFiscalYearBE());
        $fiscalYearAD = $fiscalYearBE - 543;

        // Fiscal Year Date Bounds: 1 Oct (AD-1) to 30 Sep (AD)
        $fyStart = Carbon::createFromDate($fiscalYearAD - 1, 10, 1)->startOfDay();
        $fyEnd = Carbon::createFromDate($fiscalYearAD, 9, 30)->endOfDay();

        // Calculate Opening Balance prior to fyStart (including initial balance on fyStart)
        $priorIn = Transaction::where('item_type', 'material')
            ->where('item_id', $material->id)
            ->where('transaction_type', 'in')
            ->where(function ($q) use ($fyStart) {
                $q->whereDate('transaction_date', '<', $fyStart->toDateString())
                  ->orWhere(function ($sq) use ($fyStart) {
                      $sq->whereDate('transaction_date', '=', $fyStart->toDateString())
                         ->where('reference_doc', 'ยอดยกมา');
                  });
            })
            ->sum('quantity');

        $priorOut = Transaction::where('item_type', 'material')
            ->where('item_id', $material->id)
            ->where('transaction_type', 'out')
            ->whereDate('transaction_date', '<', $fyStart->toDateString())
            ->sum('quantity');

        $openingBalance = max(0, $priorIn - $priorOut);

        // Fetch current Fiscal Year transactions (excluding fyStart opening balance if rolled up)
        $rawTransactions = Transaction::with('user')
            ->where('item_type', 'material')
            ->where('item_id', $material->id)
            ->whereDate('transaction_date', '>=', $fyStart->toDateString())
            ->whereDate('transaction_date', '<=', $fyEnd->toDateString())
            ->where(function ($q) use ($fyStart) {
                $q->whereDate('transaction_date', '>', $fyStart->toDateString())
                  ->orWhere('reference_doc', '!=', 'ยอดยกมา');
            })
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        // Compute running balance for the fiscal year
        $entries = [];
        $runningBalance = $openingBalance;

        // First Entry: Opening Balance if prior transactions exist or opening balance > 0
        if ($openingBalance > 0 || $rawTransactions->isNotEmpty()) {
            $entries[] = (object) [
                'id' => 0,
                'date' => $fyStart->toDateString(),
                'party_name' => 'ยอดยกมาจากปีงบประมาณ ' . ($fiscalYearBE - 1),
                'reference_doc' => 'ยอดยกมา',
                'unit_price' => $material->unit_price,
                'in_qty' => $openingBalance,
                'out_qty' => 0,
                'balance_qty' => $openingBalance,
                'note' => 'ยอดยกมาจากปีก่อนหน้าตามระเบียบพัสดุ',
                'created_by' => 'ระบบ',
            ];
        }

        foreach ($rawTransactions as $tx) {
            $inQty = 0;
            $outQty = 0;

            if ($tx->transaction_type === 'in') {
                $inQty = $tx->quantity;
                $runningBalance += $inQty;
            } else {
                $outQty = $tx->quantity;
                $runningBalance -= $outQty;
            }

            $entries[] = (object) [
                'id' => $tx->id,
                'date' => $tx->transaction_date ? $tx->transaction_date->format('Y-m-d') : $tx->created_at->format('Y-m-d'),
                'party_name' => $tx->party_name ?? ($tx->user ? $tx->user->name : '-'),
                'reference_doc' => $tx->reference_doc ?? '-',
                'unit_price' => $tx->unit_price ?? $material->unit_price,
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'balance_qty' => $runningBalance,
                'note' => $tx->note ?? '-',
                'created_by' => $tx->user ? $tx->user->name : 'ระบบ',
            ];
        }

        $currentFY = $this->getCurrentFiscalYearBE();
        $availableYears = [
            $currentFY,
            $currentFY - 1,
            $currentFY - 2,
            $currentFY - 3,
            $currentFY - 4,
            $currentFY - 5,
        ];

        return view('stock_cards.show', compact('material', 'entries', 'runningBalance', 'fiscalYearBE', 'openingBalance', 'availableYears'));
    }

    /**
     * Printable A4 official government form "บัญชีวัสดุ".
     */
    public function print(Request $request, Material $material)
    {
        $material->load('category');

        $fiscalYearBE = $request->input('fiscal_year', $this->getCurrentFiscalYearBE());
        $fiscalYearAD = $fiscalYearBE - 543;

        $fyStart = Carbon::createFromDate($fiscalYearAD - 1, 10, 1)->startOfDay();
        $fyEnd = Carbon::createFromDate($fiscalYearAD, 9, 30)->endOfDay();

        // Calculate Opening Balance prior to fyStart (including initial balance on fyStart)
        $priorIn = Transaction::where('item_type', 'material')
            ->where('item_id', $material->id)
            ->where('transaction_type', 'in')
            ->where(function ($q) use ($fyStart) {
                $q->whereDate('transaction_date', '<', $fyStart->toDateString())
                  ->orWhere(function ($sq) use ($fyStart) {
                      $sq->whereDate('transaction_date', '=', $fyStart->toDateString())
                         ->where('reference_doc', 'ยอดยกมา');
                  });
            })
            ->sum('quantity');

        $priorOut = Transaction::where('item_type', 'material')
            ->where('item_id', $material->id)
            ->where('transaction_type', 'out')
            ->whereDate('transaction_date', '<', $fyStart->toDateString())
            ->sum('quantity');

        $openingBalance = max(0, $priorIn - $priorOut);

        $rawTransactions = Transaction::with('user')
            ->where('item_type', 'material')
            ->where('item_id', $material->id)
            ->whereDate('transaction_date', '>=', $fyStart->toDateString())
            ->whereDate('transaction_date', '<=', $fyEnd->toDateString())
            ->where(function ($q) use ($fyStart) {
                $q->whereDate('transaction_date', '>', $fyStart->toDateString())
                  ->orWhere('reference_doc', '!=', 'ยอดยกมา');
            })
            ->orderBy('transaction_date', 'asc')
            ->orderBy('created_at', 'asc')
            ->orderBy('id', 'asc')
            ->get();

        $entries = [];
        $runningBalance = $openingBalance;

        if ($openingBalance > 0 || $rawTransactions->isNotEmpty()) {
            $entries[] = (object) [
                'date' => $fyStart,
                'party_name' => 'ยอดยกมาจากปีงบประมาณ ' . ($fiscalYearBE - 1),
                'reference_doc' => 'ยอดยกมา',
                'unit_price' => $material->unit_price,
                'in_qty' => $openingBalance,
                'out_qty' => 0,
                'balance_qty' => $openingBalance,
                'note' => 'ยอดยกมาจากปีก่อนหน้า',
            ];
        }

        foreach ($rawTransactions as $tx) {
            $inQty = 0;
            $outQty = 0;

            if ($tx->transaction_type === 'in') {
                $inQty = $tx->quantity;
                $runningBalance += $inQty;
            } else {
                $outQty = $tx->quantity;
                $runningBalance -= $outQty;
            }

            $entries[] = (object) [
                'date' => $tx->transaction_date ? $tx->transaction_date : $tx->created_at,
                'party_name' => $tx->party_name ?? ($tx->user ? $tx->user->name : '-'),
                'reference_doc' => $tx->reference_doc ?? '-',
                'unit_price' => $tx->unit_price ?? $material->unit_price,
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'balance_qty' => $runningBalance,
                'note' => $tx->note ?? '',
            ];
        }

        return view('stock_cards.print', compact('material', 'entries', 'runningBalance', 'fiscalYearBE', 'openingBalance'));
    }

    /**
     * Store incoming stock transaction (รับวัสดุเข้าสต็อก).
     */
    public function storeStockIn(Request $request, Material $material)
    {
        $validated = $request->validate([
            'transaction_date' => 'required|date',
            'party_name' => 'required|string|max:255',
            'reference_doc' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'nullable|numeric|min:0',
            'note' => 'nullable|string|max:1000',
        ]);

        DB::transaction(function () use ($material, $validated) {
            // Increase stock
            $material->stock_qty += $validated['quantity'];
            
            // Rule #7: Update Latest Unit Price (ราคารับเข้าครั้งหลังสุด)
            if (!empty($validated['unit_price'])) {
                $material->unit_price = $validated['unit_price'];
            }
            $material->save();

            // Record Inward Transaction
            $tx = Transaction::create([
                'transaction_type' => 'in',
                'item_type' => 'material',
                'item_id' => $material->id,
                'quantity' => $validated['quantity'],
                'unit_price' => $validated['unit_price'] ?? $material->unit_price,
                'user_id' => Auth::id(),
                'party_name' => $validated['party_name'],
                'reference_doc' => $validated['reference_doc'] ?? 'รับเข้าสต็อก',
                'note' => $validated['note'] ?? 'รับวัสดุเข้าสต็อกคลังพัสดุ',
                'transaction_date' => $validated['transaction_date'],
                'status' => 'completed',
            ]);

            \App\Models\ActivityLog::log(
                'stock_in',
                'material_stock',
                'บันทึกรับวัสดุเข้าคลัง: ' . $material->name . ' จำนวน ' . $validated['quantity'] . ' ' . $material->unit . ' จาก (' . $validated['party_name'] . ') เอกสาร: ' . ($validated['reference_doc'] ?? 'รับเข้าสต็อก'),
                $material
            );
        });

        return back()->with('success', 'บันทึกรับวัสดุเข้าสต็อกเรียบร้อยแล้ว');
    }

    /**
     * Update material stock card control settings (รหัส, ที่เก็บ, จำนวนอย่างสูง, จำนวนอย่างต่ำ).
     */
    public function updateCardSettings(Request $request, Material $material)
    {
        $validated = $request->validate([
            'material_code' => 'nullable|string|max:100',
            'location_name' => 'nullable|string|max:255',
            'min_stock' => 'required|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'unit_price' => 'nullable|numeric|min:0',
            'specs' => 'nullable|string|max:1000',
        ]);

        $material->update($validated);

        return back()->with('success', 'ปรับปรุงข้อมูลการคุมบัญชีวัสดุเรียบร้อยแล้ว');
    }

    /**
     * Helper to compute Thai Fiscal Year (1 Oct - 30 Sep)
     */
    private function getCurrentFiscalYearBE(): int
    {
        $now = now();
        $yearAD = $now->month >= 10 ? $now->year + 1 : $now->year;
        return $yearAD + 543;
    }
}
