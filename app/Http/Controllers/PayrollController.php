<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\OvertimeSubmission;
use App\Models\Bonus;
use App\Models\Payrolls;
use App\Models\User;
use App\Models\Attendance; // Jangan lupa import ini
use Carbon\Carbon; // Import Carbon
use Illuminate\Http\Request;

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
        $user = User::with(['profile.position', 'profile.shift'])->findOrFail($request->user_id);
        $position = $user->profile->position;

        if (!$position) {
            return response()->json(['message' => 'Posisi/Jabatan belum diatur.'], 400);
        }

        // =========================================================================
        // 2. LOGIKA BARU: Hitung Lembur Valid (Cross-Check Absensi)
        // =========================================================================

        // Ambil semua pengajuan lembur yang APPROVED di bulan ini
        $approvedOvertimes = OvertimeSubmission::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->where('status', 'approved')
            ->get();

        $validOvertimeHours = 0;
        $rejectedOvertimeCount = 0; // Untuk tracking berapa yang dibatalkan sistem

        foreach ($approvedOvertimes as $ot) {
            // A. Cari Data Absensi pada tanggal lembur tersebut
            $attendance = Attendance::with('shift') // Load shift dari history absen
                ->where('user_id', $user->id)
                ->whereDate('date', $ot->date)
                ->first();

            // B. VALIDASI 1: Apakah karyawan masuk (Scan Absen)?
            // Jika $attendance null, berarti Alpha.
            if (!$attendance) {
                // SKIP: Tidak dihitung karena Alpha/Tidak Scan
                $rejectedOvertimeCount++;
                continue;
            }

            // C. VALIDASI 2: Apakah statusnya Hadir/Terlambat?
            // Jika statusnya 'sakit', 'izin', atau 'cuti', lembur tidak valid.
            if (!in_array($attendance->status, ['hadir', 'terlambat'])) {
                // SKIP: Tidak dihitung karena status Izin/Sakit/Cuti
                $rejectedOvertimeCount++;
                continue;
            }

            // D. VALIDASI 3: Cek Jam Pulang (Apakah pulang tepat waktu?)
            if ($attendance->clock_out) {
                // Tentukan jam selesai shift. 
                // Prioritas: Ambil dari history attendance (shift_id), jika tidak ada ambil dari profile user saat ini
                $shiftEndTimeStr = $attendance->shift ? $attendance->shift->end_time : $user->profile->shift->end_time;

                // Buat objek Carbon untuk perbandingan waktu
                // Gabungkan tanggal lembur dengan jam shift
                $shiftEndTime = Carbon::parse($ot->date . ' ' . $shiftEndTimeStr);
                $actualClockOut = Carbon::parse($ot->date . ' ' . $attendance->clock_out);

                // Tambahkan toleransi sedikit (misal: 15 menit setelah jam pulang masih dianggap "tepat waktu" alias tidak lembur)
                // Jika jam pulang aktual LEBIH KECIL dari (Jam Shift + 15 menit)
                // Contoh: Shift pulang 17:00. Dia pulang 17:10. Maka Lembur HANGUS.
                // Dia harus pulang misal jam 18:00 agar lemburnya 1 jam valid.

                // Logika: Jika Actual <= Shift End, berarti dia tidak melebihkan waktu.
                if ($actualClockOut->lte($shiftEndTime->addMinutes(15))) { // Toleransi 15 menit
                    // SKIP: Pulang tenggo (on time), padahal mengajukan lembur
                    $rejectedOvertimeCount++;
                    continue;
                }
            } else {
                // Kasus: Lupa Absen Pulang (Clock Out kosong)
                // Kebijakan: Jika tidak absen pulang, lembur tidak bisa diverifikasi -> Tidak Valid
                $rejectedOvertimeCount++;
                continue;
            }

            // Jika lolos semua validasi di atas, tambahkan durasi ke total
            $validOvertimeHours += $ot->duration;
        }

        // Hitung Uang Lembur Berdasarkan Jam Valid
        $overtimePay = $validOvertimeHours * $position->overtime_rate;

        // =========================================================================
        // AKHIR LOGIKA BARU
        // =========================================================================

        // 3. Hitung Bonus
        $bonusPay = Bonus::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->sum('amount');

        // 4. Hitung Kehadiran (Allowance) & Keterlambatan (Deduction)
        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('date', $request->month)
            ->whereYear('date', $request->year)
            ->get();

        $totalPresentDays = $attendances->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalLateIncidents = $attendances->where('status', 'terlambat')->count();

        $transportAllowance = $totalPresentDays * $position->daily_transport_allowance;
        $totalDeductions = $totalLateIncidents * $position->late_fee_per_incident;

        // 5. Hitung Gaji Bersih
        $basicSalary = $position->base_salary;
        $netSalary = ($basicSalary + $overtimePay + $bonusPay + $transportAllowance) - $totalDeductions;

        // 6. Simpan ke Database
        $payroll = Payrolls::updateOrCreate(
            [
                'user_id' => $user->id,
                'month' => $request->month,
                'year' => $request->year,
            ],
            [
                'basic_salary' => $basicSalary,
                'overtime_pay' => $overtimePay, // Nilai hasil validasi ketat
                'bonus_pay' => $bonusPay,
                'deductions' => $totalDeductions,
                'net_salary' => $netSalary,
                'total_attendance' => $totalPresentDays
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Payroll generated successfully.',
            'data' => [
                'payroll' => $payroll,
                'details' => [
                    'hari_kerja' => $totalPresentDays,
                    'lembur_diajukan_jam' => $approvedOvertimes->sum('duration'),
                    'lembur_valid_jam' => $validOvertimeHours, // Info jam valid
                    'lembur_ditolak_sistem' => $rejectedOvertimeCount . ' pengajuan', // Info berapa yg batal
                    'tunjangan_transport' => $transportAllowance,
                    'total_denda' => $totalDeductions
                ]
            ]
        ]);
    }
}