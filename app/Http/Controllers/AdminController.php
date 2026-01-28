<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Office;
use App\Models\Position;
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
            'izin' => Attendance::whereDate('date', $today)->where('status', 'izin')->count(),
        ];

        $recentAttendances = Attendance::with('user.profile')
            ->whereDate('date', $today)
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.dashboard', compact('stats', 'recentAttendances'));
    }
    // Kelola Kantor (Geofencing)
    public function getOffices()
    {
        return response()->json(['data' => Office::all()]);
    }

    public function updateOffice(Request $request, $id)
    {
        $office = Office::findOrFail($id);
        $office->update($request->all());
        return response()->json(['message' => 'Lokasi kantor diperbarui']);
    }

    // Kelola Jabatan (Gaji)
    public function getPositions()
    {
        return response()->json(['data' => Position::all()]);
    }
    public function employeeIndex()
    {
        $employees = User::where('role', 'employee')->with('profile.office', 'profile.position')->get();
        return view('admin.employees.index', compact('employees'));
    }

    // Menampilkan Form Tambah
    public function employeeCreate()
    {
        $offices = Office::all();
        $positions = Position::all();
        return view('admin.employees.create', compact('offices', 'positions'));
    }

    // Menyimpan Karyawan Baru
    public function employeeStore(Request $request)
    {
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'nik' => 'required|unique:employee_profiles,nik',
            'office_id' => 'required',
            'position_id' => 'required',
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
                'phone' => $request->phone ?? '-',
                'join_date' => $request->join_date ?? now(),
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Karyawan berhasil ditambahkan');
    }

    // Menghapus Karyawan
    public function employeeDestroy($id)
    {
        $user = User::findOrFail($id);
        // Karena kita pakai cascadeOnDelete di migrasi, profile otomatis terhapus
        $user->delete();

        return redirect()->back()->with('success', 'Karyawan berhasil dihapus');
    }
    public function officeIndex()
    {
        $offices = Office::all();
        return view('admin.offices.index', compact('offices'));
    }

    public function officeStore(Request $request)
    {
        $request->validate([
            'office_name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric',
            'start_time' => 'required',
            'end_time' => 'required',
        ]);

        Office::create($request->all());
        return redirect()->back()->with('success', 'Kantor berhasil ditambahkan');
    }

    public function officeDestroy($id)
    {
        Office::findOrFail($id)->delete();
        return redirect()->back()->with('success', 'Kantor dihapus');
    }

    // === BAGIAN JABATAN (POSITION) ===
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
}