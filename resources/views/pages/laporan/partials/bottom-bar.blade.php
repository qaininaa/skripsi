{{-- ── Bottom Save Bar + Hidden Inputs ────────────────── --}}
@if ($isEditable)
<div class="flex justify-end gap-2 pb-2">
    <button type="button" onclick="openSaveModal()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
        Simpan Draft
    </button>
    <button type="button" onclick="openHandoverModal()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-sky-200 bg-sky-50 text-sm font-medium text-sky-700 hover:bg-sky-100 transition-colors shadow-sm">
        Simpan &amp; Selesaikan
    </button>
    @if(!$isMonitoringPhase)
    <button type="button" onclick="openSubmitFlow()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
        Kirim Laporan
    </button>
    @elseif($isRevision ?? false)
    <button type="button" onclick="openRevisionSubmitFlow()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-500 text-white text-sm font-medium hover:bg-emerald-600 transition-colors shadow-sm">
        Kirim ke Supervisor
    </button>
    @endif
</div>
@endif

<input type="hidden" id="save-action-input" name="action" value="">
<input type="hidden" id="save-supervisor-input" name="supervisor_id" value="">
<input type="hidden" id="save-handover-to-input" name="handover_to" value="">
