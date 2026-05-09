{{-- ── Tabel Pengukuran per Section ─────────────────────────────────────────── --}}
{{-- All computed variables ($cfuNum, $cfuTot, type-flags, freq-grouping,     --}}
{{-- $sectionConclusion, $sectionNote, $totalCols) are injected by             --}}
{{-- App\View\Composers\SectionTableComposer via AppServiceProvider.           --}}
{{-- $secNum is passed explicitly from isi.blade.php.                          --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4 overflow-hidden">

    {{-- ── Card header ─────────────────────────────────────────────────────── --}}
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center gap-3">
        <div class="h-7 w-7 rounded-lg bg-sky-50 flex items-center justify-center flex-shrink-0">
            <span class="text-xs font-bold text-sky-600">{{ $secNum }}</span>
        </div>
        <div>
            <h3 class="font-semibold text-sm text-gray-700">
                {{ $section->measurement_unit }}
                @if ($instance > 1)
                <span class="text-[10px] font-semibold px-1.5 py-0.5 rounded bg-orange-100 text-orange-600">Duplikat {{ $instance }}</span>
                @endif
            </h3>
            <p class="text-xs text-gray-400 mt-0.5">{{ $section->measurement_type }}</p>
        </div>

        {{-- Admin QC only: duplikat / hapus duplikat --}}
        @if ($canDuplicateSections ?? false)
        <div class="ml-auto flex items-center gap-2 shrink-0">
            @if ($instance === 1)
            <button type="button"
                    onclick="adminSectionAction('POST', '{{ route('report-assignment.sections.duplicate', [$report->id, $section->id]) }}')"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-emerald-200 bg-emerald-50 text-emerald-700 text-xs font-medium hover:bg-emerald-100 transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Duplikat Section
            </button>
            @endif
            @if ($instance > 1 && $instance === ($totalInstances ?? $instance))
            <button type="button"
                    onclick="if(confirm('Hapus duplikat section ini?')) adminSectionAction('DELETE', '{{ route('report-assignment.sections.remove', [$report->id, $section->id]) }}')"
                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border border-red-200 bg-red-50 text-red-600 text-xs font-medium hover:bg-red-100 transition">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                Hapus Duplikat
            </button>
            @endif
        </div>
        @endif
    </div>

    {{-- ── Measurement table ────────────────────────────────────────────────── --}}
    <div class="overflow-x-auto">
        <table class="w-full text-xs border-collapse"
               style="min-width: {{ 480 + ($hasMachineSetup ? 130 : 0) + ($maxCols * ($isSwabTime ? 220 : ($isDualAB ? 130 : ($isPerLocation ? 220 : 160)))) }}px">

            @include('pages.laporan.partials.section-tabel.thead')

            <tbody class="divide-y divide-gray-50">
                @php $_rowNum = 0; @endphp
                @foreach ($freqKeys as $_freqName)

                {{-- Frequency group header --}}
                @if ($showFreqHdr)
                <tr class="bg-sky-50 border-t border-sky-200">
                    <td colspan="{{ $totalCols }}" class="px-3 py-1.5 text-[10px] font-bold tracking-widest text-sky-700 uppercase">
                        FREKUENSI : {{ $_freqName === '__' ? 'Tidak Ditentukan' : strtoupper(\App\Models\ReportLocation::frequencyLabel($_freqName)) }}
                    </td>
                </tr>
                @endif

                {{-- Location rows --}}
                @foreach ($locsByFreq[$_freqName] as $loc)
                @php $_rowNum++; @endphp
                @include('pages.laporan.partials.section-tabel.tbody-row')
                @endforeach

                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Legend ───────────────────────────────────────────────────────────── --}}
    <div class="px-5 py-3 border-t border-gray-100 text-[11px] text-gray-400">
        @if ($isSwabTime)
        <p class="mb-1"><span class="text-gray-500">*)</span> diisi jika dibutuhkan</p>
        @endif
        <strong class="text-gray-500">Keterangan:</strong>
        B: Total Bakteri &nbsp;·&nbsp; F: Total Fungi &nbsp;·&nbsp; T: Total Bakteri + Fungi &nbsp;·&nbsp;
        MS: Memenuhi Spesifikasi &nbsp;·&nbsp; TMS: Tidak Memenuhi Spesifikasi
    </div>

    {{-- ── Catatan, Kesimpulan & Tanda Tangan ──────────────────────────────── --}}
    @include('pages.laporan.partials.section-tabel.section-footer')

</div>
