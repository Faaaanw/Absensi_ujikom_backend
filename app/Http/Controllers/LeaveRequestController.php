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
    public function allHistory(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            return redirect()->back();
        }

        $query = LeaveRequest::with('user.profile');

        // --- LOGIKA FILTER TANGGAL ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            // Memfilter data dimana tanggal mulai izin berada dalam rentang yang dipilih
            $query->whereBetween('start_date', [$request->start_date, $request->end_date]);
        }
        // -----------------------------

        $leaves = $query->orderBy('created_at', 'desc')->get();

        // Pastikan return ke view
        return view('admin.leaves.index', compact('leaves'));
    }

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

        // LOGIKA PERBAIKAN: Jangan timpa jam masuk jika sudah ada
        if ($request->status === 'approved') {
            $period = CarbonPeriod::create($leave->start_date, $leave->end_date);

            foreach ($period as $date) {
                // Cek apakah data absensi hari itu sudah ada?
                $attendance = Attendance::where('user_id', $leave->user_id)
                    ->where('date', $date->format('Y-m-d'))
                    ->first();

                if ($attendance) {
                    if (!$attendance->clock_in) {
                        // PERBAIKAN: Gunakan $leave->type agar statusnya sesuai (sakit/izin/cuti)
                        $attendance->update(['status' => $leave->type]);
                    }
                } else {
                    Attendance::create([
                        'user_id' => $leave->user_id,
                        'date' => $date->format('Y-m-d'),
                        // PERBAIKAN: Gunakan $leave->type
                        'status' => $leave->type,
                        'clock_in' => null,
                        'clock_out' => null,
                    ]);
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Status berhasil diupdate ke " . $request->status,
            'data' => $leave
        ]);
    }

}