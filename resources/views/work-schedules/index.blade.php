@extends('layouts.app')

@section('title', 'Jam Kerja Pegawai')
@section('page-title', 'Jam Kerja Pegawai & Multi-Jenjang Yayasan')
@section('page-subtitle', 'Pengaturan jam mengajar fleksibel per hari, multi-sesi lintas jenjang (TK, SD, SMP, SMA), dan jam kerja operasional')

@section('content')
<div class="row g-4 mb-4">
    <!-- ALERT NOTIFIKASI: Pegawai yang Belum Diatur Jam Kerjanya (Task 2) -->
    @if($unassignedCount > 0)
        <div class="col-12">
            <div class="alert alert-warning border-warning d-flex align-items-center justify-content-between p-3 rounded-3 shadow-sm mb-0">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-warning text-dark p-2.5 d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                        <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-dark">Perhatian: Terdapat {{ $unassignedCount }} Pegawai Belum Memiliki Jam Kerja</h6>
                        <p class="mb-0 text-dark opacity-75 small">
                            Data pegawai baru dari hasil import Excel memerlukan konfigurasi jam kerja dan penugasan jenjang (TK, SD, SMP, SMA) agar pencatatan presensi absensi akurat.
                        </p>
                    </div>
                </div>
                <div>
                    <a href="{{ route('work-schedules.index', ['assignment_type' => 'unassigned']) }}" class="btn btn-warning btn-sm fw-bold px-3 shadow-sm text-dark">
                        <i class="bi bi-funnel-fill me-1"></i> Tampilkan Pegawai Belum Diatur ({{ $unassignedCount }})
                    </a>
                </div>
            </div>
        </div>
    @endif

    <!-- Ringkasan KPI Statistik -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 bg-primary text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-white-50 small fw-medium text-uppercase">Total Pegawai</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['total_employees'] }}</h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">Terdaftar di sistem</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-people-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #059669 0%, #065f46 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-white-50 small fw-medium text-uppercase">Guru Multi-Jenjang</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['multi_unit'] }}</h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">Mengajar > 1 Jenjang (TK - SMA)</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-layers-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #2563eb 0%, #1e40af 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-white-50 small fw-medium text-uppercase">Guru Single Unit</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['single_unit'] }}</h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">Mengajar di 1 Unit Khusus</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-mortarboard-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #d97706 0%, #92400e 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-white-50 small fw-medium text-uppercase">Belum Diatur</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['unassigned'] }}</h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">Perlu di-set jam kerjanya</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-clock-history fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigasi Tab Utama -->
    <div class="col-12">
        <ul class="nav nav-pills custom-nav-pills p-1 bg-white rounded-3 shadow-sm border mb-3" id="scheduleTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="employee-tab" data-bs-toggle="pill" data-bs-target="#employee-pane" type="button" role="tab">
                    <i class="bi bi-person-badge-fill text-primary"></i>
                    <span>Jadwal Pegawai & Guru</span>
                    @if($unassignedCount > 0)
                        <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">{{ $unassignedCount }} Belum Diatur</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="unit-tab" data-bs-toggle="pill" data-bs-target="#unit-pane" type="button" role="tab">
                    <i class="bi bi-building-gear text-warning"></i>
                    <span>Master Unit & Jam Standar Yayasan</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="scheduleTabContent">
            <!-- TAB 1: Jadwal Pegawai & Guru -->
            <div class="tab-pane fade show active" id="employee-pane" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <!-- Filter Toolbar -->
                    <div class="card-header bg-transparent py-3 border-bottom">
                        <form action="{{ route('work-schedules.index') }}" method="GET" class="row g-2 align-items-center">
                            <!-- Search -->
                            <div class="col-md-3">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama guru, NIP, mapel...">
                                </div>
                            </div>

                            <!-- Filter Unit -->
                            <div class="col-md-2">
                                <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Semua Unit --</option>
                                    @foreach($units as $u)
                                        <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                            {{ $u->name }} ({{ $u->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Multi-Unit & Status -->
                            <div class="col-md-2">
                                <select name="assignment_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">-- Tipe Penugasan --</option>
                                    <option value="multi" {{ request('assignment_type') === 'multi' ? 'selected' : '' }}>Multi-Jenjang (> 1 Unit)</option>
                                    <option value="single" {{ request('assignment_type') === 'single' ? 'selected' : '' }}>Single Unit (1 Unit)</option>
                                    <option value="unassigned" {{ request('assignment_type') === 'unassigned' ? 'selected' : '' }}>⚠️ Belum Diatur ({{ $unassignedCount }})</option>
                                </select>
                            </div>

                            <!-- Buttons -->
                            <div class="col-md-5 text-md-end d-flex gap-2 justify-content-md-end">
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-funnel me-1"></i> Filter
                                </button>

                                @canExport('work-schedules')
                                <a href="{{ route('work-schedules.export', request()->query()) }}" class="btn btn-outline-success btn-sm" title="Unduh Matriks Jadwal ke Excel">
                                    <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                                </a>
                                @endcanExport

                                @canUpdate('work-schedules')
                                <button type="button" class="btn btn-outline-primary btn-sm" id="btnBulkAssignModal">
                                    <i class="bi bi-magic me-1"></i> Terapkan Preset Massal
                                </button>
                                @endcanUpdate
                            </div>
                        </form>
                    </div>

                    <!-- Tabel Daftar Pegawai & Jam Kerja -->
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">#</th>
                                        <th style="min-width: 200px;">Pegawai / Guru</th>
                                        <th style="min-width: 140px;">Unit Diampu</th>
                                        <th style="min-width: 110px;" class="text-center">Senin</th>
                                        <th style="min-width: 110px;" class="text-center">Selasa</th>
                                        <th style="min-width: 110px;" class="text-center">Rabu</th>
                                        <th style="min-width: 110px;" class="text-center">Kamis</th>
                                        <th style="min-width: 110px;" class="text-center">Jumat</th>
                                        <th style="min-width: 90px;" class="text-center">Sabtu</th>
                                        <th style="min-width: 90px;" class="text-center">Minggu</th>
                                        <th style="width: 100px;" class="text-end">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($employees as $index => $emp)
                                        @php
                                            $isConfigured = $emp->hasConfiguredSchedule();
                                        @endphp
                                        <tr class="{{ !$isConfigured ? 'table-warning bg-opacity-25' : '' }}">
                                            <td class="text-center fw-semibold text-muted">{{ $employees->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $emp->avatar_url }}" alt="{{ $emp->name }}" class="rounded-circle me-2.5 border" width="38" height="38">
                                                    <div>
                                                        <div class="fw-bold text-dark d-flex align-items-center gap-1.5" style="font-size: 0.88rem;">
                                                            <span>{{ $emp->name }}</span>
                                                            @if(!$isConfigured)
                                                                <span class="badge bg-danger text-white py-0.5 px-2 fw-bold shadow-sm" style="font-size: 0.65rem; background-color: #dc2626 !important; color: #ffffff !important;">
                                                                    Belum Diatur
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                            {{ $emp->position ?: ($emp->department ?: 'Guru / Pegawai') }} 
                                                            @if($emp->nip) • NIP: {{ $emp->nip }} @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @forelse($emp->units as $u)
                                                        <span class="badge rounded-pill px-2 py-0.5" style="font-size: 0.7rem; background-color: {{ $u->color }}20; color: {{ $u->color }}; border: 1px solid {{ $u->color }}50;">
                                                            {{ $u->code }}
                                                        </span>
                                                    @empty
                                                        @if($isConfigured)
                                                            <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">Yayasan</span>
                                                        @else
                                                            <span class="badge bg-warning text-dark border border-warning fw-semibold" style="font-size: 0.68rem;">Belum Diatur</span>
                                                        @endif
                                                    @endforelse

                                                    @if($emp->units->count() > 1)
                                                        <span class="badge bg-success text-white px-2 py-0.5 fw-bold shadow-sm" style="font-size: 0.68rem; background-color: #059669 !important; color: #ffffff !important;" title="Guru mengajar di beberapa jenjang">
                                                            Multi
                                                        </span>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- 7 Hari Jadwal & Multi-Sesi Mengajar -->
                                            @for($day = 1; $day <= 7; $day++)
                                                @php $daySch = $emp->getWorkScheduleForDay($day); @endphp
                                                <td class="text-center p-1.5">
                                                    @if(!$isConfigured)
                                                        <span class="badge bg-light text-muted border border-dashed px-1.5 py-1" style="font-size: 0.65rem;">
                                                            -
                                                        </span>
                                                    @elseif($daySch->is_day_off || (empty($daySch->time_in) && (!$daySch->slots || $daySch->slots->isEmpty())))
                                                        <span class="badge bg-light text-muted border px-1.5 py-1" style="font-size: 0.68rem; font-weight: 500;">
                                                            Libur
                                                        </span>
                                                    @elseif($daySch->slots && $daySch->slots->isNotEmpty())
                                                        <!-- Tampilan Multi-Sesi Mengajar dalam 1 Hari -->
                                                        <div class="d-flex flex-column gap-1">
                                                            @foreach($daySch->slots as $s)
                                                                <div class="p-1 rounded text-start" style="background-color: {{ $s->unit ? $s->unit->color.'12' : '#f3f4f6' }}; border: 1px solid {{ $s->unit ? $s->unit->color.'35' : '#d1d5db' }}; line-height: 1.1;">
                                                                    <div class="d-flex align-items-center justify-content-between gap-1">
                                                                        <span class="fw-bold" style="font-size: 0.68rem; color: {{ $s->unit ? $s->unit->color : '#111827' }};">
                                                                            {{ $s->unit ? $s->unit->code : 'Unit' }}
                                                                        </span>
                                                                        <span class="fw-semibold text-dark" style="font-size: 0.65rem;">
                                                                            {{ $s->formatted_start_time }}-{{ $s->formatted_end_time }}
                                                                        </span>
                                                                    </div>
                                                                    @if($s->subject)
                                                                        <div class="text-muted text-truncate mt-0.5" style="font-size: 0.6rem; max-width: 105px;" title="{{ $s->subject }}">
                                                                            {{ $s->subject }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($daySch->time_in && $daySch->time_out)
                                                        <!-- Jadwal Standar Single Shift -->
                                                        <div class="p-1 rounded" style="background-color: {{ $daySch->unit ? $daySch->unit->color.'10' : '#f3f4f6' }}; border: 1px dashed {{ $daySch->unit ? $daySch->unit->color.'40' : '#d1d5db' }};">
                                                            <div class="fw-bold text-dark" style="font-size: 0.74rem;">
                                                                {{ substr($daySch->time_in, 0, 5) }} - {{ substr($daySch->time_out, 0, 5) }}
                                                            </div>
                                                            @if($daySch->unit)
                                                                <span class="d-inline-block fw-semibold" style="font-size: 0.62rem; color: {{ $daySch->unit->color }};">
                                                                    {{ $daySch->unit->code }}
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="badge bg-light text-muted border px-1.5 py-1" style="font-size: 0.68rem; font-weight: 500;">
                                                            Libur
                                                        </span>
                                                    @endif
                                                </td>
                                            @endfor

                                            <td class="text-end">
                                                @canUpdate('work-schedules')
                                                <button type="button" class="btn {{ !$isConfigured ? 'btn-warning text-dark' : 'btn-primary' }} btn-sm btn-edit-schedule d-inline-flex align-items-center gap-1 px-2.5 py-1" data-id="{{ $emp->id }}" title="Atur Jam Kerja Guru">
                                                    <i class="bi bi-clock-history"></i>
                                                    <span class="d-none d-md-inline" style="font-size: 0.78rem;">{{ !$isConfigured ? 'Atur Sekarang' : 'Atur' }}</span>
                                                </button>
                                                @else
                                                <button type="button" class="btn btn-light btn-sm border btn-edit-schedule" data-id="{{ $emp->id }}" title="Lihat Jadwal">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                                @endcanUpdate
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5 text-muted">
                                                <i class="bi bi-clock-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                                Tidak ditemukan data pegawai yang sesuai dengan filter.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Pagination -->
                    @if($employees->hasPages())
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="text-muted small">Menampilkan {{ $employees->firstItem() }} - {{ $employees->lastItem() }} dari total {{ $employees->total() }} pegawai</span>
                            {{ $employees->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 2: Master Unit Yayasan -->
            <div class="tab-pane fade" id="unit-pane" role="tabpanel">
                <div class="row g-4">
                    @foreach($units as $unit)
                        <div class="col-lg-6">
                            <div class="card shadow-sm border-0 h-100">
                                <div class="card-header bg-transparent py-3 d-flex align-items-center justify-content-between border-bottom">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="p-2 rounded-circle text-white d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; background-color: {{ $unit->color }};">
                                            <i class="bi bi-building"></i>
                                        </span>
                                        <div>
                                            <h5 class="fw-bold mb-0 text-dark">{{ $unit->name }}</h5>
                                            <small class="text-muted">Kode Unit: <span class="badge" style="background-color: {{ $unit->color }};">{{ $unit->code }}</span></small>
                                        </div>
                                    </div>

                                    @canUpdate('work-schedules')
                                    <button type="button" class="btn btn-outline-secondary btn-sm btn-edit-unit" data-unit='@json($unit)'>
                                        <i class="bi bi-gear-fill me-1"></i> Edit Standar
                                    </button>
                                    @endcanUpdate
                                </div>
                                <div class="card-body">
                                    <p class="text-muted small mb-3">{{ $unit->description ?: 'Unit pendidikan di bawah naungan yayasan.' }}</p>

                                    <div class="row g-2 mb-3">
                                        <div class="col-4">
                                            <div class="p-2.5 rounded bg-light border text-center">
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Default Masuk</small>
                                                <strong class="text-primary fs-6">{{ $unit->formatted_time_in }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2.5 rounded bg-light border text-center">
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Default Pulang</small>
                                                <strong class="text-indigo fs-6">{{ $unit->formatted_time_out }}</strong>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <div class="p-2.5 rounded bg-light border text-center">
                                                <small class="text-muted d-block" style="font-size: 0.7rem;">Toleransi</small>
                                                <strong class="text-warning fs-6">{{ $unit->default_late_tolerance }} Menit</strong>
                                            </div>
                                        </div>
                                    </div>

                                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.8rem;">Jadwal Standar Mingguan (Senin - Minggu):</h6>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered align-middle mb-0 text-center" style="font-size: 0.75rem;">
                                            <thead class="table-light">
                                                <tr>
                                                    <th>Hari</th>
                                                    <th>Jam Masuk</th>
                                                    <th>Jam Pulang</th>
                                                    <th>Status</th>
                                                    <th>Keterangan</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($unit->schedules as $sch)
                                                    <tr class="{{ $sch->is_day_off ? 'table-light text-muted' : '' }}">
                                                        <td class="fw-semibold text-start ps-2">{{ $sch->day_name }}</td>
                                                        <td>{{ $sch->is_day_off ? '-' : substr($sch->time_in, 0, 5) }}</td>
                                                        <td>{{ $sch->is_day_off ? '-' : substr($sch->time_out, 0, 5) }}</td>
                                                        <td>
                                                            @if($sch->is_day_off)
                                                                <span class="badge bg-secondary">Libur</span>
                                                            @else
                                                                <span class="badge bg-success">Aktif</span>
                                                            @endif
                                                        </td>
                                                        <td class="text-start ps-2">{{ $sch->notes ?: '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL 1: Pengaturan Jam Kerja Guru Multi-Sesi per Hari (Dynamic jQuery AJAX) -->
<div class="modal fade" id="modalEmployeeSchedule" tabindex="-1" aria-labelledby="modalEmployeeScheduleTitle" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-height: 92vh;">
        <form id="formEmployeeSchedule" class="modal-content border-0 shadow" style="max-height: 92vh; display: flex; flex-direction: column; overflow: hidden;">
            <div class="modal-header bg-light py-3 border-bottom flex-shrink-0">
                <div class="d-flex align-items-center gap-3">
                    <img id="modal_emp_avatar" src="" alt="Avatar" class="rounded-circle border" width="45" height="45">
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-dark" id="modalEmployeeScheduleTitle">Atur Jam Kerja Guru</h5>
                        <small class="text-muted" id="modal_emp_info">Memuat data pegawai...</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 flex-grow-1" style="overflow-y: auto; max-height: calc(92vh - 135px);">
                <input type="hidden" id="emp_user_id" name="user_id">

                    <!-- SECTION A: Unit yang Diampu / Multi-Jenjang -->
                    <div class="card bg-light border-0 p-3 mb-4 rounded-3">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-mortarboard-fill text-primary"></i>
                                <span>1. Unit Sekolah yang Diampu / Ditugaskan</span>
                            </h6>
                            <span class="badge bg-primary bg-opacity-10 text-primary">Bisa pilih > 1 unit jika mengajar di beberapa jenjang</span>
                        </div>
                        <p class="text-muted small mb-3">Centang seluruh jenjang sekolah tempat guru ini mengajar (misal mengajar di TK, SD, SMP, hingga SMA):</p>
                        
                        <div class="row g-3" id="containerUnitCheckboxes">
                            @foreach($units as $u)
                                <div class="col-md-4 col-sm-6">
                                    <div class="form-check p-2.5 rounded border bg-white h-100 d-flex align-items-center gap-2">
                                        <input class="form-check-input check-unit-item ms-0 me-2" type="checkbox" name="unit_ids[]" value="{{ $u->id }}" id="unit_check_{{ $u->id }}" data-code="{{ $u->code }}">
                                        <label class="form-check-label fw-semibold text-dark flex-grow-1 cursor-pointer" for="unit_check_{{ $u->id }}">
                                            <span class="badge rounded-pill me-1" style="background-color: {{ $u->color }}; color: #fff;">{{ $u->code }}</span>
                                            {{ $u->name }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- SECTION B: Tombol Preset Cepat (1-Click Generator) -->
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        <div>
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="bi bi-calendar2-week-fill text-indigo"></i>
                                <span>2. Konfigurasi Sesi Mengajar Fleksibel (Senin - Minggu)</span>
                            </h6>
                            <small class="text-muted">Tambahkan sesi mengajar di berbagai unit dalam 1 hari yang sama (misal SD 07.30-08.30 lalu SMP 08.30-09.30)</small>
                        </div>

                        <!-- Preset Buttons -->
                        <div class="d-flex flex-wrap gap-1.5 align-items-center">
                            <span class="small text-muted me-1 fw-semibold"><i class="bi bi-lightning-charge-fill text-warning"></i> Preset Cepat:</span>
                            @foreach($units as $u)
                                <button type="button" class="btn btn-outline-secondary btn-sm btn-apply-preset py-0.5 px-2" style="font-size: 0.75rem;" data-unit-id="{{ $u->id }}">
                                    Terapkan {{ $u->code }}
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <!-- SECTION C: Tab 7 Hari (Senin s.d. Minggu) dengan Slot Mengajar Dinamis -->
                    <div class="card border shadow-none mb-0">
                        <div class="card-header bg-white p-2 border-bottom">
                            <ul class="nav nav-pills nav-fill gap-1" id="daysTab" role="tablist">
                                @php $dayTabs = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu']; @endphp
                                @foreach($dayTabs as $dayNum => $dayName)
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link py-2 px-1 fw-bold {{ $dayNum === 1 ? 'active' : '' }}" id="tab-btn-day-{{ $dayNum }}" data-bs-toggle="pill" data-bs-target="#day-pane-{{ $dayNum }}" type="button" role="tab" style="font-size: 0.8rem;">
                                            {{ $dayName }}
                                            <span class="badge bg-secondary rounded-pill ms-1 badge-day-count" id="badge_count_day_{{ $dayNum }}">0</span>
                                        </button>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                        <div class="card-body p-3">
                            <div class="tab-content" id="daysTabContent">
                                @foreach($dayTabs as $dayNum => $dayName)
                                    <div class="tab-pane fade {{ $dayNum === 1 ? 'show active' : '' }}" id="day-pane-{{ $dayNum }}" role="tabpanel" data-day="{{ $dayNum }}">
                                        <input type="hidden" name="days[{{ $dayNum }}][day_of_week]" value="{{ $dayNum }}">

                                        <!-- Header Hari: Status Libur vs Aktif -->
                                        <div class="d-flex flex-wrap align-items-center justify-content-between p-2.5 mb-3 rounded bg-light border">
                                            <div class="d-flex align-items-center gap-3">
                                                <div class="form-check form-switch mb-0">
                                                    <input class="form-check-input switch-day-toggle" type="checkbox" role="switch" id="switch_day_active_{{ $dayNum }}" data-day="{{ $dayNum }}" checked>
                                                    <input type="hidden" name="days[{{ $dayNum }}][is_day_off]" id="input_day_off_{{ $dayNum }}" value="0">
                                                    <label class="form-check-label fw-bold text-dark ms-1" for="switch_day_active_{{ $dayNum }}">
                                                        Hari {{ $dayName }} Aktif Mengajar
                                                    </label>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <!-- Tombol Salin Jadwal ke Hari Lain -->
                                                <div class="dropdown">
                                                    <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-inline-flex align-items-center gap-1 shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Salin seluruh sesi hari {{ $dayName }} ke hari lain">
                                                        <i class="bi bi-copy"></i>
                                                        <span class="d-none d-sm-inline">Salin ke Hari Lain...</span>
                                                        <span class="d-sm-none">Salin</span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 0.82rem; min-width: 250px;">
                                                        <li><h6 class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.68rem;"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Pilihan Cepat:</h6></li>
                                                        <li>
                                                            <a class="dropdown-item py-2 btn-copy-to-weekdays" href="javascript:void(0);" data-from-day="{{ $dayNum }}" data-from-name="{{ $dayName }}">
                                                                <i class="bi bi-calendar-check me-2 text-success"></i>Salin ke <strong>Senin - Jumat</strong> (Hari Kerja)
                                                            </a>
                                                        </li>
                                                        <li>
                                                            <a class="dropdown-item py-2 btn-copy-to-all" href="javascript:void(0);" data-from-day="{{ $dayNum }}" data-from-name="{{ $dayName }}">
                                                                <i class="bi bi-calendar-range me-2 text-primary"></i>Salin ke <strong>Semua Hari</strong> (Senin - Minggu)
                                                            </a>
                                                        </li>
                                                        <li><hr class="dropdown-divider my-1"></li>
                                                        <li><h6 class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.68rem;"><i class="bi bi-calendar-day text-secondary me-1"></i>Salin Khusus ke:</h6></li>
                                                        @foreach($dayTabs as $targetNum => $targetName)
                                                            @if($targetNum !== $dayNum)
                                                                <li>
                                                                    <a class="dropdown-item py-1.5 btn-copy-to-single" href="javascript:void(0);" data-from-day="{{ $dayNum }}" data-from-name="{{ $dayName }}" data-to-day="{{ $targetNum }}" data-to-name="{{ $targetName }}">
                                                                        <i class="bi bi-arrow-right-short me-1 text-primary"></i> Hari {{ $targetName }}
                                                                    </a>
                                                                </li>
                                                            @endif
                                                        @endforeach
                                                    </ul>
                                                </div>

                                                <!-- Tombol Tambah Sesi -->
                                                <button type="button" class="btn btn-primary btn-sm btn-add-slot d-inline-flex align-items-center gap-1 shadow-sm" data-day="{{ $dayNum }}">
                                                    <i class="bi bi-plus-circle-fill"></i>
                                                    <span>+ Tambah Sesi Hari {{ $dayName }}</span>
                                                </button>
                                            </div>
                                        </div>

                                        <!-- Container Daftar Sesi Mengajar -->
                                        <div class="container-slots" id="slots_container_{{ $dayNum }}">
                                            <!-- Baris slot mengajar dinamis di-render via jQuery -->
                                        </div>

                                        <!-- Pesan jika hari libur atau kosong -->
                                        <div class="alert alert-light text-center py-4 border border-dashed day-empty-notice" id="empty_notice_{{ $dayNum }}">
                                            <i class="bi bi-calendar-x text-muted fs-3 d-block mb-1"></i>
                                            <span class="text-muted small">Belum ada sesi mengajar di hari {{ $dayName }}. Klik tombol <strong>"+ Tambah Sesi Mengajar"</strong> di atas.</span>
                                        </div>

                                        <!-- Ringkasan Jam Masuk & Pulang Terkalkulasi Realtime -->
                                        <div class="d-flex align-items-center justify-content-between p-2.5 mt-3 rounded border bg-light day-summary-box" id="day_summary_{{ $dayNum }}">
                                            <div class="small text-muted">
                                                <i class="bi bi-info-circle me-1 text-primary"></i>
                                                <span>Jam masuk & pulang absensi hari {{ $dayName }} dihitung otomatis:</span>
                                            </div>
                                            <div class="d-flex align-items-center gap-3">
                                                <span class="small text-dark">Jam Masuk (Awal): <strong class="text-primary summary-in" id="summary_in_{{ $dayNum }}">-</strong></span>
                                                <span class="small text-dark">Jam Pulang (Akhir): <strong class="text-indigo summary-out" id="summary_out_{{ $dayNum }}">-</strong></span>
                                                <span class="badge bg-success text-white shadow-sm summary-duration" id="summary_dur_{{ $dayNum }}" style="background-color: #059669 !important; color: #ffffff !important; font-size: 0.78rem; font-weight: 700; padding: 5px 10px; border-radius: 6px;">0 Jam</span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-3 border-top flex-shrink-0" style="position: sticky; bottom: 0; z-index: 1055; background-color: #f8fafc !important;">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm fw-semibold" id="btnSimpanSchedule">
                        <i class="bi bi-save me-1"></i> Simpan Jam Kerja Guru
                    </button>
                </div>
            </form>
        </div>
    </div>

<!-- MODAL 2: Edit Master Unit Yayasan -->
<div class="modal fade" id="modalEditUnit" tabindex="-1" aria-labelledby="modalEditUnitTitle" aria-hidden="true">
    <div class="modal-dialog modal-md modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalEditUnitTitle">
                    <i class="bi bi-building-gear me-2 text-warning"></i>Edit Standar Unit Yayasan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditUnit">
                <div class="modal-body p-4">
                    <input type="hidden" id="edit_unit_id">

                    <div class="mb-3">
                        <label for="unit_name" class="form-label fw-semibold">Nama Unit Sekolah</label>
                        <input type="text" class="form-control" id="unit_name" name="name" required>
                    </div>

                    <div class="mb-3">
                        <label for="unit_color" class="form-label fw-semibold">Warna Identitas Unit (Hex Code)</label>
                        <div class="input-group">
                            <input type="color" class="form-control form-control-color" id="unit_color_picker" style="max-width: 50px;">
                            <input type="text" class="form-control" id="unit_color" name="color" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label for="unit_default_time_in" class="form-label fw-semibold">Jam Masuk Default</label>
                            <input type="time" class="form-control" id="unit_default_time_in" name="default_time_in" required>
                        </div>
                        <div class="col-6">
                            <label for="unit_default_time_out" class="form-label fw-semibold">Jam Pulang Default</label>
                            <input type="time" class="form-control" id="unit_default_time_out" name="default_time_out" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="unit_default_late_tolerance" class="form-label fw-semibold">Toleransi Keterlambatan (Menit)</label>
                        <input type="number" class="form-control" id="unit_default_late_tolerance" name="default_late_tolerance" min="0" max="120" required>
                    </div>

                    <div class="mb-3">
                        <label for="unit_description" class="form-label fw-semibold">Keterangan / Catatan</label>
                        <textarea class="form-control" id="unit_description" name="description" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanUnit">
                        <i class="bi bi-save me-1"></i> Simpan Standar Unit
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL 3: Bulk Assign Preset ke Banyak Pegawai Sekaligus -->
<div class="modal fade" id="modalBulkAssign" tabindex="-1" aria-labelledby="modalBulkAssignTitle" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalBulkAssignTitle">
                    <i class="bi bi-magic me-2 text-primary"></i>Terapkan Preset Jadwal Standar Massal
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formBulkAssign">
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <div class="small">Pilih unit standar yang ingin diterapkan dan centang pegawai yang akan diperbarui jadwalnya secara serentak.</div>
                    </div>

                    <div class="mb-3">
                        <label for="bulk_unit_id" class="form-label fw-semibold">Pilih Unit Sekolah Standar <span class="text-danger">*</span></label>
                        <select class="form-select" id="bulk_unit_id" name="unit_id" required>
                            <option value="">-- Pilih Unit --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }}) - Masuk: {{ $u->formatted_time_in }}, Pulang: {{ $u->formatted_time_out }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-2 d-flex justify-content-between align-items-center">
                        <label class="form-label fw-semibold mb-0">Pilih Pegawai / Guru:</label>
                        <div>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnSelectAllEmployees">Pilih Semua</button>
                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2" id="btnUnselectAllEmployees">Batal Semua</button>
                        </div>
                    </div>

                    <div class="border rounded p-3 bg-light" style="max-height: 250px; overflow-y: auto;">
                        <div class="row g-2">
                            @foreach($employees as $emp)
                                <div class="col-md-6">
                                    <div class="form-check">
                                        <input class="form-check-input check-bulk-emp" type="checkbox" name="user_ids[]" value="{{ $emp->id }}" id="bulk_emp_{{ $emp->id }}">
                                        <label class="form-check-label small" for="bulk_emp_{{ $emp->id }}">
                                            <strong>{{ $emp->name }}</strong> ({{ $emp->position ?: 'Guru' }})
                                            @if(!$emp->hasConfiguredSchedule())
                                                <span class="badge bg-warning text-dark ms-1" style="font-size: 0.62rem;">Belum Diatur</span>
                                            @endif
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanBulk">
                        <i class="bi bi-check-circle me-1"></i> Terapkan Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    const MASTER_UNITS = @json($units);

    // Template Baris Sesi Mengajar Dinamis
    function createSlotRowHtml(dayNum, slotIdx, slot = {}) {
        const unitId = slot.unit_id || '';
        const startTime = slot.start_time || '07:30';
        const endTime = slot.end_time || '08:30';
        const subject = slot.subject || '';
        const notes = slot.notes || '';

        let unitOptions = '<option value="">-- Pilih Jenjang --</option>';
        MASTER_UNITS.forEach(u => {
            const isSelected = (u.id == unitId) ? 'selected' : '';
            unitOptions += `<option value="${u.id}" ${isSelected} data-color="${u.color}">${u.name} (${u.code})</option>`;
        });

        const dayNames = {1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu', 7: 'Minggu'};
        let targetDayLinks = '';
        for (let d = 1; d <= 7; d++) {
            if (d != dayNum) {
                targetDayLinks += `
                    <li>
                        <a class="dropdown-item py-1 btn-copy-slot-to-target" href="javascript:void(0);" data-to-day="${d}">
                            <i class="bi bi-arrow-right-short me-1 text-primary"></i> Hari ${dayNames[d]}
                        </a>
                    </li>
                `;
            }
        }

        return `
            <div class="card border p-2.5 mb-2 slot-item-card" data-day="${dayNum}" data-slot-idx="${slotIdx}" style="background: #fafafa; border-radius: 8px;">
                <div class="row g-2 align-items-center">
                    <!-- Jenjang Unit -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label mb-1 text-muted fw-semibold" style="font-size: 0.7rem;">Jenjang Sekolah <span class="text-danger">*</span></label>
                        <select name="days[${dayNum}][slots][${slotIdx}][unit_id]" class="form-select form-select-sm select-slot-unit" required>
                            ${unitOptions}
                        </select>
                    </div>

                    <!-- Jam Mulai -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 text-muted fw-semibold" style="font-size: 0.7rem;">Jam Mulai <span class="text-danger">*</span></label>
                        <input type="time" name="days[${dayNum}][slots][${slotIdx}][start_time]" class="form-control form-control-sm text-center input-slot-start" value="${startTime}" required>
                    </div>

                    <!-- Jam Selesai -->
                    <div class="col-md-2 col-sm-6">
                        <label class="form-label mb-1 text-muted fw-semibold" style="font-size: 0.7rem;">Jam Selesai <span class="text-danger">*</span></label>
                        <input type="time" name="days[${dayNum}][slots][${slotIdx}][end_time]" class="form-control form-control-sm text-center input-slot-end" value="${endTime}" required>
                    </div>

                    <!-- Mata Pelajaran / Aktivitas -->
                    <div class="col-md-3 col-sm-6">
                        <label class="form-label mb-1 text-muted fw-semibold" style="font-size: 0.7rem;">Mata Pelajaran / Kelas</label>
                        <input type="text" name="days[${dayNum}][slots][${slotIdx}][subject]" class="form-control form-control-sm input-slot-subject" value="${subject}" placeholder="Contoh: IT SD / B.Inggris">
                    </div>

                    <!-- Tombol Aksi Per Jam (Salin Sesi Jam Ini, Duplikat Jam Ini, Hapus) -->
                    <div class="col-md-2 col-sm-12 text-end pt-md-3">
                        <div class="btn-group btn-group-sm w-100 shadow-sm" role="group">
                            <!-- Dropdown Salin Sesi Jam Ini ke Hari Lain -->
                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split px-2" data-bs-toggle="dropdown" aria-expanded="false" title="Salin jam sesi ini ke hari lain">
                                    <i class="bi bi-copy"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0" style="font-size: 0.8rem; min-width: 230px;">
                                    <li><h6 class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.65rem;"><i class="bi bi-clock-history me-1 text-warning"></i>Salin Jam Sesi Ini ke:</h6></li>
                                    <li><a class="dropdown-item py-1.5 btn-copy-slot-to-weekdays" href="javascript:void(0);"><i class="bi bi-calendar-check text-success me-2"></i>Senin - Jumat (Hari Kerja)</a></li>
                                    <li><a class="dropdown-item py-1.5 btn-copy-slot-to-all" href="javascript:void(0);"><i class="bi bi-calendar-range text-primary me-2"></i>Semua Hari (Senin - Minggu)</a></li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li><h6 class="dropdown-header text-uppercase fw-bold text-muted" style="font-size: 0.65rem;"><i class="bi bi-calendar-day text-secondary me-1"></i>Pilih Hari Tertentu:</h6></li>
                                    ${targetDayLinks}
                                </ul>
                            </div>

                            <!-- Duplikat ke Jam Berikutnya di Hari Ini -->
                            <button type="button" class="btn btn-outline-primary btn-duplicate-slot px-2" title="Duplikat sesi ini ke jam berikutnya di hari ini">
                                <i class="bi bi-plus-lg"></i>
                            </button>

                            <!-- Hapus Sesi -->
                            <button type="button" class="btn btn-outline-danger btn-delete-slot px-2" title="Hapus sesi mengajar ini">
                                <i class="bi bi-trash-fill"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    // Fungsi Recalculate Jam Masuk, Pulang, dan Jumlah Sesi
    function recalculateDaySummary(dayNum) {
        const container = $(`#slots_container_${dayNum}`);
        const cardSlots = container.find('.slot-item-card');
        const emptyNotice = $(`#empty_notice_${dayNum}`);
        const countBadge = $(`#badge_count_day_${dayNum}`);
        const switchEl = $(`#switch_day_active_${dayNum}`);

        const slotCount = cardSlots.length;
        countBadge.text(slotCount);

        if (slotCount === 0 || !switchEl.is(':checked')) {
            emptyNotice.show();
            $(`#summary_in_${dayNum}`).text('-');
            $(`#summary_out_${dayNum}`).text('-');
            $(`#summary_dur_${dayNum}`)
                .text('0 Jam')
                .removeClass('bg-success bg-opacity-15 text-success')
                .addClass('bg-secondary text-white')
                .css({'background-color': '#6b7280', 'color': '#ffffff', 'font-weight': '700'});
            return;
        }

        emptyNotice.hide();

        let earliestIn = null;
        let latestOut = null;
        let totalMinutes = 0;

        cardSlots.each(function() {
            const start = $(this).find('.input-slot-start').val();
            const end = $(this).find('.input-slot-end').val();

            if (start) {
                if (!earliestIn || start < earliestIn) earliestIn = start;
            }
            if (end) {
                if (!latestOut || end > latestOut) latestOut = end;
            }

            if (start && end) {
                const sParts = start.split(':');
                const eParts = end.split(':');
                const sMins = parseInt(sParts[0]) * 60 + parseInt(sParts[1]);
                const eMins = parseInt(eParts[0]) * 60 + parseInt(eParts[1]);
                if (eMins > sMins) {
                    totalMinutes += (eMins - sMins);
                }
            }
        });

        const inText = earliestIn ? earliestIn : '-';
        const outText = latestOut ? latestOut : '-';
        const hours = (totalMinutes / 60).toFixed(1);

        $(`#summary_in_${dayNum}`).text(inText);
        $(`#summary_out_${dayNum}`).text(outText);
        $(`#summary_dur_${dayNum}`)
            .text(`${slotCount} Sesi (${hours} Jam)`)
            .removeClass('bg-secondary bg-opacity-15 text-success')
            .addClass('bg-success text-white')
            .css({'background-color': '#059669', 'color': '#ffffff', 'font-weight': '700'});
    }

    // Fungsi Helper Mengambil data slot dari hari sumber
    function getDaySlotsData(sourceDay) {
        const container = $(`#slots_container_${sourceDay}`);
        const slots = [];
        container.find('.slot-item-card').each(function() {
            const unitId = $(this).find('.select-slot-unit').val();
            const start = $(this).find('.input-slot-start').val();
            const end = $(this).find('.input-slot-end').val();
            const subject = $(this).find('.input-slot-subject').val();
            if (start && end) {
                slots.push({
                    unit_id: unitId,
                    start_time: start,
                    end_time: end,
                    subject: subject
                });
            }
        });
        return slots;
    }

    // Fungsi Salin dari sourceDay ke array targetDays
    function copyScheduleSlots(sourceDay, targetDays) {
        const sourceSlots = getDaySlotsData(sourceDay);
        const dayNames = {1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu', 7: 'Minggu'};
        const sourceName = dayNames[sourceDay] || `Hari ${sourceDay}`;

        if (sourceSlots.length === 0) {
            Toast.fire({
                icon: 'warning',
                title: `Hari ${sourceName} belum memiliki sesi mengajar untuk disalin.`
            });
            return;
        }

        const copiedNames = [];

        targetDays.forEach(targetDay => {
            if (targetDay == sourceDay) return;

            copiedNames.push(dayNames[targetDay]);
            const container = $(`#slots_container_${targetDay}`);
            container.empty();

            // Aktifkan switch hari target
            const switchEl = $(`#switch_day_active_${targetDay}`);
            switchEl.prop('checked', true).trigger('change');

            // Tambahkan setiap slot ke hari target
            sourceSlots.forEach((slot, idx) => {
                const uniqueIdx = new Date().getTime() + Math.floor(Math.random() * 10000) + idx;
                const html = createSlotRowHtml(targetDay, uniqueIdx, slot);
                container.append(html);
            });

            recalculateDaySummary(targetDay);
        });

        if (copiedNames.length > 0) {
            Toast.fire({
                icon: 'success',
                title: `Jadwal hari ${sourceName} berhasil disalin ke: ${copiedNames.join(', ')}.`
            });
        }
    }

    $(document).ready(function() {
        const modalEmployeeSchedule = new bootstrap.Modal(document.getElementById('modalEmployeeSchedule'));
        const modalEditUnit = new bootstrap.Modal(document.getElementById('modalEditUnit'));
        const modalBulkAssign = new bootstrap.Modal(document.getElementById('modalBulkAssign'));

        // Event Salin ke Hari Tertentu (Single Day)
        $(document).on('click', '.btn-copy-to-single', function() {
            const fromDay = $(this).data('from-day');
            const toDay = $(this).data('to-day');
            copyScheduleSlots(fromDay, [toDay]);
        });

        // Event Salin ke Semua Hari Kerja (Senin - Jumat)
        $(document).on('click', '.btn-copy-to-weekdays', function() {
            const fromDay = $(this).data('from-day');
            copyScheduleSlots(fromDay, [1, 2, 3, 4, 5]);
        });

        // Event Salin ke Semua Hari (Senin - Minggu)
        $(document).on('click', '.btn-copy-to-all', function() {
            const fromDay = $(this).data('from-day');
            copyScheduleSlots(fromDay, [1, 2, 3, 4, 5, 6, 7]);
        });

        // Helper Salin Sesi Khusus (Per Jam) ke Hari Tertentu
        function copySpecificSlot(cardEl, targetDays) {
            const unitId = cardEl.find('.select-slot-unit').val();
            const start = cardEl.find('.input-slot-start').val();
            const end = cardEl.find('.input-slot-end').val();
            const subject = cardEl.find('.input-slot-subject').val();
            const sourceDay = cardEl.data('day');

            if (!start || !end) {
                Toast.fire({ icon: 'warning', title: 'Isi jam mulai dan jam selesai terlebih dahulu.' });
                return;
            }

            const dayNames = {1: 'Senin', 2: 'Selasa', 3: 'Rabu', 4: 'Kamis', 5: 'Jumat', 6: 'Sabtu', 7: 'Minggu'};
            const targetNames = [];

            targetDays.forEach(targetDay => {
                if (targetDay == sourceDay) return;

                targetNames.push(dayNames[targetDay]);
                const container = $(`#slots_container_${targetDay}`);

                // Aktifkan switch hari target
                const switchEl = $(`#switch_day_active_${targetDay}`);
                switchEl.prop('checked', true).trigger('change');

                const uniqueIdx = new Date().getTime() + Math.floor(Math.random() * 10000);
                const html = createSlotRowHtml(targetDay, uniqueIdx, {
                    unit_id: unitId,
                    start_time: start,
                    end_time: end,
                    subject: subject
                });
                container.append(html);
                recalculateDaySummary(targetDay);
            });

            if (targetNames.length > 0) {
                Toast.fire({
                    icon: 'success',
                    title: `Sesi [${start} - ${end}] berhasil disalin ke: ${targetNames.join(', ')}.`
                });
            }
        }

        // Salin Sesi Jam Ini ke Hari Kerja (Senin - Jumat)
        $(document).on('click', '.btn-copy-slot-to-weekdays', function() {
            const card = $(this).closest('.slot-item-card');
            copySpecificSlot(card, [1, 2, 3, 4, 5]);
        });

        // Salin Sesi Jam Ini ke Semua Hari (Senin - Minggu)
        $(document).on('click', '.btn-copy-slot-to-all', function() {
            const card = $(this).closest('.slot-item-card');
            copySpecificSlot(card, [1, 2, 3, 4, 5, 6, 7]);
        });

        // Salin Sesi Jam Ini ke Hari Tertentu
        $(document).on('click', '.btn-copy-slot-to-target', function() {
            const card = $(this).closest('.slot-item-card');
            const toDay = $(this).data('to-day');
            copySpecificSlot(card, [toDay]);
        });

        // Duplikat Sesi Jam Ini ke Jam Berikutnya di Hari yang Sama
        $(document).on('click', '.btn-duplicate-slot', function() {
            const card = $(this).closest('.slot-item-card');
            const dayNum = card.data('day');
            const unitId = card.find('.select-slot-unit').val();
            const prevEnd = card.find('.input-slot-end').val() || '08:30';
            const subject = card.find('.input-slot-subject').val();

            // Hitung jam mulai baru = prevEnd, jam selesai baru = prevEnd + 1 jam
            const newStart = prevEnd;
            const parts = prevEnd.split(':');
            let nextH = parseInt(parts[0]) + 1;
            if (nextH > 23) nextH = 23;
            const newEnd = `${String(nextH).padStart(2, '0')}:${parts[1] || '00'}`;

            const uniqueIdx = new Date().getTime() + Math.floor(Math.random() * 10000);
            const html = createSlotRowHtml(dayNum, uniqueIdx, {
                unit_id: unitId,
                start_time: newStart,
                end_time: newEnd,
                subject: subject
            });

            card.after(html);
            recalculateDaySummary(dayNum);
            Toast.fire({ icon: 'info', title: `Sesi baru [${newStart} - ${newEnd}] ditambahkan.` });
        });

        // Toggle Hari Aktif / Libur
        $('.switch-day-toggle').on('change', function() {
            const dayNum = $(this).data('day');
            const isActive = $(this).is(':checked');
            $(`#input_day_off_${dayNum}`).val(isActive ? '0' : '1');

            if (isActive) {
                $(`#slots_container_${dayNum}`).removeClass('opacity-50 pointer-events-none');
                $(`.btn-add-slot[data-day="${dayNum}"]`).prop('disabled', false);
            } else {
                $(`#slots_container_${dayNum}`).addClass('opacity-50 pointer-events-none');
                $(`.btn-add-slot[data-day="${dayNum}"]`).prop('disabled', true);
            }
            recalculateDaySummary(dayNum);
        });

        // Tambah Sesi Mengajar Baru via Tombol + Tambah Sesi
        $(document).on('click', '.btn-add-slot', function() {
            const dayNum = $(this).data('day');
            const container = $(`#slots_container_${dayNum}`);
            const currentCount = container.find('.slot-item-card').length;
            const newSlotIdx = new Date().getTime(); // unique index

            // Tentukan default jam sesi berikutnya jika sudah ada sesi sebelumnya
            let defaultStart = '07:30';
            let defaultEnd = '08:30';
            const lastSlot = container.find('.slot-item-card').last();
            if (lastSlot.length > 0) {
                const prevEnd = lastSlot.find('.input-slot-end').val();
                if (prevEnd) {
                    defaultStart = prevEnd;
                    const parts = prevEnd.split(':');
                    let nextH = parseInt(parts[0]) + 1;
                    if (nextH > 23) nextH = 23;
                    defaultEnd = `${String(nextH).padStart(2, '0')}:${parts[1]}`;
                }
            }

            const html = createSlotRowHtml(dayNum, newSlotIdx, {
                start_time: defaultStart,
                end_time: defaultEnd,
                subject: ''
            });

            container.append(html);
            recalculateDaySummary(dayNum);
        });

        // Hapus Sesi Mengajar
        $(document).on('click', '.btn-delete-slot', function() {
            const card = $(this).closest('.slot-item-card');
            const dayNum = card.data('day');
            card.fadeOut(200, function() {
                $(this).remove();
                recalculateDaySummary(dayNum);
            });
        });

        // Listener perubahan jam input sesi
        $(document).on('change input', '.input-slot-start, .input-slot-end', function() {
            const dayNum = $(this).closest('.slot-item-card').data('day');
            recalculateDaySummary(dayNum);
        });

        // Tombol Preset Cepat (1-Click Generator: TK, SD, SMP, SMA)
        $('.btn-apply-preset').on('click', function() {
            const unitId = $(this).data('unit-id');
            const unit = MASTER_UNITS.find(u => u.id == unitId);
            if (!unit) return;

            // Centang unit pada checkbox Section 1 jika belum tercentang
            $('#unit_check_' + unit.id).prop('checked', true);

            // Terapkan ke 7 hari
            for (let day = 1; day <= 7; day++) {
                const container = $(`#slots_container_${day}`);
                container.empty();

                const unitSch = unit.schedules ? unit.schedules.find(s => s.day_of_week == day) : null;
                const isOff = unitSch ? unitSch.is_day_off : (day === 7);

                const switchEl = $(`#switch_day_active_${day}`);
                switchEl.prop('checked', !isOff).trigger('change');

                if (!isOff) {
                    const startTime = unitSch && unitSch.time_in ? unitSch.time_in.substr(0, 5) : (unit.default_time_in ? unit.default_time_in.substr(0, 5) : '07:00');
                    const endTime = unitSch && unitSch.time_out ? unitSch.time_out.substr(0, 5) : (unit.default_time_out ? unit.default_time_out.substr(0, 5) : '14:00');

                    const html = createSlotRowHtml(day, 1, {
                        unit_id: unit.id,
                        start_time: startTime,
                        end_time: endTime,
                        subject: 'Kegiatan Belajar ' + unit.name
                    });
                    container.append(html);
                }

                recalculateDaySummary(day);
            }

            Toast.fire({ icon: 'info', title: `Preset ${unit.name} diterapkan ke seluruh hari.` });
        });

        // Buka Modal Edit Jadwal Pegawai via AJAX
        $('.btn-edit-schedule').on('click', function() {
            const userId = $(this).data('id');

            $.ajax({
                url: "{{ url('/work-schedules') }}/" + userId + "/edit",
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    Swal.showLoading();
                },
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        const user = response.user;
                        const days = response.days;

                        $('#emp_user_id').val(user.id);
                        $('#modal_emp_avatar').attr('src', user.avatar_url);
                        $('#modalEmployeeScheduleTitle').text('Atur Jam Kerja Guru: ' + user.name);
                        $('#modal_emp_info').text(`${user.position || 'Guru / Pegawai'} • ${user.department || 'Yayasan'} • NIP: ${user.nip}`);

                        // Reset checkbox unit
                        $('.check-unit-item').prop('checked', false);
                        if (user.unit_ids && user.unit_ids.length > 0) {
                            user.unit_ids.forEach(uId => {
                                $('#unit_check_' + uId).prop('checked', true);
                            });
                        }

                        // Render 7 hari
                        $.each(days, function(dayNum, dayData) {
                            const container = $(`#slots_container_${dayNum}`);
                            container.empty();

                            const isOff = dayData.is_day_off;
                            const switchEl = $(`#switch_day_active_${dayNum}`);
                            switchEl.prop('checked', !isOff).trigger('change');

                            if (dayData.slots && dayData.slots.length > 0) {
                                $.each(dayData.slots, function(slotIdx, slot) {
                                    const html = createSlotRowHtml(dayNum, slotIdx + 1, slot);
                                    container.append(html);
                                });
                            }

                            recalculateDaySummary(dayNum);
                        });

                        // Aktifkan tab hari Senin pertama
                        $('#daysTab button:first').tab('show');
                        modalEmployeeSchedule.show();
                    }
                },
                error: function() {
                    Swal.close();
                    Toast.fire({ icon: 'error', title: 'Gagal mengambil data jadwal pegawai.' });
                }
            });
        });

        // Submit Form Simpan Jadwal Pegawai via jQuery AJAX
        $('#formEmployeeSchedule').on('submit', function(e) {
            e.preventDefault();
            const userId = $('#emp_user_id').val();
            const btn = $('#btnSimpanSchedule');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ url('/work-schedules') }}/" + userId + "/update",
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Jam Kerja Guru');
                    if (response.status === 'success') {
                        modalEmployeeSchedule.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Jam Kerja Guru');
                    Toast.fire({
                        icon: 'error',
                        title: xhr.responseJSON?.message || 'Gagal menyimpan pengaturan jam kerja.'
                    });
                }
            });
        });

        // Buka Modal Edit Master Unit
        $('.btn-edit-unit').on('click', function() {
            const unit = $(this).data('unit');
            $('#edit_unit_id').val(unit.id);
            $('#unit_name').val(unit.name);
            $('#unit_color').val(unit.color);
            $('#unit_color_picker').val(unit.color);
            $('#unit_default_time_in').val(unit.default_time_in ? unit.default_time_in.substr(0, 5) : '07:00');
            $('#unit_default_time_out').val(unit.default_time_out ? unit.default_time_out.substr(0, 5) : '14:00');
            $('#unit_default_late_tolerance').val(unit.default_late_tolerance || 15);
            $('#unit_description').val(unit.description || '');

            modalEditUnit.show();
        });

        // Sinkronisasi Color Picker
        $('#unit_color_picker').on('input', function() {
            $('#unit_color').val($(this).val());
        });
        $('#unit_color').on('input', function() {
            $('#unit_color_picker').val($(this).val());
        });

        // Submit Form Edit Master Unit via AJAX
        $('#formEditUnit').on('submit', function(e) {
            e.preventDefault();
            const unitId = $('#edit_unit_id').val();
            const btn = $('#btnSimpanUnit');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: "{{ url('/work-schedules/units') }}/" + unitId + "/update",
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Standar Unit');
                    if (response.status === 'success') {
                        modalEditUnit.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan Standar Unit');
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal memperbarui unit.' });
                }
            });
        });

        // Buka Modal Bulk Assign
        $('#btnBulkAssignModal').on('click', function() {
            $('#formBulkAssign')[0].reset();
            $('.check-bulk-emp').prop('checked', false);
            modalBulkAssign.show();
        });

        $('#btnSelectAllEmployees').on('click', function() {
            $('.check-bulk-emp').prop('checked', true);
        });

        $('#btnUnselectAllEmployees').on('click', function() {
            $('.check-bulk-emp').prop('checked', false);
        });

        // Submit Form Bulk Assign
        $('#formBulkAssign').on('submit', function(e) {
            e.preventDefault();
            const btn = $('#btnSimpanBulk');
            btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menerapkan...');

            $.ajax({
                url: "{{ route('work-schedules.bulk-assign') }}",
                type: 'POST',
                data: $(this).serialize(),
                dataType: 'json',
                success: function(response) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Terapkan Sekarang');
                    if (response.status === 'success') {
                        modalBulkAssign.hide();
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    }
                },
                error: function(xhr) {
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle me-1"></i> Terapkan Sekarang');
                    Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal menerapkan preset massal.' });
                }
            });
        });
    });
</script>
@endsection
