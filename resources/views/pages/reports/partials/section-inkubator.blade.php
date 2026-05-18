{{-- ── Section 4: Proses Inkubasi Medium Monitoring ──────── --}}
{{-- Data dari tabel incubators + incubator_entries, dikonfigurasi via incubator_types --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">4. Proses Inkubasi Medium Monitoring</h3>
        @error('inkubator_incomplete')
        <p class="mt-1.5 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
            </svg>
            {{ $message }}
        </p>
        @enderror
    </div>
    @php
        $hasMediumSwab = $report->reportType->mediumTypes
            ->contains(fn ($m) => str_contains(strtolower($m->name), 'swab'));
        $mediumTypeLabels = array_merge(
            ['monitoring' => 'Medium Monitoring'],
            $hasMediumSwab ? ['swab' => 'Swab'] : []
        );
        $incubatorFieldLocks = $incubatorFieldLocks ?? [];
        $currentUserId = (string) (auth()->id() ?? '');
    @endphp
    @foreach ($incubatorTypes as $config)
    @php
        $ink         = $incubators[$config->id] ?? null;
        $inkEntries  = ($ink?->entries ?? collect())->keyBy('medium_type');
        $inkLabel    = $config->temperature_label;
        $inkMin      = $config->min_day;
        $configLocks = $incubatorFieldLocks[(string) $config->id] ?? [];
        $infoLocks   = $configLocks['info'] ?? [];

        $noIdLockedByOther = $isEditable
            && isset($infoLocks['no_id'])
            && (string) $infoLocks['no_id'] !== $currentUserId;

        $calibrationLockedByOther = $isEditable
            && isset($infoLocks['calibration_date'])
            && (string) $infoLocks['calibration_date'] !== $currentUserId;

        $dueDateLockedByOther = $isEditable
            && isset($infoLocks['due_date_calibration'])
            && (string) $infoLocks['due_date_calibration'] !== $currentUserId;
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
                  @if ($isEditable)
                <input type="text" name="incubator[{{ $config->id }}][no_id]" value="{{ $ink?->no_id ?? '' }}"
                      @if($noIdLockedByOther) readonly @endif
                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if($noIdLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
                  @if ($noIdLockedByOther)
                  <p class="mt-1 text-xs text-gray-400 italic">Terkunci karena sudah diisi analis lain.</p>
                  @endif
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $ink?->no_id ?? 'N/A' }}</div>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Kalibrasi Inkubator</label>
                  @if ($isEditable)
                <input type="date" name="incubator[{{ $config->id }}][calibration_date]" value="{{ $ink?->calibration_date?->format('Y-m-d') ?? '' }}"
                      @if($calibrationLockedByOther) readonly @endif
                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if($calibrationLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
                  @if ($calibrationLockedByOther)
                  <p class="mt-1 text-xs text-gray-400 italic">Terkunci karena sudah diisi analis lain.</p>
                  @endif
                @else
                <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                    {{ $ink?->calibration_date?->format('d/m/Y') ?? 'N/A' }}
                </div>
                @endif
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-500 mb-1">Tgl Due Date Kalibrasi Inkubator</label>
                  @if ($isEditable)
                <input type="date" name="incubator[{{ $config->id }}][due_date_calibration]" value="{{ $ink?->due_date_calibration?->format('Y-m-d') ?? '' }}"
                      @if($dueDateLockedByOther) readonly @endif
                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if($dueDateLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
                  @if ($dueDateLockedByOther)
                  <p class="mt-1 text-xs text-amber-600">Terkunci karena sudah diisi analis lain.</p>
                  @endif
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
            $entryLocks = $configLocks['entries'][$medType] ?? [];

            $inPairOwner = $entryLocks['date_in'] ?? $entryLocks['time_in'] ?? null;
            $outPairOwner = $entryLocks['date_out'] ?? $entryLocks['time_out'] ?? null;

            $inPairLockedByOther = $isEditable
                && $inPairOwner !== null
                && (string) $inPairOwner !== $currentUserId;

            $outPairLockedByOther = $isEditable
                && $outPairOwner !== null
                && (string) $outPairOwner !== $currentUserId;

            $incubatedByLockedByOther = $isEditable
                && isset($entryLocks['incubated_by'])
                && (string) $entryLocks['incubated_by'] !== $currentUserId;
            $dateInLockedByOther = $inPairLockedByOther || ($isEditable
                && isset($entryLocks['date_in'])
                && (string) $entryLocks['date_in'] !== $currentUserId);
            $timeInLockedByOther = $inPairLockedByOther || ($isEditable
                && isset($entryLocks['time_in'])
                && (string) $entryLocks['time_in'] !== $currentUserId);

            $removedByLockedByOther = $isEditable
                && isset($entryLocks['removed_by'])
                && (string) $entryLocks['removed_by'] !== $currentUserId;
            $dateOutLockedByOther = $outPairLockedByOther || ($isEditable
                && isset($entryLocks['date_out'])
                && (string) $entryLocks['date_out'] !== $currentUserId);
            $timeOutLockedByOther = $outPairLockedByOther || ($isEditable
                && isset($entryLocks['time_out'])
                && (string) $entryLocks['time_out'] !== $currentUserId);

            $showIncubatedBy = ! empty($entry?->incubated_by);
            $showRemovedBy = ! empty($entry?->removed_by);
            $existingIncubatedByName = $entry?->incubatedBy?->name ?? '';
            $existingRemovedByName = $entry?->removedBy?->name ?? '';
            $currentAnalystName = auth()->user()?->name ?? '';

            $dateInErrorKey = "incubator.{$config->id}.{$medType}.date_in";
            $timeInErrorKey = "incubator.{$config->id}.{$medType}.time_in";
            $dateOutErrorKey = "incubator.{$config->id}.{$medType}.date_out";
            $timeOutErrorKey = "incubator.{$config->id}.{$medType}.time_out";

            $dateInBorderClass = $errors->has($dateInErrorKey)
                ? 'border-red-400 ring-1 ring-red-400'
                : 'border-gray-200';
            $timeInBorderClass = $errors->has($timeInErrorKey)
                ? 'border-red-400 ring-1 ring-red-400'
                : 'border-gray-200';
            $dateOutBorderClass = $errors->has($dateOutErrorKey)
                ? 'border-red-400 ring-1 ring-red-400'
                : 'border-gray-200';
            $timeOutBorderClass = $errors->has($timeOutErrorKey)
                ? 'border-red-400 ring-1 ring-red-400'
                : 'border-gray-200';
        @endphp
        <div class="pt-3 border-t border-gray-50 space-y-3" data-incubator-entry-row>
            <p class="text-xs font-semibold text-sky-600">
                Tanggal Inkubasi {{ $medLabel }} (min {{ $inkMin }} hari)
                @if ($incubatedByLockedByOther || $dateInLockedByOther || $timeInLockedByOther || $removedByLockedByOther || $dateOutLockedByOther || $timeOutLockedByOther)
                <span class="ml-1 text-[10px] font-normal text-gray-400 normal-case">sebagian field terkunci analis lain</span>
                @endif
            </p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                {{-- Masuk / Diinkubasi --}}
                <div class="space-y-3">
                    @if ($isEditable)
                    <div data-incubator-owner="in">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Diinkubasi oleh</label>
                        <div
                            data-incubator-owner-name
                            data-existing-name="{{ $existingIncubatedByName }}"
                            data-current-name="{{ $currentAnalystName }}"
                            class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700"
                        >{{ $existingIncubatedByName ?: 'N/A' }}</div>
                    </div>
                    @else
                    <div data-incubator-owner="in">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Diinkubasi oleh</label>
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $entry?->incubatedBy?->name ?? 'N/A' }}</div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Masuk Inkubator</label>
                        @if ($isEditable)
                        <input type="date" name="incubator[{{ $config->id }}][{{ $medType }}][date_in]" value="{{ $entry?->date_in?->format('Y-m-d') ?? '' }}"
                               data-incubator-input="date_in"
                               @if($dateInLockedByOther) readonly @endif
                               class="block w-full rounded-lg border {{ $dateInBorderClass }} px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if($dateInLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
                        @if ($errors->has($dateInErrorKey))
                        <p class="mt-1 text-xs text-red-600">{{ $errors->first($dateInErrorKey) }}</p>
                        @endif
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                            {{ $entry?->date_in?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jam Masuk</label>
                        @if ($isEditable)
                        <input type="time" name="incubator[{{ $config->id }}][{{ $medType }}][time_in]" value="{{ $entry?->time_in ?? '' }}"
                               data-incubator-input="time_in"
                               @if($timeInLockedByOther) readonly @endif
                               class="block w-full rounded-lg border {{ $timeInBorderClass }} px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if($timeInLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
                              @if ($errors->has($timeInErrorKey))
                              <p class="mt-1 text-xs text-red-600">{{ $errors->first($timeInErrorKey) }}</p>
                              @endif
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $entry?->time_in ?? 'N/A' }}</div>
                        @endif
                    </div>
                </div>
                {{-- Keluar / Dikeluarkan --}}
                <div class="space-y-3">
                    @if ($isEditable)
                    <div data-incubator-owner="out">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dikeluarkan oleh</label>
                        <div
                            data-incubator-owner-name
                            data-existing-name="{{ $existingRemovedByName }}"
                            data-current-name="{{ $currentAnalystName }}"
                            class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700"
                        >{{ $existingRemovedByName ?: 'N/A' }}</div>
                    </div>
                    @else
                    <div data-incubator-owner="out">
                        <label class="block text-xs font-medium text-gray-500 mb-1">Dikeluarkan oleh</label>
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">{{ $entry?->removedBy?->name ?? 'N/A' }}</div>
                    </div>
                    @endif
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Keluar Inkubator</label>
                        @if ($isEditable)
                        <input type="date" name="incubator[{{ $config->id }}][{{ $medType }}][date_out]" value="{{ $entry?->date_out?->format('Y-m-d') ?? '' }}"
                               data-incubator-input="date_out"
                               @if($dateOutLockedByOther) readonly @endif
                               class="block w-full rounded-lg border {{ $dateOutBorderClass }} px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if($dateOutLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
                        @if ($errors->has($dateOutErrorKey))
                        <p class="mt-1 text-xs text-red-600">{{ $errors->first($dateOutErrorKey) }}</p>
                        @elseif ($dateOutLockedByOther)
                        @endif
                        @else
                        <div class="px-3 py-2 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-700">
                            {{ $entry?->date_out?->format('d/m/Y') ?? 'N/A' }}
                        </div>
                        @endif
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">Jam Keluar</label>
                        @if ($isEditable)
                        <input type="time" name="incubator[{{ $config->id }}][{{ $medType }}][time_out]" value="{{ $entry?->time_out ?? '' }}"
                               data-incubator-input="time_out"
                               @if($timeOutLockedByOther) readonly @endif
                               class="block w-full rounded-lg border {{ $timeOutBorderClass }} px-3 py-2 text-sm text-gray-700 focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none @if($timeOutLockedByOther) bg-gray-100 opacity-70 cursor-not-allowed @endif">
                              @if ($errors->has($timeOutErrorKey))
                              <p class="mt-1 text-xs text-red-600">{{ $errors->first($timeOutErrorKey) }}</p>
                              @elseif ($timeOutLockedByOther)
                        @endif
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
