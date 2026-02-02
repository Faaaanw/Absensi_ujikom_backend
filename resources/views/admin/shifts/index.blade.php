@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">Pengaturan Shift</h3>
        <button type="button" class="btn btn-primary shadow-sm rounded-3" data-bs-toggle="modal"
            data-bs-target="#createShiftModal">
            <i class="fa-solid fa-plus fa-sm text-white-50 me-2"></i> Tambah Shift
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <h5 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-clock me-2"></i>Daftar Shift Kerja</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="px-4 py-3 small fw-bold text-uppercase">Nama Shift</th>
                            <th class="py-3 small fw-bold text-uppercase">Jam Masuk</th>
                            <th class="py-3 small fw-bold text-uppercase">Jam Pulang</th>
                            <th class="px-4 py-3 small fw-bold text-uppercase text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($shifts as $shift)
                            <tr>
                                <td class="px-4 fw-bold">{{ $shift->name }}</td>
                                <td><span class="badge bg-success">{{ $shift->start_time }}</span></td>
                                <td><span class="badge bg-danger">{{ $shift->end_time }}</span></td>
                                <td class="px-4 text-end">
                                    <form action="{{ route('admin.shifts.destroy', $shift->id) }}" method="POST"
                                        onsubmit="return confirm('Hapus shift ini?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm rounded-3">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center py-5">Belum ada data shift.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createShiftModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary">Tambah Shift Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.shifts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">NAMA SHIFT</label>
                            <input type="text" name="name" class="form-control" placeholder="Contoh: Shift Pagi" required>
                        </div>
                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small text-muted fw-bold">JAM MASUK</label>
                                <input type="time" name="start_time" class="form-control" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small text-muted fw-bold">JAM PULANG</label>
                                <input type="time" name="end_time" class="form-control" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection