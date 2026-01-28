<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle Login Request
     */
    public function login(Request $request)
    {
        // 1. Validasi Input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // 2. Cari User berdasarkan Email
        $user = User::where('email', $request->email)->first();

        // 3. Cek Password (Hash)
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau Password salah.',
            ], 401);
        }

        // 4. Generate Token (Sanctum)
        // 'auth_token' adalah nama tokennya, bisa diganti apa saja
        $token = $user->createToken('auth_token')->plainTextToken;

        // 5. Load Data Profil Karyawan (PENTING)
        // Kita perlu data ini di Flutter (Nama, Jabatan, Kantor)
        // Menggunakan 'load' untuk eager loading relasi
        $user->load(['profile.position', 'profile.office']);

        // 6. Return Response JSON
        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'token' => $token,
                'user' => [
                    'id' => $user->id,
                    'email' => $user->email,
                    'role' => $user->role,
                    // Mengambil data dari relasi profile
                    'name' => $user->profile ? $user->profile->full_name : 'No Profile',
                    'nik' => $user->profile ? $user->profile->nik : null,
                    'position' => $user->profile && $user->profile->position ? $user->profile->position->name : null,
                    'office' => $user->profile && $user->profile->office ? $user->profile->office->office_name : null,
                    'office_coords' => $user->profile && $user->profile->office ? [
                        'lat' => $user->profile->office->latitude,
                        'lng' => $user->profile->office->longitude,
                        'radius' => $user->profile->office->radius,
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
        $user->load(['profile.position', 'profile.office']);
        
        return response()->json([
            'success' => true,
            'data' => $user
        ]);
    }
}