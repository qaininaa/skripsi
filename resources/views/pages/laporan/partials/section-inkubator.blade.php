{{-- ── Section 4: Proses Inkubasi Medium Monitoring ──────── --}}
{{-- Data dari tabel incubators + incubator_entries, dikonfigurasi via report_type_incubators --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">4. Proses Inkubasi Medium Monitoring</h3>
    </div>
    @php
        $hasMediumSwab = $report->reportType->media
            ->contains(fn ($m) => str_contains(strtolower($m->name), 'swab'));
        $mediumTypeLabels = array_merge(
            ['monitoring' => 'Medium Monitoring'],
            $hasMediumSwab ? ['swab' => 'Swab'] : []
        );
    @endphp
    @foreach ($incubatorConfigs as $config)
    @php
        $ink         = $incubators[$config->id] ?? null;
        $inkEntries  = ($ink?->entries ?? collect())->keyBy('medium_type');
        $inkLabel    = $config->temperature_label;
        $inkMin      = $config->min_days;
        $allAnalysts = \App\Models\User::where('role', 'analis')->orderBy('name')->get();
        $hdOwners    = $hd['_field_owners'] ?? [];

        $infoOwner   = isset($hdOwners["incubator_{$config->id}_info"]) ? (string) $hdOwners["incubator_{$config->id}_info"] : null;
        $infoLocked  = $isEditable && $infoOwner !== null && $infoOwner !== (string) auth()->id();
        $infoEditable = $isEditable && ! $infoLocked;
    @endphp
    <div class="p-5 space-y-4 @if(!$loop->last) border-b border-gray-100 @endif">
        <p class="text-xs font-semibold text-sky-600 uppercase tracking-wide">Inkubator suhu {{ $inkLabel }}</p>

        {{-- Row 1: Alat + Kalibrasi (grup "info") --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">Inkubator Suhu {{ $inkLabel }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Inkubator</label>
                @if ($infoEditable)
                <input type="text" name="incubator[{{ $config->id }}][no_id]" value="{{ $ink?->no_id ?? '' }}"
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                @elseif ($infoLocked)
                <input type="text" value="{{ $ink?->no_id ?? '' }}" disabled
                       class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink?->no_id ?? 'N/A' }}</div>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Inkubator</label>
                @if ($infoEditable)
                <input type="date" name="incubator[{{ $config->id }}][calibration_date]" value="{{ $ink?->calibration_date?->format('Y-m-d') ?? '' }}"
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                @elseif ($infoLocked)
                <input type="date" value="{{ $ink?->calibration_date?->format('Y-m-d') ?? '' }}" disabled
                       class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                    {{ $ink?->calibration_date?->format('d/m/Y') ?? 'N/A' }}
                </div>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Inkubator</label>
                @if ($infoEditable)
                <input type="date" name="incubator[{{ $config->id }}][due_date_calibration]" value="{{ $ink?->due_date_calibration?->format('Y-m-d') ?? '' }}"
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                @elseif ($infoLocked)
                <input type="date" value="{{ $ink?->due_date_calibration?->format('Y-m-d') ?? '' }}" disabled
                       class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                    {{ $ink?->due_date_calibration?->format('d/m/Y') ?? 'N/A' }}
                </div>
                @endif
            </div>
        </div>

        {{-- Per-medium-type: Inkubasi & Keluar --}}
        @foreach ($mediumTypeLabels as $medType => $medLabel)
        @php
            $entry       = $inkEntries->get($medType);
            $inOwner     = isset($hdOwners["incubator_{$config->id}_{$medType}_in"])  ? (string) $hdOwners["incubator_{$config->id}_{$medType}_in"]  : null;
            $outOwner    = isset($hdOwners["incubator_{$config->id}_{$medType}_out"]) ? (string) $hdOwners["incubator_{$config->id}_{$medType}_out"] : null;
            $inLocked    = $isEditable && $inOwner  !== null && $inOwner  !== (string) auth()->id();
            $outLocked   = $isEditable && $outOwner !== null && $outOwner !== (string) auth()->id();
            $inEditable  = $isEditable && ! $inLocked;
            $outEditable = $isEditable && ! $outLocked;
        @endphp
        <div class="pt-3 border-t border-gray-50 space-y-3">
            <p class="text-xs font-semibold text-sky-600">
                Tanggal Inkubasi {{ $medLabel }} (min {{ $inkMin }} hari)
                @if ($inLocked || $outLocked)
                <span class="ml-1 text-[10px] font-normal text-gray-400 normal-case">🔒 sebagian diisi analis lain</span>
                @endif
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Masuk / Diinkubasi --}}
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Diinkubasi oleh</label>
                        @if ($inEditable)
                        <select name="incubator[{{ $config->id }}][{{ $medType }}][incubated_by]"
                                class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            <option value="">— Pilih Analis —</option>
                            @foreach ($allAnalysts as $analyst)
                            <option value="{{ $analyst->id }}" @selected($entry?->incubated_by === $analyst->id)>{{ $analyst->name }}</option>
                            @endforeach
                        </select>
                        @elseif ($inLocked)
                        <div class="px-3 py-2 rounded-lg border border-gray-200 bg-gray-100 text-sm text-gray-400 cursor-not-allowed">{{ $entry?->incubatedBy?->name ?? '—' }}</div>
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $entry?->incubatedBy?->name ?? 'N/A' }}</div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                        @if ($inEditable)
                        <input type="date" name="incubator[{{ $config->id }}][{{ $medType }}][date_in]" value="{{ $entry?->date_in?->format('Y-m-d') ?? '' }}"
                               class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @elseif ($inLocked)
                        <input type="date" value="{{ $entry?->date_in?->format('Y-m-d') ?? '' }}" disabled
                               class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                            {{ $entry?->date_in?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                        @if ($inEditable)
                        <input type="time" name="incubator[{{ $config->id }}][{{ $medType }}][time_in]" value="{{ $entry?->time_in ?? '' }}"
                               class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @elseif ($inLocked)
                        <input type="time" value="{{ $entry?->time_in ?? '' }}" disabled
                               class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $entry?->time_in ?? 'N/A' }}</div>
                        @endif
                    </div>
                </div>
                {{-- Keluar / Dikeluarkan --}}
                <div class="space-y-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dikeluarkan oleh</label>
                        @if ($outEditable)
                        <select name="incubator[{{ $config->id }}][{{ $medType }}][removed_by]"
                                class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            <option value="">— Pilih Analis —</option>
                            @foreach ($allAnalysts as $analyst)
                            <option value="{{ $analyst->id }}" @selected($entry?->removed_by === $analyst->id)>{{ $analyst->name }}</option>
                            @endforeach
                        </select>
                        @elseif ($outLocked)
                        <div class="px-3 py-2 rounded-lg border border-gray-200 bg-gray-100 text-sm text-gray-400 cursor-not-allowed">{{ $entry?->removedBy?->name ?? '—' }}</div>
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $entry?->removedBy?->name ?? 'N/A' }}</div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                        @if ($outEditable)
                        <input type="date" name="incubator[{{ $config->id }}][{{ $medType }}][date_out]" value="{{ $entry?->date_out?->format('Y-m-d') ?? '' }}"
                               class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @elseif ($outLocked)
                        <input type="date" value="{{ $entry?->date_out?->format('Y-m-d') ?? '' }}" disabled
                               class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                            {{ $entry?->date_out?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                        @if ($outEditable)
                        <input type="time" name="incubator[{{ $config->id }}][{{ $medType }}][time_out]" value="{{ $entry?->time_out ?? '' }}"
                               class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @elseif ($outLocked)
                        <input type="time" value="{{ $entry?->time_out ?? '' }}" disabled
                               class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $entry?->time_out ?? 'N/A' }}</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endforeach
</div>
