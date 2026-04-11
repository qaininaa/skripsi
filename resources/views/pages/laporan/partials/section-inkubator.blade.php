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
    @endphp
    <div class="p-5 space-y-4 @if(!$loop->last) border-b border-gray-100 @endif">
        <p class="text-xs font-semibold text-sky-600 uppercase tracking-wide">{{ $inkLabel }}</p>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Nama Alat</label>
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm font-medium text-gray-700">{{ $inkLabel }}</div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">No. ID Inkubator</label>
                <input type="text" @if(!$inkF['no_id']) name="header_data[{{ $inkKey }}][no_id]" @endif value="{{ $ink['no_id'] ?? '' }}"
                       @if(!$isEditable || $inkF['no_id']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['no_id']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Inkubator</label>
                <input type="date" @if(!$inkF['calibration_date']) name="header_data[{{ $inkKey }}][calibration_date]" @endif value="{{ $ink['calibration_date'] ?? '' }}"
                       @if(!$isEditable || $inkF['calibration_date']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['calibration_date']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Inkubator</label>
                <input type="date" @if(!$inkF['due_date']) name="header_data[{{ $inkKey }}][due_date]" @endif value="{{ $ink['due_date'] ?? '' }}"
                       @if(!$isEditable || $inkF['due_date']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['due_date']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 pt-2 border-t border-gray-50">
            <div class="lg:col-span-2">
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Inkubasi Medium (min {{ $inkMin }} hari)</label>
                <input type="date" @if(!$inkF['incubation_date']) name="header_data[{{ $inkKey }}][incubation_date]" @endif value="{{ $ink['incubation_date'] ?? '' }}"
                       @if(!$isEditable || $inkF['incubation_date']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['incubation_date']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                <input type="date" @if(!$inkF['date_in']) name="header_data[{{ $inkKey }}][date_in]" @endif value="{{ $ink['date_in'] ?? '' }}"
                       @if(!$isEditable || $inkF['date_in']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['date_in']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                <input type="time" @if(!$inkF['time_in']) name="header_data[{{ $inkKey }}][time_in]" @endif value="{{ $ink['time_in'] ?? '' }}"
                       @if(!$isEditable || $inkF['time_in']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['time_in']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
            <div class="hidden lg:block lg:col-span-2"></div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                <input type="date" @if(!$inkF['date_out']) name="header_data[{{ $inkKey }}][date_out]" @endif value="{{ $ink['date_out'] ?? '' }}"
                       @if(!$isEditable || $inkF['date_out']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['date_out']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                <input type="time" @if(!$inkF['time_out']) name="header_data[{{ $inkKey }}][time_out]" @endif value="{{ $ink['time_out'] ?? '' }}"
                       @if(!$isEditable || $inkF['time_out']) readonly @endif
                       class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF['time_out']) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
            </div>
            <div class="lg:col-span-4 grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
                @foreach ([
                    ['key' => 'incubated',  'label' => 'Diinkubasi oleh',   'default_date' => date('Y-m-d')],
                    ['key' => 'removed', 'label' => 'Dikeluarkan oleh',  'default_date' => ''],
                ] as $field)
                @php
                    $fKey    = $field['key'];
                    $fName   = $fKey . '_by';
                    $fDate   = $fKey . '_date';
                    $savedWho  = $ink[$fName] ?? '';
                    $savedDate = $ink[$fDate] ?? $field['default_date'];
                @endphp
                <div class="rounded-xl border border-gray-100 bg-gray-50/50 p-3 space-y-2">
                    <p class="text-xs font-semibold text-gray-500">{{ $field['label'] }}</p>
                    @if ($isEditable && !$inkF[$fName])
                    <div class="flex flex-wrap gap-2" data-radio-group="{{ $inkKey }}_{{ $fKey }}">
                        @php
                            $monitoringAnalysts = \App\Models\User::whereIn('id', $report->analyst_monitoring ?? [])->get();
                        @endphp
                        @foreach ($monitoringAnalysts as $analyst)
                        <button type="button"
                                data-value="{{ $analyst->name }}"
                                onclick="toggleAnalis('{{ $inkKey }}', '{{ $fKey }}', this.dataset.value, this)"
                                class="inkubasi-radio-btn flex items-center gap-1.5 px-3 py-1.5 rounded-lg border text-xs font-medium transition-colors
                                       {{ $savedWho === $analyst->name ? 'bg-sky-50 border-sky-300 text-sky-700' : 'border-gray-200 bg-white text-gray-600 hover:border-sky-200' }}">
                            {{ $analyst->name }}
                        </button>
                        @endforeach
                        <input type="hidden" name="header_data[{{ $inkKey }}][{{ $fName }}]" id="radio-val-{{ $inkKey }}-{{ $fKey }}" value="{{ $savedWho }}">
                    </div>
                    @else
                    <div class="text-sm text-gray-700">{{ $savedWho ?: '—' }}</div>
                    @endif
                    <input type="date" @if(!$inkF[$fDate]) name="header_data[{{ $inkKey }}][{{ $fDate }}]" @endif
                           value="{{ $savedDate }}"
                           @if(!$isEditable || $inkF[$fDate]) readonly @endif
                           class="block w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable || $inkF[$fDate]) bg-gray-100 text-gray-400 cursor-not-allowed @endif">
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
</div>
