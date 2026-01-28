@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">Pengaturan Jabatan</h3>
        <button type="button" class="btn btn-primary shadow-sm rounded-3" data-bs-toggle="modal"
            data-bs-target="#createPositionModal">
            <i class="fa-solid fa-plus fa-sm text-white-50 me-2"></i> Tambah Jabatan
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <h5 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-briefcase me-2"></i>Daftar Jabatan</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="px-4 py-3 small fw-bold text-uppercase">Nama Jabatan</th>
                            <th class="py-3 small fw-bold text-uppercase">Gaji Pokok</th>
                            <th class="py-3 small fw-bold text-uppercase">Rate Lembur</th>
                            <th class="px-4 py-3 small fw-bold text-uppercase text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($positions as $pos)
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-dark">{{ $pos->name }}</div>
                                </td>
                                <td>
                                    <span class="fw-bold text-success">
                                        Rp {{ number_format($pos->base_salary, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-secondary">
                                        Rp {{ number_format($pos->overtime_rate, 0, ',', '.') }}
                                        <small class="text-muted">/ jam</small>
                                    </span>
                                </td>
                                <td class="px-4 text-end">
                                    <form action="{{ route('admin.positions.destroy', $pos->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus jabatan {{ $pos->name }}?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm rounded-3" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-clipboard-list fa-2x mb-3"></i>
                                    <p class="mb-0">Belum ada data jabatan.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createPositionModal" tabindex="-1" aria-labelledby="createPositionLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary" id="createPositionLabel">Tambah Jabatan Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.positions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">NAMA JABATAN</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Staff IT" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">GAJI POKOK (RP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="base_salary" class="form-control" placeholder="0" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">LEMBUR PER JAM (RP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="overtime_rate" class="form-control" placeholder="0" required>
                            </div>
                            <div class="form-text small">Nominal ini akan dikalikan jumlah jam lembur.</div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary rounded-3 px-4">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection