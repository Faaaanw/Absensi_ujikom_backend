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
                        <form action="{{ route('admin.employees.destroy', $emp->id) }}" method="POST" onsubmit="return confirm('Hapus karyawan ini?')">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection