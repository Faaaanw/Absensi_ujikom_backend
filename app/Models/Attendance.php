<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id', // <--- TAMBAHKAN INI
        'date',
        'clock_in',
        'clock_out',
        'status',
        'lat_in',      // Sesuaikan nama kolom di migration (lat_in / latitude)
        'long_in',     // Sesuaikan nama kolom di migration
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Shift (Untuk tahu hari itu dia shift apa)
    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}