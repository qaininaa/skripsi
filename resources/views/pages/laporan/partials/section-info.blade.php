{{-- ── Section 1: Pemantauan Ruang ─────────────────────── --}}
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
            @if ($isEditable)
                @php
                    $monitoringInit = count($report->analyst_monitoring ?? []) > 0
                        ? array_map('strval', $report->analyst_monitoring)
                        : [''];
                @endphp
                <div x-data="{ items: {{ json_encode($monitoringInit) }} }">
                    <template x-for="(item, index) in items" :key="index">
                        <div class="flex items-center gap-1 mb-1.5">
                            <select name="analyst_monitoring[]"
                                x-model="items[index]"
                                class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                                <option value="">— Pilih Analis —</option>
                                @foreach ($analis as $a)
                                    <option value="{{ $a->id }}">{{ $a->name }}</option>
                                @endforeach
                            </select>
                            <button type="button"
                                x-show="items.length > 1"
                                @click="items.splice(index, 1)"
                                class="flex-shrink-0 w-6 h-6 flex items-center justify-center rounded-full text-red-400 hover:bg-red-50 hover:text-red-600 transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                    </template>
                    <button type="button"
                        @click="items.push('')"
                        class="mt-0.5 text-xs text-emerald-600 hover:text-emerald-700 font-medium flex items-center gap-1 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Analis
                    </button>
                </div>
            @else
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    @php
                        $monitoringNames = \App\Models\User::whereIn('id', $report->analyst_monitoring ?? [])->pluck('name');
                    @endphp
                    {{ $monitoringNames->isNotEmpty() ? $monitoringNames->join(', ') : '—' }}
                </div>
            @endif
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Dibaca Oleh</label>
            @if ($isEditable)
                @php
                    $readingVals = array_pad(array_map('strval', $report->analyst_reading ?? []), 2, '');
                @endphp
                @foreach ([0, 1] as $ri)
                    <div class="mb-1.5">
                        <select name="analyst_reading[]"
                            class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                            <option value="">— Pilih Analis —</option>
                            @foreach ($analis as $a)
                                <option value="{{ $a->id }}" {{ $readingVals[$ri] == $a->id ? 'selected' : '' }}>
                                    {{ $a->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endforeach
            @else
                <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                    @php
                        $readingNames = \App\Models\User::whereIn('id', $report->analyst_reading ?? [])->pluck('name');
                    @endphp
                    {{ $readingNames->isNotEmpty() ? $readingNames->join(', ') : '—' }}
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
