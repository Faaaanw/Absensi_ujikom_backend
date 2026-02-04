@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <h3 class="h3 mb-4 text-gray-800 fw-bold">Daftar Pengajuan Lembur</h3>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Nama Karyawan</th>
                            <th class="py-3">Tanggal Lembur</th>
                            <th class="py-3">Durasi</th>
                            <th class="py-3">Status</th>
                            <th class="py-3 text-end px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($overtimes as $ot)
                        <tr>
                            <td class="px-4">
                                <div class="fw-bold text-dark">{{ $ot->user->profile->full_name ?? $ot->user->name }}</div>
                                <div class="small text-muted">{{ $ot->user->profile->position->name ?? '-' }}</div>
                            </td>
                            <td>{{ $ot->date }}</td>
                            <td>
                                <span class="fw-bold">{{ $ot->duration }} Jam</span>
                            </td>
                            <td>
                                @if($ot->status == 'pending')
                                    <span class="badge bg-warning text-dark">Pending</span>
                                @elseif($ot->status == 'approved')
                                    <span class="badge bg-success">Disetujui</span>
                                @else
                                    <span class="badge bg-danger">Ditolak</span>
                                @endif
                            </td>
                            <td class="px-4 text-end">
                                @if($ot->status == 'pending')
                                    <form action="{{ route('overtime.update', $ot->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="approved">
                                        <button class="btn btn-success btn-sm rounded-3" title="Setujui" onclick="return confirm('Setujui lembur ini?')">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>

                                    <form action="{{ route('overtime.update', $ot->id) }}" method="POST" class="d-inline">
                                        @csrf @method('PUT')
                                        <input type="hidden" name="status" value="rejected">
                                        <button class="btn btn-danger btn-sm rounded-3 ms-1" title="Tolak" onclick="return confirm('Tolak lembur ini?')">
                                            <i class="fa-solid fa-xmark"></i>
                                        </button>
                                    </form>
                                @else
                                    <i class="fa-solid fa-lock text-muted"></i>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">Belum ada pengajuan lembur.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection