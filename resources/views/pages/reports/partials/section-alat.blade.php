{{-- ── Section 2: Identitas Instrumen — Air Sampler ────── --}}
{{-- Data disimpan ke tabel instrument_entries via $instrument (InstrumentEntry model) --}}
@php
    $instrumentFieldLocks = $instrumentFieldLocks ?? [];
    $currentUserId = (string) (auth()->id() ?? '');

    $noIdLockedByOther = $isEditable
        && isset($instrumentFieldLocks['no_id'])
        && (string) $instrumentFieldLocks['no_id'] !== $currentUserId;

    $calibrationDateLockedByOther = $isEditable
        && isset($instrumentFieldLocks['calibration_date'])
        && (string) $instrumentFieldLocks['calibration_date'] !== $currentUserId;

    $dueDateLockedByOther = $isEditable
        && isset($instrumentFieldLocks['due_date'])
        && (string) $instrumentFieldLocks['due_date'] !== $currentUserId;
@endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">2. Identitas Instrumen </h3>
        @error('instrument_incomplete')
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            {{ $message }}
        </p>
        @enderror
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
            <input type="hidden" name="air_sampler[tool_name]" value="Air Sampler">
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $instrument?->tool_name ?? 'Air Sampler' }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Air Sampler</label>
            <input type="text" name="air_sampler[no_id]" value="{{ $instrument?->no_id ?? '' }}"
                   placeholder="Contoh: AS-001"
                   @if(!$isEditable || $noIdLockedByOther) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $noIdLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
            @if ($noIdLockedByOther)
                <p class="mt-1 text-xs text-gray-400 italic">Terkunci karena sudah diisi analis lain.</p>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Air Sampler</label>
            <input type="date" name="air_sampler[calibration_date]" value="{{ $instrument?->calibration_date?->format('Y-m-d') ?? '' }}"
                   @if(!$isEditable || $calibrationDateLockedByOther) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $calibrationDateLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
            @if ($calibrationDateLockedByOther)
                <p class="mt-1 text-xs text-gray-400 italic">Terkunci karena sudah diisi analis lain.</p>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Air Sampler</label>
            <input type="date" name="air_sampler[due_date]" value="{{ $instrument?->due_date?->format('Y-m-d') ?? '' }}"
                   @if(!$isEditable || $dueDateLockedByOther) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $dueDateLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
            @if ($dueDateLockedByOther)
                <p class="mt-1 text-xs text-gray-400 italic">Terkunci karena sudah diisi analis lain.</p>
            @endif
        </div>
    </div>
</div>
