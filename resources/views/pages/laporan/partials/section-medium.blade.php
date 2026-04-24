{{-- ── Section 3: Identitas Medium ─────────────────────── --}}
{{-- Data disimpan ke tabel medium_identities via $mediums (keyed by name) --}}
@php
    $mediumTypeList = $report->reportType->media->sortBy(fn($m) => str_contains(strtolower($m->name), 'swab') ? 1 : 0);
@endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">3. Identitas Medium</h3>
    </div>
    <div class="p-5 grid grid-cols-1 gap-6 {{ $mediumTypeList->count() > 2 ? 'lg:grid-cols-3' : 'lg:grid-cols-2' }}">
        @foreach ($mediumTypeList as $medType)
        @php
            $medName = $medType->name;
            $med     = $mediums[$medName] ?? null; // MediumIdentity model or null
            $isSwab  = str_contains(strtolower($medName), 'swab');
        @endphp
        <div>
            <h4 class="text-xs font-semibold text-sky-600 uppercase tracking-wide mb-3">{{ $medName }}</h4>
            <div class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Medium</label>
                    <input type="text" name="medium[{{ $medName }}][batch_number]" value="{{ $med?->batch_number ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
                </div>
                @if (!$isSwab)
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Nomor GPT Medium</label>
                    <input type="text" name="medium[{{ $medName }}][gpt_number]" value="{{ $med?->gpt_number ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
                </div>
                @endif
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal ED {{ $isSwab ? 'Swab Kit' : 'Medium' }}</label>
                    <input type="date" name="medium[{{ $medName }}][expiration_date]" value="{{ $med?->expiration_date?->format('Y-m-d') ?? '' }}"
                           @if(!$isEditable) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
                </div>
            </div>
        </div>
        @endforeach
    </div>
</div>
