@extends('layouts.app')

@section('title', 'Laporan Presensi & Kehadiran')
@section('page-title', 'Laporan Presensi')
@section('page-subtitle', 'Rekapitulasi performa kehadiran, analitik tren, serta ekspor data ke Excel & PDF')

@section('styles')
<style>
    /* ══════════════ KPI CARDS ══════════════ */
    .kpi-card {
        border-radius: 16px;
        border: none !important;
        position: relative;
        overflow: hidden;
        transition: transform 0.25s ease, box-shadow 0.25s ease;
        cursor: default;
    }
    .kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 30px rgba(0,0,0,0.1) !important;
    }

    .kpi-violet  { background: linear-gradient(135deg, #7c3aed, #5b21b6); box-shadow: 0 8px 24px rgba(124,58,237,0.25); }
    .kpi-indigo  { background: linear-gradient(135deg, #4f46e5, #3730a3); box-shadow: 0 8px 24px rgba(79,70,229,0.25); }
    .kpi-emerald { background: linear-gradient(135deg, #059669, #047857); box-shadow: 0 8px 24px rgba(5,150,105,0.25); }
    .kpi-amber   { background: linear-gradient(135deg, #d97706, #b45309); box-shadow: 0 8px 24px rgba(217,119,6,0.25); }
    .kpi-cyan    { background: linear-gradient(135deg, #0891b2, #0e7490); box-shadow: 0 8px 24px rgba(8,145,178,0.25); }
    .kpi-rose    { background: linear-gradient(135deg, #e11d48, #be123c); box-shadow: 0 8px 24px rgba(225,29,72,0.25); }

    .kpi-card .card-body { padding: 1.1rem; }
    .kpi-icon-wrap {
        width: 38px; height: 38px;
        border-radius: 10px;
        background: rgba(255,255,255,0.18);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
        color: #fff;
    }
    .kpi-label {
        font-size: 0.68rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: rgba(255,255,255,0.7);
        margin-bottom: 0.2rem;
    }
    .kpi-value {
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 1.75rem;
        font-weight: 800;
        color: #fff;
        line-height: 1;
    }
    .kpi-sub {
        font-size: 0.72rem;
        color: rgba(255,255,255,0.6);
        margin-top: 0.2rem;
    }

    /* ══════════════ CHARTS ══════════════ */
    .chart-card {
        border-radius: 16px;
        background: rgba(255,255,255,0.92);
        border: 1px solid rgba(99,102,241,0.08) !important;
        box-shadow: 0 4px 24px rgba(99,102,241,0.06);
    }
    .chart-container { position: relative; height: 250px; }
    .donut-container { width: 170px; height: 170px; position: relative; margin: 0 auto; }

    /* ══════════════ FILTER BOX ══════════════ */
    .filter-card {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(99,102,241,0.1) !important;
        box-shadow: 0 4px 20px rgba(99,102,241,0.05);
    }

    /* ══════════════ TABLE ══════════════ */
    .report-table-card {
        border-radius: 16px;
        background: #ffffff;
        border: 1px solid rgba(99,102,241,0.08) !important;
    }

    @media (max-width: 767.98px) {
        .kpi-value { font-size: 1.45rem; }
        .chart-container { height: 210px; }
        .hide-mobile { display: none; }
    }
</style>
@endsection

@section('content')

{{-- ══════════════ HEADER / ACTION BAR ══════════════ --}}
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge px-2.5 py-1 rounded-pill" style="background:#ede9fe;color:#5b21b6;font-weight:600;font-size:0.75rem;">
                <i class="bi bi-file-earmark-bar-graph-fill me-1"></i>Modul Laporan
            </span>
            <span class="badge px-2.5 py-1 rounded-pill" style="background:#d1fae5;color:#065f46;font-weight:600;font-size:0.75rem;">
                <i class="bi bi-calendar-check me-1"></i>Periode: {{ $periodLabel }}
            </span>
        </div>
        <h4 class="fw-bold mb-0 text-gradient" style="font-family:'Plus Jakarta Sans',sans-serif;">
            Laporan Presensi & Kehadiran
        </h4>
    </div>

    {{-- Tombol Export Excel & PDF --}}
    <div class="d-flex flex-wrap gap-2">
        @php
            $exportParams = request()->all();
            $exportParams['month'] = $selectedMonth;
            $exportParams['year']  = $selectedYear;
        @endphp

        @canExport('reports')
        <a href="{{ route('reports.export-excel', $exportParams) }}"
           class="btn btn-success fw-semibold shadow-sm"
           title="Unduh Laporan Format Microsoft Excel (.xlsx)">
            <i class="bi bi-file-earmark-excel-fill me-1.5 fs-6"></i> Export Excel
        </a>

        <a href="{{ route('reports.export-pdf', $exportParams) }}"
           class="btn btn-danger fw-semibold shadow-sm"
           style="background:linear-gradient(135deg, #e11d48, #be123c);border:none;"
           title="Unduh Laporan Format PDF Resmi (.pdf)">
            <i class="bi bi-file-earmark-pdf-fill me-1.5 fs-6"></i> Export PDF
        </a>
        @endcanExport
    </div>
</div>

{{-- ══════════════ FILTER BOX ══════════════ --}}
<div class="card filter-card mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('reports.index') }}" class="row g-2 align-items-end">
            {{-- Filter Bulan --}}
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold text-muted mb-1" style="font-size:0.75rem;">Bulan</label>
                <select name="month" class="form-select form-select-sm">
                    @for($m = 1; $m <= 12; $m++)
                        <option value="{{ $m }}" {{ $selectedMonth == $m ? 'selected' : '' }}>
                            {{ \Carbon\Carbon::createFromDate(2026, $m, 1)->translatedFormat('F') }}
                        </option>
                    @endfor
                </select>
            </div>

            {{-- Filter Tahun --}}
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold text-muted mb-1" style="font-size:0.75rem;">Tahun</label>
                <select name="year" class="form-select form-select-sm">
                    @for($y = now()->year - 2; $y <= now()->year + 1; $y++)
                        <option value="{{ $y }}" {{ $selectedYear == $y ? 'selected' : '' }}>{{ $y }}</option>
                    @endfor
                </select>
            </div>

            {{-- Filter Unit / Jenjang Sekolah --}}
            @if($canManageAll && $units->isNotEmpty())
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold text-muted mb-1" style="font-size:0.75rem;">Unit / Jenjang</label>
                <select name="unit_id" class="form-select form-select-sm">
                    <option value="">Semua Unit</option>
                    @foreach($units as $u)
                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            {{-- Filter Status --}}
            <div class="col-6 col-md-2">
                <label class="form-label fw-semibold text-muted mb-1" style="font-size:0.75rem;">Status Kehadiran</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">Semua Status</option>
                    <option value="hadir" {{ request('status') == 'hadir' ? 'selected' : '' }}>Hadir Tepat</option>
                    <option value="terlambat" {{ request('status') == 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                    <option value="izin" {{ request('status') == 'izin' ? 'selected' : '' }}>Izin</option>
                    <option value="sakit" {{ request('status') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                    <option value="alpa" {{ request('status') == 'alpa' ? 'selected' : '' }}>Alpa</option>
                </select>
            </div>

            {{-- Filter Cari Pegawai --}}
            <div class="col-12 col-md-{{ $canManageAll && $units->isNotEmpty() ? '2' : '4' }}">
                <label class="form-label fw-semibold text-muted mb-1" style="font-size:0.75rem;">Cari Nama / NIP</label>
                <div class="input-group input-group-sm">
                    <input type="text" name="search" class="form-control" placeholder="Ketik kata kunci..." value="{{ request('search') }}">
                </div>
            </div>

            {{-- Tombol Submit --}}
            <div class="col-12 col-md-2 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <a href="{{ route('reports.index') }}" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                    <i class="bi bi-arrow-counterclockwise"></i>
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════ 6 KPI CARDS ══════════════ --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi-card kpi-violet shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="kpi-icon-wrap"><i class="bi bi-pie-chart-fill"></i></div>
                    <span style="font-size:0.65rem;color:rgba(255,255,255,0.7);background:rgba(255,255,255,0.15);border-radius:6px;padding:2px 6px;">%</span>
                </div>
                <div class="kpi-label">Tingkat Hadir</div>
                <div class="kpi-value">{{ $kpi['attendance_rate'] }}%</div>
                <div class="kpi-sub">Rasio kehadiran</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi-card kpi-indigo shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="kpi-icon-wrap"><i class="bi bi-calendar2-check-fill"></i></div>
                </div>
                <div class="kpi-label">Total Record</div>
                <div class="kpi-value">{{ $kpi['total_logs'] }}</div>
                <div class="kpi-sub">Catatan log</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi-card kpi-emerald shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="kpi-icon-wrap"><i class="bi bi-check-circle-fill"></i></div>
                </div>
                <div class="kpi-label">Hadir Tepat</div>
                <div class="kpi-value">{{ $kpi['hadir_count'] }}</div>
                <div class="kpi-sub">Scan Tepat Waktu</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi-card kpi-amber shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="kpi-icon-wrap"><i class="bi bi-clock-fill"></i></div>
                </div>
                <div class="kpi-label">Terlambat</div>
                <div class="kpi-value">{{ $kpi['late_count'] }}</div>
                <div class="kpi-sub">{{ $kpi['total_late_formatted'] }} terlambat</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi-card kpi-cyan shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="kpi-icon-wrap"><i class="bi bi-file-medical-fill"></i></div>
                </div>
                <div class="kpi-label">Izin / Sakit</div>
                <div class="kpi-value">{{ $kpi['izin_count'] + $kpi['sakit_count'] }}</div>
                <div class="kpi-sub">{{ $kpi['izin_count'] }} izin • {{ $kpi['sakit_count'] }} sakit</div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-2">
        <div class="card kpi-card kpi-rose shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div class="kpi-icon-wrap"><i class="bi bi-x-circle-fill"></i></div>
                </div>
                <div class="kpi-label">Alpa / Bolos</div>
                <div class="kpi-value">{{ $kpi['alpa_count'] }}</div>
                <div class="kpi-sub">Tanpa keterangan</div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ CHARTS ROW ══════════════ --}}
<div class="row g-4 mb-4">
    {{-- Tren Kehadiran --}}
    <div class="col-lg-8">
        <div class="card chart-card h-100 shadow-sm">
            <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <div class="fw-bold" style="font-size:0.95rem;color:#1e1b4b;">
                        <i class="bi bi-graph-up-arrow me-2" style="color:#7c3aed;"></i>Tren Kehadiran Harian
                    </div>
                    <div class="text-muted d-none d-sm-block" style="font-size:0.75rem;margin-top:2px;">Perkembangan status hadir, terlambat, izin/sakit per tanggal di bulan {{ $periodLabel }}</div>
                </div>
                <span class="badge bg-light text-primary border" style="font-size:0.7rem;">Grafik Garis</span>
            </div>
            <div class="card-body p-3">
                <div class="chart-container">
                    <canvas id="trendChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    {{-- Komposisi Status --}}
    <div class="col-lg-4">
        <div class="card chart-card h-100 shadow-sm">
            <div class="card-header">
                <div class="fw-bold" style="font-size:0.95rem;color:#1e1b4b;">
                    <i class="bi bi-pie-chart me-2" style="color:#7c3aed;"></i>Distribusi Status
                </div>
                <div class="text-muted" style="font-size:0.75rem;margin-top:2px;">Komposisi bulan {{ $periodLabel }}</div>
            </div>
            <div class="card-body p-3 d-flex flex-column align-items-center justify-content-center">
                <div class="donut-container mb-3">
                    <canvas id="distributionChart"></canvas>
                </div>
                <div class="w-100 d-flex justify-content-around pt-2 border-top">
                    @foreach([
                        ['label'=>'Hadir','val'=>$kpi['hadir_count'],'color'=>'#059669'],
                        ['label'=>'Lambat','val'=>$kpi['late_count'],'color'=>'#d97706'],
                        ['label'=>'Izin/Skt','val'=>$kpi['izin_count']+$kpi['sakit_count'],'color'=>'#0891b2'],
                        ['label'=>'Alpa','val'=>$kpi['alpa_count'],'color'=>'#e11d48']
                    ] as $item)
                    <div class="text-center">
                        <div style="font-size:0.62rem;color:#9ca3af;text-transform:uppercase;letter-spacing:0.04em;">{{ $item['label'] }}</div>
                        <div class="fw-bold" style="font-size:1.05rem;color:{{ $item['color'] }};">{{ $item['val'] }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════ TABEL LAPORAN KEHADIRAN ══════════════ --}}
<div class="card report-table-card shadow-sm mb-4">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <div>
            <div class="fw-bold" style="font-size:0.95rem;color:#1e1b4b;">
                <i class="bi bi-table me-2" style="color:#7c3aed;"></i>Rincian Catatan Presensi
            </div>
            <div class="text-muted d-none d-sm-block" style="font-size:0.75rem;margin-top:2px;">
                Menampilkan {{ $attendances->firstItem() ?? 0 }} - {{ $attendances->lastItem() ?? 0 }} dari {{ $attendances->total() }} catatan
            </div>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="ps-3" style="width: 50px;">NO</th>
                        <th>PEGAWAI</th>
                        <th class="hide-mobile">UNIT / JABATAN</th>
                        <th>TANGGAL & HARI</th>
                        <th class="text-center">MASUK</th>
                        <th class="text-center hide-mobile">PULANG</th>
                        <th class="text-center">STATUS</th>
                        <th class="hide-mobile">KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $idx => $att)
                    <tr>
                        <td class="ps-3 text-muted" style="font-size:0.8rem;">
                            {{ $attendances->firstItem() + $idx }}
                        </td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ $att->user?->avatar_url }}"
                                     class="rounded-circle flex-shrink-0"
                                     width="32" height="32"
                                     style="object-fit:cover;border:2px solid rgba(124,58,237,0.2);">
                                <div>
                                    <div class="fw-semibold text-truncate" style="font-size:0.85rem;color:#1e1b4b;max-width:180px;">
                                        {{ $att->user?->name ?? 'Pegawai Dihapus' }}
                                    </div>
                                    <div style="font-size:0.7rem;color:#9ca3af;">
                                        NIP: {{ $att->user?->nip ?: '-' }}
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="hide-mobile">
                            <div style="font-size:0.82rem;font-weight:500;color:#374151;">
                                {{ $att->user?->units->pluck('name')->implode(', ') ?: ($att->user?->department ?: '-') }}
                            </div>
                            <div style="font-size:0.7rem;color:#9ca3af;">{{ $att->user?->position ?: 'Pegawai' }}</div>
                        </td>
                        <td>
                            <div style="font-size:0.84rem;font-weight:500;color:#1e1b4b;">
                                {{ $att->attendance_date?->translatedFormat('d M Y') }}
                            </div>
                            <div style="font-size:0.72rem;color:#9ca3af;">
                                {{ $att->attendance_date?->translatedFormat('l') }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($att->check_in)
                                <span class="badge" style="background:#f3f4f6;color:#374151;font-family:monospace;font-size:0.78rem;border:1px solid #e5e7eb;">
                                    {{ $att->formatted_check_in }}
                                </span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td class="text-center hide-mobile">
                            @if($att->check_out)
                                <span class="badge" style="background:#f3f4f6;color:#374151;font-family:monospace;font-size:0.78rem;border:1px solid #e5e7eb;">
                                    {{ $att->formatted_check_out }}
                                </span>
                            @else
                                <span style="color:#d1d5db;">—</span>
                            @endif
                        </td>
                        <td class="text-center">{!! $att->status_badge !!}</td>
                        <td class="hide-mobile">
                            <span style="font-size:0.8rem;color:#6b7280;">{{ $att->notes ?: '-' }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5">
                            <i class="bi bi-calendar-x" style="font-size:2.5rem;color:#cbd5e1;display:block;margin-bottom:0.5rem;"></i>
                            <div class="fw-semibold text-muted">Tidak ada data presensi pada periode yang dipilih.</div>
                            <p class="text-muted small mb-0">Coba ubah filter bulan, tahun, atau kata kunci pencarian.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($attendances->hasPages())
        <div class="p-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="text-muted small">
                Menampilkan data {{ $attendances->firstItem() }} sampai {{ $attendances->lastItem() }} dari total {{ $attendances->total() }}
            </div>
            <div>
                {{ $attendances->links('pagination::bootstrap-5') }}
            </div>
        </div>
        @endif
    </div>
</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Line Area Chart - Tren Kehadiran
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        const cd = @json($trendChart);
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: cd.labels.length ? cd.labels : ['Belum Ada Data'],
                datasets: [
                    {
                        label: 'Hadir Tepat',
                        data: cd.hadir.length ? cd.hadir : [0],
                        borderColor: '#059669',
                        backgroundColor: 'rgba(5,150,105,0.1)',
                        fill: true, tension: 0.35, borderWidth: 2.5,
                        pointRadius: 4, pointHoverRadius: 6,
                        pointBackgroundColor: '#059669',
                        pointBorderColor: '#fff', pointBorderWidth: 2,
                    },
                    {
                        label: 'Terlambat',
                        data: cd.terlambat.length ? cd.terlambat : [0],
                        borderColor: '#d97706',
                        backgroundColor: 'rgba(217,119,6,0.1)',
                        fill: true, tension: 0.35, borderWidth: 2.5,
                        pointRadius: 4, pointHoverRadius: 6,
                        pointBackgroundColor: '#d97706',
                        pointBorderColor: '#fff', pointBorderWidth: 2,
                    },
                    {
                        label: 'Izin / Sakit',
                        data: cd.izin_sakit.length ? cd.izin_sakit : [0],
                        borderColor: '#0891b2',
                        backgroundColor: 'rgba(8,145,178,0.1)',
                        fill: true, tension: 0.35, borderWidth: 2,
                        pointRadius: 3, pointHoverRadius: 5,
                        pointBackgroundColor: '#0891b2',
                        pointBorderColor: '#fff', pointBorderWidth: 2,
                    }
                ]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 10, usePointStyle: true, font: { family: 'Inter', size: 11 }, padding: 14 }
                    },
                    tooltip: {
                        mode: 'index', intersect: false,
                        backgroundColor: '#1e1b4b', padding: 12, cornerRadius: 10,
                        titleFont: { family: 'Plus Jakarta Sans', size: 13, weight: 'bold' },
                        bodyFont: { family: 'Inter', size: 12 }
                    }
                },
                scales: {
                    y: { beginAtZero: true, ticks: { precision: 0, font: { family: 'Inter', size: 11 } }, grid: { color: 'rgba(99,102,241,0.06)' } },
                    x: { ticks: { font: { family: 'Inter', size: 10 }, maxRotation: 30 }, grid: { display: false } }
                }
            }
        });
    }

    // Doughnut Chart - Distribusi Status
    const distCtx = document.getElementById('distributionChart');
    if (distCtx) {
        const dd = @json($distributionChart);
        const total = dd.data.reduce((a, b) => a + b, 0);
        new Chart(distCtx, {
            type: 'doughnut',
            data: {
                labels: dd.labels,
                datasets: [{
                    data: total > 0 ? dd.data : [1],
                    backgroundColor: total > 0
                        ? ['#059669','#d97706','#0891b2','#7c3aed','#e11d48']
                        : ['#f3f4f6'],
                    borderWidth: 3,
                    borderColor: '#fff',
                    hoverOffset: 6,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                cutout: '74%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: total > 0,
                        backgroundColor: '#1e1b4b', cornerRadius: 10,
                        callbacks: {
                            label: ctx => {
                                const pct = total > 0 ? Math.round((ctx.raw / total) * 100) : 0;
                                return ` ${ctx.label}: ${ctx.raw} (${pct}%)`;
                            }
                        }
                    }
                }
            }
        });
    }
});
</script>
@endsection
