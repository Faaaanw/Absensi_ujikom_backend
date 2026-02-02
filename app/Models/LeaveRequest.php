<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveRequest extends Model
{
    use HasFactory;
    protected $table = 'leave_requests'; // (Opsional jika nama tabel sesuai standar)

    protected $fillable = [
        'user_id',
        'type',
        'start_date',
        'end_date',
        'reason',
        'attachment',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
    public function getDurationDaysAttribute()
    {
        // Menambah 1 hari karena jika tanggal sama dihitung 1 hari
        return $this->start_date->diffInDays($this->end_date) + 1;
    }
}
