@extends('layouts.app')

@section('content')
<div class="container">
    <div class="card">
        <div class="card-header">Edit Karyawan: {{ $user->profile->full_name }}</div>
        <div class="card-body">
            
            <form action="{{ route('admin.employees.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT') {{-- PENTING: Untuk update data --}}

                <div class="row">
                    {{-- Kolom Kiri --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>NIK (Tidak bisa diubah)</label>
                            <input type="text" class="form-control bg-light" value="{{ $user->profile->nik }}" readonly>
                        </div>

                        <div class="mb-3">
                            <label>Nama Lengkap</label>
                            <input type="text" name="full_name" class="form-control" value="{{ old('full_name', $user->profile->full_name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label>Password Baru (Kosongkan jika tidak diganti)</label>
                            <input type="password" name="password" class="form-control" placeholder="***">
                        </div>
                    </div>

                    {{-- Kolom Kanan --}}
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label>Kantor</label>
                            <select name="office_id" class="form-select" required>
                                @foreach($offices as $office)
                                    <option value="{{ $office->id }}" {{ $user->profile->office_id == $office->id ? 'selected' : '' }}>
                                        {{ $office->office_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Jabatan</label>
                            <select name="position_id" class="form-select" required>
                                @foreach($positions as $position)
                                    <option value="{{ $position->id }}" {{ $user->profile->position_id == $position->id ? 'selected' : '' }}>
                                        {{ $position->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>Shift</label>
                            <select name="shift_id" class="form-select" required>
                                @foreach($shifts as $shift)
                                    <option value="{{ $shift->id }}" {{ $user->profile->shift_id == $shift->id ? 'selected' : '' }}>
                                        {{ $shift->name }} ({{ $shift->start_time }} - {{ $shift->end_time }})
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label>No. HP</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->profile->phone) }}">
                        </div>

                        <div class="mb-3">
                            <label>Tanggal Bergabung</label>
                            <input type="date" name="join_date" class="form-control" value="{{ old('join_date', $user->profile->join_date) }}">
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-success">Simpan Perubahan</button>
                    <a href="{{ route('admin.employees.index') }}" class="btn btn-secondary">Batal</a>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection