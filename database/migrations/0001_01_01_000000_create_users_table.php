<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['admin', 'employee'])->default('employee');
            $table->rememberToken();
            $table->timestamps();

            // Note: Kolom 'name' saya hapus karena di ERD Anda nama ada di employee_profiles
            // Tapi kalau mau disimpan di sini juga tidak apa-apa.
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
