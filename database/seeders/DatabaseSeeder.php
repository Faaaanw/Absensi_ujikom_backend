<?php

namespace Database\Seeders;

use App\Models\Office;
use App\Models\User;
use App\Models\Position;
use App\Models\EmployeeProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat Kantor
        $office = Office::firstOrCreate(
            ['office_name' => 'Kantor Utama'],
            [
                'latitude' => -6.827102665794467,
                'longitude' => 107.13729765891608,
                'radius' => 100,
            ]
        );

        // 2. Buat Jabatan (Penting untuk Payroll/Profile)
        $position = Position::firstOrCreate(
            ['name' => 'Staff IT'],
            [
                'base_salary' => 5000000,
                'overtime_rate' => 20000
            ]
        );

        // 3. Buat Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin2@test.com'],
            [
                'password' => 'password', // WAJIB DI-HASH
                'role' => 'admin',
            ]
        );

        // 4. Buat Employee
        $employee = User::firstOrCreate(
            ['email' => 'employee2@test.com'],
            [
                'password' => 'password',
                'role' => 'employee',
            ]
        );

        // 5. Buat Employee Profile (SANGAT PENTING)
        // Tanpa ini, $user->profile->office di AttendanceController akan Error
        EmployeeProfile::firstOrCreate(
            ['user_id' => $employee->id],
            [
                'office_id' => $office->id,
                'position_id' => $position->id,
                'nik' => '1234567890',
                'full_name' => 'Budi Karyawan',
                'phone' => '08123456789',
                'join_date' => now()->format('Y-m-d'),
            ]
        );
    }
}