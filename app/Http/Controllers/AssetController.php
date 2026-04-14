<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Department;
use App\Models\Vendor;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        $assets = Asset::with(['category', 'department'])
            ->when($search, function ($query) use ($search) {
                $query->where('asset_code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        return view('assets.index', compact('assets'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::where('type', 'asset')->get();
        $locations = Location::all();
        $departments = Department::all();
        $vendors = Vendor::all();

        return view('assets.create', compact('categories', 'locations', 'departments', 'vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'asset_code' => 'required|unique:assets,asset_code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'location_id' => 'nullable|exists:locations,id',
            'department_id' => 'nullable|exists:departments,id',
            'unit_price' => 'nullable|numeric|min:0',
            'purchase_date' => 'nullable|date',
            'status' => 'required|string',
        ]);

        Asset::create($request->all());

        return redirect()->route('assets.index')->with('success', 'บันทึกข้อมูลครุภัณฑ์เรียบร้อยแล้ว');
    }

    /**
     * Display the specified resource.
     */
    public function show(Asset $asset)
    {
        $asset->load(['category', 'location', 'department', 'vendor']);
        
        // Generate QR Code linking to this page (or a specific mobile view)
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(150)
            ->format('svg')
            ->generate(route('assets.show', $asset->id));

        return view('assets.show', compact('asset', 'qrCode'));
    }

    public function batchPrint()
    {
        $assets = Asset::all();
        return view('assets.batch-print', compact('assets'));
    }

    public function printLabels(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'กรุณาเลือกรายการที่ต้องการพิมพ์');
        }

        $assets = Asset::whereIn('id', $ids)->get();
        $labels = [];

        foreach ($assets as $asset) {
            $labels[] = [
                'asset' => $asset,
                'qrCode' => \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)->format('svg')->generate(route('assets.show', $asset->id))
            ];
        }

        return view('assets.print-labels', compact('labels'));
    }

    public function printLabel(Asset $asset)
    {
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)
            ->format('svg')
            ->generate(route('assets.show', $asset->id));
            
        return view('assets.print-label', compact('asset', 'qrCode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Asset $asset)
    {
        $categories = Category::where('type', 'asset')->get();
        $locations = Location::all();
        $departments = Department::all();
        $vendors = Vendor::all();

        return view('assets.edit', compact('asset', 'categories', 'locations', 'departments', 'vendors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'asset_code' => 'required|unique:assets,asset_code,' . $asset->id,
            'name' => 'required|string|max:255',
            'status' => 'required|string',
        ]);

        $asset->update($request->all());

        return redirect()->route('assets.show', $asset->id)->with('success', 'แก้ไขข้อมูลครุภัณฑ์เรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        $asset->delete();
        return redirect()->route('assets.index')->with('success', 'ลบข้อมูลครุภัณฑ์เรียบร้อยแล้ว');
    }
}
