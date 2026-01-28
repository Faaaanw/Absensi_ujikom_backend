<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Payrolls extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'month',
        'year',
        'total_attendance',
        'basic_salary',
        'overtime_pay',
        'bonus_pay',
        'deductions',
        'net_salary',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}