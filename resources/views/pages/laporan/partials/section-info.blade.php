{{-- ── Section 1: Pemantauan Ruang ─────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">1. Pemantauan Ruang</h3>
    </div>
    <div class="p-5 grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Tanggal Pemantauan Ruang</label>
            <div class="px-3 py-2 rounded-lg bg-gray-50 border border-gray-100 text-sm text-gray-700">
                {{ $report->report_date->isoFormat('D MMMM Y') }}
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Analis Shift 1</label>
            <div class="px-3 py-2 rounded-lg border text-sm flex items-center gap-2
                {{ $myShift === 1 ? 'bg-emerald-50 border-emerald-100 text-emerald-800 font-medium' : 'bg-gray-50 border-gray-100 text-gray-700' }}">
                {{ $report->shift1Analis->name }}
                <span class="inline-flex items-center justify-center h-5 w-12 rounded-full text-[11px] font-semibold bg-emerald-100 text-emerald-700 flex-shrink-0">
                    Shift 1
                </span>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-gray-500 mb-1">Analis Shift 2</label>
            <div class="px-3 py-2 rounded-lg border text-sm flex items-center gap-2
                {{ $myShift === 2 ? 'bg-indigo-50 border-indigo-100 text-indigo-800 font-medium' : 'bg-gray-50 border-gray-100 text-gray-700' }}">
                @if ($report->shift2Analis)
                    {{ $report->shift2Analis->name }}
                @else
                    <span class="text-gray-400 italic">Belum ditentukan</span>
                @endif
                <span class="inline-flex items-center justify-center h-5 w-12 rounded-full text-[11px] font-semibold bg-indigo-100 text-indigo-700 flex-shrink-0">
                    Shift 2
                </span>
            </div>
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
