<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Asset;
use App\Models\Material;
use App\Models\Category;

class DashboardController extends Controller
{
    public function index()
    {
        $totalAssets = Asset::count();
        $totalMaterials = Material::count();
        $recentAssets = Asset::with(['category', 'location'])->latest()->take(5)->get();

        $groupedStatuses = Asset::selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $statusCounts = [
            'normal' => (int) ($groupedStatuses['ใช้งานปกติ'] ?? 0),
            'repair' => (int) ($groupedStatuses['รอซ่อม'] ?? 0),
            'broken' => (int) ($groupedStatuses['ชำรุด'] ?? 0),
            'deteriorated' => (int) ($groupedStatuses['เสื่อมสภาพ'] ?? 0),
            'disposed' => (int) ($groupedStatuses['จำหน่ายออก'] ?? 0),
        ];

        return view('dashboard', compact('totalAssets', 'totalMaterials', 'recentAssets', 'statusCounts'));
    }
}
