@extends('layouts.app')

@section('title', 'Dashboard Supervisor')
@section('page-title', 'Dashboard')
@section('content')

<x-welcome-banner />

{{-- Stats --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
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
</div>

{{-- Quick link --}}
@if ($pending > 0)
<div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-center justify-between mb-6">
    <div class="flex items-center gap-3">
        <svg class="w-5 h-5 text-amber-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
        </svg>
        <p class="text-sm font-medium text-amber-800">Ada <span class="font-bold">{{ $pending }}</span> laporan menunggu tinjauan Anda.</p>
    </div>
    <a href="{{ route('supervisor.laporan-masuk') }}"
       class="flex-shrink-0 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-colors">
        Tinjau Sekarang
    </a>
</div>
@endif

{{-- 3 section lists --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Laporan Baru --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="h-7 w-7 rounded-lg bg-gray-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm text-gray-800">Laporan Baru</h3>
                @if(($counts['pending'] ?? 0) > 0)
                <span class="ml-1 text-xs font-semibold bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded-full">{{ $counts['pending'] ?? 0 }}</span>
                @endif
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse ($pendingReports as $item)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->batch_number }} · {{ $item->created_at->isoFormat('D MMM') }}</p>
                </div>
                <a href="{{ route('supervisor.laporan.preview', $item) }}"
                   class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat
                </a>
            </div>
            @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-gray-400">Tidak ada laporan baru</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Sedang Dimonitoring --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="h-7 w-7 rounded-lg bg-amber-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm text-gray-800">Sedang Dimonitoring</h3>
                @if(($counts['monitoring'] ?? 0) > 0)
                <span class="ml-1 text-xs font-semibold bg-amber-100 text-amber-600 px-1.5 py-0.5 rounded-full">{{ $counts['monitoring'] ?? 0 }}</span>
                @endif
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse ($monitoringReports as $item)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $item->batch_number }}
                        @if ($item->lockedByUser)
                            · <span class="text-gray-500">{{ $item->lockedByUser->name }}</span>
                        @else
                            · <span class="text-sky-500 font-medium">Tersedia</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('supervisor.laporan.preview', $item) }}"
                   class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat
                </a>
            </div>
            @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-gray-400">Tidak ada laporan dimonitoring</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Sedang Dibaca --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100">
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <div class="h-7 w-7 rounded-lg bg-indigo-100 flex items-center justify-center">
                    <svg class="w-3.5 h-3.5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                    </svg>
                </div>
                <h3 class="font-semibold text-sm text-gray-800">Sedang Dibaca</h3>
                @if(($counts['reading'] ?? 0) > 0)
                <span class="ml-1 text-xs font-semibold bg-indigo-100 text-indigo-600 px-1.5 py-0.5 rounded-full">{{ $counts['reading'] ?? 0 }}</span>
                @endif
            </div>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse ($readingReports as $item)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $item->batch_number }}
                        @if ($item->lockedByUser)
                            · <span class="text-gray-500">{{ $item->lockedByUser->name }}</span>
                        @else
                            · <span class="text-sky-500 font-medium">Tersedia</span>
                        @endif
                    </p>
                </div>
                <a href="{{ route('supervisor.laporan.preview', $item) }}"
                   class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat
                </a>
            </div>
            @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-gray-400">Tidak ada laporan dibaca</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

@endsection
