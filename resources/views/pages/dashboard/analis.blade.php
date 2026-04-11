@extends('layouts.app')

@section('title', 'Dashboard Analyst')
@section('content')

<x-welcome-banner />

@php
    use App\Models\Report;
    $counts = Report::selectRaw('status, count(*) as total')
        ->groupBy('status')
        ->pluck('total', 'status');

    $pendingReports    = Report::with('reportType', 'lockedByUser')
        ->where('status', 'pending')
        ->latest()->take(5)->get();
    $monitoringReports = Report::with('reportType', 'lockedByUser')
        ->where('status', 'monitoring')
        ->latest()->take(5)->get();
    $readingReports    = Report::with('reportType', 'lockedByUser')
        ->where('status', 'reading')
        ->latest()->take(5)->get();
@endphp

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
            <a href="{{ route('laporan.index', ['status' => 'pending']) }}" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Lihat semua →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse ($pendingReports as $item)
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">{{ $item->batch_number }} · {{ $item->created_at->isoFormat('D MMM') }}</p>
                </div>
                <div class="ml-3 flex-shrink-0 flex items-center gap-1.5">
                    <a href="{{ route('laporan.lihat', $item) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                    <button type="button"
                            onclick="confirmMulai('{{ route('laporan.isi', $item) }}', '{{ addslashes($item->product_name) }}', '{{ addslashes($item->batch_number) }}')"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-500 text-white text-xs font-medium hover:bg-sky-600 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        </svg>
                        Mulai
                    </button>
                </div>
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
            <a href="{{ route('laporan.index', ['status' => 'monitoring']) }}" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Lihat semua →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse ($monitoringReports as $item)
            @php $isMine = $item->locked_by === auth()->id(); $unclaimed = $item->locked_by === null; @endphp
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $item->batch_number }}
                        @if ($isMine)
                            · <span class="text-amber-600 font-medium">Saya</span>
                        @elseif ($item->lockedByUser)
                            · <span class="text-gray-500">{{ $item->lockedByUser->name }}</span>
                        @else
                            · <span class="text-sky-500 font-medium">Tersedia</span>
                        @endif
                    </p>
                </div>
                @if ($isMine)
                <a href="{{ route('laporan.isi', $item) }}"
                   class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-amber-500 text-white text-xs font-medium hover:bg-amber-600 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Lanjutkan
                </a>
                @elseif ($unclaimed)
                <div class="ml-3 flex-shrink-0 flex items-center gap-1.5">
                    <a href="{{ route('laporan.lihat', $item) }}"
                       class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        Lihat
                    </a>
                    <button type="button"
                            onclick="confirmMulai('{{ route('laporan.isi', $item) }}', '{{ addslashes($item->product_name) }}', '{{ addslashes($item->batch_number) }}', true)"
                            class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-sky-500 text-white text-xs font-medium hover:bg-sky-600 transition-colors">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                        </svg>
                        Mulai
                    </button>
                </div>
                @else
                <a href="{{ route('laporan.isi', $item) }}"
                   class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat
                </a>
                @endif
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
            <a href="{{ route('laporan.index', ['status' => 'reading']) }}" class="text-xs text-sky-600 hover:text-sky-800 font-medium">Lihat semua →</a>
        </div>
        <div class="divide-y divide-gray-50">
            @forelse ($readingReports as $item)
            @php $isMine = $item->locked_by === auth()->id(); @endphp
            <div class="flex items-center justify-between px-5 py-3 hover:bg-gray-50 transition-colors">
                <div class="min-w-0">
                    <p class="text-sm font-medium text-gray-800 truncate">{{ $item->product_name }}</p>
                    <p class="text-xs text-gray-400 mt-0.5">
                        {{ $item->batch_number }}
                        @if ($isMine)
                            · <span class="text-indigo-600 font-medium">Saya</span>
                        @elseif ($item->lockedByUser)
                            · <span class="text-gray-500">{{ $item->lockedByUser->name }}</span>
                        @else
                            · <span class="text-sky-500 font-medium">Tersedia</span>
                        @endif
                    </p>
                </div>
                @if ($isMine)
                <a href="{{ route('laporan.isi', $item) }}"
                   class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-500 text-white text-xs font-medium hover:bg-indigo-600 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Lanjutkan
                </a>
                @else
                <a href="{{ route('laporan.isi', $item) }}"
                   class="ml-3 flex-shrink-0 inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-gray-50 border border-gray-200 text-gray-500 text-xs font-medium hover:bg-gray-100 transition-colors">
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Lihat
                </a>
                @endif
            </div>
            @empty
            <div class="px-5 py-8 text-center">
                <p class="text-sm text-gray-400">Tidak ada laporan dibaca</p>
            </div>
            @endforelse
        </div>
    </div>

</div>

{{-- Confirm Start Modal (reused from laporan/index) --}}
<div id="mulai-modal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4">
    <div class="absolute inset-0 bg-black/40" onclick="closeMulaiModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-sm p-6 space-y-4">
        <div class="flex items-center gap-3">
            <div class="h-10 w-10 rounded-full bg-sky-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-sky-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-800">Mulai Pengerjaan Laporan?</h3>
                <p class="text-xs text-gray-500 mt-0.5">Laporan ini akan masuk ke tahap monitoring.</p>
            </div>
        </div>
        <div class="bg-gray-50 rounded-xl px-4 py-3 space-y-1 text-sm">
            <div class="flex gap-2">
                <span class="text-gray-500 w-28 flex-shrink-0">Nama Produk</span>
                <span class="font-medium text-gray-800" id="mulai-product"></span>
            </div>
            <div class="flex gap-2">
                <span class="text-gray-500 w-28 flex-shrink-0">Nomor Batch</span>
                <span class="font-medium text-gray-800" id="mulai-batch"></span>
            </div>
        </div>
        <p class="text-sm text-gray-600" id="mulai-desc">Setelah dimulai, Anda akan menjadi penanggung jawab monitoring laporan ini. Analis lain hanya bisa melihat.</p>
        <div class="flex justify-end gap-2 pt-1">
            <button type="button" onclick="closeMulaiModal()"
                    class="px-4 py-2 rounded-lg border border-gray-200 text-sm text-gray-600 hover:bg-gray-50">
                Batal
            </button>
            <a id="mulai-confirm-link" href="#"
               class="px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors">
                Ya, Mulai
            </a>
        </div>
    </div>
</div>

<script>
function confirmMulai(url, product, batch, isResume) {
    document.getElementById('mulai-product').textContent = product;
    document.getElementById('mulai-batch').textContent = batch;
    document.getElementById('mulai-confirm-link').href = url;
    if (isResume) {
        document.querySelector('#mulai-modal h3').textContent = 'Lanjutkan Monitoring Laporan?';
        document.querySelector('#mulai-modal .text-xs').textContent = 'Laporan ini sedang dalam tahap monitoring.';
        document.getElementById('mulai-desc').textContent = 'Anda akan mengambil alih pengerjaan laporan ini. Data yang sudah diisi analis sebelumnya tidak dapat diubah.';
    } else {
        document.querySelector('#mulai-modal h3').textContent = 'Mulai Pengerjaan Laporan?';
        document.querySelector('#mulai-modal .text-xs').textContent = 'Laporan ini akan masuk ke tahap monitoring.';
        document.getElementById('mulai-desc').textContent = 'Setelah dimulai, Anda akan menjadi penanggung jawab monitoring laporan ini. Analis lain hanya bisa melihat.';
    }
    document.getElementById('mulai-modal').classList.remove('hidden');
}
function closeMulaiModal() {
    document.getElementById('mulai-modal').classList.add('hidden');
}
</script>

@endsection
