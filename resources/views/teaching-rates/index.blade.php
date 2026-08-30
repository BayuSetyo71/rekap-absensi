@extends('layouts.app')

@section('title', 'Tarif Honor Mengajar')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="p-2 rounded-3 text-white shadow-sm" style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                    <i class="bi bi-tags-fill fs-5"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">Matriks Tarif Honor Mengajar</h4>
                    <p class="text-muted small mb-0">Konfigurasi nominal honor mengajar per jam berdasarkan jenjang sekolah (TK, SD, SMP, SMA) dan mata pelajaran spesifik</p>
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            @if(can_do('teaching-rates', 'create'))
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddRate">
                    <i class="bi bi-plus-lg me-1.5"></i> Tambah Tarif Baru
                </button>
            @endif
        </div>
    </div>

    <!-- Ringkasan KPI Statistik -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Aturan Tarif</span>
                        <h3 class="fw-bold mb-0 text-white mt-1">{{ $totalRates }}</h3>
                        <small class="text-white-50" style="font-size: 0.75rem;">{{ $activeRates }} tarif aktif</small>
                    </div>
                    <div class="rounded-circle p-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-cash-coin fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #059669 0%, #065f46 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Jenjang Tercover</span>
                        <h3 class="fw-bold mb-0 text-white mt-1">{{ $unitsCount }} Unit</h3>
                        <small class="text-white-50" style="font-size: 0.75rem;">TK, SD, SMP, SMA & Yayasan</small>
                    </div>
                    <div class="rounded-circle p-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-mortarboard-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-4">
            <div class="card shadow-sm border-0 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Modul Penggajian Terhubung</span>
                        <h3 class="fw-bold mb-0 text-white mt-1">Aktif & Otomatis</h3>
                        <small class="text-white-50" style="font-size: 0.75rem;">Sinkron dengan Sesi Presensi</small>
                    </div>
                    <div class="rounded-circle p-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-check2-circle fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Tabel Tarif -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <form action="{{ route('teaching-rates.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Cari mata pelajaran atau catatan..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="unit_id" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                        <option value="">Semua Jenjang / Unit</option>
                        @foreach($units as $u)
                            <option value="{{ $u->id }}" {{ $unitFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <div class="d-flex gap-1">
                        <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                            <i class="bi bi-filter me-1"></i> Filter
                        </button>
                        @if($search || $unitFilter)
                            <a href="{{ route('teaching-rates.index') }}" class="btn btn-sm btn-light border" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        @endif
                    </div>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 60px;">No</th>
                        <th>Jenjang / Unit</th>
                        <th>Mata Pelajaran</th>
                        <th>Nominal Honor / Jam</th>
                        <th>Tipe Perhitungan</th>
                        <th>Keterangan / Catatan</th>
                        <th class="text-center" style="width: 100px;">Status</th>
                        <th class="text-center pe-4" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rates as $index => $rate)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $rates->firstItem() + $index }}</td>
                            <td>
                                @if($rate->unit)
                                    <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $rate->unit->color }}20; color: {{ $rate->unit->color }}; border: 1px solid {{ $rate->unit->color }}40;">
                                        <i class="bi bi-mortarboard-fill me-1"></i>{{ $rate->unit->name }}
                                    </span>
                                @else
                                    <span class="badge bg-secondary">Umum</span>
                                @endif
                            </td>
                            <td>
                                @if(strtoupper($rate->subject_name) === 'DEFAULT')
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 fw-bold">
                                            <i class="bi bi-star-fill text-warning me-1"></i>TARIF STANDAR JENJANG
                                        </span>
                                    </div>
                                    <small class="text-muted" style="font-size: 0.75rem;">Berlaku untuk seluruh mapel reguler di unit ini</small>
                                @else
                                    <div class="fw-semibold text-dark">{{ $rate->subject_name }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-success fs-6">{{ $rate->formatted_rate }}</div>
                                <small class="text-muted" style="font-size: 0.72rem;">per 60 menit mengajar</small>
                            </td>
                            <td>
                                <span class="badge bg-light text-secondary border px-2 py-1">
                                    <i class="bi bi-clock-history me-1"></i>Per Jam (60 Menit)
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">{{ $rate->notes ?: '-' }}</small>
                            </td>
                            <td class="text-center">
                                @if(can_do('teaching-rates', 'update'))
                                    <div class="form-check form-switch d-inline-block">
                                        <input class="form-check-input btn-toggle-active" type="checkbox" role="switch"
                                               data-id="{{ $rate->id }}"
                                               {{ $rate->is_active ? 'checked' : '' }}
                                               style="cursor: pointer;">
                                    </div>
                                @else
                                    <span class="badge {{ $rate->is_active ? 'badge-subtle-success' : 'badge-subtle-danger' }}">
                                        {{ $rate->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group btn-group-sm">
                                    @if(can_do('teaching-rates', 'update'))
                                        <button type="button" class="btn btn-outline-primary btn-edit-rate" data-id="{{ $rate->id }}" title="Edit Tarif">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                    @endif
                                    @if(can_do('teaching-rates', 'delete'))
                                        <button type="button" class="btn btn-outline-danger btn-delete-rate" data-id="{{ $rate->id }}" data-name="{{ $rate->subject_name }} ({{ $rate->unit?->name }})" title="Hapus Tarif">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="bi bi-tags text-secondary fs-1 d-block mb-2 opacity-50"></i>
                                <strong>Belum ada data tarif honor mengajar</strong>
                                <p class="small mb-0">Klik tombol "Tambah Tarif Baru" untuk mengatur nominal honor per jenjang dan mata pelajaran.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rates->hasPages())
            <div class="card-footer bg-white py-3 px-4 border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">Menampilkan {{ $rates->firstItem() }} - {{ $rates->lastItem() }} dari {{ $rates->total() }} tarif</small>
                    {{ $rates->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Tambah Tarif Baru -->
<div class="modal fade" id="modalAddRate" tabindex="-1" aria-labelledby="modalAddRateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #7c3aed, #4f46e5);">
                <h5 class="modal-title fw-bold" id="modalAddRateLabel">
                    <i class="bi bi-plus-circle me-1.5"></i> Tambah Tarif Honor Mengajar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('teaching-rates.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Jenjang / Unit Sekolah <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-select" required>
                            <option value="">-- Pilih Unit / Jenjang --</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="subject_name" class="form-control" placeholder="Contoh: Coding / Informatika atau DEFAULT" required>
                        <div class="form-text small">Ketik <code>DEFAULT</code> jika ini adalah tarif standar umum untuk semua mapel di jenjang tersebut.</div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label fw-semibold text-dark small">Nominal Honor (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                                <input type="number" name="rate_per_hour" class="form-control fw-bold text-success" placeholder="50000" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold text-dark small">Tipe Hitungan <span class="text-danger">*</span></label>
                            <select name="rate_type" class="form-select" required>
                                <option value="per_hour" selected>Per Jam (60m)</option>
                                <option value="per_session">Per Sesi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Catatan / Keterangan</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan tambahan (opsional)..."></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addIsActive" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="addIsActive">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Tarif
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Edit Tarif -->
<div class="modal fade" id="modalEditRate" tabindex="-1" aria-labelledby="modalEditRateLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #4f46e5, #3730a3);">
                <h5 class="modal-title fw-bold" id="modalEditRateLabel">
                    <i class="bi bi-pencil-square me-1.5"></i> Edit Tarif Honor Mengajar
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formEditRate" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Jenjang / Unit Sekolah <span class="text-danger">*</span></label>
                        <select name="unit_id" id="editUnitId" class="form-select" required>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" name="subject_name" id="editSubjectName" class="form-control" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-7">
                            <label class="form-label fw-semibold text-dark small">Nominal Honor (Rp) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                                <input type="number" name="rate_per_hour" id="editRatePerHour" class="form-control fw-bold text-success" min="0" step="1000" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-5">
                            <label class="form-label fw-semibold text-dark small">Tipe Hitungan <span class="text-danger">*</span></label>
                            <select name="rate_type" id="editRateType" class="form-select" required>
                                <option value="per_hour">Per Jam (60m)</option>
                                <option value="per_session">Per Sesi</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Catatan / Keterangan</label>
                        <textarea name="notes" id="editNotes" class="form-control" rows="2"></textarea>
                    </div>

                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
                        <label class="form-check-label small fw-semibold text-dark" for="editIsActive">Status Aktif</label>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Dummy untuk Delete -->
<form id="deleteRateForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // 1. AJAX Edit Rate
    $('.btn-edit-rate').on('click', function() {
        const rateId = $(this).data('id');
        const url = "{{ url('teaching-rates') }}/" + rateId + "/edit";
        const updateUrl = "{{ url('teaching-rates') }}/" + rateId + "/update";

        $('#formEditRate').attr('action', updateUrl);

        Swal.fire({
            title: 'Memuat data...',
            allowOutsideClick: false,
            didOpen: () => { Swal.showLoading(); }
        });

        $.get(url, function(res) {
            Swal.close();
            if (res.status === 'success') {
                const data = res.data;
                $('#editUnitId').val(data.unit_id);
                $('#editSubjectName').val(data.subject_name);
                $('#editRatePerHour').val(parseInt(data.rate_per_hour));
                $('#editRateType').val(data.rate_type);
                $('#editNotes').val(data.notes);
                $('#editIsActive').prop('checked', data.is_active == 1);

                $('#modalEditRate').modal('show');
            }
        }).fail(function() {
            Swal.fire('Error', 'Gagal memuat data tarif.', 'error');
        });
    });

    // 2. AJAX Toggle Active
    $('.btn-toggle-active').on('change', function() {
        const rateId = $(this).data('id');
        const toggleUrl = "{{ url('teaching-rates') }}/" + rateId + "/toggle";

        $.post(toggleUrl, function(res) {
            if (res.status === 'success') {
                Toast.fire({
                    icon: 'success',
                    title: res.message
                });
            }
        }).fail(function() {
            Toast.fire({
                icon: 'error',
                title: 'Gagal memperbarui status aktif.'
            });
        });
    });

    // 3. Delete Confirmation
    $('.btn-delete-rate').on('click', function() {
        const rateId = $(this).data('id');
        const rateName = $(this).data('name');
        const deleteUrl = "{{ url('teaching-rates') }}/" + rateId;

        Swal.fire({
            title: 'Hapus Tarif Ini?',
            html: `Apakah Anda yakin ingin menghapus aturan tarif <strong>"${rateName}"</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deleteRateForm').attr('action', deleteUrl).submit();
            }
        });
    });
});
</script>
@endsection
