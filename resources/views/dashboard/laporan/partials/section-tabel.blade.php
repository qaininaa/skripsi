{{-- ── Tabel Pengukuran per Seksi (dipakai di dalam @foreach sections) ── --}}
@php
    $isShiftBased  = in_array($section->measurement_type, ['air_sampler', 'contact_plate', 'swab']);
    $isSettlePlate = $section->measurement_type === 'settle_plate';
    $isSwab        = $section->measurement_type === 'swab';
    $hasJam        = $section->measurement_type === 'air_sampler';
    $maxCols       = $section->max_exposures;
    $romanNums     = ['I', 'II', 'III', 'IV', 'V', 'VI'];
    $secNum        = $loop->index + 5;
    $savedAsgn      = ($report->header_data['shift_assignments'] ?? [])[$section->id] ?? [];
    $secAssignments = [];
    for ($c = 1; $c <= $maxCols; $c++) {
        $secAssignments[$c] = isset($savedAsgn[$c]) ? (int)$savedAsgn[$c] : 1;
    }
@endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 overflow-hidden">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
        <div class="h-7 w-7 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0">
            <span class="text-xs font-bold text-sky-600">{{ $secNum }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-sm text-gray-700">{{ $section->measurement_unit }}</h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $section->name }}</p>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse" style="min-width: {{ 480 + (!$isShiftBased ? 130 : 0) + ($maxCols * ($isShiftBased ? ($hasJam ? 220 : ($isSwab ? 220 : 160)) : 130)) }}px">
            <thead>
                {{-- Row 1: group headers --}}
                <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No.</th>
                    <th class="px-3 py-2 text-left font-semibold border-r border-sky-100" rowspan="3">Room Name</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Class</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Room Number</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Location<br>Number</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100"
                    colspan="{{ (!$isShiftBased ? 3 : 0) + $maxCols * ($isShiftBased ? ($hasJam ? 4 : 3) : 3) }}">
                        {{ $section->measurement_unit }}
                    </th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Alert<br>Limit</th>
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Action<br>Limit</th>
                    <th class="px-2 py-2 text-center font-semibold whitespace-nowrap" rowspan="3">Kesimpulan</th>
                </tr>
                {{-- Row 2: period/shift labels --}}
                <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">
                    @if (!$isShiftBased)
                    @php
                        $msJamMulai = null; $msJamSelesai = null;
                        foreach ($section->locations as $loc2) {
                            $e0 = $entryMap[$loc2->id][0][$myShift] ?? null;
                            if ($e0 && ($e0->start_time || $e0->end_time)) {
                                $msJamMulai   = $e0->start_time;
                                $msJamSelesai = $e0->end_time;
                                break;
                            }
                        }
                    @endphp
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100" colspan="3">
                        <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">Machine Set-up</div>
                        @if ($isEditable)
                        <div class="flex justify-center items-center gap-1">
                            <input type="time" name="exposure_times[{{ $section->id }}][0][start_time]"
                                   value="{{ $msJamMulai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            <span class="text-gray-400 text-[10px] font-normal">–</span>
                            <input type="time" name="exposure_times[{{ $section->id }}][0][end_time]"
                                   value="{{ $msJamSelesai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        </div>
                        @else
                        <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                            {{ $msJamMulai ? $msJamMulai . ' – ' . ($msJamSelesai ?? '—') : '—' }}
                        </div>
                        @endif
                    </th>
                    @endif
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @if ($isShiftBased)
                    @php $colAsgn = $secAssignments[$col] ?? $col; @endphp
                    <th class="px-2 py-1.5 text-center font-semibold border-r border-sky-100 whitespace-nowrap"
                        colspan="{{ $hasJam ? 4 : 3 }}">
                        Shift
                        @if ($isSwab)
                        @php $swabColTimes = $hd['swab_times'][$section->id][$col] ?? []; @endphp
                        @if ($isEditable)
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
                        <input type="hidden" name="shift_assignment[{{ $section->id }}][{{ $col }}]" id="sa-{{ $section->id }}-{{ $col }}" value="{{ $colAsgn }}">
                        @if ($isEditable && $myShift === 1 && !$shift1HandedOver)
                        <div class="flex justify-center gap-1 mt-1.5">
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 1)" id="sa-btn-{{ $section->id }}-{{ $col }}-1"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 1 ? 'bg-sky-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S1</button>
                            @if ($report->shift2Analis)
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 2)" id="sa-btn-{{ $section->id }}-{{ $col }}-2"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 2 ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S2</button>
                            @endif
                        </div>
                        @else
                        <div class="flex justify-center mt-1.5">
                            <span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $colAsgn == 1 ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">{{ $colAsgn == 1 ? 'S1' : 'S2' }}</span>
                        </div>
                        @endif
                    </th>
                    @else
                    @php
                        if ($isSettlePlate) {
                            $expJamMulai = null; $expJamSelesai = null;
                        } else {
                            $expJamMulai = null; $expJamSelesai = null;
                            foreach ($section->locations as $loc2) {
                                $e2 = $entryMap[$loc2->id][$col][$myShift] ?? null;
                                if ($e2 && ($e2->start_time || $e2->end_time)) {
                                    $expJamMulai   = $e2->start_time;
                                    $expJamSelesai = $e2->end_time;
                                    break;
                                }
                            }
                        }
                    @endphp
                    <th class="px-2 py-2 text-center font-semibold border-r border-sky-100" colspan="3">
                        <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">
                            Exposure {{ $romanNums[$col - 1] ?? $col }}
                        </div>
                        @if ($isSettlePlate)
                            @if ($isEditable)
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
                        @else
                        @if ($isEditable)
                        <div class="flex justify-center items-center gap-1">
                            <input type="time" name="exposure_times[{{ $section->id }}][{{ $col }}][start_time]"
                                   value="{{ $expJamMulai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                            <span class="text-gray-400 text-[10px] font-normal">–</span>
                            <input type="time" name="exposure_times[{{ $section->id }}][{{ $col }}][end_time]"
                                   value="{{ $expJamSelesai }}"
                                   class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        </div>
                        @else
                        <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                            {{ $expJamMulai ? $expJamMulai . ' – ' . ($expJamSelesai ?? '—') : '—' }}
                        </div>
                        @endif
                        @endif
                        {{-- Shift assignment toggle --}}
                        @php $colAsgn = $secAssignments[$col] ?? 1; @endphp
                        <input type="hidden" name="shift_assignment[{{ $section->id }}][{{ $col }}]" id="sa-{{ $section->id }}-{{ $col }}" value="{{ $colAsgn }}">
                        @if ($isEditable && $myShift === 1 && !$shift1HandedOver)
                        <div class="flex justify-center gap-1 mt-1.5">
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 1)" id="sa-btn-{{ $section->id }}-{{ $col }}-1"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 1 ? 'bg-sky-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S1</button>
                            @if ($report->shift2Analis)
                            <button type="button" onclick="setAssignment({{ $section->id }}, {{ $col }}, 2)" id="sa-btn-{{ $section->id }}-{{ $col }}-2"
                                    class="px-1.5 py-0.5 text-[10px] rounded font-semibold transition-colors {{ $colAsgn == 2 ? 'bg-amber-500 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">S2</button>
                            @endif
                        </div>
                        @else
                        <div class="flex justify-center mt-1.5">
                            <span class="px-1.5 py-0.5 text-[10px] rounded font-semibold {{ $colAsgn == 1 ? 'bg-sky-100 text-sky-700' : 'bg-amber-100 text-amber-700' }}">{{ $colAsgn == 1 ? 'S1' : 'S2' }}</span>
                        </div>
                        @endif
                    </th>
                    @endif
                    @endfor
                </tr>
                {{-- Row 3: sub-column headers --}}
                <tr class="bg-sky-50/60 text-gray-500 border-b border-gray-200">
                    @if (!$isShiftBased)
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    @endif
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @if ($isShiftBased)
                        @if ($hasJam)
                        <th class="px-1.5 py-1.5 text-center font-medium border-r border-sky-100 whitespace-nowrap">JAM</th>
                        @endif
                    @endif
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                        <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">T</th>
                    @endfor
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">B</th>
                    <th class="px-2 py-1.5 text-center font-medium border-r border-sky-100">F</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-50">
                @foreach ($section->locations as $loc)
                @php
                    $locEntries = collect();
                    for ($p = 1; $p <= $section->max_exposures; $p++) {
                        for ($s = 1; $s <= 2; $s++) {
                            if (isset($entryMap[$loc->id][$p][$s])) {
                                $locEntries->push($entryMap[$loc->id][$p][$s]);
                            }
                        }
                    }
                    $maxB   = $locEntries->max(fn($e) => $e->cfu_bacteria ?? 0) ?? 0;
                    $maxF   = $locEntries->max(fn($e) => $e->cfu_fungi ?? 0) ?? 0;
                    $hasTMS = ($loc->action_limit_bacteria && $maxB >= $loc->action_limit_bacteria)
                           || ($loc->action_limit_fungi && $maxF >= $loc->action_limit_fungi);
                    $hasAlt = !$hasTMS && (
                                ($loc->alert_limit_bacteria && $maxB >= $loc->alert_limit_bacteria)
                             || ($loc->alert_limit_fungi    && $maxF >= $loc->alert_limit_fungi));
                    $konklusi = $locEntries->isEmpty() ? null : ($hasTMS ? 'TMS' : ($hasAlt ? 'Alert' : 'MS'));

                    $classBadge = match($loc->class) {
                        'A' => 'bg-purple-100 text-purple-700',
                        'B' => 'bg-blue-100 text-blue-700',
                        'C' => 'bg-amber-100 text-amber-700',
                        default => 'bg-gray-100 text-gray-600',
                    };
                @endphp
                <tr class="hover:bg-blue-50/20 transition-colors">
                    <td class="px-2 py-2.5 text-center text-gray-400 border-r border-gray-100">{{ $loc->s_no }}</td>
                    <td class="px-3 py-2.5 text-gray-700 font-medium border-r border-gray-100 whitespace-nowrap">{{ $loc->room_name }}</td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="inline-flex items-center justify-center h-5 w-5 rounded text-[11px] font-bold {{ $classBadge }}">
                            {{ $loc->class }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center text-gray-500 border-r border-gray-100 whitespace-nowrap font-mono text-[11px]">{{ $loc->room_number }}</td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        @if (str_starts_with($loc->location_number, '*)'))
                            <span class="font-mono text-[11px] text-gray-400 italic">{{ $loc->location_number }}</span>
                        @else
                            <span class="font-mono text-[11px] text-gray-500">{{ $loc->location_number }}</span>
                        @endif
                    </td>
                    @if (!$isShiftBased)
                    @php
                        $msEntry = $entryMap[$loc->id][0][$myShift] ?? null;
                        $msTVal = ($msEntry && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null))
                            ? ($msEntry->cfu_bacteria ?? 0) + ($msEntry->cfu_fungi ?? 0) : null;
                    @endphp
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($isEditable)
                            <input type="number" min="0" name="entries[{{ $loc->id }}][0][cfu_bacteria]"
                                   value="{{ $msEntry?->cfu_bacteria }}"
                                   data-loc="{{ $loc->id }}" data-col="0" data-type="b" data-section-id="{{ $section->id }}"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $msEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($isEditable)
                            <input type="number" min="0" name="entries[{{ $loc->id }}][0][cfu_fungi]"
                                   value="{{ $msEntry?->cfu_fungi }}"
                                   data-loc="{{ $loc->id }}" data-col="0" data-type="f"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $msEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $msEntry?->cfu_fungi ?? '—' }}
                            </span>
                        @endif
                    </td>
                    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                        <span id="t-{{ $loc->id }}-0"
                              class="text-[11px] font-semibold {{ $msTVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
                            {{ $msTVal ?? '—' }}
                        </span>
                    </td>
                    @endif

                    {{-- Data columns per exposure/shift --}}
                    @for ($col = 1; $col <= $maxCols; $col++)
                    @php
                        $colAsgn = $secAssignments[$col] ?? 1;
                        if ($isShiftBased) {
                            $existEntry = $entryMap[$loc->id][1][$colAsgn] ?? null;
                            $editable   = $isEditable && ($colAsgn == $myShift);
                        } else {
                            $existEntry = $entryMap[$loc->id][$col][$colAsgn] ?? null;
                            $editable   = $isEditable && ($colAsgn == $myShift);
                        }
                        $iName = "entries[{$loc->id}][{$col}]";
                        $rowKey = "{$loc->id}-{$col}";
                    @endphp

                    @if ($hasJam)
                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="time" name="{{ $iName }}[start_time]"
                                   value="{{ $existEntry?->start_time }}"
                                   class="w-[84px] rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @else
                            <span class="text-gray-{{ $existEntry?->start_time ? '600' : '300' }} text-[11px]">{{ $existEntry?->start_time ? \Illuminate\Support\Str::substr($existEntry->start_time, 0, 5) : '-' }}</span>
                        @endif
                    </td>
                    @endif

                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="number" min="0" name="{{ $iName }}[cfu_bacteria]"
                                   value="{{ $existEntry?->cfu_bacteria }}"
                                   data-loc="{{ $loc->id }}" data-col="{{ $col }}" data-type="b" data-section-id="{{ $section->id }}"
                                   @if (str_starts_with($loc->location_number, '*)')) data-optional="true" @endif
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $existEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_bacteria ?? '—' }}
                            </span>
                        @endif
                    </td>

                    <td class="px-1 py-2 border-r border-gray-100 text-center">
                        @if ($editable)
                            <input type="number" min="0" name="{{ $iName }}[cfu_fungi]"
                                   value="{{ $existEntry?->cfu_fungi }}"
                                   data-loc="{{ $loc->id }}" data-col="{{ $col }}" data-type="f" data-section-id="{{ $section->id }}"
                                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
                        @else
                            <span class="text-[11px] {{ $existEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                                {{ $existEntry?->cfu_fungi ?? '—' }}
                            </span>
                        @endif
                    </td>

                    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
                        @php
                            $tVal = ($existEntry && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null))
                                ? ($existEntry->cfu_bacteria ?? 0) + ($existEntry->cfu_fungi ?? 0)
                                : null;
                        @endphp
                        <span id="t-{{ $rowKey }}"
                              class="text-[11px] font-semibold {{ $tVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
                            {{ $tVal ?? '—' }}
                        </span>
                    </td>
                    @endfor

                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->alert_limit_bacteria !== null ? 'text-amber-700' : 'text-gray-300' }}">
                            {{ $loc->alert_limit_bacteria ?? '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->alert_limit_fungi !== null ? 'text-amber-700' : 'text-gray-300' }}">
                            {{ $loc->alert_limit_fungi ?? '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->action_limit_bacteria !== null ? 'text-red-600' : 'text-gray-300' }}">
                            {{ $loc->action_limit_bacteria !== null ? ($loc->action_limit_bacteria == 1 ? '<1' : $loc->action_limit_bacteria) : '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center border-r border-gray-100">
                        <span class="text-[11px] font-medium {{ $loc->action_limit_fungi !== null ? 'text-red-600' : 'text-gray-300' }}">
                            {{ $loc->action_limit_fungi !== null ? ($loc->action_limit_fungi == 1 ? '<1' : $loc->action_limit_fungi) : '—' }}
                        </span>
                    </td>
                    <td class="px-2 py-2.5 text-center"
                        id="konklusi-{{ $loc->id }}"
                        data-alert-b="{{ $loc->alert_limit_bacteria ?? '' }}"
                        data-alert-f="{{ $loc->alert_limit_fungi ?? '' }}"
                        data-action-b="{{ $loc->action_limit_bacteria ?? '' }}"
                        data-action-f="{{ $loc->action_limit_fungi ?? '' }}">
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
        @if ($section->measurement_type === 'swab')
        <p class="mb-1"><span class="text-gray-500">*)</span> diisi jika dibutuhkan</p>
        @endif
        <strong class="text-gray-500">Keterangan:</strong>
        B: Total Bakteri &nbsp;·&nbsp; F: Total Fungi &nbsp;·&nbsp; T: Total Bakteri + Fungi &nbsp;·&nbsp;
        MS: Memenuhi Spesifikasi &nbsp;·&nbsp; TMS: Tidak Memenuhi Spesifikasi
    </div>

    {{-- Catatan & Kesimpulan per seksi --}}
    @php $secNote = $hd['section_notes'][$section->id] ?? []; @endphp
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
            @if ($isEditable)
            <div class="flex flex-wrap gap-2">
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border-2 transition-colors
                              {{ ($secNote['conclusion'] ?? '') === 'MS' ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-green-200' }}">
                    <input type="radio" name="header_data[section_notes][{{ $section->id }}][conclusion]" value="MS"
                           {{ ($secNote['conclusion'] ?? '') === 'MS' ? 'checked' : '' }}
                           class="text-green-500 focus:ring-green-400">
                    <span class="text-xs font-medium text-green-700">Memenuhi Spesifikasi <span class="font-bold">(MS)</span></span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer px-3 py-2 rounded-lg border-2 transition-colors
                              {{ ($secNote['conclusion'] ?? '') === 'TMS' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-red-200' }}">
                    <input type="radio" name="header_data[section_notes][{{ $section->id }}][conclusion]" value="TMS"
                           {{ ($secNote['conclusion'] ?? '') === 'TMS' ? 'checked' : '' }}
                           class="text-red-500 focus:ring-red-400">
                    <span class="text-xs font-medium text-red-700">Tidak Memenuhi Spesifikasi <span class="font-bold">(TMS)</span></span>
                </label>
            </div>
            @else
                @php $sk = $secNote['conclusion'] ?? ''; @endphp
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 inline-block text-xs">
                    @if ($sk === 'MS')
                        <span class="text-green-700 font-semibold">Memenuhi Spesifikasi (MS)</span>
                    @elseif ($sk === 'TMS')
                        <span class="text-red-700 font-semibold">Tidak Memenuhi Spesifikasi (TMS)</span>
                    @else
                        <span class="text-gray-400">Belum ditentukan</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
