<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory;

    protected $table = 'attendances'; // (Opsional jika nama tabel sesuai standar)

    // --- TAMBAHKAN BAGIAN INI ---
    protected $fillable = [
        'user_id',
        'date',
        'clock_in',
        'clock_out',
        'status',
        'latitude', // <-- Tambahkan ini jika nanti Anda menyimpan lokasi juga
        'longitude', //<-- Tambahkan ini jika nanti Anda menyimpan lokasi juga
    ];

    // Relasi ke User (biar bisa dipanggil $attendance->user)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}