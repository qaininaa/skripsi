{{-- ── Catatan & Kesimpulan Akhir ──────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">Catatan & Kesimpulan Akhir</h3>
    </div>
    <div class="p-5 space-y-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Catatan</label>
            <textarea name="header_data[notes]" rows="3"
                      @if(!$isEditable) readonly @endif
                      placeholder="Masukkan catatan pemantauan..."
                      class="block w-full rounded-lg border border-gray-200 px-3 py-2 text-sm text-gray-700 resize-none
                             focus:border-sky-400 focus:ring-1 focus:ring-sky-400 focus:outline-none
                             @if(!$isEditable) bg-gray-50 @endif">{{ $hd['notes'] ?? '' }}</textarea>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-2">Kesimpulan Akhir</label>
            @if ($isEditable)
            <div class="flex flex-wrap gap-3">
                <label class="flex items-center gap-2 cursor-pointer px-4 py-2.5 rounded-lg border-2 transition-colors
                              {{ ($hd['global_conclusion'] ?? '') === 'MS' ? 'border-green-400 bg-green-50' : 'border-gray-200 hover:border-green-200' }}">
                    <input type="radio" name="header_data[global_conclusion]" value="MS"
                           {{ ($hd['global_conclusion'] ?? '') === 'MS' ? 'checked' : '' }}
                           class="text-green-500 focus:ring-green-400">
                    <span class="text-sm font-medium text-green-700">Memenuhi Spesifikasi <span class="font-bold">(MS)</span></span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer px-4 py-2.5 rounded-lg border-2 transition-colors
                              {{ ($hd['global_conclusion'] ?? '') === 'TMS' ? 'border-red-400 bg-red-50' : 'border-gray-200 hover:border-red-200' }}">
                    <input type="radio" name="header_data[global_conclusion]" value="TMS"
                           {{ ($hd['global_conclusion'] ?? '') === 'TMS' ? 'checked' : '' }}
                           class="text-red-500 focus:ring-red-400">
                    <span class="text-sm font-medium text-red-700">Tidak Memenuhi Spesifikasi <span class="font-bold">(TMS)</span></span>
                </label>
            </div>
            @else
                @php $kg = $hd['global_conclusion'] ?? ''; @endphp
                <div class="px-4 py-2.5 rounded-lg border border-gray-100 bg-gray-50 inline-block text-sm">
                    @if ($kg === 'MS')
                        <span class="text-green-700 font-semibold">Memenuhi Spesifikasi (MS)</span>
                    @elseif ($kg === 'TMS')
                        <span class="text-red-700 font-semibold">Tidak Memenuhi Spesifikasi (TMS)</span>
                    @else
                        <span class="text-gray-400">Belum ditentukan</span>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
