@php
    $hd = $report->header_data ?? [];
    $printPreviewOnly = $printPreviewOnly ?? false;
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>{{ $report->reportType->annex_number }} — {{ $report->created_at->format('Y-m-d') }}</title>
<style>
*{box-sizing:border-box;margin:0;padding:0}
body{font-family:Verdana,Geneva,sans-serif;font-size:10pt;color:#000;background:{{ $printPreviewOnly ? '#fff' : '#d1d5db' }};overflow-x:hidden}

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
table.dt.dt-personnel th,table.dt.dt-personnel td{word-wrap:normal;word-break:normal;overflow-wrap:normal}
table.dt.dt-personnel .p-act{width:150px}
table.dt.dt-personnel .p-point{width:160px}
table.dt.dt-personnel .p-class{width:35px}
table.dt.dt-personnel .p-cfu{width:28px}
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
    padding-bottom:{{ $printPreviewOnly ? '0' : '32px' }};     /* bottom padding only; horizontal centering done by JS */
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
@unless($printPreviewOnly)
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
@endunless

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
        @php $names = \App\Models\User::whereIn('id', array_merge($report->analyst_monitoring ?? [], $report->analyst_reading ?? []))->pluck('name'); @endphp
        <tr><td>Nama Analis</td><td>{{ $names->isNotEmpty() ? $names->join(' , ') : '' }}</td></tr>
        <tr><td>Nama Produk</td><td>{{ $report->product_name }}</td></tr>
        <tr><td>Nomor Batch Produk</td><td>{{ $report->batch_number ?: '' }}</td></tr>
    </table>

    {{-- ── 2. Identitas Instrumen ───────────────────── --}}
    @if ($needsAirSampler)
    @php $as = $report->instrumentEntries->first(); @endphp
    <table class="dt dt-auto" style="margin-bottom:8px">
        <tr><td colspan="2" class="sec-hdr">2. Identitas Instrumen</td></tr>
        <tr><td style="width:45%">Nama Alat</td><td class="fw">{{ $as?->tool_name ?? 'Air Sampler' }}</td></tr>
        <tr><td>No. ID Air Sampler</td><td>{{ $as?->no_id ?? '' }}</td></tr>
        <tr><td>Tanggal Kalibrasi Air Sampler</td><td>{{ $as?->calibration_date?->format('d/m/Y') ?? '' }}</td></tr>
        <tr><td>Tanggal Due Date Kalibrasi Air Sampler</td><td>{{ $as?->due_date?->format('d/m/Y') ?? '' }}</td></tr>
    </table>
    @endif

    {{-- ── 3. Identitas Medium ──────────────────────── --}}
    @php
        $mediumTypeList = $report->reportType->mediumTypes
            ->sortBy(fn ($m) => str_contains(strtolower($m->name), 'swab') ? 1 : 0)
            ->values();
        $mediumMap = $report->mediumIdentities->keyBy('name');
    @endphp
    @if ($mediumTypeList->isNotEmpty())
    <table class="dt dt-auto" style="margin-bottom:8px">
        <tr><td colspan="2" class="sec-hdr">3. Identitas Medium</td></tr>
        @foreach ($mediumTypeList as $medType)
        @php
            $medName = $medType->name;
            $med = $mediumMap->get($medName);
            $isSwab = str_contains(strtolower($medName), 'swab');
        @endphp
        <tr><td style="width:45%">Nomor Batch {{ $medName }}</td><td>{{ $med?->batch_number ?? '' }}</td></tr>
        @if (!$isSwab)
        <tr><td>Nomor GPT {{ $medName }}</td><td>{{ $med?->gpt_number ?? '' }}</td></tr>
        @endif
        <tr><td>Tanggal ED {{ $medName }}</td><td>{{ $med?->expiration_date?->format('d/m/Y') ?? '' }}</td></tr>
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

    @php
        $incubatorTypes = $report->reportType->incubatorTypes;
        $incubatorMap = $report->incubators->keyBy('report_type_incubator_id');
        $hasMediumSwab = $report->reportType->mediumTypes
            ->contains(fn ($m) => str_contains(strtolower($m->name), 'swab'));
        $mediumTypeLabels = array_merge(
            ['monitoring' => 'Medium Monitoring'],
            $hasMediumSwab ? ['swab' => 'Swab'] : []
        );
    @endphp
    @if ($incubatorTypes->isNotEmpty())
    <table class="dt dt-auto dt-compact">
        <tr><td colspan="4" class="sec-hdr">4. Proses Inkubasi Medium Monitoring</td></tr>
        @foreach ($incubatorTypes as $config)
        @php
            $ink = $incubatorMap->get($config->id);
            $inkEntries = ($ink?->entries ?? collect())->keyBy('medium_type');
        @endphp
        <tr><td style="width:35%">Nama Alat</td><td colspan="3" class="fw">Inkubator Suhu {{ $config->temperature_label }}</td></tr>
        <tr><td>No. ID Inkubator</td><td colspan="3">{{ $ink?->no_id ?? '' }}</td></tr>
        <tr><td>Tanggal Kalibrasi Inkubator</td><td colspan="3">{{ $ink?->calibration_date?->format('d/m/Y') ?? '' }}</td></tr>
        <tr><td>Tanggal Due Date Kalibrasi Inkubator</td><td colspan="3">{{ $ink?->due_date_calibration?->format('d/m/Y') ?? '' }}</td></tr>
        @foreach ($mediumTypeLabels as $mediumType => $mediumLabel)
        @php $entry = $inkEntries->get($mediumType); @endphp
        <tr>
            <td rowspan="4" style="vertical-align:middle">Tanggal Inkubasi {{ $mediumLabel }} (min {{ $config->min_day }} hari)</td>
            <td rowspan="4" class="tc" style="vertical-align:middle;width:14%">{{ $mediumLabel }}</td>
            <td>Tanggal Masuk Inkubator: {{ $entry?->date_in?->format('d/m/Y') ?? '' }}</td>
            <td style="width:14%">Jam: {{ $entry?->time_in ?? '' }}</td>
        </tr>
        <tr><td colspan="2">Diinkubasi oleh (paraf, inisial, tanggal): {{ $entry?->incubatedBy?->name ?? '' }}{{ $entry?->date_in ? ', ' . $entry->date_in->format('d/m/Y') : '' }}</td></tr>
        <tr><td>Tanggal Keluar Inkubator: {{ $entry?->date_out?->format('d/m/Y') ?? '' }}</td><td>Jam: {{ $entry?->time_out ?? '' }}</td></tr>
        <tr><td colspan="2">Dikeluarkan oleh (paraf, inisial, tanggal): {{ $entry?->removedBy?->name ?? '' }}{{ $entry?->date_out ? ', ' . $entry->date_out->format('d/m/Y') : '' }}</td></tr>
        @endforeach
        @if (!$loop->last)
        <tr><td colspan="4" style="border:none;padding:5px"></td></tr>
        @endif
        @endforeach
    </table>
    @endif
    <div class="pg-footer"></div>
</div>
@endif

{{-- ══════════════════════════════════════════════════════
     LANDSCAPE PAGES: Measurement sections
     ══════════════════════════════════════════════════════ --}}
@foreach ($report->reportType->sections as $section)
@php
    $cfuNum = function(?string $v): ?int {
        if ($v === null || $v === '') return null;
        if (strtoupper($v) === 'TNTC') return PHP_INT_MAX;
        if ($v === '<1') return 0;
        if (is_numeric($v)) return (int) $v;
        return null;
    };
    $cfuTot = function(?string $b, ?string $f) use ($cfuNum): ?string {
        $bn = $cfuNum($b); $fn = $cfuNum($f);
        if ($bn === null && $fn === null) return null;
        if ($bn === PHP_INT_MAX || $fn === PHP_INT_MAX) return 'TNTC';
        $bn = $bn ?? 0; $fn = $fn ?? 0;
        if ($b === '<1' && $f === '<1') return '<1';
        return (string)($bn + $fn);
    };
    // Config-driven flags (matching analis view)
    $hasMachineSetup  = (bool) $section->has_machine_setup;
    $hasJam         = $section->time_slot_type === 'single';
    $isPerLocation  = $section->time_slot_type === 'per_location';
    $isDualAB       = $section->time_slot_type === 'dual_ab';
    $isSwabTime     = $section->time_slot_type === 'swab';
    $isSettlePlate  = $section->measurement_key === 'settle_plate';
    $hasShiftToggle = true;
    $colLabelRaw    = is_string($section->column_label) ? trim($section->column_label) : null;
    $colLabel       = $colLabelRaw !== '' ? $colLabelRaw : null;

    $maxCols       = $section->max_column;
    $romanNums     = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    $savedAsgn     = ($hd['shift_assignments'] ?? [])[$section->id] ?? [];
    $secAssignments = [];
    for ($c = 1; $c <= $maxCols; $c++) {
        $secAssignments[$c] = isset($savedAsgn[$c]) ? (int)$savedAsgn[$c] : 1;
    }
    $secColumnNames = $report->sectionColumnNames
        ->where('section_id', $section->id)
        ->where('instance_number', 1)
        ->keyBy(fn($r) => (int) $r->period_number)
        ->map(fn($r) => $r->label)
        ->all();
    $secNote  = $hd['section_notes'][$section->id] ?? [];
    $subColsPerExp = $isPerLocation ? 4 : 3;
    $totalCols = 5 + ($hasMachineSetup ? 3 : 0) + $maxCols * $subColsPerExp + 4 + 1;
    $pageOrientation = ($hasMachineSetup && $maxCols >= 4) ? 'landscape' : 'portrait';
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
                <th colspan="{{ ($hasMachineSetup ? 3 : 0) + $maxCols * $subColsPerExp }}">
                    {{ $section->measurement_unit }}
                </th>
                <th colspan="2" rowspan="2">Alert<br>Limit</th>
                <th colspan="2" rowspan="2">Action<br>Limit</th>
                <th rowspan="3" style="width:46px">Kesim-<br>pulan</th>
            </tr>

            {{-- Row 2: Machine set-up / Column labels --}}
            <tr>
                @if ($hasMachineSetup)
                @php
                    $msJamMulai = null; $msJamSelesai = null;
                    foreach ($section->locations as $loc2) {
                        $e0 = $entryMap[$loc2->id][0][1] ?? $entryMap[$loc2->id][0][2] ?? null;
                        if ($e0 && ($e0->start_time || $e0->end_time)) {
                            $msJamMulai = $e0->start_time; $msJamSelesai = $e0->end_time; break;
                        }
                    }
                @endphp
                <th colspan="3" style="font-size:7pt;line-height:1.35;vertical-align:top;padding:3px 2px">
                    <div style="font-weight:700;display:flex;justify-content:center;align-items:center;text-align:center">Machine set-up</div>
                    <div style="font-weight:400;margin-top:2px;display:flex;justify-content:center;align-items:center;text-align:center;visibility:hidden">
                        SP: -
                    </div>
                    <div style="font-weight:400;margin:2px -2px 0;border-top:1px solid #000;padding-top:2px;display:flex;flex-direction:column;align-items:center;text-align:center">
                        JAM<br>
                        Mulai Sebar Petri:<br>{{ $msJamMulai ?: 'N/A:N/A' }}<br><br>
                        Selesai<br>Pemantauan:<br>{{ $msJamSelesai ?: 'N/A:N/A' }}
                    </div>
                </th>
                @endif

                @for ($col = 1; $col <= $maxCols; $col++)
                @php
                    $colAsgn = $secAssignments[$col] ?? 1;
                    $colName = $secColumnNames[$col] ?? null;
                @endphp
                <th colspan="{{ $subColsPerExp }}" style="font-size:7pt;line-height:1.35;vertical-align:top;padding:3px 2px">
                    @php
                        $hasColLabel = (($colLabel ?? '') !== '');
                        $colPeriod = ($hasColLabel && $maxCols > 1) ? ($romanNums[$col - 1] ?? $col) : '';
                        $colHeaderTitle = trim((($colLabel ?? '') !== '' ? ($colLabel . ' ') : '') . $colPeriod);
                    @endphp
                    @if ($isSettlePlate)
                    <div style="font-weight:700;display:flex;justify-content:center;align-items:center;text-align:center">
                        {{ $colHeaderTitle }}
                    </div>
                    <div style="font-weight:700;margin-top:2px;display:flex;justify-content:center;align-items:center;text-align:center">
                        SP ({{ $colName ?: '....' }})
                    </div>
                    @else
                    <div style="font-weight:700;display:flex;justify-content:center;align-items:center;text-align:center">
                        {{ $isSettlePlate ? 'SP' : 'Shift' }} ({{ $colName ?: '....' }})
                    </div>
                    <div style="font-weight:400;margin:2px -2px 0;border-top:1px solid #000;padding-top:2px;display:flex;justify-content:center;align-items:center;text-align:center">
                        @if ($hasColLabel)
                        <span style="font-weight:700">{{ $colHeaderTitle }}</span>
                        @endif
                    </div>
                    @endif

                    @if ($isSwabTime)
                    @php $swabColTimes = $hd['swab_times'][$section->id][$col] ?? []; @endphp
                    <div style="font-weight:400;margin-top:2px">
                        JAM<br>Mulai Swab:<br>
                        @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                        @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                        {{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: 'N/A:N/A' }}<br>
                        @endforeach
                        <br>Selesai<br>Pemantauan:<br>
                        @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                        @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                        {{ $swabLabel }}: {{ ($st['selesai'] ?? '') ?: 'N/A:N/A' }}<br>
                        @endforeach
                    </div>
                    @endif

                    @if ($isDualAB)
                    @php
                        $stA = $hd['settle_times'][$section->id][$col]['a'] ?? [];
                        $stB = $hd['settle_times'][$section->id][$col]['b'] ?? [];
                    @endphp
                    <div style="font-weight:400;margin-top:2px;{{ $isSettlePlate ? 'margin-left:-2px;margin-right:-2px;border-top:1px solid #000;padding-top:2px;' : '' }}">
                        JAM<br>Mulai Sebar Petri:<br>
                        A: {{ ($stA['start_time'] ?? '') ?: 'N/A:N/A' }}<br>
                        B: {{ ($stB['start_time'] ?? '') ?: 'N/A:N/A' }}<br><br>
                        Selesai<br>Pemantauan:<br>
                        A: {{ ($stA['end_time'] ?? '') ?: 'N/A:N/A' }}<br>
                        B: {{ ($stB['end_time'] ?? '') ?: 'N/A:N/A' }}
                    </div>
                    @endif

                    @if ($hasJam)
                    @php
                        $expJam = $hd['exposure_times'][$section->id][$col] ?? [];
                        $expJamMulai   = $expJam['start_time'] ?? null;
                        $expJamSelesai = $expJam['end_time'] ?? null;
                        $jamMulaiLabel = $section->measurement_key === 'contact_plate'
                            ? 'Mulai Contact Plate'
                            : 'Mulai Sebar Petri';
                    @endphp
                    <div style="font-weight:400;margin-top:2px;display:flex;flex-direction:column;align-items:center;text-align:center">
                        <span style="font-weight:700">JAM</span>
                        {{ $jamMulaiLabel }}:<br>
                        {{ $expJamMulai ?: 'N/A:N/A' }}<br><br>
                        Selesai Pemantauan:<br>
                        {{ $expJamSelesai ?: 'N/A:N/A' }}
                    </div>
                    @endif
                </th>
                @endfor
            </tr>

            {{-- Row 3: B / F / T sub-headers --}}
            <tr>
                @if ($hasMachineSetup)
                <th>B</th><th>F</th><th>T</th>
                @endif
                @for ($col = 1; $col <= $maxCols; $col++)
                @if ($isPerLocation)
                <th style="white-space:nowrap">Jam</th>
                @endif
                <th>B</th><th>F</th><th>T</th>
                @endfor
                <th>T</th><th>F</th>
                <th>T</th><th>F</th>
            </tr>
        </thead>

        <tbody>
            @php
                $_freqOrderP   = ['operational', 'daily', 'weekly', 'monthly', 'semi_annual'];
                $_locsByFreqP  = $section->locations->groupBy(fn($loc) => $loc->frequency ?? '__');
                $_freqKeysP    = collect($_freqOrderP)
                                    ->filter(fn($f) => $_locsByFreqP->has($f))
                                    ->merge($_locsByFreqP->keys()->filter(fn($k) => !in_array($k, $_freqOrderP) && $k !== '__'))
                                    ->values();
                if ($_locsByFreqP->has('__')) $_freqKeysP->push('__');
                $_showFHdrP    = $_freqKeysP->count() > 1 || ($_freqKeysP->count() === 1 && $_freqKeysP->first() !== '__');
                $_totalColsP   = 5 + ($hasMachineSetup ? 3 : 0) + ($maxCols * $subColsPerExp) + 5;
                $_rowNumP      = 0;
            @endphp
            @foreach ($_freqKeysP as $_freqNameP)
            @if ($_showFHdrP)
            <tr>
                <td colspan="{{ $_totalColsP }}" style="font-weight:700;text-align:left;padding:3px 4px;font-size:7.5pt;border-top:1.5px solid #444;letter-spacing:0.05em">
                    FREQUENCY : {{ $_freqNameP === '__' ? 'TIDAK DITENTUKAN' : strtoupper(\App\Models\ReportLocation::frequencyLabel($_freqNameP)) }}
                </td>
            </tr>
            @endif
            @foreach ($_locsByFreqP[$_freqNameP] as $loc)
            @php $_rowNumP++; @endphp
            @php
                $locEntries = collect();
                for ($p = 1; $p <= $section->max_column; $p++) {
                    for ($s = 1; $s <= 2; $s++) {
                        if (isset($entryMap[$loc->id][$p][$s])) $locEntries->push($entryMap[$loc->id][$p][$s]);
                    }
                }
                $maxT   = $locEntries->reduce(fn($carry, $e) => max($carry, ($cfuNum($e->cfu_bacteria) ?? 0) + ($cfuNum($e->cfu_fungi) ?? 0)), 0);
                $maxF   = $locEntries->reduce(fn($carry, $e) => max($carry, $cfuNum($e->cfu_fungi) ?? 0), 0);
                $hasTMS = ($loc->alert_action_total && $maxT >= $loc->alert_action_total)
                       || ($loc->alert_action_fungi && $maxF >= $loc->alert_action_fungi);
                $hasAlt = !$hasTMS && (
                            ($loc->alert_limit_total && $maxT >= $loc->alert_limit_total)
                         || ($loc->alert_limit_fungi    && $maxF >= $loc->alert_limit_fungi));
                $hasCfuEntries = $locEntries->contains(fn($e) => $e->cfu_bacteria !== null || $e->cfu_fungi !== null);
                $konklusi = $hasCfuEntries ? ($hasTMS ? 'TMS' : 'MS') : null;
            @endphp
            <tr>
                <td class="tc">{{ $_rowNumP }}</td>
                <td class="tl">{{ $loc->room->room_name }}</td>
                <td class="tc">{{ $loc->room->class }}</td>
                <td class="tc" style="font-family:monospace;font-size:7.5pt">{{ $loc->room->room_number }}</td>
                <td class="tc" style="font-family:monospace;font-size:7.5pt;{{ str_starts_with($loc->location_number, '*)') ? 'font-style:italic;' : '' }}">{{ $loc->location_number }}</td>

                {{-- Machine set-up --}}
                @if ($hasMachineSetup)
                @php
                    $msEntry = $entryMap[$loc->id][0][1] ?? $entryMap[$loc->id][0][2] ?? null;
                    $msTVal  = ($msEntry && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null))
                        ? $cfuTot($msEntry->cfu_bacteria, $msEntry->cfu_fungi) : null;
                @endphp
                <td class="tc">{{ $msEntry?->cfu_bacteria ?? 'N/A' }}</td>
                <td class="tc">{{ $msEntry?->cfu_fungi ?? 'N/A' }}</td>
                <td class="tc">{{ $msTVal ?? 'N/A' }}</td>
                @endif

                {{-- Data columns --}}
                @for ($col = 1; $col <= $maxCols; $col++)
                @php
                    $colAsgn    = $secAssignments[$col] ?? 1;
                    $existEntry = $entryMap[$loc->id][$col][$colAsgn] ?? null;
                    $tVal = ($existEntry && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null))
                        ? $cfuTot($existEntry->cfu_bacteria, $existEntry->cfu_fungi) : null;
                @endphp
                @if ($isPerLocation)
                <td class="tc" style="font-size:7.5pt">{{ $existEntry?->start_time ? \Illuminate\Support\Str::substr($existEntry->start_time, 0, 5) : 'N/A' }}</td>
                @endif
                <td class="tc">{{ $existEntry?->cfu_bacteria ?? 'N/A' }}</td>
                <td class="tc">{{ $existEntry?->cfu_fungi ?? 'N/A' }}</td>
                <td class="tc">{{ $tVal ?? 'N/A' }}</td>
                @endfor

                {{-- Alert Limit --}}
                <td class="tc">{{ $loc->alert_limit_total !== null ? $loc->alert_limit_total : 'NA' }}</td>
                <td class="tc">{{ $loc->alert_limit_fungi !== null ? $loc->alert_limit_fungi : 'NA' }}</td>
                {{-- Action Limit --}}
                <td class="tc">{{ $loc->alert_action_total !== null ? ($loc->alert_action_total == 1 ? '<1' : $loc->alert_action_total) : 'NA' }}</td>
                <td class="tc">{{ $loc->alert_action_fungi !== null ? ($loc->alert_action_fungi == 1 ? '<1' : $loc->alert_action_fungi) : 'NA' }}</td>
                {{-- Kesimpulan --}}
                <td class="tc fw">
                    @if ($konklusi === 'TMS') TMS
                    @elseif ($konklusi === 'MS') MS
                    @else MS / TMS*
                    @endif
                </td>
            </tr>
            @endforeach {{-- locations in frequency group --}}
            @endforeach {{-- frequency groups --}}
        </tbody>
    </table>

    {{-- Keterangan --}}
    <div style="font-size:7.5pt;margin:3px 0">
        @if ($section->measurement_key === 'swab')
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
    @php
        $secKesp = $secNote['conclusion'] ?? '';
        // Auto-compute from entries if stored value is empty
        if ($secKesp === '') {
            $_ckHasTMS   = false;
            $_ckAllEmpty = true;
            foreach ($section->locations as $_loc) {
                foreach ($entryMap[$_loc->id] ?? [] as $_period => $_shifts) {
                    foreach ($_shifts as $_shift => $_e) {
                        $_ckAllEmpty = false;
                        $_maxT = ($cfuNum($_e->cfu_bacteria) ?? 0) + ($cfuNum($_e->cfu_fungi) ?? 0);
                        $_maxF = $cfuNum($_e->cfu_fungi) ?? 0;
                        if (($_loc->alert_action_total && $_maxT >= $_loc->alert_action_total)
                            || ($_loc->alert_action_fungi && $_maxF >= $_loc->alert_action_fungi)) {
                            $_ckHasTMS = true;
                        }
                    }
                }
            }
            if (!$_ckAllEmpty) {
                $secKesp = $_ckHasTMS ? 'TMS' : 'MS';
            }
        }
    @endphp
    <div style="margin-top:8px">
        <span class="fw">KESIMPULAN</span>
        &ensp;: &ensp;
        @if ($secKesp === 'MS')
            <span class="fw">MEMENUHI SPESIFIKASI</span>
        @elseif ($secKesp === 'TMS')
            <span class="fw">TIDAK MEMENUHI SPESIFIKASI</span>
        @else
            <span>N/A</span>
        @endif
    </div>

    {{-- Tanda Tangan --}}
    @php
        $_pSectionPivotIds = $section->locations->pluck('id')->toArray();
        $_pSecAnalysts = [];
        foreach ($_pSectionPivotIds as $_pid) {
            foreach ($entryMap[$_pid] ?? [] as $_pMap) {
                foreach ($_pMap as $_e) {
                    if ((int) $_e->analyst_id) $_pSecAnalysts[(string) $_e->analyst_id] = true;
                }
            }
        }
        $_pSecAnalystIds = array_keys($_pSecAnalysts);
        $_pAllMonIds  = array_map('strval', $report->analyst_monitoring ?? []);
        $_pAllReadIds = array_map('strval', $report->analyst_reading    ?? []);
        $_pSecMonTs   = $hd['section_ttd_monitoring'][(string) $section->id] ?? [];
        $_pSecReadTs  = $hd['section_ttd_reading'][(string) $section->id]    ?? [];
        $_pSecMonIds  = array_values(array_unique(array_merge(
            array_intersect($_pSecAnalystIds, $_pAllMonIds),
            array_intersect(array_keys($_pSecMonTs), $_pAllMonIds)
        )));
        $_pSecReadIds = array_values(array_unique(array_merge(
            array_intersect($_pSecAnalystIds, $_pAllReadIds),
            array_intersect(array_keys($_pSecReadTs), $_pAllReadIds)
        )));
        $_pSupApproval  = $report->approvals->firstWhere('step', 2);
        $_pMngrApproval = $report->approvals->firstWhere('step', 3);
        $_pUniqueIds = array_unique(array_filter(array_merge($_pSecMonIds, $_pSecReadIds)));
        $_pUserMap   = \App\Models\User::whereIn('id', $_pUniqueIds)->get()->keyBy('id');
    @endphp
    @include('partials.report-signature-print', [
        'report'       => $report,
        'secMonIds'    => $_pSecMonIds,
        'secReadIds'   => $_pSecReadIds,
        'secMonTs'     => $_pSecMonTs,
        'secReadTs'    => $_pSecReadTs,
        'userMap'      => $_pUserMap,
        'supApproval'  => $_pSupApproval,
        'mngrApproval' => $_pMngrApproval,
    ])
    <div class="pg-footer"></div>
</div>
@endforeach

@php
    $personnelMethods = $report->reportType->personnelMethods ?? collect();
    $personnelInstances = $report->personnelInstances ?? collect();
    $hasPersonnelMonitoring = (bool) ($report->reportType->has_personnel ?? false)
        && $personnelMethods->isNotEmpty()
        && $personnelInstances->isNotEmpty();
@endphp

@if ($hasPersonnelMonitoring)
    @php
        $instancesByPage = $personnelInstances
            ->groupBy('page_number')
            ->sortKeys()
            ->map(fn ($grp) => $grp->keyBy('personnel_section_method_id'));

        $maxExistingPage = (int) ($instancesByPage->keys()->max() ?? 1);
        $totalPersonnelPages = max(2, $maxExistingPage);
        $personnelPages = collect(range(1, $totalPersonnelPages));
    @endphp

    @foreach ($personnelPages as $pageNum)
        @php
            $pageInstancesByMethod = $instancesByPage->get($pageNum, collect());
            $fallbackPageOneByMethod = $instancesByPage->get(1, collect());
            $personCount = $pageNum === 1 ? 2 : 4;
            $pageNoteSource = $pageInstancesByMethod
                ->first(fn ($inst) => filled($inst->note) || filled($inst->deviation))
                ?? $pageInstancesByMethod->first();
            $pageNote = [
                'note' => $pageNoteSource?->note ?? '',
                'deviation' => $pageNoteSource?->deviation ?? '',
            ];
        @endphp
        <div class="doc-page portrait">
            <div class="pg-hdr">
                <div class="doc-num">{{ $report->reportType->annex_number }}</div>
                <div class="doc-title">{{ strtoupper($report->reportType->name) }}</div>
                <hr class="doc-title-line">
            </div>

            @if ($pageNum === 1)
                <table class="dt dt-auto" style="margin-bottom:8px">
                    <tr><td colspan="2" class="sec-hdr">Pemantauan Higienitas Personil Rutin</td></tr>
                    <tr><td style="width:45%">Tanggal Pemantauan</td><td>{{ $report->created_at->isoFormat('D MMMM Y') }}</td></tr>
                    <tr><td>Nama Produk</td><td>{{ $report->product_name ?? '' }}</td></tr>
                    <tr><td>No Batch Produk</td><td>{{ $report->batch_number ?? '' }}</td></tr>
                </table>

                <table class="dt" style="margin-bottom:8px">
                    <thead>
                        <tr>
                            <th rowspan="3" style="width:22px">No</th>
                            <th rowspan="3" style="width:95px">Metode</th>
                            <th rowspan="3" style="width:115px">Spesifikasi</th>
                            <th colspan="4">Kelas</th>
                        </tr>
                        <tr>
                            <th colspan="2">B</th>
                            <th colspan="2">C</th>
                        </tr>
                        <tr>
                            <th>T</th><th>F</th><th>T</th><th>F</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($personnelMethods as $idx => $method)
                            @php
                                $limits = $method->limits->keyBy(fn ($l) => $l->class . '_' . $l->limit_type);
                                $bAlert = $limits->get('b_alert');
                                $bAction = $limits->get('b_action');
                                $cAlert = $limits->get('c_alert');
                                $cAction = $limits->get('c_action');
                            @endphp
                            <tr>
                                <td rowspan="2" class="tc">{{ $idx + 1 }}</td>
                                <td rowspan="2" class="tc">
                                    {{ $method->method }}<br>
                                    <span style="font-size:7pt">(CFU/plate)</span>
                                </td>
                                <td class="tl">Alert Limit</td>
                                <td class="tc">{{ $bAlert ? ($bAlert->cfu_total == 1 ? '<1' : $bAlert->cfu_total) : '' }}</td>
                                <td class="tc">{{ $bAlert ? ($bAlert->cfu_fungi == 1 ? '<1' : $bAlert->cfu_fungi) : '' }}</td>
                                <td class="tc">{{ $cAlert ? ($cAlert->cfu_total == 1 ? '<1' : $cAlert->cfu_total) : '' }}</td>
                                <td class="tc">{{ $cAlert ? ($cAlert->cfu_fungi == 1 ? '<1' : $cAlert->cfu_fungi) : '' }}</td>
                            </tr>
                            <tr>
                                <td class="tl">Action Limit</td>
                                <td class="tc">{{ $bAction ? ($bAction->cfu_total == 1 ? '<1' : $bAction->cfu_total) : '' }}</td>
                                <td class="tc">{{ $bAction ? ($bAction->cfu_fungi == 1 ? '<1' : $bAction->cfu_fungi) : '' }}</td>
                                <td class="tc">{{ $cAction ? ($cAction->cfu_total == 1 ? '<1' : $cAction->cfu_total) : '' }}</td>
                                <td class="tc">{{ $cAction ? ($cAction->cfu_fungi == 1 ? '<1' : $cAction->cfu_fungi) : '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif

            @foreach ($personnelMethods as $method)
                @php
                    $isFingerDab = str_contains(strtolower($method->method), 'finger')
                        || str_contains(strtolower($method->method), 'dab');
                    $instance = $pageInstancesByMethod->get($method->id);
                    $sourceInstance = $instance;
                    if ($pageNum === 2 && ! $sourceInstance) {
                        $sourceInstance = $fallbackPageOneByMethod->get($method->id);
                    }
                    $existingRows = ($sourceInstance?->rows ?? collect())->keyBy('row_order');
                    $points = $method->samplingPoints;
                    $pointCount = max(1, $points->count());
                @endphp

                <table class="dt dt-personnel" style="margin-bottom:8px">
                    <colgroup>
                        <col style="width:90px">
                        <col style="width:66px">
                        <col class="p-act">
                        <col class="p-point">
                        <col class="p-class">
                        <col class="p-cfu">
                        <col class="p-cfu">
                        <col class="p-cfu">
                        <col style="width:62px">
                    </colgroup>
                    <thead>
                        <tr>
                            <th colspan="9" class="tl" style="font-size:8pt">Metode {{ $method->method }}</th>
                        </tr>
                        <tr>
                            <th rowspan="2" style="width:90px">Nama Personel</th>
                            <th rowspan="2" style="width:66px">Jam Pemantauan<br><span style="font-weight:400">(Jam : menit)</span></th>
                            <th rowspan="2">Aktivitas</th>
                            <th rowspan="2">Titik Sampling</th>
                            <th rowspan="2">Kelas</th>
                            <th colspan="3">Hasil Pengamatan<br>(CFU/plate)</th>
                            <th rowspan="2" style="width:62px">Kesimpulan</th>
                        </tr>
                        <tr>
                            <th>B</th>
                            <th>F</th>
                            <th>T</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($p = 0; $p < $personCount; $p++)
                            @php
                                $row = $existingRows->get($p) ?? $existingRows->get($p + 1);
                                $rowActivities = collect($row?->activities ?? [])->map(fn ($id) => (string) $id)->all();
                                $rowCfu = $row ? $row->samplingEntries->keyBy('sampling_point_id') : collect();
                                $rowClass = $isFingerDab ? 'B' : strtoupper($row?->class ?? 'B / C');
                                $timeVal = $row?->monitoring_time
                                    ? \Illuminate\Support\Str::substr((string) $row->monitoring_time, 0, 5)
                                    : 'N/A';
                            @endphp
                            @foreach ($points as $pi => $point)
                                @php
                                    $se = $rowCfu->get($point->id);
                                    $kesimpulan = in_array($se?->kesimpulan, ['MS', 'TMS'], true) ? $se->kesimpulan : 'MS / TMS';
                                @endphp
                                <tr>
                                    @if ($pi === 0)
                                        <td rowspan="{{ $pointCount }}" class="tc">{{ $row?->personnel_name ?? 'N/A' }}</td>
                                        <td rowspan="{{ $pointCount }}" class="tc">{{ $timeVal }}</td>
                                        <td rowspan="{{ $pointCount }}" class="tl" style="padding-left:4px;white-space:normal;word-break:normal">
                                            @if ($method->activities->isNotEmpty())
                                                @foreach ($method->activities as $act)
                                                    @php $isChecked = in_array((string) $act->id, $rowActivities, true); @endphp
                                                    <div>{{ $isChecked ? '☑' : '☐' }} {{ $act->activity }}</div>
                                                @endforeach
                                            @else
                                                <div>N/A</div>
                                            @endif
                                        </td>
                                    @endif
                                    <td class="tc" style="white-space:normal;word-break:normal">{{ $point->sampling_point ?? 'N/A' }}</td>
                                    @if ($pi === 0)
                                        <td rowspan="{{ $pointCount }}" class="tc">{{ $rowClass }}</td>
                                    @endif
                                    <td class="tc">{{ $se?->cfu_bacteria ?? '' }}</td>
                                    <td class="tc">{{ $se?->cfu_fungi ?? '' }}</td>
                                    <td class="tc">{{ $se?->cfu_total ?? '' }}</td>
                                    <td class="tc">{{ $kesimpulan }}</td>
                                </tr>
                            @endforeach
                        @endfor
                    </tbody>
                </table>
            @endforeach

            @if ($pageNum >= 2)
                <div style="margin-top:6px;font-size:8pt;line-height:1.5">
                    <div style="display:flex;align-items:flex-start;gap:5px;margin-bottom:2px;">
                        <span class="fw" style="display:inline-block;width:48px">Catatan</span>
                        <span>:</span>
                        <span style="display:inline-block;min-height:10px">{{ $pageNote['note'] ?: '' }}</span>
                    </div>
                    <div style="display:flex;align-items:flex-start;gap:5px;">
                        <span class="fw" style="display:inline-block;width:48px">Deviasi</span>
                        <span>:</span>
                        <span style="display:inline-block;min-height:10px">{{ $pageNote['deviation'] ?: '' }}</span>
                    </div>
                </div>

                <div style="font-size:7.5pt;margin-top:8px;line-height:1.5">
                    Keterangan :<br>
                    B : Total Bakteri<br>
                    F : Total Fungi<br>
                    T : Total Bakteri + Fungi<br>
                    MS : Memenuhi Spesifikasi<br>
                    TMS : Tidak Memenuhi Spesifikasi<br>
                    <sup>1)</sup> Lingkari salah satu
                </div>
            @endif
            <div class="pg-footer"></div>
        </div>
    @endforeach

    @php
        $personnelRows = $personnelInstances->flatMap->rows;
        $personnelSamplingEntries = $personnelRows->flatMap->samplingEntries;
        $hasPersonnelConclusion = $personnelSamplingEntries
            ->contains(fn ($entry) => in_array($entry->kesimpulan, ['MS', 'TMS'], true));
        $hasPersonnelTms = $personnelSamplingEntries
            ->contains(fn ($entry) => $entry->kesimpulan === 'TMS');

        $conclusionText = null;
        if ($hasPersonnelConclusion) {
            $conclusionText = $hasPersonnelTms
                ? 'TIDAK MEMENUHI SPESIFIKASI'
                : 'MEMENUHI SPESIFIKASI';
        }

        $monitoringPersonnelSigs = $report->personnelSignatures
            ->where('role', 'monitoring')
            ->filter(fn ($sig) => $sig->user)
            ->sortBy(fn ($sig) => $sig->signed_at?->getTimestamp() ?? $sig->created_at?->getTimestamp() ?? 0)
            ->values();

        $readingPersonnelSigs = $report->personnelSignatures
            ->where('role', 'reading')
            ->filter(fn ($sig) => $sig->user)
            ->sortBy(fn ($sig) => $sig->signed_at?->getTimestamp() ?? $sig->created_at?->getTimestamp() ?? 0)
            ->values();

        $latestApprovalByStep = static function (int $step) use ($report) {
            return $report->approvals
                ->where('step', $step)
                ->sortByDesc(fn ($row) => $row->updated_at?->getTimestamp() ?? $row->created_at?->getTimestamp() ?? 0)
                ->first();
        };

        $personnelSupApproval = $latestApprovalByStep(2);
        $personnelMngrApproval = $latestApprovalByStep(3);
    @endphp

    <div class="doc-page portrait">
        <div class="pg-hdr">
            <div class="doc-num">{{ $report->reportType->annex_number }}</div>
            <div class="doc-title">{{ strtoupper($report->reportType->name) }}</div>
            <hr class="doc-title-line">
        </div>

        <div style="margin-top:6px;margin-bottom:8px;font-size:8pt">
            <span class="fw">Kesimpulan</span>
            :
            @if ($conclusionText)
                {{ $conclusionText }}
            @else
                MEMENUHI SPESIFIKASI / TIDAK MEMENUHI SPESIFIKASI<sup>1)</sup>
            @endif
        </div>

        <table class="dt sig-tbl" style="margin-top:10px">
            <tr>
                <td style="width:25%;font-weight:700">Dimonitoring oleh:</td>
                <td style="width:25%;font-weight:700">Dibaca oleh:</td>
                <td style="width:25%;font-weight:700">Direview oleh:</td>
                <td style="width:25%;font-weight:700">Disetujui oleh:</td>
            </tr>
            <tr>
                <td style="height:24mm;vertical-align:top;text-align:center">
                    <div style="padding-top:4mm">
                        @foreach ($monitoringPersonnelSigs as $sig)
                            <div>
                                <div style="font-weight:700">{{ $sig->user->name }}</div>
                                @if ($sig->signed_at)
                                    <div style="font-size:16px;line-height:1">&#10003;</div>
                                    <div style="font-size:10px">{{ $sig->signed_at->isoFormat('D MMM Y') }}</div>
                                    <div style="font-size:10px">{{ $sig->signed_at->isoFormat('HH:mm') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </td>
                <td style="height:24mm;vertical-align:top;text-align:center">
                    <div style="padding-top:4mm">
                        @foreach ($readingPersonnelSigs as $sig)
                            <div>
                                <div style="font-weight:700">{{ $sig->user->name }}</div>
                                @if ($sig->signed_at)
                                    <div style="font-size:16px;line-height:1">&#10003;</div>
                                    <div style="font-size:10px">{{ $sig->signed_at->isoFormat('D MMM Y') }}</div>
                                    <div style="font-size:10px">{{ $sig->signed_at->isoFormat('HH:mm') }}</div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </td>
                <td style="height:24mm;vertical-align:middle;text-align:center">
                    @if ($personnelSupApproval?->user)
                        <div style="font-weight:700">{{ $personnelSupApproval->user->name }}</div>
                        @if ($personnelSupApproval->signed_at)
                            <div style="font-size:16px;line-height:1">&#10003;</div>
                            <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($personnelSupApproval->signed_at)->isoFormat('D MMM Y') }}</div>
                            <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($personnelSupApproval->signed_at)->isoFormat('HH:mm') }}</div>
                        @endif
                    @endif
                </td>
                <td style="height:24mm;vertical-align:middle;text-align:center">
                    @if ($personnelMngrApproval?->user)
                        <div style="font-weight:700">{{ $personnelMngrApproval->user->name }}</div>
                        @if ($personnelMngrApproval->signed_at)
                            <div style="font-size:16px;line-height:1">&#10003;</div>
                            <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($personnelMngrApproval->signed_at)->isoFormat('D MMM Y') }}</div>
                            <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($personnelMngrApproval->signed_at)->isoFormat('HH:mm') }}</div>
                        @endif
                    @endif
                </td>
            </tr>
            <tr>
                <td>(Analis Lab. Mikrobiologi)</td>
                <td>(Analis Lab. Mikrobiologi)</td>
                <td>(Supervisor Mikrobiologi)</td>
                <td>(Jr. Manager Lab. Mikrobiologi)</td>
            </tr>
        </table>
        <div style="font-size:7.5pt;margin-top:8px"><sup>1)</sup> Lingkari salah satu</div>
        <div class="pg-footer"></div>
    </div>
@endif
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

    // Center content horizontally with small breathing room (or none for preview-only mode)
    var vw      = window.innerWidth || document.documentElement.clientWidth;
    var scaledW = naturalW * pageZoom;
    var minLeft = {{ $printPreviewOnly ? '0' : '16' }};
    wrap.style.marginLeft = Math.max(minLeft, (vw - scaledW) / 2) + 'px';

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
    // Account for #zoom-outer horizontal padding
    var outerPadding = {{ $printPreviewOnly ? '0' : '32' }};
    var totalWidth   = maxPagePx + outerPadding;
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
    @if($autoPrint ?? false)
    window.print();
    @endif
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
