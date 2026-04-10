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
            <label class="block text-xs font-medium text-gray-500 mb-1">Analis Monitoring</label>
            @if ($isEditable)
                <select name="analyst_monitoring"
                    class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent">
                    <option value="">— Pilih Analis —</option>
                    @foreach ($analis as $a)
                        <option value="{{ $a->id }}" {{ in_array($a->id, $report->analyst_monitoring ?? []) ? 'selected' : '' }}>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
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
            <label class="block text-xs font-medium text-gray-500 mb-1">Analis Baca</label>
            @if ($isEditable)
                <select name="analyst_reading"
                    class="w-full px-3 py-2 rounded-lg bg-white border border-gray-200 text-sm text-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                    <option value="">— Pilih Analis —</option>
                    @foreach ($analis as $a)
                        <option value="{{ $a->id }}" {{ in_array($a->id, $report->analyst_reading ?? []) ? 'selected' : '' }}>
                            {{ $a->name }}
                        </option>
                    @endforeach
                </select>
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
