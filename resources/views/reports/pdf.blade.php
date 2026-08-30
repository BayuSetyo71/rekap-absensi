<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Presensi - {{ $periodLabel }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 15mm 15mm 15mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #1e293b;
            font-size: 9pt;
            line-height: 1.35;
            margin: 0;
            padding: 0;
        }

        /* ══════════════ KOP SURAT RESMI ══════════════ */
        .kop-table {
            width: 100%;
            border-bottom: 2.5px solid #1e1b4b;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .kop-table td {
            vertical-align: middle;
        }

        .brand-title {
            font-size: 16pt;
            font-weight: bold;
            color: #1e1b4b;
            letter-spacing: 0.05em;
            margin: 0;
            text-transform: uppercase;
        }

        .brand-subtitle {
            font-size: 10pt;
            font-weight: bold;
            color: #4f46e5;
            margin: 2px 0 0 0;
        }

        .brand-desc {
            font-size: 8pt;
            color: #64748b;
            margin: 2px 0 0 0;
        }

        /* ══════════════ JUDUL DOKUMEN ══════════════ */
        .report-header {
            text-align: center;
            margin-bottom: 14px;
        }

        .report-title {
            font-size: 13pt;
            font-weight: bold;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin: 0;
        }

        .report-meta {
            font-size: 9pt;
            color: #475569;
            margin-top: 3px;
        }

        /* ══════════════ KPI SUMMARY BOX ══════════════ */
        .kpi-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .kpi-cell {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 6px 10px;
            text-align: center;
            width: 16.66%;
        }

        .kpi-title {
            font-size: 7pt;
            font-weight: bold;
            color: #64748b;
            text-transform: uppercase;
        }

        .kpi-number {
            font-size: 12pt;
            font-weight: bold;
            color: #1e1b4b;
            margin-top: 2px;
        }

        /* ══════════════ TABEL DATA LAPORAN ══════════════ */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 16px;
        }

        .data-table th {
            background-color: #1e1b4b;
            color: #ffffff;
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 6px 4px;
            border: 1px solid #334155;
            text-align: center;
        }

        .data-table td {
            font-size: 8pt;
            padding: 5px 4px;
            border: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: bold; }

        .badge-status {
            font-size: 7pt;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 3px;
            text-transform: uppercase;
            display: inline-block;
        }

        .badge-hadir { background-color: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .badge-terlambat { background-color: #fef3c7; color: #92400e; border: 1px solid #fde68a; }
        .badge-izin { background-color: #cffafe; color: #155e75; border: 1px solid #a5f3fc; }
        .badge-sakit { background-color: #ede9fe; color: #5b21b6; border: 1px solid #ddd6fe; }
        .badge-alpa { background-color: #fee2e2; color: #991b1b; border: 1px solid #fecaca; }

        /* ══════════════ TANDA TANGAN / PENGESAHAN ══════════════ */
        .signature-table {
            width: 100%;
            margin-top: 15px;
            page-break-inside: avoid;
        }

        .signature-table td {
            vertical-align: top;
            font-size: 8.5pt;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <table class="kop-table">
        <tr>
            <td style="width: 70%;">
                <div class="brand-title">YAYASAN PENDIDIKAN & AKSES SISTEM</div>
                <div class="brand-subtitle">SISTEM REKAPITULASI PRESENSI & KEHADIRAN PEGAWAI</div>
                <div class="brand-desc">Layanan Terpadu Jenjang TK, SD, SMP, SMA • Website: absensi.local</div>
            </td>
            <td style="width: 30%; text-align: right; font-size: 7.5pt; color: #64748b;">
                <strong>DOKUMEN RESMI</strong><br>
                Tanggal Cetak: {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
                Dicetak oleh: {{ $currentUser->name }}
            </td>
        </tr>
    </table>

    {{-- JUDUL LAPORAN --}}
    <div class="report-header">
        <div class="report-title">LAPORAN REKAPITULASI PRESENSI & KEHADIRAN</div>
        <div class="report-meta">
            Periode Laporan: <strong>{{ $periodLabel }}</strong> (Bulan {{ $selectedMonth }} / {{ $selectedYear }})
        </div>
    </div>

    {{-- RINGKASAN KPI --}}
    <table class="kpi-table">
        <tr>
            <td class="kpi-cell">
                <div class="kpi-title">Tingkat Hadir</div>
                <div class="kpi-number" style="color:#7c3aed;">{{ $kpi['attendance_rate'] }}%</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-title">Total Record</div>
                <div class="kpi-number">{{ $kpi['total_logs'] }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-title">Hadir Tepat</div>
                <div class="kpi-number" style="color:#059669;">{{ $kpi['hadir_count'] }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-title">Terlambat</div>
                <div class="kpi-number" style="color:#d97706;">{{ $kpi['late_count'] }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-title">Izin / Sakit</div>
                <div class="kpi-number" style="color:#0891b2;">{{ $kpi['izin_count'] + $kpi['sakit_count'] }}</div>
            </td>
            <td class="kpi-cell">
                <div class="kpi-title">Alpa / Tanpa Ket.</div>
                <div class="kpi-number" style="color:#e11d48;">{{ $kpi['alpa_count'] }}</div>
            </td>
        </tr>
    </table>

    {{-- TABEL DATA PRESENSI --}}
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;">NO</th>
                <th style="width: 65px;">NIP</th>
                <th style="width: 140px;">NAMA PEGAWAI / GURU</th>
                <th style="width: 80px;">UNIT / JENJANG</th>
                <th style="width: 70px;">TANGGAL</th>
                <th style="width: 55px;">HARI</th>
                <th style="width: 50px;">MASUK</th>
                <th style="width: 50px;">PULANG</th>
                <th style="width: 65px;">STATUS</th>
                <th>KETERANGAN</th>
            </tr>
        </thead>
        <tbody>
            @forelse($records as $idx => $att)
                @php
                    $u = $att->user;
                    $unitStr = $u && $u->units->isNotEmpty() ? $u->units->pluck('name')->implode(', ') : '-';
                    $statusClass = match($att->status) {
                        'hadir'     => 'badge-hadir',
                        'terlambat' => 'badge-terlambat',
                        'izin'      => 'badge-izin',
                        'sakit'     => 'badge-sakit',
                        default     => 'badge-alpa',
                    };
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-center">{{ $u?->nip ?: '-' }}</td>
                    <td class="fw-bold">{{ $u?->name ?? 'Pegawai Dihapus' }}</td>
                    <td class="text-center">{{ $unitStr }}</td>
                    <td class="text-center">{{ $att->attendance_date ? \Carbon\Carbon::parse($att->attendance_date)->format('d/m/Y') : '-' }}</td>
                    <td class="text-center">{{ $att->attendance_date ? \Carbon\Carbon::parse($att->attendance_date)->translatedFormat('l') : '-' }}</td>
                    <td class="text-center fw-bold">{{ $att->check_in ? substr($att->check_in, 0, 5) : '-' }}</td>
                    <td class="text-center">{{ $att->check_out ? substr($att->check_out, 0, 5) : '-' }}</td>
                    <td class="text-center">
                        <span class="badge-status {{ $statusClass }}">{{ strtoupper($att->status) }}</span>
                    </td>
                    <td>{{ $att->notes ?: '-' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="text-center" style="padding: 20px; color: #94a3b8;">
                        Tidak ada data presensi yang tercatat pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{-- TANDA TANGAN / PENGESAHAN --}}
    <table class="signature-table">
        <tr>
            <td style="width: 55%;">
                <div style="font-size: 8pt; color: #64748b;">
                    <strong>Catatan:</strong><br>
                    1. Dokumen ini digenerate otomatis oleh Sistem Manajemen Presensi Yayasan.<br>
                    2. Laporan sah dan dapat dipergunakan untuk keperluan evaluasi kinerja & administrasi.
                </div>
            </td>
            <td style="width: 45%; text-align: center;">
                <div>Mengetahui,</div>
                <div class="fw-bold" style="margin-top: 2px;">Kepala Kepegawaian & HRD Yayasan</div>
                <div style="height: 45px;"></div>
                <div class="fw-bold" style="text-decoration: underline;">( _____________________________ )</div>
                <div style="font-size: 7.5pt; color: #64748b; margin-top: 2px;">NIP. ........................................</div>
            </td>
        </tr>
    </table>

</body>
</html>
