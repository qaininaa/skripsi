@php
    $sectionClass = $sectionClass ?? 'bg-white rounded-xl border border-gray-100 shadow-sm';
    $sectionMarginClass = $sectionMarginClass ?? '';
    $notice = $notice ?? null;

    $hd = $report->header_data ?? [];
    $hasShift2 = (bool) $report->shift2Analis;
    $shift1HandedOver = !empty($hd['shift1_handed_over']);

    $monitoringSignedAt = !empty($hd['ttd_monitoring_signed_at'])
        ? \Illuminate\Support\Carbon::parse($hd['ttd_monitoring_signed_at'])
        : null;
    $dibacaSignedAt = !empty($hd['ttd_dibaca_signed_at'])
        ? \Illuminate\Support\Carbon::parse($hd['ttd_dibaca_signed_at'])
        : null;

    $supervisorApproval = $report->approvals->firstWhere('step', 2);
    $managerApproval = $report->approvals->firstWhere('step', 3);

    $monitoringVisible = ! $hasShift2 || $shift1HandedOver || $monitoringSignedAt;
    $dibacaVisible = ! $hasShift2 || $dibacaSignedAt;

    $cards = [
        [
            'label' => 'Dimonitoring oleh:',
            'sub' => '(Analis Lab. Mikrobiologi)',
            'user' => $monitoringVisible ? $report->shift1Analis : null,
            'signed_at' => $monitoringSignedAt,
            'pending_text' => $hasShift2
                ? 'Akan muncul setelah Shift 1 estafet ke Shift 2.'
                : 'Otomatis saat laporan dikirim.',
        ],
        [
            'label' => 'Dibaca oleh:',
            'sub' => '(Analis Lab. Mikrobiologi)',
            'user' => ! $hasShift2
                ? $report->shift1Analis
                : ($dibacaVisible ? $report->shift2Analis : null),
            'signed_at' => $dibacaSignedAt,
            'pending_text' => $hasShift2
                ? 'Akan muncul saat Shift 2 mengirim laporan.'
                : 'Otomatis saat laporan dikirim.',
        ],
        [
            'label' => 'Direview oleh:',
            'sub' => '(Supervisor Mikrobiologi)',
            'user' => $supervisorApproval?->user,
            'signed_at' => $supervisorApproval?->signed_at,
            'pending_text' => 'Diisi otomatis saat Supervisor menyetujui.',
        ],
        [
            'label' => 'Disetujui oleh:',
            'sub' => '(QC Manager)',
            'user' => $managerApproval?->user,
            'signed_at' => $managerApproval?->signed_at,
            'pending_text' => 'Diisi otomatis saat Manajer menyetujui.',
        ],
    ];
@endphp

<div class="{{ $sectionClass }} {{ $sectionMarginClass }}">
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between gap-3">
        <h3 class="font-semibold text-sm text-gray-700">Tanda Tangan & Verifikasi</h3>
        @if ($notice)
        <span class="text-xs text-amber-600 font-medium flex items-center gap-1">
            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M12 2a10 10 0 100 20A10 10 0 0012 2z"/>
            </svg>
            {{ $notice }}
        </span>
        @endif
    </div>
    <div class="p-5 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
        @foreach ($cards as $card)
        <div class="border border-gray-200 rounded-xl p-4 min-h-[170px] flex flex-col">
            <p class="text-xs font-semibold text-gray-600">{{ $card['label'] }}</p>

            <div class="flex-1 flex flex-col items-center justify-center text-center py-4">
                @if ($card['user'])
                    <p class="text-sm font-semibold text-gray-700">{{ $card['user']->name }}</p>
                @else
                    <div class="h-px w-16 border-b border-dashed border-gray-300"></div>
                @endif

                @if ($card['signed_at'])
                    <div class="mt-3 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Tersimpan
                    </div>
                    <p class="mt-2 text-[11px] text-gray-500">{{ $card['signed_at']->isoFormat('D MMM Y') }}</p>
                    <p class="text-[11px] text-gray-500">{{ $card['signed_at']->isoFormat('HH:mm') }}</p>
                @endif
            </div>

            <p class="text-[11px] text-gray-400 text-center">{{ $card['sub'] }}</p>
        </div>
        @endforeach
    </div>
</div>
