{{-- ── Section 3: Identitas Medium ─────────────────────── --}}
@php $mediumGroups = $report->reportType->medium_groups ?? []; @endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">3. Identitas Medium</h3>
    </div>
    <div class="p-5 grid grid-cols-1 gap-6 {{ count($mediumGroups) > 2 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
        @foreach ($mediumGroups as $medKey => $medLabel)
        @php $med = $hd[$medKey] ?? []; @endphp
        <div>
            <h4 class="text-xs font-semibold text-sky-600 uppercase tracking-wide mb-3">{{ $medLabel }}</h4>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Medium</label>
                    <input type="text" name="header_data[{{ $medKey }}][nomor_batch]" value="{{ $med['nomor_batch'] ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
                </div>
                @if (!str_contains(strtolower($medLabel), 'swab'))
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor GPT Medium</label>
                    <input type="text" name="header_data[{{ $medKey }}][nomor_gpt]" value="{{ $med['nomor_gpt'] ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal ED {{ $medLabel }}</label>
                    <input type="date" name="header_data[{{ $medKey }}][expiry_date]" value="{{ $med['expiry_date'] ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
