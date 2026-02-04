<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\OvertimeSubmissionController; // <--- Import Controller Lembur
use App\Http\Controllers\ReportController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// ========================================================================
// 1. PUBLIC ROUTES (Tidak butuh Token Login)
// ========================================================================
Route::post('/login', [AuthController::class, 'login']);

// Note: submit-scan biasanya butuh login jika dilakukan dari HP karyawan.
// Jika ini dilakukan oleh "Mesin Scanner" kantor, maka boleh ditaruh di public.
// Namun jika via HP karyawan, sebaiknya pindahkan ke dalam middleware auth.
Route::post('/attendance/submit-scan', [AttendanceController::class, 'submitScan']);


// ========================================================================
// 2. PRIVATE ROUTES (Wajib Login / Header: Authorization Bearer token)
// ========================================================================
Route::middleware('auth:sanctum')->group(function () {

    // --- AUTH ---
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user', [AuthController::class, 'me']);

    // --- ATTENDANCE (ABSENSI) ---
    Route::post('/attendance/generate-token', [AttendanceController::class, 'generateAttendanceToken']);

    // --- LEAVE (CUTI/IZIN) ---
    Route::post('/leave/store', [LeaveRequestController::class, 'store']);
    Route::get('/leave/history', [LeaveRequestController::class, 'myHistory']);

    // --- OVERTIME (LEMBUR) [BARU DITAMBAHKAN] ---
    Route::post('/overtime', [App\Http\Controllers\OvertimeSubmissionController::class, 'store']);
    Route::get('/overtime/my-history', [App\Http\Controllers\OvertimeSubmissionController::class, 'myHistory']);

    // ====================================================================
    // ADMIN ROUTES (Sebaiknya nanti ditambahkan middleware 'role:admin')
    // ====================================================================

    // --- MASTER DATA SHIFT ---
    Route::apiResource('shifts', ShiftController::class);

    // --- ADMIN APPROVAL CUTI ---
    Route::get('/admin/leaves', [LeaveRequestController::class, 'allHistory']);
    Route::post('/admin/leave/{id}/status', [LeaveRequestController::class, 'approve']);

    // --- ADMIN APPROVAL LEMBUR [BARU DITAMBAHKAN] ---
    Route::get('/admin/overtime', [OvertimeSubmissionController::class, 'indexAdmin']);     // Lihat semua request
    Route::put('/admin/overtime/{id}', [OvertimeSubmissionController::class, 'updateStatus']); // Approve/Reject

    // --- ADMIN DASHBOARD & REPORT ---
    Route::get('/admin/dashboard-stats', [ReportController::class, 'dashboardStats']);
    Route::get('/admin/attendances', [ReportController::class, 'allAttendances']);
});