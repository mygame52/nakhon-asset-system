<?php

namespace App\Http\Controllers;

use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Only display main locations (or locations with room_number / ห้องพัสดุ)
        $query = Location::where(function ($q) {
            $q->where('name', 'ห้องพัสดุ')
              ->orWhereNotNull('room_number');
        })->withCount(['assets', 'materials']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('room_number', 'like', "%{$search}%");
            });
        }

        $totalLocations = (clone $query)->count();
        $locations = $query->latest()->paginate(12);

        return view('locations.index', compact('locations', 'totalLocations'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:255',
        ]);

        Location::create($validated);

        return redirect()->route('locations.index')->with('success', 'บันทึกข้อมูลสถานที่จัดเก็บใหม่เรียบร้อยแล้ว');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'room_number' => 'nullable|string|max:255',
        ]);

        $location = Location::findOrFail($id);
        $location->update($validated);

        return redirect()->route('locations.index')->with('success', 'ปรับปรุงข้อมูลสถานที่จัดเก็บเรียบร้อยแล้ว');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $location = Location::findOrFail($id);
        
        if ($location->assets()->count() > 0 || $location->materials()->count() > 0) {
            return redirect()->route('locations.index')->with('error', 'ไม่สามารถลบสถานที่ได้เนื่องจากมีรายการครุภัณฑ์หรือวัสดุจัดเก็บอยู่');
        }

        $location->delete();
        return redirect()->route('locations.index')->with('success', 'ลบสถานที่จัดเก็บเรียบร้อยแล้ว');
    }
}
