<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\Auth\LoginController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Authentication
Route::get('login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('login', [LoginController::class, 'login']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Main Application
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('assets/batch-print', [AssetController::class, 'batchPrint'])->name('assets.batch-print');
    Route::post('assets/print-labels', [AssetController::class, 'printLabels'])->name('assets.print-labels');
    Route::get('assets/{asset}/print', [AssetController::class, 'printLabel'])->name('assets.print');
    Route::resource('assets', AssetController::class);
    Route::resource('materials', MaterialController::class);
    Route::resource('categories', CategoryController::class);
    Route::resource('locations', LocationController::class);
    Route::resource('departments', DepartmentController::class);
    Route::resource('vendors', VendorController::class);
    // ... อื่นๆ
});

// ชั่วคราวเพื่อให้เข้าถึงได้ง่ายในช่วงเริ่มพัฒนา (โดยไม่ต้องมีตัว Auth กั้นทั้งหมด)
Route::resource('assets', AssetController::class);
Route::resource('materials', MaterialController::class);
Route::resource('categories', CategoryController::class);
Route::resource('locations', LocationController::class);
Route::resource('departments', DepartmentController::class);
Route::resource('vendors', VendorController::class);
