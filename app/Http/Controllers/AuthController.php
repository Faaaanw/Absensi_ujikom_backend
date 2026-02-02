<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Email/Password salah.'], 401);
        }

        if ($user->role !== 'employee') {
            return response()->json(['success' => false, 'message' => 'Akses ditolak.'], 403);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        // UBAH: Load 'profile.shift' juga
        $user->load(['profile.position', 'profile.office', 'profile.shift']);

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    'name' => $user->profile ? $user->profile->full_name : 'No Profile',
                    'nik' => $user->profile ? $user->profile->nik : null,
                    'position' => $user->profile && $user->profile->position ? $user->profile->position->name : null,

                    // DATA OFFICE (Lokasi)
                    'office' => $user->profile && $user->profile->office ? $user->profile->office->office_name : null,
                    'office_coords' => $user->profile && $user->profile->office ? [
                        'lat' => $user->profile->office->latitude,
                        'lng' => $user->profile->office->longitude,
                        'radius' => $user->profile->office->radius,
                    ] : null,

                    // DATA SHIFT (Waktu) - TAMBAHAN
                    'shift' => $user->profile && $user->profile->shift ? [
                        'name' => $user->profile->shift->name,
                        'start' => $user->profile->shift->start_time,
                        'end' => $user->profile->shift->end_time,
                    ] : null,
                ]
            ]
        ], 200);
    }


    /**
     * Handle Logout Request
     */
    public function logout(Request $request)
    {
        // Menghapus token yang sedang digunakan saat ini
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil'
        ]);
    }

    /**
     * Get User Data (Untuk cek token valid/tidak)
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load(['profile.position', 'profile.office','profile.shift']);

        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}