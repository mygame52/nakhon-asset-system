<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index(Request $request)
    {
        $query = ActivityLog::with('user')->latest();

        // Filter by Module
        if ($request->filled('module')) {
            $query->where('module', $request->input('module'));
        }

        // Filter by User
        if ($request->filled('user_id')) {
            $query->where('user_id', $request->input('user_id'));
        }

        // Filter by Keyword / Search
        if ($request->filled('search')) {
            $search = trim($request->input('search'));
            $query->where(function ($q) use ($search) {
                $q->where('description', 'LIKE', "%{$search}%")
                  ->orWhere('action', 'LIKE', "%{$search}%")
                  ->orWhere('ip_address', 'LIKE', "%{$search}%");
            });
        }

        // Filter by Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->input('date_to'));
        }

        $logs = $query->paginate(20)->withQueryString();

        // Summary Stats
        $stats = [
            'total_today'        => ActivityLog::whereDate('created_at', now())->count(),
            'requisition_today'  => ActivityLog::whereDate('created_at', now())->where('module', 'requisition')->count(),
            'stock_today'        => ActivityLog::whereDate('created_at', now())->where('module', 'material_stock')->count(),
            'auth_today'         => ActivityLog::whereDate('created_at', now())->where('module', 'auth')->count(),
        ];

        $users = User::orderBy('name')->get();

        $modules = [
            'requisition'    => 'งานเบิกพัสดุ',
            'material_stock' => 'สมุดคุมวัสดุ/คลัง',
            'material'       => 'จัดการรายการวัสดุ',
            'asset'          => 'จัดการครุภัณฑ์',
            'user'           => 'จัดการผู้ใช้งาน',
            'auth'           => 'การเข้าสู่ระบบ',
            'general'        => 'ทั่วไป',
        ];

        return view('activity_logs.index', compact('logs', 'stats', 'users', 'modules'));
    }
}
