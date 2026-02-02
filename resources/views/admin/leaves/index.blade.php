@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="h3 mb-4 text-gray-800 fw-bold">Daftar Pengajuan Izin/Cuti</h3>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Nama Karyawan</th>
                            <th class="py-3">Jenis & Alasan</th>
                            <th class="py-3">Tanggal</th>
                            <th class="py-3">Bukti</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($leaves as $leave)
                        <tr>
                            <td class="px-4">
                                <div class="fw-bold text-dark">{{ $leave->user->profile->full_name ?? $leave->user->name }}</div>
                                <div class="small text-muted">{{ $leave->user->profile->nik ?? '-' }}</div>
                            </td>
                            <td>
                                <span class="badge bg-info mb-1 text-uppercase">{{ $leave->type }}</span>
                                <div class="small text-muted text-wrap" style="max-width: 200px;">
                                    "{{ $leave->reason }}"
                                </div>
                            </td>
                            <td>
                                <div class="small fw-bold">{{ $leave->start_date }}</div>
                                <div class="small text-muted">s/d</div>
                                <div class="small fw-bold">{{ $leave->end_date }}</div>
                            </td>
                            <td>
                                @if($leave->attachment)
                                    <a href="{{ asset('storage/'.$leave->attachment) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="fa-solid fa-image me-1"></i> Lihat
                                    </a>
                                @else
                                    <span class="small text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                @if($leave->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($leave->status == 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @else
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-4 text-end">
                                @if($leave->status == 'pending')
                                    <form action="{{ route('admin.leaves.update', $leave->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-success btn-sm rounded-3" onclick="return confirm('Setujui izin ini?')">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>

                                    <button class="btn btn-danger btn-sm rounded-3 ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $leave->id }}">
                                        <i class="fa-solid fa-xmark"></i>
                                    </button>
                                @else
                                    <span class="small text-muted">Selesai</span>
                                @endif
                            </td>
                        </tr>

                        <div class="modal fade" id="rejectModal{{ $leave->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <form action="{{ route('admin.leaves.update', $leave->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="rejected">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Tolak Pengajuan</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <label class="form-label">Alasan Penolakan</label>
                                            <textarea name="rejection_reason" class="form-control" rows="3" required></textarea>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="submit" class="btn btn-danger">Tolak</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">Belum ada pengajuan izin.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection