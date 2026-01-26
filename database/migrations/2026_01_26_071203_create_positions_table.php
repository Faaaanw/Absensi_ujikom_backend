<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
 // database/migrations/xxxx_xx_xx_create_positions_table.php
public function up(): void
{
    Schema::create('positions', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->decimal('base_salary', 15, 2); // 15 digit, 2 desimal
        $table->decimal('overtime_rate', 15, 2);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('positions');
    }
};
