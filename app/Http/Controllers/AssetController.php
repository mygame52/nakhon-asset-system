<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Category;
use App\Models\Location;
use App\Models\Department;
use App\Models\Vendor;
use Illuminate\Http\Request;
use App\Http\Requests\StoreAssetRequest;
use App\Http\Requests\UpdateAssetRequest;

use App\Exports\AssetExport;
use App\Exports\AssetTemplateExport;
use App\Imports\AssetImport;
use Maatwebsite\Excel\Facades\Excel;

class AssetController extends Controller
{
    public function downloadTemplate()
    {
        return Excel::download(new AssetTemplateExport, 'asset_import_template.xlsx');
    }

    public function export(Request $request)
    {
        $filters = $request->only(['search', 'category_id', 'location_id', 'department_id', 'status']);
        $fileName = 'assets_registry_' . date('Ymd_His') . '.xlsx';
        return Excel::download(new AssetExport($filters), $fileName);
    }

    public function importPreview(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240',
        ]);

        try {
            $file = $request->file('file');
            $fileName = time() . '_' . preg_replace('/[^A-Za-z0-9.\-_]/', '', $file->getClientOriginalName());
            $path = $file->storeAs('temp_imports', $fileName);

            // Read the raw array dump for preview using native Storage facade
             $absolutePath = \Illuminate\Support\Facades\Storage::path($path);
             $data = Excel::toArray(new \stdClass, $absolutePath);
            if (empty($data) || empty($data[0])) {
                 return back()->with('error', 'ไฟล์ Excel ใช้งานไม่ได้หรือไม่มีข้อมูล');
            }

            // Extract all data rows, ignoring header row 0. Filter out completely empty rows
            $rawRows = array_slice($data[0], 1); 
            $previewRows = array_values(array_filter($rawRows, function($row) {
                return !empty($row[0]) || !empty($row[1]);
            }));

            // Prepare columns length normalization to prevent JS index errors. Ensure exactly 10 columns.
            $normalizedRows = array_map(function($row) {
                return array_pad($row, 10, '');
            }, $previewRows);

            return view('assets.import_preview', ['previewRows' => $normalizedRows, 'path' => $path]);
        } catch (\Exception $e) {
            return back()->with('error', 'เกิดข้อผิดพลาดในการอ่านไฟล์: ' . $e->getMessage());
        }
    }

    public function importProcessJson(Request $request)
    {
        $request->validate([
            'payload' => 'required|string',
            'path' => 'nullable|string',
        ]);

        try {
            $rows = json_decode($request->input('payload'), true);

            foreach ($rows as $row) {
                $purchaseDateRaw = $row[0] ?? null;
                $assetCode = $row[1] ?? null;
                $categoryName = $row[2] ?? null;
                $model = $row[3] ?? null;
                $name = $row[4] ?? null;
                $unitPriceRaw = $row[5] ?? 0;
                $acquisitionType = $row[6] ?? null;
                $departmentName = $row[7] ?? null;
                $locationName = $row[8] ?? null;
                $status = $row[9] ?? 'ใช้งานปกติ';
                
                if (empty($assetCode) || empty($name)) {
                    continue;
                }

                $purchaseDate = null;
                $rawDate = trim($purchaseDateRaw);
                if (!empty($rawDate) && $rawDate !== '-') {
                    try {
                        if (is_numeric($rawDate)) {
                            $dateObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($rawDate);
                            if ((int)$dateObj->format('Y') > 2500) {
                                $dateObj->modify('-543 years');
                            }
                            $purchaseDate = $dateObj->format('Y-m-d');
                        } else {
                            preg_match_all('/\d+/', $rawDate, $matches);
                            $numbers = $matches[0];
                            if (count($numbers) > 0) {
                                $day = 1; $month = 1; $year = 0;
                                if (count($numbers) >= 3) {
                                    if ((int)$numbers[0] > 1000) {
                                        $year = (int)$numbers[0];
                                        $month = (int)$numbers[1];
                                        $day = (int)$numbers[2];
                                    } else {
                                        $day = (int)$numbers[0];
                                        $month = (int)$numbers[1];
                                        $year = (int)$numbers[2];
                                    }
                                } elseif (count($numbers) == 2) {
                                    $month = (int)$numbers[0];
                                    $year = (int)$numbers[1];
                                } else {
                                    $year = (int)$numbers[0];
                                }

                                if ($day < 1 || $day > 31) $day = 1;
                                if ($month < 1 || $month > 12) $month = 1;
                                
                                if ($year > 0 && $year < 100) {
                                    $year += ($year >= 50) ? 2500 : 2000;
                                }
                                if ($year > 2400) {
                                    $year -= 543;
                                }

                                if ($year > 1900 && $year < 2200) {
                                    $purchaseDate = sprintf('%04d-%02d-%02d', $year, $month, $day);
                                }
                            }
                        }
                    } catch (\Exception $e) {
                        $purchaseDate = null;
                    }
                }

                $locationId = null;
                if (!empty($locationName) && $locationName !== '-') {
                    $loc = Location::firstOrCreate(['name' => trim($locationName)]);
                    $locationId = $loc->id;
                }

                $departmentId = null;
                if (!empty($departmentName) && $departmentName !== '-') {
                    $dept = Department::firstOrCreate(['name' => trim($departmentName)]);
                    $departmentId = $dept->id;
                }

                $categoryId = null;
                if (!empty($categoryName) && $categoryName !== '-') {
                    $cat = Category::firstOrCreate(['name' => trim($categoryName)]);
                    $categoryId = $cat->id;
                }

                $asset = Asset::withTrashed()->firstOrNew(['asset_code' => $assetCode]);
                
                $asset->fill([
                    'name'             => $name,
                    'category_id'      => $categoryId,
                    'model'            => $model,
                    'purchase_date'    => $purchaseDate,
                    'acquisition_type' => $acquisitionType,
                    'location_id'      => $locationId,
                    'department_id'    => $departmentId,
                    'unit_price'       => is_numeric($unitPriceRaw) ? floatval($unitPriceRaw) : 0,
                    'status'           => $status,
                ]);

                if ($asset->trashed()) {
                    $asset->restore();
                }

                $asset->save();
            }

            // Cleanup temp file if tracking
            if ($request->filled('path')) {
                $fullPath = \Illuminate\Support\Facades\Storage::path($request->input('path'));
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }

            return redirect()->route('assets.index')->with('success', 'อัปเดตและนำเข้าข้อมูลครุภัณฑ์เรียบร้อยแล้ว');
        } catch (\Exception $e) {
            return redirect()->route('assets.index')->with('error', 'เกิดข้อผิดพลาดในการบันทึกข้อมูล: ' . $e->getMessage());
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category_id');
        $locationId = $request->input('location_id');
        $departmentId = $request->input('department_id');
        $status = $request->input('status');

        $assets = Asset::with(['category', 'department', 'location'])
            ->when($search, function ($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('asset_code', 'like', "%{$search}%")
                      ->orWhere('name', 'like', "%{$search}%")
                      ->orWhere('model', 'like', "%{$search}%")
                      ->orWhereHas('category', function($cq) use ($search) {
                          $cq->where('name', 'like', "%{$search}%");
                      });
                });
            })
            ->when($categoryId, function ($query) use ($categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($locationId, function ($query) use ($locationId) {
                $query->where('location_id', $locationId);
            })
            ->when($departmentId, function ($query) use ($departmentId) {
                $query->where('department_id', $departmentId);
            })
            ->when($status, function ($query) use ($status) {
                $query->where('status', $status);
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        $categories = Category::query()->where('type', 'asset')->orderBy('name', 'asc')->get();
        $locations = Location::query()->orderBy('name', 'asc')->get();
        $departments = Department::query()->orderBy('name', 'asc')->get();

        return view('assets.index', compact('assets', 'categories', 'locations', 'departments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = Category::query()->where('type', 'asset')->get();
        $locations = Location::query()->get();
        $departments = Department::query()->get();
        $vendors = Vendor::query()->get();

        return view('assets.create', compact('categories', 'locations', 'departments', 'vendors'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAssetRequest $request)
    {
        $validated = $request->validated();

        if (empty($validated['location_id']) && !empty($validated['location_name'])) {
            $loc = Location::firstOrCreate(['name' => trim($validated['location_name'])]);
            $validated['location_id'] = $loc->id;
        }
        unset($validated['location_name']);

        if (empty($validated['department_id']) && !empty($validated['department_name'])) {
            $dept = Department::firstOrCreate(['name' => trim($validated['department_name'])]);
            $validated['department_id'] = $dept->id;
        }
        unset($validated['department_name']);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('assets', 'public');
        }

        Asset::create($validated);

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
        $assets = Asset::query()->get();
        return view('assets.batch-print', compact('assets'));
    }

    public function printLabels(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'กรุณาเลือกรายการที่ต้องการพิมพ์');
        }

        $labels = Asset::query()->whereIn('id', $ids, 'and', false)->get()->map(function($asset) {
            $asset->qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::size(100)
                ->format('svg')
                ->generate(route('assets.show', $asset->id));
            return $asset;
        });

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
        $categories = Category::query()->where('type', 'asset')->get();
        $locations = Location::query()->get();
        $departments = Department::query()->get();
        $vendors = Vendor::query()->get();

        return view('assets.edit', compact('asset', 'categories', 'locations', 'departments', 'vendors'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAssetRequest $request, Asset $asset)
    {
        $validated = $request->validated();

        if (empty($validated['location_id'])) {
            if (!empty($validated['location_name'])) {
                $loc = Location::firstOrCreate(['name' => trim($validated['location_name'])]);
                $validated['location_id'] = $loc->id;
            } else {
                $validated['location_id'] = null;
            }
        }
        unset($validated['location_name']);

        if (empty($validated['department_id'])) {
            if (!empty($validated['department_name'])) {
                $dept = Department::firstOrCreate(['name' => trim($validated['department_name'])]);
                $validated['department_id'] = $dept->id;
            } else {
                $validated['department_id'] = null;
            }
        }
        unset($validated['department_name']);

        if ($request->hasFile('image')) {
            if ($asset->image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($asset->image);
            }
            $validated['image'] = $request->file('image')->store('assets', 'public');
        }

        $asset->update($validated);

        return redirect()->route('assets.show', $asset->id)->with('success', 'แก้ไขข้อมูลครุภัณฑ์เรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Asset $asset)
    {
        Asset::destroy($asset->id);
        return redirect()->route('assets.index')->with('success', 'ลบข้อมูลครุภัณฑ์เรียบร้อยแล้ว');
    }
    /**
    * Delete all assets (soft delete) after confirming code "00000".
    */
    public function bulkDelete(Request $request)
    {
        $request->validate([
            'confirm_code' => 'required|string',
        ]);

        if ($request->input('confirm_code') !== '00000') {
            return redirect()->route('assets.index')
                ->with('error', 'รหัสยืนยันไม่ถูกต้อง');
        }

        // Soft delete all assets
        Asset::query()->delete();

        return redirect()->route('assets.index')
            ->with('success', 'ลบรายการครุภัณฑ์ทั้งหมดเรียบร้อย');
    }
    public function updateStatus(Request $request, Asset $asset)
    {
        $validated = $request->validate([
            'status' => 'required|string',
        ]);

        $asset->update([
            'status' => $validated['status']
        ]);

        return back()->with('success', 'อัปเดตสถานะครุภัณฑ์เรียบร้อยแล้ว');
    }
}
