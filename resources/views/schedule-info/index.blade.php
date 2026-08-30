@extends('layouts.app')

@section('title', 'Informasi Jadwal Mengajar')
@section('page-title', 'Informasi Jadwal Mengajar Guru')
@section('page-subtitle', 'Papan pemantauan jadwal harian & mingguan guru lintas jenjang yayasan (TK, SD, SMP, SMA)')

@section('content')
<div class="row g-4 mb-4">
    <!-- Ringkasan KPI Statistik -->
    <div class="col-12">
        <div class="row g-3">
            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-white-50 small fw-medium text-uppercase">Guru Mengajar {{ $stats['today_day_name'] }}</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['today_teachers_count'] }} <span class="fs-6 fw-normal text-white-50">Guru</span></h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">Aktif di jadwal {{ $stats['today_day_name'] }}</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-person-workspace fs-4"></i>
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
                                <span class="text-white-50 small fw-medium text-uppercase">Total Sesi Pelajaran</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['today_slots_count'] }} <span class="fs-6 fw-normal text-white-50">Sesi</span></h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">Di semua jenjang sekolah</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-clock-history fs-4"></i>
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
                                <span class="text-white-50 small fw-medium text-uppercase">Total Durasi Mengajar</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['today_hours'] }} <span class="fs-6 fw-normal text-white-50">Jam</span></h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">Akumulasi waktu belajar</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-hourglass-split fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-sm-6 col-xl-3">
                <div class="card shadow-sm border-0 h-100 text-white position-relative overflow-hidden" style="background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);">
                    <div class="card-body p-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <span class="text-white-50 small fw-medium text-uppercase">Total Guru Yayasan</span>
                                <h3 class="fw-bold mb-0 text-white mt-1">{{ $stats['total_active_teachers'] }} <span class="fs-6 fw-normal text-white-50">Guru</span></h3>
                                <small class="text-white-50" style="font-size: 0.75rem;">{{ $stats['total_configured'] }} sudah diatur jamnya</small>
                            </div>
                            <div class="rounded-circle bg-opacity-20 p-3 text-white">
                                <i class="bi bi-mortarboard-fill fs-4"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Pilihan Hari & Toolbar Filter -->
    <div class="col-12">
        <div class="card shadow-sm border-0 p-3">
            <form action="{{ route('schedule-info.index') }}" method="GET" class="row g-3 align-items-center">
                <!-- Navigasi Pill Hari (Senin - Minggu) -->
                <div class="col-12">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="small fw-bold text-muted text-uppercase me-1"><i class="bi bi-calendar-event me-1"></i>Pilih Hari:</span>
                            @php
                                $dayNamesList = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
                                $currentTodayNum = (int)\Carbon\Carbon::now()->dayOfWeekIso;
                            @endphp
                            @foreach($dayNamesList as $dNum => $dName)
                                <a href="{{ route('schedule-info.index', array_merge(request()->query(), ['day' => $dNum])) }}"
                                   class="btn btn-sm {{ $selectedDay == $dNum ? 'btn-primary text-white shadow-sm fw-bold' : 'btn-light border text-dark' }} px-3 py-1.5 rounded-pill position-relative">
                                    {{ $dName }}
                                    @if($dNum == $currentTodayNum)
                                        <span class="badge bg-danger rounded-pill ms-1" style="font-size: 0.6rem;">Hari Ini</span>
                                    @endif
                                </a>
                            @endforeach
                        </div>

                        <!-- Export Buttons (PDF & Excel) -->
                        @canExport('schedule-info')
                        <div class="d-flex align-items-center gap-2">
                            <a href="{{ route('schedule-info.export-pdf', request()->query()) }}" class="btn btn-outline-danger btn-sm shadow-sm" target="_blank" title="Unduh jadwal seluruh guru dalam format PDF siap cetak">
                                <i class="bi bi-file-earmark-pdf-fill me-1"></i> Export PDF Jadwal
                            </a>
                            <a href="{{ route('schedule-info.export', request()->query()) }}" class="btn btn-outline-success btn-sm shadow-sm" title="Unduh spreadsheet Excel">
                                <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
                            </a>
                        </div>
                        @endcanExport
                    </div>
                </div>

                <!-- Input Filter Unit & Pencarian -->
                <input type="hidden" name="day" value="{{ $selectedDay }}">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Cari nama guru, NIP, mapel...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="unit_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">-- Semua Unit Sekolah (TK, SD, SMP, SMA) --</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ request('unit_id') == $u->id ? 'selected' : '' }}>
                                {{ $u->name }} ({{ $u->code }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 d-flex gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-sm px-3">
                        <i class="bi bi-funnel me-1"></i> Terapkan Filter
                    </button>
                    @if(request()->filled('search') || request()->filled('unit_id'))
                        <a href="{{ route('schedule-info.index', ['day' => $selectedDay]) }}" class="btn btn-outline-secondary btn-sm">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Navigasi Tab Tampilan -->
    <div class="col-12">
        <ul class="nav nav-pills custom-nav-pills p-1 bg-white rounded-3 shadow-sm border mb-3" id="scheduleInfoTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="board-tab" data-bs-toggle="pill" data-bs-target="#board-pane" type="button" role="tab">
                    <i class="bi bi-kanban-fill text-primary"></i>
                    <span>Papan Sesi Mengajar Hari {{ $stats['today_day_name'] }}</span>
                    <span class="badge bg-primary rounded-pill px-2" style="font-size: 0.7rem;">{{ $stats['today_slots_count'] }} Sesi</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="matrix-tab" data-bs-toggle="pill" data-bs-target="#matrix-pane" type="button" role="tab">
                    <i class="bi bi-calendar-week-fill text-success"></i>
                    <span>Matriks Jadwal Mingguan (Senin - Minggu)</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-semibold py-2 px-3 d-flex align-items-center gap-2" id="workload-tab" data-bs-toggle="pill" data-bs-target="#workload-pane" type="button" role="tab">
                    <i class="bi bi-bar-chart-fill text-warning"></i>
                    <span>Analisis Beban Mengajar Guru</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="scheduleInfoTabContent">
            <!-- TAB 1: Papan Sesi Mengajar Hari Ini (Live Board per Jenjang) -->
            <div class="tab-pane fade show active" id="board-pane" role="tabpanel">
                <div class="row g-4">
                    @foreach($units as $unit)
                        @php
                            $unitSlots = $slotsByUnit[$unit->code]['slots'] ?? collect();
                        @endphp
                        <div class="col-lg-6 col-xl-4">
                            <div class="card shadow-sm border-0 h-100" style="border-top: 4px solid {{ $unit->color }} !important;">
                                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge rounded-circle p-2 d-inline-flex align-items-center justify-content-center text-white" style="background-color: {{ $unit->color }}; width: 32px; height: 32px;">
                                            <i class="bi bi-building"></i>
                                        </span>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark">{{ $unit->name }}</h6>
                                            <small class="text-muted" style="font-size: 0.72rem;">Standar: {{ $unit->formatted_time_in }} - {{ $unit->formatted_time_out }}</small>
                                        </div>
                                    </div>
                                    <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $unit->color }}15; color: {{ $unit->color }}; font-weight: 700;">
                                        {{ $unitSlots->count() }} Sesi
                                    </span>
                                </div>

                                <div class="card-body p-3" style="max-height: 480px; overflow-y: auto;">
                                    @forelse($unitSlots as $slot)
                                        <div class="p-3 mb-2.5 rounded-3 border bg-light shadow-2xs position-relative hover-shadow-sm transition-all" style="border-left: 4px solid {{ $unit->color }} !important;">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <span class="badge text-white px-2 py-1 fw-bold shadow-2xs" style="background-color: {{ $unit->color }}; font-size: 0.75rem;">
                                                    <i class="bi bi-clock-fill me-1"></i>{{ $slot->formatted_start_time }} - {{ $slot->formatted_end_time }}
                                                </span>
                                                <span class="badge bg-light text-muted border small" style="font-size: 0.68rem;">
                                                    {{ $slot->duration_minutes }} Menit
                                                </span>
                                            </div>

                                            <h6 class="fw-bold text-dark mb-1" style="font-size: 0.88rem;">
                                                {{ $slot->subject ?: 'Kegiatan Pembelajaran' }}
                                            </h6>

                                            <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $slot->user->avatar_url }}" alt="{{ $slot->user->name }}" class="rounded-circle border" width="28" height="28">
                                                    <div>
                                                        <div class="fw-semibold text-dark text-truncate" style="font-size: 0.78rem; max-width: 140px;">
                                                            {{ $slot->user->name }}
                                                        </div>
                                                        @if($slot->user->nip)
                                                            <div class="text-muted" style="font-size: 0.65rem;">NIP: {{ $slot->user->nip }}</div>
                                                        @endif
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-1">
                                                    @if($slot->notes)
                                                        <span class="badge bg-white text-muted border px-1.5 py-0.5" style="font-size: 0.65rem;" title="{{ $slot->notes }}">
                                                            <i class="bi bi-geo-alt"></i>
                                                        </span>
                                                    @endif
                                                    @canExport('schedule-info')
                                                    <a href="{{ route('schedule-info.export-personal-pdf', $slot->user_id) }}" class="btn btn-outline-danger btn-xs p-1 rounded-2" title="Cetak Jadwal Mengajar Guru Ini (PDF)" target="_blank">
                                                        <i class="bi bi-file-earmark-pdf-fill" style="font-size: 0.75rem;"></i>
                                                    </a>
                                                    @endcanExport
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center py-4 text-muted">
                                            <i class="bi bi-calendar-x fs-2 d-block mb-1 text-secondary opacity-40"></i>
                                            <span class="small">Tidak ada sesi mengajar di {{ $unit->name }} pada hari {{ $stats['today_day_name'] }}.</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <!-- TAB 2: Matriks Jadwal Mingguan Seluruh Guru -->
            <div class="tab-pane fade" id="matrix-pane" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Matriks Jadwal Mengajar Mingguan (Senin - Minggu)</h6>
                            <small class="text-muted">Daftar jadwal seluruh guru di yayasan lintas jenjang sekolah</small>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">#</th>
                                        <th style="min-width: 200px;">Guru / Pegawai</th>
                                        <th style="min-width: 140px;">Jenjang Diampu</th>
                                        <th style="min-width: 120px;" class="text-center">Senin</th>
                                        <th style="min-width: 120px;" class="text-center">Selasa</th>
                                        <th style="min-width: 120px;" class="text-center">Rabu</th>
                                        <th style="min-width: 120px;" class="text-center">Kamis</th>
                                        <th style="min-width: 120px;" class="text-center">Jumat</th>
                                        <th style="min-width: 95px;" class="text-center">Sabtu</th>
                                        <th style="min-width: 95px;" class="text-center">Minggu</th>
                                        <th style="width: 100px;" class="text-center">Aksi / Cetak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($teachers as $index => $t)
                                        @php $isConfigured = $t->hasConfiguredSchedule(); @endphp
                                        <tr class="{{ !$isConfigured ? 'table-light opacity-75' : '' }}">
                                            <td class="text-center fw-semibold text-muted">{{ $teachers->firstItem() + $index }}</td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <img src="{{ $t->avatar_url }}" alt="{{ $t->name }}" class="rounded-circle me-2.5 border" width="38" height="38">
                                                    <div>
                                                        <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $t->name }}</div>
                                                        <small class="text-muted d-block" style="font-size: 0.72rem;">
                                                            {{ $t->position ?: 'Guru' }} @if($t->nip) • NIP: {{ $t->nip }} @endif
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @forelse($t->units as $u)
                                                        <span class="badge rounded-pill px-2 py-0.5" style="font-size: 0.7rem; background-color: {{ $u->color }}20; color: {{ $u->color }}; border: 1px solid {{ $u->color }}50;">
                                                            {{ $u->code }}
                                                        </span>
                                                    @empty
                                                        <span class="badge bg-light text-muted border" style="font-size: 0.68rem;">-</span>
                                                    @endforelse
                                                    @if($t->units->count() > 1)
                                                        <span class="badge bg-success text-white px-2 py-0.5 fw-bold shadow-sm" style="font-size: 0.68rem; background-color: #059669 !important;">Multi</span>
                                                    @endif
                                                </div>
                                            </td>

                                            <!-- 7 Hari Jadwal -->
                                            @for($d = 1; $d <= 7; $d++)
                                                @php $daySch = $t->getWorkScheduleForDay($d); @endphp
                                                <td class="text-center p-1.5">
                                                    @if(!$isConfigured)
                                                        <span class="badge bg-light text-muted border border-dashed px-1.5 py-1" style="font-size: 0.65rem;">-</span>
                                                    @elseif($daySch->is_day_off || (empty($daySch->time_in) && (!$daySch->slots || $daySch->slots->isEmpty())))
                                                        <span class="badge bg-light text-muted border px-1.5 py-1" style="font-size: 0.68rem; font-weight: 500;">Libur</span>
                                                    @elseif($daySch->slots && $daySch->slots->isNotEmpty())
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
                                                                        <div class="text-muted text-truncate mt-0.5" style="font-size: 0.6rem; max-width: 110px;" title="{{ $s->subject }}">
                                                                            {{ $s->subject }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @elseif($daySch->time_in && $daySch->time_out)
                                                        <div class="p-1 rounded" style="background-color: {{ $daySch->unit ? $daySch->unit->color.'10' : '#f3f4f6' }}; border: 1px dashed {{ $daySch->unit ? $daySch->unit->color.'40' : '#d1d5db' }};">
                                                            <div class="fw-bold text-dark" style="font-size: 0.74rem;">
                                                                {{ substr($daySch->time_in, 0, 5) }} - {{ substr($daySch->time_out, 0, 5) }}
                                                            </div>
                                                        </div>
                                                    @else
                                                        <span class="badge bg-light text-muted border px-1.5 py-1" style="font-size: 0.68rem;">Libur</span>
                                                    @endif
                                                </td>
                                            @endfor

                                            <td class="text-center p-2">
                                                @canExport('schedule-info')
                                                <a href="{{ route('schedule-info.export-personal-pdf', $t->id) }}" class="btn btn-outline-danger btn-xs px-2 py-1 shadow-2xs d-inline-flex align-items-center gap-1" target="_blank" title="Cetak / Unduh Jadwal Mengajar Pribadi Guru Ini (PDF)">
                                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                                    <span style="font-size: 0.72rem; font-weight: 600;">Cetak PDF</span>
                                                </a>
                                                @else
                                                <span class="text-muted small">-</span>
                                                @endcanExport
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="11" class="text-center py-5 text-muted">
                                                <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                                Tidak ditemukan data jadwal guru yang sesuai.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    @if($teachers->hasPages())
                        <div class="card-footer bg-light d-flex justify-content-between align-items-center py-2 px-3">
                            <span class="text-muted small">Menampilkan {{ $teachers->firstItem() }} - {{ $teachers->lastItem() }} dari {{ $teachers->total() }} guru</span>
                            {{ $teachers->links() }}
                        </div>
                    @endif
                </div>
            </div>

            <!-- TAB 3: Analisis Beban Mengajar Guru -->
            <div class="tab-pane fade" id="workload-pane" role="tabpanel">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold text-dark mb-0">Rekapitulasi Beban Jam Mengajar Mingguan Guru</h6>
                            <small class="text-muted">Total sesi, akumulasi jam mengajar per minggu, dan jenjang yang diampu</small>
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45px;" class="text-center">#</th>
                                        <th>Nama Guru</th>
                                        <th>NIP & Jabatan</th>
                                        <th>Jenjang yang Diampu</th>
                                        <th class="text-center">Total Sesi / Minggu</th>
                                        <th class="text-center">Total Jam Mengajar</th>
                                        <th style="min-width: 160px;">Beban Waktu Mingguan</th>
                                        <th style="width: 100px;" class="text-center">Aksi / Cetak</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($workloadAnalysis as $idx => $w)
                                        <tr>
                                            <td class="text-center fw-semibold text-muted">{{ $idx + 1 }}</td>
                                            <td>
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ $w['avatar_url'] }}" alt="{{ $w['name'] }}" class="rounded-circle border" width="34" height="34">
                                                    <div class="fw-bold text-dark" style="font-size: 0.88rem;">{{ $w['name'] }}</div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="text-dark" style="font-size: 0.82rem;">{{ $w['position'] }}</div>
                                                <small class="text-muted">NIP: {{ $w['nip'] }}</small>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-wrap gap-1">
                                                    @forelse($w['units_taught'] as $uCode)
                                                        <span class="badge bg-indigo text-white px-2 py-0.5" style="font-size: 0.7rem;">{{ $uCode }}</span>
                                                    @empty
                                                        <span class="text-muted small">-</span>
                                                    @endforelse
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-light text-dark border px-2.5 py-1 fw-bold fs-7">{{ $w['total_slots'] }} Sesi</span>
                                            </td>
                                            <td class="text-center">
                                                <strong class="text-primary fs-6">{{ $w['total_hours'] }} Jam</strong>
                                                <small class="text-muted d-block" style="font-size: 0.68rem;">({{ $w['total_minutes'] }} Menit)</small>
                                            </td>
                                            <td>
                                                @php
                                                    $percent = min(100, ($w['total_hours'] / 24) * 100);
                                                    $barColor = $w['total_hours'] >= 18 ? 'bg-success' : ($w['total_hours'] >= 10 ? 'bg-primary' : 'bg-warning');
                                                @endphp
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="progress flex-grow-1" style="height: 8px;">
                                                        <div class="progress-bar {{ $barColor }}" role="progressbar" style="width: {{ $percent }}%;" aria-valuenow="{{ $percent }}" aria-valuemin="0" aria-valuemax="100"></div>
                                                    </div>
                                                    <span class="small fw-semibold text-muted" style="font-size: 0.72rem; min-width: 35px;">{{ round($percent) }}%</span>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                @canExport('schedule-info')
                                                <a href="{{ route('schedule-info.export-personal-pdf', $w['id']) }}" class="btn btn-outline-danger btn-xs px-2 py-1 shadow-2xs d-inline-flex align-items-center gap-1" target="_blank" title="Cetak Jadwal Mengajar {{ $w['name'] }} (PDF)">
                                                    <i class="bi bi-file-earmark-pdf-fill"></i>
                                                    <span style="font-size: 0.72rem; font-weight: 600;">Cetak PDF</span>
                                                </a>
                                                @else
                                                <span class="text-muted small">-</span>
                                                @endcanExport
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
