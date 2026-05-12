{{-- Pemantauan Personel --}}
{{--
    Variabel yang dibutuhkan (dari ReportViewService::buildViewData):
      $personnelMethods    Collection<PersonnelMethod> with activities, samplingPoints, limits
      $personnelInstances  Collection<PersonnelInstance> with rows.samplingEntries
      $isEditable          bool
      $report              Report model

    Struktur DB baru:
      personnel_instances   1 per (report, method, page_number)
      personnel_rows        1 per person dalam instance
      personnel_sampling_entries  1 per (row, sampling_point)
--}}
@php
    $restrictPersonnelTimeToExisting = $restrictPersonnelTimeToExisting ?? false;
@endphp
@if ($report->reportType->has_personnel && $personnelMethods->isNotEmpty())

<script>
/**
 * Cek apakah baris personel sudah cukup diisi untuk mengaktifkan kolom Hasil Pengamatan.
 * Syarat: nama personel dipilih + jam diisi + minimal 1 aktivitas dicentang + kelas dipilih.
 */
function checkPersonnelRowReady(personRowId) {
    const nameSelect   = document.querySelector(`select.personnel-name-select[data-person="${personRowId}"]`);
    const timeInput    = document.querySelector(`input.personnel-time-input[data-person="${personRowId}"]`);
    const actBoxes     = document.querySelectorAll(`input.personnel-act-checkbox[data-person="${personRowId}"]`);
    const classRadios  = document.querySelectorAll(`input[type="radio"].personnel-class-input[data-person="${personRowId}"]`);
    const classHidden  = document.querySelector(`input[type="hidden"].personnel-class-input[data-person="${personRowId}"]`);

    // Fase reading: tidak ada nameSelect/timeInput â†’ baris sudah ready dari server
    if (!nameSelect && !timeInput) {
        document.querySelectorAll(`input.personnel-cfu-input[data-person="${personRowId}"]`).forEach(inp => {
            inp.disabled = false;
            inp.classList.remove('bg-gray-50', 'cursor-not-allowed', 'text-gray-300');
        });
        return;
    }

    const hasName     = nameSelect   ? nameSelect.value !== '' : false;
    const hasTime     = timeInput    ? timeInput.value  !== '' : false;
    const hasActivity = [...actBoxes].some(cb => cb.checked);
    // Finger dab â†’ selalu kelas B (hidden), Cawan Kontak â†’ radio harus dipilih
    const hasClass    = classHidden !== null || [...classRadios].some(r => r.checked);

    const ready = hasName && hasTime && hasActivity && hasClass;

    document.querySelectorAll(`input.personnel-cfu-input[data-person="${personRowId}"]`).forEach(inp => {
        inp.disabled = !ready;
        inp.classList.toggle('bg-gray-50',        !ready);
        inp.classList.toggle('cursor-not-allowed', !ready);
        inp.classList.toggle('text-gray-300',      !ready);
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Kumpulkan semua unik personRowId dari CFU inputs
    const allRows = new Set(
        [...document.querySelectorAll('input.personnel-cfu-input')].map(el => el.dataset.person)
    );

    // Cek semua baris saat load
    allRows.forEach(id => checkPersonnelRowReady(id));

    // Pasang listener: nama
    document.querySelectorAll('select.personnel-name-select').forEach(el => {
        el.addEventListener('change', () => checkPersonnelRowReady(el.dataset.person));
    });
    // Pasang listener: jam
    document.querySelectorAll('input.personnel-time-input').forEach(el => {
        el.addEventListener('change', () => checkPersonnelRowReady(el.dataset.person));
    });
    // Pasang listener: aktivitas checkbox
    document.querySelectorAll('input.personnel-act-checkbox').forEach(el => {
        el.addEventListener('change', () => checkPersonnelRowReady(el.dataset.person));
    });
    // Pasang listener: kelas radio (Cawan Kontak)
    document.querySelectorAll('input[type="radio"].personnel-class-input').forEach(el => {
        el.addEventListener('change', () => checkPersonnelRowReady(el.dataset.person));
    });
});
</script>

{{-- Limits JSON untuk auto-kalkulasi kesimpulan (client-side) --}}
@php
    $allPersonnelUsers = \App\Domains\User\Models\User::where('role', 'analis')->orderBy('name')->get();
@endphp
<script>
window.personnelLimits = {
    @foreach ($personnelMethods as $method)
    @php
        $lims    = $method->limits->keyBy(fn ($l) => $l->class . '_' . $l->limit_type);
        $bAction = $lims->get('b_action');
        $cAction = $lims->get('c_action');
    @endphp
    "{{ $method->id }}": {
        "b": { "action": { "total": {{ $bAction?->cfu_total ?? 'null' }}, "fungi": {{ $bAction?->cfu_fungi ?? 'null' }} } },
        "c": { "action": { "total": {{ $cAction?->cfu_total ?? 'null' }}, "fungi": {{ $cAction?->cfu_fungi ?? 'null' }} } }
    }{{ $loop->last ? '' : ',' }}
    @endforeach
};
</script>

{{-- Container per halaman --}}
@php
    // Group instances: page_number â†’ [method_id => PersonnelInstance]
    $instancesByPage = $personnelInstances
        ->groupBy('page_number')
        ->sortKeys()
        ->map(fn ($grp) => $grp->keyBy('personnel_section_method_id'));

    // Format baru:
    // Halaman 1 = ringkas (2 baris/method), Halaman 2+ = detail (4 baris/method).
    // Selalu render minimal sampai halaman 2.
    $existingPageNums = $instancesByPage->keys()
        ->map(fn ($n) => (int) $n)
        ->filter(fn ($n) => $n > 0);
    $maxExistingPage = $existingPageNums->isNotEmpty() ? $existingPageNums->max() : 1;
    $totalPages = max(2, $maxExistingPage);
    $pagesToRender = collect(range(1, $totalPages));

    $isReadingPhase = $isReadingPhase ?? false;
    $canEditPersonnelTime = $canEditPersonnelTime ?? false;
    $canManagePersonnelPages = auth()->user()?->role === 'admin' && in_array($report->status, ['pending', 'monitoring']);
    $personnelActionRoute = $personnelActionRoute ?? null;
@endphp
@foreach ($pagesToRender as $pageNum)
@php
    $pageInstancesByMethod = $instancesByPage->get($pageNum, collect());
    $fallbackPageOneByMethod = $instancesByPage->get(1, collect());
    $personCount = $pageNum === 1 ? 2 : 4;
    $pageNoteSource = $pageInstancesByMethod
        ->first(fn ($inst) => ($inst->note ?? null) !== null || ($inst->deviation ?? null) !== null)
        ?? $pageInstancesByMethod->first();
    $pageNote = [
        'note' => $pageNoteSource?->note ?? '',
        'deviation' => $pageNoteSource?->deviation ?? '',
    ];
@endphp
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100 flex justify-between items-center">
        <h3 class="font-semibold text-sm text-gray-700">
            Pemantauan Personel - Halaman {{ $pageNum }} dari {{ $totalPages }}
        </h3>
        @if ($canManagePersonnelPages && $totalPages > 2 && $loop->last)
        <button type="submit" formnovalidate @if($personnelActionRoute) formaction="{{ $personnelActionRoute }}" formmethod="POST" @endif name="_personnel_action" value="remove_page_{{ $pageNum }}"
                class="text-xs text-red-400 hover:text-red-600 transition-colors">
            Hapus halaman ini
        </button>
        @endif
    </div>

    <div class="p-5 space-y-6 overflow-x-auto">
        @if ($pageNum === 1)
        <table class="w-full text-xs border border-gray-200 rounded-lg overflow-hidden">
            <thead class="bg-gray-50 text-gray-500">
                <tr>
                    <th rowspan="2" class="px-3 py-2 text-left border-b border-r border-gray-200 w-8">No</th>
                    <th rowspan="2" class="px-3 py-2 text-left border-b border-r border-gray-200">Metode</th>
                    <th rowspan="2" class="px-3 py-2 text-left border-b border-r border-gray-200">Spesifikasi</th>
                    <th colspan="2" class="px-3 py-1 text-center border-b border-r border-gray-200">Kelas B</th>
                    <th colspan="2" class="px-3 py-1 text-center border-b border-gray-200">Kelas C</th>
                </tr>
                <tr>
                    <th class="px-3 py-1 text-center border-b border-r border-gray-200 font-medium">T</th>
                    <th class="px-3 py-1 text-center border-b border-r border-gray-200 font-medium">F</th>
                    <th class="px-3 py-1 text-center border-b border-r border-gray-200 font-medium">T</th>
                    <th class="px-3 py-1 text-center border-b border-gray-200 font-medium">F</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                @foreach ($personnelMethods as $i => $method)
                @php
                    $limits      = $method->limits->keyBy(fn ($l) => $l->class . '_' . $l->limit_type);
                    $bAlert      = $limits->get('b_alert');
                    $bAction     = $limits->get('b_action');
                    $cAlert      = $limits->get('c_alert');
                    $cAction     = $limits->get('c_action');
                @endphp
                <tr class="border-b border-gray-100">
                    <td rowspan="2" class="px-3 py-2 border-r border-gray-100 text-center align-middle text-gray-500">{{ $i + 1 }}</td>
                    <td rowspan="2" class="px-3 py-2 border-r border-gray-100 align-middle font-medium text-gray-700">
                        {{ $method->method }}<br>
                        <span class="text-gray-400 font-normal">(CFU/plate)</span>
                    </td>
                    <td class="px-3 py-2 border-r border-gray-100 text-gray-600">Alert Limit</td>
                    <td class="px-3 py-2 border-r border-gray-100 text-center">{{ $bAlert ? ($bAlert->cfu_total == 1 ? '<1' : $bAlert->cfu_total) : '—' }}</td>
                    <td class="px-3 py-2 border-r border-gray-100 text-center">{{ $bAlert ? ($bAlert->cfu_fungi == 1 ? '<1' : $bAlert->cfu_fungi) : '—' }}</td>
                    <td class="px-3 py-2 border-r border-gray-100 text-center">{{ $cAlert ? ($cAlert->cfu_total == 1 ? '<1' : $cAlert->cfu_total) : '—' }}</td>
                    <td class="px-3 py-2 text-center">{{ $cAlert ? ($cAlert->cfu_fungi == 1 ? '<1' : $cAlert->cfu_fungi) : '—' }}</td>
                </tr>
                <tr>
                    <td class="px-3 py-2 border-r border-gray-100 text-gray-600">Action Limit</td>
                    <td class="px-3 py-2 border-r border-gray-100 text-center">{{ $bAction ? ($bAction->cfu_total == 1 ? '<1' : $bAction->cfu_total) : '—' }}</td>
                    <td class="px-3 py-2 border-r border-gray-100 text-center">{{ $bAction ? ($bAction->cfu_fungi == 1 ? '<1' : $bAction->cfu_fungi) : '—' }}</td>
                    <td class="px-3 py-2 border-r border-gray-100 text-center">{{ $cAction ? ($cAction->cfu_total == 1 ? '<1' : $cAction->cfu_total) : '—' }}</td>
                    <td class="px-3 py-2 text-center">{{ $cAction ? ($cAction->cfu_fungi == 1 ? '<1' : $cAction->cfu_fungi) : '—' }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif

        @foreach ($personnelMethods as $method)
        @php
            $isFingerDab = str_contains(strtolower($method->method), 'finger')
                        || str_contains(strtolower($method->method), 'dab');
            $instance    = $pageInstancesByMethod->get($method->id);
            $sourceInstance = $instance;

            // Backward-compat: jika halaman 2 belum ada, duplikasi tampilan dari halaman 1.
            if ($pageNum === 2 && ! $sourceInstance) {
                $sourceInstance = $fallbackPageOneByMethod->get($method->id);
            }

            $instId      = $instance?->id ?? ('_new_p' . $pageNum . '_m' . $method->id);
            $existingRows = $sourceInstance ? $sourceInstance->rows->keyBy('row_order') : collect();
            $points      = $method->samplingPoints;
            $pointCount  = $points->count();
        @endphp

        <table class="text-xs border-collapse" style="width:100%; table-layout:auto">
            <caption class="border border-gray-300 bg-gray-100 py-1.5 font-semibold text-sm text-gray-700 caption-top text-center">
                Metode {{ $method->method }}
            </caption>
            <thead>
                <tr class="bg-gray-50 text-gray-600">
                    <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center font-medium align-middle" style="width:150px">Nama Personel</th>
                    <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center font-medium align-middle" style="width:90px">Jam<br><span class="font-normal text-gray-400">(HH:mm)</span></th>
                    <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center font-medium align-middle">Aktivitas</th>
                    <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center font-medium align-middle" style="width:90px">Kelas</th>
                    <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center font-medium align-middle" style="width:120px">Titik Sampling</sup></th>
                    <th colspan="3" class="border border-gray-300 px-2 py-1.5 text-center font-medium">Hasil Pengamatan<br>(CFU/plate)</th>
                    <th rowspan="2" class="border border-gray-300 px-2 py-2 text-center font-medium align-middle" style="width:60px">Kesimpulan</th>
                </tr>
                <tr class="bg-gray-50 text-gray-600">
                    <th class="border border-gray-300 px-2 py-1.5 text-center font-medium" style="width:50px">B</th>
                    <th class="border border-gray-300 px-2 py-1.5 text-center font-medium" style="width:50px">F</th>
                    <th class="border border-gray-300 px-2 py-1.5 text-center font-medium" style="width:50px">T</th>
                </tr>
            </thead>
            <tbody>
                @for ($p = 0; $p < $personCount; $p++)
                @php
                    $row           = $existingRows->get($p);
                    $rowActivities = $row?->activities ?? [];
                    $rowCfu        = $row ? $row->samplingEntries->keyBy('sampling_point_id') : collect();
                    $personRowId   = $instId . '-' . $p;
                    $savedClass    = $row?->class ?? ($isFingerDab ? 'b' : '');
                    // Gate CFU berdasarkan fase & data monitoring
                    $rowHasMonData = $row !== null && $row->personnel_name !== null;
                    $cfuEditable   = $isEditable && ($isMonitoringPhase || ($isReadingPhase && $rowHasMonData));
                    $cfuNa         = $isReadingPhase && !$rowHasMonData;
                @endphp
                @foreach ($points as $pi => $point)
                @php
                    $se         = $rowCfu->get($point->id);
                    $pointRowId = $personRowId . '-' . $point->id;
                    $savedKesim = $se?->kesimpulan ?? '';
                    $kesimClass = match($savedKesim) {
                        'MS'  => 'text-xs font-semibold text-green-600',
                        'TMS' => 'text-xs font-semibold text-red-600',
                        default => 'text-xs text-gray-300',
                    };
                @endphp
                <tr>
                    @if ($pi === 0)
                    {{-- Nama Personel â€” dropdown user --}}
                    <td rowspan="{{ $pointCount }}" class="border border-gray-300 px-1 py-1 text-center align-middle">
                        @if ($isEditable && $isMonitoringPhase)
                        <select name="personnel[{{ $instId }}][row][{{ $p }}][name]"
                                data-person="{{ $personRowId }}"
                                class="personnel-name-select w-full text-xs rounded border border-gray-200 px-1 py-1 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none bg-white">
                            <option value="">-- Pilih --</option>
                            @foreach ($allPersonnelUsers as $u)
                            <option value="{{ $u->name }}" {{ ($row?->personnel_name ?? '') === $u->name ? 'selected' : '' }}>{{ $u->name }}</option>
                            @endforeach
                        </select>
                        @else
                        <span class="text-xs text-gray-700">{!! $row?->personnel_name ?? '<span class="font-medium text-gray-400">N/A</span>' !!}</span>
                        @endif
                    </td>

                    {{-- Jam Pemantauan --}}
                    <td rowspan="{{ $pointCount }}" class="border border-gray-300 px-1 py-1 text-center align-middle">
                        @if (($isEditable && $isMonitoringPhase) || ($canEditPersonnelTime && $row !== null && (!$restrictPersonnelTimeToExisting || filled($row?->monitoring_time))))
                        <input type="time"
                               name="personnel[{{ $instId }}][row][{{ $p }}][time]"
                               value="{{ $row?->monitoring_time ?? '' }}"
                               data-person="{{ $personRowId }}"
                               class="personnel-time-input w-full text-xs rounded border border-gray-200 px-1 py-1 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @else
                        <span class="text-xs text-gray-700">{!! $row?->monitoring_time ?? '<span class="font-medium text-gray-400">N/A</span>' !!}</span>
                        @endif
                    </td>

                    {{-- Aktivitas --}}
                    <td rowspan="{{ $pointCount }}" class="border border-gray-300 px-3 py-2 align-middle">
                        <div class="space-y-1.5">
                            @foreach ($method->activities as $act)
                            <label class="flex items-start gap-1.5 cursor-pointer text-xs text-gray-600">
                                <input type="checkbox"
                                       name="personnel[{{ $instId }}][row][{{ $p }}][activities][]"
                                       value="{{ $act->id }}"
                                       {{ in_array($act->id, $rowActivities) ? 'checked' : '' }}
                                       @if (!$isEditable || !$isMonitoringPhase) disabled @endif
                                       data-person="{{ $personRowId }}"
                                       class="personnel-act-checkbox mt-0.5 rounded text-sky-500 focus:ring-sky-400">
                                {{ $act->activity }}
                            </label>
                            @endforeach
                        </div>
                    </td>

                    {{-- Kelas: radio B/C (Cawan Kontak) atau tetap B (Finger Dab) --}}
                    <td rowspan="{{ $pointCount }}" class="border border-gray-300 px-2 py-1 text-center align-middle">
                        @if ($isFingerDab)
                            <span class="text-xs font-semibold text-gray-700">B</span>
                            <input type="hidden"
                                   name="personnel[{{ $instId }}][row][{{ $p }}][class]"
                                   value="b"
                                   class="personnel-class-input"
                                   data-person="{{ $personRowId }}"
                                   data-method="{{ $method->id }}">
                        @elseif ($isEditable && $isMonitoringPhase)
                            <div class="flex flex-col items-center gap-1.5">
                                @foreach (['b' => 'B', 'c' => 'C'] as $cls => $lbl)
                                <label class="flex items-center gap-1 cursor-pointer text-xs text-gray-700">
                                    <input type="radio"
                                           name="personnel[{{ $instId }}][row][{{ $p }}][class]"
                                           value="{{ $cls }}"
                                           {{ $savedClass === $cls ? 'checked' : '' }}
                                           class="personnel-class-input text-sky-500 focus:ring-sky-400"
                                           data-person="{{ $personRowId }}"
                                           data-method="{{ $method->id }}">
                                    {{ $lbl }}
                                </label>
                                @endforeach
                            </div>
                        @else
                            <span class="text-xs font-semibold uppercase text-gray-700">{!! $savedClass ?: "<span class='font-medium text-gray-400'>B/C</span>" !!}</span>
                            @if ($savedClass)
                            <input type="hidden"
                                   class="personnel-class-input"
                                   value="{{ $savedClass }}"
                                   data-person="{{ $personRowId }}"
                                   data-method="{{ $method->id }}">
                            @endif
                        @endif
                    </td>
                    @endif

                    {{-- Titik Sampling --}}
                    <td class="border border-gray-300 px-2 py-1.5 text-center text-gray-700 text-xs">{{ $point->sampling_point }}</td>

                    {{-- CFU B --}}
                    <td class="border border-gray-300 px-1 py-1 text-center">
                        @if ($cfuEditable)
                        <input type="text"
                               name="personnel[{{ $instId }}][row][{{ $p }}][cfu][{{ $point->id }}][b]"
                               value="{{ $se?->cfu_bacteria ?? '' }}"
                               data-prow="{{ $pointRowId }}"
                               data-ptype="b"
                               data-person="{{ $personRowId }}"
                               data-method="{{ $method->id }}"
                               placeholder="—"
                               class="personnel-cfu-input w-12 text-xs text-center rounded border border-gray-200 px-1 py-0.5 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @elseif ($cfuNa)
                        <span class="text-xs text-gray-400 italic">N/A</span>
                        @else
                        <span class="text-xs {{ $se?->cfu_bacteria !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">{{ $se?->cfu_bacteria ?? '—' }}</span>
                        @endif
                    </td>

                    {{-- CFU F --}}
                    <td class="border border-gray-300 px-1 py-1 text-center">
                        @if ($cfuEditable)
                        <input type="text"
                               name="personnel[{{ $instId }}][row][{{ $p }}][cfu][{{ $point->id }}][f]"
                               value="{{ $se?->cfu_fungi ?? '' }}"
                               data-prow="{{ $pointRowId }}"
                               data-ptype="f"
                               data-person="{{ $personRowId }}"
                               data-method="{{ $method->id }}"
                               placeholder="—"
                               class="personnel-cfu-input w-12 text-xs text-center rounded border border-gray-200 px-1 py-0.5 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">
                        @elseif ($cfuNa)
                        <span class="text-xs text-gray-400 italic">N/A</span>
                        @else
                        <span class="text-xs {{ $se?->cfu_fungi !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">{{ $se?->cfu_fungi ?? '—' }}</span>
                        @endif
                    </td>

                    {{-- T (auto-calc, display only) --}}
                    <td class="border border-gray-300 px-1 py-1 text-center">
                        @if ($cfuEditable)
                        <span id="pnt-{{ $pointRowId }}" class="{{ $se?->cfu_total ? 'text-xs font-semibold text-gray-700' : 'text-xs text-gray-300' }}">
                            {{ $se?->cfu_total ?? '—' }}
                        </span>
                        <input type="hidden"
                               name="personnel[{{ $instId }}][row][{{ $p }}][cfu][{{ $point->id }}][t]"
                               id="pnt-h-{{ $pointRowId }}"
                               value="{{ $se?->cfu_total ?? '' }}">
                        @elseif ($cfuNa)
                        <span class="text-xs text-gray-400 italic">N/A</span>
                        @else
                        <span class="text-xs {{ $se?->cfu_total !== null ? 'text-gray-700 font-medium' : 'text-gray-300' }}">{{ $se?->cfu_total ?? '—' }}</span>
                        @endif
                    </td>

                    {{-- Kesimpulan (auto-calc) --}}
                    <td class="border border-gray-300 px-1 py-1 text-center">
                        @if ($cfuEditable)
                        <span id="pkdisp-{{ $pointRowId }}" class="{{ $kesimClass }}">{{ $savedKesim ?: '—' }}</span>
                        <input type="hidden"
                               name="personnel[{{ $instId }}][row][{{ $p }}][cfu][{{ $point->id }}][kesimpulan]"
                               id="pkesimpulan-{{ $pointRowId }}"
                               value="{{ $savedKesim }}">
                        @elseif ($cfuNa)
                        <span class="text-xs text-gray-400 italic">N/A</span>
                        @else
                        <span class="{{ $kesimClass }}">{{ $savedKesim ?: '—' }}</span>
                        @endif
                    </td>
                </tr>
                @endforeach
                @endfor
            </tbody>
        </table>
        @endforeach

        {{-- Catatan & Deviasi (per halaman) --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-4 border-t border-gray-100">
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
                <textarea name="page_notes[{{ $pageNum }}][note]" rows="3"
                          @if (!$isEditable) readonly @endif
                          class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 resize-none focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">{{ $pageNote['note'] ?? '' }}</textarea>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Deviasi</label>
                <textarea name="page_notes[{{ $pageNum }}][deviation]" rows="3"
                          @if (!$isEditable) readonly @endif
                          class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 resize-none focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if(!$isEditable) bg-gray-50 @endif">{{ $pageNote['deviation'] ?? '' }}</textarea>
            </div>
        </div>

        {{-- Keterangan (statis) --}}
        <p class="text-[11px] text-gray-400 border-t border-gray-50 pt-3">
            <span class="font-medium text-gray-500">Keterangan:</span>
            B = Total Bakteri &nbsp;·&nbsp; F = Total Fungi &nbsp;·&nbsp; T = Total Bakteri + Fungi &nbsp;·&nbsp;
            MS = Memenuhi Spesifikasi &nbsp;·&nbsp; TMS = Tidak Memenuhi Spesifikasi &nbsp;·&nbsp;
            <sup>1)</sup>Pilih salah satu
        </p>
    </div>
</div>
@endforeach

{{-- Tombol Tambah Halaman --}}
@if ($canManagePersonnelPages)
<div class="mb-4">
    <button type="submit" formnovalidate @if($personnelActionRoute) formaction="{{ $personnelActionRoute }}" formmethod="POST" @endif name="_personnel_action" value="add_page"
            class="w-full py-3 rounded-xl border-2 border-dashed border-sky-200 text-sky-500 text-sm hover:border-sky-400 hover:text-sky-700 transition-colors">
        + Tambah Halaman Personel
    </button>
</div>
@endif

{{--  Tanda Tangan Personel --}}
@if ($report->reportType->has_personnel && $personnelMethods->isNotEmpty())

{{-- ... tabel personnel tetap sama ... --}}

{{-- TTD â€” selalu tampil, tidak perlu cek fase --}}
@php
    $monSigs = $personnelSignatures->get('monitoring', collect())->filter(fn ($sig) => $sig->user)->values();
    $readSigs = $personnelSignatures->get('reading', collect())->filter(fn ($sig) => $sig->user)->values();
    $personnelHasData = $monSigs->isNotEmpty() || $readSigs->isNotEmpty();
@endphp

<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-2">
        <h3 class="font-semibold text-sm text-gray-700">Tanda Tangan - Pemantauan Personel</h3>
        @if ($isEditable)
        <span class="text-xs text-amber-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M12 9v2m0 4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            </svg>
            Tanda tangan terisi saat Simpan & Selesaikan / Kirim, bukan saat Simpan Draft.
        </span>
        @endif
    </div>

    <div class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">

        {{-- Dimonitoring oleh --}}
        <div class="border border-gray-200 rounded-xl p-4 min-h-[170px] flex flex-col
            {{ $report->status === 'monitoring' && $isEditable ? 'ring-2 ring-sky-200 ring-offset-1' : '' }}">
            <p class="text-xs font-semibold text-gray-600 mb-3">
                Dimonitoring oleh:
                @if ($report->status === 'monitoring' && $isEditable)
                <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-sky-100 text-sky-700 rounded-full">fase aktif</span>
                @endif
            </p>
            <div class="flex-1 flex flex-col gap-3 justify-center text-center">
                @forelse ($monSigs as $monSig)
                <div>
                    <p class="text-sm font-semibold text-gray-700">{{ $monSig->user->name }}</p>
                    <div class="inline-flex items-center justify-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tersimpan
                    </div>
                    @if ($monSig->signed_at)
                    <p class="text-[11px] text-gray-500">
                        {{ $monSig->signed_at->isoFormat('D MMM Y, HH:mm') }}
                    </p>
                    @endif
                </div>
                @empty
                <div class="h-px w-16 border-b border-dashed border-gray-300 mx-auto"></div>
                @endforelse
            </div>
            <p class="text-[11px] text-gray-400 text-center mt-3">(Analis Lab. Mikrobiologi)</p>
        </div>

        {{-- Dibaca oleh --}}
        <div class="border border-gray-200 rounded-xl p-4 min-h-[170px] flex flex-col
            {{ $report->status === 'reading' && $isEditable ? 'ring-2 ring-sky-200 ring-offset-1' : '' }}">
            <p class="text-xs font-semibold text-gray-600 mb-3">
                Dibaca oleh:
                @if ($report->status === 'reading' && $isEditable)
                <span class="ml-1 px-1.5 py-0.5 text-[10px] bg-sky-100 text-sky-700 rounded-full">fase aktif</span>
                @endif
            </p>
            <div class="flex-1 flex flex-col gap-3 justify-center text-center">
                @forelse ($readSigs as $readSig)
                <div>
                    <p class="text-sm font-semibold text-gray-700">{{ $readSig->user->name }}</p>
                    <div class="inline-flex items-center justify-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tersimpan
                    </div>
                    @if ($readSig->signed_at)
                    <p class="text-[11px] text-gray-500">
                        {{ $readSig->signed_at->isoFormat('D MMM Y, HH:mm') }}
                    </p>
                    @endif
                </div>
                @empty
                <div class="h-px w-16 border-b border-dashed border-gray-300 mx-auto"></div>
                @endforelse
            </div>
            <p class="text-[11px] text-gray-400 text-center mt-3">(Analis Lab. Mikrobiologi)</p>
        </div>

        {{-- Direview oleh --}}
        <div class="border border-gray-200 rounded-xl p-4 min-h-[170px] flex flex-col">
            <p class="text-xs font-semibold text-gray-600 mb-3">Direview oleh:</p>
            <div class="flex-1 flex flex-col gap-3 justify-center text-center">
                @if ($personnelHasData && $supApproval?->user)
                    <p class="text-sm font-semibold text-gray-700">{{ $supApproval->user->name }}</p>
                    @if ($supApproval->signed_at)
                    <div class="inline-flex items-center justify-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Disetujui
                    </div>
                    <p class="text-[11px] text-gray-500">
                        {{ \Carbon\Carbon::parse($supApproval->signed_at)->isoFormat('D MMM Y, HH:mm') }}
                    </p>
                    @endif
                @else
                    <div class="h-px w-16 border-b border-dashed border-gray-300 mx-auto"></div>
                @endif
            </div>
            <p class="text-[11px] text-gray-400 text-center mt-3">(Supervisor Mikrobiologi)</p>
        </div>

        {{-- Disetujui oleh --}}
        <div class="border border-gray-200 rounded-xl p-4 min-h-[170px] flex flex-col">
            <p class="text-xs font-semibold text-gray-600 mb-3">Disetujui oleh:</p>
            <div class="flex-1 flex flex-col gap-3 justify-center text-center">
                @if ($personnelHasData && $mngrApproval?->user)
                    <p class="text-sm font-semibold text-gray-700">{{ $mngrApproval->user->name }}</p>
                    @if ($mngrApproval->signed_at)
                    <div class="inline-flex items-center justify-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Disetujui
                    </div>
                    <p class="text-[11px] text-gray-500">
                        {{ \Carbon\Carbon::parse($mngrApproval->signed_at)->isoFormat('D MMM Y, HH:mm') }}
                    </p>
                    @endif
                @else
                    <div class="h-px w-16 border-b border-dashed border-gray-300 mx-auto"></div>
                @endif
            </div>
            <p class="text-[11px] text-gray-400 text-center mt-3">(QC Manager)</p>
        </div>

    </div>
</div>

@endif {{-- end has_personnel --}}

@endif
