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
                            <th class="py-3 small fw-bold text-uppercase">Tunjangan (Hari)</th>
                            <th class="py-3 small fw-bold text-uppercase">Denda (Telat)</th>
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
                                <td>
                                    <span class="text-primary fw-bold">
                                        Rp {{ number_format($pos->daily_transport_allowance, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td>
                                    <span class="text-danger fw-bold">
                                        Rp {{ number_format($pos->late_fee_per_incident, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="px-4 text-end">
                                    <div class="d-flex justify-content-end gap-2">
                                        <button class="btn btn-outline-warning btn-sm rounded-3 btn-edit"
                                            data-id="{{ $pos->id }}" data-name="{{ $pos->name }}"
                                            data-salary="{{ $pos->base_salary }}" data-overtime="{{ $pos->overtime_rate }}"
                                            data-transport="{{ $pos->daily_transport_allowance }}"
                                            data-latefee="{{ $pos->late_fee_per_incident }}" data-bs-toggle="modal"
                                            data-bs-target="#editPositionModal">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        <form action="{{ route('admin.positions.destroy', $pos->id) }}" method="POST"
                                            onsubmit="return confirm('Hapus jabatan {{ $pos->name }}?');">
                                            @csrf @method('DELETE')
                                            <button class="btn btn-outline-danger btn-sm rounded-3" title="Hapus">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
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

    <div class="modal fade" id="createPositionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary">Tambah Jabatan Baru</h5>
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
                            <input type="number" name="base_salary" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">LEMBUR PER JAM (RP)</label>
                            <input type="number" name="overtime_rate" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">TUNJANGAN TRANSPORT</label>
                            <input type="number" name="daily_transport_allowance" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold text-danger">DENDA KETERLAMBATAN</label>
                            <input type="number" name="late_fee_per_incident" class="form-control" required>
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

    <div class="modal fade" id="editPositionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-warning">Edit Jabatan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT') <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">NAMA JABATAN</label>
                            <input type="text" name="name" id="edit_name" class="form-control" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">GAJI POKOK (RP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="base_salary" id="edit_base_salary" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">LEMBUR PER JAM (RP)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="overtime_rate" id="edit_overtime_rate" class="form-control"
                                    required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">TUNJANGAN TRANSPORT</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">Rp</span>
                                <input type="number" name="daily_transport_allowance" id="edit_transport"
                                    class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold text-danger">DENDA KETERLAMBATAN</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-danger">Rp</span>
                                <input type="number" name="late_fee_per_incident" id="edit_late_fee" class="form-control"
                                    required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0">
                        <button type="button" class="btn btn-light rounded-3" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-warning rounded-3 px-4">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Ambil semua tombol edit
            const editButtons = document.querySelectorAll('.btn-edit');

            editButtons.forEach(button => {
                button.addEventListener('click', function () {
                    // Ambil data dari atribut tombol
                    const id = this.getAttribute('data-id');
                    const name = this.getAttribute('data-name');
                    const salary = this.getAttribute('data-salary');
                    const overtime = this.getAttribute('data-overtime');
                    const transport = this.getAttribute('data-transport');
                    const latefee = this.getAttribute('data-latefee');

                    // Isi input di dalam Modal Edit
                    document.getElementById('edit_name').value = name;
                    document.getElementById('edit_base_salary').value = salary;
                    document.getElementById('edit_overtime_rate').value = overtime;
                    document.getElementById('edit_transport').value = transport;
                    document.getElementById('edit_late_fee').value = latefee;

                    // Update URL Action pada form agar mengarah ke ID yang benar
                    // Route: /admin/positions/{id}
                    const form = document.getElementById('editForm');
                    form.action = "{{ url('admin/positions') }}/" + id;
                });
            });
        });
    </script>
@endsection