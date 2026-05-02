{{-- Section 1: Pemantauan Ruang --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">1. Pemantauan Ruang</h3>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Pemantauan Ruang</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $report->created_at->isoFormat('D MMMM Y') }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dimonitoring Oleh</label>
            @if ($isEditable && $isMonitoringPhase)
                @php
                    $myId = auth()->id();
                    $myName = auth()->user()->name;
                    $monIds = $monitoringAnalysts->pluck('user_id')->toArray();
                    $otherMonIds = array_values(array_filter($monIds, fn($id) => $id != $myId));
                    $myInMon = in_array($myId, $monIds);
                @endphp
                {{-- Preserve existing analysts (other than me) as hidden inputs + read-only chips --}}
                @foreach($otherMonIds as $oid)
                    <input type="hidden" name="analyst_monitoring[]" value="{{ $oid }}">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-50 border border-emerald-100 text-sm text-emerald-700 mb-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ \App\Models\User::find($oid)?->name ?? 'N/A' }}
                    </div>
                @endforeach
                {{-- Current user is always auto-included during monitoring phase --}}
                <input type="hidden" name="analyst_monitoring[]" value="{{ $myId }}">
                <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 border border-sky-100 text-sm text-sky-700 mb-1.5">
                    <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $myName }} <span class="text-xs text-sky-500">(saya)</span>
                </div>
            @elseif ($isEditable && !$isMonitoringPhase)
                {{-- Read-only during reading phase: monitoring is already finalized --}}
                @php $monIds = $monitoringAnalysts->pluck('user_id')->toArray(); @endphp
                @foreach($monIds as $mid)
                    <input type="hidden" name="analyst_monitoring[]" value="{{ $mid }}">
                @endforeach
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    @php $monitoringNames = \App\Models\User::whereIn('id', $monIds)->pluck('name'); @endphp
                    {{ $monitoringNames->isNotEmpty() ? $monitoringNames->join(', ') : 'N/A' }}
                </div>
            @else
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    {{ $monitoringAnalysts->map->user->filter()->pluck('name')->join(', ') ?: 'N/A' }}
                </div>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dibaca Oleh</label>
            @if ($isEditable && !$isMonitoringPhase)
                @php
                    $myId = auth()->id();
                    $myName = auth()->user()->name;
                    $readIds = $readingAnalysts->pluck('user_id')->toArray();
                    $otherReadIds = array_values(array_filter($readIds, fn($id) => $id != $myId));
                    $myInRead = in_array($myId, $readIds);
                @endphp
                {{-- Preserve existing reading analysts (other than me) as hidden inputs + read-only chips --}}
                @foreach($otherReadIds as $oid)
                    <input type="hidden" name="analyst_reading[]" value="{{ $oid }}">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-indigo-50 border border-indigo-100 text-sm text-indigo-700 mb-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ \App\Models\User::find($oid)?->name ?? 'N/A' }}
                    </div>
                @endforeach
                {{-- Current user slot --}}
                @if($myInRead)
                    <input type="hidden" name="analyst_reading[]" value="{{ $myId }}">
                    <div class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-sky-50 border border-sky-100 text-sm text-sky-700 mb-1.5">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        {{ $myName }} <span class="text-xs text-sky-500">(saya)</span>
                    </div>
                @else
                    <select name="analyst_reading[]"
                        class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        <option value="" selected>- Tambahkan saya -</option>
                        <option value="{{ $myId }}">{{ $myName }}</option>
                    </select>
                @endif
            @elseif($isEditable && $isMonitoringPhase)
                {{-- Disabled during monitoring phase --}}
                @php $readIds = $readingAnalysts->pluck('user_id')->toArray(); @endphp
                @foreach($readIds as $rid)
                    <input type="hidden" name="analyst_reading[]" value="{{ $rid }}">
                @endforeach
                @if(!empty($readIds))
                    @php $existingReadNames = $readingAnalysts->map->user->filter()->pluck('name'); @endphp
                    <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                        {{ $existingReadNames->join(', ') ?: 'N/A' }}
                    </div>
                @else
                    <div class="px-3 py-2 rounded-lg bg-gray-100 border border-dashed border-gray-200 text-sm text-gray-400 italic">
                        Diisi setelah monitoring selesai
                    </div>
                @endif
            @else
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    {{ $readingAnalysts->map->user->filter()->pluck('name')->join(', ') ?: 'N/A' }}
                </div>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nama Produk</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $report->product_name }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Nomor Batch Produk</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $report->batch_number }}
            </div>
        </div>
    </div>
</div>
