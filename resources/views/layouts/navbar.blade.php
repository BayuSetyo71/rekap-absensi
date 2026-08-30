<header class="app-header">
    <!-- Kiri -->
    <div class="d-flex align-items-center gap-2">
        <button class="d-lg-none btn btn-sm"
                id="sidebarToggle"
                style="width:38px;height:38px;padding:0;border-radius:10px;border:1.5px solid rgba(99,102,241,0.2);background:rgba(99,102,241,0.06);color:#4f46e5;">
            <i class="bi bi-list fs-4"></i>
        </button>
        <div>
            <div class="fw-bold" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:0.95rem;color:#1e1b4b;line-height:1.2;">
                @yield('page-title', 'Dashboard')
            </div>
            <div class="d-none d-sm-block text-muted" style="font-size:0.75rem;">
                @yield('page-subtitle', 'Sistem Manajemen Absensi')
            </div>
        </div>
    </div>

    <!-- Kanan -->
    <div class="d-flex align-items-center gap-2">
        <!-- Tanggal (md+) -->
        <div class="d-none d-md-flex align-items-center gap-2 px-3 py-1 rounded-pill border"
             style="background:rgba(99,102,241,0.04);border-color:rgba(99,102,241,0.15)!important;font-size:0.8rem;color:#4f46e5;">
            <i class="bi bi-calendar3"></i>
            <span>{{ now()->translatedFormat('l, d F Y') }}</span>
        </div>

        <!-- Notif Bell (dekoratif) -->
        <button class="btn btn-sm d-none d-md-flex position-relative"
                style="width:36px;height:36px;padding:0;border-radius:10px;border:1.5px solid rgba(99,102,241,0.15);background:rgba(99,102,241,0.04);color:#6b7280;">
            <i class="bi bi-bell"></i>
        </button>

        <!-- User Dropdown -->
        <div class="dropdown">
            <a href="#"
               class="d-flex align-items-center text-decoration-none dropdown-toggle"
               id="userDropdown"
               data-bs-toggle="dropdown"
               aria-expanded="false"
               style="gap:0.5rem;">
                <div class="position-relative">
                    <img src="{{ auth()->user()->avatar_url }}"
                         alt="{{ auth()->user()->name }}"
                         class="rounded-circle"
                         width="34" height="34"
                         style="object-fit:cover;border:2px solid rgba(124,58,237,0.4);box-shadow:0 0 12px rgba(124,58,237,0.25);">
                    <span class="position-absolute bottom-0 end-0 rounded-circle"
                          style="width:9px;height:9px;background:#10b981;border:2px solid #f0f4ff;box-shadow:0 0 6px #10b981;"></span>
                </div>
                <div class="d-none d-md-block text-start">
                    <div class="fw-semibold" style="font-size:0.82rem;color:#1e1b4b;line-height:1.2;">{{ auth()->user()->name }}</div>
                    <div style="font-size:0.68rem;color:#9ca3af;">{{ auth()->user()->role?->display_name ?? 'User' }}</div>
                </div>
            </a>

            <ul class="dropdown-menu dropdown-menu-end shadow border-0 mt-2 py-0 overflow-hidden rounded-3"
                aria-labelledby="userDropdown"
                style="min-width:220px;border:1px solid rgba(99,102,241,0.1)!important;">
                <!-- Header -->
                <li class="px-3 py-3" style="background:linear-gradient(135deg,rgba(124,58,237,0.08),rgba(79,70,229,0.04));border-bottom:1px solid rgba(99,102,241,0.08);">
                    <div class="d-flex align-items-center gap-2">
                        <img src="{{ auth()->user()->avatar_url }}"
                             class="rounded-circle"
                             width="36" height="36"
                             style="object-fit:cover;border:2px solid rgba(124,58,237,0.3);">
                        <div>
                            <div class="fw-bold" style="font-size:0.85rem;color:#1e1b4b;">{{ auth()->user()->name }}</div>
                            <div class="text-muted text-truncate" style="font-size:0.72rem;max-width:140px;">{{ auth()->user()->email }}</div>
                            <span class="badge mt-1" style="background:rgba(124,58,237,0.15);color:#7c3aed;font-size:0.65rem;">
                                {{ auth()->user()->role?->display_name ?? 'User' }}
                            </span>
                        </div>
                    </div>
                </li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('profile.index') }}" style="font-size:0.875rem;">
                        <i class="bi bi-person-gear text-muted"></i> Profil & Sandi
                    </a>
                </li>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->canAccessMenu('roles', 'view'))
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2" href="{{ route('roles.index') }}" style="font-size:0.875rem;">
                        <i class="bi bi-shield-lock text-muted"></i> Pengaturan Role
                    </a>
                </li>
                @endif
                <li><hr class="dropdown-divider my-0"></li>
                <li>
                    <a class="dropdown-item py-2 d-flex align-items-center gap-2 text-danger"
                       href="javascript:void(0);"
                       onclick="$('#logout-form').submit();"
                       style="font-size:0.875rem;">
                        <i class="bi bi-box-arrow-right"></i> Keluar
                    </a>
                </li>
            </ul>
        </div>
    </div>
</header>

<form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>
