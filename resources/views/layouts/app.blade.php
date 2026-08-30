<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0a0a1a">
    <title>@yield('title', 'Sistem Absensi') - {{ config('app.name', 'Sistem Absensi') }}</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.min.css">

    <style>
        /* ════════════════════════════════════════════
           DESIGN SYSTEM TOKENS
        ════════════════════════════════════════════ */
        :root {
            --c-purple:      #7c3aed;
            --c-indigo:      #4f46e5;
            --c-blue:        #2563eb;
            --c-cyan:        #06b6d4;
            --c-pink:        #ec4899;
            --c-success:     #10b981;
            --c-warning:     #f59e0b;
            --c-danger:      #ef4444;

            --sidebar-width: 258px;
            --header-height: 60px;
            --transition:    0.28s cubic-bezier(0.4, 0, 0.2, 1);

            /* Glassmorphism */
            --glass-bg:      rgba(255,255,255,0.06);
            --glass-border:  rgba(255,255,255,0.1);
            --glass-blur:    blur(16px);

            /* Content area */
            --content-bg:    #f0f4ff;
            --card-bg:       #ffffff;
            --card-border:   rgba(99,102,241,0.1);
            --text-primary:  #1e1b4b;
            --text-muted:    #6b7280;
        }

        * { box-sizing: border-box; }
        html { scroll-behavior: smooth; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--content-bg);
            color: var(--text-primary);
            overflow-x: hidden;
            margin: 0;
            -webkit-tap-highlight-color: transparent;
        }

        h1,h2,h3,h4,h5,h6,.brand-font {
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        /* ════════════════════════════════════════════
           ANIMATED MESH BACKGROUND (sidebar area)
        ════════════════════════════════════════════ */
        .sidebar-mesh {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            z-index: 0;
            background: #0d0d1f;
            overflow: hidden;
        }

        .mesh-orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            animation: orbFloat 8s ease-in-out infinite;
            pointer-events: none;
        }

        .mesh-orb-1 {
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(124,58,237,0.5), transparent 70%);
            top: -40px; left: -40px;
            animation-delay: 0s;
        }
        .mesh-orb-2 {
            width: 200px; height: 200px;
            background: radial-gradient(circle, rgba(6,182,212,0.3), transparent 70%);
            top: 40%; left: 30%;
            animation-delay: 3s;
        }
        .mesh-orb-3 {
            width: 160px; height: 160px;
            background: radial-gradient(circle, rgba(236,72,153,0.25), transparent 70%);
            bottom: 10%; left: -20px;
            animation-delay: 6s;
        }

        @keyframes orbFloat {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33%  { transform: translate(15px, -20px) scale(1.05); }
            66%  { transform: translate(-10px, 15px) scale(0.97); }
        }

        /* ════════════════════════════════════════════
           SIDEBAR
        ════════════════════════════════════════════ */
        .app-sidebar {
            width: var(--sidebar-width);
            position: fixed;
            top: 0; bottom: 0; left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            overflow-x: hidden;
            transition: transform var(--transition), box-shadow var(--transition);
            scrollbar-width: thin;
            scrollbar-color: rgba(255,255,255,0.08) transparent;
        }

        .app-sidebar::-webkit-scrollbar { width: 3px; }
        .app-sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.1); border-radius: 3px; }

        /* Brand */
        .sidebar-brand {
            height: var(--header-height);
            display: flex;
            align-items: center;
            padding: 0 1.1rem;
            text-decoration: none;
            position: relative;
            flex-shrink: 0;
            border-bottom: 1px solid rgba(255,255,255,0.06);
        }

        .sidebar-brand-icon {
            width: 36px; height: 36px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: #fff;
            margin-right: 0.65rem;
            flex-shrink: 0;
            position: relative;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            box-shadow: 0 0 20px rgba(124,58,237,0.5), 0 0 40px rgba(124,58,237,0.2);
        }

        .sidebar-brand-icon::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(255,255,255,0.2), transparent);
        }

        .sidebar-close-btn {
            display: none;
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.1);
            color: rgba(255,255,255,0.7);
            width: 30px; height: 30px;
            border-radius: 8px;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.95rem;
            padding: 0;
            transition: background 0.2s;
        }
        .sidebar-close-btn:hover { background: rgba(255,255,255,0.14); color: #fff; }

        /* User Card */
        .sidebar-user {
            margin: 0.65rem 0.75rem;
            padding: 0.7rem;
            border-radius: 12px;
            background: linear-gradient(135deg, rgba(124,58,237,0.15), rgba(79,70,229,0.1));
            border: 1px solid rgba(124,58,237,0.2);
            flex-shrink: 0;
            display: flex;
            align-items: center;
            gap: 0.6rem;
        }

        .sidebar-user-avatar {
            width: 36px; height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid rgba(124,58,237,0.5);
            box-shadow: 0 0 12px rgba(124,58,237,0.3);
            flex-shrink: 0;
        }

        /* Nav */
        .sidebar-menu { list-style: none; padding: 0.25rem 0.6rem; margin: 0; flex: 1; }

        .sidebar-header {
            font-size: 0.63rem;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.12em;
            color: rgba(255,255,255,0.22);
            padding: 0.75rem 0.6rem 0.25rem;
        }

        .sidebar-item { margin-bottom: 0.18rem; }

        .sidebar-link {
            display: flex;
            align-items: center;
            padding: 0.62rem 0.8rem;
            color: rgba(255,255,255,0.5);
            text-decoration: none;
            border-radius: 10px;
            font-size: 0.875rem;
            font-weight: 500;
            transition: all 0.2s ease;
            min-height: 44px;
            position: relative;
            overflow: hidden;
        }

        .sidebar-link::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 10px;
            background: linear-gradient(135deg, rgba(124,58,237,0.0), rgba(79,70,229,0.0));
            transition: background 0.2s;
        }

        .sidebar-link:hover {
            color: rgba(255,255,255,0.9);
            background: rgba(255,255,255,0.06);
        }

        .sidebar-link.active {
            color: #fff;
            background: linear-gradient(135deg, rgba(124,58,237,0.85), rgba(79,70,229,0.85));
            box-shadow: 0 4px 16px rgba(124,58,237,0.35), inset 0 1px 0 rgba(255,255,255,0.15);
        }

        .sidebar-link i {
            font-size: 1.05rem;
            margin-right: 0.7rem;
            width: 18px;
            text-align: center;
            flex-shrink: 0;
            position: relative;
        }

        .sidebar-submenu { list-style: none; padding-left: 1.9rem; margin: 0.15rem 0; }
        .sidebar-submenu .sidebar-link { padding: 0.45rem 0.7rem; font-size: 0.82rem; min-height: 38px; }

        /* ════════════════════════════════════════════
           MAIN LAYOUT
        ════════════════════════════════════════════ */
        .app-main {
            flex: 1;
            margin-left: var(--sidebar-width);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            transition: margin-left var(--transition);
            position: relative;
        }

        /* Content background: subtle grid pattern */
        .app-main::before {
            content: '';
            position: fixed;
            top: 0; left: var(--sidebar-width);
            right: 0; bottom: 0;
            background-image:
                linear-gradient(rgba(99,102,241,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(99,102,241,0.03) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        .app-wrapper { display: flex; min-height: 100vh; }

        /* ════════════════════════════════════════════
           TOP HEADER / NAVBAR
        ════════════════════════════════════════════ */
        .app-header {
            height: var(--header-height);
            background: rgba(255,255,255,0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(99,102,241,0.1);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            position: sticky;
            top: 0;
            z-index: 1020;
            box-shadow: 0 1px 20px rgba(99,102,241,0.06);
            flex-shrink: 0;
        }

        /* ════════════════════════════════════════════
           PAGE CONTENT
        ════════════════════════════════════════════ */
        .page-content {
            padding: 1.5rem;
            flex: 1;
            position: relative;
            animation: pageFadeIn 0.35s ease-out;
        }

        /* Modal Z-Index Stacking Context Fix */
        .modal {
            z-index: 1060;
        }
        .modal-backdrop {
            z-index: 1050;
        }

        @keyframes pageFadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ════════════════════════════════════════════
           GLASSMORPHISM CARDS
        ════════════════════════════════════════════ */
        .card {
            background: rgba(255,255,255,0.9);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(99,102,241,0.08);
            border-radius: 16px;
            box-shadow: 0 4px 24px rgba(99,102,241,0.06), 0 1px 4px rgba(0,0,0,0.04);
            margin-bottom: 1.25rem;
            transition: box-shadow 0.25s ease, transform 0.25s ease;
        }

        .card:hover {
            box-shadow: 0 8px 32px rgba(99,102,241,0.1), 0 2px 8px rgba(0,0,0,0.05);
        }

        .card-header {
            background: transparent;
            border-bottom: 1px solid rgba(99,102,241,0.08);
            padding: 1rem 1.25rem;
            font-weight: 600;
            font-family: 'Plus Jakarta Sans', sans-serif;
            border-radius: 16px 16px 0 0 !important;
        }

        .card-body { padding: 1.25rem; }

        /* Gradient Cards */
        .card-gradient-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            color: #fff;
        }

        .card-gradient-success {
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            border: none;
            color: #fff;
        }

        /* ════════════════════════════════════════════
           BUTTONS
        ════════════════════════════════════════════ */
        .btn {
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            border-radius: 10px;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            border: none;
            color: #fff;
            box-shadow: 0 4px 14px rgba(124,58,237,0.3);
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #6d28d9, #4338ca);
            box-shadow: 0 6px 20px rgba(124,58,237,0.45);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-success {
            background: linear-gradient(135deg, #059669, #10b981);
            border: none;
            color: #fff;
            box-shadow: 0 4px 14px rgba(16,185,129,0.3);
        }

        .btn-success:hover {
            background: linear-gradient(135deg, #047857, #059669);
            box-shadow: 0 6px 20px rgba(16,185,129,0.4);
            transform: translateY(-1px);
            color: #fff;
        }

        .btn-outline-primary {
            border: 1.5px solid rgba(79,70,229,0.35);
            color: #4f46e5;
            background: rgba(79,70,229,0.04);
        }

        .btn-outline-primary:hover {
            background: rgba(79,70,229,0.08);
            border-color: #4f46e5;
            color: #4f46e5;
        }

        /* ════════════════════════════════════════════
           BADGES
        ════════════════════════════════════════════ */
        .badge-subtle-primary  { background: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
        .badge-subtle-success  { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-subtle-danger   { background: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }
        .badge-subtle-warning  { background: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-subtle-info     { background: #cffafe; color: #155e75; border: 1px solid #a5f3fc; }

        /* ════════════════════════════════════════════
           TABLE
        ════════════════════════════════════════════ */
        .table > :not(caption) > * > * {
            padding: 0.8rem 1rem;
            vertical-align: middle;
        }

        .table-hover > tbody > tr {
            transition: background 0.15s;
        }

        .table-hover > tbody > tr:hover {
            background: rgba(99,102,241,0.04);
        }

        .table thead th {
            font-size: 0.78rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #6b7280;
            background: rgba(99,102,241,0.03);
            border-bottom: 1px solid rgba(99,102,241,0.1);
        }

        /* ════════════════════════════════════════════
           ALERTS
        ════════════════════════════════════════════ */
        .alert {
            border-radius: 12px;
            border: none;
            padding: 0.85rem 1.1rem;
        }

        .alert-success {
            background: linear-gradient(135deg, rgba(16,185,129,0.12), rgba(5,150,105,0.08));
            color: #065f46;
            border: 1px solid rgba(16,185,129,0.2);
        }

        .alert-danger {
            background: linear-gradient(135deg, rgba(239,68,68,0.12), rgba(220,38,38,0.08));
            color: #991b1b;
            border: 1px solid rgba(239,68,68,0.2);
        }

        /* ════════════════════════════════════════════
           SIDEBAR BACKDROP
        ════════════════════════════════════════════ */
        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.6);
            backdrop-filter: blur(4px);
            z-index: 1030;
            display: none;
            opacity: 0;
            transition: opacity var(--transition);
        }

        .sidebar-backdrop.show { display: block; opacity: 1; }

        /* ════════════════════════════════════════════
           FORM CONTROLS
        ════════════════════════════════════════════ */
        .form-control, .form-select {
            border-radius: 10px;
            border: 1.5px solid rgba(99,102,241,0.15);
            background: rgba(255,255,255,0.8);
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        .form-control:focus, .form-select:focus {
            border-color: #7c3aed;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
            background: #fff;
        }

        /* ════════════════════════════════════════════
           FOOTER
        ════════════════════════════════════════════ */
        .app-footer {
            padding: 0.8rem 1.25rem;
            background: rgba(255,255,255,0.7);
            backdrop-filter: blur(12px);
            border-top: 1px solid rgba(99,102,241,0.08);
            font-size: 0.78rem;
            color: #9ca3af;
            position: relative;
            z-index: 1;
        }

        /* ════════════════════════════════════════════
           RESPONSIVE / MOBILE
        ════════════════════════════════════════════ */
        @media (max-width: 991.98px) {
            .app-sidebar {
                transform: translateX(calc(-1 * var(--sidebar-width)));
                box-shadow: none;
            }

            .app-sidebar.show {
                transform: translateX(0);
                box-shadow: 8px 0 40px rgba(0,0,0,0.3);
            }

            .app-main { margin-left: 0; }
            .app-main::before { left: 0; }
            .sidebar-mesh { display: none; }

            .sidebar-close-btn { display: flex; }

            .page-content { padding: 1rem; }
            .card-body { padding: 1rem; }
            .card-header { padding: 0.85rem 1rem; }
        }

        @media (max-width: 575.98px) {
            .page-content { padding: 0.85rem; }
            .app-header { padding: 0 0.85rem; }
            .card { border-radius: 14px; }
        }

        @supports (padding: env(safe-area-inset-bottom)) {
            .app-footer { padding-bottom: calc(0.8rem + env(safe-area-inset-bottom)); }
            .app-sidebar { padding-bottom: env(safe-area-inset-bottom); }
        }

        /* ════════════════════════════════════════════
           UTILITY
        ════════════════════════════════════════════ */
        .text-gradient {
            background: linear-gradient(135deg, #7c3aed, #4f46e5, #06b6d4);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .glow-primary { box-shadow: 0 0 20px rgba(124,58,237,0.3); }
        .glow-success { box-shadow: 0 0 20px rgba(16,185,129,0.3); }

        /* Pulse animation untuk badge aktif */
        @keyframes pulse-ring {
            0%   { box-shadow: 0 0 0 0 rgba(124,58,237,0.4); }
            70%  { box-shadow: 0 0 0 8px rgba(124,58,237,0); }
            100% { box-shadow: 0 0 0 0 rgba(124,58,237,0); }
        }

        .pulse { animation: pulse-ring 2s infinite; }

        /* Shimmer loading */
        @keyframes shimmer {
            0%   { background-position: -400px 0; }
            100% { background-position: 400px 0; }
        }
    </style>
    @yield('styles')
</head>
<body>
    <!-- Sidebar Animated Mesh Background -->
    <div class="sidebar-mesh d-none d-lg-block">
        <div class="mesh-orb mesh-orb-1"></div>
        <div class="mesh-orb mesh-orb-2"></div>
        <div class="mesh-orb mesh-orb-3"></div>
    </div>

    <div class="app-wrapper">
        <!-- Mobile Backdrop -->
        <div class="sidebar-backdrop" id="sidebarBackdrop"></div>

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Main Area -->
        <div class="app-main">
            @include('layouts.navbar')

            <main class="page-content">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="bi bi-check-circle-fill fs-5 flex-shrink-0"></i>
                        <div>{{ session('success') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 mb-3" role="alert">
                        <i class="bi bi-exclamation-triangle-fill fs-5 flex-shrink-0"></i>
                        <div>{{ session('error') }}</div>
                        <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <i class="bi bi-exclamation-octagon-fill fs-5"></i>
                            <strong>Terdapat kesalahan input!</strong>
                        </div>
                        <ul class="mb-0 ps-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @yield('content')
            </main>

            <footer class="app-footer">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-1">
                    <span>&copy; {{ date('Y') }} <strong>{{ config('app.name', 'Sistem Absensi') }}</strong></span>
                    <span>v1.0 &bull; Role: <span class="badge bg-white border" style="color: #4f46e5; font-size: 0.72rem;">{{ auth()->user()->role?->display_name ?? 'User' }}</span></span>
                </div>
            </footer>
        </div>
    </div>

    @yield('modals')

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.10.6/dist/sweetalert2.all.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.2/dist/chart.umd.min.js"></script>

    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        $(document).ready(function() {
            function openSidebar() {
                $('.app-sidebar').addClass('show');
                $('#sidebarBackdrop').addClass('show');
                $('body').css('overflow', 'hidden');
            }

            function closeSidebar() {
                $('.app-sidebar').removeClass('show');
                $('#sidebarBackdrop').removeClass('show');
                $('body').css('overflow', '');
            }

            $('#sidebarToggle').on('click', openSidebar);
            $('#sidebarBackdrop, #sidebarCloseBtn').on('click', closeSidebar);

            $(window).on('resize', function() {
                if ($(window).width() > 991) closeSidebar();
            });

            // Swipe kiri untuk tutup sidebar
            let touchStartX = 0;
            const sidebar = document.querySelector('.app-sidebar');
            if (sidebar) {
                sidebar.addEventListener('touchstart', e => { touchStartX = e.changedTouches[0].screenX; }, { passive: true });
                sidebar.addEventListener('touchend', e => {
                    if (touchStartX - e.changedTouches[0].screenX > 60) closeSidebar();
                }, { passive: true });
            }

            // Password Toggle
            $(document).on('click', '.btn-toggle-password', function(e) {
                e.preventDefault();
                const input = $(this).closest('.input-group').find('input');
                const icon  = $(this).find('i');
                const isPassword = input.attr('type') === 'password';
                input.attr('type', isPassword ? 'text' : 'password');
                icon.toggleClass('bi-eye-slash', !isPassword).toggleClass('bi-eye', isPassword);
            });

            // Auto-dismiss alert
            setTimeout(() => $('.alert-dismissible').fadeOut(400, function() { $(this).remove(); }), 5000);

            // Chevron rotate saat collapse
            $('.sidebar-link[data-bs-toggle="collapse"]').on('click', function() {
                $(this).find('.bi-chevron-down').toggleClass('rotate-180');
            });
        });

        // Toast helper
        const Toast = Swal.mixin({
            toast: true, position: 'top-end', showConfirmButton: false,
            timer: 3500, timerProgressBar: true,
            didOpen: (toast) => {
                toast.addEventListener('mouseenter', Swal.stopTimer);
                toast.addEventListener('mouseleave', Swal.resumeTimer);
            }
        });
    </script>

    <style>
        .rotate-180 { transform: rotate(180deg); }
        .bi-chevron-down { transition: transform 0.25s ease; }
    </style>

    @yield('scripts')
</body>
</html>
