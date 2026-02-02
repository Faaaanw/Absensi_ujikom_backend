<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /**
     * LANGKAH 1: GENERATE TOKEN (Dipanggil oleh Mobile User)
     */
    public function generateAttendanceToken(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        // UBAH: Load juga relation 'shift'
        $user->load(['profile.office', 'profile.shift']);

        // UBAH: Cek juga apakah shift sudah diatur
        if (!$user->profile || !$user->profile->office || !$user->profile->shift) {
            return response()->json(['message' => 'Kantor atau Shift belum diatur untuk akun ini.'], 400);
        }

        $office = $user->profile->office;
        $shift  = $user->profile->shift; // Ambil data shift

        // 1. VALIDASI GEOFENCING (Tetap pakai data Office)
        $distance = $this->calculateDistance(
            $request->latitude,
            $request->longitude,
            $office->latitude,
            $office->longitude
        );

        if ($distance > $office->radius) {
            return response()->json([
                'success' => false,
                'message' => 'Anda berada di luar radius kantor.',
                'distance' => round($distance) . ' meter'
            ], 400);
        }

        // 2. VALIDASI WAKTU (Sekarang pakai data SHIFT)
        $today = Carbon::today();
        $now = Carbon::now();
        
        // UBAH: Ambil jam pulang dari Shift
        $jamPulang = Carbon::parse($shift->end_time); 

        // Logika sederhana untuk Shift Malam (Lintas hari)
        // Jika start_time > end_time (misal 20:00 - 05:00), penanganan butuh logika tambahan.
        // Untuk sekarang diasumsikan shift pagi-sore (hari yang sama).

        $existingAttendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        if ($existingAttendance) {
            // --- SUDAH ABSEN MASUK (MAU PULANG) ---
            
            if ($existingAttendance->clock_out) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah menyelesaikan absensi hari ini.',
                ], 400);
            }

            // Cek apakah belum waktunya pulang
            if ($now->lessThan($jamPulang)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Belum waktunya pulang. Jadwal pulang: ' . $jamPulang->format('H:i'),
                ], 400);
            }
            
        } else {
            // --- BELUM ABSEN MASUK (MAU MASUK) ---

            // Jika sekarang sudah lewat jam pulang shift, tolak.
            if ($now->greaterThan($jamPulang)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Jam operasional shift ini sudah berakhir.',
                ], 400);
            }
        }

        // 3. GENERATE TOKEN
        $token = Str::random(32);
        Cache::put('attendance_token_' . $token, $user->id, 120);

        return response()->json([
            'success' => true,
            'message' => $existingAttendance ? 'Silakan scan untuk Absen Keluar' : 'Silakan scan untuk Absen Masuk',
            'token' => $token,
            'expires_in' => 120
        ]);
    }

    /**
     * LANGKAH 2: SCAN & CLOCK IN
     */
    public function submitScan(Request $request)
    {
        $request->validate(['token' => 'required']);

        $userId = Cache::pull('attendance_token_' . $request->token);

        if (!$userId) {
            return response()->json(['success' => false, 'message' => 'Token invalid/expired.'], 400);
        }

        // UBAH: Load office DAN shift
        $user = User::with(['profile.office', 'profile.shift'])->find($userId);
        
        $office = $user->profile->office;
        $shift  = $user->profile->shift; // Variable shift
        
        $today = Carbon::today();
        $waktuSekarang = Carbon::now();

        $existingAttendance = Attendance::where('user_id', $userId)
            ->whereDate('date', $today)
            ->first();

        // --- PROSES CLOCK OUT ---
        if ($existingAttendance) {
            if ($existingAttendance->clock_out) {
                return response()->json(['message' => 'Sudah Clock Out sebelumnya.'], 400);
            }

            $existingAttendance->update([
                'clock_out' => $waktuSekarang->format('H:i:s'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Clock Out',
                'data' => $existingAttendance
            ]);
        }

        // --- PROSES CLOCK IN ---
        
        // 1. Ambil jam masuk dari SHIFT
        $jamMasuk = Carbon::parse($shift->start_time); 
        
        // 2. Toleransi 30 menit
        $batasTerlambat = $jamMasuk->copy()->addMinutes(30);

        // 3. Tentukan status
        if ($waktuSekarang->greaterThan($batasTerlambat)) {
            $status = 'terlambat';
        } else {
            $status = 'hadir';
        }

        // 4. Create Attendance (UBAH: Simpan shift_id)
        $attendance = Attendance::create([
            'user_id' => $userId,
            'shift_id' => $shift->id, // <--- PENTING: Menyimpan shift hari ini
            'date' => $today,
            'clock_in' => $waktuSekarang->format('H:i:s'),
            'status' => $status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Absen Masuk. Status: ' . ucfirst($status),
            'user_id' => $userId,
            'data' => $attendance
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        // ... (Fungsi jarak sama seperti sebelumnya)
        $earthRadius = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $earthRadius * $c;
    }
}