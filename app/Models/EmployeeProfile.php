<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'position_id',
        'office_id',
        'shift_id', 
        'nik',
        'full_name',
        'phone',
        'join_date',
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Posisi
    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    // Relasi ke Kantor
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    // Relasi ke Shift (Jadwal Default Karyawan)
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}