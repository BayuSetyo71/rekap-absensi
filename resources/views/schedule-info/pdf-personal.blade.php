<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Mengajar - {{ $teacher->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 11pt;
            color: #1f2937;
            line-height: 1.4;
            margin: 0;
            padding: 0;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-logo {
            font-size: 22pt;
            font-weight: 800;
            color: #4f46e5;
            letter-spacing: -0.5px;
        }
        .header-sub {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-top: 2px;
        }
        .header-title {
            text-align: right;
            font-size: 14pt;
            font-weight: 700;
            color: #0f172a;
        }
        .header-date {
            text-align: right;
            font-size: 8.5pt;
            color: #64748b;
            margin-top: 3px;
        }
        .profile-card {
            width: 100%;
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            margin-bottom: 15px;
            padding: 10px 14px;
        }
        .profile-table {
            width: 100%;
            border-collapse: collapse;
        }
        .profile-table td {
            padding: 3px 6px;
            font-size: 9.5pt;
            vertical-align: top;
        }
        .profile-label {
            color: #64748b;
            font-weight: 600;
            width: 110px;
        }
        .profile-value {
            color: #0f172a;
            font-weight: 700;
        }
        .badge-unit {
            display: inline-block;
            padding: 2px 7px;
            font-size: 8pt;
            font-weight: 700;
            color: #ffffff;
            background-color: #4f46e5;
            border-radius: 4px;
            margin-right: 4px;
        }
        .section-title {
            font-size: 11pt;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 8px;
            border-left: 3px solid #4f46e5;
            padding-left: 8px;
        }
        .day-container {
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .day-header {
            background-color: #f1f5f9;
            border: 1px solid #cbd5e1;
            padding: 5px 10px;
            font-weight: 700;
            font-size: 9.5pt;
            color: #1e293b;
            border-radius: 4px 4px 0 0;
        }
        .day-header-off {
            background-color: #fef2f2;
            border-color: #fecaca;
            color: #991b1b;
        }
        .schedule-table {
            width: 100%;
            border-collapse: collapse;
            border: 1px solid #cbd5e1;
            border-top: none;
            margin-bottom: 8px;
        }
        .schedule-table th {
            background-color: #f8fafc;
            color: #475569;
            font-size: 8.5pt;
            font-weight: 700;
            text-align: left;
            padding: 5px 8px;
            border-bottom: 1px solid #cbd5e1;
            border-right: 1px solid #e2e8f0;
        }
        .schedule-table td {
            padding: 5px 8px;
            font-size: 9pt;
            border-bottom: 1px solid #f1f5f9;
            border-right: 1px solid #f1f5f9;
            color: #1e293b;
        }
        .schedule-table tr:last-child td {
            border-bottom: none;
        }
        .summary-box {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 20px;
            page-break-inside: avoid;
        }
        .summary-box td {
            background-color: #f8fafc;
            border: 1px solid #cbd5e1;
            padding: 10px;
            text-align: center;
        }
        .summary-num {
            font-size: 14pt;
            font-weight: 800;
            color: #4f46e5;
        }
        .summary-label {
            font-size: 8pt;
            color: #64748b;
            text-transform: uppercase;
            font-weight: 600;
            margin-top: 2px;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: top;
            font-size: 9.5pt;
        }
        .sign-space {
            height: 55px;
        }
        .footer-note {
            text-align: center;
            font-size: 8pt;
            color: #94a3b8;
            border-top: 1px dashed #cbd5e1;
            padding-top: 8px;
            margin-top: 20px;
        }
    </style>
</head>
<body>

    <!-- KOP HEADER -->
    <table class="header-table">
        <tr>
            <td style="width: 55%;">
                <div class="header-logo">SISTEM ABSENSI</div>
                <div class="header-sub">YAYASAN PENDIDIKAN • TK, SD, SMP, SMA</div>
            </td>
            <td style="width: 45%; text-align: right;">
                <div class="header-title">JADWAL MENGAJAR GURU</div>
                <div class="header-date">Dicetak pada: {{ date('d F Y, H:i') }} WIB</div>
            </td>
        </tr>
    </table>

    <!-- PROFIL GURU -->
    <div class="profile-card">
        <table class="profile-table">
            <tr>
                <td class="profile-label">Nama Pegawai</td>
                <td style="width: 10px;">:</td>
                <td class="profile-value" style="font-size: 11pt; color: #4f46e5;">{{ $teacher->name }}</td>
                <td class="profile-label">NIP / ID</td>
                <td style="width: 10px;">:</td>
                <td class="profile-value">{{ $teacher->nip ?: '-' }}</td>
            </tr>
            <tr>
                <td class="profile-label">Jabatan</td>
                <td style="width: 10px;">:</td>
                <td class="profile-value">{{ $teacher->position ?: 'Tenaga Pendidik / Guru' }}</td>
                <td class="profile-label">Unit Ditugaskan</td>
                <td style="width: 10px;">:</td>
                <td class="profile-value">
                    @forelse($teacher->units as $u)
                        <span class="badge-unit" style="background-color: {{ $u->color }};">{{ $u->name }} ({{ $u->code }})</span>
                    @empty
                        <span style="color: #94a3b8; font-weight: normal;">Umum / Belum diset</span>
                    @endforelse
                </td>
            </tr>
        </table>
    </div>

    <!-- RINCIAN JADWAL PER HARI -->
    <div class="section-title">RINCIAN JADWAL SESI MENGAJAR MINGGUAN (SENIN - MINGGU)</div>

    @php
        $dayNames = [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
        $totalSlotsCount = 0;
        $totalMinutesCount = 0;
    @endphp

    @for($d = 1; $d <= 7; $d++)
        @php
            $sch = $teacher->getWorkScheduleForDay($d);
            $dayName = $dayNames[$d];
            $slots = $sch->slots ?? collect();
            $isOff = $sch->is_day_off || (empty($sch->time_in) && $slots->isEmpty());
        @endphp

        <div class="day-container">
            <div class="day-header {{ $isOff ? 'day-header-off' : '' }}">
                <table style="width: 100%; border-collapse: collapse;">
                    <tr>
                        <td style="font-weight: 700;">HARI {{ strtoupper($dayName) }}</td>
                        <td style="text-align: right; font-weight: 600; font-size: 8.5pt;">
                            @if($isOff)
                                <span style="color: #dc2626;">LIBUR / TIDAK ADA JADWAL</span>
                            @else
                                <span style="color: #059669;">
                                    Jam Masuk Awal: <strong>{{ substr($sch->time_in, 0, 5) }}</strong> • Jam Pulang Akhir: <strong>{{ substr($sch->time_out, 0, 5) }}</strong>
                                </span>
                            @endif
                        </td>
                    </tr>
                </table>
            </div>

            @if(!$isOff && $slots->isNotEmpty())
                <table class="schedule-table">
                    <thead>
                        <tr>
                            <th style="width: 30px; text-align: center;">No</th>
                            <th style="width: 110px;">Jam Pelajaran</th>
                            <th style="width: 120px;">Jenjang / Unit</th>
                            <th>Mata Pelajaran / Kelas</th>
                            <th style="width: 80px; text-align: center;">Durasi</th>
                            <th style="width: 90px;">Ruang/Catatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($slots as $sIdx => $slot)
                            @php
                                $totalSlotsCount++;
                                $totalMinutesCount += $slot->duration_minutes;
                            @endphp
                            <tr>
                                <td style="text-align: center; font-weight: 700; color: #64748b;">{{ $sIdx + 1 }}</td>
                                <td style="font-weight: 700; color: #0f172a;">
                                    {{ $slot->formatted_start_time }} - {{ $slot->formatted_end_time }}
                                </td>
                                <td>
                                    <span style="font-weight: 700; color: {{ $slot->unit ? $slot->unit->color : '#4f46e5' }};">
                                        {{ $slot->unit ? $slot->unit->name : '-' }}
                                    </span>
                                </td>
                                <td style="font-weight: 600;">
                                    {{ $slot->subject ?: 'Kegiatan Belajar Mengajar' }}
                                </td>
                                <td style="text-align: center; color: #475569;">
                                    {{ $slot->duration_minutes }} Menit
                                </td>
                                <td style="color: #64748b; font-size: 8pt;">
                                    {{ $slot->notes ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @elseif(!$isOff && !empty($sch->time_in))
                <table class="schedule-table">
                    <tbody>
                        <tr>
                            <td style="padding: 8px 12px; color: #475569; font-style: italic;">
                                Jam Kerja Reguler: <strong>{{ substr($sch->time_in, 0, 5) }} - {{ substr($sch->time_out, 0, 5) }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        </div>
    @endfor

    <!-- RINGKASAN TOTAL BEBAN -->
    <table class="summary-box">
        <tr>
            <td style="width: 33%;">
                <div class="summary-num">{{ $totalSlotsCount }}</div>
                <div class="summary-label">Total Sesi / Minggu</div>
            </td>
            <td style="width: 33%;">
                <div class="summary-num" style="color: #059669;">{{ round($totalMinutesCount / 60, 1) }} Jam</div>
                <div class="summary-label">Total Beban Mengajar</div>
            </td>
            <td style="width: 34%;">
                <div class="summary-num" style="color: #2563eb;">{{ $teacher->units->count() }} Jenjang</div>
                <div class="summary-label">Unit yang Diampu</div>
            </td>
        </tr>
    </table>

    <!-- TANDA TANGAN -->
    <table class="signature-table">
        <tr>
            <td>
                Mengetahui,<br>
                <strong>Kepala Yayasan / HRD</strong>
                <div class="sign-space"></div>
                <strong>( ________________________ )</strong>
            </td>
            <td>
                Guru / Pegawai Bersangkutan,<br>
                <strong>Tenaga Pendidik</strong>
                <div class="sign-space"></div>
                <strong>( {{ $teacher->name }} )</strong>
            </td>
        </tr>
    </table>

    <div class="footer-note">
        Dokumen ini dibuat otomatis oleh Sistem Absensi & Informasi Jadwal Mengajar Yayasan • Harap hadir tepat waktu sesuai jadwal yang telah ditentukan.
    </div>

</body>
</html>
