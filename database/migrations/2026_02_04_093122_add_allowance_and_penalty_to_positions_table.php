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
        Schema::table('positions', function (Blueprint $table) {
            // Uang makan/transport per hari hadir
            $table->decimal('daily_transport_allowance', 15, 2)->default(0);
            // Denda per kejadian terlambat
            $table->decimal('late_fee_per_incident', 15, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            //
        });
    }
};
