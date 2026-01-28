<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeProfile extends Model
{
    protected $fillable = [
        'user_id',
        'office_id',
        'position_id',
        'nik',
        'full_name',
        'phone',
        'join_date' // Pastikan ini masuk list
    ];

    // Relasi ke User
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Office (PENTING untuk Geofencing)
    public function office()
    {
        return $this->belongsTo(Office::class);
    }

    // Relasi ke Position (PENTING untuk Payroll)
    public function position()
    {
        return $this->belongsTo(Position::class);
    }
}