<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    /**
     * LANGKAH 1: GENERATE TOKEN (Dipanggil oleh Mobile User)
     * Hanya memberikan token jika user berada di lokasi kantor.
     */
    public function generateAttendanceToken(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $user = Auth::user();
        $user->load('profile.office');
        $office = $user->profile->office;

        if (!$office) {
            return response()->json(['message' => 'Kantor belum diatur untuk user ini.'], 400);
        }

        // VALIDASI GEOFENCING
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

        // Buat token unik yang menyimpan User ID
        $token = Str::random(32);
        
        // Simpan token di cache selama 1 menit (Key: token, Value: user_id)
        Cache::put('attendance_token_' . $token, $user->id, 60);

        return response()->json([
            'success' => true,
            'message' => 'Token berhasil dibuat, silakan tunjukkan QR ke scanner.',
            'token' => $token,
            'expires_in' => 60
        ]);
    }

    /**
     * LANGKAH 2: SCAN & CLOCK IN (Dipanggil oleh Alat Scanner/Admin)
     * Alat scanner menscan QR di HP user, lalu mengirim token ke endpoint ini.
     */
    public function submitScan(Request $request)
    {
        $request->validate([
            'token' => 'required'
        ]);

        // Cari siapa pemilik token ini di Cache
        $userId = Cache::pull('attendance_token_' . $request->token); // 'pull' ambil data lalu hapus (sekali pakai)

        if (!$userId) {
            return response()->json([
                'success' => false,
                'message' => 'Token tidak valid atau sudah kadaluarsa.'
            ], 400);
        }

        $today = Carbon::today();
        
        // Cek apakah user sudah absen hari ini
        $existingAttendance = Attendance::where('user_id', $userId)
                                        ->whereDate('date', $today)
                                        ->first();

        if ($existingAttendance) {
            // Jika sudah ada record hari ini, kita anggap ini proses Clock Out
            if ($existingAttendance->clock_out) {
                return response()->json(['message' => 'Karyawan sudah melakukan Clock Out hari ini.'], 400);
            }

            $existingAttendance->update([
                'clock_out' => Carbon::now()->format('H:i:s'),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Berhasil Clock Out',
                'data' => $existingAttendance
            ]);
        }

        // Jika belum ada record hari ini, lakukan Clock In
        $jamMasuk = Carbon::now()->setTime(9, 0, 0); 
        $waktuSekarang = Carbon::now();
        $status = $waktuSekarang->greaterThan($jamMasuk) ? 'terlambat' : 'hadir';

        $attendance = Attendance::create([
            'user_id' => $userId,
            'date' => $today,
            'clock_in' => $waktuSekarang->format('H:i:s'),
            'status' => $status
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil Absen Masuk (Clock In)',
            'user_id' => $userId,
            'data' => $attendance
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
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