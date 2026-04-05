{{-- ── Bottom Save Bar + Hidden Inputs ────────────────── --}}
@if ($isEditable)
<div class="flex justify-end gap-2 pb-2">
    <button type="button" onclick="openSaveModal()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-medium text-gray-700 hover:bg-gray-50 transition-colors shadow-sm">
        Simpan Draft
    </button>
    @if ($myShift === 1 && !$shift1HandedOver && $report->shift2Analis)
    <button type="button" onclick="openConfirmModal('handover')"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-500 text-white text-sm font-medium hover:bg-amber-600 transition-colors shadow-sm">
        Estafet ke Shift 2
    </button>
    @endif
    @if (!$report->shift2Analis || $myShift === 2)
    <button type="button" onclick="openSubmitFlow()"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-500 text-white text-sm font-medium hover:bg-sky-600 transition-colors shadow-sm">
        Kirim Laporan
    </button>
    @endif
</div>
@endif

<input type="hidden" id="save-action-input" name="action" value="">
<input type="hidden" id="save-supervisor-input" name="supervisor_id" value="">
