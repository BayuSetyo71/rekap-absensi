@extends('layouts.app')

@section('title', 'Analitik Visual Kehadiran - ' . $user->name)
@section('page-title', 'Analitik & Grafik Presensi Pegawai')
@section('page-subtitle', 'Visualisasi tren kehadiran, kedisiplinan waktu, dan distribusi jam kerja individu')

@section('styles')
<style>
    .chart-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%);
        border-radius: 16px;
        color: #fff;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 25px -5px rgba(79, 70, 229, 0.25);
    }
    .profile-avatar-lg {
        width: 68px;
        height: 68px;
        object-fit: cover;
        border-radius: 16px;
        border: 3px solid rgba(255, 255, 255, 0.25);
    }
    .analytics-kpi-card {
        background: #fff;
        border-radius: 14px;
        padding: 1.1rem;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.03);
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        height: 100%;
    }
    .analytics-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(99, 102, 241, 0.1);
    }
    .chart-card-full {
        background: #fff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 2px 12px rgba(0, 0, 0, 0.03);
        overflow: hidden;
    }
    .chart-card-full .card-header {
        background: #fff;
        border-bottom: 1px solid #f1f5f9;
        padding: 1rem 1.25rem;
    }
    .chart-card-full .card-body {
        padding: 1.25rem;
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- ════════════════════════════════════════════
         1. HERO HEADER IDENTITAS PEGAWAI
    ════════════════════════════════════════════ -->
    <div class="chart-hero">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}" class="profile-avatar-lg shadow-sm">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <h4 class="fw-bold mb-0 text-white">{{ $user->name }}</h4>
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2 py-1 small">
                            {{ $user->role?->display_name ?? 'Pegawai' }}
                        </span>
                    </div>
                    <div class="text-white text-opacity-80 small d-flex flex-wrap gap-3">
                        <span><i class="bi bi-person-badge me-1"></i> NIP: {{ $user->nip ?: '-' }}</span>
                        <span><i class="bi bi-briefcase me-1"></i> {{ $user->position ?: '-' }} ({{ $user->department ?: 'Umum' }})</span>
                        <span><i class="bi bi-calendar3 me-1"></i> Periode: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d M Y') }} - {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d M Y') }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('attendance-recap.show', ['user' => $user->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-card-list me-1"></i> Lihat Tabel Riwayat Log
                </a>
                @if(auth()->user()->isSuperAdmin() || auth()->user()->canAccessMenu('users', 'view'))
                    <a href="{{ route('attendance-recap.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bi bi-arrow-left me-1"></i> Rekap Seluruh Pegawai
                    </a>
                @else
                    <a href="{{ route('dashboard') }}" class="btn btn-light btn-sm rounded-pill px-3">
                        <i class="bi bi-house me-1"></i> Ke Beranda
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         2. FILTER PERIODE TANGGAL
    ════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-3">
            <form action="{{ route('attendance-recap.chart', $user->id) }}" method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-semibold text-muted small"><i class="bi bi-calendar-event me-1"></i> Filter Rentang Analisis:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-auto text-muted small">s/d</div>
                <div class="col-auto">
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-filter me-1"></i> Perbarui Grafik
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         3. 5 KARTU METRIK ANALITIK & APRESIASI
    ════════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <!-- % Kehadiran -->
        <div class="col-6 col-md-4 col-lg">
            <div class="analytics-kpi-card text-center">
                <div class="text-muted small fw-semibold">TINGKAT KEHADIRAN</div>
                <div class="fw-bold text-primary fs-3 mt-1">{{ $stats['attendance_percentage'] }}%</div>
                <div class="text-muted small" style="font-size:0.72rem;">{{ $stats['on_time_count'] + $stats['late_count'] }} dari {{ $stats['total_logs'] }} Hari</div>
            </div>
        </div>

        <!-- Apresiasi Datang Lebih Awal -->
        <div class="col-6 col-md-4 col-lg">
            <div class="analytics-kpi-card text-center border-emerald" style="border-color: #a7f3d0 !important;">
                <div class="text-success small fw-semibold d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-award-fill text-success"></i> DATANG LEBIH AWAL
                </div>
                <div class="fw-bold text-success fs-3 mt-1">{{ $stats['total_early_formatted'] }}</div>
                <div class="text-success text-opacity-75 small" style="font-size:0.72rem;">{{ $stats['early_count'] }}x Scan < 07:30 WIB</div>
            </div>
        </div>

        <!-- Waktu Terlambat -->
        <div class="col-6 col-md-4 col-lg">
            <div class="analytics-kpi-card text-center">
                <div class="text-warning small fw-semibold d-flex align-items-center justify-content-center gap-1">
                    <i class="bi bi-clock-history"></i> WAKTU TERLAMBAT
                </div>
                <div class="fw-bold text-warning fs-3 mt-1">{{ $stats['total_late_formatted'] }}</div>
                <div class="text-muted small" style="font-size:0.72rem;">{{ $stats['late_count'] }}x Scan > 07:30 WIB</div>
            </div>
        </div>

        <!-- Scan Masuk & Pulang -->
        <div class="col-6 col-md-4 col-lg">
            <div class="analytics-kpi-card text-center">
                <div class="text-muted small fw-semibold">CHECK-IN / OUT</div>
                <div class="fw-bold text-dark fs-3 mt-1">{{ $stats['check_in_count'] }} <span class="text-muted fs-6">/ {{ $stats['check_out_count'] }}</span></div>
                <div class="text-muted small" style="font-size:0.72rem;">Total Scan Presensi</div>
            </div>
        </div>

        <!-- Izin, Sakit, Alpa -->
        <div class="col-12 col-md-4 col-lg">
            <div class="analytics-kpi-card text-center">
                <div class="text-muted small fw-semibold">KETIDAKHADIRAN</div>
                <div class="fw-bold text-danger fs-3 mt-1">{{ $stats['permit_count'] + $stats['sick_count'] + $stats['alpha_count'] }} Hari</div>
                <div class="text-muted small" style="font-size:0.72rem;">Izin: {{ $stats['permit_count'] }} • Sakit: {{ $stats['sick_count'] }} • Alpa: {{ $stats['alpha_count'] }}</div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         4. VISUALISASI GRAFIK BESAR (4 CHARTS GRID)
    ════════════════════════════════════════════ -->
    <div class="row g-4">
        <!-- Chart 1: Doughnut Chart Komposisi Kehadiran -->
        <div class="col-lg-4">
            <div class="chart-card-full h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-pie-chart-fill me-2 text-primary"></i>1. Komposisi Kehadiran</h6>
                        <small class="text-muted">Proporsi status presensi dalam periode</small>
                    </div>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center">
                    <div style="position: relative; width: 100%; height: 280px;">
                        <canvas id="pageCanvasStatusDoughnut"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 2: Multi-Line Chart Tren Jam Masuk & Pulang -->
        <div class="col-lg-8">
            <div class="chart-card-full h-100">
                <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-graph-up me-2 text-indigo"></i>2. Tren Jam Masuk & Pulang Harian</h6>
                        <small class="text-muted">Monitoring jam kedatangan aktual vs batas waktu 07:30 WIB</small>
                    </div>
                    <span class="badge bg-warning-subtle text-warning-emphasis small px-2 py-1">
                        Batas Masuk: 07:30 | Standar Pulang: 16:30
                    </span>
                </div>
                <div class="card-body">
                    <div style="position: relative; width: 100%; height: 280px;">
                        <canvas id="pageCanvasTimeline"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 3: Distribusi Kehadiran Berdasarkan Hari Kerja -->
        <div class="col-lg-6">
            <div class="chart-card-full h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-calendar-week me-2 text-teal" style="color:#0f766e;"></i>3. Pola Kehadiran per Hari Kerja</h6>
                        <small class="text-muted">Menganalisis kecenderungan hadir tepat waktu vs terlambat (Senin s/d Sabtu)</small>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position: relative; width: 100%; height: 280px;">
                        <canvas id="pageCanvasDayDistribution"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Chart 4: Durasi Jam Kerja Harian vs Standar 8 Jam -->
        <div class="col-lg-6">
            <div class="chart-card-full h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-bar-chart-line-fill me-2 text-primary"></i>4. Durasi Jam Kerja Harian</h6>
                        <small class="text-muted">Perbandingan jam kerja aktual per hari terhadap standar 8 jam kerja</small>
                    </div>
                </div>
                <div class="card-body">
                    <div style="position: relative; width: 100%; height: 280px;">
                        <canvas id="pageCanvasWorkDuration"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const startDate = "{{ $startDate }}";
        const endDate   = "{{ $endDate }}";
        const userId    = "{{ $user->id }}";

        // Ambil data chart via AJAX endpoint yang sudah lengkap
        $.ajax({
            url: `{{ url('attendance-recap') }}/${userId}/chart-ajax`,
            type: 'GET',
            data: { start_date: startDate, end_date: endDate },
            dataType: 'json',
            success: function(res) {
                if (res.status === 'success') {
                    // 1. Doughnut Chart
                    const ctx1 = document.getElementById('pageCanvasStatusDoughnut').getContext('2d');
                    new Chart(ctx1, {
                        type: 'doughnut',
                        data: res.status_chart,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11, family: 'Inter' } } }
                            },
                            cutout: '60%'
                        }
                    });

                    // 2. Timeline Multi-Line Chart
                    const ctx2 = document.getElementById('pageCanvasTimeline').getContext('2d');
                    new Chart(ctx2, {
                        type: 'line',
                        data: res.timeline_chart,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    min: 6.0,
                                    max: 20.0,
                                    ticks: {
                                        callback: function(val) {
                                            const h = Math.floor(val).toString().padStart(2, '0');
                                            const m = Math.round((val % 1) * 60).toString().padStart(2, '0');
                                            return `${h}:${m}`;
                                        },
                                        font: { size: 10 }
                                    },
                                    title: { display: true, text: 'Waktu (WIB)', font: { size: 11 } }
                                },
                                x: { ticks: { font: { size: 10 } } }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) {
                                            const val = ctx.raw;
                                            if (!val) return ctx.dataset.label + ': Tidak ada data';
                                            const h = Math.floor(val).toString().padStart(2, '0');
                                            const m = Math.round((val % 1) * 60).toString().padStart(2, '0');
                                            return `${ctx.dataset.label}: ${h}:${m} WIB`;
                                        }
                                    }
                                },
                                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                            }
                        }
                    });

                    // 3. Day Distribution Chart
                    const ctx3 = document.getElementById('pageCanvasDayDistribution').getContext('2d');
                    new Chart(ctx3, {
                        type: 'bar',
                        data: res.day_distribution_chart,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: { precision: 0, font: { size: 10 } },
                                    title: { display: true, text: 'Jumlah Hari', font: { size: 11 } }
                                },
                                x: { ticks: { font: { size: 10 } } }
                            },
                            plugins: {
                                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                            }
                        }
                    });

                    // 4. Work Duration Chart
                    const ctx4 = document.getElementById('pageCanvasWorkDuration').getContext('2d');
                    new Chart(ctx4, {
                        type: 'bar',
                        data: res.work_duration_chart,
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            scales: {
                                y: {
                                    beginAtZero: true,
                                    ticks: {
                                        callback: function(val) { return val + 'j'; },
                                        font: { size: 10 }
                                    },
                                    title: { display: true, text: 'Durasi (Jam)', font: { size: 11 } }
                                },
                                x: { ticks: { font: { size: 10 } } }
                            },
                            plugins: {
                                tooltip: {
                                    callbacks: {
                                        label: function(ctx) { return `${ctx.dataset.label}: ${ctx.raw} Jam`; }
                                    }
                                },
                                legend: { position: 'bottom', labels: { boxWidth: 12, font: { size: 11 } } }
                            }
                        }
                    });
                }
            }
        });
    });
</script>
@endsection
