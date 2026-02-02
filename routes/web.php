<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminAuthController; // Import Controller Login Baru
use Illuminate\Support\Facades\Route;

// --- 1. HALAMAN LOGIN (Public) ---
Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/login', [AdminAuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AdminAuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AdminAuthController::class, 'logout'])->name('logout');


// --- 2. HALAMAN ADMIN (DILINDUNGI) ---
// Middleware 'auth' memastikan hanya user yg sudah login yang bisa akses group ini
Route::middleware(['auth'])->prefix('admin')->group(function () {

    Route::get('/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');

    // Karyawan
    Route::get('/employees', [AdminController::class, 'employeeIndex'])->name('admin.employees.index');
    Route::get('/employees/create', [AdminController::class, 'employeeCreate'])->name('admin.employees.create');
    Route::post('/employees/store', [AdminController::class, 'employeeStore'])->name('admin.employees.store');
    Route::delete('/employees/{id}', [AdminController::class, 'employeeDestroy'])->name('admin.employees.destroy');

    Route::get('/shifts', [AdminController::class, 'shiftIndex'])->name('admin.shifts.index');
    Route::post('/shifts', [AdminController::class, 'shiftStore'])->name('admin.shifts.store');
    Route::delete('/shifts/{id}', [AdminController::class, 'shiftDestroy'])->name('admin.shifts.destroy');


    // Kantor
    Route::get('/offices', [AdminController::class, 'officeIndex'])->name('admin.offices.index');
    Route::post('/offices', [AdminController::class, 'officeStore'])->name('admin.offices.store');
    Route::delete('/offices/{id}', [AdminController::class, 'officeDestroy'])->name('admin.offices.destroy');

    // Jabatan
    Route::get('/positions', [AdminController::class, 'positionIndex'])->name('admin.positions.index');
    Route::post('/positions', [AdminController::class, 'positionStore'])->name('admin.positions.store');
    Route::delete('/positions/{id}', [AdminController::class, 'positionDestroy'])->name('admin.positions.destroy');
});