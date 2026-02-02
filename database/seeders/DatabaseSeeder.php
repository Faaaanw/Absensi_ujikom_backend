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
        // 3. Buat Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin2@test.com'],
            [
                'password' => 'password', // WAJIB DI-HASH
                'role' => 'admin',
            ]
        );

    }
}