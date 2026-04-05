{{-- ── Tanda Tangan & Verifikasi ───────────────────────── --}}
<div class="bg-white rounded-xl border border-gray-100 shadow-sm mb-4">
    <div class="px-5 py-3.5 border-b border-gray-100">
        <h3 class="font-semibold text-sm text-gray-700">Tanda Tangan & Verifikasi</h3>
    </div>
    <div class="p-5 grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach ([
            ['label' => 'Dimonitoring oleh:', 'sub' => '(Analis Lab. Mikrobiologi)', 'user' => $report->shift1Analis],
            ['label' => 'Dibaca oleh:', 'sub' => '(Analis Lab. Mikrobiologi)', 'user' => $report->shift2Analis],
            ['label' => 'Direview oleh:', 'sub' => '(Staff / Supervisor Mikrobiologi)', 'user' => null],
            ['label' => 'Disetujui oleh:', 'sub' => '(QC Manager)', 'user' => null],
        ] as $sig)
        <div class="border border-gray-200 rounded-xl p-4 min-h-[90px] flex flex-col">
            <p class="text-xs font-semibold text-gray-600">{{ $sig['label'] }}</p>
            <div class="flex-1 flex items-center justify-center py-2">
                @if ($sig['user'])
                    <p class="text-sm font-medium text-gray-700">{{ $sig['user']->name }}</p>
                @else
                    <div class="h-px w-16 border-b border-dashed border-gray-300"></div>
                @endif
            </div>
            <p class="text-[11px] text-gray-400 text-center">{{ $sig['sub'] }}</p>
        </div>
        @endforeach
    </div>
</div>
