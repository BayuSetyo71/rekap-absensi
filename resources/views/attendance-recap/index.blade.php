@extends('layouts.app')

@section('title', 'Rekap Absen Pegawai')
@section('page-title', 'Rekap Absen Per Pegawai')
@section('page-subtitle', 'Ringkasan performa kehadiran, frekuensi check-in/out, keterlambatan, apresiasi datang awal, dan visualisasi grafik')

@section('styles')
<style>
    /* Hero KPI Banner */
    .recap-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        border-radius: 16px;
        color: #fff;
        padding: 1.25rem 1.5rem;
        position: relative;
        overflow: hidden;
        margin-bottom: 1.25rem;
        box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.25);
    }
    .recap-hero::before {
        content: '';
        position: absolute;
        width: 250px;
        height: 250px;
        background: radial-gradient(circle, rgba(124, 58, 237, 0.35), transparent 70%);
        top: -60px;
        right: -40px;
        pointer-events: none;
    }

    /* Metric Grid */
    .recap-metrics-grid {
        display: grid;
        grid-template-columns: repeat(5, minmax(0, 1fr));
        gap: 0.5rem;
    }
    @media (max-width: 1199px) {
        .recap-metrics-grid {
            grid-template-columns: repeat(3, minmax(0, 1fr));
        }
    }
    @media (max-width: 575px) {
        .recap-metrics-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    /* Metric Mini Cards */
    .metric-badge-box {
        background: rgba(255, 255, 255, 0.08);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.14);
        border-radius: 10px;
        padding: 0.6rem 0.5rem;
        text-align: center;
        transition: transform 0.2s ease, background 0.2s ease;
    }
    .metric-badge-box:hover {
        transform: translateY(-2px);
        background: rgba(255, 255, 255, 0.15);
    }

    /* Table styling enhancements */
    .avatar-sm {
        width: 34px;
        height: 34px;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid rgba(99, 102, 241, 0.2);
    }
    .table-recap th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.03em;
        color: #4b5563;
        background-color: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
        vertical-align: middle;
        white-space: nowrap;
        padding: 0.6rem 0.5rem;
    }
    .table-recap td {
        vertical-align: middle;
        font-size: 0.82rem;
        padding: 0.55rem 0.5rem;
    }

    /* Soft Badges */
    .badge-soft-indigo { background: #e0e7ff; color: #4338ca; }
    .badge-soft-purple { background: #f3e8ff; color: #7e22ce; }
    .badge-soft-teal   { background: #ccfbf1; color: #0f766e; }
    .badge-soft-amber  { background: #fef3c7; color: #b45309; }
    .badge-soft-emerald{ background: #d1fae5; color: #065f46; }
    .badge-soft-rose   { background: #ffe4e6; color: #be123c; }

    /* Quick Filter Pills */
    .quick-pill {
        font-size: 0.73rem;
        padding: 0.25rem 0.65rem;
        border-radius: 20px;
        border: 1px solid #d1d5db;
        background: #fff;
        color: #4b5563;
        cursor: pointer;
        transition: all 0.2s;
        text-decoration: none;
    }
    .quick-pill:hover, .quick-pill.active {
        background: #4f46e5;
        border-color: #4f46e5;
        color: #fff;
    }

    /* Progress bar custom */
    .progress-recap {
        height: 5px;
        border-radius: 10px;
        background-color: #e2e8f0;
        overflow: hidden;
    }

    /* Modal Styling */
    .modal-hero-header {
        background: linear-gradient(135deg, #1e1b4b 0%, #3730a3 100%);
        color: #fff;
        border-top-left-radius: calc(var(--bs-modal-border-radius) - 1px);
        border-top-right-radius: calc(var(--bs-modal-border-radius) - 1px);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- ════════════════════════════════════════════
         1. HERO KPI BANNER & EXECUTIVE SUMMARY
    ════════════════════════════════════════════ -->
    <div class="recap-hero">
        <div class="row align-items-center g-3 position-relative" style="z-index: 1;">
            <div class="col-xl-4 col-lg-12">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-white bg-opacity-20 text-black px-2 py-0.5 rounded-pill small">
                        <i class="bi bi-calendar3 me-1"></i>
                        {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}
                    </span>
                    <span class="badge bg-emerald-500 bg-opacity-20 text-white px-2 py-0.5 rounded-pill small">
                        <i class="bi bi-people me-1"></i> {{ $kpi['total_employees'] }} Pegawai
                    </span>
                </div>
                <h4 class="fw-bold mb-1 text-white">Rekapitulasi Kehadiran Pegawai</h4>
                <p class="text-white text-opacity-75 mb-0 small">
                    Monitoring akumulasi jam kerja, keterlambatan, dan apresiasi kedisiplinan pegawai datang lebih awal.
                </p>
            </div>
            <div class="col-xl-8 col-lg-12">
                <div class="recap-metrics-grid">
                    <!-- Rata-rata Hadir -->
                    <div class="metric-badge-box">
                        <div class="text-white text-opacity-75" style="font-size: 0.65rem; font-weight: 600;">RATA-RATA HADIR</div>
                        <div class="fw-bold text-white fs-5 mt-0.5">{{ $kpi['avg_attendance'] }}%</div>
                        <div class="text-white text-opacity-60" style="font-size: 0.63rem;">Tingkat Rasio</div>
                    </div>

                    <!-- Check-in / out -->
                    <div class="metric-badge-box">
                        <div class="text-white text-opacity-75" style="font-size: 0.65rem; font-weight: 600;">CHECK-IN / OUT</div>
                        <div class="fw-bold text-white fs-5 mt-0.5">{{ number_format($kpi['total_check_in']) }} <span class="fs-6 text-white text-opacity-60">/ {{ number_format($kpi['total_check_out']) }}</span></div>
                        <div class="text-white text-opacity-60" style="font-size: 0.63rem;">Scan Presensi</div>
                    </div>

                    <!-- Datang Awal -->
                    <div class="metric-badge-box" style="background: rgba(16, 185, 129, 0.22); border-color: rgba(16, 185, 129, 0.4);">
                        <div class="text-white text-opacity-90" style="font-size: 0.65rem; font-weight: 600;"><i class="bi bi-award me-1"></i>DATANG AWAL</div>
                        <div class="fw-bold fs-5 mt-0.5" style="color: #6ee7b7;">{{ $kpi['total_early_time_formatted'] }}</div>
                        <div class="text-white text-opacity-75" style="font-size: 0.63rem;">Sebelum 07:30</div>
                    </div>

                    <!-- Kali Terlambat -->
                    <div class="metric-badge-box">
                        <div class="text-white text-opacity-75" style="font-size: 0.65rem; font-weight: 600;">KALI TERLAMBAT</div>
                        <div class="fw-bold text-warning fs-5 mt-0.5">{{ number_format($kpi['total_late']) }}x</div>
                        <div class="text-white text-opacity-60" style="font-size: 0.63rem;">Scan > 07:30</div>
                    </div>

                    <!-- Total Waktu Terlambat -->
                    <div class="metric-badge-box">
                        <div class="text-white text-opacity-75" style="font-size: 0.65rem; font-weight: 600;">WAKTU TERLAMBAT</div>
                        <div class="fw-bold text-warning fs-5 mt-0.5">{{ $kpi['total_late_time_formatted'] }}</div>
                        <div class="text-white text-opacity-60" style="font-size: 0.63rem;">Akumulatif</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         2. FILTER BAR & AKSI EKSPOR
    ════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-3" style="border-radius: 14px;">
        <div class="card-body p-3">
            <form action="{{ route('attendance-recap.index') }}" method="GET" id="filterForm">
                <div class="row g-2 align-items-end">
                    <!-- Tanggal Mulai -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="form-label fw-semibold text-muted small mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-calendar-event me-1"></i> Tanggal Mulai
                        </label>
                        <input type="date" name="start_date" id="startDateInput" class="form-control form-control-sm"
                               value="{{ $startDate }}">
                    </div>

                    <!-- Tanggal Selesai -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="form-label fw-semibold text-muted small mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-calendar-event me-1"></i> Tanggal Akhir
                        </label>
                        <input type="date" name="end_date" id="endDateInput" class="form-control form-control-sm"
                               value="{{ $endDate }}">
                    </div>

                    <!-- Filter Departemen -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="form-label fw-semibold text-muted small mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-building me-1"></i> Departemen
                        </label>
                        <select name="department" class="form-select form-select-sm">
                            <option value="">Semua Departemen</option>
                            @foreach($departments as $dept)
                                <option value="{{ $dept }}" {{ request('department') == $dept ? 'selected' : '' }}>
                                    {{ $dept }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Filter Performa Kehadiran -->
                    <div class="col-6 col-md-4 col-xl-2">
                        <label class="form-label fw-semibold text-muted small mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-speedometer2 me-1"></i> Kategori Performa
                        </label>
                        <select name="performance" class="form-select form-select-sm">
                            <option value="">Semua Kategori</option>
                            <option value="champion_early" {{ request('performance') == 'champion_early' ? 'selected' : '' }}>🏆 Paling Sering Datang Awal</option>
                            <option value="excellent" {{ request('performance') == 'excellent' ? 'selected' : '' }}>Sangat Baik (≥ 95%)</option>
                            <option value="good" {{ request('performance') == 'good' ? 'selected' : '' }}>Baik (80% - 94%)</option>
                            <option value="warning" {{ request('performance') == 'warning' ? 'selected' : '' }}>Perlu Perhatian (< 80%)</option>
                            <option value="frequent_late" {{ request('performance') == 'frequent_late' ? 'selected' : '' }}>Ada Keterlambatan</option>
                        </select>
                    </div>

                    <!-- Pencarian Nama / NIP -->
                    <div class="col-12 col-md-4 col-xl-2">
                        <label class="form-label fw-semibold text-muted small mb-1" style="font-size:0.75rem;">
                            <i class="bi bi-search me-1"></i> Cari Pegawai
                        </label>
                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Nama / NIP..." value="{{ request('search') }}">
                    </div>

                    <!-- Tombol Filter, Reset & Unduh Excel -->
                    <div class="col-12 col-md-4 col-xl-2">
                        <div class="d-flex gap-1 w-100">
                            <button type="submit" class="btn btn-primary btn-sm flex-fill d-flex align-items-center justify-content-center gap-1" title="Terapkan Filter">
                                <i class="bi bi-filter"></i> Filter
                            </button>
                            <a href="{{ route('attendance-recap.index') }}" class="btn btn-outline-secondary btn-sm px-2" title="Reset Filter">
                                <i class="bi bi-arrow-clockwise"></i>
                            </a>
                            @canExport('attendance-recap')
                            <a href="{{ route('attendance-recap.export', request()->all()) }}" class="btn btn-success btn-sm px-2" title="Unduh Excel (.xlsx)">
                                <i class="bi bi-file-earmark-excel"></i>
                            </a>
                            @endcanExport
                        </div>
                    </div>
                </div>

                <!-- Quick Period Filter Pills -->
                <div class="d-flex flex-wrap align-items-center gap-2 mt-2 pt-2 border-top">
                    <span class="small fw-semibold text-muted" style="font-size: 0.74rem;">Pintasan:</span>
                    <button type="button" class="quick-pill btn-quick-date" data-period="this_month">Bulan Ini</button>
                    <button type="button" class="quick-pill btn-quick-date" data-period="last_month">Bulan Lalu</button>
                    <button type="button" class="quick-pill btn-quick-date" data-period="last_30_days">30 Hari Terakhir</button>
                    <button type="button" class="quick-pill btn-quick-date" data-period="last_7_days">7 Hari Terakhir</button>
                </div>
            </form>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         3. TABEL REKAPITULASI ABSENSI PER PEGAWAI (KOMPAK & RAPI)
    ════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header bg-white py-2 px-3 d-flex flex-wrap align-items-center justify-content-between gap-2 border-bottom">
            <div>
                <span class="fw-bold text-dark" style="font-size: 0.95rem;">
                    <i class="bi bi-table text-primary me-2"></i>Daftar Rekapitulasi Presensi Pegawai
                </span>
                <span class="text-muted small ms-2" style="font-size: 0.75rem;">({{ $recapData->count() }} Pegawai)</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                @canExport('attendance-recap')
                <a href="{{ route('attendance-recap.export', request()->all()) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 py-1" style="font-size:0.75rem;">
                    <i class="bi bi-download me-1"></i> Unduh Excel
                </a>
                @endcanExport
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-recap align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 35px;">NO</th>
                        <th>PEGAWAI & DEPARTEMEN</th>
                        <th class="text-center" style="width: 65px;" title="Total Log Hari Absensi">HARI</th>
                        <th class="text-center" title="Total Scan Masuk & Pulang">CHECK-IN / OUT</th>
                        <th class="text-center" title="Scan Masuk <= 07:30">TEPAT WAKTU</th>
                        <th class="text-center" title="Apresiasi: Total Waktu Datang Sebelum 07:30">DATANG AWAL</th>
                        <th class="text-center" title="Frekuensi dan Total Waktu Keterlambatan">TERLAMBAT</th>
                        <th class="text-center" title="Izin / Sakit / Alpa">IZIN / SAKIT / ALPA</th>
                        <th style="width: 110px;" class="text-center">% HADIR</th>
                        <th class="text-center" style="width: 120px;">AKSI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recapData as $index => $row)
                        @php
                            $user = $row->user;
                            $pct  = $row->attendance_percentage;
                            $pctColor = $pct >= 90 ? '#10b981' : ($pct >= 75 ? '#f59e0b' : '#ef4444');
                        @endphp
                        <tr>
                            <!-- No -->
                            <td class="text-center text-muted fw-semibold" style="font-size:0.75rem;">{{ $loop->iteration }}</td>

                            <!-- Info Pegawai & Departemen (2 Baris Ringkas) -->
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="avatar-sm">
                                    <div class="lh-sm">
                                        <a href="javascript:void(0);" onclick="openDetailModal({{ $user->id }})" class="fw-bold text-dark text-decoration-none hover-primary d-block" style="font-size: 0.84rem;">
                                            {{ $user->name }}
                                        </a>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            <span class="badge bg-light text-secondary border py-0 px-1 font-monospace">NIP: {{ $user->nip ?: '-' }}</span>
                                            <span class="ms-1">{{ $user->position ?: '-' }}</span>
                                            <span class="text-secondary">({{ $user->department ?: 'Umum' }})</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Total Hari -->
                            <td class="text-center">
                                <span class="badge bg-light text-dark border px-2 py-1" style="font-size:0.75rem;">{{ $row->total_logs }} Hari</span>
                            </td>

                            <!-- Check-In / Out -->
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <span class="badge badge-soft-indigo px-1.5 py-0.5" title="Check-In (Scan Masuk): {{ $row->check_in_count }}" style="font-size:0.75rem;">
                                        <i class="bi bi-box-arrow-in-right"></i> {{ $row->check_in_count }}
                                    </span>
                                    <span class="badge badge-soft-purple px-1.5 py-0.5" title="Check-Out (Scan Pulang): {{ $row->check_out_count }}" style="font-size:0.75rem;">
                                        <i class="bi bi-box-arrow-right"></i> {{ $row->check_out_count }}
                                    </span>
                                </div>
                            </td>

                            <!-- Tepat Waktu -->
                            <td class="text-center">
                                <span class="badge badge-soft-emerald px-2 py-1" style="font-size:0.75rem;">
                                    <i class="bi bi-check-circle me-1"></i>{{ $row->on_time_count }}
                                </span>
                            </td>

                            <!-- Datang Lebih Awal (Apresiasi) -->
                            <td class="text-center">
                                @if($row->total_early_minutes > 0)
                                    <span class="badge badge-soft-emerald px-2 py-1" title="{{ $row->early_count }}x Scan Masuk Sebelum 07:30" style="font-size:0.75rem;">
                                        <i class="bi bi-award-fill text-success me-1"></i>{{ $row->total_early_formatted }}
                                    </span>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <!-- Terlambat (Kali & Waktu) -->
                            <td class="text-center">
                                @if($row->late_count > 0)
                                    <span class="badge badge-soft-amber px-2 py-1" title="{{ $row->late_count }}x Terlambat (Akumulasi {{ $row->total_late_formatted }})" style="font-size:0.75rem;">
                                        <i class="bi bi-clock-history me-1"></i>{{ $row->late_count }}x
                                        @if($row->total_late_minutes > 0)
                                            <span class="ms-1 fw-bold text-danger">({{ $row->total_late_formatted }})</span>
                                        @endif
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted px-2 py-0.5" style="font-size:0.72rem;">0</span>
                                @endif
                            </td>

                            <!-- Izin / Sakit / Alpa -->
                            <td class="text-center">
                                @if($row->permit_count > 0 || $row->sick_count > 0 || $row->alpha_count > 0)
                                    <div class="d-flex align-items-center justify-content-center gap-1" style="font-size:0.72rem;">
                                        @if($row->permit_count > 0)
                                            <span class="badge badge-soft-teal px-1" title="Izin: {{ $row->permit_count }}">I:{{ $row->permit_count }}</span>
                                        @endif
                                        @if($row->sick_count > 0)
                                            <span class="badge badge-soft-indigo px-1" title="Sakit: {{ $row->sick_count }}">S:{{ $row->sick_count }}</span>
                                        @endif
                                        @if($row->alpha_count > 0)
                                            <span class="badge badge-soft-rose px-1 fw-bold" title="Alpa: {{ $row->alpha_count }}">A:{{ $row->alpha_count }}</span>
                                        @endif
                                    </div>
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>

                            <!-- % Kehadiran + Progress Bar -->
                            <td>
                                <div class="d-inline-block w-100" style="max-width: 100px;">
                                    <div class="d-flex align-items-center justify-content-between mb-0.5">
                                        <span class="fw-bold" style="color: {{ $pctColor }}; font-size: 0.78rem;">{{ $pct }}%</span>
                                        <span class="text-muted" style="font-size: 0.65rem;">
                                            {{ $row->on_time_count + $row->late_count }}/{{ $row->total_logs }}
                                        </span>
                                    </div>
                                    <div class="progress progress-recap">
                                        <div class="progress-bar" role="progressbar" style="width: {{ min($pct, 100) }}%; background-color: {{ $pctColor }};" aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                    </div>
                                </div>
                            </td>

                            <!-- Tombol Aksi (Detail & Grafik) -->
                            <td class="text-center">
                                <div class="btn-group btn-group-sm">
                                    <!-- Tombol Detail Modal -->
                                    <button type="button" class="btn btn-outline-primary btn-sm px-2 py-0.5" style="font-size:0.75rem;" onclick="openDetailModal({{ $user->id }})" title="Lihat Riwayat Lengkap Pegawai">
                                        <i class="bi bi-card-list me-0.5"></i> Detail
                                    </button>

                                    <!-- Tombol Grafik Modal -->
                                    <button type="button" class="btn btn-outline-indigo btn-sm px-2 py-0.5" style="border-color:#6366f1;color:#6366f1;font-size:0.75rem;" onclick="openChartModal({{ $user->id }})" title="Lihat Grafik Visual Kehadiran">
                                        <i class="bi bi-graph-up me-0.5"></i> Grafik
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5">
                                <div class="py-4">
                                    <i class="bi bi-folder-x text-muted" style="font-size: 3rem; opacity: 0.5;"></i>
                                    <h6 class="fw-bold mt-3 text-dark">Tidak Ada Data Rekapitulasi</h6>
                                    <p class="text-muted small mb-0">Silakan sesuaikan filter tanggal atau lakukan pencarian pegawai lain.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@section('modals')
<!-- ════════════════════════════════════════════
     4. MODAL DETAIL RIWAYAT ABSEN PEGAWAI (AJAX)
════════════════════════════════════════════ -->
<div class="modal fade" id="modalDetailRecap" tabindex="-1" aria-labelledby="modalDetailRecapLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header modal-hero-header py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="" id="detailUserAvatar" class="avatar-sm rounded-circle border border-2 border-white shadow-sm" alt="Avatar">
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="detailUserName">-</h5>
                        <div class="text-white text-opacity-75 small" id="detailUserSubtitle">-</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                <!-- Mini Stats Box in Modal -->
                <div class="row g-2 mb-3" id="detailStatsContainer">
                    <div class="col-4 col-sm-2 text-center">
                        <div class="bg-white p-2 rounded-3 border shadow-xs">
                            <div class="text-muted" style="font-size: 0.68rem;">LOG HARI</div>
                            <div class="fw-bold text-dark fs-6 mt-1" id="statDetailTotalLogs">0</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 text-center">
                        <div class="bg-white p-2 rounded-3 border shadow-xs">
                            <div class="text-muted" style="font-size: 0.68rem;">CHECK-IN</div>
                            <div class="fw-bold text-indigo fs-6 mt-1" id="statDetailCheckIn">0</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 text-center">
                        <div class="bg-white p-2 rounded-3 border shadow-xs">
                            <div class="text-muted" style="font-size: 0.68rem;">CHECK-OUT</div>
                            <div class="fw-bold text-purple fs-6 mt-1" id="statDetailCheckOut">0</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 text-center">
                        <div class="bg-white p-2 rounded-3 border shadow-xs">
                            <div class="text-muted" style="font-size: 0.68rem;">DATANG AWAL</div>
                            <div class="fw-bold text-success fs-6 mt-1" id="statDetailEarly">-</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 text-center">
                        <div class="bg-white p-2 rounded-3 border shadow-xs">
                            <div class="text-muted" style="font-size: 0.68rem;">TERLAMBAT</div>
                            <div class="fw-bold text-warning fs-6 mt-1" id="statDetailLate">-</div>
                        </div>
                    </div>
                    <div class="col-4 col-sm-2 text-center">
                        <div class="bg-white p-2 rounded-3 border shadow-xs">
                            <div class="text-muted" style="font-size: 0.68rem;">% HADIR</div>
                            <div class="fw-bold text-primary fs-6 mt-1" id="statDetailPercentage">0%</div>
                        </div>
                    </div>
                </div>

                <!-- Table Log Harian -->
                <div class="card border-0 shadow-sm" style="border-radius: 12px; overflow: hidden;">
                    <div class="card-header bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-bold small text-dark"><i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Absensi Harian</span>
                        <span class="badge bg-light text-muted border small" id="detailPeriodText">-</span>
                    </div>
                    <div class="table-responsive" style="max-height: 380px;">
                        <table class="table table-hover table-sm align-middle mb-0" style="font-size: 0.82rem;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th class="ps-3">TANGGAL & HARI</th>
                                    <th class="text-center">MASUK</th>
                                    <th class="text-center">PULANG</th>
                                    <th class="text-center">DURASI</th>
                                    <th class="text-center">STATUS</th>
                                    <th>KETERANGAN</th>
                                </tr>
                            </thead>
                            <tbody id="detailLogsTableBody">
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                                        Memuat data absensi...
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white py-2 px-3 d-flex justify-content-between">
                <a href="#" id="btnFullPageDetail" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Buka Halaman Lengkap (Tabel)
                </a>
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════
     5. MODAL GRAFIK PER PEGAWAI (Chart.js AJAX - MULTI CHART)
════════════════════════════════════════════ -->
<div class="modal fade" id="modalChartRecap" tabindex="-1" aria-labelledby="modalChartRecapLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header modal-hero-header py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <img src="" id="chartUserAvatar" class="avatar-sm rounded-circle border border-2 border-white shadow-sm" alt="Avatar">
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0">Analitik Visual Kehadiran: <span id="chartUserName">-</span></h5>
                        <div class="text-white text-opacity-75 small" id="chartUserSubtitle">-</div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4 bg-light">
                <!-- 4 Quick Analytics Metric Cards -->
                <div class="row g-2 mb-3">
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2 p-md-3 rounded-3 border text-center shadow-xs">
                            <div class="text-muted small" style="font-size:0.7rem;">RATA-RATA MASUK</div>
                            <div class="fw-bold text-indigo fs-5 mt-1" id="chartMetricAvgIn">-</div>
                            <div class="text-muted" style="font-size:0.68rem;">Target ≤ 07:30</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2 p-md-3 rounded-3 border text-center shadow-xs">
                            <div class="text-muted small" style="font-size:0.7rem;">RATA-RATA PULANG</div>
                            <div class="fw-bold text-purple fs-5 mt-1" id="chartMetricAvgOut">-</div>
                            <div class="text-muted" style="font-size:0.68rem;">Target ≥ 16:30</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2 p-md-3 rounded-3 border text-center shadow-xs">
                            <div class="text-muted small" style="font-size:0.7rem;">RATA-RATA JAM KERJA</div>
                            <div class="fw-bold text-success fs-5 mt-1" id="chartMetricAvgHours">-</div>
                            <div class="text-muted" style="font-size:0.68rem;">Standar 8 Jam/Hari</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="bg-white p-2 p-md-3 rounded-3 border text-center shadow-xs">
                            <div class="text-muted small" style="font-size:0.7rem;">TOTAL KETERLAMBATAN</div>
                            <div class="fw-bold text-warning fs-5 mt-1" id="chartMetricLateMins">-</div>
                            <div class="text-muted" style="font-size:0.68rem;">Akumulatif</div>
                        </div>
                    </div>
                </div>

                <!-- 4 Multi Charts in 2x2 Grid -->
                <div class="row g-3">
                    <!-- Chart 1: Doughnut Chart Komposisi Kehadiran -->
                    <div class="col-lg-4">
                        <div class="card border-0 shadow-sm h-100 mb-0" style="border-radius: 12px;">
                            <div class="card-header bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark"><i class="bi bi-pie-chart-fill me-1 text-primary"></i> 1. Komposisi Status</span>
                                <span class="badge bg-light text-muted border small" style="font-size:0.65rem;">Rasio Kehadiran</span>
                            </div>
                            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                                <div style="position: relative; width: 100%; height: 230px;">
                                    <canvas id="canvasStatusDoughnut"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 2: Line Timeline Jam Check-In & Check-Out Harian -->
                    <div class="col-lg-8">
                        <div class="card border-0 shadow-sm h-100 mb-0" style="border-radius: 12px;">
                            <div class="card-header bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark"><i class="bi bi-graph-up me-1 text-indigo"></i> 2. Tren Jam Masuk & Pulang Harian</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis small" style="font-size:0.68rem;">Masuk ≤ 07:30 | Pulang ≥ 16:30</span>
                            </div>
                            <div class="card-body p-3">
                                <div style="position: relative; width: 100%; height: 230px;">
                                    <canvas id="canvasCheckInTimeline"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 3: Distribusi Kehadiran Berdasarkan Hari Kerja (Senin - Sabtu) -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100 mb-0" style="border-radius: 12px;">
                            <div class="card-header bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark"><i class="bi bi-calendar-week me-1 text-teal" style="color:#0f766e;"></i> 3. Pola Kehadiran per Hari Kerja</span>
                                <span class="badge bg-light text-muted border small" style="font-size:0.65rem;">Senin s/d Sabtu</span>
                            </div>
                            <div class="card-body p-3">
                                <div style="position: relative; width: 100%; height: 220px;">
                                    <canvas id="canvasDayDistribution"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Chart 4: Durasi Jam Kerja Harian vs Standar 8 Jam -->
                    <div class="col-lg-6">
                        <div class="card border-0 shadow-sm h-100 mb-0" style="border-radius: 12px;">
                            <div class="card-header bg-white py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark"><i class="bi bi-bar-chart-line-fill me-1 text-primary"></i> 4. Durasi Jam Kerja Harian</span>
                                <span class="badge bg-primary-subtle text-primary small" style="font-size:0.68rem;">Target Standar 8 Jam</span>
                            </div>
                            <div class="card-body p-3">
                                <div style="position: relative; width: 100%; height: 220px;">
                                    <canvas id="canvasWorkDuration"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-white py-2 px-3 d-flex justify-content-between align-items-center">
                <a href="#" id="btnChartFullPageDetail" class="btn btn-primary btn-sm px-3">
                    <i class="bi bi-graph-up me-1"></i> Buka Halaman Lengkap (Grafik & Analitik)
                </a>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let doughnutChartInstance        = null;
    let timelineChartInstance        = null;
    let dayDistributionChartInstance = null;
    let workDurationChartInstance    = null;

    $(document).ready(function () {
        // Pindahkan elemen modal ke body agar tidak terperangkap stacking context
        $('#modalDetailRecap, #modalChartRecap').appendTo('body');

        // Quick Period Buttons Filter
        $('.btn-quick-date').on('click', function () {
            const period = $(this).data('period');
            const today = new Date();
            let start, end;

            if (period === 'this_month') {
                start = new Date(today.getFullYear(), today.getMonth(), 1);
                end = new Date(today.getFullYear(), today.getMonth() + 1, 0);
            } else if (period === 'last_month') {
                start = new Date(today.getFullYear(), today.getMonth() - 1, 1);
                end = new Date(today.getFullYear(), today.getMonth(), 0);
            } else if (period === 'last_30_days') {
                end = today;
                start = new Date();
                start.setDate(today.getDate() - 30);
            } else if (period === 'last_7_days') {
                end = today;
                start = new Date();
                start.setDate(today.getDate() - 7);
            }

            const formatDate = d => d.toISOString().split('T')[0];
            $('#startDateInput').val(formatDate(start));
            $('#endDateInput').val(formatDate(end));
            $('#filterForm').submit();
        });
    });

    // ════════════════════════════════════════════
    // AJAX: OPEN DETAIL MODAL PER PEGAWAI
    // ════════════════════════════════════════════
    function openDetailModal(userId) {
        const modal = new bootstrap.Modal(document.getElementById('modalDetailRecap'));
        modal.show();

        const startDate = $('#startDateInput').val();
        const endDate   = $('#endDateInput').val();

        $('#detailLogsTableBody').html(`
            <tr>
                <td colspan="6" class="text-center py-4 text-muted">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    Memuat rincian absensi pegawai...
                </td>
            </tr>
        `);

        // Update link full page ke tabel riwayat
        $('#btnFullPageDetail').attr('href', `{{ url('attendance-recap') }}/${userId}?start_date=${startDate}&end_date=${endDate}`);

        $.ajax({
            url: `{{ url('attendance-recap') }}/${userId}/detail-ajax`,
            type: 'GET',
            data: { start_date: startDate, end_date: endDate },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    // Update User Info
                    $('#detailUserName').text(res.user.name);
                    $('#detailUserSubtitle').text(`NIP: ${res.user.nip} • ${res.user.position} (${res.user.department})`);
                    $('#detailUserAvatar').attr('src', res.user.avatar_url);
                    $('#detailPeriodText').text(`${res.period.start_date} - ${res.period.end_date}`);

                    // Update Mini Stats
                    $('#statDetailTotalLogs').text(res.stats.total_logs);
                    $('#statDetailCheckIn').text(res.stats.check_in_count);
                    $('#statDetailCheckOut').text(res.stats.check_out_count);
                    $('#statDetailEarly').text(res.stats.total_early_formatted);
                    $('#statDetailLate').text(res.stats.total_late_formatted);
                    $('#statDetailPercentage').text(`${res.stats.attendance_percentage}%`);

                    // Render Logs Table
                    let rowsHtml = '';
                    if (res.logs && res.logs.length > 0) {
                        res.logs.forEach(log => {
                            let checkInBadge = log.check_in !== '-' 
                                ? `<span class="badge bg-light text-dark border font-monospace">${log.check_in}</span>` 
                                : '<span class="text-muted">-</span>';
                            
                            let checkOutBadge = log.check_out !== '-' 
                                ? `<span class="badge bg-light text-dark border font-monospace">${log.check_out}</span>` 
                                : '<span class="text-muted">-</span>';

                            let noteBadge = '';
                            if (log.late_minutes > 0) {
                                noteBadge = `<span class="badge bg-danger-subtle text-danger ms-1" style="font-size:0.68rem;">+${log.late_minutes}m</span>`;
                            } else if (log.early_minutes > 0) {
                                noteBadge = `<span class="badge bg-success-subtle text-success ms-1" style="font-size:0.68rem;">-${log.early_minutes}m</span>`;
                            }

                            rowsHtml += `
                                <tr>
                                    <td class="ps-3 fw-semibold text-dark">${log.date_formatted}</td>
                                    <td class="text-center">${checkInBadge} ${noteBadge}</td>
                                    <td class="text-center">${checkOutBadge}</td>
                                    <td class="text-center fw-semibold text-dark small">${log.work_duration}</td>
                                    <td class="text-center">${log.status_badge}</td>
                                    <td class="text-muted small">${log.notes}</td>
                                </tr>
                            `;
                        });
                    } else {
                        rowsHtml = `
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    Tidak ada catatan absensi pada rentang tanggal ini.
                                </td>
                            </tr>
                        `;
                    }
                    $('#detailLogsTableBody').html(rowsHtml);
                }
            },
            error: function (xhr) {
                $('#detailLogsTableBody').html(`
                    <tr>
                        <td colspan="6" class="text-center py-4 text-danger">
                            <i class="bi bi-exclamation-triangle me-1"></i> Gagal memuat data presensi. Silakan coba kembali.
                        </td>
                    </tr>
                `);
            }
        });
    }

    // ════════════════════════════════════════════
    // AJAX: OPEN MULTI-CHART MODAL PER PEGAWAI
    // ════════════════════════════════════════════
    function openChartModal(userId) {
        const modal = new bootstrap.Modal(document.getElementById('modalChartRecap'));
        modal.show();

        const startDate = $('#startDateInput').val();
        const endDate   = $('#endDateInput').val();

        // Update link full page ke HALAMAN GRAFIK LENGKAP
        $('#btnChartFullPageDetail').attr('href', `{{ url('attendance-recap') }}/${userId}/chart?start_date=${startDate}&end_date=${endDate}`);

        $.ajax({
            url: `{{ url('attendance-recap') }}/${userId}/chart-ajax`,
            type: 'GET',
            data: { start_date: startDate, end_date: endDate },
            dataType: 'json',
            success: function (res) {
                if (res.status === 'success') {
                    $('#chartUserName').text(res.user.name);
                    $('#chartUserSubtitle').text(`NIP: ${res.user.nip} • ${res.user.position} (${res.user.department})`);
                    $('#chartUserAvatar').attr('src', res.user.avatar_url);

                    // Update Metrics
                    $('#chartMetricAvgIn').text(res.metrics.avg_check_in);
                    $('#chartMetricAvgOut').text(res.metrics.avg_check_out);
                    $('#chartMetricAvgHours').text(res.metrics.avg_work_hours);
                    $('#chartMetricLateMins').text(res.metrics.total_late_minutes);

                    // 1. Render Doughnut Chart
                    renderStatusDoughnut(res.status_chart);

                    // 2. Render Timeline Chart (Masuk & Pulang)
                    renderTimelineChart(res.timeline_chart);

                    // 3. Render Day-of-Week Distribution Chart
                    renderDayDistributionChart(res.day_distribution_chart);

                    // 4. Render Work Duration Chart
                    renderWorkDurationChart(res.work_duration_chart);
                }
            }
        });
    }

    function renderStatusDoughnut(chartData) {
        const ctx = document.getElementById('canvasStatusDoughnut').getContext('2d');
        if (doughnutChartInstance) {
            doughnutChartInstance.destroy();
        }

        doughnutChartInstance = new Chart(ctx, {
            type: 'doughnut',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10, family: 'Inter' } }
                    }
                },
                cutout: '60%'
            }
        });
    }

    function renderTimelineChart(chartData) {
        const ctx = document.getElementById('canvasCheckInTimeline').getContext('2d');
        if (timelineChartInstance) {
            timelineChartInstance.destroy();
        }

        timelineChartInstance = new Chart(ctx, {
            type: 'line',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        min: 6.0,
                        max: 20.0,
                        ticks: {
                            callback: function (val) {
                                const h = Math.floor(val).toString().padStart(2, '0');
                                const m = Math.round((val % 1) * 60).toString().padStart(2, '0');
                                return `${h}:${m}`;
                            },
                            font: { size: 9 }
                        },
                        title: { display: true, text: 'Waktu (WIB)', font: { size: 10 } }
                    },
                    x: {
                        ticks: { font: { size: 9 } }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                const val = ctx.raw;
                                if (!val) return ctx.dataset.label + ': Tidak ada data';
                                const h = Math.floor(val).toString().padStart(2, '0');
                                const m = Math.round((val % 1) * 60).toString().padStart(2, '0');
                                return `${ctx.dataset.label}: ${h}:${m} WIB`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }

    function renderDayDistributionChart(chartData) {
        const ctx = document.getElementById('canvasDayDistribution').getContext('2d');
        if (dayDistributionChartInstance) {
            dayDistributionChartInstance.destroy();
        }

        dayDistributionChartInstance = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0, font: { size: 9 } },
                        title: { display: true, text: 'Jumlah Hari', font: { size: 10 } }
                    },
                    x: {
                        ticks: { font: { size: 9 } }
                    }
                },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }

    function renderWorkDurationChart(chartData) {
        const ctx = document.getElementById('canvasWorkDuration').getContext('2d');
        if (workDurationChartInstance) {
            workDurationChartInstance.destroy();
        }

        workDurationChartInstance = new Chart(ctx, {
            type: 'bar',
            data: chartData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(val) { return val + 'j'; },
                            font: { size: 9 }
                        },
                        title: { display: true, text: 'Jam Kerja', font: { size: 10 } }
                    },
                    x: {
                        ticks: { font: { size: 9 } }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function (ctx) {
                                return `${ctx.dataset.label}: ${ctx.raw} Jam`;
                            }
                        }
                    },
                    legend: {
                        position: 'bottom',
                        labels: { boxWidth: 10, font: { size: 10 } }
                    }
                }
            }
        });
    }
</script>
@endsection
