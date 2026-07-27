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
        
        $statusCounts = [
            'normal' => Asset::where('status', 'ใช้งานปกติ')->count(),
            'repair' => Asset::where('status', 'รอซ่อม')->count(),
            'broken' => Asset::where('status', 'ชำรุด')->count(),
            'deteriorated' => Asset::where('status', 'เสื่อมสภาพ')->count(),
            'disposed' => Asset::where('status', 'จำหน่ายออก')->count(),
        ];
        
        return view('dashboard', compact('totalAssets', 'totalMaterials', 'recentAssets', 'statusCounts'));
    }
}
