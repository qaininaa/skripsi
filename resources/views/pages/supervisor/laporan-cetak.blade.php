@php $hd = $report->header_data ?? []; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $report->reportType->annex_number }} — {{ $report->created_at->format('Y-m-d') }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Verdana,Geneva,sans-serif;font-size:10pt;color:#000;background:#d1d5db;overflow-x:hidden}

/* ── Pages ─────────────────────────────── */
.doc-page{background:#fff;margin:1rem auto 2rem;box-shadow:0 2px 14px rgba(0,0,0,.2);position:relative;padding-bottom:18mm}
.doc-page.portrait{width:210mm;min-height:297mm;padding:12.7mm 2.5mm 18mm 2.5mm}
.doc-page.landscape{width:297mm;min-height:210mm;padding:12.7mm 2.5mm 18mm 2.5mm}
.doc-page+.doc-page{page-break-before:always}

/* ── Toolbar ────────────────────────── */
.toolbar{
    background:#fff;
    border-bottom:1px solid #ddd;
    padding:10px 16px;
    display:grid;
    grid-template-columns:1fr auto 1fr;
    align-items:center;
    gap:8px 12px;
    position:sticky;
    top:0;
    z-index:10;
    width:100%;
}

.toolbar-title{
    display:flex;
    align-items:center;
    gap:6px;
    min-width:0;
    overflow:hidden;
}
.toolbar-title a{
    font-size:12px;
    color:#555;
    text-decoration:none;
    display:flex;
    align-items:center;
    gap:4px;
    flex-shrink:0;
    white-space:nowrap;
}
.toolbar-title .sep{
    color:#ccc;
    flex-shrink:0;
}
.toolbar-title .title-text{
    font-size:12px;
    font-weight:700;
    /* Allow wrapping; no truncation */
    white-space:normal;
    word-break:break-word;
    overflow-wrap:anywhere;
    line-height:1.35;
}

.toolbar-zoom{
    display:flex;
    align-items:center;
    gap:4px;
    flex-shrink:0;
    justify-self:center;
}
.toolbar-zoom button{
    background:#f3f4f6;
    border:1px solid #d1d5db;
    border-radius:4px;
    cursor:pointer;
    display:flex;
    align-items:center;
    justify-content:center;
}
.toolbar-zoom .zoom-btn{width:36px;height:36px;font-size:20px}
.toolbar-zoom .zoom-reset{padding:0 10px;height:36px;font-size:12px;color:#555}
.toolbar-zoom .zoom-label{font-size:12px;font-weight:600;min-width:46px;text-align:center;color:#374151}

.toolbar-print{
    background:#222;
    color:#fff;
    border:none;
    border-radius:6px;
    padding:9px 18px;
    font-size:13px;
    font-weight:600;
    cursor:pointer;
    font-family:Verdana;
    flex-shrink:0;
    white-space:nowrap;
}

.toolbar-actions{display:flex;align-items:center;justify-content:flex-end;}

/* ── Mobile ────────────────────────── */
@media(max-width:640px){
    .toolbar{
        grid-template-columns:1fr auto;
        grid-template-rows:auto auto;
        padding:10px 12px;
        gap:8px 10px;
    }
    /* Title spans full width on first row */
    .toolbar-title{
        grid-column:1 / -1;
        grid-row:1;
    }
    .toolbar-title a{font-size:13px}
    .toolbar-title .title-text{font-size:13px}

    /* Zoom on second row left, print on second row right */
    .toolbar-zoom{
        grid-column:1;
        grid-row:2;
        justify-self:start;
    }
    .toolbar-zoom .zoom-btn{width:40px;height:40px;font-size:22px}
    .toolbar-zoom .zoom-reset{height:40px;padding:0 12px;font-size:13px}
    .toolbar-zoom .zoom-label{font-size:13px;min-width:50px}

    .toolbar-print{
        grid-column:2;
        grid-row:2;
        justify-self:end;
        padding:10px 14px;
        font-size:13px;
    }
}

/* ── Tables ────────────────────────────── */
table.dt{border-collapse:collapse;width:100%;table-layout:fixed}
table.dt th,table.dt td{border:1px solid #000;padding:2px 2px;vertical-align:middle;overflow:visible;white-space:normal;word-wrap:break-word;word-break:break-word}
table.dt th{font-size:7pt;font-weight:700;text-align:center}
table.dt td{font-size:8pt}
.tc{text-align:center}.tl{text-align:left}.fw{font-weight:700}
table.dt-auto{table-layout:auto}
table.dt-auto th,table.dt-auto td{padding:15px 8px;white-space:normal;word-wrap:break-word}
table.dt-compact th,table.dt-compact td{padding:8px 8px;white-space:normal;word-wrap:break-word}

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
.pg-footer{display:none}

/* ── Zoom / scroll wrapper ─────────────── */
/*
   We separate the "body background" area from the zoom transform.
   #zoom-outer  = full-width grey background, handles the scrollable area
   #zoom-wrap   = scaled content, centered horizontally
*/
#zoom-outer{
    width:100%;
    overflow-x:auto;         /* horizontal scroll when zoomed in */
    padding-bottom:32px;     /* bottom padding only; horizontal centering done by JS */
}
#zoom-wrap{
    transform-origin:top left;
    transition:transform .12s ease;
    width:fit-content;
}

/* ── Print ─────────────────────────────── */
@media print{
    *{-webkit-print-color-adjust:exact!important;print-color-adjust:exact!important}
    body{background:#fff!important}
    #zoom-outer{padding:0!important;overflow:visible!important}
    #zoom-wrap{transform:none!important;width:100%!important;margin:0!important}
    .doc-page{box-shadow:none!important;margin:0!important;width:100%!important;min-height:auto!important}
    .no-print{display:none!important}
    .doc-page.portrait{page:portrait}
    .doc-page.landscape{page:landscape}
    thead{display:table-header-group}
}
@page{
    size:A4;
    @bottom-right{
        margin:0 10mm 5mm -10mm;
        content:"Hal " counter(page) " dari " counter(pages);
        font-size:8pt;
        font-family:Verdana,Geneva,sans-serif;
    }
}
@page portrait{size:A4 portrait;margin:12.7mm 2.5mm 12.7mm 2.5mm}
@page landscape{size:A4 landscape;margin:12.7mm 2.5mm 12.7mm 2.5mm}
</style>
</head>
<body>

{{-- ── Print Toolbar (screen only) ──────────────────── --}}
<div class="no-print toolbar">
    <div class="toolbar-title">
        <a href="{{ $backUrl ?? route('supervisor.laporan.show', $report) }}">
            <svg width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
            Kembali
        </a>
        <span class="sep">|</span>
        <span class="title-text">{{ $report->reportType->annex_number }} — {{ $report->reportType->name }}</span>
    </div>

    <div class="toolbar-zoom">
        <button class="zoom-btn" onclick="zoomOut()" title="Zoom Out">−</button>
        <span id="zoom-level" class="zoom-label">100%</span>
        <button class="zoom-btn" onclick="zoomIn()" title="Zoom In">+</button>
        <button class="zoom-reset" onclick="zoomReset()" title="Reset">↺</button>
    </div>

    <div class="toolbar-actions">
        @if(($showPrint ?? false) || auth()->user()->role === 'manajer')
        <button class="toolbar-print" onclick="window.print()">Cetak / Download PDF</button>
        @endif
    </div>
</div>

<div id="zoom-outer">
<div id="zoom-wrap">
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
        <tr><td style="width:45%">Tanggal Pemantauan Ruang</td><td>{{ $report->created_at->isoFormat('D MMMM Y') }}</td></tr>
        <tr><td>Nama Analis</td><td>{{ $report->shift1Analis->name }}{{ $report->shift2Analis ? ' / ' . $report->shift2Analis->name : '' }}</td></tr>
        <tr><td>Nama Produk</td><td>{{ $report->product_name }}</td></tr>
        <tr><td>Nomor Batch Produk</td><td>{{ $report->batch_number ?: '' }}</td></tr>
    </table>

    {{-- ── 2. Identitas Instrumen ───────────────────── --}}
    @if ($needsAirSampler)
    @php $as = $hd['air_sampler'] ?? []; @endphp
    <table class="dt dt-auto" style="margin-bottom:8px">
        <tr><td colspan="2" class="sec-hdr">2. Identitas Instrumen</td></tr>
        <tr><td style="width:45%">Nama Alat</td><td class="fw">Air Sampler</td></tr>
        <tr><td>No. ID Air Sampler</td><td>{{ $as['no_id'] ?? '' }}</td></tr>
        <tr><td>Tanggal Kalibrasi Air Sampler</td><td>{{ $as['calibration_date'] ?? '' }}</td></tr>
        <tr><td>Tanggal Due Date Kalibrasi Air Sampler</td><td>{{ $as['due_date'] ?? '' }}</td></tr>
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
        @if (!str_contains(strtolower($medLabel), 'swab'))
        <tr><td>Nomor GPT {{ $medLabel }}</td><td>{{ $med['nomor_gpt'] ?? '' }}</td></tr>
        @endif
        <tr><td>Tanggal ED {{ $medLabel }}</td><td>{{ $med['expiry_date'] ?? '' }}</td></tr>
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

    <table class="dt dt-auto dt-compact">
        <tr><td colspan="4" class="sec-hdr">4. Proses Inkubasi Medium Monitoring</td></tr>
        @foreach ([
            'inkubator_20_25' => ['label' => 'Inkubator Suhu 20–25°C', 'min_days' => 3],
            'inkubator_30_35' => ['label' => 'Inkubator Suhu 30–35°C', 'min_days' => 2],
        ] as $inkKey => $inkInfo)
        @php $ink = $hd[$inkKey] ?? []; @endphp
        <tr><td style="width:35%">Nama Alat</td><td colspan="3" class="fw">{{ $inkInfo['label'] }}</td></tr>
        <tr><td>No. ID Inkubator</td><td colspan="3">{{ $ink['no_id'] ?? '' }}</td></tr>
        <tr><td>Tanggal Kalibrasi Inkubator</td><td colspan="3">{{ $ink['calibration_date'] ?? '' }}</td></tr>
        <tr><td>Tanggal Due Date Kalibrasi Inkubator</td><td colspan="3">{{ $ink['due_date'] ?? '' }}</td></tr>
        {{-- Medium Monitoring --}}
        <tr>
            <td rowspan="8" style="vertical-align:middle">Tanggal Inkubasi Medium (min {{ $inkInfo['min_days'] }} hari)</td>
            <td rowspan="4" class="tc" style="vertical-align:middle;width:14%">Medium<br>Monitoring</td>
            <td>Tanggal Masuk Inkubator: {{ $ink['date_in'] ?? '' }}</td>
            <td style="width:14%">Jam: {{ $ink['time_in'] ?? '' }}</td>
        </tr>
        <tr><td colspan="2">Diinkubasi oleh (paraf, inisial, tanggal): {{ $ink['incubated_by'] ?? '' }}{{ isset($ink['incubated_date']) ? ', ' . $ink['incubated_date'] : '' }}</td></tr>
        <tr><td>Tanggal Keluar Inkubator: {{ $ink['date_out'] ?? '' }}</td><td>Jam: {{ $ink['time_out'] ?? '' }}</td></tr>
        <tr><td colspan="2">Dikeluarkan oleh (paraf, inisial, tanggal): {{ $ink['removed_by'] ?? '' }}{{ isset($ink['removed_date']) ? ', ' . $ink['removed_date'] : '' }}</td></tr>
        {{-- Swab --}}
        <tr>
            <td rowspan="4" class="tc" style="vertical-align:middle">Swab</td>
            <td>Tanggal Masuk Inkubator: {{ $ink['date_in'] ?? '' }}</td>
            <td>Jam: {{ $ink['time_in'] ?? '' }}</td>
        </tr>
        <tr><td colspan="2">Diinkubasi oleh (paraf, inisial, tanggal): {{ $ink['incubated_by'] ?? '' }}{{ isset($ink['incubated_date']) ? ', ' . $ink['incubated_date'] : '' }}</td></tr>
        <tr><td>Tanggal Keluar Inkubator: {{ $ink['date_out'] ?? '' }}</td><td>Jam: {{ $ink['time_out'] ?? '' }}</td></tr>
        <tr><td colspan="2">Dikeluarkan oleh (paraf, inisial, tanggal): {{ $ink['removed_by'] ?? '' }}{{ isset($ink['removed_date']) ? ', ' . $ink['removed_date'] : '' }}</td></tr>
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
    // Config-driven flags (matching analis view)
    $hasSharedTime  = (bool) $section->has_shared_time;
    $hasJam         = $section->time_slot_type === 'single';
    $isPerLocation  = $section->time_slot_type === 'per_location';
    $isDualAB       = $section->time_slot_type === 'dual_ab';
    $isSwabTime     = $section->time_slot_type === 'swab';
    $hasShiftToggle = (bool) $section->has_shift_toggle;
    $colLabel       = $section->column_label ?? 'Exposure';

    $maxCols       = $section->max_exposure;
    $romanNums     = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    $savedAsgn     = ($hd['shift_assignments'] ?? [])[$section->id] ?? [];
    $secAssignments = [];
    for ($c = 1; $c <= $maxCols; $c++) {
        $secAssignments[$c] = isset($savedAsgn[$c]) ? (int)$savedAsgn[$c] : 1;
    }
    $secNote  = $hd['section_notes'][$section->id] ?? [];
    $subColsPerExp = $isPerLocation ? 4 : 3;
    $totalCols = 5 + ($hasSharedTime ? 3 : 0) + $maxCols * $subColsPerExp + 4 + 1;
    $pageOrientation = ($hasSharedTime && $maxCols >= 4) ? 'landscape' : 'portrait';
@endphp
<div class="doc-page {{ $pageOrientation }}">
    {{-- Page header --}}
    <div class="pg-hdr">
        <div class="doc-num">{{ $report->reportType->annex_number }}</div>
        <div class="doc-title">{{ strtoupper($report->reportType->name) }}</div>
        <hr class="doc-title-line">
    </div>

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
                <th colspan="{{ ($hasSharedTime ? 3 : 0) + $maxCols * $subColsPerExp }}">
                    {{ $section->measurement_unit }}
                </th>
                <th colspan="2" rowspan="2">Alert<br>Limit</th>
                <th colspan="2" rowspan="2">Action<br>Limit</th>
                <th rowspan="3" style="width:46px">Kesim-<br>pulan</th>
            </tr>

            {{-- Row 2: Machine set-up / Column labels --}}
            <tr>
                @if ($hasSharedTime)
                @php
                    $msJamMulai = null; $msJamSelesai = null;
                    foreach ($section->locations as $loc2) {
                        $e0 = $entryMap[$loc2->pivot->id][0][1] ?? $entryMap[$loc2->pivot->id][0][2] ?? null;
                        if ($e0 && ($e0->start_time || $e0->end_time)) {
                            $msJamMulai = $e0->start_time; $msJamSelesai = $e0->end_time; break;
                        }
                    }
                @endphp
                <th colspan="3" style="font-size:7pt;line-height:1.35;vertical-align:top;padding:3px 2px">
                    <div style="font-weight:700">Machine set-up</div>
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>
                        Mulai Sebar Petri:<br>{{ $msJamMulai ?: '-:-' }}<br><br>
                        Selesai<br>Pemantauan:<br>{{ $msJamSelesai ?: '-:-' }}
                    </div>
                </th>
                @endif

                @for ($col = 1; $col <= $maxCols; $col++)
                @php $colAsgn = $secAssignments[$col] ?? 1; @endphp
                <th colspan="{{ $subColsPerExp }}" style="font-size:7pt;line-height:1.35;vertical-align:top;padding:3px 2px">
                    <div style="font-weight:700">{{ $colLabel }} {{ $maxCols > 1 ? ($romanNums[$col - 1] ?? $col) : '' }}</div>

                    @if ($isSwabTime)
                    @php $swabColTimes = $hd['swab_times'][$section->id][$col] ?? []; @endphp
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>Mulai Swab:<br>
                        @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                        @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                        {{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: '-:-' }}<br>
                        @endforeach
                        <br>Selesai<br>Pemantauan:<br>
                        @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                        @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                        {{ $swabLabel }}: {{ ($st['selesai'] ?? '') ?: '-:-' }}<br>
                        @endforeach
                    </div>
                    @endif

                    @if ($isDualAB)
                    @php
                        $stA = $hd['settle_times'][$section->id][$col]['a'] ?? [];
                        $stB = $hd['settle_times'][$section->id][$col]['b'] ?? [];
                    @endphp
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>Mulai Sebar Petri:<br>
                        A: {{ ($stA['start_time'] ?? '') ?: '-:-' }}<br>
                        B: {{ ($stB['start_time'] ?? '') ?: '-:-' }}<br><br>
                        Selesai<br>Pemantauan:<br>
                        A: {{ ($stA['end_time'] ?? '') ?: '-:-' }}<br>
                        B: {{ ($stB['end_time'] ?? '') ?: '-:-' }}
                    </div>
                    @endif

                    @if ($hasJam)
                    @php
                        $expJam = $hd['exposure_times'][$section->id][$col] ?? [];
                        $expJamMulai   = $expJam['start_time'] ?? null;
                        $expJamSelesai = $expJam['end_time'] ?? null;
                    @endphp
                    <div style="font-weight:400;margin-top:2px">
                        Mulai: {{ $expJamMulai ?: '-:-' }}<br>
                        Selesai: {{ $expJamSelesai ?: '-:-' }}
                    </div>
                    @endif
                </th>
                @endfor
            </tr>

            {{-- Row 3: B / F / T sub-headers --}}
            <tr>
                @if ($hasSharedTime)
                <th>B</th><th>F</th><th>T</th>
                @endif
                @for ($col = 1; $col <= $maxCols; $col++)
                @if ($isPerLocation)
                <th style="white-space:nowrap">Jam</th>
                @endif
                <th>B</th><th>F</th><th>T</th>
                @endfor
                <th>B</th><th>F</th>
                <th>B</th><th>F</th>
            </tr>
        </thead>

        <tbody>
            @foreach ($section->locations as $loc)
            @php
                $locEntries = collect();
                for ($p = 1; $p <= $section->max_exposure; $p++) {
                    for ($s = 1; $s <= 2; $s++) {
                        if (isset($entryMap[$loc->pivot->id][$p][$s])) $locEntries->push($entryMap[$loc->pivot->id][$p][$s]);
                    }
                }
                $maxB   = $locEntries->max(fn($e) => $e->cfu_bacteria ?? 0) ?? 0;
                $maxF   = $locEntries->max(fn($e) => $e->cfu_fungi ?? 0) ?? 0;
                $hasTMS = ($loc->alert_action_bacteria && $maxB >= $loc->alert_action_bacteria)
                       || ($loc->alert_action_fungi && $maxF >= $loc->alert_action_fungi);
                $hasAlt = !$hasTMS && (
                            ($loc->alert_limit_bacteria && $maxB >= $loc->alert_limit_bacteria)
                         || ($loc->alert_limit_fungi    && $maxF >= $loc->alert_limit_fungi));
                $konklusi = $locEntries->isEmpty() ? null : ($hasTMS ? 'TMS' : ($hasAlt ? 'Alert' : 'MS'));
            @endphp
            <tr>
                <td class="tc">{{ $loop->iteration }}</td>
                <td class="tl">{{ $loc->room->room_name }}</td>
                <td class="tc">{{ $loc->room->class }}</td>
                <td class="tc" style="font-family:monospace;font-size:7.5pt">{{ $loc->room->room_number }}</td>
                <td class="tc" style="font-family:monospace;font-size:7.5pt;{{ str_starts_with($loc->location_number, '*)') ? 'font-style:italic;' : '' }}">{{ $loc->location_number }}</td>

                {{-- Machine set-up --}}
                @if ($hasSharedTime)
                @php
                    $msEntry = $entryMap[$loc->pivot->id][0][1] ?? $entryMap[$loc->pivot->id][0][2] ?? null;
                    $msTVal  = ($msEntry && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null))
                        ? round(($msEntry->cfu_bacteria ?? 0) + ($msEntry->cfu_fungi ?? 0), 10) : null;
                @endphp
                <td class="tc">{{ $msEntry?->cfu_bacteria ?? '' }}</td>
                <td class="tc">{{ $msEntry?->cfu_fungi ?? '' }}</td>
                <td class="tc fw">{{ $msTVal ?? '' }}</td>
                @endif

                {{-- Data columns --}}
                @for ($col = 1; $col <= $maxCols; $col++)
                @php
                    $colAsgn    = $secAssignments[$col] ?? 1;
                    $existEntry = $hasSharedTime
                        ? ($entryMap[$loc->pivot->id][1][$colAsgn] ?? null)
                        : ($entryMap[$loc->pivot->id][$col][$colAsgn] ?? null);
                    $tVal = ($existEntry && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null))
                        ? round(($existEntry->cfu_bacteria ?? 0) + ($existEntry->cfu_fungi ?? 0), 10) : null;
                @endphp
                @if ($isPerLocation)
                <td class="tc" style="font-size:7.5pt">{{ $existEntry?->start_time ? \Illuminate\Support\Str::substr($existEntry->start_time, 0, 5) : '' }}</td>
                @endif
                <td class="tc">{{ $existEntry?->cfu_bacteria ?? '' }}</td>
                <td class="tc">{{ $existEntry?->cfu_fungi ?? '' }}</td>
                <td class="tc fw">{{ $tVal ?? '' }}</td>
                @endfor

                {{-- Alert Limit --}}
                <td class="tc">{{ $loc->alert_limit_bacteria !== null ? $loc->alert_limit_bacteria : 'NA' }}</td>
                <td class="tc">{{ $loc->alert_limit_fungi !== null ? $loc->alert_limit_fungi : 'NA' }}</td>
                {{-- Action Limit --}}
                <td class="tc">{{ $loc->alert_action_bacteria !== null ? ($loc->alert_action_bacteria == 1 ? '<1' : $loc->alert_action_bacteria) : 'NA' }}</td>
                <td class="tc">{{ $loc->alert_action_fungi !== null ? ($loc->alert_action_fungi == 1 ? '<1' : $loc->alert_action_fungi) : 'NA' }}</td>
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
        @if ($section->measurement_type === 'swab')
        *) diisi jika dibutuhkan<br>
        @endif
        Keterangan:<br>
        B: Total Bakteri, F: Total Fungi, T: Total Bakteri + Fungi, MS: Memenuhi Spesifikasi, TMS: Tidak Memenuhi Spesifikasi
    </div>

    {{-- CATATAN --}}
    <div style="margin-top:10px">
        @php $secCatatan = $secNote['notes'] ?? ''; @endphp
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
    @php $secKesp = $secNote['conclusion'] ?? ''; @endphp
    <div style="margin-top:8px">
        <span class="fw">KESIMPULAN</span>
        &ensp;: &ensp;
        @if ($secKesp === 'MS')
            <span class="fw">MEMENUHI SPESIFIKASI</span>
        @elseif ($secKesp === 'TMS')
            <span class="fw">TIDAK MEMENUHI SPESIFIKASI</span>
        @else
            <span>-</span>
        @endif
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
            <td>(Supervisor Mikrobiologi)</td>
            <td>(QC Manager)</td>
        </tr>
    </table>
    <div class="pg-footer"></div>
</div>
@endforeach
</div>{{-- /zoom-wrap --}}
</div>{{-- /zoom-outer --}}

<script>
var pageZoom = 1.0;
var userManualZoom = false;

function applyZoom() {
    pageZoom = Math.round(Math.max(0.15, Math.min(2.0, pageZoom)) * 100) / 100;
    var wrap  = document.getElementById('zoom-wrap');
    var outer = document.getElementById('zoom-outer');
    var lv    = document.getElementById('zoom-level');

    // Reset transforms first so scrollHeight/Width reflect natural dimensions
    wrap.style.transform  = 'none';
    wrap.style.height     = '';
    wrap.style.marginLeft = '';
    var naturalH = wrap.scrollHeight;
    var naturalW = wrap.scrollWidth;

    // Apply scale from top-left so horizontal position is predictable
    wrap.style.transform = 'scale(' + pageZoom + ')';

    // Compensate height: CSS transform doesn't affect document flow
    wrap.style.height = (naturalH * pageZoom) + 'px';

    // Center content horizontally with at least 16px left breathing room
    var vw      = window.innerWidth || document.documentElement.clientWidth;
    var scaledW = naturalW * pageZoom;
    wrap.style.marginLeft = Math.max(16, (vw - scaledW) / 2) + 'px';

    if (lv) lv.textContent = Math.round(pageZoom * 100) + '%';
}

function zoomIn()    { userManualZoom = true; pageZoom = Math.min(2.0,  pageZoom + 0.1); applyZoom(); }
function zoomOut()   { userManualZoom = true; pageZoom = Math.max(0.15, pageZoom - 0.1); applyZoom(); }
function zoomReset() { userManualZoom = false; autoFit(); }

function autoFit() {
    // Find the widest page: landscape = 297mm, portrait = 210mm
    var hasLandscape = document.querySelector('.doc-page.landscape') !== null;
    var maxPageMM    = hasLandscape ? 297 : 210;
    // 1mm ≈ 3.7795px at 96dpi
    var maxPagePx    = maxPageMM * 3.7795;
    // Account for #zoom-outer horizontal padding (16px * 2 = 32px)
    var totalWidth   = maxPagePx + 32;
    var vw = window.innerWidth || document.documentElement.clientWidth;

    if (vw < totalWidth) {
        pageZoom = Math.max(0.15, Math.floor((vw / totalWidth) * 100) / 100);
    } else {
        pageZoom = 1.0;
    }
    applyZoom();
}

document.addEventListener('DOMContentLoaded', function(){
    autoFit();
});

var resizeTimer;
window.addEventListener('resize', function() {
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(function() {
        if (!userManualZoom) autoFit();
    }, 150);
});
</script>
</body>
</html>