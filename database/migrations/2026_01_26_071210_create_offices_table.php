<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    // database/migrations/xxxx_xx_xx_create_offices_table.php
    public function up(): void
    {
        Schema::create('offices', function (Blueprint $table) {
            $table->id();
            $table->string('office_name');
            $table->decimal('latitude', 10, 8); // Presisi tinggi untuk GPS
            $table->decimal('longitude', 11, 8);
            $table->integer('radius'); // Dalam meter

            $table->time('start_time'); // Contoh: 08:00:00
            $table->time('end_time');   // Contoh: 17:00:00
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('offices');
    }
};
