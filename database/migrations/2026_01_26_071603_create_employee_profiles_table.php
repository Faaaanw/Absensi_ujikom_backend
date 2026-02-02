<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_employee_profiles_table.php
// (REVISI DARI KODE KAMU)
    public function up(): void
    {
        Schema::create('employee_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('position_id')->constrained('positions');
            $table->foreignId('office_id')->constrained('offices');

            // TAMBAHAN: Relasi ke Shift
            $table->foreignId('shift_id')->constrained('shifts');

            $table->string('nik', 20)->unique();
            $table->string('full_name');
            $table->string('phone', 15);
            $table->date('join_date');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employee_profiles');
    }
};
