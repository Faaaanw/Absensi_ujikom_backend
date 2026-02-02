@extends('layouts.app')

@section('content')
    <style>
    /* CSS PERBAIKAN: Membalik Video Secara Horizontal */
    #reader video {
        /* Membalikkan gambar secara horizontal */
        transform: scaleX(-1) !important; 
        -webkit-transform: scaleX(-1) !important;
        
        /* Agar video tetap rapi */
        width: 100% !important;
        object-fit: cover;
        border-radius: 8px; /* Opsional: pemanis sudut */
    }
</style>
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h3 class="h3 mb-0 text-gray-800 fw-bold">Dashboard & Scanner</h3>
        <span class="small text-muted">{{ \Carbon\Carbon::now()->format('l, d F Y') }}</span>
    </div>

    <div class="row">
        <div class="col-lg-8 mb-4">
            <div class="row mb-4">
                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-primary rounded-3">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs fw-bold text-primary text-uppercase mb-1">Total Pegawai</div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['total'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-users fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-success rounded-3">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs fw-bold text-success text-uppercase mb-1">Hadir Hari Ini</div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['hadir'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-check fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card border-0 shadow-sm h-100 py-2 border-start border-4 border-warning rounded-3">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs fw-bold text-warning text-uppercase mb-1">Terlambat</div>
                                    <div class="h5 mb-0 fw-bold text-gray-800">{{ $stats['terlambat'] }}</div>
                                </div>
                                <div class="col-auto">
                                    <i class="fas fa-user-clock fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-bottom-0 pt-4 px-4">
                    <h5 class="fw-bold text-secondary mb-0"><i class="fa-solid fa-list-check me-2"></i>Log Absensi Terakhir
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-secondary">
                                <tr>
                                    <th class="px-4 py-3 small fw-bold text-uppercase">Nama Pegawai</th>
                                    <th class="py-3 small fw-bold text-uppercase">Waktu Scan</th>
                                    <th class="px-4 py-3 small fw-bold text-uppercase text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentAttendances as $row)
                                    <tr>
                                        <td class="px-4">
                                            <div class="d-flex align-items-center">
                                                <div class="avatar-sm me-2">
                                                    <span class="avatar-title rounded-circle bg-light text-primary fw-bold p-2">
                                                        {{ substr($row->user->profile->full_name ?? 'User', 0, 1) }}
                                                    </span>
                                                </div>
                                                <span
                                                    class="fw-bold text-dark">{{ $row->user->profile->full_name ?? 'Unknown' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-muted small">
                                            {{-- PERBAIKAN 1: Format Jam menjadi lebih rapi (H:i) --}}
                                            <i class="far fa-clock me-1"></i>
                                            {{ \Carbon\Carbon::parse($row->clock_in)->format('H:i') }} WIB
                                        </td>
                                        <td class="px-4 text-end">
                                            {{-- PERBAIKAN 2: Logika Status yang lebih ketat --}}
                                            @if($row->status == 'terlambat')
                                                <span
                                                    class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-3">
                                                    TERLAMBAT
                                                </span>
                                            @elseif($row->status == 'hadir')
                                                <span
                                                    class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-3">
                                                    HADIR
                                                </span>
                                            @else
                                                {{-- Handle status lain jika ada (misal: Izin/Sakit) --}}
                                                <span class="badge bg-secondary rounded-pill px-3">
                                                    {{ strtoupper($row->status) }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted small">Belum ada data absensi hari
                                            ini.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Kolom Kanan (Scanner) tetap sama HTML-nya, kita ubah Script di bawah --}}
        <div class="col-lg-4">
            {{-- ... Kode Card Scanner HTML tetap sama ... --}}

            <div class="card border-0 shadow-sm rounded-4 text-center overflow-hidden">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="m-0 fw-bold"><i class="fa-solid fa-qrcode me-2"></i>Scanner Absensi</h6>
                </div>

                <div class="card-body bg-light p-4">
                    <div class="scanner-wrapper position-relative bg-dark rounded-3 overflow-hidden shadow-inner"
                        style="min-height: 250px;">
                        <div id="camera-placeholder"
                            class="d-flex flex-column align-items-center justify-content-center h-100 position-absolute w-100 top-0 start-0 text-white-50">
                            <i class="fa-solid fa-camera fa-3x mb-3"></i>
                            <p class="small mb-0">Kamera Non-aktif</p>
                        </div>
                        <div id="reader" style="width: 100%; height: 100%; position: relative; z-index: 10;"></div>
                    </div>

                    <div class="mt-4">
                        <button id="btn-start" class="btn btn-primary w-100 rounded-pill fw-bold shadow-sm mb-2"
                            onclick="startScanner()">
                            <i class="fa-solid fa-power-off me-2"></i> Nyalakan Kamera
                        </button>
                        <button id="btn-stop" class="btn btn-danger w-100 rounded-pill fw-bold shadow-sm d-none"
                            onclick="stopScanner()">
                            <i class="fa-solid fa-stop me-2"></i> Matikan Kamera
                        </button>
                    </div>

                    <div id="scan-result" class="mt-3 small text-muted fst-italic">
                        Menunggu scan...
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm rounded-4 mt-3 bg-info bg-opacity-10 border-start border-4 border-info">
                <div class="card-body p-3">
                    <div class="d-flex">
                        <i class="fa-solid fa-circle-info text-info me-3 mt-1"></i>
                        <div class="small text-muted">
                            Pastikan karyawan menunjukkan QR Code dengan jelas. Toleransi keterlambatan adalah 30 menit dari
                            jam masuk.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        let html5QrCode;
        const readerId = "reader";
        const resultElement = document.getElementById("scan-result");
        const btnStart = document.getElementById("btn-start");
        const btnStop = document.getElementById("btn-stop");
        const placeholder = document.getElementById("camera-placeholder");

        function startScanner() {
            html5QrCode = new Html5Qrcode(readerId);

            btnStart.classList.add("d-none");
            btnStop.classList.remove("d-none");
            placeholder.classList.add("d-none");
            resultElement.innerText = "Mengarahkan kamera...";

            const config = { fps: 10, qrbox: { width: 250, height: 250 } };

            html5QrCode.start(
                { facingMode: "environment" },
                config,
                onScanSuccess,
                onScanFailure
            ).catch(err => {
                console.error("Gagal memulai kamera", err);
                alert("Gagal mengakses kamera: " + err);
                stopScanner();
            });
        }

        function stopScanner() {
            if (html5QrCode) {
                html5QrCode.stop().then(() => {
                    html5QrCode.clear();
                    resetUI();
                }).catch(err => {
                    console.error("Gagal mematikan kamera", err);
                });
            } else {
                resetUI();
            }
        }

        function resetUI() {
            btnStart.classList.remove("d-none");
            btnStop.classList.add("d-none");
            placeholder.classList.remove("d-none");
            resultElement.innerText = "Kamera dimatikan.";
        }

        function onScanSuccess(decodedText, decodedResult) {
            // Pause agar tidak scan berkali-kali
            html5QrCode.pause();
            resultElement.innerText = "Memproses...";

            fetch('/api/attendance/submit-scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // Pastikan CSRF Token ada jika menggunakan Web routes, 
                    // jika API routes (stateless) biasanya tidak perlu, tapi jika session based perlu:
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify({ token: decodedText })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // PERBAIKAN 3: Menampilkan Jam Realtime dari Server
                        // Kita ambil jam dari response data (data.data.clock_in atau clock_out)

                        let timeString = "";
                        let statusString = "";

                        if (data.data) {
                            // Ambil waktu Clock In atau Clock Out tergantung response
                            let rawTime = data.data.clock_out ? data.data.clock_out : data.data.clock_in;
                            // Format waktu sederhana (ambil 5 karakter awal HH:mm)
                            timeString = rawTime ? rawTime.substring(0, 5) : '-';
                            statusString = data.data.status ? data.data.status.toUpperCase() : 'BERHASIL';
                        }

                        // Alert dengan detail waktu
                        alert(`✅ ${data.message}\n🕒 Waktu: ${timeString} WIB\n📊 Status: ${statusString}`);

                        location.reload();
                    } else {
                        alert("❌ GAGAL: " + data.message);
                        html5QrCode.resume();
                        resultElement.innerText = "Silakan coba lagi.";
                    }
                })
                .catch(err => {
                    alert("Error Sistem: " + err);
                    html5QrCode.resume();
                });
        }

        function onScanFailure(error) {
            // console.warn(`Code scan error = ${error}`);
        }
    </script>
@endsection