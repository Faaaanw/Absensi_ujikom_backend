@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card shadow-sm">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <strong class="text-primary">Tambah Karyawan Baru</strong>
                        <a href="{{ route('admin.employees.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.employees.store') }}" method="POST">
                            @csrf

                            <h6 class="mb-3 text-muted border-bottom pb-2">Informasi Akun (Login)</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Email Address</label>
                                    <input type="email" name="email"
                                        class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}"
                                        required placeholder="email@perusahaan.com">
                                    @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Password</label>
                                    <input type="password" name="password"
                                        class="form-control @error('password') is-invalid @enderror" required
                                        placeholder="Min. 6 karakter">
                                    @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <h6 class="mt-4 mb-3 text-muted border-bottom pb-2">Data Pribadi & Penempatan</h6>
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nama Lengkap</label>
                                    <input type="text" name="full_name"
                                        class="form-control @error('full_name') is-invalid @enderror"
                                        value="{{ old('full_name') }}" required placeholder="Masukkan nama sesuai KTP">
                                    @error('full_name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">NIK (Nomor Induk Karyawan)</label>
                                    <input type="text" name="nik" class="form-control @error('nik') is-invalid @enderror"
                                        value="{{ old('nik') }}" required placeholder="Contoh: EMP2024001">
                                    @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Nomor Telepon</label>
                                    <input type="text" name="phone" class="form-control" value="{{ old('phone') }}"
                                        placeholder="0812xxxx">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tanggal Bergabung</label>
                                    <input type="date" name="join_date" class="form-control" value="{{ date('Y-m-d') }}"
                                        required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Kantor / Lokasi Absen</label>
                                    <select name="office_id" class="form-select" required>
                                        <option value="" selected disabled>Pilih Kantor</option>
                                        @foreach($offices as $office)
                                            <option value="{{ $office->id }}">{{ $office->office_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Jabatan</label>
                                    <select name="position_id" class="form-select" required>
                                        <option value="" selected disabled>Pilih Jabatan</option>
                                        @foreach($positions as $pos)
                                            <option value="{{ $pos->id }}">{{ $pos->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Shift Kerja</label>
                                    <select name="shift_id" class="form-select" required>
                                        <option value="" selected disabled>Pilih Shift</option>
                                        @foreach($shifts as $shift)
                                            <option value="{{ $shift->id }}">
                                                {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <div class="mt-4 text-end border-top pt-3">
                                <button type="submit" class="btn btn-primary px-4">
                                    <i class="fa-solid fa-save me-2"></i> Simpan Karyawan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection