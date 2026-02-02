<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'start_time',
        'end_time',
    ];

    // Relasi: Satu shift bisa dipakai banyak karyawan
    public function employees()
    {
        return $this->hasMany(EmployeeProfile::class);
    }

    // Relasi: Satu shift bisa dipakai di banyak data absensi
    public function attendances()
    {
        return $this->hasMany(Attendance::class);
    }
}