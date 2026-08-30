<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="theme-color" content="#06001f">
    <title>Login - {{ config('app.name', 'Sistem Absensi') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            min-height: 100dvh;
            display: flex;
            overflow: hidden;
        }

        /* ═══════════════════════════
           LEFT PANEL — Ilustrasi
        ═══════════════════════════ */
        .login-left {
            flex: 1;
            position: relative;
            background: #06001f;
            display: none;
            overflow: hidden;
        }

        @media (min-width: 992px) {
            .login-left { display: flex; }
        }

        /* Animated gradient mesh */
        .login-left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 60% 60% at 20% 30%, rgba(124,58,237,0.55) 0%, transparent 70%),
                radial-gradient(ellipse 50% 50% at 80% 70%, rgba(6,182,212,0.35) 0%, transparent 70%),
                radial-gradient(ellipse 45% 45% at 60% 10%, rgba(236,72,153,0.25) 0%, transparent 70%);
            animation: meshPulse 8s ease-in-out infinite alternate;
        }

        @keyframes meshPulse {
            0%   { opacity: 0.8; transform: scale(1); }
            100% { opacity: 1;   transform: scale(1.05); }
        }

        .login-left::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(255,255,255,0.03) 1px, transparent 1px),
                linear-gradient(90deg, rgba(255,255,255,0.03) 1px, transparent 1px);
            background-size: 50px 50px;
        }

        .login-left-content {
            position: relative;
            z-index: 10;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3rem;
            width: 100%;
            text-align: center;
        }

        /* Floating orbs */
        .orb {
            position: absolute;
            border-radius: 50%;
            filter: blur(80px);
            animation: orbFloat 10s ease-in-out infinite;
            pointer-events: none;
        }

        .orb-1 { width:300px;height:300px;background:rgba(124,58,237,0.4);top:-80px;left:-80px;animation-delay:0s; }
        .orb-2 { width:250px;height:250px;background:rgba(6,182,212,0.3);bottom:-60px;right:-60px;animation-delay:4s; }
        .orb-3 { width:200px;height:200px;background:rgba(236,72,153,0.25);top:50%;left:50%;transform:translate(-50%,-50%);animation-delay:2s; }

        @keyframes orbFloat {
            0%,100% { transform: translate(0,0); }
            50%      { transform: translate(20px,-20px); }
        }

        .orb-3 {
            animation: orbFloat3 12s ease-in-out infinite;
            animation-delay: 2s;
        }

        @keyframes orbFloat3 {
            0%,100% { top:50%;left:50%;transform:translate(-50%,-50%); }
            50%      { top:45%;left:55%;transform:translate(-50%,-50%); }
        }

        /* Logo di panel kiri */
        .left-logo {
            width: 72px; height: 72px;
            border-radius: 20px;
            background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.05));
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            color: #fff;
            margin-bottom: 2rem;
            box-shadow: 0 8px 32px rgba(124,58,237,0.4), inset 0 1px 0 rgba(255,255,255,0.2);
        }

        .left-tagline {
            font-size: 0.78rem;
            color: rgba(255,255,255,0.4);
            text-transform: uppercase;
            letter-spacing: 0.15em;
            margin-bottom: 0.75rem;
        }

        .left-headline {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 2.4rem;
            font-weight: 800;
            color: #fff;
            line-height: 1.2;
            margin-bottom: 1.25rem;
        }

        .left-headline span {
            background: linear-gradient(135deg, #a78bfa, #67e8f9);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .left-description {
            color: rgba(255,255,255,0.5);
            font-size: 0.92rem;
            line-height: 1.7;
            max-width: 340px;
        }

        /* Feature pills */
        .feature-pills {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .feature-pill {
            background: rgba(255,255,255,0.07);
            border: 1px solid rgba(255,255,255,0.12);
            border-radius: 20px;
            padding: 0.35rem 0.9rem;
            font-size: 0.75rem;
            color: rgba(255,255,255,0.6);
            backdrop-filter: blur(4px);
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .feature-pill i { color: #a78bfa; }

        /* ═══════════════════════════
           RIGHT PANEL — Form Login
        ═══════════════════════════ */
        .login-right {
            width: 100%;
            max-width: 480px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1.5rem;
            overflow-y: auto;
            position: relative;
        }

        @media (min-width: 992px) {
            .login-right { width: 460px; flex-shrink: 0; }
        }

        /* Dekoratif sudut kanan atas */
        .login-right::before {
            content: '';
            position: absolute;
            top: 0; right: 0;
            width: 180px; height: 180px;
            background: radial-gradient(circle, rgba(124,58,237,0.06) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-right::after {
            content: '';
            position: absolute;
            bottom: 0; left: 0;
            width: 150px; height: 150px;
            background: radial-gradient(circle, rgba(6,182,212,0.05) 0%, transparent 70%);
            pointer-events: none;
        }

        .login-form-wrapper {
            width: 100%;
            max-width: 380px;
            position: relative;
            z-index: 1;
            animation: formSlideIn 0.5s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        @keyframes formSlideIn {
            from { opacity: 0; transform: translateX(30px) scale(0.97); }
            to   { opacity: 1; transform: translateX(0) scale(1); }
        }

        /* Mobile: logo di kanan */
        .mobile-logo {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            margin-bottom: 2rem;
        }

        @media (min-width: 992px) {
            .mobile-logo { display: none; }
        }

        .mobile-logo-icon {
            width: 44px; height: 44px;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            color: #fff;
            box-shadow: 0 6px 16px rgba(124,58,237,0.3);
            flex-shrink: 0;
        }

        .form-title {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 1.65rem;
            font-weight: 800;
            color: #1e1b4b;
            margin-bottom: 0.35rem;
        }

        .form-subtitle {
            font-size: 0.85rem;
            color: #9ca3af;
            margin-bottom: 2rem;
        }

        /* Form fields */
        .field-group {
            position: relative;
            margin-bottom: 1rem;
        }

        .field-label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.4rem;
        }

        .field-input {
            width: 100%;
            padding: 0.75rem 1rem 0.75rem 2.8rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 12px;
            font-size: 0.9rem;
            color: #1e1b4b;
            background: #fafafa;
            transition: all 0.2s ease;
            outline: none;
            font-family: 'Inter', sans-serif;
        }

        .field-input:focus {
            border-color: #7c3aed;
            background: #fff;
            box-shadow: 0 0 0 4px rgba(124,58,237,0.1);
        }

        .field-icon {
            position: absolute;
            left: 0.9rem;
            bottom: 0.85rem;
            color: #9ca3af;
            font-size: 1rem;
            pointer-events: none;
            transition: color 0.2s;
        }

        .field-group:focus-within .field-icon { color: #7c3aed; }

        .field-toggle {
            position: absolute;
            right: 0.75rem;
            bottom: 0.5rem;
            background: none;
            border: none;
            padding: 0.35rem;
            color: #9ca3af;
            cursor: pointer;
            border-radius: 6px;
            line-height: 1;
            transition: color 0.2s;
        }

        .field-toggle:hover { color: #7c3aed; }

        /* Login button */
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            border: none;
            border-radius: 12px;
            background: linear-gradient(135deg, #7c3aed, #4f46e5);
            color: #fff;
            font-size: 0.95rem;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            cursor: pointer;
            box-shadow: 0 6px 20px rgba(124,58,237,0.35);
            transition: all 0.25s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            min-height: 52px;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #6d28d9, #4338ca);
            box-shadow: 0 8px 28px rgba(124,58,237,0.5);
            transform: translateY(-2px);
        }

        .btn-login:active { transform: translateY(0); }

        /* Divider */
        .divider {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin: 1.5rem 0 1rem;
            color: #d1d5db;
            font-size: 0.78rem;
        }

        .divider::before, .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: #f3f4f6;
        }

        /* Demo buttons */
        .demo-btn {
            width: 100%;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.65rem 0.9rem;
            border: 1.5px solid #f3f4f6;
            border-radius: 11px;
            background: #fafafa;
            text-decoration: none;
            color: #374151;
            font-size: 0.83rem;
            transition: all 0.2s ease;
            min-height: 52px;
            margin-bottom: 0.5rem;
        }

        .demo-btn:hover {
            border-color: rgba(124,58,237,0.3);
            background: rgba(124,58,237,0.03);
            transform: translateX(4px);
            color: #1e1b4b;
        }

        .demo-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.88rem;
            flex-shrink: 0;
        }

        .demo-arrow {
            margin-left: auto;
            color: #d1d5db;
            transition: transform 0.2s, color 0.2s;
            flex-shrink: 0;
        }

        .demo-btn:hover .demo-arrow {
            transform: translateX(4px);
            color: #7c3aed;
        }

        /* Alert overrides */
        .alert {
            border-radius: 10px;
            font-size: 0.85rem;
            padding: 0.7rem 0.9rem;
            margin-bottom: 1rem;
        }

        /* Remember + tech badge */
        .form-check-input:checked {
            background-color: #7c3aed;
            border-color: #7c3aed;
        }

        /* Responsive */
        @media (max-width: 479px) {
            .login-right { padding: 1.5rem 1.1rem; }
            .form-title { font-size: 1.4rem; }
        }

        @supports (padding: env(safe-area-inset-bottom)) {
            .login-right { padding-bottom: calc(2rem + env(safe-area-inset-bottom)); }
        }
    </style>
</head>
<body>
    <!-- LEFT: Ilustrasi -->
    <div class="login-left">
        <div class="orb orb-1"></div>
        <div class="orb orb-2"></div>
        <div class="orb orb-3"></div>

        <div class="login-left-content">
            <div class="left-logo">
                <i class="bi bi-fingerprint"></i>
            </div>
            <div class="left-tagline">Sistem Manajemen SDM Modern</div>
            <div class="left-headline">
                Pantau Absensi<br><span>Lebih Cerdas &amp; Efisien</span>
            </div>
            <p class="left-description">
                Platform terintegrasi untuk manajemen kehadiran, injeksi data mesin fingerprint, dan kontrol hak akses berbasis role secara dinamis.
            </p>
            <div class="feature-pills">
                <div class="feature-pill"><i class="bi bi-fingerprint"></i> Fingerprint Import</div>
                <div class="feature-pill"><i class="bi bi-shield-lock"></i> RBAC Dinamis</div>
                <div class="feature-pill"><i class="bi bi-graph-up-arrow"></i> Analitik Real-time</div>
                <div class="feature-pill"><i class="bi bi-file-earmark-excel"></i> Export Excel</div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Form -->
    <div class="login-right">
        <div class="login-form-wrapper">
            <!-- Logo mobile -->
            <div class="mobile-logo">
                <div class="mobile-logo-icon">
                    <i class="bi bi-fingerprint"></i>
                </div>
                <div>
                    <div style="font-family:'Plus Jakarta Sans',sans-serif;font-weight:800;font-size:1rem;color:#1e1b4b;">ABSENSI</div>
                    <div style="font-size:0.68rem;color:#9ca3af;">HR & ACCESS SYSTEM</div>
                </div>
            </div>

            <div class="form-title">Selamat Datang 👋</div>
            <div class="form-subtitle">Masuk ke akun Anda untuk melanjutkan</div>

            <!-- Alerts -->
            @if(session('error'))
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    <div>{{ session('error') }}</div>
                </div>
            @endif
            @if(session('success'))
                <div class="alert alert-success d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-check-circle-fill flex-shrink-0"></i>
                    <div>{{ session('success') }}</div>
                </div>
            @endif
            @if($errors->any())
                <div class="alert alert-danger d-flex align-items-center gap-2" role="alert">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0"></i>
                    <div>{{ $errors->first() }}</div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('login') }}" method="POST" id="loginForm" autocomplete="on">
                @csrf

                <div class="field-group">
                    <label class="field-label" for="login">Email atau Username</label>
                    <i class="bi bi-person field-icon"></i>
                    <input type="text"
                           class="field-input @error('login') is-invalid @enderror"
                           id="login" name="login"
                           value="{{ old('login') }}"
                           placeholder="contoh@email.com"
                           required autofocus
                           autocomplete="username">
                </div>

                <div class="field-group">
                    <label class="field-label" for="password">Kata Sandi</label>
                    <i class="bi bi-lock field-icon"></i>
                    <input type="password"
                           class="field-input @error('password') is-invalid @enderror"
                           id="password" name="password"
                           placeholder="••••••••"
                           required
                           autocomplete="current-password">
                    <button type="button" class="field-toggle" id="togglePassword" title="Tampilkan/Sembunyikan">
                        <i class="bi bi-eye-slash" id="toggleIcon"></i>
                    </button>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <label class="d-flex align-items-center gap-2 mb-0" style="cursor:pointer;">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" style="width:16px;height:16px;border-radius:5px;" {{ old('remember') ? 'checked' : '' }}>
                        <span style="font-size:0.82rem;color:#6b7280;">Ingat Saya</span>
                    </label>
                    <span class="badge" style="background:#f3f4f6;color:#9ca3af;font-size:0.68rem;">PHP 8.3 &bull; MySQL</span>
                </div>

                <button type="submit" class="btn-login" id="btnLogin">
                    <span>Masuk ke Sistem</span>
                    <i class="bi bi-arrow-right-short fs-4"></i>
                </button>
            </form>

            <!-- Demo Login -->
            <div class="divider">
                <span style="white-space:nowrap;color:#9ca3af;">⚡ Uji Coba 1-Klik</span>
            </div>

            <a href="{{ route('demo.login', 'superadmin') }}" class="demo-btn" id="demoSuperAdmin">
                <div class="demo-avatar" style="background:rgba(124,58,237,0.12);">
                    <i class="bi bi-shield-check" style="color:#7c3aed;font-size:1rem;"></i>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:0.83rem;line-height:1.2;">Super Admin</div>
                    <div style="font-size:0.72rem;color:#9ca3af;">Akses penuh semua menu & fitur</div>
                </div>
                <i class="bi bi-arrow-right demo-arrow fs-5"></i>
            </a>

            <a href="{{ route('demo.login', 'admin') }}" class="demo-btn" id="demoAdmin">
                <div class="demo-avatar" style="background:rgba(6,182,212,0.12);">
                    <i class="bi bi-person-badge" style="color:#06b6d4;font-size:1rem;"></i>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:0.83rem;line-height:1.2;">Admin HRD</div>
                    <div style="font-size:0.72rem;color:#9ca3af;">Akses berdasarkan izin role</div>
                </div>
                <i class="bi bi-arrow-right demo-arrow fs-5"></i>
            </a>

            <a href="{{ route('demo.login', 'user') }}" class="demo-btn" id="demoUser">
                <div class="demo-avatar" style="background:rgba(107,114,128,0.12);">
                    <i class="bi bi-person" style="color:#6b7280;font-size:1rem;"></i>
                </div>
                <div>
                    <div class="fw-semibold" style="font-size:0.83rem;line-height:1.2;">Karyawan / User</div>
                    <div style="font-size:0.72rem;color:#9ca3af;">Akses terbatas (Dashboard)</div>
                </div>
                <i class="bi bi-arrow-right demo-arrow fs-5"></i>
            </a>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Toggle password
            $('#togglePassword').on('click', function() {
                const pw   = $('#password');
                const icon = $('#toggleIcon');
                const show = pw.attr('type') === 'password';
                pw.attr('type', show ? 'text' : 'password');
                icon.toggleClass('bi-eye-slash', !show).toggleClass('bi-eye text-purple', show);
                if (show) icon.css('color', '#7c3aed');
                else icon.css('color', '');
            });

            // Loading states
            $('#loginForm').on('submit', function() {
                const btn = $('#btnLogin');
                btn.html('<span class="spinner-border spinner-border-sm me-2" role="status"></span>Memproses...');
                btn.prop('disabled', true).css('opacity', '0.8');
            });

            $('.demo-btn').on('click', function() {
                $(this).html(`
                    <span class="spinner-border spinner-border-sm me-2" style="color:#7c3aed;" role="status"></span>
                    <span style="color:#9ca3af;font-size:0.83rem;">Memuat akun demo...</span>
                `);
            });
        });
    </script>
</body>
</html>
