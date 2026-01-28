<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Absensi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body { overflow-x: hidden; }
        #sidebar-wrapper {
            min-height: 100vh;
            width: 250px;
            margin-left: -250px;
            transition: margin .25s ease-out;
        }
        #sidebar-wrapper .sidebar-heading { padding: 0.875rem 1.25rem; font-size: 1.2rem; }
        #sidebar-wrapper .list-group { width: 250px; }
        #page-content-wrapper { min-width: 100vw; }
        body.sb-sidenav-toggled #sidebar-wrapper { margin-left: 0; }
        @media (min-width: 768px) {
            #sidebar-wrapper { margin-left: 0; }
            #page-content-wrapper { min-width: 0; width: 100%; }
        }
    </style>
</head>
<body>
    <div class="d-flex" id="wrapper">
        <div class="bg-dark text-white" id="sidebar-wrapper">
            <div class="sidebar-heading border-bottom border-secondary">
                <i class="fa-solid fa-fingerprint me-2"></i> Absensi App
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.dashboard') }}" class="list-group-item list-group-item-action bg-dark text-white border-secondary {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge me-2"></i> Dashboard
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
                    <i class="fa-solid fa-users me-2"></i> Data Karyawan
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white border-secondary">
                    <i class="fa-solid fa-building me-2"></i> Pengaturan Kantor
                </a>
                <a href="#" class="list-group-item list-group-item-action bg-dark text-white border-secondary text-danger mt-5">
                    <i class="fa-solid fa-right-from-bracket me-2"></i> Logout
                </a>
            </div>
        </div>

        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-3">
                <button class="btn btn-outline-dark btn-sm" id="sidebarToggle">
                    <i class="fa-solid fa-bars"></i>
                </button>
                <div class="ms-auto">
                    <span class="text-muted">Halo, Admin</span>
                </div>
            </nav>

            <div class="container-fluid p-4">
                @yield('content')
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        window.addEventListener('DOMContentLoaded', event => {
            const sidebarToggle = document.body.querySelector('#sidebarToggle');
            if (sidebarToggle) {
                sidebarToggle.addEventListener('click', event => {
                    event.preventDefault();
                    document.body.classList.toggle('sb-sidenav-toggled');
                });
            }
        });
    </script>
</body>
</html>