@extends('layouts.admin')

@section('title', 'Laporan Saya')
@section('page-title', 'Laporan')
@section('avatar-color', 'bg-sky-600')

@section('sidebar')
    @include('dashboard.laporan.partials.sidebar')
@endsection

@section('content')

@php
    $tabs = [
        'all'         => ['label' => 'Semua',      'color' => 'gray'],
        'pending'     => ['label' => 'Menunggu',   'color' => 'gray'],
        'in_progress' => ['label' => 'Dikerjakan', 'color' => 'yellow'],
        'submitted'   => ['label' => 'Dikirim',    'color' => 'blue'],
        'approved'    => ['label' => 'Disetujui',  'color' => 'green'],
        'rejected'    => ['label' => 'Ditolak',    'color' => 'red'],
    ];

    $total = $counts->sum();
@endphp

<div class="space-y-5">

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-gray-800">Laporan Pemantauan</h2>
            <p class="text-sm text-gray-500 mt-0.5">Daftar semua penugasan laporan yang diberikan kepada Anda.</p>
        </div>
    </div>

    {{-- Status Tabs --}}
    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex overflow-x-auto border-b border-gray-100 scrollbar-none">
            @foreach ($tabs as $key => $tab)
                @php
                    $count = $key === 'all' ? $total : ($counts[$key] ?? 0);
                    $isActive = $status === $key;
                @endphp
                <a href="{{ route('laporan.index', ['status' => $key]) }}"
                   class="flex items-center gap-2 px-4 py-3.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                          {{ $isActive
                              ? 'border-sky-500 text-sky-600'
                              : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                    {{ $tab['label'] }}
                    @if ($count > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-semibold
                                     {{ $isActive ? 'bg-sky-100 text-sky-700' : 'bg-gray-100 text-gray-600' }}">
                            {{ $count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        {{-- Table --}}
        @if ($items->isEmpty())
            <div class="flex flex-col items-center justify-center py-16 text-center px-4">
                <div class="h-14 w-14 rounded-2xl bg-gray-50 flex items-center justify-center mb-4">
                    <svg class="w-7 h-7 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                </div>
                <p class="text-sm font-medium text-gray-500">Tidak ada laporan
                    @if ($status !== 'all')
                        dengan status <span class="font-semibold">{{ $tabs[$status]['label'] }}</span>
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-xs font-semibold uppercase tracking-wide text-gray-500 bg-gray-50/60">
                            <th class="px-5 py-3 text-left">Tanggal</th>
                            <th class="px-5 py-3 text-left">Jenis Laporan</th>
                            <th class="px-5 py-3 text-center">Shift</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3.5 text-gray-700 whitespace-nowrap font-medium">
                                    {{ $item->tanggal->isoFormat('D MMM Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    <span class="font-semibold text-sky-700">{{ $item->reportType->annex_number }}</span>
                                    <span class="text-gray-400 mx-1">—</span>
                                    <span class="text-gray-600">{{ $item->reportType->name }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @php
                                        $myShift = $item->shift1_analis_id == Auth::id() ? 1 : 2;
                                    @endphp
                                    <span class="inline-flex items-center justify-center h-6 w-14 rounded-full text-xs font-semibold
                                                 {{ $myShift === 1 ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700' }}">
                                        Shift {{ $myShift }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @php
                                        $badge = match($item->status) {
                                            'pending'     => ['bg-gray-100 text-gray-600',   'Menunggu'],
                                            'in_progress' => ['bg-yellow-100 text-yellow-700','Dikerjakan'],
                                            'submitted'   => ['bg-blue-100 text-blue-700',   'Dikirim'],
                                            'approved'    => ['bg-green-100 text-green-700', 'Disetujui'],
                                            'rejected'    => ['bg-red-100 text-red-700',     'Ditolak'],
                                            default       => ['bg-gray-100 text-gray-600',   $item->status],
                                        };
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if ($item->status === 'pending')
                                        <button disabled
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 text-sky-400 text-xs font-medium cursor-not-allowed border border-sky-100">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                            Mulai
                                        </button>
                                    @elseif ($item->status === 'in_progress')
                                        <button disabled
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-yellow-50 text-yellow-600 text-xs font-medium cursor-not-allowed border border-yellow-100">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Lanjutkan
                                        </button>
                                    @else
                                        <button disabled
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-400 text-xs font-medium cursor-not-allowed border border-gray-100">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($items->hasPages())
                <div class="px-5 py-3 border-t border-gray-100">
                    {{ $items->links() }}
                </div>
            @endif
        @endif
    </div>

</div>
@endsection
