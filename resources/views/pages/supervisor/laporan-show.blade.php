@extends('layouts.app')

@section('title', 'Tinjau Laporan')
@section('page-title', 'Tinjau Laporan')
@section('content')
@php $hd = $report->header_data ?? []; @endphp

<div class="space-y-4">

    {{-- Back link + Header --}}
    <div class="flex items-center justify-between gap-3">
        <div class="flex items-center gap-3">
        <a href="{{ route('supervisor.laporan-masuk') }}"
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
        <a href="{{ route('supervisor.laporan.cetak', $report->id) }}" target="_blank"
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
            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-orange-100 text-orange-700">Dikembalikan</span>
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
                <label class="block text-xs font-medium text-gray-500 mb-1">Analis Shift 1</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700 font-medium flex items-center gap-2">
                    {{ $report->shift1Analis->name }}
                    <span class="inline-flex items-center justify-center h-5 w-12 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 flex-shrink-0">Shift 1</span>
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Analis Shift 2</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700 flex items-center gap-2">
                    @if ($report->shift2Analis)
                        {{ $report->shift2Analis->name }}
                    @else
                        <span class="text-gray-400 italic">Belum ditentukan</span>
                    @endif
                    <span class="inline-flex items-center justify-center h-5 w-12 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700 flex-shrink-0">Shift 2</span>
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
    @php $as = $hd['air_sampler'] ?? []; @endphp
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">2. Identitas Instrumen</h3>
        </div>
        <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">Air Sampler</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Air Sampler</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $as['no_id'] ?? '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Air Sampler</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $as['calibration_date'] ?? '—' }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Air Sampler</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $as['due_date'] ?? '—' }}</div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── 3. Identitas Medium ────────────────────────────── --}}
    @php $mediumGroups = $report->reportType->medium_groups ?? []; @endphp
    @if (!empty($mediumGroups))
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">3. Identitas Medium</h3>
        </div>
        <div class="p-5 grid grid-cols-1 gap-6 {{ count($mediumGroups) > 2 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
            @foreach ($mediumGroups as $medKey => $medLabel)
            @php $med = $hd[$medKey] ?? []; @endphp
            <div>
                <h4 class="text-xs font-semibold text-emerald-600 uppercase tracking-wide mb-3">{{ $medLabel }}</h4>
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Medium</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $med['nomor_batch'] ?? '—' }}</div>
                    </div>
                    @if ($medKey !== 'medium_swab')
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Nomor GPT Medium</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $med['nomor_gpt'] ?? '—' }}</div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal ED {{ $medKey === 'medium_swab' ? 'Swab Kit' : 'Medium' }}</label>
                        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $med['expiry_date'] ?? '—' }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ── 4. Proses Inkubasi Medium Monitoring ──────────── --}}
    @if ($needsInkubator)
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">4. Proses Inkubasi Medium Monitoring</h3>
        </div>
        @foreach ([
            'inkubator_20_25' => ['label' => 'Inkubator Suhu 20–25°C', 'min_days' => 3],
            'inkubator_30_35' => ['label' => 'Inkubator Suhu 30–35°C', 'min_days' => 2],
        ] as $inkKey => $inkInfo)
        @php $ink = $hd[$inkKey] ?? []; $inkLabel = $inkInfo['label']; $inkMin = $inkInfo['min_days']; @endphp
        <div class="p-5 space-y-4 @if(!$loop->last) border-b border-gray-100 @endif">
            <p class="text-xs font-semibold text-emerald-600 uppercase tracking-wide">{{ $inkLabel }}</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">{{ $inkLabel }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['no_id'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['calibration_date'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['due_date'] ?? '—' }}</div>
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-gray-50">
                <div class="lg:col-span-2">
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Inkubasi Medium (min {{ $inkMin }} hari)</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['incubation_date'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['date_in'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['time_in'] ?? '—' }}</div>
                </div>
                <div class="hidden lg:block lg:col-span-2"></div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['date_out'] ?? '—' }}</div>
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">{{ $ink['time_out'] ?? '—' }}</div>
                </div>
                <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                    @foreach ([
                        ['key' => 'incubated',  'label' => 'Diinkubasi oleh'],
                        ['key' => 'removed', 'label' => 'Dikeluarkan oleh'],
                    ] as $field)
                    @php $fKey = $field['key']; @endphp
                    <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3 space-y-2">
                        <p class="text-xs font-semibold text-gray-500">{{ $field['label'] }}</p>
                        <div class="text-sm text-gray-700">{{ $ink[$fKey . '_by'] ?? '—' }}</div>
                        <div class="px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm text-gray-700">{{ $ink[$fKey . '_date'] ?? '—' }}</div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ── Tabel Pengukuran per Seksi ────────────────────── --}}
    @foreach ($report->reportType->sections as $section)
    @php
        $isShiftBased  = in_array($section->measurement_type, ['air_sampler', 'contact_plate', 'swab']);
        $isSettlePlate = $section->measurement_type === 'settle_plate';
        $isSwab        = $section->measurement_type === 'swab';
        $hasJam        = $section->measurement_type === 'air_sampler';
        $isPerLocation = $section->time_slot_type === 'per_location';
        $maxCols       = $section->max_exposures;
        $romanNums     = ['I', 'II', 'III', 'IV', 'V', 'VI'];
        $secNum        = $loop->index + 5;
        $savedAsgn     = ($hd['shift_assignments'] ?? [])[$section->id] ?? [];
        $secAssignments = [];
        for ($c = 1; $c <= $maxCols; $c++) {
            $secAssignments[$c] = isset($savedAsgn[$c]) ? (int)$savedAsgn[$c] : 1;
        }
        $secNote = $hd['section_notes'][$section->id] ?? [];
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
            <table class="w-full text-xs border-collapse" style="min-width: {{ 480 + (!$isShiftBased ? 130 : 0) + ($maxCols * ($isShiftBased ? ($hasJam ? 220 : ($isSwab ? 220 : 160)) : ($isPerLocation ? 220 : 130))) }}px">
                <thead>
                    {{-- Row 1: group headers --}}
                    <tr class="bg-emerald-50 text-gray-600 border-b border-emerald-100">
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">No.</th>
                        <th class="px-3 py-2 text-left font-semibold border-r border-emerald-100" rowspan="3">Nama Ruangan</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">Kelas</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">No. Ruangan</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" rowspan="3">No.<br>Lokasi</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100"
                            colspan="{{ (!$isShiftBased ? 3 : 0) + $maxCols * ($isShiftBased ? ($hasJam ? 4 : 3) : ($isPerLocation ? 4 : 3)) }}">
                            {{ $section->measurement_unit }}
                        </th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" colspan="2" rowspan="2">Batas<br>Alert</th>
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100 whitespace-nowrap" colspan="2" rowspan="2">Batas<br>Tindakan</th>
                        <th class="px-2 py-2 text-center font-semibold whitespace-nowrap" rowspan="3">Kesimpulan</th>
                    </tr>
                    {{-- Row 2: period/shift labels --}}
                    <tr class="bg-emerald-50 text-gray-600 border-b border-emerald-100">
                        @if (!$isShiftBased)
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
                        @php $colAsgn = $secAssignments[$col] ?? 1; @endphp
                        @if ($isShiftBased)
                        <th class="px-2 py-1.5 text-center font-semibold border-r border-emerald-100 whitespace-nowrap"
                            colspan="{{ $hasJam ? 4 : 3 }}">
                            Shift
                            @if ($isSwab)
                            @php $swabColTimes = $hd['swab_times'][$section->id][$col] ?? []; @endphp
                            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                                @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                                @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                                <div>{{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: '—' }} – {{ ($st['selesai'] ?? '') ?: '—' }}</div>
                                @endforeach
                            </div>
                            @endif
                            <div class="flex justify-center mt-1.5">
                                <span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $colAsgn == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $colAsgn == 1 ? 'S1' : 'S2' }}
                                </span>
                            </div>
                        </th>
                        @else
                        @php
                            $expJamMulai = null; $expJamSelesai = null;
                            if (!$isSettlePlate) {
                                foreach ($section->locations as $loc2) {
                                    $e2 = $entryMap[$loc2->id][$col][1] ?? $entryMap[$loc2->id][$col][2] ?? null;
                                    if ($e2 && ($e2->start_time || $e2->end_time)) {
                                        $expJamMulai   = $e2->start_time;
                                        $expJamSelesai = $e2->end_time;
                                        break;
                                    }
                                }
                            }
                        @endphp
                        <th class="px-2 py-2 text-center font-semibold border-r border-emerald-100" colspan="{{ $isPerLocation ? 4 : 3 }}">
                            <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">
                                Exposure {{ $romanNums[$col - 1] ?? $col }}
                            </div>
                            @if ($isSettlePlate)
                            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                                @php $stAB = $hd['settle_times'][$section->id][$col][$ab] ?? []; @endphp
                                <div>{{ $abLabel }}: {{ ($stAB['start_time'] ?? '') ?: '—' }} – {{ ($stAB['end_time'] ?? '') ?: '—' }}</div>
                                @endforeach
                            </div>
                            @elseif (!$isPerLocation)
                            <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                                {{ $expJamMulai ? $expJamMulai . ' – ' . ($expJamSelesai ?? '—') : '—' }}
                            </div>
                            @endif
                            <div class="flex justify-center mt-1.5">
                                <span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $colAsgn == 1 ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700' }}">
                                    {{ $colAsgn == 1 ? 'S1' : 'S2' }}
                                </span>
                            </div>
                        </th>
                        @endif
                        @endfor
                    </tr>
                    {{-- Row 3: sub-column headers --}}
                    <tr class="bg-emerald-50/60 text-gray-500 border-b border-gray-200">
                        @if (!$isShiftBased)
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">B</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">T</th>
                        @endif
                        @for ($col = 1; $col <= $maxCols; $col++)
                        @if (($isShiftBased && $hasJam) || $isPerLocation)
                            <th class="px-1.5 py-1.5 text-center font-medium border-r border-emerald-100 whitespace-nowrap">JAM</th>
                        @endif
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">B</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                            <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">T</th>
                        @endfor
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-emerald-100">F</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @foreach ($section->locations as $loc)
                    @php
                        $locEntries = collect();
                        for ($p = 1; $p <= $section->max_exposures; $p++) {
                            for ($s = 1; $s <= 2; $s++) {
                                if (isset($entryMap[$loc->id][$p][$s])) {
                                    $locEntries->push($entryMap[$loc->id][$p][$s]);
                                }
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

                        $classBadge = match($loc->class) {
                            'A' => 'bg-purple-100 text-purple-700',
                            'B' => 'bg-blue-100 text-blue-700',
                            'C' => 'bg-amber-100 text-amber-700',
                            default => 'bg-gray-100 text-gray-600',
                        };
                    @endphp
                    <tr class="hover:bg-emerald-50/20 transition-colors">
                        <td class="px-2 py-2.5 text-center text-gray-400 border-r border-gray-100">{{ $loc->s_no }}</td>
                        <td class="px-3 py-2.5 text-gray-700 font-medium border-r border-gray-100 whitespace-nowrap">{{ $loc->room_name }}</td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="inline-flex items-center justify-center h-5 w-5 rounded text-[11px] font-bold {{ $classBadge }}">{{ $loc->class }}</span>
                        </td>
                        <td class="px-2 py-2.5 text-center text-gray-500 border-r border-gray-100 whitespace-nowrap text-[11px]">{{ $loc->room_number }}</td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            @if (str_starts_with($loc->location_number, '*)'))
                                <span class="text-[11px] text-gray-400 italic">{{ $loc->location_number }}</span>
                            @else
                                <span class="text-[11px] text-gray-500">{{ $loc->location_number }}</span>
                            @endif
                        </td>

                        @if (!$isShiftBased)
                        @php
                            $msEntry = $entryMap[$loc->id][0][1] ?? $entryMap[$loc->id][0][2] ?? null;
                            $msTVal = ($msEntry && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null))
                                ? ($msEntry->cfu_bacteria ?? 0) + ($msEntry->cfu_fungi ?? 0) : null;
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
                            $colAsgn = $secAssignments[$col] ?? 1;
                            $existEntry = $isShiftBased
                                ? ($entryMap[$loc->id][1][$colAsgn] ?? null)
                                : ($entryMap[$loc->id][$col][$colAsgn] ?? null);
                            $tVal = ($existEntry && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null))
                                ? ($existEntry->cfu_bacteria ?? 0) + ($existEntry->cfu_fungi ?? 0)
                                : null;
                        @endphp

                        @if ($hasJam || $isPerLocation)
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

                        {{-- Alert Limit --}}
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->alert_limit_bacteria !== null ? 'text-amber-700' : 'text-gray-300' }}">
                                {{ $loc->alert_limit_bacteria ?? '—' }}
                            </span>
                        </td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->alert_limit_fungi !== null ? 'text-amber-700' : 'text-gray-300' }}">
                                {{ $loc->alert_limit_fungi ?? '—' }}
                            </span>
                        </td>
                        {{-- Action Limit --}}
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->action_limit_bacteria !== null ? 'text-red-600' : 'text-gray-300' }}">
                                {{ $loc->action_limit_bacteria !== null ? ($loc->action_limit_bacteria == 1 ? '<1' : $loc->action_limit_bacteria) : '—' }}
                            </span>
                        </td>
                        <td class="px-2 py-2.5 text-center border-r border-gray-100">
                            <span class="text-[11px] font-medium {{ $loc->action_limit_fungi !== null ? 'text-red-600' : 'text-gray-300' }}">
                                {{ $loc->action_limit_fungi !== null ? ($loc->action_limit_fungi == 1 ? '<1' : $loc->action_limit_fungi) : '—' }}
                            </span>
                        </td>
                        {{-- Kesimpulan --}}
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
            @if ($section->measurement_type === 'swab')
            <p class="mb-1"><span class="text-gray-500">*)</span> diisi jika dibutuhkan</p>
            @endif
            <strong class="text-gray-500">Keterangan:</strong>
            B: Total Bakteri &nbsp;·&nbsp; F: Total Fungi &nbsp;·&nbsp; T: Total Bakteri + Fungi &nbsp;·&nbsp;
            MS: Memenuhi Spesifikasi &nbsp;·&nbsp; TMS: Tidak Memenuhi Spesifikasi
        </div>

        {{-- Catatan & Kesimpulan per seksi --}}
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
    </div>
    @endforeach

    {{-- ── Tanda Tangan & Verifikasi ──────────────────────── --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="px-5 py-3.5 border-b border-gray-100">
            <h3 class="font-semibold text-sm text-gray-700">Tanda Tangan & Verifikasi</h3>
        </div>
        <div class="p-5 grid grid-cols-2 lg:grid-cols-4 gap-4">
            @foreach ([
                ['label' => 'Dimonitoring oleh:', 'sub' => '(Analis Lab. Mikrobiologi)', 'user' => $report->shift1Analis],
                ['label' => 'Dibaca oleh:', 'sub' => '(Analis Lab. Mikrobiologi)', 'user' => $report->shift2Analis],
                ['label' => 'Direview oleh:', 'sub' => '(Staff / Supervisor Mikrobiologi)', 'user' => null],
                ['label' => 'Disetujui oleh:', 'sub' => '(QC Manager)', 'user' => null],
            ] as $sig)
            <div class="border border-gray-200 rounded-xl p-4 min-h-[90px] flex flex-col">
                <p class="text-xs font-semibold text-gray-600">{{ $sig['label'] }}</p>
                <div class="flex-1 flex items-center justify-center py-2">
                    @if ($sig['user'])
                        <p class="text-sm font-medium text-gray-700">{{ $sig['user']->name }}</p>
                    @else
                        <div class="h-px w-16 border-b border-dashed border-gray-300"></div>
                    @endif
                </div>
                <p class="text-[11px] text-gray-400 text-center">{{ $sig['sub'] }}</p>
            </div>
            @endforeach
        </div>
    </div>

    {{-- ── Aksi: Setujui / Kembalikan ──────────────────────────── --}}
    @if ($approval->isPending())
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-4">Tindakan</h3>
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
                        <p class="text-xs text-emerald-600">Laporan akan ditandai sebagai disetujui</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('supervisor.laporan.approve', $report->id) }}">
                    @csrf
                    <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-emerald-500 text-white text-sm font-semibold hover:bg-emerald-600 active:bg-emerald-700 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Setujui Laporan
                    </button>
                </form>
            </div>

            {{-- Return --}}
            <div class="rounded-xl border-2 border-orange-200 bg-orange-50 p-4 flex flex-col gap-3">
                <div class="flex items-center gap-3">
                    <div class="h-9 w-9 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                        </svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-orange-800">Kembalikan Laporan</p>
                        <p class="text-xs text-orange-600">Kirim kembali ke analis untuk diperbaiki</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('supervisor.laporan.return', $report->id) }}" class="flex flex-col gap-2">
                    @csrf
                    <div class="flex gap-2">
                        <select name="returned_to_user_id"
                            class="flex-1 rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm focus:border-orange-400 focus:ring-1 focus:ring-orange-400 focus:outline-none">
                            <option value="">-- Pilih Analis Tujuan --</option>
                            <option value="{{ $report->shift1_analyst_id }}">{{ $report->shift1Analis->name }} (Shift 1)</option>
                            @if ($report->shift2Analis)
                            <option value="{{ $report->shift2_analyst_id }}">{{ $report->shift2Analis->name }} (Shift 2)</option>
                            @endif
                        </select>
                        <textarea name="notes" placeholder="Catatan / alasan pengembalian (opsional)"
                            class="flex-1 rounded-lg border border-orange-200 bg-white px-3 py-2 text-sm resize-none focus:border-orange-400 focus:ring-1 focus:ring-orange-400 focus:outline-none"
                            rows="1"></textarea>
                        <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-orange-500 text-white text-sm font-semibold hover:bg-orange-600 active:bg-orange-700 transition-colors flex items-center gap-2 shadow-sm whitespace-nowrap">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
                            </svg>
                            Kembalikan
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </div>
    @endif

</div>
@endsection
