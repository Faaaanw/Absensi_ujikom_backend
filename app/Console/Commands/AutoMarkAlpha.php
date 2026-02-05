<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Attendance;
use App\Models\LeaveRequest;
use Carbon\Carbon;

class AutoMarkAlpha extends Command
{
    protected $signature = 'attendance:mark-alpha-realtime';
    protected $description = 'Cek Absensi: Set Alpha untuk hari ini (jika telat) DAN hari-hari sebelumnya (jika server sempat mati)';

    public function handle()
    {
        $this->info("Memulai proses pengecekan Alpha...");

        // Ambil user aktif beserta Shift-nya
        $users = User::with('profile.shift')
            ->where('role', '!=', 'admin')
            ->get();

        $countRealtime = 0;
        $countBackfill = 0;
        $today = Carbon::today(); // Hari ini jam 00:00:00
        $now = Carbon::now();     // Waktu sekarang lengkap jam menit

        foreach ($users as $user) {
            // Validasi data profil & shift
            if (!$user->profile || !$user->profile->shift) {
                continue;
            }

            $shift = $user->profile->shift;

            // ==========================================================
            // BAGIAN 1: BACKFILL (Cek Kemarin & Hari Sebelumnya)
            // ==========================================================
            // Kita cek 3 hari ke belakang untuk berjaga-jaga jika server mati saat weekend/libur panjang
            // Loop dari 1 hari yang lalu sampai 3 hari yang lalu

            for ($i = 1; $i <= 3; $i++) {
                $checkDate = Carbon::today()->subDays($i);

                // 1.A. Skip jika hari tersebut adalah Weekend (Sabtu/Minggu)
                if ($checkDate->isWeekend()) {
                    continue;
                }

                // 1.B. Cek apakah SUDAH ADA data absensi di tanggal tersebut?
                $attendancePastExists = Attendance::where('user_id', $user->id)
                    ->whereDate('date', $checkDate)
                    ->exists();

                // Jika sudah ada record (hadir/sakit/alpha/izin), skip.
                if ($attendancePastExists) {
                    continue;
                }

                // 1.C. Cek apakah saat itu sedang CUTI Approved?
                $isOnLeavePast = $this->checkLeaveStatus($user->id, $checkDate);
                if ($isOnLeavePast) {
                    continue;
                }

                // 1.D. EKSEKUSI: Buat status Alpha untuk MASA LALU
                $this->createAlpha($user->id, $shift->id, $checkDate);
                $countBackfill++;
                $this->info("BACKFILL: User {$user->name} tanggal {$checkDate->format('Y-m-d')} ditandai Alpha.");
            }

            // ==========================================================
            // BAGIAN 2: REALTIME (Cek Hari Ini)
            // ==========================================================

            // 2.A. Skip jika hari ini Weekend
            if ($today->isWeekend()) {
                continue;
            }

            // 2.B. Tentukan Batas Alpha Hari Ini
            $jamMasuk = Carbon::parse($shift->start_time);
            $jamMasuk->setDate($today->year, $today->month, $today->day);
            $batasAlpha = $jamMasuk->copy()->addHours(2);

            // 2.C. Cek Waktu: Hanya proses jika sekarang > batas alpha
            if ($now->greaterThan($batasAlpha)) {

                // Cek absensi hari ini
                $attendanceTodayExists = Attendance::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->exists();

                if ($attendanceTodayExists) {
                    continue;
                }

                // Cek Cuti hari ini
                if ($this->checkLeaveStatus($user->id, $today)) {
                    continue;
                }

                // EKSEKUSI: Buat status Alpha untuk HARI INI
                $this->createAlpha($user->id, $shift->id, $today);
                $countRealtime++;
                $this->info("REALTIME: User {$user->name} terlambat > 2 jam hari ini. Status set ke Alpha.");
            }
        }

        $this->info("Selesai. Backfill: {$countBackfill}, Realtime: {$countRealtime}.");
    }

    /**
     * Helper untuk cek Cuti
     */
    private function checkLeaveStatus($userId, $date)
    {
        return LeaveRequest::where('user_id', $userId)
            ->where('status', 'approved')
            ->whereDate('start_date', '<=', $date)
            ->whereDate('end_date', '>=', $date)
            ->exists();
    }

    /**
     * Helper untuk Create Alpha
     */
    private function createAlpha($userId, $shiftId, $date)
    {
        Attendance::create([
            'user_id' => $userId,
            'shift_id' => $shiftId,
            'date' => $date, // Tanggal sesuai parameter (bisa hari ini atau kemarin)
            'status' => 'alpha',
            'clock_in' => null,
            'clock_out' => null,
        ]);
    }
}