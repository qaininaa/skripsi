{{-- ── Action Bar (top) ────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <a href="{{ route('laporan.index') }}"
           class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 transition-colors">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali
        </a>
        <div class="w-px h-5 bg-gray-200"></div>
        <div>
            <h2 class="text-base font-semibold text-gray-800">
                {{ $report->reportType->annex_number }}
                <span class="text-gray-400 font-normal mx-1">—</span>
                <span class="text-sm font-normal text-gray-600">{{ $report->reportType->name }}</span>
            </h2>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ $report->product_name }} · Batch <span>{{ $report->batch_number }}</span>
                · {{ $report->created_at->isoFormat('D MMM Y') }}
            </p>
        </div>
    </div>

    @if ($isEditable)
    <div class="flex items-center gap-2">
        <button type="button" onclick="openSaveModal()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Simpan Draft
        </button>
        @if ($myShift === 1 && !$shift1HandedOver && $report->shift2Analis)
        <button type="button" onclick="openConfirmModal('handover')"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
            </svg>
            Estafet ke Shift 2
        </button>
        @endif
        @if (!$report->shift2Analis || $myShift === 2)
        <button type="button" onclick="openSubmitFlow()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Kirim Laporan
        </button>
        @endif
    </div>
    @else
        @if ($myShift === 1 && $shift1HandedOver)
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium bg-amber-50 text-amber-700 border border-amber-200">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Sudah diteruskan ke Shift 2 - Mode Lihat
        </span>
        @else
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium
            @if ($report->status === 'submitted') bg-blue-50 text-blue-700
            @elseif ($report->status === 'approved') bg-green-50 text-green-700
            @else bg-gray-100 text-gray-500 @endif">
            @php
                $statusLabel = ['in_progress' => 'Sedang Dikerjakan', 'submitted' => 'Dikirim', 'approved' => 'Disetujui', 'rejected' => 'Ditolak', 'pending' => 'Menunggu'][$report->status] ?? $report->status;
            @endphp
            {{ $statusLabel }} - Mode Lihat
        </span>
        @endif
    @endif
</div>
