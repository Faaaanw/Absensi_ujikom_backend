<?php

use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;



Route::get('/', function () {
    return view('welcome');
});
Route::get('/admin/dashboard', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/admin/employees', [AdminController::class, 'employeeIndex'])->name('admin.employees.index');
Route::get('/admin/employees/create', [AdminController::class, 'employeeCreate'])->name('admin.employees.create');
Route::post('/admin/employees/store', [AdminController::class, 'employeeStore'])->name('admin.employees.store');
Route::delete('/admin/employees/{id}', [AdminController::class, 'employeeDestroy'])->name('admin.employees.destroy');
// Kantor
Route::get('/admin/offices', [AdminController::class, 'officeIndex'])->name('admin.offices.index');
Route::post('/admin/offices', [AdminController::class, 'officeStore'])->name('admin.offices.store');
Route::delete('/admin/offices/{id}', [AdminController::class, 'officeDestroy'])->name('admin.offices.destroy');

// Jabatan
Route::get('/admin/positions', [AdminController::class, 'positionIndex'])->name('admin.positions.index');
Route::post('/admin/positions', [AdminController::class, 'positionStore'])->name('admin.positions.store');
Route::delete('/admin/positions/{id}', [AdminController::class, 'positionDestroy'])->name('admin.positions.destroy');