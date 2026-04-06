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
@foreach ($report->reportType->sections as $section)@include('pages.laporan.partials.section-tabel')
@endforeach

@include('pages.laporan.partials.section-catatan')

@include('pages.laporan.partials.section-ttd')

@include('pages.laporan.partials.bottom-bar')

</form>
@include('pages.laporan.partials.modals')

@endsection

@push('scripts')
@include('pages.laporan.partials.scripts')
@endpush
