<?php

namespace App\Http\Controllers;

use App\Models\Material;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Transaction;
use App\Models\Notification;
use App\Models\User;
use App\Models\Department;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MaterialRequisitionController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $departmentId = $request->input('department_id');

        $query = Requisition::with(['user', 'department', 'items.material', 'items.approver'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('requisition_code', 'like', "%{$search}%")
                      ->orWhere('requester_name', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items.material', function ($mq) use ($search) {
                          $mq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->when($startDate, function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            });

        if (!$user->hasAnyRole(['admin', 'procurement'])) {
            $query->where('user_id', $user->id);
        }

        $requisitions = $query->latest()->paginate(15)->withQueryString();
        $materials = Material::where('stock_qty', '>', 0)->orderBy('name', 'asc')->get();
        $departments = Department::orderBy('name', 'asc')->get();

        // Remember past requesters for smart autocomplete
        $pastRequesters = Requisition::whereNotNull('requester_name')
            ->where('requester_name', '!=', '')
            ->distinct()
            ->pluck('requester_name')
            ->merge(User::pluck('name'))
            ->unique()
            ->filter()
            ->values();

        return view('requisitions.index', compact('requisitions', 'materials', 'departments', 'pastRequesters'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'reason_for_request' => 'required|string|max:1000',
            'requisition_date' => 'nullable|date',
            'items' => 'required|array|min:1|max:10',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.requested_qty' => 'required|integer|min:1',
        ], [
            'items.max' => 'ใบเบิกพัสดุ 1 ใบ สามารถขอเบิกได้สูงสุด 10 รายการเท่านั้น',
            'items.min' => 'กรุณาเลือกรายการวัสดุอย่างน้อย 1 รายการ',
        ]);

        $user = Auth::user();

        // Check for duplicate materials in the same submission
        $materialIds = array_column($validated['items'], 'material_id');
        if (count($materialIds) !== count(array_unique($materialIds))) {
            return back()->with('error', 'พบรายการวัสดุซ้ำกันในใบเบิกเดียว กรุณารวมเป็นรายการเดียว');
        }

        // Determine Requisition Date (Backdated Support)
        $reqDate = !empty($validated['requisition_date']) ? Carbon::parse($validated['requisition_date']) : now();

        // Generate Collision-Free Code: จ.XXX/YYYY (e.g. จ.001/2569)
        $yearBE = $reqDate->year + 543;
        $seq = Requisition::where('requisition_code', 'LIKE', "%/{$yearBE}")->count() + 1;
        $reqCode = 'จ.' . sprintf('%03d', $seq) . '/' . $yearBE;
        while (Requisition::where('requisition_code', $reqCode)->exists()) {
            $seq++;
            $reqCode = 'จ.' . sprintf('%03d', $seq) . '/' . $yearBE;
        }

        DB::transaction(function () use ($validated, $user, $reqCode, $reqDate) {
            $requisition = Requisition::create([
                'requisition_code' => $reqCode,
                'user_id' => $user->id,
                'department_id' => $user->department_id,
                'status' => 'pending',
                'reason_for_request' => $validated['reason_for_request'],
                'created_at' => $reqDate,
                'updated_at' => $reqDate,
            ]);

            foreach ($validated['items'] as $itemData) {
                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'material_id' => $itemData['material_id'],
                    'requested_qty' => $itemData['requested_qty'],
                    'status' => 'pending',
                    'created_at' => $reqDate,
                    'updated_at' => $reqDate,
                ]);
            }

            // Send notification to Admin & Procurement users
            $officers = User::role(['admin', 'procurement'])->get();
            foreach ($officers as $officer) {
                Notification::send(
                    $officer->id,
                    'คำขอเบิกวัสดุใหม่ (' . $reqCode . ')',
                    $user->name . ' ได้ยื่นขอเบิกวัสดุจำนวน ' . count($validated['items']) . ' รายการ',
                    route('requisitions.show', $requisition->id)
                );
            }
        });

        return redirect()->route('requisitions.index')->with('success', 'ส่งคำขอเบิกวัสดุเรียบร้อยแล้ว อยู่ระหว่างรอการอนุมัติ');
    }

    /**
     * Get the next collision-free requisition code for a specific date (JSON endpoint).
     */
    public function getNextCode(Request $request)
    {
        $dateStr = $request->input('date', now()->toDateString());
        $date = Carbon::parse($dateStr);
        $yearBE = $date->month >= 10 ? ($date->year + 543 + 1) : ($date->year + 543);

        $seq = 1;
        do {
            $candidate = sprintf('จ.%03d/%d', $seq, $yearBE);
            $exists = Requisition::where('requisition_code', $candidate)->exists();
            if ($exists) {
                $seq++;
            }
        } while ($exists);

        return response()->json([
            'success' => true,
            'code' => $candidate,
            'year_be' => $yearBE,
        ]);
    }

    /**
     * Store a backdated direct requisition (บันทึกตัดจ่ายพัสดุย้อนหลัง - อนุมัติทันที)
     */
    public function storeDirect(Request $request)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'procurement'])) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ใช้งานการบันทึกตัดจ่ายพัสดุย้อนหลัง');
        }

        $validated = $request->validate([
            'requisition_code' => 'nullable|string|max:100|unique:requisitions,requisition_code',
            'requisition_date' => 'required|date',
            'requester_name' => 'required|string|max:255',
            'department_id' => 'nullable|exists:departments,id',
            'reason_for_request' => 'nullable|string|max:1000',
            'items' => 'required|array|min:1|max:20',
            'items.*.material_id' => 'required|exists:materials,id',
            'items.*.quantity' => 'required|integer|min:1',
        ], [
            'requisition_code.unique' => 'เลขที่ใบเบิกนี้มีอยู่ในระบบแล้ว โปรดระบุเลขที่อื่นที่ไม่ซ้ำกัน',
            'requisition_date.required' => 'กรุณาระบุวันที่ขอเบิก/ตัดจ่ายพัสดุ',
            'requester_name.required' => 'กรุณาระบุชื่อผู้ขอเบิก',
            'items.min' => 'กรุณาเลือกรายการวัสดุอย่างน้อย 1 รายการ',
            'items.*.material_id.required' => 'กรุณาเลือกวัสดุ',
            'items.*.quantity.min' => 'จำนวนที่จ่ายต้องมีค่าอย่างน้อย 1',
        ]);

        // Check for duplicate materials in the same submission
        $materialIds = array_column($validated['items'], 'material_id');
        if (count($materialIds) !== count(array_unique($materialIds))) {
            return back()->withInput()->with('error', 'พบรายการวัสดุซ้ำกันในใบเบิกเดียว กรุณารวมเป็นรายการเดียว');
        }

        $reqDate = Carbon::parse($validated['requisition_date']);
        $yearBE = $reqDate->month >= 10 ? ($reqDate->year + 543 + 1) : ($reqDate->year + 543);

        // Auto-run requisition code if not specified or empty
        $reqCode = trim($validated['requisition_code'] ?? '');
        if (empty($reqCode)) {
            $seq = 1;
            do {
                $candidate = sprintf('จ.%03d/%d', $seq, $yearBE);
                $exists = Requisition::where('requisition_code', $candidate)->exists();
                if ($exists) {
                    $seq++;
                }
            } while ($exists);
            $reqCode = $candidate;
        }

        // Check stock availability
        foreach ($validated['items'] as $itemData) {
            $material = Material::find($itemData['material_id']);
            if ($material->stock_qty < $itemData['quantity']) {
                return back()->withInput()->with('error', "จำนวนวัสดุ '{$material->name}' ในสต็อกไม่เพียงพอ (คงเหลือ: {$material->stock_qty} {$material->unit})");
            }
        }

        DB::transaction(function () use ($validated, $user, $reqCode, $reqDate) {
            $department = !empty($validated['department_id']) ? Department::find($validated['department_id']) : null;
            $deptName = $department ? $department->name : '';
            $requesterName = trim($validated['requester_name']);

            $requisition = Requisition::create([
                'requisition_code' => $reqCode,
                'user_id' => $user->id,
                'requester_name' => $requesterName,
                'department_id' => $validated['department_id'] ?? null,
                'status' => 'approved',
                'reason_for_request' => $validated['reason_for_request'] ?: 'บันทึกจ่ายพัสดุย้อนหลังตามเอกสารกระดาษ',
                'created_at' => $reqDate,
                'updated_at' => $reqDate,
            ]);

            foreach ($validated['items'] as $itemData) {
                $material = Material::lockForUpdate()->find($itemData['material_id']);
                $qty = (int) $itemData['quantity'];

                RequisitionItem::create([
                    'requisition_id' => $requisition->id,
                    'material_id' => $material->id,
                    'requested_qty' => $qty,
                    'approved_qty' => $qty,
                    'status' => 'approved',
                    'admin_note' => 'อนุมัติจ่ายตามเอกสารกระดาษ (บันทึกย้อนหลัง)',
                    'approved_by' => $user->id,
                    'approved_at' => $reqDate,
                    'officer_approved_by' => $user->id,
                    'officer_approved_at' => $reqDate,
                    'officer_note' => 'เห็นชอบตามเอกสารกระดาษ',
                    'created_at' => $reqDate,
                    'updated_at' => $reqDate,
                ]);

                // Deduct stock
                $material->stock_qty -= $qty;
                $material->save();

                // Log Outward Transaction for Stock Card
                $partyDisplay = $requesterName . (!empty($deptName) ? " ({$deptName})" : '');
                Transaction::create([
                    'transaction_type' => 'out',
                    'item_type' => 'material',
                    'item_id' => $material->id,
                    'quantity' => $qty,
                    'unit_price' => $material->unit_price,
                    'user_id' => $user->id,
                    'party_name' => $partyDisplay,
                    'reference_doc' => $reqCode,
                    'note' => 'เบิกจ่ายตามใบเบิก ' . $reqCode . (!empty($validated['reason_for_request']) ? ' (' . $validated['reason_for_request'] . ')' : ''),
                    'transaction_date' => $reqDate->toDateString(),
                    'created_at' => $reqDate,
                    'status' => 'completed',
                ]);
            }
        });

        return redirect()->route('requisitions.index')->with('success', "บันทึกตัดจ่ายพัสดุย้อนหลังเลขที่ {$reqCode} เรียบร้อยแล้ว (ตัดสต็อกและลงสมุดบัญชีวัสดุให้อัตโนมัติ)");
    }

    public function show(Requisition $requisition)
    {
        $user = Auth::user();

        if (!$user->hasAnyRole(['admin', 'procurement']) && $requisition->user_id !== $user->id) {
            abort(403, 'คุณไม่มีสิทธิ์เข้าดูใบเบิกรายการนี้');
        }

        $requisition->load(['user', 'department', 'items.material', 'items.approver']);

        return view('requisitions.show', compact('requisition'));
    }

    public function officerApproveItem(Request $request, RequisitionItem $item)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['procurement', 'admin'])) {
            return back()->with('error', 'เฉพาะเจ้าหน้าที่พัสดุหรือหัวหน้าพัสดุเท่านั้นที่มีสิทธิ์ดำเนินการในขั้นตอนนี้');
        }

        if ($item->status !== 'pending') {
            return back()->with('error', 'รายการนี้ผ่านการดำเนินการในขั้นตอนเจ้าหน้าที่พัสดุไปแล้ว');
        }

        $validated = $request->validate([
            'approved_qty' => 'required|integer|min:1',
            'officer_note' => 'nullable|string|max:1000',
        ]);

        $material = $item->material;

        if ($material->stock_qty < $validated['approved_qty']) {
            return back()->with('error', 'จำนวนวัสดุในสต็อกคงเหลือไม่เพียงพอ (คงเหลือ: ' . number_format($material->stock_qty) . ' ' . $material->unit . ')');
        }

        if ($validated['approved_qty'] != $item->requested_qty && empty($validated['officer_note'])) {
            return back()->with('error', 'การปรับเปลี่ยนจำนวนที่อนุมัติ จำเป็นต้องระบุเหตุผลในช่องหมายเหตุ');
        }

        DB::transaction(function () use ($item, $validated, $material) {
            $item->update([
                'approved_qty' => $validated['approved_qty'],
                'status' => 'officer_approved',
                'officer_note' => $validated['officer_note'] ?? null,
                'officer_approved_by' => Auth::id(),
                'officer_approved_at' => now(),
            ]);

            $this->updateParentStatus($item->requisition);

            \App\Models\ActivityLog::log(
                'officer_approved',
                'requisition',
                'เจ้าหน้าที่พัสดุ (' . Auth::user()->name . ') อนุมัติขั้นที่ 1 รายการเบิก ' . $material->name . ' จำนวน ' . $validated['approved_qty'] . ' ' . $material->unit . ' [' . $item->requisition->requisition_code . ']',
                $item
            );

            // Send Notification to Head of Procurement (admin role)
            $admins = User::role('admin')->get();
            foreach ($admins as $adminUser) {
                Notification::send(
                    $adminUser->id,
                    'รอหัวหน้าพัสดุอนุมัติ (' . $item->requisition->requisition_code . ')',
                    'เจ้าหน้าที่พัสดุอนุมัติรายการ ' . $material->name . ' แล้ว รอท่านพิจารณาอนุมัติขั้นสุดท้าย',
                    route('requisitions.show', $item->requisition_id)
                );
            }
        });

        return back()->with('success', 'เจ้าหน้าที่พัสดุอนุมัติรายการเรียบร้อยแล้ว (ส่งต่อให้หัวหน้าพัสดุพิจารณาอนุมัติขั้นสุดท้าย)');
    }

    public function approveItem(Request $request, RequisitionItem $item)
    {
        $user = Auth::user();
        
        // Strict check: Head of Procurement must have admin role
        if (!$user->hasRole('admin')) {
            return back()->with('error', 'สิทธิ์การอนุมัติขั้นสุดท้ายเป็นของหัวหน้าพัสดุ (Admin) เท่านั้น');
        }

        // STRICT CHECK: Item MUST be officer_approved first!
        if ($item->status === 'pending') {
            return back()->with('error', 'หัวหน้าพัสดุจะอนุมัติได้ ก็ต่อเมื่อผ่านการอนุมัติจากเจ้าหน้าที่พัสดุเรียบร้อยแล้วเท่านั้น');
        }

        if ($item->status === 'approved') {
            return back()->with('error', 'รายการนี้ได้รับการอนุมัติขั้นสุดท้ายไปเรียบร้อยแล้ว');
        }

        if ($item->status === 'rejected') {
            return back()->with('error', 'รายการนี้ถูกปฏิเสธไปแล้ว');
        }

        $validated = $request->validate([
            'approved_qty' => 'required|integer|min:1',
            'admin_note' => 'nullable|string|max:1000',
            'transaction_date' => 'nullable|date',
        ]);

        $material = $item->material;

        if ($material->stock_qty < $validated['approved_qty']) {
            return back()->with('error', 'จำนวนวัสดุในสต็อกคงเหลือไม่เพียงพอ (คงเหลือ: ' . number_format($material->stock_qty) . ' ' . $material->unit . ')');
        }

        DB::transaction(function () use ($item, $material, $validated) {
            $txDate = !empty($validated['transaction_date']) 
                ? $validated['transaction_date'] 
                : ($item->requisition->created_at ? $item->requisition->created_at->toDateString() : now()->toDateString());

            $item->update([
                'approved_qty' => $validated['approved_qty'],
                'status' => 'approved',
                'admin_note' => $validated['admin_note'] ?? null,
                'approved_by' => Auth::id(),
                'approved_at' => Carbon::parse($txDate)->endOfDay(),
            ]);

            // Deduct Stock
            $material->stock_qty -= $validated['approved_qty'];
            $material->save();

            // Log Transaction
            Transaction::create([
                'transaction_type' => 'out',
                'item_type' => 'material',
                'item_id' => $material->id,
                'quantity' => $validated['approved_qty'],
                'user_id' => $item->requisition->user_id,
                'party_name' => $item->requisition->department ? $item->requisition->department->name : ($item->requisition->user ? $item->requisition->user->name : 'ผู้เบิก'),
                'reference_doc' => $item->requisition->requisition_code,
                'note' => 'เบิกตามคำขอ ' . $item->requisition->requisition_code . ' (รายการ: ' . $material->name . ')' . ($validated['admin_note'] ? ' - ' . $validated['admin_note'] : ''),
                'transaction_date' => $txDate,
                'status' => 'completed',
            ]);

            $this->updateParentStatus($item->requisition);

            \App\Models\ActivityLog::log(
                'head_approved',
                'requisition',
                'หัวหน้าพัสดุ (' . Auth::user()->name . ') อนุมัติขั้นที่ 2 (ตัดสต็อก) รายการเบิก ' . $material->name . ' จำนวน ' . $validated['approved_qty'] . ' ' . $material->unit . ' [' . $item->requisition->requisition_code . ']',
                $item
            );

            // Send Notification to user
            Notification::send(
                $item->requisition->user_id,
                'อนุมัติเบิกวัสดุเรียบร้อย (' . $item->requisition->requisition_code . ')',
                'รายการ ' . $material->name . ' ได้รับการอนุมัติขั้นสุดท้ายจากหัวหน้าพัสดุจำนวน ' . number_format($validated['approved_qty']) . ' ' . $material->unit,
                route('requisitions.show', $item->requisition_id)
            );
        });

        return back()->with('success', 'หัวหน้าพัสดุอนุมัติรายการขั้นสุดท้ายเรียบร้อยแล้ว');
    }

    public function rejectItem(Request $request, RequisitionItem $item)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'procurement'])) {
            return back()->with('error', 'คุณไม่มีสิทธิ์ปฏิเสธรายการเบิก');
        }

        if ($item->status === 'approved') {
            return back()->with('error', 'รายการนี้อนุมัติสมบูรณ์ไปแล้ว ไม่สามารถปฏิเสธได้');
        }

        $validated = $request->validate([
            'admin_note' => 'required|string|max:1000',
        ]);

        DB::transaction(function () use ($item, $validated, $user) {
            $updateData = [
                'status' => 'rejected',
                'admin_note' => $validated['admin_note'],
            ];

            if ($user->hasRole('admin')) {
                $updateData['approved_by'] = $user->id;
                $updateData['approved_at'] = now();
            } else {
                $updateData['officer_approved_by'] = $user->id;
                $updateData['officer_approved_at'] = now();
                $updateData['officer_note'] = $validated['admin_note'];
            }

            $item->update($updateData);

            $this->updateParentStatus($item->requisition);

            // Send Notification to user
            Notification::send(
                $item->requisition->user_id,
                'ปฏิเสธรายการเบิกวัสดุ (' . $item->requisition->requisition_code . ')',
                'รายการ ' . $item->material->name . ' ถูกปฏิเสธ: ' . $validated['admin_note'],
                route('requisitions.show', $item->requisition_id)
            );
        });

        return back()->with('success', 'ปฏิเสธรายการวัสดุเรียบร้อยแล้ว');
    }

    private function updateParentStatus(Requisition $requisition)
    {
        $items = $requisition->items;
        $totalItems = $items->count();
        $approvedCount = $items->where('status', 'approved')->count();
        $officerApprovedCount = $items->where('status', 'officer_approved')->count();
        $rejectedCount = $items->where('status', 'rejected')->count();
        $pendingCount = $items->where('status', 'pending')->count();

        if ($pendingCount === 0 && $officerApprovedCount === 0) {
            if ($approvedCount === $totalItems) {
                $requisition->status = 'approved';
            } elseif ($rejectedCount === $totalItems) {
                $requisition->status = 'rejected';
            } else {
                $requisition->status = 'partial';
            }
        } elseif ($officerApprovedCount > 0) {
            $requisition->status = 'officer_approved';
        } else {
            if ($approvedCount > 0 || $rejectedCount > 0) {
                $requisition->status = 'partial';
            } else {
                $requisition->status = 'pending';
            }
        }

        $requisition->save();
    }

    public function print(Requisition $requisition)
    {
        $user = Auth::user();
        if (!$user->hasAnyRole(['admin', 'procurement']) && $requisition->user_id !== $user->id) {
            abort(403);
        }

        $requisition->load(['user', 'department', 'items.material', 'items.approver']);

        return view('requisitions.print', compact('requisition'));
    }

    public function exportCsv(Request $request)
    {
        $user = Auth::user();
        $status = $request->input('status');
        $search = $request->input('search');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $departmentId = $request->input('department_id');

        $query = Requisition::with(['user', 'department', 'items.material', 'items.approver'])
            ->when($search, function ($q) use ($search) {
                $q->where(function ($sq) use ($search) {
                    $sq->where('requisition_code', 'like', "%{$search}%")
                      ->orWhereHas('user', function ($uq) use ($search) {
                          $uq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items.material', function ($mq) use ($search) {
                          $mq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($status, function ($q) use ($status) {
                $q->where('status', $status);
            })
            ->when($departmentId, function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
            ->when($startDate, function ($q) use ($startDate) {
                $q->whereDate('created_at', '>=', $startDate);
            })
            ->when($endDate, function ($q) use ($endDate) {
                $q->whereDate('created_at', '<=', $endDate);
            });

        if (!$user->hasAnyRole(['admin', 'procurement'])) {
            $query->where('user_id', $user->id);
        }

        $requisitions = $query->latest()->get();

        $filename = 'รายงานการเบิกวัสดุ_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($requisitions) {
            $file = fopen('php://output', 'w');
            fprintf($file, chr(0xEF).chr(0xBB).chr(0xBF)); // UTF-8 BOM

            fputcsv($file, [
                'เลขที่ใบเบิก',
                'วันที่ขอเบิก',
                'ผู้ขอเบิก',
                'แผนก/หน่วยงาน',
                'รายการวัสดุ',
                'จำนวนขอเบิก',
                'จำนวนอนุมัติ',
                'หน่วยนับ',
                'สถานะรายการ',
                'สถานะใบเบิก',
                'หมายเหตุเจ้าหน้าที่',
            ]);

            $statusMap = [
                'pending' => 'รออนุมัติ',
                'partial' => 'อนุมัติบางส่วน',
                'approved' => 'อนุมัติแล้ว',
                'rejected' => 'ปฏิเสธ',
            ];

            $itemStatusMap = [
                'pending' => 'รออนุมัติ',
                'approved' => 'อนุมัติแล้ว',
                'rejected' => 'ปฏิเสธ',
            ];

            foreach ($requisitions as $req) {
                foreach ($req->items as $item) {
                    fputcsv($file, [
                        $req->requisition_code,
                        $req->created_at->format('d/m/Y H:i'),
                        $req->user->name ?? '-',
                        $req->department->name ?? '-',
                        $item->material->name ?? '-',
                        $item->requested_qty,
                        $item->approved_qty ?? 0,
                        $item->material->unit ?? '-',
                        $itemStatusMap[$item->status] ?? $item->status,
                        $statusMap[$req->status] ?? $req->status,
                        $item->admin_note ?? '-',
                    ]);
                }
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
