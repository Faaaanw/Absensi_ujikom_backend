@extends('layouts.app')

@section('content')
    <div class="container-fluid">

        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800 fw-bold">Laporan Harian Pegawai</h1>
        </div>

        <div class="card shadow mb-4">
            <div class="card-header py-3 bg-white">
                <form action="{{ route('admin.reports.daily') }}" method="GET" class="row g-3 align-items-end">

                    {{-- Input Tanggal Mulai --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Dari Tanggal</label>
                        <input type="date" name="start_date" class="form-control" 
                               value="{{ $startDate ?? date('Y-m-d') }}">
                    </div>

                    {{-- Input Tanggal Akhir --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Sampai Tanggal</label>
                        <input type="date" name="end_date" class="form-control" 
                               value="{{ $endDate ?? date('Y-m-d') }}">
                    </div>

                    {{-- Filter Kantor --}}
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Filter Kantor</label>
                        <select name="office_id" class="form-select">
                            <option value="">Semua Kantor</option>
                            @if(isset($offices))
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" 
                                            {{ ($officeId ?? '') == $office->id ? 'selected' : '' }}>
                                            {{ $office->office_name ?? $office->name }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    {{-- Tombol Action --}}
                    <div class="col-md-3 d-flex gap-2">
                        {{-- Tombol Filter --}}
                        <button type="submit" name="action" value="filter" class="btn btn-primary flex-fill fw-bold">
                            <i class="fa-solid fa-filter me-1"></i> Lihat
                        </button>
                        
                        {{-- Tombol Export --}}
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
                                    {{-- MULAI LOGIKA PHP YANG DIPERBARUI --}}
                                    @php
                                        // Ambil data relasi
                                        $attendance = $emp->attendances->first();
                                        $leave      = $emp->leaves->first() ?? null;
                                        $overtime   = $emp->overtimes->first() ?? null;

                                        $statusBadge = '';

                                        // LOGIKA PRIORITAS: Cek fisik kehadiran (clock_in) terlebih dahulu
                                        if ($attendance && $attendance->clock_in) {
                                            // Jika pegawai melakukan scan absen
                                            if ($attendance->status == 'terlambat') {
                                                $statusBadge = "<span class='badge bg-danger bg-opacity-10 text-danger border border-danger'>Terlambat</span>";
                                            } else {
                                                // Default hadir tepat waktu
                                                $statusBadge = "<span class='badge bg-success bg-opacity-10 text-success border border-success'>Hadir</span>";
                                            }

                                            // Jika dia hadir tapi juga punya status cuti/sakit di hari yang sama (kasus jarang tapi mungkin)
                                            if ($leave) {
                                                $statusBadge .= "<br><small class='text-muted fst-italic'>(" . ucfirst($leave->type) . ")</small>";
                                            }

                                        } elseif ($leave) {
                                            // Jika TIDAK scan absen, baru cek status Izin/Cuti
                                            $type = ucfirst($leave->type);
                                            $color = ($leave->type == 'sakit') ? 'warning' : 'info';
                                            $statusBadge = "<span class='badge bg-$color rounded-pill'>$type</span>";
                                            
                                        } else {
                                            // Tidak absen dan tidak cuti
                                            $statusBadge = "<span class='badge bg-secondary'>Alpha / Belum Absen</span>";
                                        }

                                        // Tambahan Badge Lembur (selalu muncul jika ada data lembur)
                                        if ($overtime) {
                                            $statusBadge .= " <span class='badge bg-primary rounded-pill ms-1'>+ Lembur</span>";
                                        }
                                        
                                        // Ambil nama kantor
                                        $officeName = $emp->profile->office->name ?? $emp->profile->office->office_name ?? '-';
                                    @endphp
                                    {{-- SELESAI LOGIKA PHP --}}

                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-3">
                                                    <span class="avatar-title rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 35px; height: 35px;">
                                                        {{ substr($emp->profile->full_name ?? $emp->name ?? 'U', 0, 1) }}
                                                    </span>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark">{{ $emp->profile->full_name ?? $emp->name }}</div>
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
                                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($attendance->clock_in)->format('H:i') }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($attendance && $attendance->clock_out)
                                                <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($attendance->clock_out)->format('H:i') }}</span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            {{-- Render Badge HTML --}}
                                            {!! $statusBadge !!}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">Tidak ada data pegawai sesuai filter.</td>
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