@extends('layouts.app')

@section('title', 'Isi Laporan — ' . $report->reportType->annex_number)
@section('page-title', 'Isi Laporan')
@section('avatar-color', 'bg-green-600')
@section('content')
<form method="POST" action="{{ route('laporan.save', $report) }}" id="laporan-form">
@csrf

@include('pages.laporan.partials.action-bar')

@if (session('success'))
<div class="mb-5 px-4 py-3 bg-green-50 border border-green-100 rounded-xl text-sm text-green-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
    </svg>
    {{ session('success') }}
</div>
@endif

@if ($errors->has('cfu'))
<div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-start gap-2">
    <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    <span>{{ $errors->first('cfu') }}</span>
</div>
@endif

@if (!$isEditable && in_array($report->status, ['monitoring', 'reading']) && $report->locked_by !== null)
<div class="mb-5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
    Laporan ini sedang {{ $report->status === 'monitoring' ? 'dimonitoring' : 'dibaca' }} oleh analis <span class="font-semibold">{{$report->lockedByUser?->name ?? 'analis lain' }}.</span>Anda hanya dapat melihat.
</div>
@endif

@if (!empty($returnedApproval?->notes))
<div class="mb-5 px-4 py-3 bg-orange-50 border border-orange-200 rounded-xl flex items-start gap-3">
    <div class="h-8 w-8 rounded-full bg-orange-100 flex items-center justify-center flex-shrink-0 mt-0.5">
        <svg class="w-4 h-4 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6" />
        </svg>
    </div>
    <div>
        <p class="text-sm font-semibold text-orange-800">Laporan dikembalikan untuk direvisi</p>
        <p class="text-sm text-orange-700 mt-0.5 italic">&ldquo;{{ $returnedApproval->notes }}&rdquo;</p>
        <p class="text-xs text-orange-500 mt-1">— {{ $returnedApproval->user?->name ?? 'Reviewer' }}</p>
    </div>
</div>
@endif

{{-- ── 1. Pemantauan Ruang ─────────────────────────────── --}}
@include('pages.laporan.partials.section-info')

@php $hd = $report->header_data ?? []; @endphp

@if ($needsAirSampler)
@include('pages.laporan.partials.section-alat', ['isEditable' => $isEditable && $isMonitoringPhase])
@endif

@if ($needsMedium && ($report->reportType->media->isNotEmpty() ?? false))
@include('pages.laporan.partials.section-medium', ['isEditable' => $isEditable && $isMonitoringPhase])
@endif

@if ($needsInkubator)
@include('pages.laporan.partials.section-inkubator', ['isEditable' => $isEditable && $isMonitoringPhase])
@endif

{{-- ── 4+. Tabel Pengukuran per Seksi ─────────────────── --}}
@foreach ($sectionInstances as $sectionInstance)
@php
    $section        = $sectionInstance['section'];
    $instance       = $sectionInstance['instance'];
    $totalInstances = $sectionInstance['totalInstances'];
    $secNum         = $sectionInstance['secNum'];
@endphp
@include('pages.laporan.partials.section-tabel', ['instance' => $instance, 'totalInstances' => $totalInstances, 'secNum' => $secNum])
@endforeach

@include('pages.laporan.partials.bottom-bar')

</form>
@include('pages.laporan.partials.modals')

@endsection

@push('scripts')
@include('pages.laporan.partials.scripts')
@endpush
