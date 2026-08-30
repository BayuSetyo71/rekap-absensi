@extends('layouts.app')

@section('title', 'Jadwal Mengajar Saya')
@section('page-title', 'Jadwal Mengajar Saya')
@section('page-subtitle', 'Jadwal sesi mengajar mingguan per jam dari Senin sampai Minggu')

@section('styles')
<style>
    /* ══════════════ HERO SCHEDULE BANNER ══════════════ */
    .schedule-hero {
        border-radius: 20px;
        border: none !important;
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%);
        position: relative;
        overflow: hidden;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 30px rgba(15,23,42,0.15);
    }

    .schedule-hero::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(ellipse 50% 80% at 10% 50%, rgba(99,102,241,0.3) 0%, transparent 60%),
            radial-gradient(ellipse 40% 60% at 90% 20%, rgba(6,182,212,0.2) 0%, transparent 60%);
        pointer-events: none;
    }

    .schedule-hero .card-body {
        position: relative;
        z-index: 1;
        padding: 1.75rem;
    }

    /* ══════════════ METRIC BOXES ══════════════ */
    .metric-card-box {
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid rgba(99,102,241,0.12) !important;
        box-shadow: 0 4px 15px rgba(99,102,241,0.04);
        padding: 1.25rem;
        transition: all 0.25s ease;
        height: 100%;
    }

    .metric-card-box:hover {
        transform: translateY(-3px);
        box-shadow: 0 8px 25px rgba(99,102,241,0.1) !important;
    }

    .metric-icon-wrap {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }

    /* ══════════════ 7-DAY WEEKLY GRID ══════════════ */
    .day-card {
        border-radius: 18px;
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        box-shadow: 0 4px 16px rgba(0,0,0,0.03);
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        overflow: hidden;
    }

    .day-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 28px rgba(99,102,241,0.12) !important;
        border-color: rgba(99,102,241,0.3) !important;
    }

    /* Highlight Hari Ini */
    .day-card.is-today {
        border: 2px solid #6366f1 !important;
        box-shadow: 0 8px 24px rgba(99,102,241,0.2) !important;
        background: #ffffff;
        position: relative;
    }

    .day-card.is-today .day-card-header {
        background: linear-gradient(135deg, #4f46e5, #6366f1);
        color: #ffffff;
    }

    .day-card.is-today .day-card-header .day-title {
        color: #ffffff !important;
    }

    .day-card.is-today .day-card-header .day-badge-count {
        background: rgba(255,255,255,0.2) !important;
        color: #ffffff !important;
        border-color: rgba(255,255,255,0.3) !important;
    }

    .day-card-header {
        padding: 1rem 1.15rem;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    .day-title {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-weight: 800;
        font-size: 1.05rem;
        color: #1e293b;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 0.45rem;
    }

    .slot-item {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        padding: 0.85rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s ease;
        position: relative;
        border-left: 4px solid #6366f1;
    }

    .slot-item:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        border-left-color: #4f46e5;
        transform: translateX(2px);
    }

    .slot-time-badge {
        font-family: 'Plus Jakarta Sans', monospace;
        font-weight: 700;
        font-size: 0.82rem;
        color: #1e1b4b;
        background: #e0e7ff;
        padding: 3px 8px;
        border-radius: 8px;
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
    }

    .empty-day-state {
        padding: 2.5rem 1rem;
        text-align: center;
        color: #94a3b8;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        height: 100%;
    }

    /* Unit color pills */
    .unit-pill {
        font-size: 0.7rem;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
        text-transform: uppercase;
        letter-spacing: 0.04em;
    }

    /* View Switcher */
    .view-switcher-btn.active {
        background: #4f46e5 !important;
        color: #fff !important;
        box-shadow: 0 4px 12px rgba(79,70,229,0.3);
    }
</style>
@endsection

@section('content')

{{-- ══════════════ HERO SCHEDULE BANNER ══════════════ --}}
<div class="schedule-hero">
    <div class="card-body">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-7">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="rounded-circle border border-2 border-white shadow" style="width: 64px; height: 64px; object-fit: cover;">
                    <div>
                        <div class="d-flex flex-wrap gap-2 mb-1">
                            <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background:rgba(255,255,255,0.15);color:#fff;border:1px solid rgba(255,255,255,0.2);font-size:0.75rem;">
                                <i class="bi bi-person-badge me-1"></i>NIP: {{ $user->nip ?: '-' }}
                            </span>
                            <span class="badge px-2.5 py-1 rounded-pill fw-semibold" style="background:rgba(99,102,241,0.25);color:#a5b4fc;border:1px solid rgba(99,102,241,0.3);font-size:0.75rem;">
                                <i class="bi bi-award me-1"></i>{{ $user->position ?: 'Tenaga Pendidik / Guru' }}
                            </span>
                        </div>
                        <h3 class="fw-bold text-white mb-0" style="font-family:'Plus Jakarta Sans',sans-serif;font-size:clamp(1.3rem,3.5vw,1.75rem);">
                            Jadwal Mengajar: {{ $user->name }}
                        </h3>
                        <p class="text-white-50 small mb-0 mt-1">
                            Jadwal mengajar resmi mingguan (Senin - Minggu) lintas jenjang sekolah yayasan.
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-lg-end">
                    <a href="{{ route('schedule-info.export-personal-pdf', $user->id) }}" class="btn btn-primary fw-semibold rounded-pill px-3 shadow-sm" target="_blank">
                        <i class="bi bi-file-earmark-pdf-fill me-1"></i> Unduh Jadwal PDF
                    </a>
                    @if(auth()->user()->isSuperAdmin() || can_do('schedule-info', 'view'))
                        <a href="{{ route('schedule-info.index') }}" class="btn btn-outline-light rounded-pill px-3" title="Lihat Jadwal Seluruh Guru Yayasan">
                            <i class="bi bi-people-fill me-1"></i> Jadwal Seluruh Guru
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ 4 RINGKASAN METRIK MINGGUAN ══════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="metric-card-box d-flex align-items-center gap-3">
            <div class="metric-icon-wrap bg-primary-subtle text-primary">
                <i class="bi bi-clock-history"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Beban Jam / Minggu</div>
                <div class="fw-bold text-dark fs-4">{{ $summary['total_hours'] }} <span class="fs-6 text-muted fw-normal">Jam</span></div>
                <div class="small text-muted" style="font-size:0.75rem;">{{ $summary['total_minutes'] }} Total Menit</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-box d-flex align-items-center gap-3">
            <div class="metric-icon-wrap bg-indigo-subtle text-indigo" style="background:#e0e7ff;color:#4338ca;">
                <i class="bi bi-calendar2-range"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Total Sesi / Minggu</div>
                <div class="fw-bold text-indigo fs-4" style="color:#4338ca;">{{ $summary['total_slots'] }} <span class="fs-6 text-muted fw-normal">Sesi</span></div>
                <div class="small text-muted" style="font-size:0.75rem;">Senin s.d. Minggu</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-box d-flex align-items-center gap-3">
            <div class="metric-icon-wrap bg-success-subtle text-success">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Jadwal Hari Ini ({{ $summary['today_name'] }})</div>
                <div class="fw-bold text-success fs-4">{{ $summary['today_slots_count'] }} <span class="fs-6 text-muted fw-normal">Sesi</span></div>
                <div class="small text-muted" style="font-size:0.75rem;">{{ $summary['today_hours'] }} Jam Mengajar</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="metric-card-box d-flex align-items-center gap-3">
            <div class="metric-icon-wrap bg-warning-subtle text-warning">
                <i class="bi bi-building"></i>
            </div>
            <div>
                <div class="text-muted small fw-semibold text-uppercase" style="font-size:0.7rem;">Jenjang Diamapu</div>
                <div class="fw-bold text-dark fs-5 text-truncate" style="max-width: 140px;" title="{{ $user->units->pluck('name')->implode(', ') }}">
                    {{ $user->units->pluck('code')->implode(', ') ?: 'Umum' }}
                </div>
                <div class="small text-muted" style="font-size:0.75rem;">{{ $user->units->count() }} Unit Sekolah</div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ GRID JADWAL 7 HARI (SENIN S.D. MINGGU) ══════════════ --}}
<div class="d-flex align-items-center justify-content-between mb-3">
    <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2" style="font-family:'Plus Jakarta Sans',sans-serif;">
        <i class="bi bi-calendar2-week text-primary"></i>
        <span>Rincian Sesi Mengajar Sepekan</span>
    </h5>
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-light text-muted border px-2.5 py-1 small">
            <span class="d-inline-block rounded-circle bg-primary me-1" style="width:8px;height:8px;"></span> Highlight: Hari Ini
        </span>
    </div>
</div>

<div class="row g-3">
    @foreach($scheduleByDay as $dayNum => $dayData)
        <div class="col-12 col-md-6 col-xl-4 col-xxl-3">
            <div class="day-card {{ $dayData['is_today'] ? 'is-today' : '' }}">
                {{-- Header Hari --}}
                <div class="day-card-header">
                    <h6 class="day-title">
                        @if($dayData['is_today'])
                            <i class="bi bi-star-fill text-warning"></i>
                        @else
                            <i class="bi bi-calendar3 text-primary"></i>
                        @endif
                        <span>{{ $dayData['day_name'] }}</span>
                        @if($dayData['is_today'])
                            <span class="badge bg-white text-primary rounded-pill px-2 py-0.5" style="font-size:0.65rem;font-weight:800;">HARI INI</span>
                        @endif
                    </h6>
                    <span class="badge bg-light text-dark border day-badge-count" style="font-size:0.72rem;font-weight:700;">
                        {{ $dayData['total_slots'] }} Sesi • {{ $dayData['total_hours'] }}j
                    </span>
                </div>

                {{-- Konten Sesi --}}
                <div class="card-body p-3 d-flex flex-column" style="background:#f8fafc; min-height: 240px;">
                    @forelse($dayData['slots'] as $slot)
                        @php
                            $unitColor = $slot->unit?->color ?? '#4f46e5';
                        @endphp
                        <div class="slot-item shadow-sm" style="border-left-color: {{ $unitColor }};">
                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                <span class="slot-time-badge">
                                    <i class="bi bi-clock"></i>
                                    {{ $slot->formatted_start_time }} - {{ $slot->formatted_end_time }}
                                </span>
                                <span class="unit-pill" style="background-color: {{ $unitColor }}20; color: {{ $unitColor }}; border: 1px solid {{ $unitColor }}40;">
                                    {{ $slot->unit?->name ?? 'Yayasan' }}
                                </span>
                            </div>

                            <div class="fw-bold text-dark mb-1" style="font-size: 0.92rem;">
                                {{ $slot->subject ?: 'Sesi Mengajar' }}
                            </div>

                            <div class="d-flex align-items-center justify-content-between text-muted small mt-2 pt-2 border-top border-light">
                                <span class="text-truncate" style="max-width: 140px;" title="{{ $slot->notes ?: 'Reguler' }}">
                                    <i class="bi bi-geo-alt me-1"></i>{{ $slot->notes ?: 'Ruang Kelas' }}
                                </span>
                                <span class="badge bg-light text-muted border">
                                    {{ $slot->duration_minutes }} Menit
                                </span>
                            </div>
                        </div>
                    @empty
                        <div class="empty-day-state my-auto">
                            <i class="bi bi-moon-stars text-secondary opacity-50 fs-2 mb-2"></i>
                            <div class="fw-semibold text-dark" style="font-size:0.88rem;">Tidak Ada Sesi</div>
                            <div class="small text-muted" style="font-size:0.75rem;">
                                {{ $dayData['is_day_off'] ? 'Hari Libur / Off' : 'Jadwal Kosong' }}
                            </div>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- ══════════════ CATATAN / PETUNJUK FOOTER ══════════════ --}}
<div class="card border-0 shadow-sm rounded-4 p-3 mt-4" style="background: linear-gradient(135deg, #f0fdf4, #e0f2fe); border: 1px solid rgba(16,185,129,0.2) !important;">
    <div class="d-flex align-items-start gap-3">
        <div class="rounded-circle p-2 bg-success text-white shadow-sm flex-shrink-0">
            <i class="bi bi-info-circle-fill fs-5"></i>
        </div>
        <div>
            <h6 class="fw-bold text-dark mb-1">Informasi Sinkronisasi Jadwal & Honor Mengajar</h6>
            <p class="text-muted small mb-0" style="line-height:1.5;">
                Jadwal mengajar di atas digunakan oleh sistem sebagai acuan otomatis dalam mencocokkan jam scan fingerprint dan menghitung honorium per jam pada modul <strong>Penggajian Guru (Payroll)</strong>. Jika terdapat perubahan jadwal kelas atau pertukaran jam mengajar, silakan hubungi bagian Kurikulum / Admin HRD.
            </p>
        </div>
    </div>
</div>

@endsection
