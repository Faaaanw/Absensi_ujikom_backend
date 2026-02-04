<?php

namespace App\Http\Controllers;

use App\Models\OvertimeSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OvertimeSubmissionController extends Controller
{
    // ==========================================
    // BAGIAN KARYAWAN (EMPLOYEE)
    // ==========================================

    /**
     * POST /api/overtime
     * Karyawan mengajukan lembur
     * 
     */

    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'duration' => 'required|integer|min:1', // Minimal 1 jam
            // Tambahkan 'reason' jika Anda menambah kolom keterangan di database
        ]);

        $user = Auth::user();

        // Cek duplikasi: Apakah sudah ada pengajuan di tanggal yang sama?
        $exists = OvertimeSubmission::where('user_id', $user->id)
            ->whereDate('date', $request->date)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'Anda sudah memiliki pengajuan lembur pada tanggal tersebut.'
            ], 400);
        }

        $submission = OvertimeSubmission::create([
            'user_id' => $user->id,
            'date' => $request->date,
            'duration' => $request->duration,
            'status' => 'pending', // Default pending
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pengajuan lembur berhasil dikirim. Menunggu persetujuan.',
            'data' => $submission
        ], 201);
    }

    /**
     * GET /api/overtime/my-history
     * Karyawan melihat riwayat pengajuan mereka sendiri
     */
    public function myHistory()
    {
        $user = Auth::user();

        $history = OvertimeSubmission::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $history
        ]);
    }

    // ==========================================
    // BAGIAN ADMIN (HRD/MANAGER)
    // ==========================================

    /**
     * GET /api/admin/overtime
     * Admin melihat semua pengajuan (bisa difilter status)
     */
    public function indexAdmin(Request $request)
    {
        if (Auth::user()->role !== 'admin') {
            // Sesuaikan return ini jika Anda menggunakan Blade, ganti json ke view()
            return redirect()->back()->with('error', 'Unauthorized');
        }

        $query = OvertimeSubmission::with('user.profile');

        // --- LOGIKA FILTER TANGGAL ---
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('date', [$request->start_date, $request->end_date]);
        }
        // -----------------------------

        // Filter status (jika ada)
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }

        $overtimes = $query->orderBy('date', 'desc')->get();

        // Pastikan return ke view, bukan JSON, agar Blade bisa membacanya
        return view('admin.overtime.index', compact('overtimes'));
    }

    /**
     * PUT /api/admin/overtime/{id}
     * Admin menyetujui atau menolak lembur
     */
    public function updateStatus(Request $request, $id)
    {
        // Cek otoritas admin
        if (Auth::user()->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'status' => 'required|in:approved,rejected', // Hanya boleh 2 status ini
        ]);

        $submission = OvertimeSubmission::find($id);

        if (!$submission) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $submission->update([
            'status' => $request->status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Status lembur berhasil diperbarui menjadi: ' . $request->status,
            'data' => $submission
        ]);
    }
}