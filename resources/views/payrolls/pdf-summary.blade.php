<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Penggajian Yayasan - {{ $formattedPeriod }}</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 12mm 12mm 12mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1e293b;
            line-height: 1.35;
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
            font-size: 16pt;
            font-weight: 800;
            color: #4f46e5;
        }
        .header-sub {
            font-size: 7.5pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header-title {
            text-align: right;
            font-size: 12pt;
            font-weight: 700;
            color: #0f172a;
        }
        .header-period {
            text-align: right;
            font-size: 8.5pt;
            color: #4f46e5;
            font-weight: 600;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th {
            background-color: #4f46e5;
            color: #ffffff;
            font-size: 7.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            padding: 6px 6px;
            border: 1px solid #3730a3;
        }
        .data-table td {
            padding: 5px 6px;
            font-size: 8pt;
            border: 1px solid #cbd5e1;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .fw-bold { font-weight: 700; }
        
        .badge-unit {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.5pt;
            font-weight: 700;
            color: #ffffff;
            background-color: #4f46e5;
            border-radius: 2px;
            margin-right: 2px;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            font-size: 8pt;
        }
        .signature-space {
            height: 45px;
        }
        .signature-name {
            font-weight: 700;
            text-decoration: underline;
            color: #0f172a;
        }
        .signature-role {
            font-size: 7pt;
            color: #64748b;
            margin-top: 2px;
        }
        .watermark {
            position: fixed;
            bottom: 3px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 6.5pt;
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
                <div class="header-sub">Laporan Rekapitulasi Beban Penggajian Guru & Honor Mengajar Multi-Jenjang</div>
            </td>
            <td style="width: 45%;">
                <div class="header-title">REKAPITULASI PENGGAJIAN</div>
                <div class="header-period">Periode: {{ strtoupper($formattedPeriod) }} @if($selectedUnit) (Unit: {{ $selectedUnit->name }}) @endif</div>
            </td>
        </tr>
    </table>

    <!-- Data Table -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 25px;" class="text-center">No</th>
                <th style="width: 75px;">NIP / ID</th>
                <th>Nama Guru / Pendidik</th>
                <th style="width: 90px;">Jenjang Unit</th>
                <th style="width: 50px;" class="text-center">Hadir</th>
                <th style="width: 60px;" class="text-center">Total Jam</th>
                <th style="width: 85px;" class="text-right">Honor Kotor (Rp)</th>
                <th style="width: 75px;" class="text-right">Tunjangan (Rp)</th>
                <th style="width: 75px;" class="text-right">Potongan (Rp)</th>
                <th style="width: 90px;" class="text-right">Take Home Pay (Rp)</th>
                <th style="width: 60px;" class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totHours = 0;
                $totGross = 0;
                $totAllow = 0;
                $totDeduct = 0;
                $totNet = 0;
            @endphp
            @forelse($payrolls as $idx => $p)
                @php
                    $totHours += (float)$p->total_hours_taught;
                    $totGross += (float)$p->gross_teaching_amount;
                    $totAllow += (float)$p->total_allowances;
                    $totDeduct += (float)$p->total_deductions;
                    $totNet += (float)$p->net_salary;
                @endphp
                <tr style="{{ $idx % 2 == 1 ? 'background-color: #f8fafc;' : '' }}">
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td>{{ $p->user->nip ?: '-' }}</td>
                    <td><strong>{{ $p->user->name }}</strong></td>
                    <td>
                        @foreach($p->user->units as $u)
                            <span class="badge-unit" style="background-color: {{ $u->color ?: '#4f46e5' }};">{{ $u->code }}</span>
                        @endforeach
                    </td>
                    <td class="text-center">{{ $p->total_present_days }} hr</td>
                    <td class="text-center fw-bold">{{ number_format($p->total_hours_taught, 1, ',', '.') }}</td>
                    <td class="text-right">{{ number_format($p->gross_teaching_amount, 0, ',', '.') }}</td>
                    <td class="text-right text-success" style="color: #059669;">{{ number_format($p->total_allowances, 0, ',', '.') }}</td>
                    <td class="text-right text-danger" style="color: #dc2626;">{{ number_format($p->total_deductions, 0, ',', '.') }}</td>
                    <td class="text-right fw-bold" style="color: #047857;">{{ number_format($p->net_salary, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span style="font-size: 7pt; font-weight: 700; color: {{ $p->status === 'paid' ? '#059669' : ($p->status === 'locked' ? '#2563eb' : '#d97706') }}; text-transform: uppercase;">
                            {{ $p->status }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="11" class="text-center" style="padding: 15px; color: #94a3b8;">
                        Tidak ada data penggajian pada periode ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #e0e7ff; font-weight: 700; color: #1e1b4b;">
                <td colspan="4" class="text-right">TOTAL KESELURUHAN ({{ count($payrolls) }} GURU):</td>
                <td class="text-center">-</td>
                <td class="text-center">{{ number_format($totHours, 1, ',', '.') }}</td>
                <td class="text-right">{{ number_format($totGross, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #059669;">{{ number_format($totAllow, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #dc2626;">{{ number_format($totDeduct, 0, ',', '.') }}</td>
                <td class="text-right" style="color: #047857; font-size: 9pt;">Rp {{ number_format($totNet, 0, ',', '.') }}</td>
                <td class="text-center">-</td>
            </tr>
        </tfoot>
    </table>

    <!-- Tanda Tangan -->
    <table class="signatures-table">
        <tr>
            <td>
                <div>Dibuat oleh,</div>
                <div style="font-weight: 700;">Admin HRD / Tata Usaha</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
            </td>
            <td>
                <div>Diperiksa oleh,</div>
                <div style="font-weight: 700;">Kepala Urusan Keuangan</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
            </td>
            <td>
                <div>Menyetujui,</div>
                <div style="font-weight: 700;">Ketua Yayasan Pendidikan</div>
                <div class="signature-space"></div>
                <div class="signature-name">( ........................................ )</div>
            </td>
        </tr>
    </table>

    <div class="watermark">
        Dicetak dari Sistem HRD & Absensi Yayasan pada {{ \Carbon\Carbon::now()->translatedFormat('d F Y H:i') }} WIB
    </div>
</body>
</html>
