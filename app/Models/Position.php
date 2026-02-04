<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    protected $fillable = [
        'name', 
        'base_salary', 
        'overtime_rate',
        'daily_transport_allowance', // <--- BARU
        'late_fee_per_incident'      // <--- BARU
    ];
}