@extends('layouts.app')

@section('title', 'Dashboard Manajer')
@section('page-title', 'Dashboard')
@section('content')

{{-- Welcome Banner --}}
<x-welcome-banner />

{{-- Quick link --}}
@if ($pending > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p class="text-sm font-medium text-amber-800">Ada <span class="font-bold">{{ $pending }}</span> laporan menunggu tinjauan Anda.</p>
    </div>
    <a href="{{ route('manager.incoming-reports') }}"
       class="flex-shrink-0 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-colors">
        Tinjau Sekarang
    </a>
</div>
@endif

@php
    $managerTmsTotalMethods = $managerTmsTotalMethods ?? 0;
    $managerTmsTotalReports = $managerTmsTotalReports ?? 0;
    $managerTmsByMethod = $managerTmsByMethod ?? [];
@endphp

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-6 mt-4">
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-xl bg-amber-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Menunggu Review</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $pending }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-xl bg-green-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Disetujui</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $approved }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-xl bg-red-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Dikembalikan</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $returned }}</p>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5 flex items-center gap-4">
        <div class="h-12 w-12 rounded-xl bg-rose-50 flex items-center justify-center flex-shrink-0">
            <svg class="w-6 h-6 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
        <div>
            <p class="text-xs text-gray-500 font-medium">Total Metode yang Berstatus TMS (Approved)</p>
            <p class="text-2xl font-bold text-gray-800 mt-0.5">{{ $managerTmsTotalMethods }}</p>
        </div>
    </div>
</div>

<div class="mt-6 bg-white rounded-xl border border-gray-100 shadow-sm">
    <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3">
        <div>
            <h3 class="text-sm font-semibold text-gray-800">Ringkasan Metode yang Berstatus TMS (Laporan Approved)</h3>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $managerTmsTotalMethods }} metode berstatus TMS dari {{ $managerTmsTotalReports }} laporan yang sudah Anda setujui.
            </p>
        </div>
        <a href="{{ route('report-archive.index') }}"
           class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-medium border border-gray-200 text-gray-600 hover:bg-gray-50 transition-colors">
            Buka Arsip
        </a>
    </div>

    @if ($managerTmsTotalMethods === 0)
    <div class="px-5 py-10 text-center">
        <p class="text-sm text-gray-500">Belum ada metode yang berstatus TMS pada laporan approved.</p>
    </div>
    @else
    <div class="p-5 grid grid-cols-1 xl:grid-cols-2 gap-4">
        @foreach ($managerTmsByMethod as $method)
        <div class="rounded-xl border border-rose-100 bg-rose-50/40">
            <div class="px-4 py-3 border-b border-rose-100 flex items-center justify-between">
                <p class="text-sm font-semibold text-rose-800">{{ $method['label'] }}</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-rose-100 text-rose-700">
                    {{ $method['count'] }} TMS
                </span>
            </div>
            <div class="p-3 space-y-2 max-h-72 overflow-y-auto">
                @forelse ($method['items'] as $item)
                <div class="rounded-lg border border-white/80 bg-white px-3 py-2">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $item['product_name'] }}</p>
                        <a href="{{ route('report-archive.show', $item['report_id']) }}"
                           class="inline-flex items-center gap-1 text-xs font-medium text-sky-600 hover:text-sky-700 whitespace-nowrap">
                            Lihat
                        </a>
                    </div>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Batch: {{ $item['batch_number'] ?: 'N/A' }}
                    </p>
                    <p class="text-xs text-gray-500 mt-0.5">
                        Section: {{ $item['section_label'] }}
                        @if (($item['instance_number'] ?? 1) > 1)
                            - Duplikat {{ $item['instance_number'] }}
                        @endif
                    </p>
                    @if (! empty($item['approved_at']))
                    <p class="text-[11px] text-gray-400 mt-1">
                        Approved: {{ $item['approved_at']->isoFormat('D MMM Y, HH:mm') }}
                    </p>
                    @endif
                </div>
                @empty
                <div class="px-2 py-4 text-center">
                    <p class="text-xs text-gray-400">Tidak ada data TMS untuk metode ini.</p>
                </div>
                @endforelse
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

@endsection
