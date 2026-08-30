@extends('layouts.app')

@section('title', $isEmployee ? 'Beranda Karyawan & Presensi' : 'Portal Menu & Aplikasi')
@section('page-title', $isEmployee ? 'Portal Karyawan' : 'Menu Utama')
@section('page-subtitle', $isEmployee ? 'Ringkasan presensi mandiri, jadwal mengajar, dan slip gaji digital Anda' : 'Pusat navigasi modul aplikasi berdasarkan hak akses peran Anda')

@section('styles')
<style>
    /* ══════════════ HERO PORTAL BANNER ══════════════ */
    .portal-hero {
        border-radius: 20px;
        border: none !important;
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #4338ca 100%);
        position: relative;
        overflow: hidden;
        margin-bottom: 1.75rem;
    }

    .portal-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 50% 80% at 5% 50%, rgba(124,58,237,0.45) 0%, transparent 60%),
            radial-gradient(ellipse 40% 60% at 95% 20%, rgba(6,182,212,0.25) 0%, transparent 60%);
        animation: heroGlow 6s ease-in-out infinite alternate;
    }

    @keyframes heroGlow {
        0%   { opacity: 0.7; }
        100% { opacity: 1; }
    }

    .portal-hero .card-body {
        position: relative;
        z-index: 1;
        padding: 1.75rem;
    }

    .clock-pill {
        background: rgba(255,255,255,0.1);
        border: 1px solid rgba(255,255,255,0.18);
        border-radius: 12px;
        padding: 0.5rem 1rem;
        backdrop-filter: blur(10px);
        display: inline-flex;
        align-items: center;
        gap: 0.6rem;
    }

    /* ══════════════ EMPLOYEE CARDS & SCHEDULE WIDGET ══════════════ */
    .schedule-today-card {
        border-radius: 20px;
        background: #ffffff;
        border: 1px solid rgba(99,102,241,0.12) !important;
        box-shadow: 0 4px 20px rgba(99,102,241,0.06);
    }

    .slot-item-pill {
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        border-left: 4px solid #4f46e5;
        border-radius: 12px;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }
    .slot-item-pill:hover {
        background: #f1f5f9;
        border-color: #cbd5e1;
        transform: translateX(3px);
    }

    .kpi-stat-box {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(99,102,241,0.1) !important;
        box-shadow: 0 4px 15px rgba(99,102,241,0.04);
        padding: 1.25rem;
        transition: all 0.25s ease;
    }
    .kpi-stat-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(99,102,241,0.1) !important;
    }

    /* ══════════════ SEARCH & FILTER BAR ══════════════ */
    .search-hub-card {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(99,102,241,0.12) !important;
        box-shadow: 0 4px 20px rgba(99,102,241,0.06);
        margin-bottom: 1.75rem;
    }

    .search-input-wrap {
        position: relative;
    }

    .search-input-wrap input {
        height: 48px;
        border-radius: 12px;
        padding-left: 45px;
        padding-right: 40px;
        font-size: 0.95rem;
        border: 1.5px solid rgba(99,102,241,0.2);
        background: #f8fafc;
        transition: all 0.25s ease;
    }

    .search-input-wrap input:focus {
        background: #ffffff;
        border-color: #7c3aed;
        box-shadow: 0 0 0 4px rgba(124,58,237,0.12);
    }

    .search-icon-pos {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #6366f1;
        font-size: 1.15rem;
        pointer-events: none;
    }

    .search-clear-pos {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        cursor: pointer;
        font-size: 1.1rem;
        border: none;
        background: transparent;
        padding: 4px;
    }
    .search-clear-pos:hover { color: #ef4444; }

    /* Category Filter Pills */
    .filter-pill {
        border-radius: 10px;
        padding: 0.4rem 0.9rem;
        font-size: 0.8rem;
        font-weight: 600;
        border: 1.5px solid rgba(99,102,241,0.15);
        background: #ffffff;
        color: #4b5563;
        cursor: pointer;
        transition: all 0.2s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        user-select: none;
    }

    .filter-pill:hover {
        background: rgba(99,102,241,0.06);
        color: #4f46e5;
        border-color: rgba(99,102,241,0.3);
    }

    .filter-pill.active {
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        color: #ffffff;
        border-color: transparent;
        box-shadow: 0 4px 12px rgba(124,58,237,0.25);
    }

    /* ══════════════ GROUP SECTION ══════════════ */
    .group-section-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 1rem;
        padding-bottom: 0.5rem;
        border-bottom: 1.5px dashed rgba(99,102,241,0.15);
    }

    .group-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.1rem;
        font-weight: 700;
        color: #1e1b4b;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    /* ══════════════ MODULE CARD ══════════════ */
    .module-card {
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid rgba(99,102,241,0.1) !important;
        box-shadow: 0 4px 20px rgba(99,102,241,0.05);
        transition: all 0.28s cubic-bezier(0.4, 0, 0.2, 1);
        display: flex;
        flex-direction: column;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .module-card::before {
        content: '';
        position: absolute;
        top: 0; left: 0; right: 0;
        height: 4px;
        background: var(--module-gradient, linear-gradient(135deg, #7c3aed, #4f46e5));
        opacity: 0;
        transition: opacity 0.25s ease;
    }

    .module-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 16px 36px rgba(99,102,241,0.12) !important;
        border-color: rgba(99,102,241,0.3) !important;
    }

    .module-card:hover::before {
        opacity: 1;
    }

    .module-icon-box {
        width: 48px; height: 48px;
        border-radius: 14px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        color: #ffffff;
        box-shadow: 0 6px 16px rgba(0,0,0,0.12);
        flex-shrink: 0;
        transition: transform 0.25s ease;
    }

    .module-card:hover .module-icon-box {
        transform: scale(1.08) rotate(3deg);
    }

    .module-name {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.05rem;
        font-weight: 700;
        color: #1e1b4b;
        margin-bottom: 0.35rem;
        line-height: 1.3;
    }

    .module-desc {
        font-size: 0.83rem;
        color: #64748b;
        line-height: 1.45;
        margin-bottom: 1.1rem;
        flex-grow: 1;
    }

    .btn-launch-module {
        background: linear-gradient(135deg, #7c3aed, #4f46e5);
        color: #fff;
        border: none;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 600;
        padding: 0.55rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.4rem;
        text-decoration: none;
        transition: all 0.2s ease;
        box-shadow: 0 4px 12px rgba(124,58,237,0.25);
    }

    .btn-launch-module:hover {
        background: linear-gradient(135deg, #6d28d9, #4338ca);
        color: #fff;
        box-shadow: 0 6px 18px rgba(124,58,237,0.35);
        transform: translateX(2px);
    }

    .btn-shortcut-subtle {
        background: rgba(99,102,241,0.06);
        border: 1px solid rgba(99,102,241,0.15);
        color: #4f46e5;
        border-radius: 10px;
        font-size: 0.8rem;
        padding: 0.45rem 0.75rem;
        font-weight: 500;
        text-decoration: none;
        transition: all 0.2s ease;
    }

    .btn-shortcut-subtle:hover {
        background: rgba(99,102,241,0.12);
        color: #4338ca;
    }

    /* Notification badge */
    .badge-pill-alert {
        position: absolute;
        top: 12px;
        right: 12px;
        background: #ef4444;
        color: #fff;
        font-size: 0.68rem;
        font-weight: 700;
        padding: 3px 8px;
        border-radius: 20px;
        box-shadow: 0 2px 8px rgba(239,68,68,0.4);
        animation: pulseAlert 2s infinite;
    }

    @keyframes pulseAlert {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }

    /* ══════════════ RESPONSIVE ══════════════ */
    @media (max-width: 767.98px) {
        .portal-hero .card-body { padding: 1.25rem; }
        .group-title { font-size: 1rem; }
    }
</style>
@endsection

@section('content')

@if($isEmployee && $employeeData)
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 🌟 DASHBOARD KHUSUS GURU & KARYAWAN (EMPLOYEE SELF-SERVICE) --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

{{-- 1. HERO BANNER PROFIL KARYAWAN --}}
<div class="portal-hero shadow">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border border-2 border-white shadow-sm" style="width: 60px; height: 60px; object-fit: cover;">
                    <div>
                        <div class="d-flex flex-wrap gap-2 mb-1">
                            <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);font-size:0.75rem;">
                                <i class="bi bi-person-badge me-1"></i>NIP: {{ $user->nip ?: '-' }}
                            </span>
                            <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background:rgba(16,185,129,0.2);color:#6ee7b7;border:1px solid rgba(16,185,129,0.2);font-size:0.75rem;">
                                <i class="bi bi-building me-1"></i>{{ $user->units->pluck('name')->implode(', ') ?: 'Yayasan' }}
                            </span>
                        </div>
                        <h3 class="fw-bold text-white mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.2rem,3vw,1.6rem);">
                            Selamat Datang, {{ $user->name }}! 👋
                        </h3>
                        <div class="text-white-50 small mt-1">
                            {{ $user->position ?: 'Guru / Pegawai' }} • {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-lg-end">
                    <div class="clock-pill">
                        <i class="bi bi-clock-history text-white-50 fs-5"></i>
                        <div>
                            <div style="font-size:0.65rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.08em;">Waktu Server Realtime</div>
                            <div class="fw-bold text-white" id="portalLiveClock" style="font-size:1.05rem;font-family:'Plus Jakarta Sans',monospace;">
                                {{ now()->format('H:i:s') }} WIB
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2. WIDGET UTAMA: JADWAL MENGAJAR HARI INI --}}
<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card schedule-today-card p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
                        <i class="bi bi-calendar3-event text-primary"></i>
                        <span>Jadwal Mengajar Hari Ini</span>
                    </h5>
                    <div class="text-muted small mt-0.5">Daftar alokasi sesi mengajar Anda untuk hari {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-semibold" style="font-size:0.8rem;">
                        <i class="bi bi-clock-history me-1"></i>{{ $employeeData['today_slots']->count() }} Sesi Mengajar Terdaftar
                    </span>
                </div>
            </div>

            <div class="schedule-slot-list mb-3">
                <div class="row g-3">
                    @forelse($employeeData['today_slots'] as $slot)
                        @php
                            $unitColor = $slot->unit?->color ?? '#4f46e5';
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="slot-item-pill h-100 d-flex flex-column justify-content-between p-3" style="border-left-color: {{ $unitColor }};">
                                <div>
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge font-monospace fw-bold px-2.5 py-1 text-dark" style="background:#e0e7ff; font-size:0.82rem;">
                                            <i class="bi bi-clock me-1"></i>{{ $slot->formatted_start_time }} - {{ $slot->formatted_end_time }}
                                        </span>
                                        <span class="badge rounded-pill px-2.5 py-1 text-uppercase" style="background-color: {{ $unitColor }}20; color: {{ $unitColor }}; border: 1px solid {{ $unitColor }}40; font-size:0.72rem;">
                                            {{ $slot->unit?->name ?? 'Yayasan' }}
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1" style="font-size:0.95rem;">
                                        {{ $slot->subject ?: 'Sesi Mengajar Reguler' }}
                                    </h6>
                                    @if($slot->notes)
                                        <div class="text-muted small mb-2"><i class="bi bi-geo-alt me-1"></i>{{ $slot->notes }}</div>
                                    @endif
                                </div>
                                <div class="pt-2 border-top border-light mt-2 d-flex justify-content-between align-items-center text-muted small">
                                    <span>Durasi Sesi</span>
                                    <span class="fw-semibold text-primary">{{ $slot->duration_minutes }} Menit</span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <div class="text-center py-4 text-muted bg-light rounded-4 border border-dashed">
                                <i class="bi bi-cup-hot text-muted opacity-50" style="font-size: 2.5rem;"></i>
                                <div class="fw-semibold mt-2 text-dark">Tidak ada sesi mengajar hari ini</div>
                                <div class="small text-muted">Hari ini adalah jadwal tenang / tidak ada slot mengajar terdaftar pada jadwal Anda.</div>
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>

            <div class="pt-3 border-top d-flex flex-wrap justify-content-between align-items-center gap-2">
                <a href="{{ route('my-schedule.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="bi bi-calendar2-range me-1"></i> Lihat Jadwal Mingguan Saya (Senin - Minggu)
                </a>
                <a href="{{ route('schedule-info.export-personal-pdf', $user->id) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm" target="_blank">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh Jadwal PDF
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 3. STATISTIK KEHADIRAN BULAN BERJALAN & SLIP GAJI --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="kpi-stat-box text-center">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Hari Hadir</div>
            <div class="fw-bold text-success fs-3">{{ $employeeData['monthly_present'] }} <span class="fs-6 text-muted fw-normal">Hari</span></div>
            <div class="small text-muted">Bulan {{ \Carbon\Carbon::now()->translatedFormat('F') }}</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat-box text-center">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Terlambat</div>
            <div class="fw-bold text-warning fs-3">{{ $employeeData['monthly_late'] }} <span class="fs-6 text-muted fw-normal">Kali</span></div>
            <div class="small text-muted">Toleransi s.d 15 Mnt</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat-box text-center">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Izin / Sakit</div>
            <div class="fw-bold text-info fs-3">{{ $employeeData['monthly_permit'] }} <span class="fs-6 text-muted fw-normal">Hari</span></div>
            <div class="small text-muted">Tercatat di Sistem</div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="kpi-stat-box text-center">
            <div class="text-muted small text-uppercase fw-semibold mb-1">Persentase Kehadiran</div>
            <div class="fw-bold text-primary fs-3">{{ $employeeData['monthly_percentage'] }}%</div>
            <div class="small text-muted">{{ $employeeData['monthly_percentage'] >= 90 ? 'Performa Sangat Baik 👏' : 'Tingkatkan Kehadiran' }}</div>
        </div>
    </div>
</div>

{{-- 4. KARTU AKSES CEPAT FITUR KARYAWAN --}}
<div class="group-section-header">
    <div class="group-title">
        <i class="bi bi-grid-fill text-primary"></i>
        <span>Akses Cepat Modul Karyawan</span>
    </div>
</div>

<div class="row g-3 mb-4">
    {{-- Card Jadwal --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card module-card p-3" style="--module-gradient: linear-gradient(135deg, #0891b2, #0e7490);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="module-icon-box" style="background: linear-gradient(135deg, #0891b2, #0e7490);">
                    <i class="bi bi-calendar2-week-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Jadwal Mengajar Saya</h6>
                    <span class="text-muted small">{{ $employeeData['total_weekly_hours'] }} Jam / Minggu</span>
                </div>
            </div>
            <p class="small text-muted mb-3 flex-grow-1">Lihat jadwal harian dan mingguan per jam Senin sampai Minggu.</p>
            <div class="d-flex gap-2">
                <a href="{{ route('my-schedule.index') }}" class="btn btn-sm btn-outline-primary rounded-pill flex-grow-1">Buka</a>
                <a href="{{ route('schedule-info.export-personal-pdf', $user->id) }}" class="btn btn-sm btn-light border rounded-pill" title="Cetak PDF"><i class="bi bi-download"></i></a>
            </div>
        </div>
    </div>

    {{-- Card Riwayat Absensi --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card module-card p-3" style="--module-gradient: linear-gradient(135deg, #4f46e5, #3730a3);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="module-icon-box" style="background: linear-gradient(135deg, #4f46e5, #3730a3);">
                    <i class="bi bi-calendar2-check-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Riwayat Presensi</h6>
                    <span class="text-muted small">Log Masuk & Pulang</span>
                </div>
            </div>
            <p class="small text-muted mb-3 flex-grow-1">Cek riwayat absensi harian dan status kehadiran scan fingerprint.</p>
            <a href="{{ route('attendances.index') }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">Lihat Riwayat</a>
        </div>
    </div>

    {{-- Card Rekap & Analitik --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card module-card p-3" style="--module-gradient: linear-gradient(135deg, #7c3aed, #5b21b6);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="module-icon-box" style="background: linear-gradient(135deg, #7c3aed, #5b21b6);">
                    <i class="bi bi-person-lines-fill"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Grafik Kehadiran</h6>
                    <span class="text-muted small">Analisis & Tren</span>
                </div>
            </div>
            <p class="small text-muted mb-3 flex-grow-1">Pantau tren ketepatan waktu, durasi kerja, dan grafik bulanan Anda.</p>
            <a href="{{ route('attendance-recap.chart', ['user' => $user->id, 'start_date' => now()->startOfMonth()->toDateString(), 'end_date' => now()->endOfMonth()->toDateString()]) }}" class="btn btn-sm btn-outline-primary rounded-pill w-100">Buka Grafik</a>
        </div>
    </div>

    {{-- Card Slip Gaji --}}
    <div class="col-12 col-md-6 col-xl-3">
        <div class="card module-card p-3" style="--module-gradient: linear-gradient(135deg, #059669, #047857);">
            <div class="d-flex align-items-center gap-3 mb-2">
                <div class="module-icon-box" style="background: linear-gradient(135deg, #059669, #047857);">
                    <i class="bi bi-wallet2"></i>
                </div>
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Slip Gaji Saya</h6>
                    <span class="text-muted small">Honor & Tunjangan</span>
                </div>
            </div>
            <p class="small text-muted mb-3 flex-grow-1">Rincian honor mengajar per jam, tunjangan, dan unduh slip gaji digital.</p>
            <a href="{{ route('payrolls.index') }}" class="btn btn-sm btn-outline-success rounded-pill w-100">Lihat Slip Gaji</a>
        </div>
    </div>
@else
{{-- ══════════════════════════════════════════════════════════════════════════ --}}
{{-- 👑 DASHBOARD PORTAL MANAJERIAL (SUPER ADMIN & ADMINISTRATOR HRD) --}}
{{-- ══════════════════════════════════════════════════════════════════════════ --}}

{{-- 1. HERO PORTAL BANNER --}}
<div class="portal-hero shadow">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-7">
                <div class="d-flex flex-wrap gap-2 mb-2">
                    <span class="badge px-3 py-1 rounded-pill fw-semibold"
                          style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);font-size:0.75rem;">
                        <i class="bi bi-shield-check me-1"></i>Peran: {{ $stats['role_name'] }}
                    </span>
                    <span class="badge px-3 py-1 rounded-pill fw-semibold"
                          style="background:rgba(16,185,129,0.2);color:#6ee7b7;border:1px solid rgba(16,185,129,0.2);font-size:0.75rem;">
                        <i class="bi bi-grid-fill me-1"></i>{{ $stats['total_modules'] }} Modul Tersedia
                    </span>
                </div>
                <h3 class="fw-bold text-white mb-2" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.3rem,4vw,1.8rem);">
                    Selamat Datang, {{ $user->name }}! 👋
                </h3>
                <p class="mb-0 text-white-50" style="font-size:0.9rem;max-width:480px;">
                    Silakan pilih menu aplikasi di bawah ini atau gunakan pencarian cepat untuk membuka modul sistem absensi & HR.
                </p>
            </div>
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-lg-end">
                    <div class="clock-pill">
                        <i class="bi bi-clock-history text-white-50 fs-5"></i>
                        <div>
                            <div style="font-size:0.65rem;color:rgba(255,255,255,0.5);text-transform:uppercase;letter-spacing:0.08em;">Waktu Sistem Live</div>
                            <div class="fw-bold text-white" id="portalLiveClock" style="font-size:1rem;font-family:'Plus Jakarta Sans',monospace;">
                                {{ now()->format('H:i:s') }} WIB
                            </div>
                        </div>
                    </div>

                    @if($isSuperAdmin || can_do('roles', 'view'))
                    <a href="{{ route('roles.index') }}"
                       class="btn btn-sm fw-semibold"
                       style="background:rgba(255,255,255,0.12);border:1px solid rgba(255,255,255,0.2);color:#fff;border-radius:12px;backdrop-filter:blur(8px);"
                       title="Konfigurasi Hak Akses & Matriks Role">
                        <i class="bi bi-shield-lock-fill me-1"></i> Atur Hak Akses
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 2. SEARCH & CATEGORY FILTER BAR --}}
<div class="card search-hub-card">
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 align-items-center">
            {{-- Input Pencarian Realtime --}}
            <div class="col-12 col-md-6 col-lg-5">
                <div class="search-input-wrap">
                    <i class="bi bi-search search-icon-pos"></i>
                    <input type="text"
                           id="portalSearchInput"
                           class="form-control"
                           placeholder="Cari modul menu, fitur, atau aksi..."
                           autocomplete="off">
                    <button type="button" id="portalSearchClear" class="search-clear-pos d-none" title="Hapus pencarian">
                        <i class="bi bi-x-circle-fill"></i>
                    </button>
                </div>
            </div>

            {{-- Kategori Filter Pills --}}
            <div class="col-12 col-md-6 col-lg-7">
                <div class="d-flex flex-wrap gap-1.5 align-items-center justify-content-md-end" id="categoryFilterContainer">
                    <button type="button" class="filter-pill active" data-category="all">
                        <i class="bi bi-grid"></i> Semua ({{ $stats['total_modules'] }})
                    </button>
                    @foreach($menuGroups as $grp)
                        <button type="button" class="filter-pill" data-category="grp-{{ $grp['group_id'] }}">
                            <i class="{{ $grp['group_icon'] }}"></i> {{ $grp['group_name'] }}
                        </button>
                    @endforeach
                    <button type="button" class="filter-pill" data-category="grp-account">
                        <i class="bi bi-person-gear"></i> Akun
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 3. GRUP-GRUP MENU & MODUL ADMIN --}}
<div id="moduleGridWrapper">
    @forelse($menuGroups as $group)
        <div class="menu-group-block mb-4" id="group-block-{{ $group['group_id'] }}" data-group-code="grp-{{ $group['group_id'] }}">
            <div class="group-section-header">
                <div class="group-title">
                    <i class="{{ $group['group_icon'] }}" style="color:#7c3aed;"></i>
                    <span>{{ $group['group_name'] }}</span>
                    <span class="badge bg-light text-muted border rounded-pill" style="font-size:0.7rem;font-weight:600;">
                        {{ count($group['items']) }} Modul
                    </span>
                </div>
            </div>

            <div class="row g-3">
                @foreach($group['items'] as $item)
                    @php
                        $isWorkScheduleAlert = ($item['code'] === 'work-schedules') && ($stats['unconfigured_count'] > 0);
                    @endphp
                    <div class="col-12 col-md-6 col-xl-4 module-item-entry"
                         data-module-name="{{ strtolower($item['name']) }}"
                         data-module-desc="{{ strtolower($item['description']) }}"
                         data-module-category="grp-{{ $group['group_id'] }}">
                        <div class="card module-card p-3 p-lg-4" style="--module-gradient: {{ $item['gradient'] }};">
                            {{-- Notifikasi Badge Khusus --}}
                            @if($isWorkScheduleAlert)
                                <span class="badge-pill-alert" title="{{ $stats['unconfigured_count'] }} Pegawai Belum Diatur Jam Kerjanya">
                                    <i class="bi bi-exclamation-circle-fill me-1"></i>{{ $stats['unconfigured_count'] }} Perlu Diatur
                                </span>
                            @endif

                            {{-- Header Card --}}
                            <div class="d-flex align-items-start gap-3 mb-3">
                                <div class="module-icon-box" style="background: {{ $item['gradient'] }};">
                                    <i class="{{ $item['icon'] }}"></i>
                                </div>
                                <div class="overflow-hidden">
                                    <div class="d-flex flex-wrap gap-1 mb-1">
                                        <span class="badge {{ $item['badge_class'] }}" style="font-size:0.68rem;padding:2px 7px;">
                                            {{ $item['badge'] }}
                                        </span>
                                        <span class="badge bg-light text-muted border" style="font-size:0.68rem;padding:2px 6px;">
                                            {{ $item['access_level'] }}
                                        </span>
                                    </div>
                                    <h5 class="module-name text-truncate" title="{{ $item['name'] }}">
                                        {{ $item['name'] }}
                                    </h5>
                                </div>
                            </div>

                            {{-- Deskripsi Modul --}}
                            <p class="module-desc">
                                {{ $item['description'] }}
                            </p>

                            {{-- Action Buttons Footer --}}
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto gap-2">
                                <a href="{{ $item['url'] }}" class="btn-launch-module flex-grow-1">
                                    <span>Buka Menu</span>
                                    <i class="bi bi-arrow-right"></i>
                                </a>

                                @if($item['can_export'] && $item['export_url'])
                                    <a href="{{ $item['export_url'] }}"
                                       class="btn-shortcut-subtle"
                                       title="Ekspor Data Modul Ini">
                                        <i class="bi bi-file-earmark-arrow-down-fill me-1"></i>Ekspor
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @empty
        <div class="card p-5 text-center shadow-sm" style="border-radius:16px;">
            <i class="bi bi-shield-slash" style="font-size:3rem;color:#cbd5e1;"></i>
            <h5 class="fw-bold mt-3 text-muted">Belum ada modul yang dapat diakses</h5>
            <p class="text-muted small">Silakan hubungi Administrator untuk mengatur hak akses akun Anda.</p>
        </div>
    @endforelse

    {{-- GRUP PERSONAL / AKUN --}}
    <div class="menu-group-block mb-4" id="group-block-account" data-group-code="grp-account">
        <div class="group-section-header">
            <div class="group-title">
                <i class="bi bi-person-circle" style="color:#0284c7;"></i>
                <span>Pengaturan Akun Personal</span>
            </div>
        </div>
        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-4 module-item-entry"
                 data-module-name="{{ strtolower($accountModule['name']) }}"
                 data-module-desc="{{ strtolower($accountModule['description']) }}"
                 data-module-category="grp-account">
                <div class="card module-card p-3 p-lg-4" style="--module-gradient: {{ $accountModule['gradient'] }};">
                    <div class="d-flex align-items-start gap-3 mb-3">
                        <div class="module-icon-box" style="background: {{ $accountModule['gradient'] }};">
                            <i class="{{ $accountModule['icon'] }}"></i>
                        </div>
                        <div class="overflow-hidden">
                            <div class="d-flex flex-wrap gap-1 mb-1">
                                <span class="badge {{ $accountModule['badge_class'] }}" style="font-size:0.68rem;padding:2px 7px;">
                                    {{ $accountModule['badge'] }}
                                </span>
                            </div>
                            <h5 class="module-name text-truncate">
                                {{ $accountModule['name'] }}
                            </h5>
                        </div>
                    </div>
                    <p class="module-desc">
                        {{ $accountModule['description'] }}
                    </p>
                    <div class="d-flex align-items-center justify-content-between pt-2 border-top mt-auto gap-2">
                        <a href="{{ $accountModule['url'] }}" class="btn-launch-module flex-grow-1" style="background:linear-gradient(135deg, #0284c7, #0369a1);">
                            <span>Buka Profil</span>
                            <i class="bi bi-arrow-right"></i>
                        </a>
                        <a href="javascript:void(0);" onclick="$('#logout-form').submit();" class="btn-shortcut-subtle text-danger" title="Keluar dari sesi aplikasi">
                            <i class="bi bi-box-arrow-right me-1"></i>Logout
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- TAMPILAN KETIKA HASIL SEARCH KOSONG --}}
    <div id="portalSearchEmptyState" class="card p-5 text-center shadow-sm d-none" style="border-radius:18px;">
        <i class="bi bi-search" style="font-size:3rem;color:#94a3b8;margin-bottom:0.75rem;"></i>
        <h5 class="fw-bold text-dark mb-1">Modul menu tidak ditemukan</h5>
        <p class="text-muted small mb-3">Tidak ada menu atau fitur yang cocok dengan kata kunci "<span id="portalEmptyQueryText" class="fw-semibold text-primary"></span>".</p>
        <div>
            <button type="button" id="btnResetSearchState" class="btn btn-outline-primary btn-sm px-3 rounded-pill">
                <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pencarian
            </button>
        </div>
    </div>
</div>

@endif

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // 1. Live Clock
    setInterval(() => {
        const n = new Date();
        const t = [n.getHours(), n.getMinutes(), n.getSeconds()].map(v => String(v).padStart(2,'0')).join(':');
        $('#portalLiveClock').text(t + ' WIB');
        if ($('#clockInLiveTime').length) {
            $('#clockInLiveTime').text(t + ' WIB');
        }
    }, 1000);

    // 2. Realtime Instant Search & Category Filter Logic (Admin)
    const searchInput = $('#portalSearchInput');
    const searchClear = $('#portalSearchClear');
    const emptyState  = $('#portalSearchEmptyState');
    const emptyQuery  = $('#portalEmptyQueryText');
    const moduleItems = $('.module-item-entry');
    const groupBlocks = $('.menu-group-block');
    let activeCategory = 'all';

    function applyFilter() {
        if (!searchInput.length) return;
        const query = searchInput.val().trim().toLowerCase();

        if (query.length > 0) {
            searchClear.removeClass('d-none');
        } else {
            searchClear.addClass('d-none');
        }

        let totalVisible = 0;

        moduleItems.each(function() {
            const item = $(this);
            const name = item.attr('data-module-name') || '';
            const desc = item.attr('data-module-desc') || '';
            const cat  = item.attr('data-module-category') || '';

            const matchQuery = (query === '') || name.includes(query) || desc.includes(query);
            const matchCategory = (activeCategory === 'all') || (cat === activeCategory);

            if (matchQuery && matchCategory) {
                item.removeClass('d-none');
                totalVisible++;
            } else {
                item.addClass('d-none');
            }
        });

        groupBlocks.each(function() {
            const grp = $(this);
            const visibleInGroup = grp.find('.module-item-entry:not(.d-none)').length;
            if (visibleInGroup > 0) {
                grp.removeClass('d-none');
            } else {
                grp.addClass('d-none');
            }
        });

        if (totalVisible === 0) {
            emptyQuery.text(query || 'kategori yang dipilih');
            emptyState.removeClass('d-none');
        } else {
            emptyState.addClass('d-none');
        }
    }

    searchInput.on('input', applyFilter);

    searchClear.on('click', function() {
        searchInput.val('').trigger('input').focus();
    });

    $('#btnResetSearchState').on('click', function() {
        searchInput.val('');
        activeCategory = 'all';
        $('.filter-pill').removeClass('active');
        $('.filter-pill[data-category="all"]').addClass('active');
        applyFilter();
    });

    $('.filter-pill').on('click', function() {
        $('.filter-pill').removeClass('active');
        $(this).addClass('active');
        activeCategory = $(this).attr('data-category');
        applyFilter();
    });

    $(document).on('keydown', function(e) {
        if (e.key === '/' && !$(e.target).is('input, textarea, select')) {
            e.preventDefault();
            searchInput.focus();
        }
    });
});
</script>
@endsection

