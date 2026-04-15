{{-- ── Tabel Pengukuran per Seksi (dipakai di dalam @foreach sections) ── --}}
@php
    // Helper: convert CFU string value to numeric for comparison/summation
    // Returns PHP_INT_MAX for TNTC (always exceeds any limit), 0 for <1, int for numbers
    $cfuNum = function(?string $v): ?int {
        if ($v === null || $v === '') return null;
        if (strtoupper($v) === 'TNTC') return PHP_INT_MAX;
        if ($v === '<1') return 0;
        if (is_numeric($v)) return (int)$v;
        return null;
    };
    // Helper: compute T = B + F as display string
    $cfuTot = function(?string $b, ?string $f) use ($cfuNum): ?string {
        if ($b === null && $f === null) return null;
        if (strtoupper((string)$b) === 'TNTC' || strtoupper((string)$f) === 'TNTC') return 'TNTC';
        $bn = ($b === '<1') ? 0 : ($b !== null && is_numeric($b) ? (int)$b : null);
        $fn = ($f === '<1') ? 0 : ($f !== null && is_numeric($f) ? (int)$f : null);
        if ($bn === null && $fn === null) return null;
        $sum = ($bn ?? 0) + ($fn ?? 0);
        if ($sum === 0 && ($b === '<1' || $f === '<1')) return '<1';
        return (string)$sum;
    };
@endphp
@php
    // Config-driven flags dari tabel sections
    $instance       = $instance ?? 1;
    $hasSharedTime  = (bool) $section->has_shared_time;
    $hasJam         = $section->time_slot_type === 'single';
    $isPerLocation  = $section->time_slot_type === 'per_location';
    $isDualAB       = $section->time_slot_type === 'dual_ab';
    $isSwabTime     = $section->time_slot_type === 'swab';
    $hasShiftToggle = (bool) $section->has_shift_toggle;
    $colLabel       = $section->column_label ?? 'Exposure';

    $maxCols       = $section->max_exposure;
    $romanNums     = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    $secNum        = $loop->index + 5;
    $savedAsgn     = ($hd['shift_assignments'][$section->id] ?? []);
    $secAssignments = [];
    for ($c = 1; $c <= $maxCols; $c++) {
        $secAssignments[$c] = isset($savedAsgn[$c]) ? (int)$savedAsgn[$c] : 1;
    }

    // Hitung sub-kolom per exposure: B + F + T = 3, +1 JAM jika per_location
    $subColsPerExp = $isPerLocation ? 4 : 3;

    // Check ownership of time fields for this section
    $hdOwners = $hd['_field_owners'] ?? [];
    $expTimeLocked = $isEditable && isset($hdOwners["exposure_times_{$section->id}"]) && (int) $hdOwners["exposure_times_{$section->id}"] !== auth()->id();
    $settlTimeLocked = $isEditable && isset($hdOwners["settle_times_{$section->id}"]) && (int) $hdOwners["settle_times_{$section->id}"] !== auth()->id();
    $swabTimeLocked = $isEditable && isset($hdOwners["swab_times_{$section->id}"]) && (int) $hdOwners["swab_times_{$section->id}"] !== auth()->id();
@endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
        <div class="h-7 w-7 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0">
            <span class="text-xs font-bold text-sky-600">{{ $secNum }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-sm text-gray-700 flex items-center gap-2">
                {{ $section->measurement_unit }}
                @if ($instance > 1)
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-orange-100 text-orange-600">Duplikat {{ $instance }}</span>
                @endif
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $section->name }}</p>
        </div>

        {{-- Admin-only: tombol duplikat / hapus duplikat --}}
        @if ($isAdminPreview ?? false)
        <div class="ml-auto flex items-center gap-2 shrink-0">
            @if ($instance === 1)
            <button type="button"
                    onclick="adminSectionAction('POST', '{{ route('tugas-pelaporan.sections.duplicate', [$report->id, $section->id]) }}')"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-medium hover:bg-emerald-100 transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Duplikat Seksi
            </button>
            @endif
            @if ($instance > 1 && $instance === ($totalInstances ?? $instance))
            <button type="button"
                    onclick="if(confirm('Hapus duplikat seksi ini?')) adminSectionAction('DELETE', '{{ route('tugas-pelaporan.sections.remove', [$report->id, $section->id]) }}')"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                Hapus Duplikat
            </button>
            @endif
        </div>
        @endif
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse" style="min-width: {{ 480 + ($hasSharedTime ? 130 : 0) + ($maxCols * ($isSwabTime ? 220 : ($isDualAB ? 130 : ($isPerLocation ? 220 : 160)))) }}px">
            <thead>
                {{-- Row 1: group headers --}}
                <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No.</th>
                    <th class="px-3 py-2 text-left font-semibold border-r border-sky-100" rowspan="3">Nama Ruangan</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Kelas</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No. Ruangan</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No.<br>Lokasi</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100"
                    colspan="{{ ($hasSharedTime ? 3 : 0) + $maxCols * $subColsPerExp }}">
                        {{ $section->measurement_unit }}
                    </th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Alert<br>Limit</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Alert<br>Action</th>
                    <th class="px-2 py-2 text-center font-semibold whitespace-nowrap" rowspan="3">Kesimpulan</th>
                </tr>
                {{-- Row 2: period/shift labels --}}
                <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">
                    @if ($hasSharedTime)
                    @php
                        $msJamMulai   = $hd['exposure_times'][$section->id][0]['start_time'] ?? null;
                        $msJamSelesai = $hd['exposure_times'][$section->id][0]['end_time'] ?? null;
                    @endphp
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100" colspan="3">
                        <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">Machine Set-up</div>
                        @if ($isEditable && !$expTimeLocked)
                        <div class="flex justify-center items-center gap-1">
                            <input type="time" name="exposure_times[{{ $section->id }}][0][start_time]"
                                   value="{{ $msJamMulai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            <span class="text-gray-400 text-[10px] font-normal">–</span>
                            <input type="time" name="exposure_times[{{ $section->id }}][0][end_time]"
                                   value="{{ $msJamSelesai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        </div>
                        @elseif ($expTimeLocked)
                        <div class="flex justify-center items-center gap-1">
                            <input type="time" value="{{ $msJamMulai }}" disabled
                                   class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-normal text-gray-400 cursor-not-allowed">
                            <span class="text-gray-400 text-[10px] font-normal">–</span>
                            <input type="time" value="{{ $msJamSelesai }}" disabled
                                   class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-normal text-gray-400 cursor-not-allowed">
                        </div>
                        @else
                        <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                            {{ $msJamMulai ? $msJamMulai . ' – ' . ($msJamSelesai ?? '—') : '—' }}
                        </div>
                        @endif
                    </th>
                    @endif
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @php $colAsgn = $secAssignments[$col] ?? 1; @endphp
                    <th class="px-2 py-1.5 text-center font-semibold border-r border-sky-100 whitespace-nowrap"
                        colspan="{{ $subColsPerExp }}">
                        {{ $colLabel }} {{ $maxCols > 1 ? ($romanNums[$col - 1] ?? $col) : '' }}

                        {{-- Swab time slots (S1, S1-2, S1-3) --}}
                        @if ($isSwabTime)
                        @php $swabColTimes = $hd['swab_times'][$section->id][$col] ?? []; @endphp
                        @if ($isEditable && !$swabTimeLocked)
                        <div class="space-y-0.5 mt-1">
                            @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                            @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                            <div class="flex items-center justify-center gap-0.5">
                                <span class="text-[9px] font-bold text-gray-500 w-12 text-left shrink-0">{{ $swabLabel }}:</span>
                                <input type="time" name="swab_times[{{ $section->id }}][{{ $col }}][{{ $swabKey }}][mulai]"
                                       value="{{ $st['mulai'] ?? '' }}"
                                       class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                                <span class="text-gray-400 text-[10px]">–</span>
                                <input type="time" name="swab_times[{{ $section->id }}][{{ $col }}][{{ $swabKey }}][selesai]"
                                       value="{{ $st['selesai'] ?? '' }}"
                                       class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            </div>
                            @endforeach
                        </div>
                        @else
                        <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                            @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                            @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                            <div>{{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: '—' }} – {{ ($st['selesai'] ?? '') ?: '—' }}</div>
                            @endforeach
                        </div>
                        @endif
                        @endif

                        {{-- Dual A/B time slots (settle plate) --}}
                        @if ($isDualAB)
                            @if ($isEditable && !$settlTimeLocked)
                            <div class="space-y-0.5 mt-1">
                                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                                @php $stAB = $hd['settle_times'][$section->id][$col][$ab] ?? []; @endphp
                                <div class="flex items-center justify-center gap-0.5">
                                    <span class="text-[9px] font-bold text-gray-500 w-3 text-left">{{ $abLabel }}:</span>
                                    <input type="time" name="settle_times[{{ $section->id }}][{{ $col }}][{{ $ab }}][start_time]"
                                           value="{{ $stAB['start_time'] ?? '' }}"
                                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                                    <span class="text-gray-400 text-[10px]">–</span>
                                    <input type="time" name="settle_times[{{ $section->id }}][{{ $col }}][{{ $ab }}][end_time]"
                                           value="{{ $stAB['end_time'] ?? '' }}"
                                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                                @php $stAB = $hd['settle_times'][$section->id][$col][$ab] ?? []; @endphp
                                <div>{{ $abLabel }}: {{ ($stAB['start_time'] ?? '') ?: '—' }} – {{ ($stAB['end_time'] ?? '') ?: '—' }}</div>
                                @endforeach
                            </div>
                            @endif
                        @endif

                        {{-- Single time slot: Mulai–Selesai per exposure column (in header) --}}
                        @if ($hasJam)
                        @php
                            $expJam        = $hd['exposure_times'][$section->id][$col] ?? [];
                            $expJamMulai   = $expJam['start_time'] ?? null;
                            $expJamSelesai = $expJam['end_time'] ?? null;
                        @endphp
                        @if ($isEditable && !$expTimeLocked)
                        <div class="space-y-0.5 mt-1">
                            <div class="flex items-center justify-center gap-0.5">
                                <span class="text-[9px] text-gray-500 w-10 shrink-0">Mulai:</span>
                                <input type="time" name="exposure_times[{{ $section->id }}][{{ $col }}][start_time]"
                                       value="{{ $expJamMulai }}"
                                       class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            </div>
                            <div class="flex items-center justify-center gap-0.5">
                                <span class="text-[9px] text-gray-500 w-10 shrink-0">Selesai:</span>
                                <input type="time" name="exposure_times[{{ $section->id }}][{{ $col }}][end_time]"
                                       value="{{ $expJamSelesai }}"
                                       class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            </div>
                        </div>
                        @else
                        <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                            <div>Mulai: {{ $expJamMulai ?: '—' }}</div>
                            <div>Selesai: {{ $expJamSelesai ?: '—' }}</div>
                        </div>
                        @endif
                        @endif


                    </th>
                    @endfor
                </tr>
                {{-- Row 3: sub-column headers --}}
                <tr class="bg-sky-50/60 text-gray-500 border-b border-gray-200">
                    @if ($hasSharedTime)
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    @endif
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @if ($isPerLocation)
                        <th class="px-1.5 py-1.5 text-center font-medium border-r border-sky-100 whitespace-nowrap">JAM</th>
                    @endif
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    @endfor
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($section->locations as $loc)
                @php
                    $locEntries = collect();
                    for ($p = 1; $p <= $section->max_exposure; $p++) {
                        for ($s = 1; $s <= 2; $s++) {
                            if (isset($entryMap[$loc->pivot->id][$instance][$p][$s])) {
                                $locEntries->push($entryMap[$loc->pivot->id][$instance][$p][$s]);
                            }
                        }
                    }
                    $maxT   = $locEntries->max(fn($e) => ($cfuNum($e->cfu_bacteria) ?? 0) + ($cfuNum($e->cfu_fungi) ?? 0)) ?? 0;
                    $maxF   = $locEntries->max(fn($e) => $cfuNum($e->cfu_fungi) ?? 0) ?? 0;
                    $hasTMS = ($loc->alert_action_total && $maxT >= $loc->alert_action_total)
                           || ($loc->alert_action_fungi && $maxF >= $loc->alert_action_fungi);
                    $hasAlt = !$hasTMS && (
                                ($loc->alert_limit_total && $maxT >= $loc->alert_limit_total)
                             || ($loc->alert_limit_fungi    && $maxF >= $loc->alert_limit_fungi));
                    $konklusi = $locEntries->isEmpty() ? null : ($hasTMS ? 'TMS' : ($hasAlt ? 'Alert' : 'MS'));

                    $classBadge = match($loc->room->class) {
                        'A' => 'bg-purple-100 text-purple-700',
                        'B' => 'bg-blue-100 text-blue-700',
                        'C' => 'bg-amber-100 text-amber-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-blue-50/20 transition-colors">
                    <td class="px-2 py-2.5 text-center text-gray-400 border-r border-gray-100">{{ $loop->iteration }}</td>
                    <td class="px-3 py-2.5 text-gray-700 font-medium border-r border-gray-100 whitespace-nowrap">{{ $loc->room->room_name }}</td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="inline-flex items-center justify-center h-5 w-5 rounded text-[11px] font-bold {{ $classBadge }}">
                            {{ $loc->room->class }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center text-gray-500 border-r border-gray-100 whitespace-nowrap text-[11px]">{{ $loc->room->room_number }}</td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        @if (str_starts_with($loc->location_number, '*)'))
                            <span class="text-[11px] text-gray-400 italic">{{ $loc->location_number }}</span>
                        @else
                            <span class="text-[11px] text-gray-500">{{ $loc->location_number }}</span>
                        @endif
                    </td>
                    @if ($hasSharedTime)
                    @php
                        $msEntry = $entryMap[$loc->pivot->id][$instance][0][$myShift] ?? null;
                        $msTVal = $cfuTot($msEntry?->cfu_bacteria, $msEntry?->cfu_fungi);
                        $msLocked = $isEditable && $msEntry && $msEntry->analyst_id && $msEntry->analyst_id !== auth()->id()
                                    && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null);
                        $msEditable = $isEditable && !$msLocked;
                    @endphp
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($msEditable)
                            <input type="text" inputmode="text" name="entries[{{ $loc->pivot->id }}][{{ $instance }}][0][cfu_bacteria]"
                                   value="{{ $msEntry?->cfu_bacteria }}"
                                   placeholder="—"
                                   data-loc="{{ $loc->pivot->id }}-{{ $instance }}" data-col="0" data-type="b" data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @elseif ($msLocked)
                            <input type="text" value="{{ $msEntry?->cfu_bacteria }}" disabled
                                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
                        @else
                            <span class="text-[11px] {{ $msEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($msEditable)
                            <input type="text" inputmode="text" name="entries[{{ $loc->pivot->id }}][{{ $instance }}][0][cfu_fungi]"
                                   value="{{ $msEntry?->cfu_fungi }}"
                                   placeholder="—"
                                   data-loc="{{ $loc->pivot->id }}-{{ $instance }}" data-col="0" data-type="f"
                                   data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @elseif ($msLocked)
                            <input type="text" value="{{ $msEntry?->cfu_fungi }}" disabled
                                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
                        @else
                            <span class="text-[11px] {{ $msEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_fungi ?? '—' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                        <span id="t-{{ $loc->pivot->id }}-{{ $instance }}-0"
                              class="text-[11px] font-semibold {{ $msTVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
                            {{ $msTVal ?? '—' }}
                        </span>
                    </td>
                    @endif

                    {{-- Data columns per exposure/shift --}}
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @php
                        $colAsgn    = $secAssignments[$col] ?? 1;
                        $existEntry = $entryMap[$loc->pivot->id][$instance][$col][$colAsgn] ?? null;
                        $entryLocked = $isEditable && $existEntry && $existEntry->analyst_id
                                       && $existEntry->analyst_id !== auth()->id()
                                       && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null);
                        $editable = $isEditable && ($colAsgn == $myShift) && !$entryLocked;
                        $iName = "entries[{$loc->pivot->id}][{$instance}][{$col}]";
                        $rowKey = "{$loc->pivot->id}-{$instance}-{$col}";
                    @endphp

                    @if ($isPerLocation)
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="time" name="{{ $iName }}[start_time]"
                                   value="{{ $existEntry?->start_time }}"
                                   class="w-[84px] rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @elseif ($entryLocked)
                            <input type="time" value="{{ $existEntry?->start_time }}" disabled
                                   class="w-[84px] rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-gray-400 cursor-not-allowed">
                        @else
                            <span class="text-gray-{{ $existEntry?->start_time ? '600' : '300' }} text-[11px]">{{ $existEntry?->start_time ? \Illuminate\Support\Str::substr($existEntry->start_time, 0, 5) : '-' }}</span>
                        @endif
                    </td>
                    @endif

                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="text" inputmode="text" name="{{ $iName }}[cfu_bacteria]"
                                   value="{{ $existEntry?->cfu_bacteria }}"
                                   placeholder="—"
                                   data-loc="{{ $loc->pivot->id }}-{{ $instance }}" data-col="{{ $col }}" data-type="b" data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                                   @if (str_starts_with($loc->location_number, '*)')) data-optional="true" @endif
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @elseif ($entryLocked)
                            <input type="text" value="{{ $existEntry?->cfu_bacteria }}" disabled
                                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
                        @else
                            <span class="text-[11px] {{ $existEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        @endif
                    </td>

                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="text" inputmode="text" name="{{ $iName }}[cfu_fungi]"
                                   value="{{ $existEntry?->cfu_fungi }}"
                                   placeholder="—"
                                   data-loc="{{ $loc->pivot->id }}-{{ $instance }}" data-col="{{ $col }}" data-type="f" data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @elseif ($entryLocked)
                            <input type="text" value="{{ $existEntry?->cfu_fungi }}" disabled
                                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
                        @else
                            <span class="text-[11px] {{ $existEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_fungi ?? '—' }}
                            </span>
                        @endif
                    </td>

                    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                        @php
                            $tVal = $cfuTot($existEntry?->cfu_bacteria, $existEntry?->cfu_fungi);
                        @endphp
                        <span id="t-{{ $rowKey }}"
                              class="text-[11px] font-semibold {{ $tVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
                            {{ $tVal ?? '—' }}
                        </span>
                    </td>
                    @endfor

                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->alert_limit_total !== null ? 'text-amber-700' : 'text-gray-300' }}">
                            {{ $loc->alert_limit_total ?? '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->alert_limit_fungi !== null ? 'text-amber-700' : 'text-gray-300' }}">
                            {{ $loc->alert_limit_fungi ?? '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->alert_action_total !== null ? 'text-red-600' : 'text-gray-300' }}">
                            {{ $loc->alert_action_total !== null ? ($loc->alert_action_total == 1 ? '<1' : $loc->alert_action_total) : '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->alert_action_fungi !== null ? 'text-red-600' : 'text-gray-300' }}">
                            {{ $loc->alert_action_fungi !== null ? ($loc->alert_action_fungi == 1 ? '<1' : $loc->alert_action_fungi) : '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center konklusi-cell"
                        id="konklusi-{{ $loc->pivot->id }}-{{ $instance }}"
                        data-section-id="{{ $section->id }}"
                        data-section-instance="{{ $section->id }}-{{ $instance }}"
                        data-alert-t="{{ $loc->alert_limit_total ?? '' }}"
                        data-alert-f="{{ $loc->alert_limit_fungi ?? '' }}"
                        data-action-t="{{ $loc->alert_action_total ?? '' }}"
                        data-action-f="{{ $loc->alert_action_fungi ?? '' }}">
                        @if ($konklusi === 'TMS')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-red-100 text-red-700">TMS</span>
                        @elseif ($konklusi === 'Alert')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-yellow-100 text-yellow-700">Alert</span>
                        @elseif ($konklusi === 'MS')
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-green-100 text-green-700">MS</span>
                        @else
                            <span class="text-gray-300 text-[11px]">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="px-5 py-3 border-t border-gray-100 text-[11px] text-gray-400">
        @if ($isSwabTime)
        <p class="mb-1"><span class="text-gray-500">*)</span> diisi jika dibutuhkan</p>
        @endif
        <strong class="text-gray-500">Keterangan:</strong>
        B: Total Bakteri &nbsp;·&nbsp; F: Total Fungi &nbsp;·&nbsp; T: Total Bakteri + Fungi &nbsp;·&nbsp;
        MS: Memenuhi Spesifikasi &nbsp;·&nbsp; TMS: Tidak Memenuhi Spesifikasi
    </div>

    {{-- Catatan & Kesimpulan per seksi (only rendered by instance 1, aggregates all instances) --}}
    @if ($instance === 1)
    @php
        $secNote = $hd['section_notes'][$section->id] ?? [];
        // Auto-compute section conclusion across ALL instances
        $totalInstances  = (int) ($hd['_section_counts'][$section->id] ?? 1);
        $sectionHasTMS   = false;
        $sectionAllEmpty = true;
        for ($_inst = 1; $_inst <= $totalInstances; $_inst++) {
            foreach ($section->locations as $_loc) {
                $locEntries2 = collect();
                for ($p = 1; $p <= $section->max_exposure; $p++) {
                    for ($s = 1; $s <= 2; $s++) {
                        if (isset($entryMap[$_loc->pivot->id][$_inst][$p][$s])) {
                            $locEntries2->push($entryMap[$_loc->pivot->id][$_inst][$p][$s]);
                        }
                    }
                }
                if ($locEntries2->isNotEmpty()) {
                    $sectionAllEmpty = false;
                    $maxT2 = $locEntries2->max(fn($e) => ($cfuNum($e->cfu_bacteria) ?? 0) + ($cfuNum($e->cfu_fungi) ?? 0)) ?? 0;
                    $maxF2 = $locEntries2->max(fn($e) => $cfuNum($e->cfu_fungi) ?? 0) ?? 0;
                    if (($_loc->alert_action_total && $maxT2 >= $_loc->alert_action_total)
                        || ($_loc->alert_action_fungi && $maxF2 >= $_loc->alert_action_fungi)) {
                        $sectionHasTMS = true;
                    }
                }
            }
        }
        $sectionConclusion = $sectionAllEmpty ? null : ($sectionHasTMS ? 'TMS' : 'MS');
    @endphp
    <div class="px-5 py-4 border-t border-gray-100 space-y-3">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
            @if ($isEditable)
            <textarea name="header_data[section_notes][{{ $section->id }}][notes]" rows="2"
                      placeholder="Catatan untuk seksi ini..."
                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 resize-none
                             focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">{{ $secNote['notes'] ?? '' }}</textarea>
            @else
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700 min-h-[40px]">
                {{ $secNote['notes'] ?? '—' }}
            </div>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1.5">Kesimpulan</label>
            <input type="hidden"
                   name="header_data[section_notes][{{ $section->id }}][conclusion]"
                   id="section-konklusi-input-{{ $section->id }}"
                   value="{{ $sectionConclusion ?? '' }}">
            <div id="section-konklusi-{{ $section->id }}">
                @if ($sectionConclusion === 'TMS')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-100 text-red-700 border border-red-200">
                        Tidak Memenuhi Spesifikasi <span class="font-bold">(TMS)</span>
                    </span>
                @elseif ($sectionConclusion === 'MS')
                    <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold bg-green-100 text-green-700 border border-green-200">
                        Memenuhi Spesifikasi <span class="font-bold">(MS)</span>
                    </span>
                @else
                    <span class="text-xs text-gray-400 italic">Belum ada data</span>
                @endif
            </div>
        </div>
    </div>

    {{-- ── Per-section Signature ─────────────────────────────── --}}
    @php
        // Collect unique analyst_ids from all entries belonging to this section
        $_sectionPivotIds = $section->locations->pluck('pivot.id')->toArray();
        $_secAnalysts = [];
        foreach ($_sectionPivotIds as $_pid) {
            foreach ($entryMap[$_pid] ?? [] as $_instMap) {
                foreach ($_instMap as $_pMap) {
                    foreach ($_pMap as $_e) {
                        if ((int) $_e->analyst_id) {
                            $_secAnalysts[(string) $_e->analyst_id] = true;
                        }
                    }
                }
            }
        }
        $_secAnalystIds = array_keys($_secAnalysts);
        $_allMonIds  = array_map('strval', $report->analyst_monitoring ?? []);
        $_allReadIds = array_map('strval', $report->analyst_reading    ?? []);
        $_secMonTs   = $hd['section_ttd_monitoring'][(string) $section->id] ?? [];
        $_secReadTs  = $hd['section_ttd_reading'][(string) $section->id]    ?? [];
        // Union: analysts with entries in this section + analysts who saved time fields (timestamp-based)
        $_secMonIds  = array_values(array_unique(array_merge(
            array_intersect($_secAnalystIds, $_allMonIds),
            array_intersect(array_keys($_secMonTs), $_allMonIds)
        )));
        $_secReadIds = array_values(array_unique(array_merge(
            array_intersect($_secAnalystIds, $_allReadIds),
            array_intersect(array_keys($_secReadTs), $_allReadIds)
        )));
        // Supervisor & Manager approvals (same for all sections)
        $_supApproval = $report->approvals->firstWhere('step', 2);
        $_mngrApproval = $report->approvals->firstWhere('step', 3);
        // Fetch user objects in one query
        $_secUniqueIds = array_unique(array_filter(array_merge($_secMonIds, $_secReadIds)));
        $_secUserMap   = \App\Models\User::whereIn('id', $_secUniqueIds)->get()->keyBy('id');
    @endphp
    <div class="px-5 py-4 border-t border-gray-100">
        <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-3">Tanda Tangan & Verifikasi</p>
        <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">
            {{-- Dimonitoring oleh --}}
            <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                <p class="text-[11px] font-semibold text-gray-600 mb-2">Dimonitoring oleh:</p>
                <div class="flex-1 flex flex-col gap-2 justify-center">
                    @forelse ($_secMonIds as $_uid)
                    @php $_u = $_secUserMap->get($_uid); $_ts = isset($_secMonTs[$_uid]) ? \Illuminate\Support\Carbon::parse($_secMonTs[$_uid]) : null; @endphp
                    @if ($_u)
                    <div class="text-center">
                        @if ($_ts)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Tersimpan
                        </span>
                        @endif
                        <p class="text-sm font-semibold text-gray-700">{{ $_u->name }}</p>
                        @if ($_ts)
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $_ts->isoFormat('D MMM Y, HH:mm') }}</p>
                        @endif
                    </div>
                    @endif
                    @empty
                    <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                    @endforelse
                </div>
                <p class="text-[10px] text-gray-400 text-center mt-2">(Analis Lab. Mikrobiologi)</p>
            </div>
            {{-- Dibaca oleh --}}
            <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                <p class="text-[11px] font-semibold text-gray-600 mb-2">Dibaca oleh:</p>
                <div class="flex-1 flex flex-col gap-2 justify-center">
                    @forelse ($_secReadIds as $_uid)
                    @php $_u = $_secUserMap->get($_uid); $_ts = isset($_secReadTs[$_uid]) ? \Illuminate\Support\Carbon::parse($_secReadTs[$_uid]) : null; @endphp
                    @if ($_u)
                    <div class="text-center">
                        @if ($_ts)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Tersimpan
                        </span>
                        @endif
                        <p class="text-sm font-semibold text-gray-700">{{ $_u->name }}</p>
                        @if ($_ts)
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ $_ts->isoFormat('D MMM Y, HH:mm') }}</p>
                        @endif
                    </div>
                    @endif
                    @empty
                    <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                    @endforelse
                </div>
                <p class="text-[10px] text-gray-400 text-center mt-2">(Analis Lab. Mikrobiologi)</p>
            </div>
            {{-- Direview oleh --}}
            <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                <p class="text-[11px] font-semibold text-gray-600 mb-2">Direview oleh:</p>
                <div class="flex-1 flex flex-col gap-2 justify-center">
                    @if ($_supApproval?->user)
                    <div class="text-center">
                        <p class="text-sm font-semibold text-gray-700">{{ $_supApproval->user->name }}</p>
                        @if ($_supApproval->signed_at)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Disetujui
                        </span>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ \Illuminate\Support\Carbon::parse($_supApproval->signed_at)->isoFormat('D MMM Y, HH:mm') }}</p>
                        @endif
                    </div>
                    @else
                    <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                    @endif
                </div>
                <p class="text-[10px] text-gray-400 text-center mt-2">(Supervisor Mikrobiologi)</p>
            </div>
            {{-- Disetujui oleh --}}
            <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
                <p class="text-[11px] font-semibold text-gray-600 mb-2">Disetujui oleh:</p>
                <div class="flex-1 flex flex-col gap-2 justify-center">
                    @if ($_mngrApproval?->user)
                    <div class="text-center">
                        <p class="text-sm font-semibold text-gray-700">{{ $_mngrApproval->user->name }}</p>
                        @if ($_mngrApproval->signed_at)
                        <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Disetujui
                        </span>
                        <p class="text-[11px] text-gray-500 mt-0.5">{{ \Illuminate\Support\Carbon::parse($_mngrApproval->signed_at)->isoFormat('D MMM Y, HH:mm') }}</p>
                        @endif
                    </div>
                    @else
                    <div class="text-center"><div class="h-px w-12 border-b border-dashed border-gray-300 mx-auto"></div></div>
                    @endif
                </div>
                <p class="text-[10px] text-gray-400 text-center mt-2">(QC Manager)</p>
            </div>
        </div>
    </div>
    @endif
</div>
