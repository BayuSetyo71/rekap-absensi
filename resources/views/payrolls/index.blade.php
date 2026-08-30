@extends('layouts.app')

@section('title', 'Penggajian Guru (Payroll)')

@section('content')
<div class="container-fluid px-0">
    <!-- Header Section -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <div class="p-2 rounded-3 text-white shadow-sm" style="background: linear-gradient(135deg, #059669, #10b981);">
                    <i class="bi bi-wallet2 fs-5"></i>
                </div>
                <div>
                    <h4 class="fw-bold mb-0 text-dark">{{ $isGuru ? 'Slip Gaji Digital Saya' : 'Penggajian Guru & Honor Mengajar' }}</h4>
                    <p class="text-muted small mb-0">
                        {{ $isGuru ? 'Rincian honor mengajar per jam, tunjangan, potongan, dan riwayat slip gaji bulanan Anda' : 'Perhitungan otomatis honor mengajar per jam, multi-jenjang (TK, SD, SMP, SMA), tunjangan, dan slip gaji digital' }}
                    </p>
                </div>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(!$isGuru && can_do('payrolls', 'export'))
                <a href="{{ route('payrolls.export-excel', ['period' => $periodMonth, 'unit_id' => $unitFilter]) }}" class="btn btn-outline-success btn-sm px-3 shadow-sm">
                    <i class="bi bi-file-earmark-excel me-1"></i> Rekap Excel
                </a>
                <a href="{{ route('payrolls.export-pdf', ['period' => $periodMonth, 'unit_id' => $unitFilter]) }}" class="btn btn-outline-danger btn-sm px-3 shadow-sm" target="_blank">
                    <i class="bi bi-file-earmark-pdf me-1"></i> Rekap PDF
                </a>
            @endif
            @if(!$isGuru && can_do('payrolls', 'create'))
                <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGeneratePayroll">
                    <i class="bi bi-lightning-charge-fill me-1"></i> Hitung Gaji Otomatis
                </button>
            @endif
        </div>
    </div>

    <!-- Ringkasan KPI Statistik Penggajian Bulan Terpilih -->
    <div class="row g-3 mb-4">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Take Home Pay</span>
                        <h3 class="fw-bold mb-0 text-white mt-1">Rp {{ number_format($totalNet, 0, ',', '.') }}</h3>
                        <small class="text-white-50" style="font-size: 0.75rem;">Gaji bersih seluruh guru</small>
                    </div>
                    <div class="rounded-circle p-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-cash-stack fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #4f46e5 0%, #3730a3 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Honor Mengajar Kotor</span>
                        <h3 class="fw-bold mb-0 text-white mt-1">Rp {{ number_format($totalGross, 0, ',', '.') }}</h3>
                        <small class="text-white-50" style="font-size: 0.75rem;">Sebelum tunjangan/potongan</small>
                    </div>
                    <div class="rounded-circle p-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-calculator-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Total Jam Mengajar</span>
                        <h3 class="fw-bold mb-0 text-white mt-1">{{ number_format($totalHours, 1, ',', '.') }} Jam</h3>
                        <small class="text-white-50" style="font-size: 0.75rem;">Realisasi sesi hadir</small>
                    </div>
                    <div class="rounded-circle p-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-clock-history fs-3"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card shadow-sm border-0 h-100 overflow-hidden text-white" style="background: linear-gradient(135deg, #d97706 0%, #b45309 100%);">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-white-50 small fw-semibold text-uppercase" style="font-size: 0.72rem; letter-spacing: 0.05em;">Guru Terproses</span>
                        <h3 class="fw-bold mb-0 text-white mt-1">{{ $totalTeachers }} Orang</h3>
                        <small class="text-white-50" style="font-size: 0.75rem;">{{ $totalPaid }} sudah dibayar</small>
                    </div>
                    <div class="rounded-circle p-3 text-white" style="background: rgba(255,255,255,0.15);">
                        <i class="bi bi-people-fill fs-3"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Tabel Penggajian -->
    <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom">
            <form action="{{ route('payrolls.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Filter Periode Bulan -->
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label text-muted small fw-semibold mb-1">Periode Bulan</label>
                    <input type="month" name="period" class="form-control form-control-sm bg-light" value="{{ $periodMonth }}" onchange="this.form.submit()">
                </div>

                @if(!$isGuru)
                    <!-- Filter Status -->
                    <div class="col-6 col-sm-3 col-md-2">
                        <label class="form-label text-muted small fw-semibold mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                            <option value="">Semua Status</option>
                            <option value="draft" {{ $statusFilter == 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="locked" {{ $statusFilter == 'locked' ? 'selected' : '' }}>Terkunci</option>
                            <option value="paid" {{ $statusFilter == 'paid' ? 'selected' : '' }}>Dibayar</option>
                        </select>
                    </div>

                    <!-- Filter Unit -->
                    <div class="col-6 col-sm-3 col-md-3">
                        <label class="form-label text-muted small fw-semibold mb-1">Jenjang / Unit</label>
                        <select name="unit_id" class="form-select form-select-sm bg-light" onchange="this.form.submit()">
                            <option value="">Semua Jenjang</option>
                            @foreach($units as $u)
                                <option value="{{ $u->id }}" {{ $unitFilter == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Cari Nama / NIP -->
                    <div class="col-12 col-md-4">
                        <label class="form-label text-muted small fw-semibold mb-1">Pencarian Guru</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" class="form-control bg-light" placeholder="Nama guru / NIP..." value="{{ $search }}">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i></button>
                            @if($search || $statusFilter || $unitFilter)
                                <a href="{{ route('payrolls.index', ['period' => $periodMonth]) }}" class="btn btn-light border" title="Reset"><i class="bi bi-arrow-counterclockwise"></i></a>
                            @endif
                        </div>
                    </div>
                @endif
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4" style="width: 50px;">No</th>
                        <th>Nama Guru / Pendidik</th>
                        <th>Jenjang Bertugas</th>
                        <th class="text-center">Hari Hadir</th>
                        <th class="text-center">Jam Mengajar</th>
                        <th class="text-end">Honor Mengajar</th>
                        <th class="text-end">Tunjangan / Potongan</th>
                        <th class="text-end">Take Home Pay</th>
                        <th class="text-center" style="width: 110px;">Status</th>
                        <th class="text-center pe-4" style="width: 150px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payrolls as $index => $p)
                        <tr>
                            <td class="ps-4 text-muted fw-semibold">{{ $payrolls->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <img src="{{ $p->user->avatar_url }}" alt="{{ $p->user->name }}" class="rounded-circle border" style="width: 38px; height: 38px; object-fit: cover;">
                                    <div>
                                        <div class="fw-bold text-dark">{{ $p->user->name }}</div>
                                        <small class="text-muted" style="font-size: 0.75rem;">NIP: {{ $p->user->nip ?: '-' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    @forelse($p->user->units as $unit)
                                        <span class="badge rounded-pill px-2 py-0.5" style="background-color: {{ $unit->color }}20; color: {{ $unit->color }}; border: 1px solid {{ $unit->color }}40; font-size: 0.7rem;">
                                            {{ $unit->code }}
                                        </span>
                                    @empty
                                        <span class="badge bg-secondary" style="font-size: 0.7rem;">Umum</span>
                                    @endforelse
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-subtle-primary px-2.5 py-1 fw-bold">
                                    <i class="bi bi-calendar-check me-1"></i>{{ $p->total_present_days }} Hari
                                </span>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark">{{ number_format($p->total_hours_taught, 1, ',', '.') }} Jam</div>
                                <small class="text-muted" style="font-size: 0.72rem;">{{ $p->total_sessions_taught }} sesi</small>
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-dark">{{ $p->formatted_gross_amount }}</div>
                            </td>
                            <td class="text-end">
                                @if($p->total_allowances > 0)
                                    <div class="text-success small fw-semibold">+Rp {{ number_format($p->total_allowances, 0, ',', '.') }}</div>
                                @endif
                                @if($p->total_deductions > 0)
                                    <div class="text-danger small fw-semibold">-Rp {{ number_format($p->total_deductions, 0, ',', '.') }}</div>
                                @endif
                                @if($p->total_allowances == 0 && $p->total_deductions == 0)
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="fw-bold text-success fs-6">{{ $p->formatted_net_salary }}</div>
                            </td>
                            <td class="text-center">
                                {!! $p->status_badge !!}
                            </td>
                            <td class="text-center pe-4">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('payrolls.show', $p->id) }}" class="btn btn-outline-primary" title="Rincian Breakdown & Penyesuaian">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <a href="{{ route('payrolls.slip-pdf', $p->id) }}" class="btn btn-outline-danger" title="Cetak Slip Gaji (PDF)" target="_blank">
                                        <i class="bi bi-file-earmark-pdf"></i>
                                    </a>
                                    @if(!$isGuru && $p->status !== 'paid' && can_do('payrolls', 'delete'))
                                        <button type="button" class="btn btn-outline-secondary btn-delete-payroll" data-id="{{ $p->id }}" data-name="{{ $p->user->name }}" title="Hapus Draft">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center py-5 text-muted">
                                <i class="bi bi-wallet2 text-secondary fs-1 d-block mb-2 opacity-50"></i>
                                <strong>Belum ada data penggajian pada periode {{ Carbon\Carbon::createFromFormat('Y-m', $periodMonth)->translatedFormat('F Y') }}</strong>
                                <p class="small mb-3">Klik tombol "Hitung Gaji Otomatis" untuk mengkalkulasi honor guru berdasarkan jam mengajar dan presensi bulan ini.</p>
                                @if(!$isGuru && can_do('payrolls', 'create'))
                                    <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGeneratePayroll">
                                        <i class="bi bi-lightning-charge-fill me-1"></i> Hitung Gaji Periode Ini
                                    </button>
                                @endif
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($payrolls->hasPages())
            <div class="card-footer bg-white py-3 px-4 border-top">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                    <small class="text-muted">Menampilkan {{ $payrolls->firstItem() }} - {{ $payrolls->lastItem() }} dari {{ $payrolls->total() }} pegawai</small>
                    {{ $payrolls->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal Generate Payroll Otomatis -->
<div class="modal fade" id="modalGeneratePayroll" tabindex="-1" aria-labelledby="modalGeneratePayrollLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #059669, #10b981);">
                <h5 class="modal-title fw-bold" id="modalGeneratePayrollLabel">
                    <i class="bi bi-lightning-charge-fill me-1.5"></i> Hitung Gaji Guru Otomatis
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payrolls.generate') }}" method="POST" id="formGeneratePayroll">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill fs-5 flex-shrink-0 text-info mt-0.5"></i>
                        <div class="small">
                            Sistem akan mengumpulkan data presensi <strong>Hadir & Terlambat</strong> guru, lalu mencocokkan jam sesi mengajar di tiap jenjang dengan <strong>Tarif Honor</strong> yang berlaku.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Pilih Periode Bulan & Tahun <span class="text-danger">*</span></label>
                        <input type="month" name="period_month" class="form-control fw-bold text-primary" value="{{ $periodMonth }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Pilih Sasaran Perhitungan</label>
                        <select name="user_id" class="form-select">
                            <option value="">⚡ Seluruh Guru Aktif Sekaligus (Rekomendasi)</option>
                            @foreach($allTeachers as $t)
                                <option value="{{ $t->id }}">{{ $t->name }} ({{ $t->nip ?: 'No NIP' }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Pilih satu guru jika hanya ingin menghitung ulang perorangan.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm" id="btnSubmitGenerate">
                        <i class="bi bi-play-circle-fill me-1"></i> Mulai Kalkulasi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Form Dummy untuk Delete -->
<form id="deletePayrollForm" method="POST" class="d-none">
    @csrf
    @method('DELETE')
</form>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // 1. Loading saat submit kalkulasi
    $('#formGeneratePayroll').on('submit', function() {
        $('#btnSubmitGenerate').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Menghitung...');
    });

    // 2. Konfirmasi Hapus Draft
    $('.btn-delete-payroll').on('click', function() {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const deleteUrl = "{{ url('payrolls') }}/" + id;

        Swal.fire({
            title: 'Hapus Draft Gaji?',
            html: `Apakah Anda yakin ingin menghapus data draft gaji untuk <strong>"${name}"</strong>?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                $('#deletePayrollForm').attr('action', deleteUrl).submit();
            }
        });
    });
});
</script>
@endsection
