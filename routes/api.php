<?php

use App\Http\Controllers\Api\AttendanceController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ReportController;
use Illuminate\Support\Facades\Route;

// Route Public (Tanpa Token)
Route::post('/login', [AuthController::class, 'login']);

// Route Private (Butuh Token Bearer)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    Route::post('/attendance/generate-token', [AttendanceController::class, 'generateAttendanceToken']);
    Route::post('/attendance/submit-scan', [AttendanceController::class, 'submitScan']);

    Route::post('/leave/store', [\App\Http\Controllers\LeaveRequestController::class, 'store']);
    Route::get('/leave/history', [\App\Http\Controllers\LeaveRequestController::class, 'myHistory']);
    Route::get('/admin/leaves', [\App\Http\Controllers\LeaveRequestController::class, 'allHistory']);
    Route::post('/admin/leave/{id}/status', [\App\Http\Controllers\LeaveRequestController::class, 'approve']);

    // api.php di dalam middleware auth:sanctum
    Route::get('/admin/dashboard-stats', [ReportController::class, 'dashboardStats']);
    Route::get('/admin/attendances', [ReportController::class, 'allAttendances']); // Untuk history semua karyawan
    Route::post('/attendance/submit-scan', [App\Http\Controllers\Api\AttendanceController::class, 'submitScan']);
});