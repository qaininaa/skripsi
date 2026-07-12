@extends('layouts.app')

@section('title', 'Laporan Masuk')
@section('page-title', 'Laporan Masuk')
@section('content')

@php
    $tabs = [
        'pending'  => ['label' => 'Menunggu Review', 'color' => 'amber'],
        'approved' => ['label' => 'Disetujui',        'color' => 'green'],
        'rejected' => ['label' => 'Ditolak',          'color' => 'red'],
    ];
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Laporan Masuk</h2>
        <p class="text-sm text-gray-500 mt-0.5">Laporan dari analis yang dikirimkan kepada Anda untuk ditinjau.</p>
    </div>

    @if (session('success'))
        <div class="bg-green-50 border border-green-200 rounded-xl px-4 py-3 text-sm text-green-800 flex items-center gap-2">
            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex overflow-x-auto border-b border-gray-100 scrollbar-none">
            @foreach ($tabs as $key => $tabDef)
                @php
                    $isActive = $tab === $key;
                    $count    = $counts[$key] ?? 0;
                @endphp
                <a href="{{ route('supervisor.laporan-masuk', ['tab' => $key]) }}"
                   class="flex items-center gap-2 px-4 py-3.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                          {{ $isActive ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                    {{ $tabDef['label'] }}
                    @if ($count > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-semibold
                                     {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto">
            @if ($reports->isEmpty())
                <div class="flex flex-col items-center justify-center py-14 text-center">
                    <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-400">Tidak ada laporan di sini.</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Produk</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor Batch Produk</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dimonitoring Oleh</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Dibaca Oleh</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($reports as $report)
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-gray-700 whitespace-nowrap">
                                    {{ $report->created_at->isoFormat('D MMM Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ $report->product_name }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ $report->batch_number ?: '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    @php
                                        $monitoringNames = \App\Models\User::whereIn('id', $report->analyst_monitoring ?? [])->pluck('name');
                                    @endphp
                                    {{ $monitoringNames->isNotEmpty() ? $monitoringNames->join(', ') : '—' }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    @php
                                        $readingNames = \App\Models\User::whereIn('id', $report->analyst_reading ?? [])->pluck('name');
                                    @endphp
                                    {{ $readingNames->isNotEmpty() ? $readingNames->join(', ') : '—' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($report->approval_status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                            Menunggu
                                        </span>
                                    @elseif ($report->approval_status === 'approved')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                            Disetujui
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-red-100 text-red-700">
                                            Ditolak
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route('supervisor.laporan.show', $report->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 text-emerald-700 text-xs font-medium hover:bg-emerald-100 transition-colors">
                                        Tinjau
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                @if ($reports->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $reports->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

</div>
@endsection
