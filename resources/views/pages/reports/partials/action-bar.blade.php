{{-- ── Action Bar (top) ────────────────────────────────── --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-5">
    <div class="flex items-center gap-3">
        <a href="javascript:history.back()"
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
        @if (($revisionActionMode ?? 'default') === 'switch_to_reading')
        <button type="button" onclick="openSwitchToReadingFlow()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border bg-amber-500 hover:bg-amber-600 text-sm font-medium text-white transition-colors shadow-sm">
            Ke Pembacaan
        </button>
        <button type="button" onclick="openRevisionSubmitFlow()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Kirim ke Supervisor
        </button>
        @elseif (($revisionActionMode ?? 'default') === 'submit_revision_only')
        <button type="button" onclick="openRevisionSubmitFlow()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Kirim ke Supervisor
        </button>
        @else
        {{-- Simpan Draft (all phases) --}}
        <button type="button" onclick="openSaveModal()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Simpan Draft
        </button>

        {{-- Handover / finish-monitoring button (both phases) --}}
        <button type="button" onclick="openHandoverModal()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-sky-200 bg-sky-50 text-sm font-medium text-sky-700 hover:bg-sky-100 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"/>
            </svg>
            Simpan & Selesaikan
        </button>

        @if(!$isMonitoringPhase)
        {{-- Kirim Laporan (reading phase only) --}}
        <button type="button" onclick="openSubmitFlow()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Kirim Laporan
        </button>
        @elseif($isRevision ?? false)
        {{-- Kirim ke Supervisor (revision — skip reading phase) --}}
        <button type="button" onclick="openRevisionSubmitFlow()"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Kirim ke Supervisor
        </button>
        @endif
        @endif
    </div>
    @else
        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-sm font-medium
            @if ($report->status === 'submitted') bg-blue-50 text-blue-700
            @elseif ($report->status === 'pending_manager') bg-sky-50 text-sky-700
            @elseif ($report->status === 'approved') bg-green-50 text-green-700
            @else bg-gray-100 text-gray-500 @endif">
            @php
                $statusLabel = [
                    'monitoring' => 'Sedang Dimonitoring',
                    'reading'    => 'Sedang Dibaca',
                    'submitted'  => 'Dikirim ke Supervisor',
                    'pending_manager' => 'Dikirim ke Manajer',
                    'approved'   => 'Disetujui',
                    'pending'    => 'Menunggu',
                ][$report->status] ?? $report->status;
            @endphp
            {{ $statusLabel }} - Mode Lihat
        </span>
    @endif
</div>
