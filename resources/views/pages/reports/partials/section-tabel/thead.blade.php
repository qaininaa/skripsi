{{-- ── Tabel Header (3 rows) ──────────────────────────────────────────────── --}}
{{-- Variables provided by SectionTableComposer + parent @include scope.     --}}
<thead>
    {{-- Row 1: group headers --}}
    <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No.</th>
        <th class="px-3 py-2 text-left font-semibold border-r border-sky-100" rowspan="3">Nama Ruangan</th>
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">Kelas</th>
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No. Ruangan</th>
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" rowspan="3">No.<br>Lokasi</th>
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100"
            colspan="{{ ($hasMachineSetup ? 3 : 0) + $maxCols * $subColsPerExp }}">
            {{ $section->measurement_unit }}
        </th>
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Alert<br>Limit</th>
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100 whitespace-nowrap" colspan="2" rowspan="2">Alert<br>Action</th>
        <th class="px-2 py-2 text-center font-semibold whitespace-nowrap" rowspan="3">Kesimpulan</th>
    </tr>

    {{-- Row 2: period/shift labels with time inputs ──────────────────────── --}}
    <tr class="bg-sky-50 text-gray-600 border-b border-sky-100">

        {{-- Machine Set-up column group --}}
        @if ($hasMachineSetup)
        @php
            $msTimes      = $secTimesFromEntries[0] ?? [];
            $msJamMulai   = $msTimes['start_time'] ?? null;
            $msJamSelesai = $msTimes['end_time'] ?? null;
        @endphp
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100" colspan="3">
            <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">Machine Set-up</div>
            @if ($isEditable && $isMonitoring)
            @php
                $msTimeLocked = $ms0TimeLockedByOther ?? false;
            @endphp
            <div class="flex justify-center items-center gap-1">
                <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][0][start_time]"
                       value="{{ $msJamMulai }}"
                       @if($msTimeLocked) readonly @endif
                       class="rounded border {{ $msTimeLocked ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                <span class="text-gray-400 text-[10px] font-normal">–</span>
                <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][0][end_time]"
                       value="{{ $msJamSelesai }}"
                       @if($msTimeLocked) readonly @endif
                       class="rounded border {{ $msTimeLocked ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
            </div>
            @else
            <div class="text-[10px] font-normal text-gray-500 whitespace-nowrap">
                {{ $msJamMulai ? $msJamMulai . ' – ' . ($msJamSelesai ?? 'N/A') : 'N/A' }}
            </div>
            @endif
        </th>
        @endif

        {{-- Exposure/period column groups --}}
        @for ($col = 1; $col <= $maxCols; $col++)
        <th class="px-2 py-1.5 text-center font-semibold border-r border-sky-100 whitespace-nowrap"
            colspan="{{ $subColsPerExp }}">
            @php
                $columnNameVal = $columnNames[$col] ?? null;
                $hasColLabel = (($colLabel ?? '') !== '');
                $colPeriod = ($hasColLabel && $maxCols > 1) ? ($romanNums[$col - 1] ?? $col) : '';
                $colHeaderTitle = trim((($colLabel ?? '') !== '' ? ($colLabel . ' ') : '') . $colPeriod);
                $colLabelIsLockedByOther = $columnLabelLockedByOther[$col] ?? false;
                $timeIsLockedByOther = $timeLockedByOther[$col] ?? false;
            @endphp
            {{ $colHeaderTitle }}

            @if ($isSettlePlate)
            @if ($isEditable && $isMonitoring)
            <div class="mt-1 flex items-center justify-center gap-1 text-[10px] font-normal text-gray-600">
                <span class="text-[9px] font-bold text-gray-500">SP:</span>
                <input type="text"
                       name="column_names[{{ $section->id }}][{{ $instance }}][{{ $col }}]"
                       value="{{ $columnNameVal }}"
                       placeholder="SP"
                       maxlength="100"
                       @if($colLabelIsLockedByOther) readonly @endif
                       class="w-20 rounded border {{ $colLabelIsLockedByOther ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
            </div>
            @else
            <div class="text-[10px] text-gray-500 mt-1">SP: {{ $columnNameVal ?: 'N/A' }}</div>
            @endif
            @else
            @if ($isEditable && $isMonitoring)
            <div class="mt-1 flex items-center justify-center gap-1 text-[10px] font-normal text-gray-600">
                <span class="text-[9px] font-bold text-gray-500">Shift:</span>
                <input type="text"
                       name="column_names[{{ $section->id }}][{{ $instance }}][{{ $col }}]"
                       value="{{ $columnNameVal }}"
                       placeholder="Shift"
                       maxlength="100"
                       @if($colLabelIsLockedByOther) readonly @endif
                       class="w-20 rounded border {{ $colLabelIsLockedByOther ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                @if ($colLabelIsLockedByOther)
                <span class="text-[9px] text-amber-500" title="Terkunci analis lain">🔒</span>
                @endif
            </div>
            @else
            <div class="text-[10px] text-gray-500 mt-1">Shift: {{ $columnNameVal ?: 'N/A' }}</div>
            @endif
            @endif

            {{-- Swab time slots --}}
            @if ($isSwabTime)
            @php $swabColTimes = $secTimesFromEntries[$col]['swab'] ?? []; @endphp
            @if ($isEditable && $isMonitoring)
            <div class="space-y-0.5 mt-1">
                @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] font-bold text-gray-500 w-12 text-left shrink-0">{{ $swabLabel }}:</span>
                    <input type="time" name="swab_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $swabKey }}][mulai]"
                           value="{{ $st['mulai'] ?? '' }}"
                           @if($timeIsLockedByOther) readonly @endif
                           class="rounded border {{ $timeIsLockedByOther ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    <span class="text-gray-400 text-[10px]">–</span>
                    <input type="time" name="swab_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $swabKey }}][selesai]"
                           value="{{ $st['selesai'] ?? '' }}"
                           @if($timeIsLockedByOther) readonly @endif
                           class="rounded border {{ $timeIsLockedByOther ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                </div>
                @endforeach
            </div>
            @else
            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                <div>{{ $swabLabel }}: {{ ($st['mulai'] ?? '') ?: 'N/A' }} – {{ ($st['selesai'] ?? '') ?: 'N/A' }}</div>
                @endforeach
            </div>
            @endif
            @endif

            {{-- Dual A/B time slots (settle plate) --}}
            @if ($isDualAB)
            @if ($isEditable && $isMonitoring)
            <div class="space-y-0.5 mt-1">
                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                @php
                    $stAB = $secTimesFromEntries[$col][$ab] ?? [];
                    $abTimeLocked = is_array($timeLockedByOther[$col] ?? null)
                        ? ($timeLockedByOther[$col][$ab] ?? false)
                        : ($timeLockedByOther[$col] ?? false);
                @endphp
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] font-bold text-gray-500 w-3 text-left">{{ $abLabel }}:</span>
                    <input type="time" name="settle_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $ab }}][start_time]"
                           value="{{ $stAB['start_time'] ?? '' }}"
                           @if($abTimeLocked) readonly @endif
                           class="rounded border {{ $abTimeLocked ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    <span class="text-gray-400 text-[10px]">–</span>
                    <input type="time" name="settle_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $ab }}][end_time]"
                           value="{{ $stAB['end_time'] ?? '' }}"
                           @if($abTimeLocked) readonly @endif
                           class="rounded border {{ $abTimeLocked ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                </div>
                @endforeach
            </div>
            @else
            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                @php $stAB = $secTimesFromEntries[$col][$ab] ?? []; @endphp
                <div>{{ $abLabel }}: {{ ($stAB['start_time'] ?? '') ?: 'N/A' }} – {{ ($stAB['end_time'] ?? '') ?: 'N/A' }}</div>
                @endforeach
            </div>
            @endif
            @endif

            {{-- Single time slot per exposure column --}}
            @if ($hasTime)
            @php
                $expJamMulai   = $secTimesFromEntries[$col]['start_time'] ?? null;
                $expJamSelesai = $secTimesFromEntries[$col]['end_time'] ?? null;
            @endphp
            @if ($isEditable && $isMonitoring)
            <div class="space-y-0.5 mt-1">
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] text-gray-500 w-10 shrink-0">Mulai:</span>
                    <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][start_time]"
                           value="{{ $expJamMulai }}"
                           @if($timeIsLockedByOther) readonly @endif
                           class="rounded border {{ $timeIsLockedByOther ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                </div>
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] text-gray-500 w-10 shrink-0">Selesai:</span>
                    <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][end_time]"
                           value="{{ $expJamSelesai }}"
                           @if($timeIsLockedByOther) readonly @endif
                           class="rounded border {{ $timeIsLockedByOther ? 'border-gray-200 bg-gray-50 opacity-70 cursor-not-allowed' : 'border-sky-200 bg-white' }} px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                </div>
            </div>
            @else
            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                <div>Mulai: {{ $expJamMulai ?: 'N/A' }}</div>
                <div>Selesai: {{ $expJamSelesai ?: 'N/A' }}</div>
            </div>
            @endif
            @endif

        </th>
        @endfor
    </tr>

    {{-- Row 3: sub-column headers (B / F / T) ───────────────────────────── --}}
    <tr class="bg-sky-50/60 text-gray-500 border-b border-gray-200">
        @if ($hasMachineSetup)
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
