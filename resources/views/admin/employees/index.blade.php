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
                                    {{-- TOMBOL DETAIL GAJI --}}
                                    <button type="button" class="btn btn-sm btn-info text-white btn-detail-gaji"
                                        data-id="{{ $emp->id }}"
                                        data-url="{{ route('admin.employees.salary-detail', $emp->id) }}">
                                        <i class="fas fa-money-bill-wave"></i> Detail Gaji
                                    </button>

                                    {{-- TOMBOL EDIT --}}
                                    <a href="{{ route('admin.employees.edit', $emp->id) }}" class="btn btn-sm btn-warning">
                                        Edit
                                    </a>

                                    {{-- TOMBOL DELETE --}}
                                    <form action="{{ route('admin.employees.destroy', $emp->id) }}" method="POST"
                                        onsubmit="return confirm('Yakin ingin menghapus?');">
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
    <div class="modal fade" id="salaryDetailModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Estimasi Gaji Bulan Ini: <span id="modalMonth"></span></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-4">
                        <h4 id="modalUserName" class="fw-bold mb-0"></h4>
                        <small class="text-muted" id="modalUserNIK"></small>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="text-success fw-bold border-bottom pb-2">Pendapatan</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Gaji Pokok</td>
                                    <td class="text-end fw-bold" id="valGajiPokok">Rp 0</td>
                                </tr>
                                <tr>
                                    <td>Tunjangan Transport <br><small class="text-muted" id="lblTransport">0 Hari</small>
                                    </td>
                                    <td class="text-end align-middle" id="valTransport">Rp 0</td>
                                </tr>
                                <tr>
                                    <td>Lembur (Valid) <br><small class="text-muted" id="lblLembur">0 Jam</small></td>
                                    <td class="text-end align-middle" id="valLembur">Rp 0</td>
                                </tr>
                                <tr>
                                    <td>Bonus</td>
                                    <td class="text-end" id="valBonus">Rp 0</td>
                                </tr>
                            </table>
                        </div>

                        <div class="col-md-6">
                            <h6 class="text-danger fw-bold border-bottom pb-2">Potongan</h6>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td>Denda Terlambat <br><small class="text-muted" id="lblTelat">0 Kali</small></td>
                                    <td class="text-end align-middle text-danger" id="valDenda">Rp 0</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center bg-light p-3 rounded">
                        <h5 class="mb-0 fw-bold">Total Estimasi Bersih</h5>
                        <h3 class="mb-0 fw-bold text-primary" id="valTotal">Rp 0</h3>
                    </div>
                    <div class="mt-2 text-center">
                        <small class="text-muted fst-italic">*Angka ini adalah estimasi realtime berdasarkan data saat
                            ini.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script>
        $(document).ready(function () {
            // Fungsi Format Rupiah
            const formatRupiah = (number) => {
                return new Intl.NumberFormat('id-ID', {
                    style: 'currency',
                    currency: 'IDR',
                    minimumFractionDigits: 0
                }).format(number);
            };

            // Event Click Tombol Detail
            $('.btn-detail-gaji').click(function () {
                var url = $(this).data('url');
                var modal = $('#salaryDetailModal');

                // Reset isi modal (loading state)
                $('#modalUserName').text('Loading...');
                $('#valTotal').text('...');

                // Tampilkan modal
                var bsModal = new bootstrap.Modal(document.getElementById('salaryDetailModal'));
                bsModal.show();

                // AJAX Request
                $.ajax({
                    url: url,
                    type: 'GET',
                    success: function (response) {
                        // Isi Header
                        $('#modalMonth').text(response.month_name);
                        $('#modalUserName').text(response.user);
                        $('#modalUserNIK').text(response.nik);

                        // Isi Pendapatan
                        $('#valGajiPokok').text(formatRupiah(response.details.gaji_pokok));

                        $('#lblTransport').text(response.details.tunjangan_transport.count + ' Hari');
                        $('#valTransport').text(formatRupiah(response.details.tunjangan_transport.total));

                        $('#lblLembur').text(response.details.lembur.hours + ' Jam Valid');
                        $('#valLembur').text(formatRupiah(response.details.lembur.total));

                        $('#valBonus').text(formatRupiah(response.details.bonus));

                        // Isi Potongan
                        $('#lblTelat').text(response.details.denda_terlambat.count + ' Kali');
                        $('#valDenda').text('- ' + formatRupiah(response.details.denda_terlambat.total));

                        // Isi Total
                        $('#valTotal').text(formatRupiah(response.final_total));
                    },
                    error: function (xhr) {
                        // UBAH JADI INI SEMENTARA UNTUK DEBUGGING
                        console.log(xhr.responseText); // Cek Console browser (F12)
                        alert('Error: ' + xhr.status + ' ' + xhr.statusText + '\n\nPesan: ' + xhr.responseText);
                        bsModal.hide();
                    }
                });
            });
        });
    </script>
@endsection