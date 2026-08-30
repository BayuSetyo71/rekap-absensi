@extends('layouts.app')

@section('title', 'Rincian Slip Gaji - ' . $payroll->user->name)

@section('content')
<div class="container-fluid px-0">
    <!-- Header & Navigasi -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('payrolls.index', ['period' => $payroll->period_month]) }}" class="btn btn-light border shadow-sm" title="Kembali ke Rekap">
                <i class="bi bi-arrow-left"></i>
            </a>
            <div>
                <div class="d-flex align-items-center gap-2">
                    <h4 class="fw-bold mb-0 text-dark">Rincian Slip Gaji Guru</h4>
                    {!! $payroll->status_badge !!}
                </div>
                <p class="text-muted small mb-0">Periode: <strong>{{ $payroll->formatted_period }}</strong> &bull; Pegawai: <strong>{{ $payroll->user->name }}</strong></p>
            </div>
        </div>

        <div class="d-flex flex-wrap align-items-center gap-2">
            @if(can_do('payrolls', 'export'))
                <a href="{{ route('payrolls.slip-pdf', $payroll->id) }}" class="btn btn-outline-danger shadow-sm" target="_blank">
                    <i class="bi bi-file-earmark-pdf-fill me-1"></i> Cetak Slip PDF
                </a>
            @endif

            @if(can_do('payrolls', 'update') && $payroll->status !== 'paid')
                <form action="{{ route('payrolls.recalculate', $payroll->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary shadow-sm" title="Sinkronkan dengan data presensi terbaru">
                        <i class="bi bi-arrow-repeat me-1"></i> Hitung Ulang
                    </button>
                </form>

                <!-- Ubah Status Dropdown -->
                <div class="dropdown d-inline-block">
                    <button class="btn btn-dark dropdown-toggle shadow-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-shield-check me-1"></i> Ubah Status
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0">
                        @if($payroll->status !== 'draft')
                            <li>
                                <form action="{{ route('payrolls.status.update', $payroll->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="draft">
                                    <button type="submit" class="dropdown-item text-warning">
                                        <i class="bi bi-pencil-square me-2"></i> Jadikan Draft
                                    </button>
                                </form>
                            </li>
                        @endif
                        @if($payroll->status !== 'locked')
                            <li>
                                <form action="{{ route('payrolls.status.update', $payroll->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="locked">
                                    <button type="submit" class="dropdown-item text-info">
                                        <i class="bi bi-lock-fill me-2"></i> Kunci Gaji (Locked)
                                    </button>
                                </form>
                            </li>
                        @endif
                        @if($payroll->status !== 'paid')
                            <li>
                                <form action="{{ route('payrolls.status.update', $payroll->id) }}" method="POST">
                                    @csrf
                                    <input type="hidden" name="status" value="paid">
                                    <button type="submit" class="dropdown-item text-success fw-bold">
                                        <i class="bi bi-check2-circle me-2"></i> Tandai Sudah Dibayar (Paid)
                                    </button>
                                </form>
                            </li>
                        @endif
                    </ul>
                </div>
            @endif
        </div>
    </div>

    <!-- Profil Guru & Ringkasan Periode Card -->
    <div class="card shadow-sm border-0 rounded-4 mb-4 overflow-hidden" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 100%);">
        <div class="card-body p-4 text-white">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-auto text-center text-md-start">
                    <img src="{{ $payroll->user->avatar_url }}" alt="{{ $payroll->user->name }}" class="rounded-circle border border-3 border-white border-opacity-25 shadow" style="width: 70px; height: 70px; object-fit: cover;">
                </div>
                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1 justify-content-center justify-content-md-start">
                        <h4 class="fw-bold mb-0 text-white">{{ $payroll->user->name }}</h4>
                        @foreach($payroll->user->units as $u)
                            <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $u->color }}30; color: #fff; border: 1px solid {{ $u->color }}70; font-size: 0.75rem;">
                                {{ $u->name }}
                            </span>
                        @endforeach
                    </div>
                    <div class="text-white-50 small d-flex flex-wrap gap-3 justify-content-center justify-content-md-start">
                        <span><i class="bi bi-card-text me-1"></i> NIP: {{ $payroll->user->nip ?: '-' }}</span>
                        <span><i class="bi bi-envelope me-1"></i> {{ $payroll->user->email }}</span>
                        <span><i class="bi bi-telephone me-1"></i> {{ $payroll->user->phone ?: '-' }}</span>
                    </div>
                </div>
                <div class="col-12 col-md-auto text-center text-md-end border-top border-md-top-0 border-white border-opacity-10 pt-3 pt-md-0">
                    <div class="small text-white-50 text-uppercase fw-semibold" style="font-size: 0.72rem;">Gaji Bersih (Take Home Pay)</div>
                    <div class="fs-3 fw-bold text-success mt-0">{{ $payroll->formatted_net_salary }}</div>
                    <small class="text-white-50" style="font-size: 0.72rem;">{{ $payroll->total_hours_taught }} Jam &bull; {{ $payroll->total_present_days }} Hari Hadir</small>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Kolom Kiri: Breakdown Sesi Mengajar & Komponen Penyesuaian -->
        <div class="col-12 col-lg-8">
            <!-- 1. Tabel Rincian Breakdown Sesi Mengajar -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-journal-bookmark-fill text-primary fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">Rincian Realisasi Mengajar Per Jenjang & Mapel</h6>
                    </div>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">
                        {{ $payroll->details->count() }} Mata Pelajaran
                    </span>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Jenjang / Unit</th>
                                <th>Mata Pelajaran</th>
                                <th class="text-center">Sesi Terlaksana</th>
                                <th class="text-center">Durasi Jam</th>
                                <th class="text-end">Tarif / Jam</th>
                                <th class="text-end pe-4">Subtotal Honor</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payroll->details as $d)
                                <tr>
                                    <td class="ps-4">
                                        @if($d->unit)
                                            <span class="badge rounded-pill px-2.5 py-1" style="background-color: {{ $d->unit->color }}20; color: {{ $d->unit->color }}; border: 1px solid {{ $d->unit->color }}40;">
                                                {{ $d->unit->name }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">Umum</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $d->subject }}</div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-2 py-1">{{ $d->total_sessions }} Sesi</span>
                                    </td>
                                    <td class="text-center fw-bold text-dark">
                                        {{ number_format($d->total_hours, 1, ',', '.') }} Jam
                                    </td>
                                    <td class="text-end text-muted">
                                        {{ $d->formatted_rate }}
                                    </td>
                                    <td class="text-end pe-4 fw-bold text-dark">
                                        {{ $d->formatted_subtotal }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        <i class="bi bi-info-circle fs-4 d-block mb-1"></i>
                                        Tidak ada jam mengajar di hari kehadiran pada periode ini.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light fw-bold border-top">
                            <tr>
                                <td colspan="2" class="ps-4 text-dark">TOTAL REKAPITULASI MENGAJAR</td>
                                <td class="text-center text-primary">{{ $payroll->total_sessions_taught }} Sesi</td>
                                <td class="text-center text-primary">{{ number_format($payroll->total_hours_taught, 1, ',', '.') }} Jam</td>
                                <td class="text-end text-muted">Subtotal Kotor:</td>
                                <td class="text-end pe-4 text-dark fs-6">{{ $payroll->formatted_gross_amount }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            <!-- 2. Komponen Penyesuaian (Tunjangan & Potongan) -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-sliders text-success fs-5"></i>
                        <h6 class="fw-bold mb-0 text-dark">Penyesuaian Gaji (Tunjangan & Potongan)</h6>
                    </div>
                    @if(can_do('payrolls', 'update') && $payroll->status !== 'paid')
                        <button type="button" class="btn btn-sm btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalAddAdjustment">
                            <i class="bi bi-plus-circle me-1"></i> Tambah Penyesuaian
                        </button>
                    @endif
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4" style="width: 120px;">Tipe</th>
                                <th>Nama Komponen</th>
                                <th>Keterangan / Catatan</th>
                                <th class="text-end" style="width: 160px;">Nominal (Rp)</th>
                                @if(can_do('payrolls', 'update') && $payroll->status !== 'paid')
                                    <th class="text-center pe-4" style="width: 70px;">Aksi</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($payroll->adjustments as $adj)
                                <tr>
                                    <td class="ps-4">
                                        {!! $adj->type_badge !!}
                                    </td>
                                    <td class="fw-semibold text-dark">
                                        {{ $adj->name }}
                                    </td>
                                    <td class="small text-muted">
                                        {{ $adj->notes ?: '-' }}
                                    </td>
                                    <td class="text-end fw-bold {{ $adj->type === 'allowance' ? 'text-success' : 'text-danger' }}">
                                        {{ $adj->type === 'allowance' ? '+' : '-' }}{{ $adj->formatted_amount }}
                                    </td>
                                    @if(can_do('payrolls', 'update') && $payroll->status !== 'paid')
                                        <td class="text-center pe-4">
                                            <form action="{{ route('payrolls.adjustments.destroy', [$payroll->id, $adj->id]) }}" method="POST" onsubmit="return confirm('Hapus komponen penyesuaian ini?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger btn-sm p-1 border-0" title="Hapus">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ (can_do('payrolls', 'update') && $payroll->status !== 'paid') ? 5 : 4 }}" class="text-center py-4 text-muted">
                                        <small>Belum ada komponen tunjangan atau potongan tambahan pada slip gaji ini.</small>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Kalkulator Ringkasan & Informasi Tambahan -->
        <div class="col-12 col-lg-4">
            <!-- Kartu Kalkulator Ringkasan -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden mb-4">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-receipt me-1.5 text-primary"></i> Ringkasan Pembayaran Gaji</h6>
                </div>
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <span class="text-muted small">Honor Mengajar Kotor</span>
                        <span class="fw-bold text-dark">{{ $payroll->formatted_gross_amount }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-2.5">
                        <span class="text-success small"><i class="bi bi-plus-circle me-1"></i>Total Tunjangan</span>
                        <span class="fw-bold text-success">+{{ $payroll->formatted_total_allowances }}</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-danger small"><i class="bi bi-dash-circle me-1"></i>Total Potongan</span>
                        <span class="fw-bold text-danger">-{{ $payroll->formatted_total_deductions }}</span>
                    </div>

                    <hr class="my-3 opacity-15">

                    <div class="p-3 rounded-3 text-center mb-3" style="background: linear-gradient(135deg, #dcfce7 0%, #bbf7d0 100%); border: 1px solid #86efac;">
                        <span class="text-success-emphasis small text-uppercase fw-bold" style="font-size: 0.72rem; letter-spacing: 0.05em;">Take Home Pay (Gaji Bersih)</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ $payroll->formatted_net_salary }}</h3>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('payrolls.slip-pdf', $payroll->id) }}" class="btn btn-outline-danger shadow-sm" target="_blank">
                            <i class="bi bi-download me-1.5"></i> Unduh Slip Gaji PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Kartu Info Log Presensi & Sistem -->
            <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="bi bi-info-circle me-1.5 text-info"></i> Informasi & Log Sistem</h6>
                </div>
                <div class="card-body p-4">
                    <ul class="list-group list-group-flush small">
                        <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                            <span class="text-muted">Total Kehadiran:</span>
                            <span class="fw-bold text-primary">{{ $payroll->total_present_days }} Hari Hadir</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                            <span class="text-muted">Status Gaji:</span>
                            <span>{!! $payroll->status_badge !!}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                            <span class="text-muted">Diproses Oleh:</span>
                            <span class="fw-semibold text-dark">{{ $payroll->processor?->name ?: 'Sistem Otomatis' }}</span>
                        </li>
                        <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                            <span class="text-muted">Tanggal Dibuat:</span>
                            <span class="text-dark">{{ $payroll->created_at->translatedFormat('d M Y H:i') }}</span>
                        </li>
                        @if($payroll->paid_at)
                            <li class="list-group-item d-flex justify-content-between px-0 py-2 border-0">
                                <span class="text-muted">Waktu Pembayaran:</span>
                                <span class="text-success fw-bold">{{ $payroll->paid_at->translatedFormat('d M Y H:i') }}</span>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Penyesuaian (Tunjangan / Potongan) -->
<div class="modal fade" id="modalAddAdjustment" tabindex="-1" aria-labelledby="modalAddAdjustmentLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #059669, #10b981);">
                <h5 class="modal-title fw-bold" id="modalAddAdjustmentLabel">
                    <i class="bi bi-plus-circle me-1.5"></i> Tambah Penyesuaian Gaji
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('payrolls.adjustments.store', $payroll->id) }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Jenis Penyesuaian <span class="text-danger">*</span></label>
                        <select name="type" class="form-select" required>
                            <option value="allowance" selected>🟢 Tunjangan (Menambah Gaji)</option>
                            <option value="deduction">🔴 Potongan (Mengurangi Gaji)</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Nama Komponen <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="Contoh: Tunjangan Wali Kelas, Kasbon Koperasi, dll." required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Nominal (Rp) <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light fw-bold text-muted">Rp</span>
                            <input type="number" name="amount" class="form-control fw-bold" placeholder="100000" min="1" step="1000" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Catatan Tambahan (Opsional)</label>
                        <textarea name="notes" class="form-control" rows="2" placeholder="Catatan atau nomor referensi kasbon..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light px-4 py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4 shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Penyesuaian
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
