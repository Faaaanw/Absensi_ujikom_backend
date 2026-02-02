<?php

// database/migrations/xxxx_xx_xx_create_shifts_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: "Shift Pagi", "Shift Malam"
            $table->time('start_time'); // 08:00:00
            $table->time('end_time');   // 17:00:00
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shifts');
    }
};