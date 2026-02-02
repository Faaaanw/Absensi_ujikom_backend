@extends('layouts.app')

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4>Data Karyawan</h4>
        <a href="{{ route('admin.employees.create') }}" class="btn btn-primary">+ Tambah Karyawan</a>
    </div>

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
                                <span class="badge bg-info text-dark">
                                    {{ $emp->profile->shift->name ?? '-' }}
                                </span>
                                <br>
                                <small class="text-muted">
                                    {{ $emp->profile->shift->start_time ?? '' }} - {{ $emp->profile->shift->end_time ?? '' }}
                                </small>
                            </td>

                            <td>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection