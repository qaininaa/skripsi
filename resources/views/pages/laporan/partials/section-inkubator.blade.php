{{-- ── Section 4: Proses Inkubasi Medium Monitoring ──────── --}}
{{-- Data dari tabel incubators, dikonfigurasi via report_type_incubators --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">4. Proses Inkubasi Medium Monitoring</h3>
    </div>
    @foreach ($incubatorConfigs as $config)
    @php
        $ink = $incubators[$config->id] ?? null; // Incubator model or null
        $inkLabel = $config->temperature_label;
        $inkMin   = $config->min_days;
        $allAnalysts = \App\Models\User::where('role', 'analis')->orderBy('name')->get();

        // Ownership: who owns each group for this incubator config?
        $hdOwners    = $hd['_field_owners'] ?? [];
        $infoOwner   = isset($hdOwners["incubator_{$config->id}_info"]) ? (string) $hdOwners["incubator_{$config->id}_info"] : null;
        $inOwner     = isset($hdOwners["incubator_{$config->id}_in"])   ? (string) $hdOwners["incubator_{$config->id}_in"]   : null;
        $outOwner    = isset($hdOwners["incubator_{$config->id}_out"])  ? (string) $hdOwners["incubator_{$config->id}_out"]  : null;
        $infoLocked  = $isEditable && $infoOwner !== null && $infoOwner !== (string) auth()->id();
        $inLocked    = $isEditable && $inOwner   !== null && $inOwner   !== (string) auth()->id();
        $outLocked   = $isEditable && $outOwner  !== null && $outOwner  !== (string) auth()->id();
        $infoEditable = $isEditable && ! $infoLocked;
        $inEditable  = $isEditable && ! $inLocked;
        $outEditable = $isEditable && ! $outLocked;
    @endphp
    <div class="p-5 space-y-4 @if(!$loop->last) border-b border-gray-100 @endif">
        <p class="text-xs font-semibold text-sky-600 uppercase tracking-wide">Inkubator suhu {{ $inkLabel }}</p>

        {{-- Row 1: Alat + Kalibrasi (grup "in") --}}
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

        {{-- Row 2: Inkubasi & Keluar --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-50">
            {{-- Masuk / Diinkubasi (grup "in") --}}
            <div class="space-y-3">
                <p class="text-xs font-semibold text-sky-600">
                    Tanggal Inkubasi Medium (min {{ $inkMin }} hari)
                    @if ($inLocked)
                    <span class="ml-1 text-[10px] font-normal text-gray-400 normal-case">
                        🔒 diisi analis lain
                    </span>
                    @endif
                </p>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Diinkubasi oleh</label>
                    @if ($inEditable)
                    <select name="incubator[{{ $config->id }}][incubated_by]"
                            class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        <option value="">— Pilih Analis —</option>
                        @foreach ($allAnalysts as $analyst)
                        <option value="{{ $analyst->id }}" @selected($ink?->incubated_by === $analyst->id)>{{ $analyst->name }}</option>
                        @endforeach
                    </select>
                    @elseif ($inLocked)
                    <div class="px-3 py-2 rounded-lg border border-gray-200 bg-gray-100 text-sm text-gray-400 cursor-not-allowed">{{ $ink?->incubatedBy?->name ?? '—' }}</div>
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink?->incubatedBy?->name ?? 'N/A' }}</div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                    @if ($inEditable)
                    <input type="date" name="incubator[{{ $config->id }}][date_in]" value="{{ $ink?->date_in?->format('Y-m-d') ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @elseif ($inLocked)
                    <input type="date" value="{{ $ink?->date_in?->format('Y-m-d') ?? '' }}" disabled
                           class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                        {{ $ink?->date_in?->format('d/m/Y') ?? 'N/A' }}
                    </div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                    @if ($inEditable)
                    <input type="time" name="incubator[{{ $config->id }}][time_in]" value="{{ $ink?->time_in ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @elseif ($inLocked)
                    <input type="time" value="{{ $ink?->time_in ?? '' }}" disabled
                           class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink?->time_in ?? 'N/A' }}</div>
                    @endif
                </div>
            </div>
            {{-- Keluar / Dikeluarkan (grup "out") --}}
            <div class="space-y-3">
                <p class="text-xs font-semibold text-sky-600">
                    &nbsp;
                    @if ($outLocked)
                    <span class="ml-1 text-[10px] font-normal text-gray-400 normal-case">
                        🔒 diisi analis lain
                    </span>
                    @endif
                </p>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dikeluarkan oleh</label>
                    @if ($outEditable)
                    <select name="incubator[{{ $config->id }}][removed_by]"
                            class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        <option value="">— Pilih Analis —</option>
                        @foreach ($allAnalysts as $analyst)
                        <option value="{{ $analyst->id }}" @selected($ink?->removed_by === $analyst->id)>{{ $analyst->name }}</option>
                        @endforeach
                    </select>
                    @elseif ($outLocked)
                    <div class="px-3 py-2 rounded-lg border border-gray-200 bg-gray-100 text-sm text-gray-400 cursor-not-allowed">{{ $ink?->removedBy?->name ?? '—' }}</div>
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink?->removedBy?->name ?? 'N/A' }}</div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                    @if ($outEditable)
                    <input type="date" name="incubator[{{ $config->id }}][date_out]" value="{{ $ink?->date_out?->format('Y-m-d') ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @elseif ($outLocked)
                    <input type="date" value="{{ $ink?->date_out?->format('Y-m-d') ?? '' }}" disabled
                           class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                        {{ $ink?->date_out?->format('d/m/Y') ?? 'N/A' }}
                    </div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                    @if ($outEditable)
                    <input type="time" name="incubator[{{ $config->id }}][time_out]" value="{{ $ink?->time_out ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @elseif ($outLocked)
                    <input type="time" value="{{ $ink?->time_out ?? '' }}" disabled
                           class="block w-full rounded-lg border border-gray-200 bg-gray-100 px-3 py-2 text-sm text-gray-400 cursor-not-allowed">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink?->time_out ?? 'N/A' }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
