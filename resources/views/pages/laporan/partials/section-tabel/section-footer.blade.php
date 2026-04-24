{{-- ── Section Footer: Catatan, Kesimpulan, Tanda Tangan ────────────────── --}}
{{-- Rendered only by instance === 1 (wraps all instances' conclusions).      --}}
{{-- Variables: $section, $instance, $hd, $sectionConclusion, $sectionNote   --}}
{{--             $sectionSignatures, $report, $isEditable, $totalInstances    --}}
{{-- $sectionConclusion and $sectionNote are provided by SectionTableComposer --}}

@if ($instance === 1)
<div class="px-5 py-4 border-t border-gray-100 space-y-3">
    {{-- Catatan --}}
    <div>
        <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
        @if ($isEditable)
        <textarea name="header_data[section_notes][{{ $section->id }}][notes]" rows="2"
                  placeholder="Catatan untuk seksi ini..."
                  class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 resize-none
                         focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none">{{ $sectionNote['notes'] ?? '' }}</textarea>
        @else
        <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700 min-h-[40px]">
            {{ $sectionNote['notes'] ?? 'N/A' }}
        </div>
        @endif
    </div>

    {{-- Kesimpulan --}}
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
@endif

{{-- ── Per-section Signature ───────────────────────────────────────────────── --}}
@php
    $_instSigs   = isset($sectionSignatures) ? $sectionSignatures->get("{$section->id}|{$instance}", collect()) : collect();
    $_monSigs    = $_instSigs->where('role', 'monitoring');
    $_readSigs   = $_instSigs->where('role', 'reading');
    $_supApproval  = $report->approvals->firstWhere('step', 2);
    $_mngrApproval = $report->approvals->firstWhere('step', 3);
@endphp
<div class="px-5 py-4 border-t border-gray-100">
    <p class="text-[11px] font-semibold text-gray-400 uppercase tracking-wide mb-3">
        Tanda Tangan & Verifikasi
        @if ($instance > 1)<span class="text-orange-500 normal-case font-normal">&nbsp;(Duplikat {{ $instance }})</span>@endif
    </p>
    <div class="grid grid-cols-2 xl:grid-cols-4 gap-3">

        {{-- Dimonitoring oleh --}}
        <div class="border border-gray-200 rounded-xl p-3 flex flex-col min-h-[110px]">
            <p class="text-[11px] font-semibold text-gray-600 mb-2">Dimonitoring oleh:</p>
            <div class="flex-1 flex flex-col gap-2 justify-center">
                @forelse ($_monSigs as $_sig)
                <div class="text-center">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Tersimpan
                    </span>
                    <p class="text-sm font-semibold text-gray-700">{{ $_sig->user->name }}</p>
                    @if ($_sig->signed_at)
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ \Illuminate\Support\Carbon::parse($_sig->signed_at)->isoFormat('D MMM Y, HH:mm') }}</p>
                    @endif
                </div>
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
                @forelse ($_readSigs as $_sig)
                <div class="text-center">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700 mt-0.5">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Tersimpan
                    </span>
                    <p class="text-sm font-semibold text-gray-700">{{ $_sig->user->name }}</p>
                    @if ($_sig->signed_at)
                    <p class="text-[11px] text-gray-500 mt-0.5">{{ \Illuminate\Support\Carbon::parse($_sig->signed_at)->isoFormat('D MMM Y, HH:mm') }}</p>
                    @endif
                </div>
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
