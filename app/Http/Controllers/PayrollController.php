<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OvertimeSubmission;
use App\Models\Payroll;
use App\Models\Bonus;
use App\Models\Payrolls;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PayrollController extends Controller
{

    public function generatePayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer',
            'year' => 'required|integer',
            'user_id' => 'required|exists:users,id'
        ]);

        // 1. Ambil Data Karyawan & Jabatan
        $user = User::with('profile.position')->findOrFail($request->user_id);
        $position = $user->profile->position;

        if (!$position) {
            return response()->json(['message' => 'Posisi/Jabatan belum diatur.'], 400);
        }

        // 2. Hitung Lembur (Overtime) - Hanya yang Approved
        $totalOvertimeHours = OvertimeSubmission::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->where('status', 'approved')
            ->sum('duration');

        $overtimePay = $totalOvertimeHours * $position->overtime_rate;

        // 3. Hitung Bonus
        $bonusPay = Bonus::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->sum('amount');

        // 4. Hitung Kehadiran (Allowance) & Keterlambatan (Deduction)
        // Ambil semua data absensi bulan ini
        $attendances = \App\Models\Attendance::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->get();

        // Hitung jumlah hari masuk (Hadir & Terlambat dihitung masuk)
        $totalPresentDays = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();

        // Hitung jumlah kejadian terlambat
        $totalLateIncidents = $attendances->where('status', 'terlambat')->count();

        // KALKULASI TUNJANGAN & POTONGAN
        // Tunjangan Transport = Hari Masuk * Tarif Transport Jabatan
        $transportAllowance = $totalPresentDays * $position->daily_transport_allowance;

        // Potongan = Jumlah Terlambat * Tarif Denda Jabatan
        $totalDeductions = $totalLateIncidents * $position->late_fee_per_incident;

        // 5. Hitung Gaji Bersih (Net Salary)
        $basicSalary = $position->base_salary;

        // Rumus: Gaji Pokok + Lembur + Bonus + Transport - Potongan
        $netSalary = ($basicSalary + $overtimePay + $bonusPay + $transportAllowance) - $totalDeductions;

        // 6. Simpan ke Database
        // Catatan: Jika tabel payrolls belum punya kolom 'allowances', 
        // kamu bisa gabungkan transport ke basic_salary atau buat migration baru.
        // Di sini saya asumsikan basic_salary tetap murni gaji pokok.

        $payroll = Payrolls::updateOrCreate(
            [
                'user_id' => $user->id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
                'basic_salary' => $basicSalary,
                'overtime_pay' => $overtimePay,
                'bonus_pay' => $bonusPay,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'total_attendance' => $totalPresentDays // Menyimpan total hari hadir
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Payroll generated successfully.',
            'data' => [
                'payroll' => $payroll,
                'details' => [
                    'hari_kerja' => $totalPresentDays,
                    'tunjangan_transport' => $transportAllowance,
                    'jumlah_terlambat' => $totalLateIncidents,
                    'total_denda' => $totalDeductions
                ]
            ]
        ]);
    }
}