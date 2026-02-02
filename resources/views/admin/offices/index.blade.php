@extends('layouts.app')

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">Pengaturan Kantor</h3>
        <button type="button" class="btn btn-primary shadow-sm rounded-3" data-bs-toggle="modal"
            data-bs-target="#createOfficeModal">
            <i class="fa-solid fa-plus fa-sm text-white-50 me-2"></i> Tambah Kantor
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-bottom-0 pt-4 px-4">
            <h5 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-building me-2"></i>Daftar Kantor</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-secondary">
                        <tr>
                            <th class="px-4 py-3 small fw-bold text-uppercase">Nama Kantor</th>
                            <th class="py-3 small fw-bold text-uppercase">Lokasi</th>
                            {{-- Kolom Jadwal DIHAPUS --}}
                            <th class="px-4 py-3 small fw-bold text-uppercase text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($offices as $office)
                            <tr>
                                <td class="px-4">
                                    <div class="fw-bold text-dark">{{ $office->office_name }}</div>
                                    <div class="small text-muted">ID: #{{ $office->id }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <i class="fa-solid fa-map-pin text-danger me-2"></i>
                                        <div>
                                            <div class="small text-dark">{{ $office->latitude }}, {{ $office->longitude }}</div>
                                            <div class="small text-muted" style="font-size: 0.75rem;">Radius:
                                                {{ $office->radius }}m</div>
                                        </div>
                                    </div>
                                </td>
                                {{-- Data Jadwal DIHAPUS --}}
                                <td class="px-4 text-end">
                                    <form action="{{ route('admin.offices.destroy', $office->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus kantor ini?');">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-outline-danger btn-sm rounded-3" title="Hapus">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="3" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-building-circle-slash fa-2x mb-3"></i>
                                    <p class="mb-0">Belum ada data kantor.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="modal fade" id="createOfficeModal" tabindex="-1" aria-labelledby="createOfficeLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content rounded-4 border-0">
                <div class="modal-header border-bottom-0">
                    <h5 class="modal-title fw-bold text-primary" id="createOfficeLabel">Tambah Kantor Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('admin.offices.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">NAMA KANTOR</label>
                            <input type="text" name="office_name" class="form-control" placeholder="Contoh: Kantor Pusat"
                                required>
                        </div>

                        <div class="row">
                            <div class="col-6 mb-3">
                                <label class="form-label small text-muted fw-bold">LATITUDE</label>
                                <input type="text" name="latitude" class="form-control" placeholder="-6.2088" required>
                            </div>
                            <div class="col-6 mb-3">
                                <label class="form-label small text-muted fw-bold">LONGITUDE</label>
                                <input type="text" name="longitude" class="form-control" placeholder="106.8456" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small text-muted fw-bold">RADIUS (METER)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light"><i class="fa-solid fa-circle-notch"></i></span>
                                <input type="number" name="radius" class="form-control" value="50" required>
                            </div>
                        </div>
                        
                        {{-- Input Jam Masuk & Pulang SUDAH DIHAPUS --}}

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