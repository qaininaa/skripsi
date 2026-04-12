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

@if (!$isEditable && in_array($report->status, ['monitoring', 'reading']) && $report->locked_by !== null)
<div class="mb-5 px-4 py-3 bg-amber-50 border border-amber-200 rounded-xl text-sm text-amber-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
    </svg>
    Laporan ini sedang {{ $report->status === 'monitoring' ? 'dimonitoring' : 'dibaca' }} oleh analis <span class="font-semibold">{{$report->lockedByUser?->name ?? 'analis lain' }}.</span>Anda hanya dapat melihat.
</div>
@endif

{{-- ── 1. Pemantauan Ruang ─────────────────────────────── --}}
@include('pages.laporan.partials.section-info')

@php $hd = $report->header_data ?? []; @endphp

@if ($needsAirSampler)
@include('pages.laporan.partials.section-alat')
@endif

@if (!empty($report->reportType->medium_groups ?? []))
@include('pages.laporan.partials.section-medium')
@endif

@if ($needsInkubator)
@include('pages.laporan.partials.section-inkubator')
@endif

{{-- ── 4+. Tabel Pengukuran per Seksi ─────────────────── --}}
@foreach ($sectionInstances as $sectionInstance)
@php $section = $sectionInstance['section']; $instance = $sectionInstance['instance']; $totalInstances = $sectionInstance['totalInstances']; @endphp
@include('pages.laporan.partials.section-tabel', ['instance' => $instance, 'totalInstances' => $totalInstances])
@endforeach

@include('pages.laporan.partials.section-ttd')

@include('pages.laporan.partials.bottom-bar')

</form>
@include('pages.laporan.partials.modals')

@endsection

@push('scripts')
@include('pages.laporan.partials.scripts')
@endpush
