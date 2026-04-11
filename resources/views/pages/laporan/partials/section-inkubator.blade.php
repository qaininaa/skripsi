{{-- ── Section 4: Proses Inkubasi Medium Monitoring ──────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">4. Proses Inkubasi Medium Monitoring</h3>
    </div>
    @foreach ([
        'inkubator_20_25' => ['label' => 'Inkubator Suhu 20–25°C', 'min_days' => 3],
        'inkubator_30_35' => ['label' => 'Inkubator Suhu 30–35°C', 'min_days' => 2],
    ] as $inkKey => $inkInfo)
    @php
        $ink = $hd[$inkKey] ?? []; $inkLabel = $inkInfo['label']; $inkMin = $inkInfo['min_days'];
        $_fo = $hd['_field_owners'] ?? [];
        $inkF = [];
        foreach (['no_id','calibration_date','due_date','incubation_date','date_in','time_in','date_out','time_out','incubated_by','incubated_date','removed_by','removed_date'] as $_k) {
            $_o = $_fo["{$inkKey}.$_k"] ?? null;
            $inkF[$_k] = $isEditable && $_o && (int)$_o !== auth()->id();
        }
        $allAnalysts = \App\Models\User::where('role', 'analis')->orderBy('name')->get();
    @endphp
    <div class="p-5 space-y-4 @if(!$loop->last) border-b border-gray-100 @endif">
        <p class="text-xs font-semibold text-sky-600 uppercase tracking-wide">{{ $inkLabel }}</p>

        {{-- Row 1: Alat + Kalibrasi --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">{{ $inkLabel }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Inkubator</label>
                @if ($isEditable && !$inkF['no_id'])
                <input type="text" name="header_data[{{ $inkKey }}][no_id]" value="{{ $ink['no_id'] ?? '' }}"
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink['no_id'] ?? '—' }}</div>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Inkubator</label>
                @if ($isEditable && !$inkF['calibration_date'])
                <input type="date" name="header_data[{{ $inkKey }}][calibration_date]" value="{{ $ink['calibration_date'] ?? '' }}"
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                    {{ isset($ink['calibration_date']) ? \Carbon\Carbon::parse($ink['calibration_date'])->format('d/m/Y') : '—' }}
                </div>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Inkubator</label>
                @if ($isEditable && !$inkF['due_date'])
                <input type="date" name="header_data[{{ $inkKey }}][due_date]" value="{{ $ink['due_date'] ?? '' }}"
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                    {{ isset($ink['due_date']) ? \Carbon\Carbon::parse($ink['due_date'])->format('d/m/Y') : '—' }}
                </div>
                @endif
            </div>
        </div>

        {{-- Row 2: Inkubasi & Keluar --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-2 border-t border-gray-50">
            {{-- Masuk / Diinkubasi --}}
            <div class="space-y-3">
                <p class="text-xs font-semibold text-sky-600">Tanggal Inkubasi Medium (min {{ $inkMin }} hari)</p>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Diinkubasi oleh</label>
                    @if ($isEditable && !$inkF['incubated_by'])
                    <select name="header_data[{{ $inkKey }}][incubated_by]"
                            class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        <option value="">— Pilih Analis —</option>
                        @foreach ($allAnalysts as $analyst)
                        <option value="{{ $analyst->name }}" @selected(($ink['incubated_by'] ?? '') === $analyst->name)>{{ $analyst->name }}</option>
                        @endforeach
                    </select>
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink['incubated_by'] ?? '—' }}</div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                    @if ($isEditable && !$inkF['date_in'])
                    <input type="date" name="header_data[{{ $inkKey }}][date_in]" value="{{ $ink['date_in'] ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                        {{ isset($ink['date_in']) ? \Carbon\Carbon::parse($ink['date_in'])->format('d/m/Y') : '—' }}
                    </div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                    @if ($isEditable && !$inkF['time_in'])
                    <input type="time" name="header_data[{{ $inkKey }}][time_in]" value="{{ $ink['time_in'] ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink['time_in'] ?? '—' }}</div>
                    @endif
                </div>
            </div>
            {{-- Keluar / Dikeluarkan --}}
            <div class="space-y-3">
                <p class="text-xs font-semibold text-sky-600">&nbsp;</p>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Dikeluarkan oleh</label>
                    @if ($isEditable && !$inkF['removed_by'])
                    <select name="header_data[{{ $inkKey }}][removed_by]"
                            class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        <option value="">— Pilih Analis —</option>
                        @foreach ($allAnalysts as $analyst)
                        <option value="{{ $analyst->name }}" @selected(($ink['removed_by'] ?? '') === $analyst->name)>{{ $analyst->name }}</option>
                        @endforeach
                    </select>
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink['removed_by'] ?? '—' }}</div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                    @if ($isEditable && !$inkF['date_out'])
                    <input type="date" name="header_data[{{ $inkKey }}][date_out]" value="{{ $ink['date_out'] ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                        {{ isset($ink['date_out']) ? \Carbon\Carbon::parse($ink['date_out'])->format('d/m/Y') : '—' }}
                    </div>
                    @endif
                </div>
                <div>
                    <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                    @if ($isEditable && !$inkF['time_out'])
                    <input type="time" name="header_data[{{ $inkKey }}][time_out]" value="{{ $ink['time_out'] ?? '' }}"
                           class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    @else
                    <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink['time_out'] ?? '—' }}</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
