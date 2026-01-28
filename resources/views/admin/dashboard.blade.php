@extends('layouts.app')

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="row text-center mb-4">
                <div class="col-md-4">
                    <div class="card bg-primary text-white p-3">
                        <h5>Total</h5>
                        <h2>{{ $stats['total'] }}</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-success text-white p-3">
                        <h5>Hadir</h5>
                        <h2>{{ $stats['hadir'] }}</h2>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-warning text-dark p-3">
                        <h5>Terlambat</h5>
                        <h2>{{ $stats['terlambat'] }}</h2>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white"><strong>Log Absensi Hari Ini</strong></div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Nama</th>
                                <th>Jam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentAttendances as $row)
                                <tr>
                                    <td>{{ $row->user->profile->full_name }}</td>
                                    <td>{{ $row->clock_in }}</td>
                                    <td><span
                                            class="badge {{ $row->status == 'hadir' ? 'bg-success' : 'bg-warning' }}">{{ strtoupper($row->status) }}</span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header bg-dark text-white text-center">
                    <strong>Scanner Absensi</strong>
                </div>
                <div class="card-body text-center">
                    <div id="reader" style="width: 100%"></div>
                    <div id="result" class="mt-3"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://unpkg.com/html5-qrcode"></script>
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        function onScanSuccess(decodedText, decodedResult) {
            // Hentikan scanner agar tidak spam request
            html5QrcodeScanner.clear();

            fetch('/api/attendance/submit-scan', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    // Kita buat bypass atau kirim token di sini nanti
                },
                body: JSON.stringify({ token: decodedText })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Gunakan alert yang lebih manis atau langsung reload
                        console.log("Success:", data);
                        alert("✅ " + data.message);
                    } else {
                        alert("❌ Gagal: " + data.message);
                    }
                    location.reload();
                })
                .catch(err => {
                    alert("Error: " + err);
                    location.reload();
                });
        }

        let html5QrcodeScanner = new Html5QrcodeScanner("reader", {
            fps: 10,
            qrbox: { width: 250, height: 250 }
        });
        html5QrcodeScanner.render(onScanSuccess);
    </script>
@endsection