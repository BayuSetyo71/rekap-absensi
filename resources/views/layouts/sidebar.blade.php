<aside class="app-sidebar" id="appSidebar">
    <div class="mesh-orb mesh-orb-1 d-lg-none"></div>
    <div class="mesh-orb mesh-orb-2 d-lg-none"></div>

    <!-- Brand -->
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon">
            <i class="bi bi-fingerprint"></i>
        </div>
        <div>
            <div class="fw-bold text-white" style="font-family: 'Plus Jakarta Sans', sans-serif; font-size: 0.95rem; line-height:1.1;">ABSENSI</div>
            <div style="font-size: 0.6rem; color: rgba(255,255,255,0.35); letter-spacing:0.1em;">HR & ACCESS SYSTEM</div>
        </div>
        <button class="sidebar-close-btn" id="sidebarCloseBtn" type="button" title="Tutup">
            <i class="bi bi-x-lg"></i>
        </button>
    </a>

    <!-- User Profile -->
    <div class="sidebar-user">
        <img src="{{ auth()->user()->avatar_url }}"
             alt="{{ auth()->user()->name }}"
             class="sidebar-user-avatar">
        <div class="overflow-hidden">
            <div class="text-white fw-semibold text-truncate" style="font-size: 0.82rem; max-width: 150px;">
                {{ auth()->user()->name }}
            </div>
            <div class="d-flex align-items-center gap-1 mt-1">
                <span class="d-inline-block rounded-circle" style="width:6px;height:6px;background:#10b981;box-shadow:0 0 6px #10b981;flex-shrink:0;"></span>
                <span style="font-size:0.65rem; color:rgba(255,255,255,0.45);">{{ auth()->user()->role?->display_name ?? 'User' }}</span>
            </div>
        </div>
    </div>

    <!-- Pencarian Menu Realtime di Sidebar -->
    <div class="sidebar-search px-3 mb-2">
        <div class="position-relative">
            <input type="text"
                   id="sidebarSearchInput"
                   class="form-control form-control-sm text-white"
                   placeholder="Cari menu..."
                   style="background: rgba(255,255,255,0.08); border: 1px solid rgba(255,255,255,0.12); font-size: 0.78rem; padding-left: 30px; padding-right: 25px; border-radius: 8px; color: #fff;">
            <i class="bi bi-search position-absolute top-50 start-0 translate-middle-y ms-2 text-white-50" style="font-size: 0.75rem;"></i>
            <button type="button" id="sidebarSearchClear" class="btn btn-link position-absolute top-50 end-0 translate-middle-y me-1 p-0 text-white-50 d-none" style="text-decoration: none; font-size: 0.75rem;">
                <i class="bi bi-x-circle-fill"></i>
            </button>
        </div>
    </div>

    <!-- Navigation -->
    <ul class="sidebar-menu" id="sidebarMenuList">
        <li class="sidebar-header" id="sidebarMenuHeaderMain">Menu Navigasi</li>

        @php
            $userMenus = get_user_menus();
            $unconfiguredCount = get_unconfigured_schedules_count();
        @endphp

        @forelse($userMenus as $menu)
            @php
                $hasChildren  = $menu->children->isNotEmpty();
                $isParentActive = $menu->isCurrentRoute() || $menu->children->contains(fn($c) => $c->isCurrentRoute());
                $hasUnconfiguredChild = $menu->children->contains(fn($c) => $c->code === 'work-schedules') && ($unconfiguredCount > 0);
            @endphp

            @if($hasChildren)
                <li class="sidebar-item nav-menu-entry" data-menu-name="{{ strtolower($menu->name) }}">
                    <a class="sidebar-link {{ $isParentActive ? 'active' : '' }}"
                       data-bs-toggle="collapse"
                       href="#submenu-{{ $menu->id }}"
                       role="button"
                       aria-expanded="{{ $isParentActive ? 'true' : 'false' }}">
                        <i class="{{ $menu->icon }}"></i>
                        <span class="flex-grow-1">{{ $menu->name }}</span>
                        @if($hasUnconfiguredChild)
                            <span class="badge bg-danger rounded-pill px-1.5 py-0.5 me-1" style="font-size: 0.65rem; font-weight: 700;" title="{{ $unconfiguredCount }} pegawai belum diatur jam kerjanya">
                                {{ $unconfiguredCount }}
                            </span>
                        @endif
                        <i class="bi bi-chevron-down ms-auto {{ $isParentActive ? 'rotate-180' : '' }}" style="font-size:0.72rem;transition:transform 0.25s;flex-shrink:0;"></i>
                    </a>
                    <div class="collapse {{ $isParentActive ? 'show' : '' }}" id="submenu-{{ $menu->id }}">
                        <ul class="sidebar-submenu">
                            @foreach($menu->children as $child)
                                <li class="nav-submenu-entry" data-menu-name="{{ strtolower($child->name) }}">
                                    <a href="{{ $child->link }}" class="sidebar-link {{ $child->isCurrentRoute() ? 'active' : '' }}">
                                        <i class="{{ $child->icon }}"></i>
                                        <span class="flex-grow-1">{{ $child->name }}</span>
                                        @if($child->code === 'work-schedules' && $unconfiguredCount > 0)
                                            <span class="badge bg-danger rounded-pill px-2 py-0.5 ms-auto" style="font-size: 0.65rem; font-weight: 700;" title="{{ $unconfiguredCount }} pegawai belum diatur jam kerjanya">
                                                {{ $unconfiguredCount }}
                                            </span>
                                        @endif
                                    </a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </li>
            @else
                <li class="sidebar-item nav-menu-entry" data-menu-name="{{ strtolower($menu->name) }}">
                    <a href="{{ $menu->link }}" class="sidebar-link {{ $menu->isCurrentRoute() ? 'active' : '' }}">
                        <i class="{{ $menu->icon }}"></i>
                        <span class="flex-grow-1">{{ $menu->name }}</span>
                        @if($menu->code === 'work-schedules' && $unconfiguredCount > 0)
                            <span class="badge bg-danger rounded-pill px-2 py-0.5 ms-auto" style="font-size: 0.65rem; font-weight: 700;" title="{{ $unconfiguredCount }} pegawai belum diatur jam kerjanya">
                                {{ $unconfiguredCount }}
                            </span>
                        @endif
                    </a>
                </li>
            @endif
        @empty
            <li class="text-center py-4">
                <i class="bi bi-shield-slash" style="font-size:1.5rem;color:rgba(255,255,255,0.2);"></i>
                <div style="font-size:0.75rem;color:rgba(255,255,255,0.25);margin-top:0.5rem;">Tidak ada menu tersedia</div>
            </li>
        @endforelse

        <!-- Pesan jika hasil pencarian kosong -->
        <li class="text-center py-4 d-none" id="sidebarSearchEmpty">
            <i class="bi bi-search text-white-50" style="font-size:1.4rem;"></i>
            <div style="font-size:0.75rem;color:rgba(255,255,255,0.4);margin-top:0.3rem;">Menu tidak ditemukan</div>
        </li>

        <li class="sidebar-header mt-2" id="sidebarMenuHeaderAccount">Akun</li>
        <li class="sidebar-item nav-menu-entry" data-menu-name="profil saya akun">
            <a href="{{ route('profile.index') }}" class="sidebar-link {{ request()->routeIs('profile.*') ? 'active' : '' }}">
                <i class="bi bi-person-circle"></i>
                <span>Profil Saya</span>
            </a>
        </li>
        <li class="sidebar-item nav-menu-entry" data-menu-name="keluar logout">
            <a href="javascript:void(0);" onclick="$('#logout-form').submit();" class="sidebar-link" style="color:rgba(248,113,113,0.7);">
                <i class="bi bi-box-arrow-right"></i>
                <span>Keluar</span>
            </a>
        </li>
    </ul>
</aside>

<script>
    // Realtime Search Filter Menu di Sidebar
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('sidebarSearchInput');
        const searchClear = document.getElementById('sidebarSearchClear');
        const emptyNotice = document.getElementById('sidebarSearchEmpty');
        const menuEntries = document.querySelectorAll('.nav-menu-entry');
        const headerMain = document.getElementById('sidebarMenuHeaderMain');
        const headerAccount = document.getElementById('sidebarMenuHeaderAccount');

        if (!searchInput) return;

        searchInput.addEventListener('input', function() {
            const query = this.value.trim().toLowerCase();

            if (query.length > 0) {
                searchClear.classList.remove('d-none');
            } else {
                searchClear.classList.add('d-none');
            }

            let visibleCount = 0;

            menuEntries.forEach(entry => {
                const parentName = entry.getAttribute('data-menu-name') || '';
                const subEntries = entry.querySelectorAll('.nav-submenu-entry');
                let matchInSubmenu = false;

                if (subEntries.length > 0) {
                    subEntries.forEach(sub => {
                        const subName = sub.getAttribute('data-menu-name') || '';
                        if (subName.includes(query) || parentName.includes(query)) {
                            sub.classList.remove('d-none');
                            matchInSubmenu = true;
                        } else {
                            sub.classList.add('d-none');
                        }
                    });

                    // Jika ada submenu yang cocok atau parent cocok
                    if (matchInSubmenu || parentName.includes(query)) {
                        entry.classList.remove('d-none');
                        visibleCount++;
                        // Otomatis buka collapse saat mencari
                        const collapseEl = entry.querySelector('.collapse');
                        if (collapseEl && !collapseEl.classList.contains('show')) {
                            collapseEl.classList.add('show');
                        }
                    } else {
                        entry.classList.add('d-none');
                    }
                } else {
                    if (parentName.includes(query)) {
                        entry.classList.remove('d-none');
                        visibleCount++;
                    } else {
                        entry.classList.add('d-none');
                    }
                }
            });

            if (visibleCount === 0 && query.length > 0) {
                emptyNotice.classList.remove('d-none');
                if (headerMain) headerMain.classList.add('d-none');
                if (headerAccount) headerAccount.classList.add('d-none');
            } else {
                emptyNotice.classList.add('d-none');
                if (headerMain) headerMain.classList.remove('d-none');
                if (headerAccount) headerAccount.classList.remove('d-none');
            }
        });

        searchClear.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            searchInput.focus();
        });
    });
</script>
