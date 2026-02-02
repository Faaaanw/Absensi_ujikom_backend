<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Absensi</title>

    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        :root {
            --sidebar-width: 260px;
            --primary-color: #4e73df;
            --sidebar-bg: #111827;
            /* Dark Navy */
            --sidebar-hover: #1f2937;
            --text-muted: #9ca3af;
        }

        body {
            font-family: 'Nunito', sans-serif;
            background-color: #f3f4f6;
            /* Light gray background */
            overflow-x: hidden;
        }

        /* --- Sidebar Styles --- */
        #sidebar-wrapper {
            min-height: 100vh;
            width: var(--sidebar-width);
            margin-left: calc(var(--sidebar-width) * -1);
            background-color: var(--sidebar-bg);
            transition: margin .25s ease-out;
            z-index: 1000;
        }

        #sidebar-wrapper .sidebar-brand {
            padding: 1.5rem 1rem;
            font-size: 1.2rem;
            font-weight: 800;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 1px;
            display: flex;
            align-items: center;
        }

        #sidebar-wrapper .list-group {
            width: var(--sidebar-width);
            padding: 0 10px;
        }

        .list-group-item-action {
            background-color: transparent;
            color: var(--text-muted);
            border: none;
            padding: 12px 20px;
            margin-bottom: 5px;
            border-radius: 8px;
            /* Rounded corners */
            font-weight: 600;
            transition: all 0.3s;
        }

        .list-group-item-action:hover {
            background-color: var(--sidebar-hover);
            color: #fff;
            transform: translateX(5px);
        }

        .list-group-item-action.active {
            background-color: var(--primary-color);
            color: #fff;
            box-shadow: 0 4px 6px -1px rgba(78, 115, 223, 0.4);
        }

        .list-group-item-action i {
            width: 25px;
            text-align: center;
        }

        /* --- Content & Navbar --- */
        #page-content-wrapper {
            min-width: 100vw;
            transition: margin .25s ease-out;
        }

        .navbar {
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        /* --- Toggle States --- */
        body.sb-sidenav-toggled #sidebar-wrapper {
            margin-left: 0;
        }

        @media (min-width: 768px) {
            #sidebar-wrapper {
                margin-left: 0;
            }

            #page-content-wrapper {
                min-width: 0;
                width: 100%;
            }

            body.sb-sidenav-toggled #sidebar-wrapper {
                margin-left: calc(var(--sidebar-width) * -1);
            }
        }
    </style>
</head>

<body>
    <div class="d-flex" id="wrapper">
        <div id="sidebar-wrapper">
            <div class="sidebar-brand border-bottom border-secondary mb-3">
                <i class="fa-solid fa-fingerprint me-2 text-primary"></i>
                <span>Absensi App</span>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.dashboard') }}"
                    class="list-group-item list-group-item-action {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-gauge me-2"></i> Dashboard
                </a>
                <a href="{{ route('admin.employees.index') }}"
                    class="list-group-item list-group-item-action {{ request()->is('admin/employees*') ? 'active' : '' }}">
                    <i class="fa-solid fa-users me-2"></i> Data Karyawan
                </a>

                <div class="text-secondary small fw-bold text-uppercase mt-3 mb-2 px-3" style="font-size: 0.75rem;">
                    Master Data</div>

                <a href="{{ route('admin.offices.index') }}"
                    class="list-group-item list-group-item-action {{ request()->is('admin/offices*') ? 'active' : '' }}">
                    <i class="fa-solid fa-building me-2"></i> Kantor
                </a>
                <a href="{{ route('admin.positions.index') }}"
                    class="list-group-item list-group-item-action {{ request()->is('admin/positions*') ? 'active' : '' }}">
                    <i class="fa-solid fa-id-card me-2"></i> Jabatan
                </a>
                <a href="{{ route('admin.shifts.index') }}"
                    class="list-group-item list-group-item-action {{ request()->is('admin/shifts*') ? 'active' : '' }}">
                    <i class="fa-solid fa-clock me-2"></i> Shift
                </a>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-danger">Logout</button>
                </form>
            </div>
        </div>
        <div id="page-content-wrapper">
            <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom px-4 py-3">
                <button class="btn btn-light text-primary" id="sidebarToggle">
                    <i class="fa-solid fa-bars fa-lg"></i>
                </button>

                <div class="collapse navbar-collapse" id="navbarSupportedContent">
                    <ul class="navbar-nav ms-auto mt-2 mt-lg-0">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" id="navbarDropdown"
                                role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <div class="text-end me-2 d-none d-lg-block">
                                    <div class="small text-gray-600 fw-bold">Admin User</div>
                                    <div style="font-size: 10px;" class="text-muted">Administrator</div>
                                </div>
                                <img src="https://ui-avatars.com/api/?name=Admin+User&background=4e73df&color=fff"
                                    alt="Profile" class="rounded-circle" width="40" height="40">
                            </a>
                            <div class="dropdown-menu dropdown-menu-end shadow border-0 animate__animated animate__fadeIn"
                                aria-labelledby="navbarDropdown">
                                <a class="dropdown-item" href="#"><i
                                        class="fa-solid fa-user fa-sm fa-fw mr-2 text-gray-400"></i> Profile</a>
                                <a class="dropdown-item" href="#"><i
                                        class="fa-solid fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i> Settings</a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item text-danger" href="#"><i
                                        class="fa-solid fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i> Logout</a>
                            </div>
                        </li>
                    </ul>
                </div>
            </nav>

            <div class="container-fluid p-4">
                <div class="row mb-4">
                    <div class="col-12">
                        @yield('content')
                    </div>
                </div>
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