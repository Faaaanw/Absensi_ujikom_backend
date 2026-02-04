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
    protected $description = 'Cek realtime: Set Alpha jika telat > 2 jam dari jam masuk shift';

    public function handle()
    {
        $now = Carbon::now();
        $today = Carbon::today();

        // 1. Skip jika hari libur (Weekend)
        if ($now->isWeekend()) {
            return;
        }

        // 2. Ambil user aktif beserta Shift-nya
        // Pastikan relasi di model User sudah benar: user -> profile -> shift
        $users = User::with('profile.shift')
            ->where('role', '!=', 'admin') // Kecualikan admin
            ->get();

        $count = 0;

        foreach ($users as $user) {
            // Validasi data shift
            if (!$user->profile || !$user->profile->shift) {
                continue;
            }

            $shift = $user->profile->shift;

            // 3. Tentukan Batas Alpha User Ini
            // Contoh: Shift 09:00, maka batasnya 11:00
            $jamMasuk = Carbon::parse($shift->start_time); // Misal 09:00:00

            // Set tanggal jam masuk ke hari ini agar bisa dibandingkan dengan $now
            $jamMasuk->setDate($today->year, $today->month, $today->day);

            $batasAlpha = $jamMasuk->copy()->addHours(2); // Jam 11:00:00

            // 4. LOGIKA UTAMA
            // Hanya proses jika:
            // A. Waktu sekarang SUDAH MELEWATI batas alpha (Sekarang > 11:00)
            if ($now->greaterThan($batasAlpha)) {

                // B. Cek apakah sudah ada data absensi hari ini?
                $attendanceExists = Attendance::where('user_id', $user->id)
                    ->whereDate('date', $today)
                    ->exists();

                // Jika SUDAH ada record (baik itu hadir, terlambat, izin, atau alpha yg dibuat sebelumnya), SKIP.
                if ($attendanceExists) {
                    continue;
                }

                // C. Cek apakah dia sedang CUTI Approved?
                $isOnLeave = LeaveRequest::where('user_id', $user->id)
                    ->where('status', 'approved')
                    ->whereDate('start_date', '<=', $today)
                    ->whereDate('end_date', '>=', $today)
                    ->exists();

                if ($isOnLeave) {
                    continue; // Sedang cuti, jangan di-Alpha
                }

                // D. EKSEKUSI: Buat status Alpha
                Attendance::create([
                    'user_id' => $user->id,
                    'shift_id' => $shift->id,
                    'date' => $today,
                    'status' => 'alpha', // Otomatis Alpha
                    'clock_in' => null,
                    'clock_out' => null,
                ]);

                $count++;
                $this->info("User {$user->name} terlambat > 2 jam. Status set ke Alpha.");
            }
        }

        if ($count > 0) {
            $this->info("Proses selesai. {$count} karyawan ditandai Alpha.");
        }
    }
}