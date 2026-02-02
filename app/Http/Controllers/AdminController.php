<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Position;
use App\Models\Shift; // <--- JANGAN LUPA IMPORT INI
use DB;
use Hash;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;

class AdminController extends Controller
{
    public function index()
    {
        $today = Carbon::today()->format('Y-m-d');

        $stats = [
            'total' => User::where('role', 'employee')->count(),
            'hadir' => Attendance::whereDate('date', $today)->where('status', 'hadir')->count(),
            'terlambat' => Attendance::whereDate('date', $today)->where('status', 'terlambat')->count(),
            // Izin logic bisa disesuaikan nanti
            'izin' => Attendance::whereDate('date', $today)->where('status', 'izin')->count(),
        ];

        // Eager load profile agar nama muncul
        $recentAttendances = Attendance::with('user.profile')
            ->whereDate('date', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAttendances'));
    }

    // --- BAGIAN KARYAWAN (EMPLOYEE) ---

    public function employeeIndex()
    {
        // Load juga relasi 'shift'
        $employees = User::where('role', 'employee')
            ->with('profile.office', 'profile.position', 'profile.shift')
            ->get();
        return view('admin.employees.index', compact('employees'));
    }

    public function employeeCreate()
    {
        $offices = Office::all();
        $positions = Position::all();
        $shifts = Shift::all(); // <--- Ambil data Shift
        return view('admin.employees.create', compact('offices', 'positions', 'shifts'));
    }

    public function employeeStore(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nik' => 'required|unique:employee_profiles,nik',
            'office_id' => 'required',
            'position_id' => 'required',
            'shift_id' => 'required', // <--- Validasi Shift wajib dipilih
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employee',
            ]);

            $user->profile()->create([
                'nik' => $request->nik,
                'full_name' => $request->full_name,
                'office_id' => $request->office_id,
                'position_id' => $request->position_id,
                'shift_id' => $request->shift_id, // <--- Simpan Shift ID
                'phone' => $request->phone ?? '-',
                'join_date' => $request->join_date ?? now(),
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    public function employeeDestroy($id)
    {
        $user = User::findOrFail($id);
        $user->delete();
        return redirect()->back()->with('success', 'Karyawan berhasil dihapus');
    }

    // --- BAGIAN KANTOR (OFFICE) ---
    // (Tidak ada perubahan signifikan di sini, kecuali jika Anda ingin menghapus start_time/end_time dari office karena sudah pindah ke Shift)
    public function officeIndex()
    {
        $offices = Office::all();
        return view('admin.offices.index', compact('offices'));
    }

    public function officeStore(Request $request)
    {
        // Validasi disesuaikan (Start time & End time opsional jika pakai Shift, tapi biarkan saja jika ingin default)
        $request->validate([
            'office_name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric',
        ]);

        Office::create($request->all());
        return redirect()->back()->with('success', 'Kantor berhasil ditambahkan');
    }

    public function officeDestroy($id)
    {
        Office::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Kantor dihapus');
    }

    // --- BAGIAN JABATAN (POSITION) ---
    public function positionIndex()
    {
        $positions = Position::all();
        return view('admin.positions.index', compact('positions'));
    }

    public function positionStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'base_salary' => 'required|numeric',
            'overtime_rate' => 'required|numeric',
        ]);

        Position::create($request->all());
        return redirect()->back()->with('success', 'Jabatan berhasil ditambahkan');
    }

    public function positionDestroy($id)
    {
        Position::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Jabatan dihapus');
    }

    // --- BAGIAN SHIFT (BARU) ---
    public function shiftIndex()
    {
        $shifts = Shift::all();
        return view('admin.shifts.index', compact('shifts'));
    }

    public function shiftStore(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        Shift::create($request->all());
        return redirect()->back()->with('success', 'Shift berhasil ditambahkan');
    }

    public function shiftDestroy($id)
    {
        Shift::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Shift dihapus');
    }
}