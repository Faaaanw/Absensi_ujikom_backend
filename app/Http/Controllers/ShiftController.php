<?php

namespace App\Http\Controllers;

use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    /**
     * GET /api/shifts
     * Menampilkan semua daftar shift
     */
    public function index()
    {
        $shifts = Shift::all();

        return response()->json([
            'success' => true,
            'data' => $shifts
        ]);
    }

    /**
     * POST /api/shifts
     * Membuat shift baru
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255', // Contoh: "Shift Pagi"
            'start_time' => 'required|date_format:H:i', // Format jam:menit (08:00)
            'end_time' => 'required|date_format:H:i',   // Format jam:menit (17:00)
        ]);

        $shift = Shift::create([
            'name' => $request->name,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil dibuat',
            'data' => $shift
        ], 201);
    }

    /**
     * GET /api/shifts/{id}
     * Detail shift tertentu
     */
    public function show($id)
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift tidak ditemukan'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $shift
        ]);
    }

    /**
     * PUT /api/shifts/{id}
     * Update jam kerja shift
     */
    public function update(Request $request, $id)
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift tidak ditemukan'], 404);
        }

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_time' => 'sometimes|date_format:H:i', // Menggunakan format H:i (e.g. 08:00)
            'end_time' => 'sometimes|date_format:H:i',
        ]);

        $shift->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Shift berhasil diperbarui',
            'data' => $shift
        ]);
    }

    /**
     * DELETE /api/shifts/{id}
     * Hapus shift (Ada validasi relasi agar tidak error)
     */
    public function destroy($id)
    {
        $shift = Shift::find($id);

        if (!$shift) {
            return response()->json(['success' => false, 'message' => 'Shift tidak ditemukan'], 404);
        }

        // --- VALIDASI PENTING ---
        // Jangan hapus shift jika masih ada karyawan yang menggunakannya
        // atau jika ada data absensi yang terikat ke shift ini.
        
        if ($shift->employees()->exists()) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal hapus: Shift ini sedang digunakan oleh karyawan.'
            ], 400);
        }

        if ($shift->attendances()->exists()) {
            return response()->json([
                'success' => false, 
                'message' => 'Gagal hapus: Terdapat riwayat absensi menggunakan shift ini.'
            ], 400);
        }

        $shift->delete();

        return response()->json([
            'success' => true, 
            'message' => 'Shift berhasil dihapus'
        ]);
    }
}