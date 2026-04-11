{{-- ── Section 3: Identitas Medium ─────────────────────── --}}
@php $mediumGroups = $report->reportType->medium_groups ?? []; @endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">3. Identitas Medium</h3>
    </div>
    <div class="p-5 grid grid-cols-1 gap-6 {{ count($mediumGroups) > 2 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
        @foreach ($mediumGroups as $medKey => $medLabel)
        @php
            $med = $hd[$medKey] ?? [];
            $_fo = $hd['_field_owners'] ?? [];
            $medF = [];
            foreach (['nomor_batch', 'nomor_gpt', 'expiry_date'] as $_k) {
                $_o = $_fo["{$medKey}.$_k"] ?? null;
                $medF[$_k] = $isEditable && $_o && (int)$_o !== auth()->id();
            }
        @endphp
        <div>
            <h4 class="text-xs font-semibold text-sky-600 uppercase tracking-wide mb-3">{{ $medLabel }}</h4>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Medium</label>
                    <input type="text" @if(!$medF['nomor_batch']) name="header_data[{{ $medKey }}][nomor_batch]" @endif value="{{ $med['nomor_batch'] ?? '' }}"
                           @if(!$isEditable || $medF['nomor_batch']) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $medF['nomor_batch']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
                </div>
                @if (!str_contains(strtolower($medLabel), 'swab'))
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor GPT Medium</label>
                    <input type="text" @if(!$medF['nomor_gpt']) name="header_data[{{ $medKey }}][nomor_gpt]" @endif value="{{ $med['nomor_gpt'] ?? '' }}"
                           @if(!$isEditable || $medF['nomor_gpt']) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $medF['nomor_gpt']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal ED {{ $medLabel }}</label>
                    <input type="date" @if(!$medF['expiry_date']) name="header_data[{{ $medKey }}][expiry_date]" @endif value="{{ $med['expiry_date'] ?? '' }}"
                           @if(!$isEditable || $medF['expiry_date']) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $medF['expiry_date']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
