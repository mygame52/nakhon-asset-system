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
        
        return view('dashboard', compact('totalAssets', 'totalMaterials', 'recentAssets'));
    }
}
