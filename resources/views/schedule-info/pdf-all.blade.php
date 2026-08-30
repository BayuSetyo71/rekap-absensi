<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Jadwal Mengajar Guru Yayasan</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 10mm 12mm 10mm 12mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 8.5pt;
            color: #1f2937;
            line-height: 1.3;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
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
            font-size: 12pt;
            font-weight: 700;
            color: #0f172a;
        }
        .header-date {
            text-align: right;
            font-size: 7.5pt;
            color: #64748b;
            margin-top: 2px;
        }
        .table-schedule {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .table-schedule th {
            background-color: #0f172a;
            color: #ffffff;
            font-size: 8pt;
            font-weight: 700;
            text-align: center;
            padding: 5px 4px;
            border: 1px solid #334155;
        }
        .table-schedule td {
            padding: 4px 5px;
            font-size: 7.8pt;
            border: 1px solid #cbd5e1;
            vertical-align: top;
        }
        .table-schedule tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .badge-unit {
            display: inline-block;
            padding: 1px 4px;
            font-size: 6.8pt;
            font-weight: 700;
            color: #ffffff;
            border-radius: 3px;
            margin-right: 2px;
            margin-bottom: 2px;
        }
        .slot-item {
            font-size: 7.2pt;
            padding: 1.5px 3px;
            margin-bottom: 2px;
            border-radius: 2px;
            background-color: #ffffff;
            border: 1px solid #e2e8f0;
            line-height: 1.15;
        }
        .slot-unit {
            font-weight: 700;
        }
        .slot-time {
            font-weight: 600;
            color: #0f172a;
        }
        .slot-subject {
            color: #475569;
            font-style: italic;
        }
        .off-day {
            color: #94a3b8;
            font-style: italic;
            text-align: center;
            font-size: 7pt;
        }
        .footer-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            page-break-inside: avoid;
        }
        .footer-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 8.5pt;
        }
        .sign-space {
            height: 45px;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 50%;">
                <div class="header-logo">SISTEM ABSENSI</div>
                <div class="header-sub">YAYASAN PENDIDIKAN • TK, SD, SMP, SMA</div>
            </td>
            <td style="width: 50%; text-align: right;">
                <div class="header-title">LAPORAN JADWAL MENGAJAR GURU YAYASAN</div>
                <div class="header-date">Dicetak pada: {{ date('d F Y, H:i') }} WIB • Total {{ $teachers->count() }} Guru</div>
            </td>
        </tr>
    </table>

    <!-- TABEL JADWAL MINGGUAN -->
    <table class="table-schedule">
        <thead>
            <tr>
                <th style="width: 22px;">No</th>
                <th style="width: 140px; text-align: left;">Nama Guru & NIP</th>
                <th style="width: 75px;">Unit Diampu</th>
                <th style="width: 85px;">Senin</th>
                <th style="width: 85px;">Selasa</th>
                <th style="width: 85px;">Rabu</th>
                <th style="width: 85px;">Kamis</th>
                <th style="width: 85px;">Jumat</th>
                <th style="width: 60px;">Sabtu</th>
                <th style="width: 55px;">Minggu</th>
                <th style="width: 45px;">Sesi</th>
                <th style="width: 50px;">Beban</th>
            </tr>
        </thead>
        <tbody>
            @forelse($teachers as $idx => $t)
                @php
                    $isConfigured = $t->hasConfiguredSchedule();
                    $totalSlots = $t->teachingSlots->count();
                    $totalMins = $t->teachingSlots->sum(fn($s) => $s->duration_minutes);
                    $totalHours = round($totalMins / 60, 1);
                @endphp
                <tr>
                    <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $idx + 1 }}</td>
                    <td>
                        <strong style="color: #0f172a; font-size: 8.2pt;">{{ $t->name }}</strong>
                        <div style="color: #64748b; font-size: 7pt;">{{ $t->position ?: 'Guru' }} @if($t->nip) • NIP: {{ $t->nip }} @endif</div>
                    </td>
                    <td style="text-align: center;">
                        @forelse($t->units as $u)
                            <span class="badge-unit" style="background-color: {{ $u->color }};">{{ $u->code }}</span>
                        @empty
                            <span style="color: #94a3b8; font-size: 7pt;">-</span>
                        @endforelse
                    </td>

                    @for($d = 1; $d <= 7; $d++)
                        @php $daySch = $t->getWorkScheduleForDay($d); @endphp
                        <td>
                            @if(!$isConfigured)
                                <div class="off-day">-</div>
                            @elseif($daySch->is_day_off || (empty($daySch->time_in) && (!$daySch->slots || $daySch->slots->isEmpty())))
                                <div class="off-day">Libur</div>
                            @elseif($daySch->slots && $daySch->slots->isNotEmpty())
                                @foreach($daySch->slots as $s)
                                    <div class="slot-item" style="border-left: 2px solid {{ $s->unit ? $s->unit->color : '#4f46e5' }};">
                                        <span class="slot-unit" style="color: {{ $s->unit ? $s->unit->color : '#4f46e5' }};">{{ $s->unit ? $s->unit->code : '' }}</span>
                                        <span class="slot-time">{{ $s->formatted_start_time }}-{{ $s->formatted_end_time }}</span>
                                        @if($s->subject)
                                            <div class="slot-subject">{{ $s->subject }}</div>
                                        @endif
                                    </div>
                                @endforeach
                            @elseif($daySch->time_in && $daySch->time_out)
                                <div class="slot-item" style="text-align: center;">
                                    <span class="slot-time">{{ substr($daySch->time_in, 0, 5) }} - {{ substr($daySch->time_out, 0, 5) }}</span>
                                </div>
                            @else
                                <div class="off-day">Libur</div>
                            @endif
                        </td>
                    @endfor

                    <td style="text-align: center; font-weight: 700; color: #475569;">
                        {{ $totalSlots }}
                    </td>
                    <td style="text-align: center; font-weight: 700; color: #4f46e5;">
                        {{ $totalHours }} J
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" style="text-align: center; padding: 20px; color: #94a3b8;">
                        Tidak ada data jadwal guru.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- TANDA TANGAN -->
    <table class="footer-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Ketua Yayasan</strong>
                <div class="sign-space"></div>
                <strong>( ________________________ )</strong>
            </td>
            <td>
                Ditetapkan di Jakarta, {{ date('d F Y') }}<br>
                <strong>Kepala Bagian SDM / HRD</strong>
                <div class="sign-space"></div>
                <strong>( ________________________ )</strong>
            </td>
        </tr>
    </table>

</body>
</html>
