<?php

namespace App\Http\Controllers;

use App\Exports\AttendanceReportExport;
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
use Maatwebsite\Excel\Facades\Excel;

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
        // 1. VALIDASI (Hapus validasi 'nik' karena sekarang otomatis)
        $request->validate([
            'full_name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            // 'nik' => 'required...', // BARIS INI DIHAPUS
            'office_id' => 'required',
            'position_id' => 'required',
            'shift_id' => 'required',
        ]);

        // 2. GENERATE NIK OTOMATIS
        // Format: EMP + TahunBulanHari + 4 Angka Acak (Contoh: EMP202402038821)
        $generatedNik = 'EMP' . date('Ymd') . rand(1000, 9999);

        // Cek Database: Pastikan NIK ini belum dipakai orang lain (Looping sampai dapat yang unik)
        while (\App\Models\EmployeeProfile::where('nik', $generatedNik)->exists()) {
            $generatedNik = 'EMP' . date('Ymd') . rand(1000, 9999);
        }

        // 3. SIMPAN DATA
        // Kita butuh 'use ($request, $generatedNik)' agar variabel bisa masuk ke dalam function transaction
        DB::transaction(function () use ($request, $generatedNik) {
            $user = User::create([
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employee',
            ]);

            $user->profile()->create([
                'nik' => $generatedNik, // <--- MASUKKAN NIK OTOMATIS DI SINI
                'full_name' => $request->full_name,
                'office_id' => $request->office_id,
                'position_id' => $request->position_id,
                'shift_id' => $request->shift_id,
                'phone' => $request->phone ?? '-',
                'join_date' => $request->join_date ?? now(),
            ]);
        });

        return redirect()->route('admin.employees.index')
            ->with('success', 'Karyawan berhasil ditambahkan dengan NIK: ' . $generatedNik);
    }

    public function employeeEdit($id)
    {
        $user = User::with('profile')->findOrFail($id);
        $offices = Office::all();
        $positions = Position::all();
        $shifts = Shift::all();

        return view('admin.employees.edit', compact('user', 'offices', 'positions', 'shifts'));
    }

    // 2. PROSES UPDATE DATA
    public function employeeUpdate(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'full_name' => 'required',
            // Validasi email unik kecuali untuk user ini sendiri
            'email' => 'required|email|unique:users,email,' . $user->id,
            // Password boleh kosong jika tidak ingin diganti
            'password' => 'nullable|min:6',
            'office_id' => 'required',
            'position_id' => 'required',
            'shift_id' => 'required',
        ]);

        DB::transaction(function () use ($request, $user) {
            // Update User Login (Email & Password)
            $userData = ['email' => $request->email];

            // Jika password diisi, maka update password. Jika kosong, biarkan yang lama.
            if ($request->filled('password')) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Update Profile Karyawan
            // NIK TIDAK DIUPDATE (biasanya NIK itu permanen)
            $user->profile()->update([
                'full_name' => $request->full_name,
                'office_id' => $request->office_id,
                'position_id' => $request->position_id,
                'shift_id' => $request->shift_id,
                'phone' => $request->phone ?? $user->profile->phone,
                'join_date' => $request->join_date ?? $user->profile->join_date,
            ]);
        });

        return redirect()->route('admin.employees.index')->with('success', 'Data karyawan berhasil diperbarui');
    }

    // 3. PROSES HAPUS
    public function employeeDestroy($id)
    {
        // Cari user
        $user = User::findOrFail($id);

        // Hapus user (Profile akan otomatis terhapus jika di migrasi pakai onDelete('cascade'))
        // Jika tidak cascade, hapus manual: $user->profile()->delete();
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

    public function officeUpdate(Request $request, $id)
    {
        $request->validate([
            'office_name' => 'required',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric',
        ]);

        $office = Office::findOrFail($id);

        $office->update([
            'office_name' => $request->office_name,
            'latitude' => $request->latitude,
            'longitude' => $request->longitude,
            'radius' => $request->radius,
        ]);

        return redirect()->back()->with('success', 'Data kantor berhasil diperbarui');
    }

    // Method DESTROY (Hapus)
    public function officeDestroy($id)
    {
        $office = Office::findOrFail($id);

        // Opsional: Cek apakah kantor masih dipakai oleh karyawan
        // if($office->employees()->count() > 0) {
        //    return redirect()->back()->with('error', 'Gagal hapus. Masih ada karyawan di kantor ini.');
        // }

        $office->delete();
        return redirect()->back()->with('success', 'Kantor berhasil dihapus');
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

    public function positionUpdate(Request $request, $id)
    {
        // 1. Validasi Input (Sama seperti create)
        $request->validate([
            'name' => 'required|string|max:255',
            'base_salary' => 'required|numeric|min:0',
            'overtime_rate' => 'required|numeric|min:0',
            'daily_transport_allowance' => 'required|numeric|min:0',
            'late_fee_per_incident' => 'required|numeric|min:0',
        ]);

        // 2. Cari data dan update
        $position = Position::findOrFail($id);

        $position->update([
            'name' => $request->name,
            'base_salary' => $request->base_salary,
            'overtime_rate' => $request->overtime_rate,
            'daily_transport_allowance' => $request->daily_transport_allowance,
            'late_fee_per_incident' => $request->late_fee_per_incident,
        ]);

        return redirect()->back()->with('success', 'Jabatan berhasil diperbarui');
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
    public function leavesIndex()
    {
        // Ambil data izin, urutkan dari yang terbaru
        $leaves = \App\Models\LeaveRequest::with('user.profile')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.leaves.index', compact('leaves'));
    }

    public function leavesUpdate(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'status' => 'required|in:approved,rejected',
            'rejection_reason' => 'required_if:status,rejected' // Wajib isi alasan jika ditolak
        ]);

        $leave = \App\Models\LeaveRequest::findOrFail($id);

        // Update status
        $leave->update([
            'status' => $request->status,
            'rejection_reason' => $request->rejection_reason
        ]);

        // LOGIKA KHUSUS: Jika disetujui, otomatis isi tabel Absensi (Attendance)
        if ($request->status === 'approved') {
            $period = \Carbon\CarbonPeriod::create($leave->start_date, $leave->end_date);

            foreach ($period as $date) {
                Attendance::updateOrCreate(
                    [
                        'user_id' => $leave->user_id,
                        'date' => $date->format('Y-m-d'),
                    ],
                    [
                        'status' => 'izin', // Set status harian jadi 'izin'
                        'clock_in' => null,  // Tidak perlu jam masuk
                        'clock_out' => null, // Tidak perlu jam pulang
                    ]
                );
            }
        }

        return redirect()->back()->with('success', 'Status pengajuan izin berhasil diperbarui.');
    }

    // ==========================================
    // BAGIAN PENGATURAN LEMBUR (OVERTIME)
    // ==========================================

    public function overtimeIndex()
    {
        $overtimes = \App\Models\OvertimeSubmission::with('user.profile')
            ->orderBy('date', 'desc')
            ->get();

        return view('admin.overtime.index', compact('overtimes'));
    }

    public function overtimeUpdate(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:approved,rejected',
        ]);

        $overtime = \App\Models\OvertimeSubmission::findOrFail($id);

        $overtime->update([
            'status' => $request->status
        ]);

        return redirect()->back()->with('success', 'Status lembur berhasil diperbarui.');
    }
    public function dailyReport(Request $request)
    {
        // 1. Ambil Filter dari Request (Default: Hari ini)
        $startDate = $request->input('start_date', Carbon::today()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::today()->format('Y-m-d'));
        $officeId = $request->input('office_id');

        // 2. CEK: Apakah User Klik Tombol "Export Excel"?
        if ($request->input('action') == 'export') {
            $fileName = 'Laporan_Absensi_' . $startDate . '_sd_' . $endDate . '.xlsx';

            // Download Excel
            return Excel::download(
                new AttendanceReportExport($startDate, $endDate, $officeId),
                $fileName
            );
        }

        // 3. Jika Tidak Export, Tampilkan Data di Website (Filter Biasa)
        $query = User::query();

        // Filter user berdasarkan kantor (jika dipilih)
        if ($officeId) {
            $query->whereHas('profile', function ($q) use ($officeId) {
                $q->where('office_id', $officeId);
            });
        }

        $employees = $query->with([
            'profile.office',
            'profile.shift',
            'attendances' => function ($q) use ($startDate, $endDate) {
                $q->whereBetween('date', [$startDate, $endDate]);
            }
        ])->get();

        $offices = Office::all(); // Sesuaikan nama model Office kamu

        return view('admin.reports.daily', compact('employees', 'offices', 'startDate', 'endDate', 'officeId'));
    }
}