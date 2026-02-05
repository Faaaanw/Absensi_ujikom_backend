@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Laporan Harian Pegawai</h1>
            <span class="badge bg-primary fs-6">
                {{ \Carbon\Carbon::parse($date ?? request('date', date('Y-m-d')))->translatedFormat('d F Y') }}
            </span>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <form action="{{ route('admin.reports.daily') }}" method="GET" class="row g-3 align-items-end">

                    {{-- Input Tanggal Tunggal --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Pilih Tanggal</label>
                        <input type="date" name="date" class="form-control"
                            value="{{ $date ?? request('date', date('Y-m-d')) }}">
                    </div>

                    {{-- Filter Kantor --}}
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Filter Kantor</label>
                        <select name="office_id" class="form-select">
                            <option value="">Semua Kantor</option>
                            @if(isset($offices))
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ ($officeId ?? '') == $office->id ? 'selected' : '' }}>
                                        {{ $office->office_name ?? $office->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Tombol Action --}}
                    <div class="col-md-4 d-flex gap-2">
                        <button type="submit" name="action" value="filter" class="btn btn-primary flex-fill fw-bold">
                            <i class="fa-solid fa-filter me-1"></i> Tampilkan
                        </button>
                        <button type="submit" name="action" value="export" class="btn btn-success flex-fill fw-bold">
                            <i class="fa-solid fa-file-excel me-1"></i> Excel
                        </button>
                    </div>
                </form>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3">Nama Pegawai</th>
                                <th class="py-3">Kantor & Shift</th>
                                <th class="py-3 text-center">Jam Masuk</th>
                                <th class="py-3 text-center">Jam Pulang</th>
                                <th class="py-3 text-center">Status Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if(isset($employees))
                                @forelse($employees as $emp)
                                    {{-- MULAI LOGIKA PHP --}}
                                    @php
                                        // 1. Ambil Tanggal Laporan
                                        $targetDateString = $date ?? request('date', date('Y-m-d'));
                                        $reportDate = \Carbon\Carbon::parse($targetDateString);

                                        // 2. Cari Attendance (Absensi) untuk tanggal ini
                                        $attendance = $emp->attendances->firstWhere('date', $targetDateString);

                                        // 3. Filter Leave (Cuti/Izin/Sakit) yang approved pada tanggal ini
                                        $leave = $emp->leaves->filter(function ($value) use ($reportDate) {
                                            $start = \Carbon\Carbon::parse($value->start_date)->startOfDay();
                                            $end = \Carbon\Carbon::parse($value->end_date)->endOfDay();
                                            return $reportDate->between($start, $end) && $value->status == 'approved';
                                        })->first();

                                        // 4. Filter Overtime (Lembur)
                                        // PERBAIKAN: Cari lembur HANYA di tanggal ini DAN status approved
                                        $overtime = $emp->overtimes->filter(function ($ot) use ($targetDateString) {
                                            return $ot->date == $targetDateString && $ot->status == 'approved';
                                        })->first();

                                        // 5. Variabel Pendukung
                                        $shiftStart = \Carbon\Carbon::parse($emp->profile->shift->start_time ?? '09:00:00');
                                        $shiftStart->setDate($reportDate->year, $reportDate->month, $reportDate->day);
                                        $batasAlpha = $shiftStart->copy()->addHours(2);
                                        $now = \Carbon\Carbon::now();
                                        $officeName = $emp->profile->office->name ?? $emp->profile->office->office_name ?? '-';
                                        $statusBadge = '';

                                        // Variabel flag untuk cek apakah pegawai HADIR secara fisik
                                        // Kita asumsikan false dulu
                                        $isPresent = false;

                                        // --- LOGIKA STATUS UTAMA ---

                                        // A. Cek Cuti/Izin/Sakit (Prioritas Tertinggi)
                                        if ($leave) {
                                            $type = ucfirst($leave->type);
                                            $color = (strtolower($leave->type) == 'sakit') ? 'warning' : 'info';
                                            $statusBadge = "<span class='badge bg-$color rounded-pill'>$type</span>";
                                            // Pegawai tidak hadir fisik
                                            $isPresent = false;
                                        }
                                        // B. Cek Data Absensi
                                        elseif ($attendance) {
                                            // Status Sakit/Izin/Cuti dari sinkronisasi
                                            if (in_array(strtolower($attendance->status), ['sakit', 'izin', 'cuti'])) {
                                                $type = ucfirst($attendance->status);
                                                $color = (strtolower($attendance->status) == 'sakit') ? 'warning' : 'info';
                                                $statusBadge = "<span class='badge bg-$color rounded-pill'>$type</span>";
                                                $isPresent = false;
                                            }
                                            // Status Alpha Manual
                                            elseif ($attendance->status == 'alpha') {
                                                $statusBadge = "<span class='badge bg-danger'>Alpha</span>";
                                                $isPresent = false;
                                            }
                                            // Hadir / Terlambat (Ada Clock In)
                                            elseif ($attendance->clock_in) {
                                                if ($attendance->status == 'terlambat') {
                                                    $statusBadge = "<span class='badge bg-danger bg-opacity-10 text-danger border border-danger'>Terlambat</span>";
                                                } else {
                                                    $statusBadge = "<span class='badge bg-success bg-opacity-10 text-success border border-success'>Hadir</span>";
                                                }
                                                // INI KUNCINYA: Jika ada clock_in dan bukan status sakit/izin, maka HADIR
                                                $isPresent = true;
                                            }
                                            // Data ada tapi tidak jelas (Fallback)
                                            else {
                                                $statusBadge = "<span class='badge bg-danger'>Alpha</span>";
                                                $isPresent = false;
                                            }
                                        }
                                        // C. Tidak ada data sama sekali
                                        else {
                                            if ($reportDate->isToday()) {
                                                if ($now->greaterThan($batasAlpha)) {
                                                    $statusBadge = "<span class='badge bg-danger'>Alpha (Otomatis)</span>";
                                                } else {
                                                    $statusBadge = "<span class='badge bg-secondary'>Belum Absen</span>";
                                                }
                                            } elseif ($reportDate->lt(\Carbon\Carbon::today())) {
                                                $statusBadge = "<span class='badge bg-danger'>Alpha</span>";
                                            } else {
                                                $statusBadge = "<span class='badge bg-light text-dark border'>-</span>";
                                            }
                                            $isPresent = false;
                                        }

                                        // --- LOGIKA BADGE LEMBUR ---
                                        // Hanya tampilkan jika ada data lembur DAN pegawai dianggap HADIR
                                        if ($overtime && $isPresent) {
                                            $statusBadge .= " <span class='badge bg-primary rounded-pill ms-1'>+ Lembur</span>";
                                        }
                                    @endphp
                                    {{-- SELESAI LOGIKA PHP --}}

                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span
                                                        class="avatar-title rounded-circle bg-primary text-white d-flex align-items-center justify-content-center"
                                                        style="width: 35px; height: 35px;">
                                                        {{ substr($emp->profile->full_name ?? $emp->name ?? 'U', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $emp->profile->full_name ?? $emp->name }}
                                                    </div>
                                                    <div class="small text-muted">{{ $emp->email }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="small fw-bold">{{ $officeName }}</div>
                                            <div class="small text-muted">
                                                Shift:
                                                {{ \Carbon\Carbon::parse($emp->profile->shift->start_time ?? '00:00')->format('H:i') }}
                                                -
                                                {{ \Carbon\Carbon::parse($emp->profile->shift->end_time ?? '00:00')->format('H:i') }}
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            @if($attendance && $attendance->clock_in)
                                                <span
                                                    class="fw-bold text-dark">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($attendance && $attendance->clock_out)
                                                <span
                                                    class="fw-bold text-dark">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {!! $statusBadge !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Tidak ada data pegawai sesuai filter.
                                        </td>
                                    </tr>
                                @endforelse
                            @else
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Data karyawan tidak ditemukan.</td>
                                </tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection