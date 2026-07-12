{{-- ── Section 2: Identitas Instrumen — Air Sampler ────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">2. Identitas Instrumen </h3>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @php
            $as = $hd['air_sampler'] ?? [];
            $_fo = $hd['_field_owners'] ?? [];
            $asF = [];
            foreach (['nama_alat', 'no_id', 'calibration_date', 'due_date'] as $_k) {
                $_o = $_fo["air_sampler.$_k"] ?? null;
                $asF[$_k] = $isEditable && $_o && (int)$_o !== auth()->id();
            }
        @endphp
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
            <input type="text" @if(!$asF['nama_alat']) name="header_data[air_sampler][nama_alat]" @endif value="{{ $as['nama_alat'] ?? 'Air Sampler' }}"
                   @if(!$isEditable || $asF['nama_alat']) readonly @endif
                   class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $asF['nama_alat']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Air Sampler</label>
            <input type="text" @if(!$asF['no_id']) name="header_data[air_sampler][no_id]" @endif value="{{ $as['no_id'] ?? '' }}"
                   @if(!$isEditable || $asF['no_id']) readonly @endif
                   class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $asF['no_id']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Air Sampler</label>
            <input type="date" @if(!$asF['calibration_date']) name="header_data[air_sampler][calibration_date]" @endif value="{{ $as['calibration_date'] ?? '' }}"
                   @if(!$isEditable || $asF['calibration_date']) readonly @endif
                   class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $asF['calibration_date']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Air Sampler</label>
            <input type="date" @if(!$asF['due_date']) name="header_data[air_sampler][due_date]" @endif value="{{ $as['due_date'] ?? '' }}"
                   @if(!$isEditable || $asF['due_date']) readonly @endif
                   class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $asF['due_date']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
        </div>
    </div>
</div>
