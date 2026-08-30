<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Slip Gaji - {{ $payroll->user->name }} - {{ $payroll->period_month }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9.5pt;
            color: #1e293b;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header-logo {
            font-size: 18pt;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: -0.5px;
        }
        .header-sub {
            font-size: 7.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1px;
        }
        .header-title {
            text-align: right;
            font-size: 13pt;
            font-weight: 700;
            color: #0f172a;
        }
        .header-period {
            text-align: right;
            font-size: 9pt;
            color: #4f46e5;
            font-weight: 600;
            margin-top: 2px;
        }
        .info-card {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 4px;
            margin-bottom: 12px;
            padding: 8px 12px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2px 4px;
            font-size: 8.5pt;
            vertical-align: top;
        }
        .info-label {
            color: #64748b;
            font-weight: 600;
            width: 90px;
        }
        .info-value {
            color: #0f172a;
            font-weight: 700;
        }
        .badge-unit {
            display: inline-block;
            padding: 1px 6px;
            font-size: 7.5pt;
            font-weight: 700;
            color: #ffffff;
            background-color: #4f46e5;
            border-radius: 3px;
            margin-right: 3px;
        }
        .section-title {
            font-size: 9.5pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 6px;
            border-left: 3px solid #4f46e5;
            padding-left: 6px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 12px;
        }
        .data-table th {
            background-color: #f1f5f9;
            color: #334155;
            font-size: 8pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 8px;
            border: 1px solid #cbd5e1;
        }
        .data-table td {
            padding: 5px 8px;
            font-size: 8.5pt;
            border: 1px solid #e2e8f0;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: 700; }
        
        .calc-box {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .calc-box td {
            padding: 5px 8px;
            font-size: 9pt;
        }
        .calc-box .label {
            width: 65%;
            border-bottom: 1px dashed #e2e8f0;
        }
        .calc-box .amount {
            width: 35%;
            text-align: right;
            font-weight: 700;
            border-bottom: 1px dashed #e2e8f0;
        }
        .take-home-pay-box {
            background-color: #ecfdf5;
            border: 2px solid #10b981;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 20px;
            text-align: right;
        }
        .thp-title {
            font-size: 8pt;
            color: #065f46;
            text-transform: uppercase;
            font-weight: 700;
            letter-spacing: 0.5px;
        }
        .thp-amount {
            font-size: 16pt;
            color: #047857;
            font-weight: 800;
            margin-top: 1px;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
        }
        .signatures-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .signature-space {
            height: 55px;
        }
        .signature-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-role {
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .watermark {
            position: fixed;
            bottom: 5px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 7pt;
            color: #94a3b8;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="header-logo">YAYASAN PENDIDIKAN</div>
                <div class="header-sub">Unit Sekolah: TK &bull; SD &bull; SMP &bull; SMA &bull; Manajemen Yayasan</div>
            </td>
            <td style="width: 45%;">
                <div class="header-title">SLIP GAJI GURU</div>
                <div class="header-period">Periode: {{ $payroll->formatted_period }}</div>
            </td>
        </tr>
    </table>

    <!-- Profil Pegawai -->
    <div class="info-card">
        <table class="info-table">
            <tr>
                <td class="info-label">Nama Guru</td>
                <td style="width: 5px;">:</td>
                <td class="info-value">{{ $payroll->user->name }}</td>
                <td class="info-label">NIP / ID</td>
                <td style="width: 5px;">:</td>
                <td class="info-value">{{ $payroll->user->nip ?: '-' }}</td>
            </tr>
            <tr>
                <td class="info-label">Jenjang Unit</td>
                <td style="width: 5px;">:</td>
                <td>
                    @foreach($payroll->user->units as $u)
                        <span class="badge-unit" style="background-color: {{ $u->color ?: '#4f46e5' }};">{{ $u->name }}</span>
                    @endforeach
                </td>
                <td class="info-label">Kehadiran</td>
                <td style="width: 5px;">:</td>
                <td class="info-value">{{ $payroll->total_present_days }} Hari Hadir</td>
            </tr>
            <tr>
                <td class="info-label">Status Gaji</td>
                <td style="width: 5px;">:</td>
                <td class="info-value" style="color: {{ $payroll->status === 'paid' ? '#059669' : '#d97706' }}; text-transform: uppercase;">
                    {{ $payroll->status === 'paid' ? 'LUNAS / SUDAH DIBAYAR' : ($payroll->status === 'locked' ? 'TERKUNCI (SIAP DIBAYAR)' : 'DRAFT') }}
                </td>
                <td class="info-label">Tgl Cetak</td>
                <td style="width: 5px;">:</td>
                <td>{{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB</td>
            </tr>
        </table>
    </div>

    <!-- 1. Rincian Realisasi Mengajar -->
    <div class="section-title">A. RINCIAN HONOR MENGAJAR (BERBASIS JAM & MAPEL)</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 120px;">Jenjang / Unit</th>
                <th>Mata Pelajaran</th>
                <th style="width: 60px;" class="text-center">Sesi</th>
                <th style="width: 70px;" class="text-center">Total Jam</th>
                <th style="width: 80px;" class="text-right">Tarif / Jam</th>
                <th style="width: 95px;" class="text-right">Subtotal Honor</th>
            </tr>
        </thead>
        <tbody>
            @forelse($payroll->details as $idx => $d)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $d->unit?->name ?: 'Yayasan' }}</td>
                    <td><strong>{{ $d->subject }}</strong></td>
                    <td class="text-center">{{ $d->total_sessions }}</td>
                    <td class="text-center">{{ number_format($d->total_hours, 1, ',', '.') }} Jam</td>
                    <td class="text-right">{{ $d->formatted_rate }}</td>
                    <td class="text-right fw-bold">{{ $d->formatted_subtotal }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 10px; color: #94a3b8;">
                        Tidak ada sesi mengajar di hari kehadiran pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #f8fafc; font-weight: 700;">
                <td colspan="3" class="text-right">TOTAL REALISASI MENGAJAR:</td>
                <td class="text-center">{{ $payroll->total_sessions_taught }} Sesi</td>
                <td class="text-center">{{ number_format($payroll->total_hours_taught, 1, ',', '.') }} Jam</td>
                <td class="text-right">Total Kotor:</td>
                <td class="text-right" style="color: #0f172a;">{{ $payroll->formatted_gross_amount }}</td>
            </tr>
        </tfoot>
    </table>

    <!-- 2. Rincian Penyesuaian (Tunjangan & Potongan) -->
    <div class="section-title">B. KOMPONEN PENGHASILAN & POTONGAN LAINNYA</div>
    <table class="calc-box">
        <tr>
            <td class="label">1. Total Honor Mengajar Kotor</td>
            <td class="amount">{{ $payroll->formatted_gross_amount }}</td>
        </tr>

        <!-- Tunjangan -->
        @if($payroll->allowances->isNotEmpty())
            @foreach($payroll->allowances as $al)
                <tr>
                    <td class="label" style="padding-left: 15px; color: #059669;">+ {{ $al->name }} @if($al->notes) <small style="color:#64748b;">({{ $al->notes }})</small> @endif</td>
                    <td class="amount" style="color: #059669;">+{{ $al->formatted_amount }}</td>
                </tr>
            @endforeach
        @endif

        <!-- Potongan -->
        @if($payroll->deductions->isNotEmpty())
            @foreach($payroll->deductions as $de)
                <tr>
                    <td class="label" style="padding-left: 15px; color: #dc2626;">- {{ $de->name }} @if($de->notes) <small style="color:#64748b;">({{ $de->notes }})</small> @endif</td>
                    <td class="amount" style="color: #dc2626;">-{{ $de->formatted_amount }}</td>
                </tr>
            @endforeach
        @endif
    </table>

    <!-- Take Home Pay Card -->
    <div class="take-home-pay-box">
        <div class="thp-title">Total Gaji Bersih Diterima (Take Home Pay)</div>
        <div class="thp-amount">{{ $payroll->formatted_net_salary }}</div>
    </div>

    <!-- Tanda Tangan -->
    <table class="signatures-table">
        <tr>
            <td>
                <div>Mengetahui,</div>
                <div style="font-weight: 700; color: #0f172a;">Bendahara / Yayasan</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
                <div class="signature-role">NIP / Jabatan</div>
            </td>
            <td>
                <div>Diterima oleh,</div>
                <div style="font-weight: 700; color: #0f172a;">Guru / Tenaga Pengajar</div>
                <div class="signature-space"></div>
                <div class="signature-name">{{ $payroll->user->name }}</div>
                <div class="signature-role">NIP: {{ $payroll->user->nip ?: '-' }}</div>
            </td>
        </tr>
    </table>

    <div class="watermark">
        Dokumen ini diterbitkan secara elektronik oleh Sistem HRD & Absensi Yayasan &bull; {{ \Carbon\Carbon::now()->format('d/m/Y H:i:s') }}
    </div>
</body>
</html>
