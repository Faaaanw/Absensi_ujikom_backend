@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Data Karyawan</h4>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">+ Tambah Karyawan</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="card">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Nama</th>
                        <th>NIK</th>
                        <th>Kantor</th>
                        <th>Jabatan</th>
                        <th>Shift</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($employees as $emp)
                        <tr>
                            <td>{{ $emp->profile->full_name }}<br><small class="text-muted">{{ $emp->email }}</small></td>
                            <td>{{ $emp->profile->nik }}</td>
                            <td>{{ $emp->profile->office->office_name }}</td>
                            <td>{{ $emp->profile->position->name }}</td>
                            <td>
                                <span class="badge bg-info text-dark">{{ $emp->profile->shift->name ?? '-' }}</span>
                                <br>
                                <small class="text-muted">
                                    {{ $emp->profile->shift->start_time ?? '' }} - {{ $emp->profile->shift->end_time ?? '' }}
                                </small>
                            </td>
                            <td>
                                <div class="d-flex gap-2">
                                    {{-- TOMBOL EDIT --}}
                                    <a href="{{ route('admin.employees.edit', $emp->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    {{-- TOMBOL DELETE (Harus pakai Form) --}}
                                    <form action="{{ route('admin.employees.destroy', $emp->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus karyawan ini? Data yang dihapus tidak bisa dikembalikan.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection