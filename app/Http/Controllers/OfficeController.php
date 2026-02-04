<?php

namespace App\Http\Controllers;

use App\Models\Position;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    // Menampilkan daftar jabatan
    public function index()
    {
        $positions = Position::all();
        // Pastikan view-nya sesuai dengan folder kamu
        return view('admin.positions.index', compact('positions'));
    }

    // Menyimpan Jabatan Baru
    public function store(Request $request)
    {
        // 1. Tambahkan validasi untuk kolom baru
        $request->validate([
            'name' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'overtime_rate' => 'required|numeric|min:0',
            'daily_transport_allowance' => 'required|numeric|min:0', // <--- BARU
            'late_fee_per_incident' => 'required|numeric|min:0',     // <--- BARU
        ]);

        // 2. Simpan semua data (karena di Model $fillable sudah ditambahkan, bisa pakai all())
        Position::create($request->all());

        return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan');
    }

    // Update Jabatan
    public function update(Request $request, $id)
    {
        $position = Position::findOrFail($id);

        // 1. Validasi update juga harus menyertakan kolom baru
        $request->validate([
            'name' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'overtime_rate' => 'required|numeric|min:0',
            'daily_transport_allowance' => 'required|numeric|min:0', // <--- BARU
            'late_fee_per_incident' => 'required|numeric|min:0',     // <--- BARU
        ]);

        // 2. Update data
        $position->update($request->all());

        return redirect()->back()->with('success', 'Jabatan berhasil diperbarui');
    }

    public function destroy($id)
    {
        $position = Position::findOrFail($id);
        $position->delete();

        return redirect()->back()->with('success', 'Jabatan berhasil dihapus');
    }
}