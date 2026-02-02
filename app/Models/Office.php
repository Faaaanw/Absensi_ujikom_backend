<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Office extends Model
{
    use HasFactory;

    // HAPUS start_time dan end_time dari sini
    protected $fillable = [
        'office_name', 
        'latitude', 
        'longitude', 
        'radius'
    ];

    // Relasi ke Employee Profile
    public function employees()
    {
        return $this->hasMany(EmployeeProfile::class);
    }
}