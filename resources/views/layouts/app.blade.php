<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard')</title>

    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap">

    <link rel="stylesheet" href="{{ asset('adminlte/plugins/fontawesome-free/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/plugins/overlayScrollbars/css/OverlayScrollbars.min.css') }}">
    <link rel="stylesheet" href="{{ asset('adminlte/dist/css/adminlte.min.css') }}">

    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.4/css/dataTables.bootstrap4.min.css">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

</head>
<style>
    body {
        font-family: 'Plus Jakarta Sans', sans-serif !important;
        background-color: #f8fafc !important;
        color: #334155;
    }
    
    .wrapper, .content-wrapper { background-color: #f8fafc !important; }

    /* Custom Scrollbar */
    ::-webkit-scrollbar { width: 6px; height: 6px; }
    ::-webkit-scrollbar-track { background: transparent; }
    ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

    /* Global Card styling */
    .card {
        border: none !important;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;
        border-radius: 16px !important;
        transition: all 0.3s ease;
        background: #ffffff;
    }
    .card-header {
        background-color: transparent !important;
        border-bottom: 1px solid #f1f5f9 !important;
        padding: 20px 24px;
        font-weight: 700;
        font-size: 1.1rem;
        color: #1e293b;
    }
    .card-body { padding: 24px; }

    /* Global Buttons */
    .btn {
        border-radius: 8px !important;
        padding: 8px 16px;
        font-weight: 600;
        transition: all 0.2s;
        border: none;
    }
    .btn-primary { background: #4f46e5; color: white; }
    .btn-primary:hover { background: #4338ca; box-shadow: 0 4px 12px rgba(79,70,229,0.3); transform: translateY(-1px); }
    .btn-success { background: #10b981; }
    .btn-success:hover { background: #059669; box-shadow: 0 4px 12px rgba(16,185,129,0.3); transform: translateY(-1px); }
    .btn-info { background: #0ea5e9; }
    .btn-danger { background: #ef4444; }
    .btn-danger:hover { background: #dc2626; box-shadow: 0 4px 12px rgba(239,68,68,0.3); transform: translateY(-1px); }

    /* Forms */
    .form-control, .form-select, .dataTables_filter input {
        border-radius: 8px !important;
        border: 1px solid #e2e8f0;
        padding: 10px 14px !important;
        height: auto !important;
        box-shadow: none !important;
        transition: all 0.2s;
    }
    .form-control:focus {
        border-color: #4f46e5;
        box-shadow: 0 0 0 3px rgba(79,70,229,0.1) !important;
    }
    label { font-weight: 600 !important; color: #475569; }

    /* Table Styling */
    .table { border-collapse: separate; border-spacing: 0; width: 100%; border-color: #f1f5f9; }
    .table thead th {
        background-color: #f8fafc;
        color: #64748b;
        font-weight: 700;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        border-bottom: 2px solid #e2e8f0 !important;
        padding: 16px;
    }
    .table tbody td {
        padding: 16px;
        color: #334155;
        vertical-align: middle;
        border-bottom: 1px solid #f1f5f9;
    }
    .table tbody tr:hover { background-color: #f8fafc; }

    /* Sidebar adjustments */
    .os-theme-light.os-host-transition > .os-scrollbar-vertical > .os-scrollbar-track > .os-scrollbar-handle {
        background: rgba(0,0,0,0.15);
    }
    .nav-sidebar .nav-item > .nav-link {
        border-radius: 8px;
        margin: 0 8px 4px 8px;
        color: #475569;
        font-weight: 500;
    }
    .nav-sidebar .nav-item > .nav-link:hover {
        background-color: #f1f5f9;
        color: #0f172a;
    }
    .nav-sidebar .nav-item > .nav-link.active {
        background-color: #4f46e5 !important;
        color: white !important;
        box-shadow: 0 4px 12px rgba(79,70,229,0.2) !important;
    }

    .nav-treeview { display: none; }
    .nav-treeview.menu-open { display: block; }
    
    /* Ensure sidebar scrolls */
    .sidebar { 
        height: calc(100vh - 100px); 
        overflow-y: auto; 
        padding-bottom: 40px; 
    }
</style>

<body class="hold-transition sidebar-mini layout-fixed">

    <div class="wrapper">

        <!-- Navbar -->

        <nav class="main-header navbar navbar-expand navbar-light bg-white border-bottom-0 shadow-sm" style="z-index: 1034;">

            <ul class="navbar-nav">

                <li class="nav-item">
                    <a class="nav-link text-secondary" data-widget="pushmenu" href="#" role="button">
                        <i class="fas fa-bars"></i>
                    </a>
                </li>

                <li class="nav-item d-none d-sm-inline-block">
                    <a href="/" class="nav-link text-dark font-weight-bold">Home</a>
                </li>

            </ul>

        </nav>

        <!-- Sidebar -->

        <aside class="main-sidebar sidebar-light-primary border-right-0 shadow-sm" style="background-color: #ffffff;">

            <a href="/" class="brand-link border-bottom-0 text-center py-4">
                <span class="brand-text font-weight-bold text-dark" style="font-size: 1.4rem; letter-spacing: 1px;">
                    <i class="fas fa-graduation-cap text-primary mr-2"></i>SmartCollege
                </span>
            </a>

            <div class="sidebar">

                <nav class="mt-2">

                    <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu" data-accordion="false">
                        <li class="nav-item">
                                    <a href="/" class="nav-link {{ request()->is('/') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-tachometer-alt"></i>
                                        <p>Dashboard</p>
                                    </a>
                                </li>

                        <li class="nav-item">
                                    <a href="/generate" class="nav-link {{ request()->is('generate*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-cogs"></i>
                                        <p>Generate Timetable</p>
                                    </a>
                                </li>

                        <li class="nav-item">
                            <a href="/timetable" class="nav-link {{ request()->is('timetable*') ? 'active' : '' }}">
                                <i class="nav-icon fas fa-edit"></i>
                                <p>Manage Timetable</p>
                            </a>
                        </li>
                        @php
                            $isMasterActive = request()->is('departments*', 'sections*', 'subjects*', 'faculty*', 'faculty-availability*', 'rooms*', 'subject-labs*', 'section-rooms*', 'rules*', 'excel-import*', 'import/paste*');
                        @endphp
                        <li class="nav-item {{ $isMasterActive ? 'menu-open' : '' }}">
                            <a href="#" class="nav-link {{ $isMasterActive ? 'active' : '' }}">
                                <i class="nav-icon fas fa-bars"></i>
                                <p>
                                    Master
                                    <i class="right fas fa-angle-left"></i>
                                </p>
                            </a>
                            <ul class="nav nav-treeview" style="{{ $isMasterActive ? 'display: block;' : '' }}">
                                

                                <!-- <li><br></li> -->
                                
                                <!-- <li><br></li> -->
                                <li class="nav-item">
                                    <a href="/departments" class="nav-link {{ request()->is('departments*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-building"></i>
                                        <p>Departments</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/sections" class="nav-link {{ request()->is('sections*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-layer-group"></i>
                                        <p>Sections</p>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="/faculty" class="nav-link {{ request()->is('faculty*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user"></i>
                                        <p>Faculty</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/faculty-availability"
                                        class="nav-link {{ request()->is('faculty-availability*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-user-clock"></i>
                                        <p>Faculty Availability</p>
                                    </a>
                                </li>


                                <li class="nav-item">
                                    <a href="/subjects" class="nav-link {{ request()->is('subjects*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-book"></i>
                                        <p>Subjects</p>
                                    </a>
                                </li>

                                
                                <li class="nav-item">
                                    <a href="/rooms" class="nav-link {{ request()->is('rooms*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-door-open"></i>
                                        <p>Rooms & Labs</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/subject-labs"
                                        class="nav-link {{ request()->is('subject-labs*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-flask"></i>
                                        <p>Subject Lab Mapping</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/section-rooms"
                                        class="nav-link {{ request()->is('section-rooms*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-school"></i>
                                        <p>Section Classroom</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/rules" class="nav-link {{ request()->is('rules*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-cogs"></i>
                                        <p>Rule Configuration</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/excel-import"
                                        class="nav-link {{ request()->is('excel-import*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-file-excel"></i>
                                        <p>auto add data</p>
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a href="/import/paste"
                                        class="nav-link {{ request()->is('import/paste*') ? 'active' : '' }}">
                                        <i class="nav-icon fas fa-paste"></i>
                                        <p>GTU Paste Import</p>
                                    </a>
                                </li>

                                
                            </ul>
                        </li>
                    </ul>

                </nav>

            </div>

        </aside>

        <div class="content-wrapper">

            <section class="content pt-3">

                <div class="container-fluid">

                    @yield('content')

                </div>

            </section>

        </div>

        <footer class="main-footer">

            <strong>
                Copyright © 2026
            </strong>

        </footer>

    </div>

    <script src="{{ asset('adminlte/plugins/jquery/jquery.min.js') }}"></script>

    <script src="{{ asset('adminlte/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>

    <script src="{{ asset('adminlte/plugins/overlayScrollbars/js/jquery.overlayScrollbars.min.js') }}"></script>

    <script src="{{ asset('adminlte/dist/js/adminlte.min.js') }}"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/jquery.dataTables.min.js"></script>

    <script src="https://cdn.datatables.net/1.13.4/js/dataTables.bootstrap4.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    @stack('scripts')

</body>

</html>