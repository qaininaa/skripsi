@php $hd = $report->header_data ?? []; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $report->reportType->annex_number }} — {{ $report->tanggal->format('Y-m-d') }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Verdana,Geneva,sans-serif;font-size:10pt;color:#000;background:#d1d5db}

/* ── Pages ─────────────────────────────── */
.doc-page{background:#fff;margin:1rem auto 2rem;box-shadow:0 2px 14px rgba(0,0,0,.2);position:relative;padding-bottom:18mm}
.doc-page.portrait{width:210mm;min-height:297mm;padding:12mm 15mm 18mm}
.doc-page.landscape{width:297mm;min-height:210mm;padding:10mm 10mm 18mm}
.doc-page+.doc-page{page-break-before:always}

/* ── Tables ────────────────────────────── */
table.dt{border-collapse:collapse;width:100%;table-layout:fixed}
table.dt th,table.dt td{border:1px solid #000;padding:2px 2px;vertical-align:middle;overflow:visible;white-space:normal;word-wrap:break-word;word-break:break-word}
table.dt th{font-size:7pt;font-weight:700;text-align:center}
table.dt td{font-size:8pt}
.tc{text-align:center}.tl{text-align:left}.fw{font-weight:700}
table.dt-auto{table-layout:auto}
table.dt-auto th,table.dt-auto td{padding:20px 8px;white-space:normal;word-wrap:break-word}

/* ── Vertical headers ──────────────────── */
.vt{writing-mode:vertical-rl;transform:rotate(180deg);white-space:nowrap;padding:6px 2px !important;font-size:7pt;font-weight:700;text-align:center}

/* ── Page header ───────────────────────── */
.pg-hdr .doc-num{text-align:right;font-size:8pt;line-height:1.4}
.pg-hdr .doc-title{text-align:center;font-weight:700;font-size:9pt;text-decoration:none;margin-top:3px;margin-bottom:0;padding-bottom:6px}
.pg-hdr .doc-title-line{border:none;border-top:2.5px solid #000;margin:0 0 8px}

/* ── Section headers (borderless) ──────── */
.sec-hdr{font-weight:700;font-size:10pt;border:none !important;border-bottom:1px solid #000 !important;padding:8px 0 4px !important}

/* ── Signature table ───────────────────── */
.sig-tbl td{font-size:8pt;text-align:center;vertical-align:top;padding:3px 6px}

/* ── Page footer ───────────────────────── */
.pg-footer{position:absolute;bottom:8mm;right:15mm;font-size:8pt;font-style:italic}

/* ── Print ─────────────────────────────── */
@media print{
    *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
    body{background:#fff!important}
    .doc-page{box-shadow:none!important;margin:0!important;width:100%!important;min-height:auto!important}
    .no-print{display:none!important}
    .doc-page.portrait{page:portrait}
    .doc-page.landscape{page:landscape}
    thead{display:table-header-group}
}
@page{size:A4;margin:10mm 15mm}
@page portrait{size:A4 portrait;margin:10mm 15mm}
@page landscape{size:A4 landscape;margin:8mm 10mm}
</style>
</head>
<body>

{{-- ── Print Toolbar (screen only) ──────────────────── --}}
<div class="no-print" style="background:#fff;border-bottom:1px solid #ddd;padding:8px 20px;display:flex;align-items:center;justify-content:space-between;position:sticky;top:0;z-index:10">
    <div style="display:flex;align-items:center;gap:10px">
        <a href="{{ route('supervisor.laporan.show', $report) }}"
           style="font-size:12px;color:#555;text-decoration:none;display:flex;align-items:center;gap:4px">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <span style="color:#ccc">|</span>
        <span style="font-size:12px;font-weight:700">{{ $report->reportType->annex_number }} — {{ $report->reportType->name }}</span>
    </div>
    <button onclick="window.print()"
            style="background:#222;color:#fff;border:none;border-radius:6px;padding:7px 18px;font-size:12px;font-weight:600;cursor:pointer;font-family:Verdana">
        Cetak / Download PDF
    </button>
</div>

{{-- ══════════════════════════════════════════════════════
     PAGE 1 (Portrait): Sections 1 – 3
     ══════════════════════════════════════════════════════ --}}
<div class="doc-page portrait">
    {{-- Page header --}}
    <div class="pg-hdr">
        <div class="doc-num">{{ $report->reportType->annex_number }}</div>
        <div class="doc-title">{{ strtoupper($report->reportType->name) }}</div>
        <hr class="doc-title-line">
    </div>

    {{-- ── 1. Pemantauan Ruang ──────────────────────── --}}
    <table class="dt dt-auto" style="margin-bottom:8px">
        <tr><td colspan="2" class="sec-hdr">1. Pemantauan Ruang</td></tr>
        <tr><td style="width:45%">Tanggal Pemantauan Ruang</td><td>{{ $report->tanggal->isoFormat('D MMMM Y') }}</td></tr>
        <tr><td>Nama Analis</td><td>{{ $report->shift1Analis->name }}{{ $report->shift2Analis ? ' / ' . $report->shift2Analis->name : '' }}</td></tr>
        <tr><td>Nama Produk</td><td>{{ $report->nama_produk }}</td></tr>
        <tr><td>Nomor Batch Produk</td><td>{{ $report->nomor_batch_produk ?: '' }}</td></tr>
    </table>

    {{-- ── 2. Identitas Instrumen ───────────────────── --}}
    @if ($needsAirSampler)
    @php $as = $hd['air_sampler'] ?? []; @endphp
    <table class="dt dt-auto" style="margin-bottom:8px">
        <tr><td colspan="2" class="sec-hdr">2. Identitas Instrumen</td></tr>
        <tr><td style="width:45%">Nama Alat</td><td class="fw">Air Sampler</td></tr>
        <tr><td>No. ID Air Sampler</td><td>{{ $as['no_id'] ?? '' }}</td></tr>
        <tr><td>Tanggal Kalibrasi Air Sampler</td><td>{{ $as['tanggal_kalibrasi'] ?? '' }}</td></tr>
        <tr><td>Tanggal Due Date Kalibrasi Air Sampler</td><td>{{ $as['tanggal_due_date'] ?? '' }}</td></tr>
    </table>
    @endif

    {{-- ── 3. Identitas Medium ──────────────────────── --}}
    @php $mediumGroups = $report->reportType->medium_groups ?? []; @endphp
    @if (!empty($mediumGroups))
    <table class="dt dt-auto" style="margin-bottom:8px">
        <tr><td colspan="2" class="sec-hdr">3. Identitas Medium</td></tr>
        @foreach ($mediumGroups as $medKey => $medLabel)
        @php $med = $hd[$medKey] ?? []; @endphp
        <tr><td style="width:45%">Nomor Batch {{ $medLabel }}</td><td>{{ $med['nomor_batch'] ?? '' }}</td></tr>
        @if ($medKey !== 'medium_swab')
        <tr><td>Nomor GPT {{ $medLabel }}</td><td>{{ $med['nomor_gpt'] ?? '' }}</td></tr>
        @endif
        <tr><td>Tanggal ED {{ $medLabel }}</td><td>{{ $med['tanggal_ed'] ?? '' }}</td></tr>
        @if (!$loop->last)
        <tr><td colspan="2" style="border-left:1px solid #000;border-right:1px solid #000;padding:5px"></td></tr>
        @endif
        @endforeach
    </table>
    @endif
    <div class="pg-footer"></div>
</div>

{{-- ══════════════════════════════════════════════════════
     PAGE 2 (Portrait): Section 4 – Proses Inkubasi
     ══════════════════════════════════════════════════════ --}}
@if ($needsInkubator)
<div class="doc-page portrait">
    <div class="pg-hdr">
        <div class="doc-num">{{ $report->reportType->annex_number }}</div>
        <div class="doc-title">{{ strtoupper($report->reportType->name) }}</div>
        <hr class="doc-title-line">
    </div>

    <table class="dt dt-auto">
        <tr><td colspan="4" class="sec-hdr">4. Proses Inkubasi Medium Monitoring</td></tr>
        @foreach ([
            'inkubator_20_25' => ['label' => 'Inkubator Suhu 20–25°C', 'min_days' => 3],
            'inkubator_30_35' => ['label' => 'Inkubator Suhu 30–35°C', 'min_days' => 2],
        ] as $inkKey => $inkInfo)
        @php $ink = $hd[$inkKey] ?? []; @endphp
        <tr><td style="width:35%">Nama Alat</td><td colspan="3" class="fw">{{ $inkInfo['label'] }}</td></tr>
        <tr><td>No. ID Inkubator</td><td colspan="3">{{ $ink['no_id'] ?? '' }}</td></tr>
        <tr><td>Tanggal Kalibrasi Inkubator</td><td colspan="3">{{ $ink['tanggal_kalibrasi'] ?? '' }}</td></tr>
        <tr><td>Tanggal Due Date Kalibrasi Inkubator</td><td colspan="3">{{ $ink['tanggal_due_date'] ?? '' }}</td></tr>
        {{-- Medium Monitoring --}}
        <tr>
            <td rowspan="8" style="vertical-align:middle">Tanggal Inkubasi Medium (min {{ $inkInfo['min_days'] }} hari)</td>
            <td rowspan="4" class="tc" style="vertical-align:middle;width:14%">Medium<br>Monitoring</td>
            <td>Tanggal Masuk Inkubator: {{ $ink['tanggal_masuk'] ?? '' }}</td>
            <td style="width:14%">Jam: {{ $ink['jam_masuk'] ?? '' }}</td>
        </tr>
        <tr><td colspan="2">Diinkubasi oleh (paraf, inisial, tanggal): {{ $ink['diinkubasi_oleh'] ?? '' }}{{ isset($ink['diinkubasi_tanggal']) ? ', ' . $ink['diinkubasi_tanggal'] : '' }}</td></tr>
        <tr><td>Tanggal Keluar Inkubator: {{ $ink['tanggal_keluar'] ?? '' }}</td><td>Jam: {{ $ink['jam_keluar'] ?? '' }}</td></tr>
        <tr><td colspan="2">Dikeluarkan oleh (paraf, inisial, tanggal): {{ $ink['dikeluarkan_oleh'] ?? '' }}{{ isset($ink['dikeluarkan_tanggal']) ? ', ' . $ink['dikeluarkan_tanggal'] : '' }}</td></tr>
        {{-- Swab --}}
        <tr>
            <td rowspan="4" class="tc" style="vertical-align:middle">Swab</td>
            <td>Tanggal Masuk Inkubator: {{ $ink['tanggal_masuk'] ?? '' }}</td>
            <td>Jam: {{ $ink['jam_masuk'] ?? '' }}</td>
        </tr>
        <tr><td colspan="2">Diinkubasi oleh (paraf, inisial, tanggal): {{ $ink['diinkubasi_oleh'] ?? '' }}{{ isset($ink['diinkubasi_tanggal']) ? ', ' . $ink['diinkubasi_tanggal'] : '' }}</td></tr>
        <tr><td>Tanggal Keluar Inkubator: {{ $ink['tanggal_keluar'] ?? '' }}</td><td>Jam: {{ $ink['jam_keluar'] ?? '' }}</td></tr>
        <tr><td colspan="2">Dikeluarkan oleh (paraf, inisial, tanggal): {{ $ink['dikeluarkan_oleh'] ?? '' }}{{ isset($ink['dikeluarkan_tanggal']) ? ', ' . $ink['dikeluarkan_tanggal'] : '' }}</td></tr>
        @if (!$loop->last)
        <tr><td colspan="4" style="border:none;padding:5px"></td></tr>
        @endif
        @endforeach
    </table>
    <div class="pg-footer"></div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     LANDSCAPE PAGES: Measurement sections
     ══════════════════════════════════════════════════════ --}}
@foreach ($report->reportType->sections as $section)
@php
    $isShiftBased  = in_array($section->measurement_type, ['air_sampler', 'contact_plate', 'swab']);
    $isSettlePlate = $section->measurement_type === 'settle_plate';
    $isSwab        = $section->measurement_type === 'swab';
    $hasJam        = $section->measurement_type === 'air_sampler';
    $maxCols       = $section->max_exposures;
    $romanNums     = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    $savedAsgn     = ($hd['shift_assignments'] ?? [])[$section->id] ?? [];
    $secAssignments = [];
    for ($c = 1; $c <= $maxCols; $c++) {
        $secAssignments[$c] = isset($savedAsgn[$c]) ? (int)$savedAsgn[$c] : 1;
    }
    $secNote  = $hd['section_notes'][$section->id] ?? [];
    $totalCols = 5 + (!$isShiftBased ? 3 : 0) + $maxCols * ($hasJam ? 4 : 3) + 4 + 1;
@endphp
<div class="doc-page landscape">
    {{-- Page header --}}
    <div class="pg-hdr">
        <div class="doc-num">{{ $report->reportType->annex_number }}</div>
        <div class="doc-title">{{ strtoupper($report->reportType->name) }}</div>
        <hr class="doc-title-line">
    </div>

    {{-- Unit title --}}
    <div style="text-align:center;font-weight:700;font-size:8pt;margin-bottom:3px">{{ $section->measurement_unit }}</div>

    {{-- ── Data table ───────────────────────────────── --}}
    <table class="dt">
        <thead>
            {{-- Row 1: top-level group headers --}}
            <tr>
                <th class="vt" rowspan="3" style="width:18px">S. No.</th>
                <th class="vt" rowspan="3" style="width:80px">Room Name</th>
                <th class="vt" rowspan="3" style="width:25px">Class</th>
                <th class="vt" rowspan="3">Room Number</th>
                <th class="vt" rowspan="3">Location Number</th>
                <th colspan="{{ (!$isShiftBased ? 3 : 0) + $maxCols * ($hasJam ? 4 : 3) }}">
                    {{ $section->measurement_unit }}
                </th>
                <th colspan="2" rowspan="2">Alert<br>Limit</th>
                <th colspan="2" rowspan="2">Action<br>Limit</th>
                <th rowspan="3" style="width:46px">Kesim-<br>pulan</th>
            </tr>

            {{-- Row 2: Machine set-up / Exposure / Shift labels --}}
            <tr>
                {{-- SETTLE PLATE: Machine set-up + Exposures --}}
                @if (!$isShiftBased)
                @php
                    $msJamMulai = null; $msJamSelesai = null;
                    foreach ($section->locations as $loc2) {
                        $e0 = $entryMap[$loc2->id][0][1] ?? $entryMap[$loc2->id][0][2] ?? null;
                        if ($e0 && ($e0->jam_mulai || $e0->jam_selesai)) {
                            $msJamMulai = $e0->jam_mulai; $msJamSelesai = $e0->jam_selesai; break;
                        }
                    }
                @endphp
                <th colspan="3" style="font-size:7pt;line-height:1.35;vertical-align:top;padding:3px 2px">
                    <div style="font-weight:700">Machine set-up</div>
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>
                        Mulai Sebar Petri:<br>{{ $msJamMulai ?: '__:__' }}<br><br>
                        Selesai<br>Pemantauan:<br>{{ $msJamSelesai ?: '__:__' }}
                    </div>
                </th>
                @endif

                @for ($col = 1; $col <= $maxCols; $col++)
                @php $colAsgn = $secAssignments[$col] ?? 1; @endphp

                @if ($isShiftBased)
                {{-- AIR SAMPLER / CONTACT PLATE / SWAB --}}
                <th colspan="{{ $hasJam ? 4 : 3 }}" style="font-size:7pt;line-height:1.3;vertical-align:top;padding:3px 2px">
                    <div style="font-weight:700">Shift (S{{ $colAsgn }})</div>
                    @if ($hasJam)
                    <div style="font-weight:400;margin-top:1px">Pemantauan {{ $romanNums[$col - 1] ?? $col }}</div>
                    @endif
                    @if ($isSwab)
                    @php $swabColTimes = $hd['swab_times'][$section->id][$col] ?? []; @endphp
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>Mulai Swab:<br>
                        @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                        @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                        {{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: '__:__' }}<br>
                        @endforeach
                        <br>Selesai<br>Pemantauan:<br>
                        @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                        @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                        {{ $swabLabel }}: {{ ($st['selesai'] ?? '') ?: '__:__' }}<br>
                        @endforeach
                    </div>
                    @elseif (!$hasJam)
                    {{-- Contact plate --}}
                    @php
                        $cpJamMulai = null; $cpJamSelesai = null;
                        foreach ($section->locations as $loc2) {
                            $e2 = $entryMap[$loc2->id][1][$colAsgn] ?? null;
                            if ($e2 && ($e2->jam_mulai || $e2->jam_selesai)) {
                                $cpJamMulai = $e2->jam_mulai; $cpJamSelesai = $e2->jam_selesai; break;
                            }
                        }
                    @endphp
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>Mulai Contact Plate:<br>{{ $cpJamMulai ?: '__:__' }}<br><br>
                        Selesai Pemantauan:<br>{{ $cpJamSelesai ?: '__:__' }}
                    </div>
                    @endif
                </th>

                @else
                {{-- SETTLE PLATE exposures --}}
                @php
                    if ($isSettlePlate) {
                        $stA = $hd['settle_times'][$section->id][$col]['a'] ?? [];
                        $stB = $hd['settle_times'][$section->id][$col]['b'] ?? [];
                    } else {
                        $expJamMulai = null; $expJamSelesai = null;
                        foreach ($section->locations as $loc2) {
                            $e2 = $entryMap[$loc2->id][$col][1] ?? $entryMap[$loc2->id][$col][2] ?? null;
                            if ($e2 && ($e2->jam_mulai || $e2->jam_selesai)) {
                                $expJamMulai = $e2->jam_mulai; $expJamSelesai = $e2->jam_selesai; break;
                            }
                        }
                    }
                @endphp
                <th colspan="3" style="font-size:7pt;line-height:1.35;vertical-align:top;padding:3px 2px">
                    <div style="font-weight:700">Exposure {{ $romanNums[$col - 1] ?? $col }}</div>
                    <div style="font-weight:400;font-size:6.5pt">(S{{ $colAsgn }})</div>
                    @if ($isSettlePlate)
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>Mulai Sebar Petri:<br>
                        A: {{ ($stA['jam_mulai'] ?? '') ?: '__:__' }}<br>
                        B: {{ ($stB['jam_mulai'] ?? '') ?: '__:__' }}<br><br>
                        Selesai<br>Pemantauan:<br>
                        A: {{ ($stA['jam_selesai'] ?? '') ?: '__:__' }}<br>
                        B: {{ ($stB['jam_selesai'] ?? '') ?: '__:__' }}
                    </div>
                    @endif
                </th>
                @endif
                @endfor
            </tr>

            {{-- Row 3: B / F / T sub-headers --}}
            <tr>
                @if (!$isShiftBased)
                <th>B</th><th>F</th><th>T</th>
                @endif
                @for ($col = 1; $col <= $maxCols; $col++)
                @if ($isShiftBased && $hasJam)
                <th style="white-space:nowrap">Jam</th>
                @endif
                <th>B</th><th>F</th><th>T</th>
                @endfor
                <th>B</th><th>F</th>
                <th>B</th><th>F</th>
            </tr>

            {{-- Frequency note row --}}
            <tr>
                <td colspan="{{ $totalCols }}" style="border-left:none;border-right:none;font-size:7pt;font-weight:700;padding:3px 0">
                    FREQUENCY : EVERY OPERATIONAL AND DAILY (SETIAP OPERASIONAL DAN HARIAN)
                    @if ($isSettlePlate)
                    <br><span style="font-weight:400;font-style:italic">* settle plate was exposed continuously for maximum every 4 hours (grade B) for aseptic filtration product only.</span>
                    @endif
                </td>
            </tr>
        </thead>

        <tbody>
            @foreach ($section->locations as $loc)
            @php
                $locEntries = collect();
                for ($p = 1; $p <= $section->max_exposures; $p++) {
                    for ($s = 1; $s <= 2; $s++) {
                        if (isset($entryMap[$loc->id][$p][$s])) $locEntries->push($entryMap[$loc->id][$p][$s]);
                    }
                }
                $maxB   = $locEntries->max(fn($e) => $e->cfu_bacteria ?? 0) ?? 0;
                $maxF   = $locEntries->max(fn($e) => $e->cfu_fungi ?? 0) ?? 0;
                $hasTMS = ($loc->action_limit_bacteria && $maxB >= $loc->action_limit_bacteria)
                       || ($loc->action_limit_fungi && $maxF >= $loc->action_limit_fungi);
                $hasAlt = !$hasTMS && (
                            ($loc->alert_limit_bacteria && $maxB >= $loc->alert_limit_bacteria)
                         || ($loc->alert_limit_fungi    && $maxF >= $loc->alert_limit_fungi));
                $konklusi = $locEntries->isEmpty() ? null : ($hasTMS ? 'TMS' : ($hasAlt ? 'Alert' : 'MS'));
            @endphp
            <tr>
                <td class="tc">{{ $loc->s_no }}</td>
                <td class="tl">{{ $loc->room_name }}</td>
                <td class="tc">{{ $loc->class }}</td>
                <td class="tc" style="font-family:monospace;font-size:7.5pt">{{ $loc->room_number }}</td>
                <td class="tc" style="font-family:monospace;font-size:7.5pt;{{ str_starts_with($loc->location_number, '*)') ? 'font-style:italic;' : '' }}">{{ $loc->location_number }}</td>

                {{-- Machine set-up (settle plate only) --}}
                @if (!$isShiftBased)
                @php
                    $msEntry = $entryMap[$loc->id][0][1] ?? $entryMap[$loc->id][0][2] ?? null;
                    $msTVal  = ($msEntry && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null))
                        ? ($msEntry->cfu_bacteria ?? 0) + ($msEntry->cfu_fungi ?? 0) : null;
                @endphp
                <td class="tc">{{ $msEntry?->cfu_bacteria ?? '' }}</td>
                <td class="tc">{{ $msEntry?->cfu_fungi ?? '' }}</td>
                <td class="tc fw">{{ $msTVal ?? '' }}</td>
                @endif

                {{-- Data columns --}}
                @for ($col = 1; $col <= $maxCols; $col++)
                @php
                    $colAsgn    = $secAssignments[$col] ?? 1;
                    $existEntry = $isShiftBased
                        ? ($entryMap[$loc->id][1][$colAsgn] ?? null)
                        : ($entryMap[$loc->id][$col][$colAsgn] ?? null);
                    $tVal = ($existEntry && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null))
                        ? ($existEntry->cfu_bacteria ?? 0) + ($existEntry->cfu_fungi ?? 0) : null;
                @endphp
                @if ($hasJam)
                <td class="tc" style="font-size:7.5pt">{{ $existEntry?->jam_mulai ? \Illuminate\Support\Str::substr($existEntry->jam_mulai, 0, 5) : '' }}</td>
                @endif
                <td class="tc">{{ $existEntry?->cfu_bacteria ?? '' }}</td>
                <td class="tc">{{ $existEntry?->cfu_fungi ?? '' }}</td>
                <td class="tc fw">{{ $tVal ?? '' }}</td>
                @endfor

                {{-- Alert Limit --}}
                <td class="tc">{{ $loc->alert_limit_bacteria !== null ? $loc->alert_limit_bacteria : 'NA' }}</td>
                <td class="tc">{{ $loc->alert_limit_fungi !== null ? $loc->alert_limit_fungi : 'NA' }}</td>
                {{-- Action Limit --}}
                <td class="tc">{{ $loc->action_limit_bacteria !== null ? ($loc->action_limit_bacteria == 1 ? '<1' : $loc->action_limit_bacteria) : 'NA' }}</td>
                <td class="tc">{{ $loc->action_limit_fungi !== null ? ($loc->action_limit_fungi == 1 ? '<1' : $loc->action_limit_fungi) : 'NA' }}</td>
                {{-- Kesimpulan --}}
                <td class="tc fw">
                    @if ($konklusi === 'TMS') TMS
                    @elseif ($konklusi === 'Alert') Alert
                    @elseif ($konklusi === 'MS') MS
                    @else MS / TMS*
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- Keterangan --}}
    <div style="font-size:7.5pt;margin:3px 0">
        @if ($isSwab)
        *) diisi jika dibutuhkan<br>
        @endif
        Keterangan:<br>
        B: Total Bakteri, F: Total Fungi, T: Total Bakteri + Fungi, MS: Memenuhi Spesifikasi, TMS: Tidak Memenuhi Spesifikasi
    </div>

    {{-- CATATAN --}}
    <div style="margin-top:10px">
        @php $secCatatan = $secNote['catatan'] ?? ''; @endphp
        <span class="fw">CATATAN :</span>
        @if ($secCatatan)
        <div style="margin-top:2px">{{ $secCatatan }}</div>
        @else
        <div style="border-bottom:1px dotted #000;height:14px;margin:4px 0"></div>
        <div style="border-bottom:1px dotted #000;height:14px;margin:4px 0"></div>
        <div style="border-bottom:1px dotted #000;height:14px;margin:4px 0"></div>
        @endif
    </div>

    {{-- KESIMPULAN --}}
    @php $secKesp = $secNote['kesimpulan'] ?? ''; @endphp
    <div style="margin-top:8px">
        <span class="fw">KESIMPULAN</span>
        &ensp;: &ensp;
        <span style="{{ $secKesp === 'MS' ? 'font-weight:700;text-decoration:underline;' : '' }}">MEMENUHI SPESIFIKASI</span>
        &ensp;/&ensp;
        <span style="{{ $secKesp === 'TMS' ? 'font-weight:700;text-decoration:underline;' : '' }}">TIDAK MEMENUHI SPESIFIKASI</span>*
    </div>
    <div style="font-size:8pt;margin-top:3px">
        * <u>lingkari</u> sesuai dengan pengamatan
    </div>

    {{-- Tanda Tangan --}}
    <table class="dt sig-tbl" style="margin-top:10px">
        <tr>
            <td style="width:25%;font-weight:700">Dimonitoring oleh:</td>
            <td style="width:25%;font-weight:700">Dibaca oleh:</td>
            <td style="width:25%;font-weight:700">Direview oleh:</td>
            <td style="width:25%;font-weight:700">Disetujui oleh:</td>
        </tr>
        <tr>
            <td style="height:22mm;vertical-align:bottom">{{ $report->shift1Analis->name }}</td>
            <td style="height:22mm;vertical-align:bottom">{{ $report->shift2Analis?->name ?? '' }}</td>
            <td style="height:22mm"></td>
            <td style="height:22mm"></td>
        </tr>
        <tr>
            <td>(Analis Lab. Mikrobiologi)</td>
            <td>(Analis Lab. Mikrobiologi)</td>
            <td>(Staff/ Supervisor Mikrobiologi)</td>
            <td>(QC Manager)</td>
        </tr>
    </table>
    <div class="pg-footer"></div>
</div>
@endforeach

<script>
document.addEventListener('DOMContentLoaded', function(){
    var footers = document.querySelectorAll('.pg-footer');
    var total = footers.length;
    footers.forEach(function(f, i){
        f.textContent = 'Hal ' + (i + 1) + ' dari ' + total;
    });
});
</script>
</body>
</html>
