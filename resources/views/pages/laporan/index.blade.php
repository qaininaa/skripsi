@extends('layouts.app')

@section('title', 'Laporan Saya')
@section('page-title', 'Laporan')
@section('avatar-color', 'bg-green-600')
@section('content')

@if (session('error'))
<div class="mb-5 px-4 py-3 bg-red-50 border border-red-200 rounded-xl text-sm text-red-700 flex items-center gap-2">
    <svg class="w-4 h-4 text-red-500 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
    </svg>
    {{ session('error') }}
</div>
@endif

@php
    $tabs = [
        'all'         => ['label' => 'Semua',               'color' => 'gray'],
        'pending'     => ['label' => 'Belum Dikerjakan',    'color' => 'gray'],
        'monitoring'  => ['label' => 'Sedang Dimonitoring', 'color' => 'yellow'],
        'reading'     => ['label' => 'Sedang Dibaca',       'color' => 'indigo'],
        'submitted'   => ['label' => 'Dikirim',             'color' => 'blue'],
        'returned'    => ['label' => 'Dikembalikan',        'color' => 'orange'],
        'approved'    => ['label' => 'Disetujui',           'color' => 'green'],
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
                            <th class="px-5 py-3 text-left">Nama Produk</th>
                            <th class="px-5 py-3 text-left">Nomor Batch Produk</th>
                            <th class="px-5 py-3 text-center">Status</th>
                            <th class="px-5 py-3 text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($items as $item)
                            <tr class="hover:bg-gray-50/50 transition-colors">
                                <td class="px-5 py-3.5 text-gray-700 whitespace-nowrap font-medium">
                                    {{ $item->created_at->isoFormat('D MMM Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ $item->product_name }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ $item->batch_number }}
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @php
                                        $badge = match($item->status) {
                                            'pending'    => ['bg-gray-100 text-gray-600',     'Belum Dikerjakan'],
                                            'monitoring' => ['bg-yellow-100 text-yellow-700',  'Sedang Dimonitoring'],
                                            'reading'    => ['bg-indigo-100 text-indigo-700',  'Sedang Dibaca'],
                                            'submitted'  => ['bg-blue-100 text-blue-700',      'Dikirim ke Supervisor'],
                                            'pending_manager' => ['bg-sky-100 text-sky-700',   'Dikirim ke Manajer'],
                                            'returned'   => ['bg-orange-100 text-orange-700',  'Dikembalikan'],
                                            'approved'   => ['bg-green-100 text-green-700',    'Disetujui'],
                                            default      => ['bg-gray-100 text-gray-600',      $item->status],
                                        };
                                        $lockedName = (in_array($item->status, ['monitoring', 'reading'], true) && $item->lockedByUser)
                                            ? $item->lockedByUser->name : null;
                                        $targetApproval = match($item->status) {
                                            'submitted' => $item->approvals->firstWhere('step', 2),
                                            'pending_manager' => $item->approvals->firstWhere('step', 3),
                                            default => null,
                                        };
                                        $targetName = $targetApproval?->user?->name;
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold {{ $badge[0] }}">
                                        {{ $badge[1] }}
                                    </span>
                                    @if ($targetName)
                                        <div class="text-[11px] text-gray-400 mt-0.5">ke {{ $targetName }}</div>
                                    @endif
                                    @if ($lockedName)
                                        <div class="text-[11px] text-gray-400 mt-0.5">oleh {{ $lockedName }}</div>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-center">
                                    @if (in_array($item->status, ['submitted', 'pending_manager', 'approved']))
                                        <a href="{{ route('laporan.isi', $item) }}"
                                           class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-medium hover:bg-gray-100 transition-colors border border-gray-100">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Lihat
                                        </a>
                                    @elseif ($item->status === 'returned')
                                        @php $retApproval = $item->approvals->firstWhere('status', 'returned'); @endphp
                                        <div class="flex flex-col items-center gap-1">
                                            <a href="{{ route('laporan.isi', $item) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-orange-500 text-white text-xs font-medium hover:bg-orange-600 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Revisi
                                            </a>
                                            @if ($retApproval?->notes)
                                                <p class="text-xs text-orange-600 italic max-w-[160px] leading-snug">
                                                    &ldquo;{{ $retApproval->notes }}&rdquo;
                                                </p>
                                            @endif
                                        </div>
                                    @elseif ($item->status === 'pending')
                                        <div class="flex items-center justify-center gap-2">
                                            <a href="{{ route('laporan.lihat', $item) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-medium hover:bg-gray-100 transition-colors border border-gray-100">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat
                                            </a>
                                            <button type="button"
                                                    onclick="confirmMulai('{{ route('laporan.isi', $item) }}', '{{ addslashes($item->product_name) }}', '{{ addslashes($item->batch_number) }}')"
                                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-500 text-white text-xs font-medium hover:bg-sky-600 transition-colors">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Mulai
                                            </button>
                                        </div>
                                    @elseif (in_array($item->status, ['monitoring', 'reading']))
                                        @php $isMine = $item->locked_by === auth()->id(); @endphp
                                        @if ($isMine)
                                            <a href="{{ route('laporan.isi', $item) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-medium transition-colors bg-yellow-500 text-white hover:bg-yellow-600">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                </svg>
                                                Lanjutkan
                                            </a>
                                        @elseif ($item->status === 'monitoring' && $item->locked_by === null)
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('laporan.lihat', $item) }}"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-medium hover:bg-gray-100 transition-colors border border-gray-100">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat
                                                </a>
                                                <button type="button"
                                                        onclick="confirmMulai('{{ route('laporan.isi', $item) }}', '{{ addslashes($item->product_name) }}', '{{ addslashes($item->batch_number) }}', 'resume_monitoring')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-500 text-white text-xs font-medium hover:bg-sky-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Mulai
                                                </button>
                                            </div>
                                        @elseif ($item->status === 'reading' && $item->locked_by === null)
                                            <div class="flex items-center justify-center gap-2">
                                                <a href="{{ route('laporan.lihat', $item) }}"
                                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-medium hover:bg-gray-100 transition-colors border border-gray-100">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                    </svg>
                                                    Lihat
                                                </a>
                                                <button type="button"
                                                        onclick="confirmMulai('{{ route('laporan.isi', $item) }}', '{{ addslashes($item->product_name) }}', '{{ addslashes($item->batch_number) }}', 'reading')"
                                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-500 text-white text-xs font-medium hover:bg-indigo-600 transition-colors">
                                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                    Mulai
                                                </button>
                                            </div>
                                        @else
                                            <a href="{{ route('laporan.isi', $item) }}"
                                               class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-gray-50 text-gray-600 text-xs font-medium hover:bg-gray-100 transition-colors border border-gray-100">
                                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                                Lihat
                                            </a>
                                        @endif
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

{{-- Confirm Start Modal --}}
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
function confirmMulai(url, product, batch, mode) {
    document.getElementById('mulai-product').textContent = product;
    document.getElementById('mulai-batch').textContent = batch;
    document.getElementById('mulai-confirm-link').href = url;
    if (mode === 'resume_monitoring' || mode === true) {
        document.querySelector('#mulai-modal h3').textContent = 'Lanjutkan Monitoring Laporan?';
        document.querySelector('#mulai-modal p.text-xs').textContent = 'Laporan ini sedang dalam tahap monitoring.';
        document.getElementById('mulai-desc').textContent = 'Anda akan mengambil alih pengerjaan laporan ini. Data yang sudah diisi analis sebelumnya tidak dapat diubah.';
    } else if (mode === 'reading') {
        document.querySelector('#mulai-modal h3').textContent = 'Mulai Pembacaan Laporan?';
        document.querySelector('#mulai-modal p.text-xs').textContent = 'Laporan ini siap memasuki tahap pembacaan.';
        document.getElementById('mulai-desc').textContent = 'Setelah dimulai, Anda akan menjadi penanggung jawab pembacaan laporan ini. Analis lain hanya bisa melihat.';
    } else {
        document.querySelector('#mulai-modal h3').textContent = 'Mulai Pengerjaan Laporan?';
        document.querySelector('#mulai-modal p.text-xs').textContent = 'Laporan ini akan masuk ke tahap monitoring.';
        document.getElementById('mulai-desc').textContent = 'Setelah dimulai, Anda akan menjadi penanggung jawab monitoring laporan ini. Analis lain hanya bisa melihat.';
    }
    document.getElementById('mulai-modal').classList.remove('hidden');
}
function closeMulaiModal() {
    document.getElementById('mulai-modal').classList.add('hidden');
}
</script>

@endsection
