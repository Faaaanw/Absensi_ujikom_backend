<?php

namespace App\Exports;

use App\Models\Attendance;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize; // Agar lebar kolom otomatis
use Carbon\Carbon;

class AttendanceReportExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize
{
    protected $startDate;
    protected $endDate;
    protected $officeId;

    public function __construct($startDate, $endDate, $officeId)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->officeId = $officeId;
    }

    public function collection()
    {
        $query = Attendance::with(['user.profile.office', 'user.profile.shift'])
            ->whereBetween('date', [$this->startDate, $this->endDate]);

        if ($this->officeId) {
            $query->whereHas('user.profile.office', function ($q) {
                $q->where('id', $this->officeId);
            });
        }

        return $query->orderBy('date', 'asc')->get();
    }

    public function headings(): array
    {
        return [
            'Tanggal',
            'Nama Pegawai',
            'Kantor',
            'Jam Masuk',
            'Jam Pulang',
            'Status Kehadiran',
        ];
    }

    public function map($attendance): array
    {
        $user = $attendance->user;
        $profile = $user->profile;
        $shift = $profile->shift ?? null;

        // --- LOGIKA STATUS (Supaya di Excel statusnya akurat) ---
        $statusText = ucfirst($attendance->status);
        
        // Cek ulang keterlambatan (jika shift ada)
        if ($shift && $attendance->clock_in) {
            $jamMasukShift = Carbon::parse($attendance->date . ' ' . $shift->start_time);
            $jamAbsen = Carbon::parse($attendance->date . ' ' . $attendance->clock_in);
            $batasToleransi = $jamMasukShift->copy()->addMinutes(30);

            if ($jamAbsen->greaterThan($batasToleransi)) {
                $statusText = 'Terlambat';
            }
        }

        return [
            $attendance->date,
            $profile->full_name ?? $user->name ?? 'No Name',
            $profile->office->name ?? '-',
            $attendance->clock_in,
            $attendance->clock_out ?? '-',
            $statusText,
        ];
    }
}