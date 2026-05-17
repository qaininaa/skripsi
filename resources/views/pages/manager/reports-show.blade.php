@extends('layouts.app')

@section('title', 'Tinjau Laporan')
@section('page-title', 'Tinjau Laporan')
@section('content')
@php
    $sectionNotesBySectionInstance = $report->sectionNotes
        ->keyBy(fn ($row) => (string) $row->section_id . '|' . (int) ($row->instance_number ?? 1));
@endphp

<div class="space-y-4">

    {{-- Back link + Header --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
        <a href="{{ route('manager.incoming-reports') }}"
           class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-colors">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
        </a>
        <div>
            <h2 class="text-lg font-semibold text-gray-800">
                {{ $report->reportType->annex_number ?? '' }}
                <span class="text-gray-400 font-normal mx-1">—</span>
                <span class="text-base font-normal text-gray-600">{{ $report->reportType->name ?? '—' }}</span>
            </h2>
            <p class="text-sm text-gray-500 mt-0.5">
                {{ $report->product_name }} · Batch <span>{{ $report->batch_number }}</span>
                ·  {{ $report->created_at->isoFormat('D MMM Y') }}
            </p>
        </div>
        </div>
        <a href="{{ route('manager.reports.print', $report->id) }}" target="_blank"
           class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors shadow-sm flex-shrink-0">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Generate Dokumen
        </a>
    </div>


    {{-- Approval status strip --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm px-5 py-3 flex flex-wrap items-center gap-3">
        <p class="text-xs text-gray-400 font-medium">Status Tinjauan:</p>
        @if ($approval->isPending())
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">Menunggu Review</span>
        @elseif ($approval->isApproved())
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">Disetujui</span>
            @if ($approval->signed_at)
                <span class="text-xs text-gray-400">pada {{ $approval->signed_at->isoFormat('D MMM Y, HH:mm') }}</span>
            @endif
        @elseif ($approval->isReturned())
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">Dikembalikan ke Supervisor</span>
        @else
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">Ditolak</span>
        @endif
        @if ($approval->notes)
            <span class="text-xs text-gray-500 border-l border-gray-200 pl-3">&ldquo;{{ $approval->notes }}&rdquo;</span>
        @endif
    </div>

    {{-- ── 1. Pemantauan Ruang ────────────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">1. Pemantauan Ruang</h3>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Pemantauan Ruang</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    {{ $report->created_at->isoFormat('D MMMM Y') }}
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dimonitoring Oleh</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    @php
                        $monitoringNames = $report->analysts
                            ->where('type', 'monitoring')
                            ->map(fn ($a) => $a->user?->name)
                            ->filter()
                            ->unique()
                            ->values();
                    @endphp
                    {{ $monitoringNames->isNotEmpty() ? $monitoringNames->join(', ') : '—' }}
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Dibaca Oleh</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    @php
                        $readingNames = $report->analysts
                            ->where('type', 'reading')
                            ->map(fn ($a) => $a->user?->name)
                            ->filter()
                            ->unique()
                            ->values();
                    @endphp
                    {{ $readingNames->isNotEmpty() ? $readingNames->join(', ') : '—' }}
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Produk</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $report->product_name }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Produk</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $report->batch_number ?: '—' }}</div>
            </div>
        </div>
    </div>

    {{-- ── 2. Identitas Instrumen — Air Sampler ──────────── --}}
    @if ($needsAirSampler)
    @php $as = $report->instrumentEntries->firstWhere('tool_name', 'Air Sampler'); @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">2. Identitas Instrumen</h3>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">{{ $as?->tool_name ?? 'Air Sampler' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Air Sampler</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $as?->no_id ?? '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Air Sampler</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $as?->calibration_date ? $as->calibration_date->format('d/m/Y') : '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Air Sampler</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $as?->due_date ? $as->due_date->format('d/m/Y') : '—' }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── 3. Identitas Medium ────────────────────────────── --}}
    @php
        $mediumTypeList = $report->relationLoaded('reportType') ? $report->reportType->mediumTypes : collect();
        $mediumByName = $report->mediumIdentities->keyBy('name');
    @endphp
    @if ($needsMedium && $mediumTypeList->isNotEmpty())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">3. Identitas Medium</h3>
        </div>
        <div class="p-5 grid grid-cols-1 gap-6 {{ $mediumTypeList->count() > 2 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
            @foreach ($mediumTypeList as $medType)
            @php
                $medName = $medType->name;
                $med = $mediumByName->get($medName);
                $isSwab = str_contains(strtolower($medName), 'swab');
            @endphp
            <div>
                <h4 class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-3">{{ $medName }}</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Medium</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $med?->batch_number ?? '—' }}</div>
                    </div>
                    @if (! $isSwab)
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nomor GPT Medium</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $med?->gpt_number ?? '—' }}</div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal ED {{ $isSwab ? 'Swab Kit' : 'Medium' }}</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $med?->expiration_date ? $med->expiration_date->format('d/m/Y') : '—' }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── 4. Proses Inkubasi Medium Monitoring ──────────── --}}
    @if ($needsInkubator)
    @php
        $incubatorByRtiId = $report->incubators->keyBy('report_type_incubator_id');
    @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">4. Proses Inkubasi Medium Monitoring</h3>
        </div>
        @foreach ($report->reportType->incubatorTypes->sortByDesc('min_day') as $inkRti)
        @php
            $inkRecord = $incubatorByRtiId[$inkRti->id] ?? null;
            $inkEntry = ($inkRecord?->entries ?? collect())->firstWhere('medium_type', 'monitoring');
            $inkLabel = $inkRti->temperature_label;
            $inkMin = $inkRti->min_day;
        @endphp
        <div class="p-5 space-y-4 @if(!$loop->last) border-b border-gray-100 @endif">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">{{ $inkLabel }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">{{ $inkLabel }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkRecord?->no_id ?? '—' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkRecord?->calibration_date ? $inkRecord->calibration_date->format('d/m/Y') : '—' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkRecord?->due_date_calibration ? $inkRecord->due_date_calibration->format('d/m/Y') : '—' }}</div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-50">
                <div class="space-y-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Inkubasi Medium (min {{ $inkMin }} hari)</label>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Diinkubasi oleh</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkEntry?->incubatedBy?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkEntry?->date_in ? $inkEntry->date_in->format('d/m/Y') : '—' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkEntry?->time_in ?? '—' }}</div>
                    </div>
                </div>
                <div class="space-y-3">
                    <label class="block text-xs font-medium text-gray-500 mb-1">&nbsp;</label>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dikeluarkan oleh</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkEntry?->removedBy?->name ?? '—' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkEntry?->date_out ? $inkEntry->date_out->format('d/m/Y') : '—' }}</div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $inkEntry?->time_out ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Tabel Pengukuran per Section ────────────────────── --}}
    @foreach ($report->reportType->sections as $section)
    @php
        // CFU helpers (values are varchar: '<1', 'TNTC', or integer string)
        $cfuNum = function(?string $v): ?int {
            if ($v === null || $v === '') return null;
            if (strtoupper($v) === 'TNTC') return PHP_INT_MAX;
            if ($v === '<1') return 0;
            if (is_numeric($v)) return (int)$v;
            return null;
        };
        $cfuTot = function(?string $b, ?string $f) use ($cfuNum): ?string {
            if ($b === null && $f === null) return null;
            if (strtoupper((string)$b) === 'TNTC' || strtoupper((string)$f) === 'TNTC') return 'TNTC';
            $bn = ($b === '<1') ? 0 : ($b !== null && is_numeric($b) ? (int)$b : null);
            $fn = ($f === '<1') ? 0 : ($f !== null && is_numeric($f) ? (int)$f : null);
            if ($bn === null && $fn === null) return null;
            $sum = ($bn ?? 0) + ($fn ?? 0);
            if ($sum === 0 && ($b === '<1' || $f === '<1')) return '<1';
            return (string)$sum;
        };
    @endphp
    @php
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
        $secNum        = $loop->index + 5;
        $secAssignments = [];
        for ($c = 1; $c <= $maxCols; $c++) {
            $secAssignments[$c] = 1;
        }
        $secColumnNames = $report->sectionColumnNames
            ->where('section_id', $section->id)
            ->where('instance_number', 1)
            ->keyBy(fn($r) => (int) $r->period_number)
            ->map(fn($r) => $r->label)
            ->all();
        $secNoteRow = $sectionNotesBySectionInstance->get((string) $section->id . '|1');
        $secNote = [
            'notes' => $secNoteRow?->notes,
            'conclusion' => $secNoteRow?->conclusion,
        ];
        $subColsPerExp = $isPerLocation ? 4 : 3;

        // Build time lookup from report_environmental_entries (normalized source).
        $secTimesFromEntries = [];
        foreach ($section->locations as $_loc) {
            $_pivId  = $_loc->id;
            $_class  = strtolower($_loc->room->class ?? '');
            $_locNum = $_loc->location_number ?? '';
            $_isS1_3 = stripos($_locNum, 'S1-3') !== false;
            $_isS1_2 = stripos($_locNum, 'S1-2') !== false;
            $_swK    = $_isS1_3 ? 's1_3' : ($_isS1_2 ? 's1_2' : 's1');
            for ($_col = 0; $_col <= $maxCols; $_col++) {
                $_e = $entryMap[$_pivId][$_col][1] ?? $entryMap[$_pivId][$_col][2] ?? null;
                if (! $_e || (! $_e->start_time && ! $_e->end_time)) { continue; }
                if ($_class && ! isset($secTimesFromEntries[$_col][$_class])) {
                    $secTimesFromEntries[$_col][$_class] = ['start_time' => $_e->start_time, 'end_time' => $_e->end_time];
                }
                if (! isset($secTimesFromEntries[$_col]['swab'][$_swK])) {
                    $secTimesFromEntries[$_col]['swab'][$_swK] = ['mulai' => $_e->start_time, 'selesai' => $_e->end_time];
                }
                if (! isset($secTimesFromEntries[$_col]['start_time'])) {
                    $secTimesFromEntries[$_col]['start_time'] = $_e->start_time;
                    $secTimesFromEntries[$_col]['end_time']   = $_e->end_time;
                }
            }
        }
    @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden">
        <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
            <div class="h-7 w-7 rounded-lg bg-emerald-50 flex items-center justify-center flex-shrink-0">
                <span class="text-xs font-bold text-emerald-600">{{ $secNum }}</span>
            </div>
            <div>
                <h3 class="font-semibold text-sm text-gray-700">{{ $section->measurement_unit }}</h3>
                <p class="text-xs text-gray-400 mt-0.5">{{ $section->name }}</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-xs border-collapse">
                <thead>
                    <tr class="bg-emerald-50 text-gray-600 border-b border-emerald-100">
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">No.</th>
                        <th class="px-3 py-2 text-left font-semibold border-r border-emerald-100" rowspan="3">Nama Ruangan</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">Kelas</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">No. Ruangan</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">No.<br>Lokasi</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100"
                            colspan="{{ ($hasMachineSetup ? 3 : 0) + $maxCols * $subColsPerExp }}">
                            {{ $section->measurement_unit }}
                        </th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" colspan="2" rowspan="2">Batas<br>Alert</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" colspan="2" rowspan="2">Batas<br>Tindakan</th>
                        <th class="px-2 py-2 text-center font-semibold whitespace-nowrap" rowspan="3">Kesimpulan</th>
                    </tr>
                    <tr class="bg-emerald-50 text-gray-600 border-b border-emerald-100">
                        @if ($hasMachineSetup)
                        @php
                            $msJamMulai = null; $msJamSelesai = null;
                            foreach ($section->locations as $loc2) {
                                $e0 = $entryMap[$loc2->id][0][1] ?? $entryMap[$loc2->id][0][2] ?? null;
                                if ($e0 && ($e0->start_time || $e0->end_time)) {
                                    $msJamMulai   = $e0->start_time;
                                    $msJamSelesai = $e0->end_time;
                                    break;
                                }
                            }
                        @endphp
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100" colspan="3">
                            <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">Machine Set-up</div>
                            <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                                {{ $msJamMulai ? $msJamMulai . ' – ' . ($msJamSelesai ?? '—') : '—' }}
                            </div>
                        </th>
                        @endif
                        @for ($col = 1; $col <= $maxCols; $col++)
                        @php
                            $colAsgn = $secAssignments[$col] ?? 1;
                            $colName = $secColumnNames[$col] ?? null;
                        @endphp
                        <th class="px-2 py-1.5 text-center font-semibold border-r border-emerald-100 whitespace-nowrap"
                            colspan="{{ $subColsPerExp }}">
                            @php
                                $hasColLabel = (($colLabel ?? '') !== '');
                                $colPeriod = ($hasColLabel && $maxCols > 1) ? ($romanNums[$col - 1] ?? $col) : '';
                                $colHeaderTitle = trim((($colLabel ?? '') !== '' ? ($colLabel . ' ') : '') . $colPeriod);
                            @endphp
                            {{ $colHeaderTitle }}
                            <div class="text-[10px] text-gray-500 mt-1">
                                {{ $isSettlePlate ? 'SP' : 'Shift' }}: {{ $colName ?: '—' }}
                            </div>
                            @if ($isSwabTime)
                            @php $swabColTimes = $secTimesFromEntries[$col]['swab'] ?? []; @endphp
                            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                                @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                                @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                                <div>{{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: '—' }} – {{ ($st['selesai'] ?? '') ?: '—' }}</div>
                                @endforeach
                            </div>
                            @endif
                            @if ($isDualAB)
                            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                                @php $stAB = $secTimesFromEntries[$col][$ab] ?? []; @endphp
                                <div>{{ $abLabel }}: {{ ($stAB['start_time'] ?? '') ?: '—' }} – {{ ($stAB['end_time'] ?? '') ?: '—' }}</div>
                                @endforeach
                            </div>
                            @endif
                            @if ($hasJam)
                            @php
                                $expJamMulai   = $secTimesFromEntries[$col]['start_time'] ?? null;
                                $expJamSelesai = $secTimesFromEntries[$col]['end_time'] ?? null;
                            @endphp
                            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                                <div>Mulai: {{ $expJamMulai ?: '—' }}</div>
                                <div>Selesai: {{ $expJamSelesai ?: '—' }}</div>
                            </div>
                            @endif
                        </th>
                        @endfor
                    </tr>
                    <tr class="bg-emerald-50/60 text-gray-500 border-b border-gray-200">
                        @if ($hasMachineSetup)
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">B</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">T</th>
                        @endif
                        @for ($col = 1; $col <= $maxCols; $col++)
                        @if ($isPerLocation)
                            <th class="px-1.5 py-1.5 text-center font-medium border-r border-emerald-100 whitespace-nowrap">JAM</th>
                        @endif
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">B</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">T</th>
                        @endfor
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">T</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">T</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($section->locations as $loc)
                    @php
                        $locEntries = collect();
                        for ($p = 0; $p <= $section->max_column; $p++) {
                            for ($s = 1; $s <= 2; $s++) {
                                if (isset($entryMap[$loc->id][$p][$s])) {
                                    $locEntries->push($entryMap[$loc->id][$p][$s]);
                                }
                            }
                        }
                        $maxT   = $locEntries->max(fn($e) => ($cfuNum($e->cfu_bacteria) ?? 0) + ($cfuNum($e->cfu_fungi) ?? 0)) ?? 0;
                        $maxF   = $locEntries->max(fn($e) => $cfuNum($e->cfu_fungi) ?? 0) ?? 0;
                        $hasTMS = ($loc->alert_action_total && $maxT >= $loc->alert_action_total)
                               || ($loc->alert_action_fungi && $maxF >= $loc->alert_action_fungi);
                        $hasAlt = !$hasTMS && (
                                    ($loc->alert_limit_total && $maxT >= $loc->alert_limit_total)
                                 || ($loc->alert_limit_fungi    && $maxF >= $loc->alert_limit_fungi));
                        $hasCfuEntries = $locEntries->contains(fn($e) => $e->cfu_bacteria !== null || $e->cfu_fungi !== null);
                        $konklusi = $hasCfuEntries ? ($hasTMS ? 'TMS' : ($hasAlt ? 'Alert' : 'MS')) : null;

                        $classBadge = match($loc->room->class) {
                            'A' => 'bg-purple-100 text-purple-700',
                            'B' => 'bg-blue-100 text-blue-700',
                            'C' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-emerald-50/20 transition-colors">
                        <td class="px-2 py-2.5 text-center text-gray-400 border-r border-gray-100">{{ $loop->iteration }}</td>
                        <td class="px-3 py-2.5 text-gray-700 font-medium border-r border-gray-100 whitespace-nowrap">{{ $loc->room->room_name }}</td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="inline-flex items-center justify-center h-5 w-5 rounded text-[11px] font-bold {{ $classBadge }}">{{ $loc->room->class }}</span>
                        </td>
                        <td class="px-2 py-2.5 text-center text-gray-500 border-r border-gray-100 whitespace-nowrap text-[11px]">{{ $loc->room->room_number }}</td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            @if (str_starts_with($loc->location_number, '*)'))
                                <span class="text-[11px] text-gray-400 italic">{{ $loc->location_number }}</span>
                            @else
                                <span class="text-[11px] text-gray-500">{{ $loc->location_number }}</span>
                            @endif
                        </td>

                        @if ($hasMachineSetup)
                        @php
                            $msEntry = $entryMap[$loc->id][0][1] ?? $entryMap[$loc->id][0][2] ?? null;
                            $msTVal = $cfuTot($msEntry?->cfu_bacteria, $msEntry?->cfu_fungi);
                        @endphp
                        <td class="px-1 py-2 border-r border-gray-100 text-center">
                            <span class="text-[11px] {{ $msEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        </td>
                        <td class="px-1 py-2 border-r border-gray-100 text-center">
                            <span class="text-[11px] {{ $msEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_fungi ?? '—' }}
                            </span>
                        </td>
                        <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                            <span class="text-[11px] font-semibold {{ $msTVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
                                {{ $msTVal ?? '—' }}
                            </span>
                        </td>
                        @endif

                        @for ($col = 1; $col <= $maxCols; $col++)
                        @php
                            $colAsgn    = $secAssignments[$col] ?? 1;
                            $existEntry = $entryMap[$loc->id][$col][$colAsgn] ?? null;
                            $tVal = $cfuTot($existEntry?->cfu_bacteria, $existEntry?->cfu_fungi);
                        @endphp

                        @if ($isPerLocation)
                        <td class="px-1 py-2 border-r border-gray-100 text-center">
                            <span class="text-gray-{{ $existEntry?->start_time ? '600' : '300' }} text-[11px]">
                                {{ $existEntry?->start_time ? \Illuminate\Support\Str::substr($existEntry->start_time, 0, 5) : '-' }}
                            </span>
                        </td>
                        @endif

                        <td class="px-1 py-2 border-r border-gray-100 text-center">
                            <span class="text-[11px] {{ $existEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        </td>
                        <td class="px-1 py-2 border-r border-gray-100 text-center">
                            <span class="text-[11px] {{ $existEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_fungi ?? '—' }}
                            </span>
                        </td>
                        <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                            <span class="text-[11px] font-semibold {{ $tVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
                                {{ $tVal ?? '—' }}
                            </span>
                        </td>
                        @endfor

                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->alert_limit_total !== null ? 'text-amber-700' : 'text-gray-300' }}">
                                {{ $loc->alert_limit_total ?? '—' }}
                            </span>
                        </td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->alert_limit_fungi !== null ? 'text-amber-700' : 'text-gray-300' }}">
                                {{ $loc->alert_limit_fungi ?? '—' }}
                            </span>
                        </td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->alert_action_total !== null ? 'text-red-600' : 'text-gray-300' }}">
                                {{ $loc->alert_action_total !== null ? ($loc->alert_action_total == 1 ? '<1' : $loc->alert_action_total) : '—' }}
                            </span>
                        </td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->alert_action_fungi !== null ? 'text-red-600' : 'text-gray-300' }}">
                                {{ $loc->alert_action_fungi !== null ? ($loc->alert_action_fungi == 1 ? '<1' : $loc->alert_action_fungi) : '—' }}
                            </span>
                        </td>
                        <td class="px-2 py-2.5 text-center">
                            @if ($konklusi === 'TMS')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700">TMS</span>
                            @elseif ($konklusi === 'Alert')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">Alert</span>
                            @elseif ($konklusi === 'MS')
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">MS</span>
                            @else
                                <span class="text-gray-300 text-[11px]">—</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-5 py-3 border-t border-gray-100 text-[11px] text-gray-400">
            @if ($section->measurement_key === 'swab')
            <p class="mb-1"><span class="text-gray-500">*)</span> diisi jika dibutuhkan</p>
            @endif
            <strong class="text-gray-500">Keterangan:</strong>
            B: Total Bakteri &nbsp;·&nbsp; F: Total Fungi &nbsp;·&nbsp; T: Total Bakteri + Fungi &nbsp;·&nbsp;
            MS: Memenuhi Spesifikasi &nbsp;·&nbsp; TMS: Tidak Memenuhi Spesifikasi
        </div>

        <div class="px-5 py-4 border-t border-gray-100 space-y-3">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700 min-h-[40px]">
                    {{ $secNote['notes'] ?? '—' }}
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1.5">Kesimpulan</label>
                @php $sk = $secNote['conclusion'] ?? ''; @endphp
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 inline-block text-xs">
                    @if ($sk === 'MS')
                        <span class="text-green-700 font-semibold">Memenuhi Spesifikasi (MS)</span>
                    @elseif ($sk === 'TMS')
                        <span class="text-red-700 font-semibold">Tidak Memenuhi Spesifikasi (TMS)</span>
                    @else
                        <span class="text-gray-400">Belum ditentukan</span>
                    @endif
                </div>
            </div>
        </div>

        {{-- ── Per-section TTD ─────────────────────────────── --}}
        @php
            $_sectionSigs = $report->sectionSignatures->where('section_id', $section->id);
            $_secMonSigs = $_sectionSigs->where('role', 'monitoring')->sortBy('signed_at');
            $_secReadSigs = $_sectionSigs->where('role', 'reading')->sortBy('signed_at');

            $_secMonIds = array_values(array_unique(
                $_secMonSigs->pluck('user_id')->filter()->map(fn ($id) => (string) $id)->all()
            ));
            $_secReadIds = array_values(array_unique(
                $_secReadSigs->pluck('user_id')->filter()->map(fn ($id) => (string) $id)->all()
            ));

            $_secMonTs = [];
            foreach ($_secMonSigs as $_sig) {
                if ($_sig->user_id && $_sig->signed_at) {
                    $_secMonTs[(string) $_sig->user_id] = $_sig->signed_at;
                }
            }
            $_secReadTs = [];
            foreach ($_secReadSigs as $_sig) {
                if ($_sig->user_id && $_sig->signed_at) {
                    $_secReadTs[(string) $_sig->user_id] = $_sig->signed_at;
                }
            }

            $_supApproval  = $report->approvals->firstWhere('step', 2);
            $_mngrApproval = $report->approvals->firstWhere('step', 3);
            $_secUniqueIds = array_unique(array_filter(array_merge($_secMonIds, $_secReadIds)));
            $_secUserMap   = \Domain\User\Models\User::whereIn('id', $_secUniqueIds)->get()->keyBy('id');
            $_sectionHasData = !empty($_secMonIds) || !empty($_secReadIds);
        @endphp
        <div class="px-5 py-4 border-t border-gray-100">
            <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Tanda Tangan & Verifikasi</p>
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
                {{-- Dimonitoring oleh --}}
                <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                    <p class="text-[11px] font-semibold text-gray-600 mb-2">Dimonitoring oleh:</p>
                    <div class="flex-1 flex flex-col gap-2 justify-center">
                        @forelse ($_secMonIds as $_uid)
                        @php $_u = $_secUserMap->get($_uid); $_ts = isset($_secMonTs[$_uid]) ? \Illuminate\Support\Carbon::parse($_secMonTs[$_uid]) : null; @endphp
                        @if ($_u)
                        <div class="text-center">
                            @if ($_ts)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Tersimpan
                            </span>
                            @endif
                            <p class="text-sm font-semibold text-gray-700">{{ $_u->name }}</p>
                            @if ($_ts)
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $_ts->isoFormat('D MMM Y, HH:mm') }}</p>
                            @endif
                        </div>
                        @endif
                        @empty
                        <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                        @endforelse
                    </div>
                    <p class="text-[10px] text-gray-400 text-center mt-2">(Analis Lab. Mikrobiologi)</p>
                </div>
                {{-- Dibaca oleh --}}
                <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                    <p class="text-[11px] font-semibold text-gray-600 mb-2">Dibaca oleh:</p>
                    <div class="flex-1 flex flex-col gap-2 justify-center">
                        @forelse ($_secReadIds as $_uid)
                        @php $_u = $_secUserMap->get($_uid); $_ts = isset($_secReadTs[$_uid]) ? \Illuminate\Support\Carbon::parse($_secReadTs[$_uid]) : null; @endphp
                        @if ($_u)
                        <div class="text-center">
                            @if ($_ts)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Tersimpan
                            </span>
                            @endif
                            <p class="text-sm font-semibold text-gray-700">{{ $_u->name }}</p>
                            @if ($_ts)
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ $_ts->isoFormat('D MMM Y, HH:mm') }}</p>
                            @endif
                        </div>
                        @endif
                        @empty
                        <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                        @endforelse
                    </div>
                    <p class="text-[10px] text-gray-400 text-center mt-2">(Analis Lab. Mikrobiologi)</p>
                </div>
                {{-- Direview oleh --}}
                <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                    <p class="text-[11px] font-semibold text-gray-600 mb-2">Direview oleh:</p>
                    <div class="flex-1 flex flex-col gap-2 justify-center">
                        @if ($_sectionHasData && $_supApproval?->user)
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-700">{{ $_supApproval->user->name }}</p>
                            @if ($_supApproval->signed_at)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Disetujui
                            </span>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ \Illuminate\Support\Carbon::parse($_supApproval->signed_at)->isoFormat('D MMM Y, HH:mm') }}</p>
                            @endif
                        </div>
                        @else
                        <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                        @endif
                    </div>
                    <p class="text-[10px] text-gray-400 text-center mt-2">(Supervisor Mikrobiologi)</p>
                </div>
                {{-- Disetujui oleh --}}
                <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                    <p class="text-[11px] font-semibold text-gray-600 mb-2">Disetujui oleh:</p>
                    <div class="flex-1 flex flex-col gap-2 justify-center">
                        @if ($_sectionHasData && $_mngrApproval?->user)
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-700">{{ $_mngrApproval->user->name }}</p>
                            @if ($_mngrApproval->signed_at)
                            <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Disetujui
                            </span>
                            <p class="text-[11px] text-gray-500 mt-0.5">{{ \Illuminate\Support\Carbon::parse($_mngrApproval->signed_at)->isoFormat('D MMM Y, HH:mm') }}</p>
                            @endif
                        </div>
                        @else
                        <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                        @endif
                    </div>
                    <p class="text-[10px] text-gray-400 text-center mt-2">(QC Manager)</p>
                </div>
            </div>
        </div>
    </div>
    @endforeach

    {{-- ── Aksi: Setujui / Kembalikan ──────────────────────────── --}}
    @if ($approval->isPending())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tindakan</h3>

        @if ($errors->has('auth_error'))
        <div class="mb-4 px-4 py-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-700">
            {{ $errors->first('auth_error') }}
        </div>
        @endif

        <div class="flex flex-col gap-3">

            {{-- Approve --}}
            <div class="rounded-xl border-2 border-emerald-200 bg-emerald-50 p-4 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-emerald-800">Setujui Laporan</p>
                        <p class="text-xs text-emerald-600">Laporan akan ditandai sebagai disetujui (final)</p>
                    </div>
                </div>
                <button type="button" onclick="openConfirmModal('approve')"
                    class="px-5 py-2.5 rounded-lg bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 active:bg-emerald-700 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Setujui Laporan
                </button>
            </div>

            {{-- Return to Supervisor / Analyst --}}
            @if ($supervisor || $monitoringUsers->isNotEmpty())
            <div class="rounded-xl border-2 border-orange-200 bg-orange-50 p-4 flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-orange-800">Kembalikan Laporan</p>
                        <p class="text-xs text-orange-600">Kirim kembali ke Supervisor atau Analis untuk diperbaiki</p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <select id="return-target-select"
                        class="flex-1 rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm focus:border-orange-400 focus:ring-1 focus:ring-orange-400 focus:outline-none">
                        <option value="">-- Pilih Supervisor/Analis --</option>
                        @if ($supervisor)
                        <option value="supervisor:{{ $supervisor->id }}">{{ $supervisor->name }} (Supervisor)</option>
                        @endif
                        @foreach ($monitoringUsers as $analyst)
                        <option value="analyst:{{ $analyst->id }}">{{ $analyst->name }} (Analis)</option>
                        @endforeach
                    </select>
                    <textarea id="return-notes-input" placeholder="Catatan / alasan pengembalian (opsional)"
                        class="flex-1 rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm resize-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 focus:outline-none"
                        rows="1"></textarea>
                    <button type="button" onclick="openConfirmModal('return')"
                        class="px-5 py-2.5 rounded-lg bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 active:bg-orange-700 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                        Kembalikan
                    </button>
                </div>
            </div>
            @endif

        </div>
    </div>
    @endif

</div>

{{-- ── Modal Konfirmasi Kredensial ──────────────────────────────── --}}
<div id="confirm-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-gray-900/50 backdrop-blur-sm" onclick="closeConfirmModal()"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md p-6">
        <div id="modal-icon-approve" class="hidden flex items-center gap-3 mb-5">
            <div class="h-10 w-10 rounded-full bg-emerald-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </div>
            <div>
                <p class="text-base font-semibold text-gray-800">Konfirmasi Persetujuan Final</p>
                <p class="text-xs text-gray-500">Masukkan kredensial Anda untuk menyetujui laporan ini</p>
            </div>
        </div>
        <div id="modal-icon-return" class="hidden flex items-center gap-3 mb-5">
            <div class="h-10 w-10 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                </svg>
            </div>
            <div>
                <p class="text-base font-semibold text-gray-800">Konfirmasi Pengembalian</p>
                <p id="modal-return-subtitle" class="text-xs text-gray-500">Masukkan kredensial Anda untuk mengembalikan laporan</p>
            </div>
        </div>

        <form id="confirm-form" method="POST" action="">
            @csrf
            <input type="hidden" name="returned_to_user_id" id="modal-returned-to-user-id" value="{{ $supervisor?->id }}">
            <input type="hidden" name="notes" id="modal-notes" value="">

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Username</label>
                    <input type="text" name="username" id="modal-username" autocomplete="username"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 focus:outline-none"
                        placeholder="Masukkan username Anda" required>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-600 mb-1.5">Password</label>
                    <input type="password" name="password" id="modal-password" autocomplete="current-password"
                        class="w-full rounded-lg border border-gray-200 px-3 py-2.5 text-sm focus:border-blue-400 focus:ring-1 focus:ring-blue-400 focus:outline-none"
                        placeholder="Masukkan password Anda" required>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeConfirmModal()"
                    class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50 transition-colors">
                    Batal
                </button>
                <button id="modal-submit-btn" type="submit"
                    class="flex-1 px-4 py-2.5 rounded-lg text-white text-sm font-semibold transition-colors shadow-sm">
                    Konfirmasi
                </button>
            </div>
        </form>
    </div>
</div>

<script type="application/json" id="manager-review-config">
@php
    echo json_encode([
        'approveUrl' => route('manager.reports.approve', $report->id),
        'returnUrl' => route('manager.reports.return', $report->id),
    ], JSON_UNESCAPED_SLASHES);
@endphp
</script>
<script>
const MANAGER_REVIEW_CONFIG = JSON.parse(document.getElementById('manager-review-config').textContent);

function openConfirmModal(action) {
    const modal = document.getElementById('confirm-modal');
    const form  = document.getElementById('confirm-form');
    const iconApprove = document.getElementById('modal-icon-approve');
    const iconReturn  = document.getElementById('modal-icon-return');
    const submitBtn   = document.getElementById('modal-submit-btn');

    document.getElementById('modal-username').value = '';
    document.getElementById('modal-password').value = '';

    if (action === 'approve') {
        form.action = MANAGER_REVIEW_CONFIG.approveUrl;
        iconApprove.classList.remove('hidden');
        iconReturn.classList.add('hidden');
        submitBtn.className = 'flex-1 px-4 py-2.5 rounded-lg text-white text-sm font-semibold transition-colors shadow-sm bg-emerald-500 hover:bg-emerald-600 active:bg-emerald-700';
        document.getElementById('modal-notes').value = '';
    } else if (action === 'return') {
        const select = document.getElementById('return-target-select');
        if (!select.value) {
            alert('Pilih tujuan pengembalian terlebih dahulu.');
            return;
        }
        const [type, id] = select.value.split(':');
        const label = type === 'analyst' ? 'Analis' : 'Supervisor';
        form.action = MANAGER_REVIEW_CONFIG.returnUrl;
        iconApprove.classList.add('hidden');
        iconReturn.classList.remove('hidden');
        submitBtn.className = 'flex-1 px-4 py-2.5 rounded-lg text-white text-sm font-semibold transition-colors shadow-sm bg-orange-500 hover:bg-orange-600 active:bg-orange-700';
        document.getElementById('modal-returned-to-user-id').value = id;
        document.getElementById('modal-return-subtitle').textContent = 'Masukkan kredensial Anda untuk mengembalikan laporan ke ' + label;
        document.getElementById('modal-notes').value = document.getElementById('return-notes-input')?.value ?? '';
    }

    modal.classList.remove('hidden');
    setTimeout(() => document.getElementById('modal-username').focus(), 100);
}

function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeConfirmModal();
});
</script>

@endsection
