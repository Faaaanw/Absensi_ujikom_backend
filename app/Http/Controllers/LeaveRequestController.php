<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
class LeaveRequestController extends Controller
{
    /**
     * Submit Pengajuan (Sisi Karyawan)
     */
    public function store(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'type' => 'required|in:sakit,izin,cuti',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
            'attachment' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Max 2MB
        ]);

        $userId = Auth::id();

        // 2. Handle Upload File
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            // Simpan di folder public/leaves
            $attachmentPath = $request->file('attachment')->store('leaves', 'public');
        }

        // 3. Simpan ke Database
        $leave = LeaveRequest::create([
            'user_id' => $userId,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'reason' => $request->reason,
            'attachment' => $attachmentPath,
            'status' => 'pending',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan berhasil dikirim dan menunggu approval.',
            'data' => $leave
        ], 201);
    }

    /**
     * List Riwayat Pengajuan Saya (Sisi Karyawan)
     */
    public function myHistory()
    {
        $leaves = LeaveRequest::where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }

    /**
     * Update Status Izin (Sisi Admin - Dashboard)
     */
   public function allHistory()
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $leaves = LeaveRequest::with('user.profile')->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $leaves
        ]);
    }

    /**
     * Sisi Admin: Approve/Reject
     * Sesuai rute: /admin/leave/{id}/status
     */
    public function approve(Request $request, $id)
    {
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected'
        ]);

        $leave = LeaveRequest::findOrFail($id);
        $leave->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason
        ]);

        // LOGIKA PENTING: Jika Approved, otomatis isi tabel Attendance
        if ($request->status === 'approved') {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);

            foreach ($period as $date) {
                Attendance::updateOrCreate(
                    [
                        'user_id' => $leave->user_id,
                        'date'    => $date->format('Y-m-d'),
                    ],
                    [
                        'status'  => 'izin', // Menandai hari ini sebagai izin (bukan Alpha)
                        'clock_in' => null,
                        'clock_out' => null,
                    ]
                );
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Status berhasil diupdate ke " . $request->status,
            'data' => $leave
        ]);
    }
}