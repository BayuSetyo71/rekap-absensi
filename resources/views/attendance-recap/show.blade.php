@extends('layouts.app')

@section('title', 'Detail Rekap Presensi - ' . $user->name)
@section('page-title', 'Detail Rekap Presensi Pegawai')
@section('page-subtitle', 'Rincian riwayat kehadiran dan analisis performa individual')

@section('styles')
<style>
    .profile-hero {
        background: linear-gradient(135deg, #1e1b4b 0%, #312e81 60%, #4338ca 100%);
        border-radius: 16px;
        color: #fff;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        box-shadow: 0 10px 20px -5px rgba(79, 70, 229, 0.2);
    }
    .profile-avatar-lg {
        width: 72px;
        height: 72px;
        object-fit: cover;
        border-radius: 16px;
        border: 3px solid rgba(255, 255, 255, 0.2);
    }
    .stat-card-mini {
        background: #fff;
        border-radius: 12px;
        padding: 1rem;
        border: 1px solid #e2e8f0;
        text-align: center;
        transition: transform 0.2s;
    }
    .stat-card-mini:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
<div class="container-fluid px-0">

    <!-- ════════════════════════════════════════════
         1. HERO HEADER IDENTITAS PEGAWAI
    ════════════════════════════════════════════ -->
    <div class="profile-hero">
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
                        <span><i class="bi bi-envelope me-1"></i> {{ $user->email }}</span>
                    </div>
                </div>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('attendance-recap.chart', ['user' => $user->id, 'start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-outline-light btn-sm rounded-pill px-3">
                    <i class="bi bi-graph-up me-1"></i> Buka Halaman Grafik
                </a>
                <a href="{{ route('attendance-recap.index', ['start_date' => $startDate, 'end_date' => $endDate]) }}" class="btn btn-light btn-sm rounded-pill px-3">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Rekap
                </a>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         2. KPI SUMMARY CARDS
    ════════════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card-mini shadow-sm">
                <div class="text-muted small fw-semibold">TOTAL HARI</div>
                <div class="fw-bold text-dark fs-4 mt-1">{{ $stats['total_logs'] }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">Catatan Absen</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card-mini shadow-sm">
                <div class="text-muted small fw-semibold">CHECK-IN</div>
                <div class="fw-bold text-indigo fs-4 mt-1">{{ $stats['check_in_count'] }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">Scan Masuk</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card-mini shadow-sm">
                <div class="text-muted small fw-semibold">CHECK-OUT</div>
                <div class="fw-bold text-purple fs-4 mt-1">{{ $stats['check_out_count'] }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">Scan Pulang</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card-mini shadow-sm border-emerald" style="border-color: #a7f3d0 !important;">
                <div class="text-success small fw-semibold"><i class="bi bi-award-fill me-1"></i>DATANG AWAL</div>
                <div class="fw-bold text-success fs-4 mt-1">{{ $stats['total_early_formatted'] }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">Sebelum 07:30</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card-mini shadow-sm">
                <div class="text-warning small fw-semibold"><i class="bi bi-clock-history me-1"></i>TERLAMBAT</div>
                <div class="fw-bold text-warning fs-4 mt-1">{{ $stats['total_late_formatted'] }}</div>
                <div class="text-muted" style="font-size: 0.7rem;">{{ $stats['late_count'] }}x Keterlambatan</div>
            </div>
        </div>
        <div class="col-6 col-sm-4 col-md-2">
            <div class="stat-card-mini shadow-sm">
                <div class="text-muted small fw-semibold">% KEHADIRAN</div>
                <div class="fw-bold text-primary fs-4 mt-1">{{ $stats['attendance_percentage'] }}%</div>
                <div class="text-muted" style="font-size: 0.7rem;">Tingkat Rasio</div>
            </div>
        </div>
    </div>

    <!-- ════════════════════════════════════════════
         3. FILTER PERIODE & LOG DETAIL TABEL
    ════════════════════════════════════════════ -->
    <div class="card border-0 shadow-sm mb-4" style="border-radius: 14px;">
        <div class="card-body p-3">
            <form action="{{ route('attendance-recap.show', $user->id) }}" method="GET" class="row g-2 align-items-center">
                <div class="col-auto">
                    <label class="fw-semibold text-muted small"><i class="bi bi-calendar3 me-1"></i> Periode:</label>
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
                        <i class="bi bi-filter me-1"></i> Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm" style="border-radius: 14px; overflow: hidden;">
        <div class="card-header bg-white py-3 border-bottom">
            <h5 class="fw-bold mb-0 text-dark" style="font-size: 1rem;">
                <i class="bi bi-clock-history text-primary me-2"></i>Log Riwayat Presensi Harian
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 0.88rem;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3 text-center" style="width: 50px;">NO</th>
                        <th>TANGGAL & HARI</th>
                        <th class="text-center">JAM MASUK</th>
                        <th class="text-center">JAM PULANG</th>
                        <th class="text-center">DURASI KERJA</th>
                        <th class="text-center">STATUS</th>
                        <th>KETERANGAN</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $item)
                        @php
                            $workDuration = '-';
                            if ($item->check_in && $item->check_out) {
                                $inTime  = \Carbon\Carbon::createFromTimeString($item->check_in);
                                $outTime = \Carbon\Carbon::createFromTimeString($item->check_out);
                                if ($outTime->gte($inTime)) {
                                    $h = $inTime->diffInHours($outTime);
                                    $m = $inTime->diffInMinutes($outTime) % 60;
                                    $workDuration = "{$h}j {$m}m";
                                }
                            }
                        @endphp
                        <tr>
                            <td class="text-center text-muted fw-semibold">{{ $loop->iteration + ($attendances->currentPage() - 1) * $attendances->perPage() }}</td>
                            <td class="ps-3 fw-semibold text-dark">
                                {{ $item->attendance_date ? \Carbon\Carbon::parse($item->attendance_date)->translatedFormat('l, d F Y') : '-' }}
                            </td>
                            <td class="text-center">
                                @if($item->check_in)
                                    <span class="badge bg-light text-dark border font-monospace">{{ substr($item->check_in, 0, 5) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($item->check_out)
                                    <span class="badge bg-light text-dark border font-monospace">{{ substr($item->check_out, 0, 5) }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center fw-semibold text-dark small">{{ $workDuration }}</td>
                            <td class="text-center">{!! $item->status_badge !!}</td>
                            <td class="text-muted small">{{ $item->notes ?: '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                Tidak ada catatan kehadiran pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($attendances->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $attendances->links() }}
            </div>
        @endif
    </div>

</div>
@endsection
