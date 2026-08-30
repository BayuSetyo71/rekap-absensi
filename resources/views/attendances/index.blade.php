@php
    $isRegularEmployee = !auth()->user()->isSuperAdmin() && !can_do('users', 'view');
@endphp

@section('title', $isRegularEmployee ? 'Riwayat Presensi Saya' : 'Data Absensi & Inject Excel')
@section('page-title', $isRegularEmployee ? 'Riwayat Presensi Saya' : 'Data Absensi')
@section('page-subtitle', $isRegularEmployee ? 'Catatan kehadiran harian, jam masuk & pulang, dan status presensi Anda' : 'Kelola data presensi harian, inject Excel & unduh laporan')

@section('styles')
<style>
    /* ── Responsive Tabel Absensi ── */
    @media (max-width: 767.98px) {
        .att-hide-sm { display: none !important; }

        .btn-group-att .btn {
            min-height: 36px;
            min-width: 36px;
            padding: 0.3rem 0.5rem;
        }

        /* Pagination ringkas di mobile */
        .pagination {
            flex-wrap: wrap;
            gap: 2px;
            justify-content: center;
        }

        .page-link {
            padding: 0.35rem 0.55rem;
            font-size: 0.82rem;
        }

        /* Filter form spacing */
        .filter-form-wrapper {
            gap: 0.5rem !important;
        }

        /* Action buttons di bawah filter */
        .filter-action-wrap {
            flex-direction: column;
            gap: 0.5rem;
        }

        .filter-action-wrap .btn-group-action {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0.4rem;
        }

        .filter-action-wrap .btn-group-action .btn {
            justify-content: center;
        }
    }

    /* Aksi tombol di dalam tabel */
    .btn-att-action {
        width: 34px;
        height: 34px;
        padding: 0;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
    }

    /* Stats card hover */
    .stat-mini-card {
        border-radius: 12px;
        transition: transform 0.18s ease;
    }

    .stat-mini-card:hover {
        transform: translateY(-2px);
    }
</style>
@endsection

@section('content')
{{-- ── Stats Summary Row ── --}}
<div class="row g-2 mb-4">
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center h-100 stat-mini-card">
            <div class="card-body p-2 py-3">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 0.68rem;">Total</small>
                <div class="fw-bold text-dark" style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">{{ $stats['total'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center h-100 stat-mini-card border-start border-success border-4" style="background: rgba(16,185,129,0.05);">
            <div class="card-body p-2 py-3">
                <small class="text-success text-uppercase fw-semibold d-block" style="font-size: 0.68rem;">Hadir Tepat</small>
                <div class="fw-bold text-success" style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">{{ $stats['hadir'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center h-100 stat-mini-card border-start border-warning border-4" style="background: rgba(245,158,11,0.05);">
            <div class="card-body p-2 py-3">
                <small class="text-warning text-uppercase fw-semibold d-block" style="font-size: 0.68rem;">Terlambat</small>
                <div class="fw-bold text-warning" style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">{{ $stats['terlambat'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center h-100 stat-mini-card border-start border-info border-4" style="background: rgba(6,182,212,0.05);">
            <div class="card-body p-2 py-3">
                <small class="text-info text-uppercase fw-semibold d-block" style="font-size: 0.68rem;">Izin</small>
                <div class="fw-bold text-info" style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">{{ $stats['izin'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center h-100 stat-mini-card border-start border-primary border-4" style="background: rgba(79,70,229,0.05);">
            <div class="card-body p-2 py-3">
                <small class="text-primary text-uppercase fw-semibold d-block" style="font-size: 0.68rem;">Sakit</small>
                <div class="fw-bold text-primary" style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">{{ $stats['sakit'] }}</div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-4 col-xl-2">
        <div class="card border-0 shadow-sm text-center h-100 stat-mini-card border-start border-danger border-4" style="background: rgba(239,68,68,0.05);">
            <div class="card-body p-2 py-3">
                <small class="text-danger text-uppercase fw-semibold d-block" style="font-size: 0.68rem;">Alpa</small>
                <div class="fw-bold text-danger" style="font-size: 1.5rem; font-family: 'Outfit', sans-serif;">{{ $stats['alpa'] }}</div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-12">
        <div class="card shadow-sm" style="border: none;">
            {{-- ── Filter & Action Toolbar ── --}}
            <div class="card-header bg-transparent py-3">
                <form action="{{ route('attendances.index') }}" method="GET">
                    <div class="row g-2 filter-form-wrapper">
                        {{-- Filter Tanggal Mulai --}}
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1" style="font-size: 0.78rem;">Dari Tanggal</label>
                            <input type="date" class="form-control form-control-sm" name="start_date"
                                   value="{{ request('start_date', $startDate) }}">
                        </div>

                        {{-- Filter Tanggal Selesai --}}
                        <div class="col-6 col-md-3">
                            <label class="form-label small text-muted mb-1" style="font-size: 0.78rem;">Sampai Tanggal</label>
                            <input type="date" class="form-control form-control-sm" name="end_date"
                                   value="{{ request('end_date', $endDate) }}">
                        </div>

                        {{-- Filter Pegawai (admin/superadmin) --}}
                        @if(auth()->user()->isSuperAdmin() || auth()->user()->canAccessMenu('users', 'view'))
                        <div class="col-12 col-md-3">
                            <label class="form-label small text-muted mb-1" style="font-size: 0.78rem;">Pilih Pegawai</label>
                            <select name="user_id" class="form-select form-select-sm">
                                <option value="">-- Semua Pegawai --</option>
                                @foreach($employees as $emp)
                                    <option value="{{ $emp->id }}" {{ request('user_id') == $emp->id ? 'selected' : '' }}>
                                        {{ $emp->name }} ({{ $emp->nip ?? $emp->email }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @endif

                        {{-- Filter Status --}}
                        <div class="col-12 col-md-3">
                            <label class="form-label small text-muted mb-1" style="font-size: 0.78rem;">Status Kehadiran</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">-- Semua Status --</option>
                                <option value="hadir" {{ request('status') === 'hadir' ? 'selected' : '' }}>Hadir</option>
                                <option value="terlambat" {{ request('status') === 'terlambat' ? 'selected' : '' }}>Terlambat</option>
                                <option value="izin" {{ request('status') === 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="sakit" {{ request('status') === 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="alpa" {{ request('status') === 'alpa' ? 'selected' : '' }}>Alpa</option>
                            </select>
                        </div>
                    </div>

                    {{-- Tombol Aksi Filter --}}
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 pt-3 mt-2 border-top filter-action-wrap">
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-funnel me-1"></i> Filter
                            </button>
                            <a href="{{ route('attendances.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-arrow-counterclockwise"></i>
                                <span class="d-none d-sm-inline ms-1">Reset</span>
                            </a>
                        </div>

                        <div class="btn-group-action d-flex gap-2 flex-wrap">
                            @canExport('attendances')
                            <a href="{{ route('attendances.export', request()->query()) }}" class="btn btn-outline-success btn-sm">
                                <i class="bi bi-file-earmark-excel-fill me-1"></i>
                                <span class="d-none d-sm-inline">Export </span>Excel
                            </a>
                            @endcanExport

                            @canCreate('attendances')
                            <button type="button" class="btn btn-success btn-sm fw-semibold" id="btnOpenUploadModal">
                                <i class="bi bi-file-earmark-arrow-up-fill me-1"></i>
                                <span class="d-none d-sm-inline">Inject </span>Excel
                            </button>
                            <button type="button" class="btn btn-primary btn-sm" id="btnTambahManual">
                                <i class="bi bi-plus-lg me-1"></i>
                                <span class="d-none d-sm-inline">Tambah </span>Manual
                            </button>
                            @endcanCreate
                        </div>
                    </div>
                </form>
            </div>

            {{-- ── Tabel Data Absensi ── --}}
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width: 40px;" class="text-center att-hide-sm">#</th>
                                <th class="att-hide-sm">Tanggal</th>
                                <th>Pegawai</th>
                                <th class="text-center">Masuk</th>
                                <th class="text-center att-hide-sm">Pulang</th>
                                <th class="text-center">Status</th>
                                <th class="att-hide-sm">Keterangan</th>
                                <th class="text-end" style="width: 100px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($attendances as $index => $att)
                                <tr>
                                    <td class="text-center fw-semibold text-muted att-hide-sm" style="font-size: 0.85rem;">
                                        {{ $attendances->firstItem() + $index }}
                                    </td>
                                    <td class="att-hide-sm">
                                        <div class="fw-semibold" style="font-size: 0.88rem;">{{ $att->attendance_date?->translatedFormat('d M Y') }}</div>
                                        <small class="text-muted">{{ $att->attendance_date?->translatedFormat('l') }}</small>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="{{ $att->user?->avatar_url }}"
                                                 alt="{{ $att->user?->name }}"
                                                 class="rounded-circle me-2 flex-shrink-0"
                                                 width="30" height="30"
                                                 style="object-fit: cover; border: 1.5px solid #e2e8f0;">
                                            <div class="overflow-hidden">
                                                <span class="fw-semibold text-dark d-block text-truncate" style="max-width: 120px; font-size: 0.88rem;">
                                                    {{ $att->user?->name ?? 'User Terhapus' }}
                                                </span>
                                                {{-- Tampilkan tanggal di dalam kolom pegawai saat mobile --}}
                                                <small class="text-muted d-block d-md-none" style="font-size: 0.73rem;">
                                                    {{ $att->attendance_date?->translatedFormat('d M Y') }}
                                                </small>
                                                <small class="text-muted d-none d-md-block att-hide-sm" style="font-size: 0.73rem;">
                                                    {{ $att->user?->nip ?? $att->user?->id ?? '-' }}
                                                </small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($att->check_in)
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.78rem;">{{ $att->formatted_check_in }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center att-hide-sm">
                                        @if($att->check_out)
                                            <span class="badge bg-light text-dark border font-monospace" style="font-size: 0.78rem;">{{ $att->formatted_check_out }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        {!! $att->status_badge !!}
                                    </td>
                                    <td class="att-hide-sm">
                                        <small class="text-muted">{{ $att->notes ?? '-' }}</small>
                                    </td>
                                    <td class="text-end">
                                        <div class="d-flex gap-1 justify-content-end btn-group-att">
                                            @canUpdate('attendances')
                                            <button type="button" class="btn btn-light border btn-edit-att btn-att-action"
                                                    data-id="{{ $att->id }}" title="Edit Absensi">
                                                <i class="bi bi-pencil-fill text-primary" style="font-size: 0.85rem;"></i>
                                            </button>
                                            @endcanUpdate

                                            @canDelete('attendances')
                                            <button type="button" class="btn btn-light border btn-delete-att btn-att-action"
                                                    data-id="{{ $att->id }}"
                                                    data-info="{{ $att->user?->name }} ({{ $att->formatted_date }})"
                                                    title="Hapus Absensi">
                                                <i class="bi bi-trash-fill text-danger" style="font-size: 0.85rem;"></i>
                                            </button>
                                            @endcanDelete
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                                        Belum ada data absensi untuk filter yang dipilih.
                                        @canCreate('attendances')
                                        <div class="mt-2 d-flex gap-2 justify-content-center flex-wrap">
                                            <button type="button" class="btn btn-sm btn-success" id="btnOpenUploadModal2">
                                                <i class="bi bi-upload me-1"></i> Inject Excel
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnTambahManual2">
                                                <i class="bi bi-plus-lg me-1"></i> Tambah Manual
                                            </button>
                                        </div>
                                        @endcanCreate
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Pagination --}}
            @if($attendances->hasPages())
                <div class="card-footer bg-transparent d-flex flex-wrap justify-content-between align-items-center gap-2 py-2 px-3">
                    <span class="text-muted small">
                        {{ $attendances->firstItem() }}–{{ $attendances->lastItem() }} dari {{ $attendances->total() }} catatan
                    </span>
                    <div>{{ $attendances->links() }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL STEP 1: PILIH FILE EXCEL / CSV UNTUK DIPRATINJAU -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalUploadExcel" tabindex="-1" aria-labelledby="modalUploadTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold" id="modalUploadTitle">
                    <i class="bi bi-file-earmark-excel-fill me-2"></i>Inject Data Absensi dari Excel / Mesin Fingerprint
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formUploadExcel" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <!-- Template Download Section -->
                    <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 border mb-4">
                        <div>
                            <h6 class="fw-bold mb-1 text-dark"><i class="bi bi-download me-1 text-primary"></i> Unduh Format Template</h6>
                            <small class="text-muted">Gunakan template bawaan atau langsung upload hasil export mesin fingerprint.</small>
                        </div>
                        <a href="{{ route('attendances.template') }}" class="btn btn-outline-primary btn-sm px-3">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Unduh
                        </a>
                    </div>

                    <!-- File Input -->
                    <div class="mb-3">
                        <label for="excel_file_input" class="form-label fw-semibold">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="excel_file_input" name="excel_file" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Mendukung format export mesin Fingerprint (misal: <code>Rekap_Fingerprint_PAUD.xlsx</code>) serta berkas <code>.xlsx</code> / <code>.csv</code> lainnya.</small>
                        <div class="invalid-feedback" id="error_excel_file_input"></div>
                    </div>

                    <div class="alert alert-info border small mb-0">
                        <i class="bi bi-info-circle-fill me-1"></i> <strong>Alur Kerja Cerdas:</strong> Sistem akan membaca dan menampilkan <strong>Pratinjau Data (Preview)</strong> terlebih dahulu agar Anda dapat memeriksa baris-baris data sebelum disimpan ke database.
                    </div>

                    <!-- Progress Indicator -->
                    <div class="progress mt-3 d-none" id="previewProgressBar" style="height: 10px;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 100%"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success" id="btnProsesPreview">
                        <i class="bi bi-eye-fill me-1"></i> Periksa & Pratinjau Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL STEP 2: PRATINJAU (PREVIEW) DATA SEBELUM COMMIT KE DATABASE -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalPreviewData" tabindex="-1" aria-labelledby="modalPreviewTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 95%;">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title fw-bold" id="modalPreviewTitle">
                    <i class="bi bi-table me-2 text-warning"></i>Pratinjau Data Absensi Sebelum Disimpan
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Summary Badges Card -->
                <div class="card border-0 shadow-sm mb-3">
                    <div class="card-body p-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="badge bg-primary px-3 py-2 fs-6">
                                    <i class="bi bi-list-check me-1"></i> Total Data: <strong id="sumTotalRows">0</strong>
                                </span>
                                <span class="badge bg-success px-3 py-2 fs-6">
                                    <i class="bi bi-plus-circle me-1"></i> Data Baru: <strong id="sumNewRows">0</strong>
                                </span>
                                <span class="badge bg-warning text-dark px-3 py-2 fs-6">
                                    <i class="bi bi-arrow-repeat me-1"></i> Data Ditimpa: <strong id="sumExistingRows">0</strong>
                                </span>
                                <span class="badge bg-info text-dark px-3 py-2 fs-6" id="badgeUnregisteredWrapper">
                                    <i class="bi bi-person-plus me-1"></i> Pegawai Baru: <strong id="sumUnregistered">0</strong>
                                </span>
                            </div>

                            <!-- Options Switch -->
                            <div class="d-flex align-items-center gap-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="optAutoCreateUsers" checked>
                                    <label class="form-check-label small fw-semibold" for="optAutoCreateUsers" title="Buat akun pegawai otomatis jika ID/Nama belum terdaftar">
                                        Auto-Buat Akun Pegawai Baru
                                    </label>
                                </div>
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" role="switch" id="optOverwriteExisting" checked>
                                    <label class="form-check-label small fw-semibold" for="optOverwriteExisting" title="Perbarui jam/status jika sudah ada di tanggal yang sama">
                                        Timpa Data Lama (Overwrite)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Preview Filter & Selection -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-outline-primary btn-sm" id="btnSelectAllRows">
                                <i class="bi bi-check-square me-1"></i> Pilih Semua (<span id="selectedRowCount">0</span>)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm" id="btnDeselectAllRows">
                                <i class="bi bi-dash-square me-1"></i> Batal Pilih
                            </button>
                        </div>
                        <div class="input-group input-group-sm" style="max-width: 300px;">
                            <span class="input-group-text bg-light"><i class="bi bi-search text-muted"></i></span>
                            <input type="text" class="form-control" id="searchPreviewTable" placeholder="Cari nama / ID di pratinjau...">
                        </div>
                    </div>
                    <div class="table-responsive" style="max-height: 440px;">
                        <table class="table table-hover table-sm align-middle mb-0" id="tablePreview">
                            <thead class="table-dark sticky-top">
                                <tr>
                                    <th style="width: 40px;" class="text-center">
                                        <input type="checkbox" class="form-check-input" id="checkAllPreviewMaster" checked>
                                    </th>
                                    <th style="width: 70px;">ID / PIN</th>
                                    <th>Nama di File Excel</th>
                                    <th>Pegawai Sistem</th>
                                    <th>Tanggal</th>
                                    <th class="text-center">Jam Masuk</th>
                                    <th class="text-center">Jam Pulang</th>
                                    <th style="width: 140px;">Status Kehadiran</th>
                                    <th>Keterangan</th>
                                    <th class="text-center" style="width: 110px;">Status Data</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <!-- Generated by jQuery -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary" id="btnBackToUpload">
                    <i class="bi bi-arrow-left me-1"></i> Ganti File Excel
                </button>
                <button type="button" class="btn btn-success btn-lg px-4 fw-bold shadow" id="btnCommitImport">
                    <i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Semua ke Database
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 3: TAMBAH / EDIT ABSENSI MANUAL -->
<!-- ========================================================================= -->
<div class="modal fade" id="modalAttendance" tabindex="-1" aria-labelledby="modalAttendanceTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold" id="modalAttendanceTitle">
                    <i class="bi bi-calendar-plus me-2 text-primary"></i>Tambah Data Absensi Manual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAttendance">
                <div class="modal-body p-4">
                    <input type="hidden" id="attendance_id" name="attendance_id">

                    <!-- Pilih Pegawai -->
                    <div class="mb-3" id="group_user_id">
                        <label for="modal_att_user_id" class="form-label fw-semibold">Pegawai <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_att_user_id" name="user_id" required>
                            <option value="">-- Pilih Pegawai --</option>
                            @foreach($employees as $emp)
                                <option value="{{ $emp->id }}">{{ $emp->name }} ({{ $emp->nip ?? $emp->email }})</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback" id="error_user_id"></div>
                    </div>

                    <!-- Tanggal Absensi -->
                    <div class="mb-3">
                        <label for="modal_att_date" class="form-label fw-semibold">Tanggal Absensi <span class="text-danger">*</span></label>
                        <input type="date" class="form-control" id="modal_att_date" name="attendance_date" value="{{ date('Y-m-d') }}" required>
                        <div class="invalid-feedback" id="error_attendance_date"></div>
                    </div>

                    <!-- Jam Masuk & Pulang -->
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="modal_att_check_in" class="form-label fw-semibold">Jam Masuk (HH:MM)</label>
                            <input type="time" class="form-control" id="modal_att_check_in" name="check_in" value="08:00">
                        </div>
                        <div class="col-6">
                            <label for="modal_att_check_out" class="form-label fw-semibold">Jam Pulang (HH:MM)</label>
                            <input type="time" class="form-control" id="modal_att_check_out" name="check_out" value="17:00">
                        </div>
                    </div>

                    <!-- Status Kehadiran -->
                    <div class="mb-3">
                        <label for="modal_att_status" class="form-label fw-semibold">Status Kehadiran <span class="text-danger">*</span></label>
                        <select class="form-select" id="modal_att_status" name="status" required>
                            <option value="hadir">Hadir (Tepat Waktu)</option>
                            <option value="terlambat">Terlambat</option>
                            <option value="izin">Izin</option>
                            <option value="sakit">Sakit</option>
                            <option value="alpa">Alpa (Tanpa Keterangan)</option>
                        </select>
                        <div class="invalid-feedback" id="error_status"></div>
                    </div>

                    <!-- Keterangan -->
                    <div class="mb-3">
                        <label for="modal_att_notes" class="form-label fw-semibold">Keterangan / Catatan</label>
                        <textarea class="form-control" id="modal_att_notes" name="notes" rows="2" placeholder="Catatan opsional..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary" id="btnSimpanAttendance">
                        <i class="bi bi-save me-1"></i> Simpan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        const modalUpload = new bootstrap.Modal(document.getElementById('modalUploadExcel'));
        const modalPreview = new bootstrap.Modal(document.getElementById('modalPreviewData'));
        const modalAttendance = new bootstrap.Modal(document.getElementById('modalAttendance'));

        // Variabel penampung data item hasil parsing
        let parsedPreviewItems = [];

        // 1. Buka Modal Upload File
        $('#btnOpenUploadModal').on('click', function() {
            $('#formUploadExcel')[0].reset();
            $('#previewProgressBar').addClass('d-none');
            $('.form-control').removeClass('is-invalid');
            modalUpload.show();
        });

        // 2. Submit Upload & Proses Pratinjau (Preview) via jQuery AJAX
        $('#formUploadExcel').on('submit', function(e) {
            e.preventDefault();
            $('.form-control').removeClass('is-invalid');

            const fileInput = $('#excel_file_input')[0].files[0];
            if (!fileInput) {
                $('#excel_file_input').addClass('is-invalid');
                $('#error_excel_file_input').text('Silakan pilih file Excel/CSV terlebih dahulu.');
                return;
            }

            const formData = new FormData(this);
            $('#btnProsesPreview').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Membaca File...');
            $('#previewProgressBar').removeClass('d-none');

            $.ajax({
                url: "{{ route('attendances.preview') }}",
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function(response) {
                    $('#btnProsesPreview').prop('disabled', false).html('<i class="bi bi-eye-fill me-1"></i> Periksa & Pratinjau Data');
                    $('#previewProgressBar').addClass('d-none');

                    if (response.status === 'success') {
                        parsedPreviewItems = response.items;
                        renderPreviewTable(parsedPreviewItems, response.summary);
                        modalUpload.hide();
                        modalPreview.show();
                    }
                },
                error: function(xhr) {
                    $('#btnProsesPreview').prop('disabled', false).html('<i class="bi bi-eye-fill me-1"></i> Periksa & Pratinjau Data');
                    $('#previewProgressBar').addClass('d-none');

                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#' + field + '_input').addClass('is-invalid');
                            $('#error_' + field + '_input').text(messages[0]);
                        });
                    } else {
                        Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan saat memproses file Excel.', 'error');
                    }
                }
            });
        });

        // Fungsi Render Baris Tabel Pratinjau
        function renderPreviewTable(items, summary) {
            $('#sumTotalRows').text(summary.total_rows);
            $('#sumNewRows').text(summary.new_rows);
            $('#sumExistingRows').text(summary.existing_rows);
            $('#sumUnregistered').text(summary.unregistered_count);

            if (summary.unregistered_count > 0) {
                $('#badgeUnregisteredWrapper').show();
            } else {
                $('#badgeUnregisteredWrapper').hide();
            }

            const tbody = $('#previewTableBody');
            tbody.empty();

            if (items.length === 0) {
                tbody.html('<tr><td colspan="10" class="text-center py-4 text-muted">Tidak ada baris data yang terbaca.</td></tr>');
                updateSelectedCount();
                return;
            }

            items.forEach((item, idx) => {
                const isExistingBadge = item.is_existing
                    ? '<span class="badge bg-warning text-dark"><i class="bi bi-arrow-repeat me-1"></i>Timpa Data</span>'
                    : '<span class="badge bg-success"><i class="bi bi-plus-circle me-1"></i>Data Baru</span>';

                const employeeBadge = item.is_registered
                    ? `<span class="fw-semibold text-dark">${item.matched_user_name}</span> <small class="text-muted d-block">NIP: ${item.matched_user_nip || '-'}</small>`
                    : `<span class="badge bg-info text-dark"><i class="bi bi-person-plus me-1"></i>Daftar Otomatis</span>`;

                const tr = `
                    <tr class="preview-row" data-index="${idx}">
                        <td class="text-center">
                            <input type="checkbox" class="form-check-input preview-item-cb" data-index="${idx}" checked>
                        </td>
                        <td><code>${item.raw_id || '-'}</code></td>
                        <td><strong class="text-dark">${item.raw_name || '-'}</strong></td>
                        <td>${employeeBadge}</td>
                        <td><code>${item.date}</code> <small class="text-muted d-block">${item.formatted_date}</small></td>
                        <td class="text-center font-monospace">${item.check_in || '-'}</td>
                        <td class="text-center font-monospace">${item.check_out || '-'}</td>
                        <td>
                            <select class="form-select form-select-sm preview-status-select" data-index="${idx}">
                                <option value="hadir" ${item.status === 'hadir' ? 'selected' : ''}>Hadir</option>
                                <option value="terlambat" ${item.status === 'terlambat' ? 'selected' : ''}>Terlambat</option>
                                <option value="izin" ${item.status === 'izin' ? 'selected' : ''}>Izin</option>
                                <option value="sakit" ${item.status === 'sakit' ? 'selected' : ''}>Sakit</option>
                                <option value="alpa" ${item.status === 'alpa' ? 'selected' : ''}>Alpa</option>
                            </select>
                        </td>
                        <td><small class="text-muted">${item.notes || '-'}</small></td>
                        <td class="text-center">${isExistingBadge}</td>
                    </tr>
                `;
                tbody.append(tr);
            });

            updateSelectedCount();
        }

        // Live Filter / Search di Modal Pratinjau
        $('#searchPreviewTable').on('keyup', function() {
            const val = $(this).val().toLowerCase();
            $('#previewTableBody tr').filter(function() {
                $(this).toggle($(this).text().toLowerCase().indexOf(val) > -1);
            });
        });

        // Toggle Select All Checkbox
        $('#checkAllPreviewMaster, #btnSelectAllRows').on('click', function() {
            $('.preview-item-cb').prop('checked', true);
            $('#checkAllPreviewMaster').prop('checked', true);
            updateSelectedCount();
        });

        $('#btnDeselectAllRows').on('click', function() {
            $('.preview-item-cb').prop('checked', false);
            $('#checkAllPreviewMaster').prop('checked', false);
            updateSelectedCount();
        });

        $(document).on('change', '.preview-item-cb', function() {
            updateSelectedCount();
        });

        // Update dropdown status di array data preview
        $(document).on('change', '.preview-status-select', function() {
            const idx = $(this).data('index');
            if (parsedPreviewItems[idx]) {
                parsedPreviewItems[idx].status = $(this).val();
            }
        });

        function updateSelectedCount() {
            const checkedCount = $('.preview-item-cb:checked').length;
            $('#selectedRowCount').text(checkedCount);
        }

        // Tombol Kembali ke Upload
        $('#btnBackToUpload').on('click', function() {
            modalPreview.hide();
            modalUpload.show();
        });

        // 3. STEP 2: COMMIT / SIMPAN DATA KE DATABASE
        $('#btnCommitImport').on('click', function() {
            const selectedIndexes = [];
            $('.preview-item-cb:checked').each(function() {
                selectedIndexes.push($(this).data('index'));
            });

            if (selectedIndexes.length === 0) {
                Swal.fire('Perhatian!', 'Pilih minimal satu baris data untuk disimpan.', 'warning');
                return;
            }

            const itemsToCommit = selectedIndexes.map(idx => parsedPreviewItems[idx]);
            const autoCreateUsers = $('#optAutoCreateUsers').is(':checked');
            const overwrite = $('#optOverwriteExisting').is(':checked');

            Swal.fire({
                title: 'Simpan ke Database?',
                text: `Anda akan memasukkan ${itemsToCommit.length} catatan absensi ke dalam database. Lanjutkan?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Simpan Semua!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#btnCommitImport').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan ke Database...');

                    $.ajax({
                        url: "{{ route('attendances.commit') }}",
                        type: 'POST',
                        data: JSON.stringify({
                            items: itemsToCommit,
                            auto_create_users: autoCreateUsers,
                            overwrite: overwrite
                        }),
                        contentType: 'application/json',
                        dataType: 'json',
                        success: function(response) {
                            $('#btnCommitImport').prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Semua ke Database');

                            if (response.status === 'success') {
                                modalPreview.hide();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Inject Data Berhasil!',
                                    text: response.message,
                                    confirmButtonText: 'OK / Muat Ulang'
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            $('#btnCommitImport').prop('disabled', false).html('<i class="bi bi-cloud-arrow-up-fill me-1"></i> Simpan Semua ke Database');
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Terjadi kesalahan saat menyimpan data.', 'error');
                        }
                    });
                }
            });
        });

        // 4. Tombol Tambah Manual
        $('#btnTambahManual, #btnTambahManual2').on('click', function() {
            $('#formAttendance')[0].reset();
            $('#attendance_id').val('');
            $('#group_user_id').show();
            $('#modal_att_date').prop('readonly', false);
            $('.form-control, .form-select').removeClass('is-invalid');
            $('#modalAttendanceTitle').html('<i class="bi bi-calendar-plus me-2 text-primary"></i>Tambah Data Absensi Manual');
            modalAttendance.show();
        });

        // Tombol Inject di Empty State
        $('#btnOpenUploadModal2').on('click', function() {
            $('#formUploadExcel')[0].reset();
            $('#previewProgressBar').addClass('d-none');
            $('.form-control').removeClass('is-invalid');
            modalUpload.show();
        });

        // 5. Tombol Edit Absensi
        $('.btn-edit-att').on('click', function() {
            const attId = $(this).data('id');
            $('.form-control, .form-select').removeClass('is-invalid');

            $.ajax({
                url: "{{ url('/attendances') }}/" + attId + "/edit",
                type: 'GET',
                dataType: 'json',
                beforeSend: function() {
                    Swal.showLoading();
                },
                success: function(response) {
                    Swal.close();
                    if (response.status === 'success') {
                        const data = response.data;
                        $('#attendance_id').val(data.id);
                        $('#modal_att_user_id').val(data.user_id);
                        $('#group_user_id').hide();

                        const dateFormatted = data.attendance_date ? data.attendance_date.substring(0, 10) : '';
                        $('#modal_att_date').val(dateFormatted).prop('readonly', true);

                        $('#modal_att_check_in').val(data.check_in ? data.check_in.substring(0, 5) : '');
                        $('#modal_att_check_out').val(data.check_out ? data.check_out.substring(0, 5) : '');
                        $('#modal_att_status').val(data.status);
                        $('#modal_att_notes').val(data.notes || '');

                        const userName = data.user ? data.user.name : 'Pegawai';
                        $('#modalAttendanceTitle').html('<i class="bi bi-pencil-square me-2 text-primary"></i>Edit Absensi: ' + userName + ' (' + dateFormatted + ')');
                        modalAttendance.show();
                    }
                },
                error: function() {
                    Swal.close();
                    Toast.fire({ icon: 'error', title: 'Gagal mengambil data absensi.' });
                }
            });
        });

        // 6. Submit Form Tambah / Edit Absensi Manual via jQuery AJAX
        $('#formAttendance').on('submit', function(e) {
            e.preventDefault();
            $('.form-control, .form-select').removeClass('is-invalid');

            const attId = $('#attendance_id').val();
            const isEdit = attId !== '';
            const url = isEdit ? "{{ url('/attendances') }}/" + attId + "/update" : "{{ route('attendances.store') }}";

            const formData = {
                user_id: $('#modal_att_user_id').val(),
                attendance_date: $('#modal_att_date').val(),
                check_in: $('#modal_att_check_in').val(),
                check_out: $('#modal_att_check_out').val(),
                status: $('#modal_att_status').val(),
                notes: $('#modal_att_notes').val(),
            };

            $('#btnSimpanAttendance').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menyimpan...');

            $.ajax({
                url: url,
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#btnSimpanAttendance').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (response.status === 'success') {
                        modalAttendance.hide();
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
                    $('#btnSimpanAttendance').prop('disabled', false).html('<i class="bi bi-save me-1"></i> Simpan');
                    if (xhr.status === 422) {
                        const errors = xhr.responseJSON.errors;
                        $.each(errors, function(field, messages) {
                            $('#modal_att_' + field).addClass('is-invalid');
                            $('#error_' + field).text(messages[0]);
                        });
                    } else {
                        Toast.fire({ icon: 'error', title: xhr.responseJSON?.message || 'Gagal menyimpan absensi.' });
                    }
                }
            });
        });

        // 7. Hapus Data Absensi via SweetAlert2 & jQuery AJAX
        $('.btn-delete-att').on('click', function() {
            const attId = $(this).data('id');
            const attInfo = $(this).data('info');

            Swal.fire({
                title: 'Hapus Catatan Absensi?',
                text: `Apakah Anda yakin ingin menghapus data absensi: ${attInfo}?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "{{ url('/attendances') }}/" + attId,
                        type: 'DELETE',
                        dataType: 'json',
                        success: function(response) {
                            if (response.status === 'success') {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Terhapus!',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    location.reload();
                                });
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Gagal!', xhr.responseJSON?.message || 'Gagal menghapus data absensi.', 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endsection
