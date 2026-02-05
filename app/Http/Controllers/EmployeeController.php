<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Attendance;
use App\Models\OvertimeSubmission;
use App\Models\Bonus;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmployeeController extends Controller 
{
    // ... method index, create, store, dll tetap ada ...

    /**
     * AJAX Method: Hitung estimasi gaji bulan ini secara realtime
     */
    public function getSalaryDetail($id)
    {
        $user = User::with(['profile.position', 'profile.shift'])->findOrFail($id);
        
        // Ambil bulan & tahun saat ini
        $now = Carbon::now();
        $month = $now->month;
        $year = $now->year;

        $position = $user->profile->position;

        if (!$position) {
            return response()->json(['error' => 'Jabatan/Posisi belum diatur untuk karyawan ini.'], 404);
        }

        // ==========================================
        // 1. HITUNG KEHADIRAN & DENDA
        // ==========================================
        $attendances = Attendance::where('user_id', $id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->get();

        $totalPresent = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalLate = $attendances->where('status', 'terlambat')->count(); // Hitung berapa kali telat

        // Uang Transport (Hadir x Tunjangan Harian)
        $transportAllowance = $totalPresent * $position->daily_transport_allowance;
        
        // Denda Keterlambatan (Total Telat x Denda per kejadian)
        $lateDeduction = $totalLate * $position->late_fee_per_incident;


        // ==========================================
        // 2. HITUNG LEMBUR (VALIDASI KETAT)
        // ==========================================
        $approvedOvertimes = OvertimeSubmission::where('user_id', $id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->where('status', 'approved')
            ->get();

        $validOvertimeHours = 0;
        
        foreach ($approvedOvertimes as $ot) {
            // Cek Absensi di hari lembur
            $attendance = $attendances->where('date', $ot->date)->first();

            // Syarat 1: Harus Hadir/Telat (bukan alpha/cuti)
            if (!$attendance || !in_array($attendance->status, ['hadir', 'terlambat'])) {
                continue; 
            }

            // Syarat 2: Harus Scan Pulang
            if (!$attendance->clock_out) {
                continue;
            }

            // Syarat 3: Cek Jam Pulang vs Jam Shift
            // Prioritas shift history, fallback ke profile
            $shiftEndTimeStr = $attendance->shift ? $attendance->shift->end_time : $user->profile->shift->end_time;
            
            $shiftEndTime = Carbon::parse($ot->date . ' ' . $shiftEndTimeStr);
            $actualClockOut = Carbon::parse($ot->date . ' ' . $attendance->clock_out);

            // Jika pulang <= (Jam Shift + 15 menit), lembur hangus
            if ($actualClockOut->lte($shiftEndTime->addMinutes(15))) {
                continue;
            }

            $validOvertimeHours += $ot->duration;
        }

        $overtimePay = $validOvertimeHours * $position->overtime_rate;


        // ==========================================
        // 3. HITUNG BONUS
        // ==========================================
        $bonusPay = Bonus::where('user_id', $id)
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->sum('amount');


        // ==========================================
        // 4. TOTAL GAJI BERSIH (ESTIMASI)
        // ==========================================
        $basicSalary = $position->base_salary;
        $totalIncome = $basicSalary + $overtimePay + $bonusPay + $transportAllowance;
        $totalDeduction = $lateDeduction; // Bisa ditambah potongan lain jika ada
        $netSalary = $totalIncome - $totalDeduction;

        // Return Data JSON untuk Modal
        return response()->json([
            'month_name' => $now->translatedFormat('F Y'),
            'user' => $user->profile->full_name,
            'nik' => $user->profile->nik,
            'details' => [
                'gaji_pokok' => $basicSalary,
                'tunjangan_transport' => [
                    'count' => $totalPresent, // Berapa hari masuk
                    'total' => $transportAllowance
                ],
                'lembur' => [
                    'hours' => $validOvertimeHours,
                    'rate' => $position->overtime_rate,
                    'total' => $overtimePay
                ],
                'bonus' => $bonusPay,
                'denda_terlambat' => [
                    'count' => $totalLate, // Berapa kali telat
                    'total' => $lateDeduction
                ]
            ],
            'final_total' => $netSalary
        ]);
    }
}