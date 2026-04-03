@extends('layouts.admin')

@section('title', 'Isi Laporan — ' . $report->reportType->annex_number)
@section('page-title', 'Isi Laporan')
@section('avatar-color', 'bg-green-600')
@section('content')
<form method="POST" action="{{ route('laporan.save', $report) }}" id="laporan-form">
@csrf

{{-- ── Action Bar ─────────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('laporan.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <div class="w-px h-5 bg-gray-200"></div>
        <div>
            <h2 class="text-base font-semibold text-gray-800">
                {{ $report->reportType->annex_number }}
                <span class="text-gray-400 font-normal mx-1">—</span>
                <span class="text-sm font-normal text-gray-600">{{ $report->reportType->name }}</span>
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $report->product_name }} · Batch <span class="font-mono">{{ $report->batch_number }}</span>
                · {{ $report->report_date->isoFormat('D MMM Y') }}
            </p>
        </div>
    </div>

    @if ($isEditable)
    <div class="flex items-center gap-2">
        <button type="button" onclick="openSaveModal()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Simpan Draft
        </button>
        @if ($myShift === 1 && !$shift1HandedOver && $report->shift2Analis)
        <button type="button" onclick="openConfirmModal('handover')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
            Estafet ke Shift 2
        </button>
        @endif
        @if (!$report->shift2Analis || $myShift === 2)
        <button type="button" onclick="openSubmitFlow()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Kirim Laporan
        </button>
        @endif
    </div>
    @else
        @if ($myShift === 1 && $shift1HandedOver)
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-amber-50 text-amber-700 border border-amber-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Sudah diteruskan ke Shift 2 — Mode Lihat
        </span>
        @else
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium
            @if ($report->status === 'submitted') bg-blue-50 text-blue-700
            @elseif ($report->status === 'approved') bg-green-50 text-green-700
            @else bg-gray-100 text-gray-500 @endif">
            @php
                $statusLabel = ['submitted' => 'Dikirim', 'approved' => 'Disetujui', 'rejected' => 'Ditolak'][$report->status] ?? $report->status;
            @endphp
            {{ $statusLabel }} — Mode Lihat
        </span>
        @endif
    @endif
</div>

@if (session('success'))
<div class="mb-5 px-4 py-3 bg-green-50 border border-green-100 rounded-xl text-sm text-green-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- ── Banner: Shift 2 menunggu estafet ────────────────── --}}
@if ($myShift === 2 && !$shift1HandedOver && $report->status === 'in_progress')
<div class="mb-5 px-4 py-4 bg-amber-50 border border-amber-200 rounded-xl flex items-start gap-3">
    <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
    </svg>
    <div>
        <p class="text-sm font-semibold text-amber-800">Menunggu estafet dari Shift 1</p>
        <p class="text-xs text-amber-700 mt-0.5">
            Anda belum bisa mengisi data Shift 2. Analis Shift 1
            (<span class="font-medium">{{ $report->shift1Analis->name }}</span>)
            harus menekan tombol <span class="font-semibold">Estafet ke Shift 2</span> terlebih dahulu.
        </p>
    </div>
</div>
@endif

{{-- ── 1. Pemantauan Ruang ─────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">1. Pemantauan Ruang</h3>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Pemantauan Ruang</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $report->report_date->isoFormat('D MMMM Y') }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Analis Shift 1</label>
            <div class="px-3 py-2 rounded-lg border text-sm flex items-center gap-2
                {{ $myShift === 1 ? 'bg-emerald-50 border-emerald-100 text-emerald-800 font-medium' : 'bg-gray-50 border-gray-100 text-gray-700' }}">
                {{ $report->shift1Analis->name }}
                <span class="inline-flex items-center justify-center h-5 w-12 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 flex-shrink-0">
                    Shift 1
                </span>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Analis Shift 2</label>
            <div class="px-3 py-2 rounded-lg border text-sm flex items-center gap-2
                {{ $myShift === 2 ? 'bg-indigo-50 border-indigo-100 text-indigo-800 font-medium' : 'bg-gray-50 border-gray-100 text-gray-700' }}">
                @if ($report->shift2Analis)
                    {{ $report->shift2Analis->name }}
                @else
                    <span class="text-gray-400 italic">Belum ditentukan</span>
                @endif
                <span class="inline-flex items-center justify-center h-5 w-12 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700 flex-shrink-0">
                    Shift 2
                </span>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Produk</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $report->product_name }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Produk</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $report->batch_number }}
            </div>
        </div>
    </div>
</div>

@php $hd = $report->header_data ?? []; @endphp

{{-- ── 2a. Identitas Instrumen — Air Sampler ───────────── --}}
@if ($needsAirSampler)
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">2. Identitas Instrumen </h3>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">Air Sampler</div>
        </div>
        @php $as = $hd['air_sampler'] ?? []; @endphp
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Air Sampler</label>
            <input type="text" name="header_data[air_sampler][no_id]" value="{{ $as['no_id'] ?? '' }}"
                   @if(!$isEditable) readonly @endif
                   class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Air Sampler</label>
            <input type="date" name="header_data[air_sampler][calibration_date]" value="{{ $as['calibration_date'] ?? '' }}"
                   @if(!$isEditable) readonly @endif
                   class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Air Sampler</label>
            <input type="date" name="header_data[air_sampler][due_date]" value="{{ $as['due_date'] ?? '' }}"
                   @if(!$isEditable) readonly @endif
                   class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
        </div>
    </div>
</div>
@endif

{{-- ── 3. Identitas Medium ─────────────────────────────── --}}
@php $mediumGroups = $report->reportType->medium_groups ?? []; @endphp
@if (!empty($mediumGroups))
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">3. Identitas Medium</h3>
    </div>
    <div class="p-5 grid grid-cols-1 gap-6 {{ count($mediumGroups) > 2 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
        @foreach ($mediumGroups as $medKey => $medLabel)
        @php $med = $hd[$medKey] ?? []; @endphp
        <div>
            <h4 class="text-xs font-semibold text-sky-600 uppercase tracking-wide mb-3">{{ $medLabel }}</h4>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Medium</label>
                    <input type="text" name="header_data[{{ $medKey }}][nomor_batch]" value="{{ $med['nomor_batch'] ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
                </div>
                @if ($medKey !== 'medium_swab')
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor GPT Medium</label>
                    <input type="text" name="header_data[{{ $medKey }}][nomor_gpt]" value="{{ $med['nomor_gpt'] ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal ED {{ $medKey === 'medium_swab' ? 'Swab Kit' : 'Medium' }}</label>
                    <input type="date" name="header_data[{{ $medKey }}][expiry_date]" value="{{ $med['expiry_date'] ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
@endif

{{-- ── 4. Proses Inkubasi Medium Monitoring ──────────── --}}
@if ($needsInkubator)
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">4. Proses Inkubasi Medium Monitoring</h3>
    </div>
    @foreach ([
        'inkubator_20_25' => ['label' => 'Inkubator Suhu 20–25°C', 'min_days' => 3],
        'inkubator_30_35' => ['label' => 'Inkubator Suhu 30–35°C', 'min_days' => 2],
    ] as $inkKey => $inkInfo)
    @php $ink = $hd[$inkKey] ?? []; $inkLabel = $inkInfo['label']; $inkMin = $inkInfo['min_days']; @endphp
    <div class="p-5 space-y-4 @if(!$loop->last) border-b border-gray-100 @endif">
        <p class="text-xs font-semibold text-sky-600 uppercase tracking-wide">{{ $inkLabel }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">{{ $inkLabel }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Inkubator</label>
                <input type="text" name="header_data[{{ $inkKey }}][no_id]" value="{{ $ink['no_id'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Inkubator</label>
                <input type="date" name="header_data[{{ $inkKey }}][calibration_date]" value="{{ $ink['calibration_date'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Inkubator</label>
                <input type="date" name="header_data[{{ $inkKey }}][due_date]" value="{{ $ink['due_date'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-gray-50">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Inkubasi Medium (min {{ $inkMin }} hari)</label>
                <input type="date" name="header_data[{{ $inkKey }}][incubation_date]" value="{{ $ink['incubation_date'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                <input type="date" name="header_data[{{ $inkKey }}][date_in]" value="{{ $ink['date_in'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                <input type="time" name="header_data[{{ $inkKey }}][time_in]" value="{{ $ink['time_in'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
            {{-- spacer: ocupy col 1-2 on large screens so keluar fields align under masuk fields --}}
            <div class="hidden lg:block lg:col-span-2"></div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                <input type="date" name="header_data[{{ $inkKey }}][date_out]" value="{{ $ink['date_out'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                <input type="time" name="header_data[{{ $inkKey }}][time_out]" value="{{ $ink['time_out'] ?? '' }}"
                       @if(!$isEditable) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
            </div>
            {{-- Diinkubasi & Dikeluarkan oleh --}}
            <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                @foreach ([
                    ['key' => 'incubated',  'label' => 'Diinkubasi oleh',   'default_date' => date('Y-m-d')],
                    ['key' => 'removed', 'label' => 'Dikeluarkan oleh',  'default_date' => ''],
                ] as $field)
                @php
                    $fKey    = $field['key'];
                    $fName   = $fKey . '_by';
                    $fDate   = $fKey . '_date';
                    $savedWho  = $ink[$fName] ?? '';
                    $savedDate = $ink[$fDate] ?? $field['default_date'];
                @endphp
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3 space-y-2">
                    <p class="text-xs font-semibold text-gray-500">{{ $field['label'] }}</p>
                    @if ($isEditable)
                    <div class="flex flex-wrap gap-2" data-radio-group="{{ $inkKey }}_{{ $fKey }}">
                        @foreach ([1 => $report->shift1Analis, 2 => $report->shift2Analis] as $sNum => $analyst)
                        @if ($analyst)
                        <button type="button"
                                data-value="{{ $analyst->name }}"
                                onclick="toggleAnalis('{{ $inkKey }}', '{{ $fKey }}', this.dataset.value, this)"
                                class="inkubasi-radio-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors
                                       {{ $savedWho === $analyst->name ? 'bg-sky-50 border-sky-300 text-sky-700' : 'border-gray-200 bg-white text-gray-600 hover:border-sky-200' }}">
                            {{ $analyst->name }}
                            <span class="px-1 py-0.5 rounded text-[10px] font-semibold {{ $sNum === 1 ? 'bg-emerald-100 text-emerald-600' : 'bg-indigo-100 text-indigo-600' }}">S{{ $sNum }}</span>
                        </button>
                        @endif
                        @endforeach
                        <input type="hidden" name="header_data[{{ $inkKey }}][{{ $fName }}]" id="radio-val-{{ $inkKey }}-{{ $fKey }}" value="{{ $savedWho }}">
                    </div>
                    @else
                    <div class="text-sm text-gray-700">{{ $savedWho ?: '—' }}</div>
                    @endif
                    <input type="date" name="header_data[{{ $inkKey }}][{{ $fDate }}]"
                           value="{{ $savedDate }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

{{-- ── 4+. Tabel Pengukuran per Seksi ─────────────────── --}}
@foreach ($report->reportType->sections as $section)
@php
    $isShiftBased  = in_array($section->measurement_type, ['air_sampler', 'contact_plate', 'swab']);
    $isSettlePlate = $section->measurement_type === 'settle_plate';
    $isSwab        = $section->measurement_type === 'swab';
    $hasJam        = $section->measurement_type === 'air_sampler';
    $maxCols       = $section->max_exposures;
    $romanNums     = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    $secNum        = $loop->index + 5;
    $savedAsgn      = ($report->header_data['shift_assignments'] ?? [])[$section->id] ?? [];
    $secAssignments = [];
    for ($c = 1; $c <= $maxCols; $c++) {
        $secAssignments[$c] = isset($savedAsgn[$c]) ? (int)$savedAsgn[$c] : 1;
    }
@endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
        <div class="h-7 w-7 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0">
            <span class="text-xs font-bold text-sky-600">{{ $secNum }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-sm text-gray-700">{{ $section->measurement_unit }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $section->name }}</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse" style="min-width: {{ 480 + (!$isShiftBased ? 130 : 0) + ($maxCols * ($isShiftBased ? ($hasJam ? 220 : ($isSwab ? 220 : 160)) : 130)) }}px">
            <thead>
                {{-- Row 1: group headers --}}
                <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No.</th>
                    <th class="px-3 py-2 text-left font-semibold border-r border-sky-100" rowspan="3">Room Name</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Class</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Room Number</th>
                    @if (!$isShiftBased)
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Location<br>Number</th>
                    @else
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Location<br>Number</th>
                    @endif
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100"
                    colspan="{{ (!$isShiftBased ? 3 : 0) + $maxCols * ($isShiftBased ? ($hasJam ? 4 : 3) : 3) }}">
                        {{ $section->measurement_unit }}
                    </th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Alert<br>Limit</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Action<br>Limit</th>
                    <th class="px-2 py-2 text-center font-semibold whitespace-nowrap" rowspan="3">Kesimpulan</th>
                </tr>
                {{-- Row 2: period/shift labels --}}
                <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">
                    @if (!$isShiftBased)
                    @php
                        $msJamMulai = null; $msJamSelesai = null;
                        foreach ($section->locations as $loc2) {
                            $e0 = $entryMap[$loc2->id][0][$myShift] ?? null;
                            if ($e0 && ($e0->start_time || $e0->end_time)) {
                                $msJamMulai   = $e0->start_time;
                                $msJamSelesai = $e0->end_time;
                                break;
                            }
                        }
                    @endphp
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100" colspan="3">
                        <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">Machine Set-up</div>
                        @if ($isEditable)
                        <div class="flex justify-center items-center gap-1">
                            <input type="time" name="exposure_times[{{ $section->id }}][0][start_time]"
                                   value="{{ $msJamMulai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            <span class="text-gray-400 text-[10px] font-normal">–</span>
                            <input type="time" name="exposure_times[{{ $section->id }}][0][end_time]"
                                   value="{{ $msJamSelesai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        </div>
                        @else
                        <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                            {{ $msJamMulai ? $msJamMulai . ' – ' . ($msJamSelesai ?? '—') : '—' }}
                        </div>
                        @endif
                    </th>
                    @endif
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @if ($isShiftBased)
                    @php $colAsgn = $secAssignments[$col] ?? $col; @endphp
                    <th class="px-2 py-1.5 text-center font-semibold border-r border-sky-100 whitespace-nowrap"
                        colspan="{{ $hasJam ? 4 : 3 }}">
                        Shift
                        @if ($isSwab)
                        @php $swabColTimes = $hd['swab_times'][$section->id][$col] ?? []; @endphp
                        @if ($isEditable)
                        <div class="space-y-0.5 mt-1">
                            @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                            @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                            <div class="flex items-center justify-center gap-0.5">
                                <span class="text-[9px] font-bold text-gray-500 w-12 text-left shrink-0">{{ $swabLabel }}:</span>
                                <input type="time" name="swab_times[{{ $section->id }}][{{ $col }}][{{ $swabKey }}][mulai]"
                                       value="{{ $st['mulai'] ?? '' }}"
                                       class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                                <span class="text-gray-400 text-[10px]">–</span>
                                <input type="time" name="swab_times[{{ $section->id }}][{{ $col }}][{{ $swabKey }}][selesai]"
                                       value="{{ $st['selesai'] ?? '' }}"
                                       class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                            @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                            @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                            <div>{{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: '—' }} – {{ ($st['selesai'] ?? '') ?: '—' }}</div>
                            @endforeach
                        </div>
                        @endif
                        @endif
                        <input type="hidden" name="shift_assignment[{{ $section->id }}][{{ $col }}]" id="sa-{{ $section->id }}-{{ $col }}" value="{{ $colAsgn }}">
                        @if ($isEditable && $myShift === 1 && !$shift1HandedOver)
                        <div class="flex justify-center gap-1 mt-1.5">
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 1)" id="sa-btn-{{ $section->id }}-{{ $col }}-1"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 1 ? 'bg-sky-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S1</button>
                            @if ($report->shift2Analis)
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 2)" id="sa-btn-{{ $section->id }}-{{ $col }}-2"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 2 ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S2</button>
                            @endif
                        </div>
                        @else
                        <div class="flex justify-center mt-1.5">
                            <span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $colAsgn == 1 ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">{{ $colAsgn == 1 ? 'S1' : 'S2' }}</span>
                        </div>
                        @endif
                    </th>
                    @else
                    @php
                        if ($isSettlePlate) {
                            $expJamMulai = null; $expJamSelesai = null;
                        } else {
                            $expJamMulai = null; $expJamSelesai = null;
                            foreach ($section->locations as $loc2) {
                                $e2 = $entryMap[$loc2->id][$col][$myShift] ?? null;
                                if ($e2 && ($e2->start_time || $e2->end_time)) {
                                    $expJamMulai   = $e2->start_time;
                                    $expJamSelesai = $e2->end_time;
                                    break;
                                }
                            }
                        }
                    @endphp
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100" colspan="3">
                        <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">
                            Exposure {{ $romanNums[$col - 1] ?? $col }}
                        </div>
                        @if ($isSettlePlate)
                            @if ($isEditable)
                            <div class="space-y-0.5 mt-1">
                                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                                @php $stAB = $hd['settle_times'][$section->id][$col][$ab] ?? []; @endphp
                                <div class="flex items-center justify-center gap-0.5">
                                    <span class="text-[9px] font-bold text-gray-500 w-3 text-left">{{ $abLabel }}:</span>
                                    <input type="time" name="settle_times[{{ $section->id }}][{{ $col }}][{{ $ab }}][start_time]"
                                           value="{{ $stAB['start_time'] ?? '' }}"
                                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                                    <span class="text-gray-400 text-[10px]">–</span>
                                    <input type="time" name="settle_times[{{ $section->id }}][{{ $col }}][{{ $ab }}][end_time]"
                                           value="{{ $stAB['end_time'] ?? '' }}"
                                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                                @php $stAB = $hd['settle_times'][$section->id][$col][$ab] ?? []; @endphp
                                <div>{{ $abLabel }}: {{ ($stAB['start_time'] ?? '') ?: '—' }} – {{ ($stAB['end_time'] ?? '') ?: '—' }}</div>
                                @endforeach
                            </div>
                            @endif
                        @else
                        @if ($isEditable)
                        <div class="flex justify-center items-center gap-1">
                            <input type="time" name="exposure_times[{{ $section->id }}][{{ $col }}][start_time]"
                                   value="{{ $expJamMulai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            <span class="text-gray-400 text-[10px] font-normal">–</span>
                            <input type="time" name="exposure_times[{{ $section->id }}][{{ $col }}][end_time]"
                                   value="{{ $expJamSelesai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        </div>
                        @else
                        <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                            {{ $expJamMulai ? $expJamMulai . ' – ' . ($expJamSelesai ?? '—') : '—' }}
                        </div>
                        @endif
                        @endif
                        {{-- Shift assignment toggle --}}
                        @php $colAsgn = $secAssignments[$col] ?? 1; @endphp
                        <input type="hidden" name="shift_assignment[{{ $section->id }}][{{ $col }}]" id="sa-{{ $section->id }}-{{ $col }}" value="{{ $colAsgn }}">
                        @if ($isEditable && $myShift === 1 && !$shift1HandedOver)
                        <div class="flex justify-center gap-1 mt-1.5">
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 1)" id="sa-btn-{{ $section->id }}-{{ $col }}-1"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 1 ? 'bg-sky-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S1</button>
                            @if ($report->shift2Analis)
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 2)" id="sa-btn-{{ $section->id }}-{{ $col }}-2"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 2 ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S2</button>
                            @endif
                        </div>
                        @else
                        <div class="flex justify-center mt-1.5">
                            <span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $colAsgn == 1 ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">{{ $colAsgn == 1 ? 'S1' : 'S2' }}</span>
                        </div>
                        @endif
                    </th>
                    @endif
                    @endfor
                </tr>
                {{-- Row 3: sub-column headers --}}
                <tr class="bg-sky-50/60 text-gray-500 border-b border-gray-200">
                    @if (!$isShiftBased)
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    @endif
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @if ($isShiftBased)
                        @if ($hasJam)
                        <th class="px-1.5 py-1.5 text-center font-medium border-r border-sky-100 whitespace-nowrap">JAM</th>
                        @endif
                    @endif
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    @endfor
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($section->locations as $loc)
                @php
                    // Collect all entries for this location for kesimpulan
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
                <tr class="hover:bg-blue-50/20 transition-colors">
                    <td class="px-2 py-2.5 text-center text-gray-400 border-r border-gray-100">{{ $loc->s_no }}</td>
                    <td class="px-3 py-2.5 text-gray-700 font-medium border-r border-gray-100 whitespace-nowrap">{{ $loc->room_name }}</td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="inline-flex items-center justify-center h-5 w-5 rounded text-[11px] font-bold {{ $classBadge }}">
                            {{ $loc->class }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center text-gray-500 border-r border-gray-100 whitespace-nowrap font-mono text-[11px]">{{ $loc->room_number }}</td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        @if (str_starts_with($loc->location_number, '*)'))
                            <span class="font-mono text-[11px] text-gray-400 italic">{{ $loc->location_number }}</span>
                        @else
                            <span class="font-mono text-[11px] text-gray-500">{{ $loc->location_number }}</span>
                        @endif
                    </td>
                    @if (!$isShiftBased)
                    @php
                        $msEntry = $entryMap[$loc->id][0][$myShift] ?? null;
                        $msTVal = ($msEntry && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null))
                            ? ($msEntry->cfu_bacteria ?? 0) + ($msEntry->cfu_fungi ?? 0) : null;
                    @endphp
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($isEditable)
                            <input type="number" min="0" name="entries[{{ $loc->id }}][0][cfu_bacteria]"
                                   value="{{ $msEntry?->cfu_bacteria }}"
                                   data-loc="{{ $loc->id }}" data-col="0" data-type="b" data-section-id="{{ $section->id }}"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $msEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($isEditable)
                            <input type="number" min="0" name="entries[{{ $loc->id }}][0][cfu_fungi]"
                                   value="{{ $msEntry?->cfu_fungi }}"
                                   data-loc="{{ $loc->id }}" data-col="0" data-type="f"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $msEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_fungi ?? '—' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                        <span id="t-{{ $loc->id }}-0"
                              class="text-[11px] font-semibold {{ $msTVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
                            {{ $msTVal ?? '—' }}
                        </span>
                    </td>
                    @endif

                    {{-- Data columns per exposure/shift --}}
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @php
                        $colAsgn = $secAssignments[$col] ?? 1;
                        if ($isShiftBased) {
                            $existEntry = $entryMap[$loc->id][1][$colAsgn] ?? null;
                            $editable   = $isEditable && ($colAsgn == $myShift);
                        } else {
                            $existEntry = $entryMap[$loc->id][$col][$colAsgn] ?? null;
                            $editable   = $isEditable && ($colAsgn == $myShift);
                        }
                        $iName = "entries[{$loc->id}][{$col}]";
                        $rowKey = "{$loc->id}-{$col}";
                    @endphp

                    {{-- JAM input (only for air_sampler) --}}
                    @if ($hasJam)
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="time" name="{{ $iName }}[start_time]"
                                   value="{{ $existEntry?->start_time }}"
                                   class="w-[84px] rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @else
                            <span class="text-gray-{{ $existEntry?->start_time ? '600' : '300' }} text-[11px]">{{ $existEntry?->start_time ? \Illuminate\Support\Str::substr($existEntry->start_time, 0, 5) : '-' }}</span>
                        @endif
                    </td>
                    @endif

                    {{-- CFU Bacteria (B) --}}
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="number" min="0" name="{{ $iName }}[cfu_bacteria]"
                                   value="{{ $existEntry?->cfu_bacteria }}"
                                   data-loc="{{ $loc->id }}" data-col="{{ $col }}" data-type="b" data-section-id="{{ $section->id }}"
                                   @if (str_starts_with($loc->location_number, '*)')) data-optional="true" @endif
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $existEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        @endif
                    </td>

                    {{-- CFU Fungi (F) --}}
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="number" min="0" name="{{ $iName }}[cfu_fungi]"
                                   value="{{ $existEntry?->cfu_fungi }}"
                                   data-loc="{{ $loc->id }}" data-col="{{ $col }}" data-type="f" data-section-id="{{ $section->id }}"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $existEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_fungi ?? '—' }}
                            </span>
                        @endif
                    </td>

                    {{-- T (Total = B + F, auto-calculated) --}}
                    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                        @php
                            $tVal = ($existEntry && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null))
                                ? ($existEntry->cfu_bacteria ?? 0) + ($existEntry->cfu_fungi ?? 0)
                                : null;
                        @endphp
                        <span id="t-{{ $rowKey }}"
                              class="text-[11px] font-semibold {{ $tVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
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
                    <td class="px-2 py-2.5 text-center"
                        id="konklusi-{{ $loc->id }}"
                        data-alert-b="{{ $loc->alert_limit_bacteria ?? '' }}"
                        data-alert-f="{{ $loc->alert_limit_fungi ?? '' }}"
                        data-action-b="{{ $loc->action_limit_bacteria ?? '' }}"
                        data-action-f="{{ $loc->action_limit_fungi ?? '' }}">
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
    @php $secNote = $hd['section_notes'][$section->id] ?? []; @endphp
    <div class="px-5 py-4 border-t border-gray-100 space-y-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
            @if ($isEditable)
            <textarea name="header_data[section_notes][{{ $section->id }}][notes]" rows="2"
                      placeholder="Catatan untuk seksi ini..."
                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 resize-none
                             focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">{{ $secNote['notes'] ?? '' }}</textarea>
            @else
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700 min-h-[40px]">
                {{ $secNote['notes'] ?? '—' }}
            </div>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Kesimpulan</label>
            @if ($isEditable)
            <div class="flex flex-wrap gap-2">
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border-2 transition-colors
                              {{ ($secNote['conclusion'] ?? '') === 'MS' ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-green-200' }}">
                    <input type="radio" name="header_data[section_notes][{{ $section->id }}][conclusion]" value="MS"
                           {{ ($secNote['conclusion'] ?? '') === 'MS' ? 'checked' : '' }}
                           class="text-green-500 focus:ring-green-400">
                    <span class="text-xs font-medium text-green-700">Memenuhi Spesifikasi <span class="font-bold">(MS)</span></span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border-2 transition-colors
                              {{ ($secNote['conclusion'] ?? '') === 'TMS' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-red-200' }}">
                    <input type="radio" name="header_data[section_notes][{{ $section->id }}][conclusion]" value="TMS"
                           {{ ($secNote['conclusion'] ?? '') === 'TMS' ? 'checked' : '' }}
                           class="text-red-500 focus:ring-red-400">
                    <span class="text-xs font-medium text-red-700">Tidak Memenuhi Spesifikasi <span class="font-bold">(TMS)</span></span>
                </label>
            </div>
            @else
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
            @endif
        </div>
    </div>
</div>
@endforeach

{{-- ── Catatan & Kesimpulan ──────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">Catatan & Kesimpulan Akhir</h3>
    </div>
    <div class="p-5 space-y-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
            <textarea name="header_data[notes]" rows="3"
                      @if(!$isEditable) readonly @endif
                      placeholder="Masukkan catatan pemantauan..."
                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 resize-none
                             focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none
                             @if(!$isEditable) bg-gray-50 @endif">{{ $hd['notes'] ?? '' }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-2">Kesimpulan Akhir</label>
            @if ($isEditable)
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 cursor-pointer px-4 py-2.5 rounded-lg border-2 transition-colors
                              {{ ($hd['global_conclusion'] ?? '') === 'MS' ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-green-200' }}">
                    <input type="radio" name="header_data[global_conclusion]" value="MS"
                           {{ ($hd['global_conclusion'] ?? '') === 'MS' ? 'checked' : '' }}
                           class="text-green-500 focus:ring-green-400">
                    <span class="text-sm font-medium text-green-700">Memenuhi Spesifikasi <span class="font-bold">(MS)</span></span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer px-4 py-2.5 rounded-lg border-2 transition-colors
                              {{ ($hd['global_conclusion'] ?? '') === 'TMS' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-red-200' }}">
                    <input type="radio" name="header_data[global_conclusion]" value="TMS"
                           {{ ($hd['global_conclusion'] ?? '') === 'TMS' ? 'checked' : '' }}
                           class="text-red-500 focus:ring-red-400">
                    <span class="text-sm font-medium text-red-700">Tidak Memenuhi Spesifikasi <span class="font-bold">(TMS)</span></span>
                </label>
            </div>
            @else
                @php $kg = $hd['global_conclusion'] ?? ''; @endphp
                <div class="px-4 py-2.5 rounded-lg border border-gray-100 bg-gray-50 inline-block text-sm">
                    @if ($kg === 'MS')
                        <span class="text-green-700 font-semibold">Memenuhi Spesifikasi (MS)</span>
                    @elseif ($kg === 'TMS')
                        <span class="text-red-700 font-semibold">Tidak Memenuhi Spesifikasi (TMS)</span>
                    @else
                        <span class="text-gray-400">Belum ditentukan</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ── Tanda Tangan ──────────────────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
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

{{-- Bottom save bar --}}
@if ($isEditable)
<div class="flex justify-end gap-2 pb-2">
    <button type="button" onclick="openSaveModal()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
        Simpan Draft
    </button>
    @if ($myShift === 1 && !$shift1HandedOver && $report->shift2Analis)
    <button type="button" onclick="openConfirmModal('handover')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-colors shadow-sm">
        Estafet ke Shift 2
    </button>
    @endif
    @if (!$report->shift2Analis || $myShift === 2)
    <button type="button" onclick="openSubmitFlow()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
        Kirim Laporan
    </button>
    @endif
</div>
@endif

{{-- Hidden inputs for save confirmation --}}
<input type="hidden" id="save-action-input" name="action" value="">
<input type="hidden" id="save-supervisor-input" name="supervisor_id" value="">

</form>
<div id="save-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" onclick="closeSaveModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
        <h3 id="save-modal-title" class="text-base font-semibold text-gray-800">Konfirmasi Simpan Draft</h3>
        <p id="save-modal-desc" class="text-sm text-gray-500">Masukkan username dan password Anda untuk menyimpan.</p>
        <div class="space-y-3">
            {{-- Supervisor select (only shown for submit action) --}}
            <div id="supervisor-select-row" class="hidden">
                <label class="block text-xs font-medium text-gray-600 mb-1">Kirim ke Supervisor</label>
                <select id="save-modal-supervisor"
                        class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none bg-white">
                    <option value="">-- Pilih Supervisor --</option>
                    @foreach (\App\Models\User::where('role', 'supervisor')->orderBy('name')->get() as $sup)
                        <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                    @endforeach
                </select>
                <p id="save-modal-supervisor-error" class="hidden mt-1 text-xs text-red-600">Pilih supervisor terlebih dahulu.</p>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Username</label>
                <input id="save-modal-username" type="text" autocomplete="username"
                       class="block w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Password</label>
                <div class="relative" x-data="{ show: false }">
                    <input id="save-modal-password" :type="show ? 'text' : 'password'" autocomplete="current-password"
                           class="block w-full rounded-lg border border-gray-300 px-3 py-2 pr-10 text-sm focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    <button type="button" @click="show = !show" class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 focus:outline-none">
                      <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.641 0-8.578-3.007-9.964-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                      <svg x-show="show" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                    </button>
                </div>
            </div>
            <p id="save-modal-error" class="hidden text-xs text-red-600"></p>
        </div>
        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="closeSaveModal()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                Batal
            </button>
            <button type="button" id="save-modal-confirm" onclick="confirmSave()"
                    class="px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 disabled:opacity-50">
                Simpan
            </button>
        </div>
    </div>
</div>

{{-- Alert modal (replaces browser alert()) --}}
<div id="alert-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" onclick="closeAlertModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-3">
        <h3 id="alert-modal-title" class="text-base font-semibold text-gray-800">Perhatian</h3>
        <p id="alert-modal-msg" class="text-sm text-gray-600 whitespace-pre-line"></p>
        <div class="flex justify-end pt-1">
            <button type="button" onclick="closeAlertModal()"
                    class="px-4 py-2 rounded-lg bg-gray-800 text-white text-sm font-medium hover:bg-gray-700">
                OK
            </button>
        </div>
    </div>
</div>

{{-- Confirm modal (replaces browser confirm()) --}}
<div id="confirm-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-3">
        <h3 id="confirm-modal-title" class="text-base font-semibold text-gray-800">Konfirmasi</h3>
        <p id="confirm-modal-msg" class="text-sm text-gray-600"></p>
        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="closeConfirmModal()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                Batal
            </button>
            <button type="button" id="confirm-modal-ok" onclick="doConfirm()"
                    class="px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600">
                OK
            </button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Simpan Draft / Estafet: password confirmation modal ────────────────
let _pendingAction = 'save';

const _modalConfig = {
    save: {
        title: 'Konfirmasi Simpan Draft',
        desc:  'Masukkan username dan password Anda untuk menyimpan.',
        btnText: 'Simpan',
        btnClass: 'bg-sky-500 hover:bg-sky-600',
    },
    handover: {
        title: 'Konfirmasi Estafet ke Shift 2',
        desc:  'Setelah diteruskan, data Shift 1 tidak dapat diubah lagi. Masukkan username dan password Anda untuk melanjutkan.',
        btnText: 'Estafet',
        btnClass: 'bg-amber-500 hover:bg-amber-600',
    },
    submit: {
        title: 'Konfirmasi Kirim Laporan',
        desc:  'Setelah dikirim, data tidak dapat diubah lagi. Masukkan username dan password Anda untuk melanjutkan.',
        btnText: 'Kirim Laporan',
        btnClass: 'bg-sky-500 hover:bg-sky-600',
    },
};

function openSaveModal()    { openConfirmModal('save'); }
function openConfirmModal(action) {
    if (action === 'handover') {
        const missing = getMissingCols(1);
        if (missing.size > 0) {
            showAlertModal(
                'Data Belum Lengkap',
                'Kolom Shift 1 berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
            );
            return;
        }
    }
    _pendingAction = action;
    const cfg = _modalConfig[action];
    document.getElementById('save-modal-title').textContent   = cfg.title;
    document.getElementById('save-modal-desc').textContent    = cfg.desc;
    const btn = document.getElementById('save-modal-confirm');
    btn.textContent = cfg.btnText;
    btn.className   = `px-4 py-2 rounded-lg text-white text-sm font-medium disabled:opacity-50 ${cfg.btnClass}`;
    btn.disabled    = false;
    document.getElementById('save-modal-username').value = '';
    document.getElementById('save-modal-password').value = '';
    document.getElementById('save-modal-error').classList.add('hidden');
    // Show supervisor dropdown only for submit action
    const supRow = document.getElementById('supervisor-select-row');
    if (action === 'submit') {
        supRow.classList.remove('hidden');
        document.getElementById('save-modal-supervisor').value = '';
        document.getElementById('save-modal-supervisor-error').classList.add('hidden');
    } else {
        supRow.classList.add('hidden');
    }
    document.getElementById('save-modal').classList.remove('hidden');
    document.getElementById('save-modal-username').focus();
}

function closeSaveModal() {
    document.getElementById('save-modal').classList.add('hidden');
}

async function confirmSave() {
    const action   = _pendingAction;
    const username = document.getElementById('save-modal-username').value.trim();
    const password = document.getElementById('save-modal-password').value;
    const errEl    = document.getElementById('save-modal-error');
    const btn      = document.getElementById('save-modal-confirm');

    // Validate supervisor selection for submit action
    if (action === 'submit') {
        const supId = document.getElementById('save-modal-supervisor').value;
        const supErr = document.getElementById('save-modal-supervisor-error');
        if (!supId) {
            supErr.classList.remove('hidden');
            return;
        }
        supErr.classList.add('hidden');
    }

    if (!username || !password) {
        errEl.textContent = 'Username dan password harus diisi.';
        errEl.classList.remove('hidden');
        return;
    }

    btn.disabled = true;
    btn.textContent = 'Memeriksa...';
    errEl.classList.add('hidden');

    try {
        const res = await fetch('{{ route('laporan.verify-password') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                             ?? document.querySelector('input[name="_token"]')?.value,
            },
            body: JSON.stringify({ username, password }),
        });

        const data = await res.json();

        if (data.ok) {
            closeSaveModal();
            document.getElementById('save-action-input').value = action;
            if (action === 'submit') {
                document.getElementById('save-supervisor-input').value =
                    document.getElementById('save-modal-supervisor').value;
            }
            formDirty = false;
            document.getElementById('laporan-form').submit();
        } else {
            errEl.textContent = data.message ?? 'Username atau password salah.';
            errEl.classList.remove('hidden');
            btn.disabled = false;
            btn.textContent = _modalConfig[action].btnText;
        }
    } catch (e) {
        errEl.textContent = 'Terjadi kesalahan. Coba lagi.';
        errEl.classList.remove('hidden');
        btn.disabled = false;
        btn.textContent = _modalConfig[action].btnText;
    }
}

// Close modal on Enter key in password field
document.addEventListener('DOMContentLoaded', () => {
    document.getElementById('save-modal-password')?.addEventListener('keydown', e => {
        if (e.key === 'Enter') confirmSave();
    });
});

// Auto-calculate T = B + F when user types in a CFU input
document.addEventListener('input', function (e) {
    if (!e.target.classList.contains('cfu-input')) return;

    const loc = e.target.dataset.loc;
    const col = e.target.dataset.col;

    const bInput = document.querySelector(`[data-loc="${loc}"][data-col="${col}"][data-type="b"]`);
    const fInput = document.querySelector(`[data-loc="${loc}"][data-col="${col}"][data-type="f"]`);

    const b = parseFloat(bInput?.value) >= 0 ? parseFloat(bInput.value) : 0;
    const f = parseFloat(fInput?.value) >= 0 ? parseFloat(fInput.value) : 0;

    const tSpan = document.getElementById(`t-${loc}-${col}`);
    if (tSpan) {
        const hasValue = (bInput?.value !== '' || fInput?.value !== '');
        tSpan.textContent  = hasValue ? (b + f) : '—';
        tSpan.className    = `text-[11px] font-semibold ${hasValue ? 'text-gray-700' : 'text-gray-300'}`;
    }

    // Recalculate Kesimpulan for this location across all its inputs
    const konklusiCell = document.getElementById(`konklusi-${loc}`);
    if (konklusiCell) {
        // Gather max B and max F across all cols for this loc
        let maxB = 0, maxF = 0, hasAny = false;
        document.querySelectorAll(`[data-loc="${loc}"][data-type="b"]`).forEach(inp => {
            if (inp.value !== '') { hasAny = true; maxB = Math.max(maxB, parseFloat(inp.value) || 0); }
        });
        document.querySelectorAll(`[data-loc="${loc}"][data-type="f"]`).forEach(inp => {
            if (inp.value !== '') { hasAny = true; maxF = Math.max(maxF, parseFloat(inp.value) || 0); }
        });
        const alertB  = konklusiCell.dataset.alertB  !== '' ? parseFloat(konklusiCell.dataset.alertB)  : null;
        const alertF  = konklusiCell.dataset.alertF  !== '' ? parseFloat(konklusiCell.dataset.alertF)  : null;
        const actionB = konklusiCell.dataset.actionB !== '' ? parseFloat(konklusiCell.dataset.actionB) : null;
        const actionF = konklusiCell.dataset.actionF !== '' ? parseFloat(konklusiCell.dataset.actionF) : null;
        let label = '—', cls = 'text-gray-300 text-[11px]';
        if (hasAny) {
            const isTMS = (actionB !== null && maxB >= actionB) || (actionF !== null && maxF >= actionF);
            const isAlert = !isTMS && ((alertB !== null && maxB >= alertB) || (alertF !== null && maxF >= alertF));
            if (isTMS)       { label = 'TMS';   cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700'; }
            else if (isAlert){ label = 'Alert'; cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700'; }
            else             { label = 'MS';    cls = 'inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700'; }
        }
        konklusiCell.innerHTML = `<span class="${cls}">${label}</span>`;
    }
});

// Deselectable analis radio (click same button again to clear)
function toggleAnalis(inkKey, field, value, btn) {
    const hidden = document.getElementById(`radio-val-${inkKey}-${field}`);
    if (!hidden) return;
    const isSelected = hidden.value === value;
    hidden.value = isSelected ? '' : value;
    const group = btn.parentElement;
    group.querySelectorAll('.inkubasi-radio-btn').forEach(b => {
        const active = !isSelected && b.dataset.value === value;
        b.classList.toggle('bg-sky-50', active);
        b.classList.toggle('border-sky-300', active);
        b.classList.toggle('text-sky-700', active);
        b.classList.toggle('border-gray-200', !active);
        b.classList.toggle('bg-white', !active);
        b.classList.toggle('text-gray-600', !active);
    });
    formDirty = true;
}

// Shift assignment toggle
function setAssignment(secId, col, shift) {
    const myShift = {{ $myShift }};
    const hidden = document.getElementById(`sa-${secId}-${col}`);
    if (hidden) hidden.value = shift;
    for (const s of [1, 2]) {
        const btn = document.getElementById(`sa-btn-${secId}-${col}-${s}`);
        if (!btn) continue;
        if (s === shift) {
            btn.className = 'px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors ' + (s === 1 ? 'bg-sky-500 text-white' : 'bg-amber-500 text-white');
        } else {
            btn.className = 'px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors bg-gray-100 text-gray-500 hover:bg-gray-200';
        }
    }
    // Toggle editability of column inputs based on assignment
    const isMine = (shift === myShift);
    document.querySelectorAll(`input[data-section-id="${secId}"][data-col="${col}"]`).forEach(inp => {
        inp.disabled = !isMine;
        inp.classList.toggle('bg-gray-100', !isMine);
        inp.classList.toggle('bg-white', isMine);
        if (!isMine) inp.value = '';
    });
    // Also toggle time inputs in the same column (JAM)
    const colCell = document.querySelectorAll(`input[name*="entries"][name*="[${col}]"][name*="start_time"]`);
    colCell.forEach(inp => {
        const row = inp.closest('tr');
        if (!row) return;
        const secInput = row.querySelector(`input[data-section-id="${secId}"]`);
        if (!secInput) return;
        inp.disabled = !isMine;
        inp.classList.toggle('bg-gray-100', !isMine);
        inp.classList.toggle('bg-white', isMine);
        if (!isMine) inp.value = '';
    });
    formDirty = true;
}

// Custom alert modal
function showAlertModal(title, msg) {
    document.getElementById('alert-modal-title').textContent = title;
    document.getElementById('alert-modal-msg').textContent = msg;
    document.getElementById('alert-modal').classList.remove('hidden');
}
function closeAlertModal() {
    document.getElementById('alert-modal').classList.add('hidden');
}

// Custom confirm modal
let _confirmCallback = null;
function showConfirmModal(title, msg, btnLabel, onConfirm) {
    document.getElementById('confirm-modal-title').textContent = title;
    document.getElementById('confirm-modal-msg').textContent = msg;
    document.getElementById('confirm-modal-ok').textContent = btnLabel;
    _confirmCallback = onConfirm;
    document.getElementById('confirm-modal').classList.remove('hidden');
}
function closeConfirmModal() {
    document.getElementById('confirm-modal').classList.add('hidden');
    _confirmCallback = null;
}
function doConfirm() {
    const cb = _confirmCallback;
    closeConfirmModal();
    if (cb) cb();
}

// Helper: returns Set of missing exposure labels for a given shift
function getMissingCols(checkShift) {
    const missing = new Set();
    document.querySelectorAll('input[id^="sa-"]').forEach(inp => {
        const m = inp.id.match(/^sa-(\d+)-(\d+)$/);
        if (!m) return;
        const secId = m[1], col = m[2];
        if (parseInt(inp.value) !== checkShift) return;
        const bInputs = document.querySelectorAll(`input[data-section-id="${secId}"][data-col="${col}"][data-type="b"]`);
        if (!bInputs.length) return;
        bInputs.forEach(bi => { if (!bi.dataset.optional && bi.value === '') missing.add('Exposure ' + col); });
    });
    return missing;
}

// Kirim Laporan: validate then open password modal
function openSubmitFlow() {
    const myShift = {{ $myShift }};
    const missing = getMissingCols(myShift);
    if (missing.size > 0) {
        showAlertModal(
            'Data Belum Lengkap',
            'Kolom Shift ' + myShift + ' berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
        );
        return;
    }
    openConfirmModal('submit');
}

// Validate assigned columns before handover or submit
function validateAction(action) {
    const myShift = {{ $myShift }};
    const checkShift = action === 'handover' ? 1 : myShift;
    const missing = getMissingCols(checkShift);
    if (missing.size > 0) {
        showAlertModal(
            'Data Belum Lengkap',
            'Kolom Shift ' + checkShift + ' berikut belum diisi lengkap:\n\u2022 ' + [...missing].join('\n\u2022 ') + '\n\nIsi semua data sebelum melanjutkan.'
        );
        return false;
    }
    return true;
}

// Warn before navigating away if form has been changed
let formDirty = false;
document.getElementById('laporan-form')?.addEventListener('change', () => { formDirty = true; });
document.getElementById('laporan-form')?.addEventListener('submit', () => { formDirty = false; });
window.addEventListener('beforeunload', (e) => {
    if (formDirty) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
@endpush
