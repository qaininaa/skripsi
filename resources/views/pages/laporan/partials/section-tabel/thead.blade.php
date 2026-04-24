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
            $msJamMulai   = $hd['exposure_times'][$section->id][$instance][0]['start_time'] ?? null;
            $msJamSelesai = $hd['exposure_times'][$section->id][$instance][0]['end_time'] ?? null;
        @endphp
        <th class="px-2 py-2 text-center font-semibold border-r border-sky-100" colspan="3">
            <div class="whitespace-nowrap text-xs font-semibold text-gray-700 mb-1">Machine Set-up</div>
            @if ($isEditable && !$ms0Locked && $isMonitoring)
            <div class="flex justify-center items-center gap-1">
                <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][0][start_time]"
                       value="{{ $msJamMulai }}"
                       class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                <span class="text-gray-400 text-[10px] font-normal">–</span>
                <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][0][end_time]"
                       value="{{ $msJamSelesai }}"
                       class="rounded border border-sky-200 bg-white px-1 py-0.5 text-[10px] font-normal text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
            </div>
            @elseif ($ms0Locked)
            <div class="flex justify-center items-center gap-1">
                <input type="time" value="{{ $msJamMulai }}" disabled
                       class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-normal text-gray-400 cursor-not-allowed">
                <span class="text-gray-400 text-[10px] font-normal">–</span>
                <input type="time" value="{{ $msJamSelesai }}" disabled
                       class="rounded border border-gray-200 bg-gray-100 px-1 py-0.5 text-[10px] font-normal text-gray-400 cursor-not-allowed">
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
        @php
            $colSettlOwner  = isset($hdOwners["settle_times_{$section->id}_{$instance}_{$col}"])
                                ? (string) $hdOwners["settle_times_{$section->id}_{$instance}_{$col}"]
                                : null;
            $colSettlLocked = $isEditable && $colSettlOwner !== null && $colSettlOwner !== (string) auth()->id();

            $colSwabOwner  = isset($hdOwners["swab_times_{$section->id}_{$instance}_{$col}"])
                               ? (string) $hdOwners["swab_times_{$section->id}_{$instance}_{$col}"]
                               : null;
            $colSwabLocked = $isEditable && $colSwabOwner !== null && $colSwabOwner !== (string) auth()->id();

            $colExpOwner  = isset($hdOwners["exposure_times_{$section->id}_{$instance}_{$col}"])
                              ? (string) $hdOwners["exposure_times_{$section->id}_{$instance}_{$col}"]
                              : null;
            $colExpLocked = $isEditable && $colExpOwner !== null && $colExpOwner !== (string) auth()->id();
        @endphp
        <th class="px-2 py-1.5 text-center font-semibold border-r border-sky-100 whitespace-nowrap"
            colspan="{{ $subColsPerExp }}">
            {{ $colLabel }} {{ $maxCols > 1 ? ($romanNums[$col - 1] ?? $col) : '' }}

            {{-- Swab time slots --}}
            @if ($isSwabTime)
            @php $swabColTimes = $hd['swab_times'][$section->id][$instance][$col] ?? []; @endphp
            @if ($isEditable && !$colSwabLocked && $isMonitoring)
            <div class="space-y-0.5 mt-1">
                @foreach (['s1' => 'S1', 's1_2' => '*) S1-2', 's1_3' => '*) S1-3'] as $swabKey => $swabLabel)
                @php $st = $swabColTimes[$swabKey] ?? []; @endphp
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] font-bold text-gray-500 w-12 text-left shrink-0">{{ $swabLabel }}:</span>
                    <input type="time" name="swab_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $swabKey }}][mulai]"
                           value="{{ $st['mulai'] ?? '' }}"
                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    <span class="text-gray-400 text-[10px]">–</span>
                    <input type="time" name="swab_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $swabKey }}][selesai]"
                           value="{{ $st['selesai'] ?? '' }}"
                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
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
            @if ($isEditable && !$colSettlLocked && $isMonitoring)
            <div class="space-y-0.5 mt-1">
                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                @php $stAB = $hd['settle_times'][$section->id][$instance][$col][$ab] ?? []; @endphp
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] font-bold text-gray-500 w-3 text-left">{{ $abLabel }}:</span>
                    <input type="time" name="settle_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $ab }}][start_time]"
                           value="{{ $stAB['start_time'] ?? '' }}"
                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                    <span class="text-gray-400 text-[10px]">–</span>
                    <input type="time" name="settle_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][{{ $ab }}][end_time]"
                           value="{{ $stAB['end_time'] ?? '' }}"
                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                </div>
                @endforeach
            </div>
            @else
            <div class="text-[10px] text-gray-500 space-y-0.5 mt-1">
                @foreach (['a' => 'A', 'b' => 'B'] as $ab => $abLabel)
                @php $stAB = $hd['settle_times'][$section->id][$instance][$col][$ab] ?? []; @endphp
                <div>{{ $abLabel }}: {{ ($stAB['start_time'] ?? '') ?: 'N/A' }} – {{ ($stAB['end_time'] ?? '') ?: 'N/A' }}</div>
                @endforeach
            </div>
            @endif
            @endif

            {{-- Single time slot per exposure column --}}
            @if ($hasTime)
            @php
                $expJam        = $hd['exposure_times'][$section->id][$instance][$col] ?? [];
                $expJamMulai   = $expJam['start_time'] ?? null;
                $expJamSelesai = $expJam['end_time'] ?? null;
            @endphp
            @if ($isEditable && !$colExpLocked && $isMonitoring)
            <div class="space-y-0.5 mt-1">
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] text-gray-500 w-10 shrink-0">Mulai:</span>
                    <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][start_time]"
                           value="{{ $expJamMulai }}"
                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                </div>
                <div class="flex items-center justify-center gap-0.5">
                    <span class="text-[9px] text-gray-500 w-10 shrink-0">Selesai:</span>
                    <input type="time" name="exposure_times[{{ $section->id }}][{{ $instance }}][{{ $col }}][end_time]"
                           value="{{ $expJamSelesai }}"
                           class="rounded border border-sky-200 bg-white px-1 py-0 text-[10px] text-gray-600 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
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
