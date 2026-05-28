{{-- ── Single body row for one location ──────────────────────────────────── --}}
{{-- Requires (from parent scope):                                             --}}
{{--   $loc, $_rowNum, $instance, $myShift, $entryMap, $section               --}}
{{--   CFU helpers ($cfuNum, $cfuTot) from SectionTableComposer               --}}
{{--   Type flags ($hasMachineSetup, $isPerLocation, …) from SectionTableComposer --}}
@php
    $classBadge = match($loc->room->class) {
        'A'     => 'bg-purple-100 text-purple-700',
        'B'     => 'bg-blue-100 text-blue-700',
        'C'     => 'bg-amber-100 text-amber-700',
        default => 'bg-gray-100 text-gray-600',
    };

    // Collect all CFU entries for this location across all columns/shifts (including machine setup period 0)
    $locEntries = collect();
    for ($p = 0; $p <= $section->max_column; $p++) {
        for ($s = 1; $s <= 2; $s++) {
            if (isset($entryMap[$loc->id][$instance][$p][$s])) {
                $locEntries->push($entryMap[$loc->id][$instance][$p][$s]);
            }
        }
    }

    // Conclusion flags for this row
    $maxT   = $locEntries->max(fn($e) => ($cfuNum($e->cfu_bacteria) ?? 0) + ($cfuNum($e->cfu_fungi) ?? 0)) ?? 0;
    $maxF   = $locEntries->max(fn($e) => $cfuNum($e->cfu_fungi) ?? 0) ?? 0;
    $hasTMS = ($loc->alert_action_total && $maxT >= $loc->alert_action_total)
           || ($loc->alert_action_fungi && $maxF >= $loc->alert_action_fungi);
    $hasAlt = !$hasTMS && (
                ($loc->alert_limit_total && $maxT >= $loc->alert_limit_total)
             || ($loc->alert_limit_fungi && $maxF >= $loc->alert_limit_fungi));
    $hasCfuEntries = $locEntries->contains(fn($e) => $e->cfu_bacteria !== null || $e->cfu_fungi !== null);
    $konklusi      = $hasCfuEntries ? ($hasTMS ? 'TMS' : ($hasAlt ? 'Alert' : 'MS')) : null;
@endphp
<tr class="hover:bg-blue-50/20 transition-colors">
    {{-- Fixed columns --}}
    <td class="px-2 py-2.5 text-center text-gray-400 border-r border-gray-100">{{ $_rowNum }}</td>
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

    {{-- Machine Set-up columns --}}
    @if ($hasMachineSetup)
    @php
        $msEntry       = $entryMap[$loc->id][$instance][0][$myShift] ?? null;
        $msTVal        = $cfuTot($msEntry?->cfu_bacteria, $msEntry?->cfu_fungi);
        $msLocked      = $isEditable && $msEntry && $msEntry->analyst_id
                         && $msEntry->analyst_id !== auth()->id()
                         && ($msEntry->cfu_bacteria !== null || $msEntry->cfu_fungi !== null);
        $msCfuEditable = $isEditable && $isReading && !$msLocked && $msHasTime;
        $msNa          = $isReading && !$msHasTime;
    @endphp
    {{-- B --}}
    <td class="px-1 py-2 border-r border-gray-100 text-center">
        @if ($msCfuEditable)
            <input type="text" inputmode="text"
                   name="entries[{{ $loc->id }}][{{ $instance }}][0][cfu_bacteria]"
                   value="{{ $msEntry?->cfu_bacteria }}"
                   list="cfu-value-options"
                   placeholder="—"
                   data-loc="{{ $loc->id }}-{{ $instance }}" data-col="0" data-type="b"
                   data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
        @elseif ($msLocked)
            <input type="text" value="{{ $msEntry?->cfu_bacteria }}" disabled
                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
        @elseif ($msNa && $msEntry?->cfu_bacteria === null)
            <span class="text-[11px] text-gray-400 italic">N/A</span>
        @else
            <span class="text-[11px] {{ $msEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                {{ $msEntry?->cfu_bacteria ?? 'N/A' }}
            </span>
        @endif
    </td>
    {{-- F --}}
    <td class="px-1 py-2 border-r border-gray-100 text-center">
        @if ($msCfuEditable)
            <input type="text" inputmode="text"
                   name="entries[{{ $loc->id }}][{{ $instance }}][0][cfu_fungi]"
                   value="{{ $msEntry?->cfu_fungi }}"
                   list="cfu-value-options"
                   placeholder="—"
                   data-loc="{{ $loc->id }}-{{ $instance }}" data-col="0" data-type="f"
                   data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
        @elseif ($msLocked)
            <input type="text" value="{{ $msEntry?->cfu_fungi }}" disabled
                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
        @elseif ($msNa && $msEntry?->cfu_fungi === null)
            <span class="text-[11px] text-gray-400 italic">N/A</span>
        @else
            <span class="text-[11px] {{ $msEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                {{ $msEntry?->cfu_fungi ?? 'N/A' }}
            </span>
        @endif
    </td>
    {{-- T (computed) --}}
    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
          <span id="t-{{ $loc->id }}-{{ $instance }}-0"
              class="text-[11px] font-semibold {{ $msTVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
            {{ $msTVal ?? 'N/A' }}
        </span>
    </td>
    @endif

    {{-- Exposure/period data columns --}}
    @for ($col = 1; $col <= $maxCols; $col++)
    @php
        $colAsgn     = $secAssignments[$col] ?? 1;
        $existEntry  = $entryMap[$loc->id][$instance][$col][$colAsgn] ?? null;
        $entryLocked = $isEditable && $existEntry && $existEntry->analyst_id
                       && $existEntry->analyst_id !== auth()->id()
                       && ($existEntry->cfu_bacteria !== null || $existEntry->cfu_fungi !== null);
        $iName  = "entries[{$loc->id}][{$instance}][{$col}]";
        $rowKey = "{$loc->id}-{$instance}-{$col}";

        // Determine whether this column has any time data (gates reading-phase B/F inputs)
        if ($isDualAB) {
            $_abClass = strtolower((string) ($loc->room->class ?? ''));
            $_stAB = $secTimesFromEntries[$col][$_abClass] ?? [];
            $colHasTime = ! empty($_stAB['start_time']);
        } elseif ($hasTime) {
            $colHasTime = ! empty($secTimesFromEntries[$col]['start_time'] ?? null);
        } elseif ($isSwabTime) {
            $_locNum = (string) ($loc->location_number ?? '');
            $_swabKey = stripos($_locNum, 'S1-3') !== false
                ? 's1_3'
                : (stripos($_locNum, 'S1-2') !== false ? 's1_2' : 's1');
            $colHasTime = ! empty($secTimesFromEntries[$col]['swab'][$_swabKey]['mulai'] ?? null);
        } elseif ($isPerLocation) {
            $colHasTime = ! empty($existEntry?->start_time);
        } else {
            $colHasTime = true;
        }

        $timeEditable = $isEditable && $isMonitoring && ($colAsgn == $myShift) && !$entryLocked;
        $cfuEditable  = $isEditable && $isReading && ($colAsgn == $myShift) && !$entryLocked && $colHasTime;
        $cfuNa        = $isReading && !$colHasTime;
        $tVal         = $cfuTot($existEntry?->cfu_bacteria, $existEntry?->cfu_fungi);
    @endphp

    {{-- JAM (per-location time) --}}
    @if ($isPerLocation)
    <td class="px-1 py-2 border-r border-gray-100 text-center">
        @if ($timeEditable)
            <div class="inline-flex items-center gap-0.5" data-time-now-wrapper>
                <input type="time" name="{{ $iName }}[start_time]"
                       value="{{ $existEntry?->start_time }}"
                       data-time-now-input
                       class="w-[84px] rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-gray-700
                              focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                <button type="button"
                        data-time-now-btn
                        title="Isi jam sekarang"
                        class="rounded border border-sky-200 bg-sky-50 px-1 py-0 text-[9px] font-semibold text-sky-700 hover:bg-sky-100">
                    Now
                </button>
            </div>
        @elseif ($entryLocked)
            <input type="time" value="{{ $existEntry?->start_time }}" disabled
                   class="w-[84px] rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-gray-400 cursor-not-allowed">
        @elseif ($isReading && !$existEntry?->start_time)
            <span class="text-[11px] text-gray-400 italic">N/A</span>
        @else
            <span class="text-gray-{{ $existEntry?->start_time ? '600' : '300' }} text-[11px]">
                {{ $existEntry?->start_time ? \Illuminate\Support\Str::substr($existEntry->start_time, 0, 5) : 'N/A' }}
            </span>
        @endif
    </td>
    @endif

    {{-- B --}}
    <td class="px-1 py-2 border-r border-gray-100 text-center">
        @if ($cfuEditable)
            <input type="text" inputmode="text" name="{{ $iName }}[cfu_bacteria]"
                   value="{{ $existEntry?->cfu_bacteria }}"
                   list="cfu-value-options"
                   placeholder="—"
                   data-loc="{{ $loc->id }}-{{ $instance }}" data-col="{{ $col }}" data-type="b"
                   data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                   @if (str_starts_with($loc->location_number, '*)')) data-optional="true" @endif
                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
        @elseif ($entryLocked)
            <input type="text" value="{{ $existEntry?->cfu_bacteria }}" disabled
                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
        @elseif ($cfuNa && $existEntry?->cfu_bacteria === null)
            <span class="text-[11px] text-gray-400 italic">N/A</span>
        @else
            <span class="text-[11px] {{ $existEntry?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                {{ $existEntry?->cfu_bacteria ?? 'N/A' }}
            </span>
        @endif
    </td>

    {{-- F --}}
    <td class="px-1 py-2 border-r border-gray-100 text-center">
        @if ($cfuEditable)
            <input type="text" inputmode="text" name="{{ $iName }}[cfu_fungi]"
                   value="{{ $existEntry?->cfu_fungi }}"
                   list="cfu-value-options"
                   placeholder="—"
                   data-loc="{{ $loc->id }}-{{ $instance }}" data-col="{{ $col }}" data-type="f"
                   data-section-id="{{ $section->id }}" data-section-instance="{{ $section->id }}-{{ $instance }}"
                   class="w-12 rounded border border-gray-200 bg-white px-1 py-0.5 text-[11px] text-center text-gray-700
                          focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none cfu-input">
        @elseif ($entryLocked)
            <input type="text" value="{{ $existEntry?->cfu_fungi }}" disabled
                   class="w-12 rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[11px] text-center text-gray-400 cursor-not-allowed">
        @elseif ($cfuNa && $existEntry?->cfu_fungi === null)
            <span class="text-[11px] text-gray-400 italic">N/A</span>
        @else
            <span class="text-[11px] {{ $existEntry?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">
                {{ $existEntry?->cfu_fungi ?? 'N/A' }}
            </span>
        @endif
    </td>

    {{-- T (computed) --}}
    <td class="px-1 py-2 border-r border-gray-100 text-center bg-gray-50/40">
        <span id="t-{{ $rowKey }}"
              class="text-[11px] font-semibold {{ $tVal !== null ? 'text-gray-700' : 'text-gray-300' }}">
            {{ $tVal ?? 'N/A' }}
        </span>
    </td>
    @endfor

    {{-- Alert Limit --}}
    <td class="px-2 py-2.5 text-center border-r border-gray-100">
        <span class="text-[11px] font-medium {{ $loc->alert_limit_total !== null ? 'text-amber-700' : 'text-gray-300' }}">
            {{ $loc->alert_limit_total ?? 'N/A' }}
        </span>
    </td>
    <td class="px-2 py-2.5 text-center border-r border-gray-100">
        <span class="text-[11px] font-medium {{ $loc->alert_limit_fungi !== null ? 'text-amber-700' : 'text-gray-300' }}">
            {{ $loc->alert_limit_fungi ?? 'N/A' }}
        </span>
    </td>

    {{-- Alert Action --}}
    <td class="px-2 py-2.5 text-center border-r border-gray-100">
        <span class="text-[11px] font-medium {{ $loc->alert_action_total !== null ? 'text-red-600' : 'text-gray-300' }}">
            {{ $loc->alert_action_total !== null ? ($loc->alert_action_total == 1 ? '<1' : $loc->alert_action_total) : 'N/A' }}
        </span>
    </td>
    <td class="px-2 py-2.5 text-center border-r border-gray-100">
        <span class="text-[11px] font-medium {{ $loc->alert_action_fungi !== null ? 'text-red-600' : 'text-gray-300' }}">
            {{ $loc->alert_action_fungi !== null ? ($loc->alert_action_fungi == 1 ? '<1' : $loc->alert_action_fungi) : 'N/A' }}
        </span>
    </td>

    {{-- Kesimpulan --}}
    <td class="px-2 py-2.5 text-center konklusi-cell"
        id="konklusi-{{ $loc->id }}-{{ $instance }}"
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
