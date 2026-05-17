@extends('layouts.app')

@section('title', 'Arsip Laporan')
@section('page-title', 'Arsip Laporan')
@section('content')

<div class="space-y-5">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">Arsip Laporan</h2>
        <p class="text-sm text-gray-500 mt-0.5">Pilih folder arsip terlebih dahulu, lalu cari laporan yang dibutuhkan.</p>
    </div>

    @if (! $activeFolder)
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm p-5">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($folders as $folder)
                    <a href="{{ route('report-archive.index', ['folder' => $folder['key']]) }}"
                        class="group rounded-xl border p-4 transition-colors border-gray-200 hover:border-green-200 hover:bg-green-50/60">
                        <div class="flex items-start justify-between gap-3">
                            <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-gray-100 text-gray-600 group-hover:bg-green-100 group-hover:text-green-700">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.7" d="M3 7.5A1.5 1.5 0 014.5 6h4.379a1.5 1.5 0 011.06.44l1.12 1.12a1.5 1.5 0 001.06.44H19.5A1.5 1.5 0 0121 9.5v8A1.5 1.5 0 0119.5 19h-15A1.5 1.5 0 013 17.5v-10z" />
                                </svg>
                            </span>
                            <span class="text-xs font-medium text-gray-500">
                                {{ $folder['count'] }} arsip
                            </span>
                        </div>
                        <h3 class="mt-3 text-base font-semibold text-gray-800">
                            AHU {{ $folder['name'] }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            {{ $folder['description'] }}
                        </p>
                    </a>
                @endforeach
            </div>
        </div>
    @else
        <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
            <div class="px-5 py-4 border-b border-gray-100">
                <div class="flex flex-col md:flex-row md:items-center md:justify-start gap-3">
                    <a href="{{ route('report-archive.index') }}"
                        class="inline-flex items-center justify-center px-3 py-2 rounded-lg text-sm text-gray-500 border border-gray-300 hover:text-gray-700 hover:bg-gray-50 transition-colors">
                        Kembali ke folder
                    </a>
                    <div>
                        <p class="text-xs uppercase tracking-wide text-gray-500">Folder Aktif</p>
                        <h3 class="text-base font-semibold text-gray-800">AHU {{ $activeFolder['name'] }}</h3>
                    </div>
                </div>

                <form method="GET" action="{{ route('report-archive.index') }}" class="flex items-center gap-3 mt-4">
                    <input type="hidden" name="folder" value="{{ $activeFolder['key'] }}">
                    <div class="relative flex-1 max-w-sm">
                        <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama produk atau batch..."
                            class="w-full pl-9 pr-4 py-2 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-medium hover:bg-green-700 transition-colors">
                        Cari
                    </button>
                    <a href="{{ route('report-archive.index', ['folder' => $activeFolder['key']]) }}"
                        class="px-3 py-2 rounded-lg text-sm text-gray-500 border border-gray-300 hover:text-gray-700 hover:bg-gray-50 transition-colors">
                        Reset
                    </a>
                </form>
            </div>

            <div class="overflow-x-auto">
                @if ($reports->isEmpty())
                    <div class="flex flex-col items-center justify-center py-14 text-center">
                        <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8" />
                        </svg>
                        <p class="text-sm font-medium text-gray-400">Belum ada laporan yang diarsipkan.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-100">
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider w-16">No</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Jenis Laporan</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Produk</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Batch Produk</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Print</th>
                                <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            @foreach ($reports as $report)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-5 py-3.5 text-gray-500">
                                        {{ ($reports->currentPage() - 1) * $reports->perPage() + $loop->iteration }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-700 whitespace-nowrap">
                                        {{ $report->created_at->isoFormat('D MMM Y') }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-700">
                                        {{ $report->reportType->annex_number }} - {{ $report->reportType->name ?? '-' }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-700">
                                        {{ $report->product_name }}
                                    </td>
                                    <td class="px-5 py-3.5 text-gray-700">
                                        {{ $report->batch_number ?: '-' }}
                                    </td>
                                    <td class="px-5 py-3.5">
                                        @if ($report->printed_at)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-green-100 text-green-700">
                                                Sudah Print
                                            </span>
                                            <p class="mt-1 text-[11px] text-gray-500">
                                                {{ $report->printed_at->isoFormat('D MMM Y, HH:mm') }}
                                            </p>
                                            @if ($report->printedByUser)
                                                <p class="text-[11px] text-gray-400">
                                                    oleh {{ $report->printedByUser->name }}
                                                </p>
                                            @endif
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">
                                                Belum Print
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-5 py-3.5">
                                        <a href="{{ route('report-archive.show', ['report' => $report->id, 'folder' => $activeFolder['key']]) }}"
                                            target="_blank" rel="noopener noreferrer"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-50 text-green-700 text-xs font-medium hover:bg-green-100 transition-colors">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Print
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    @if ($reports->hasPages())
                        <div class="px-5 py-4 border-t border-gray-100">
                            {{ $reports->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    @endif
</div>
@endsection
