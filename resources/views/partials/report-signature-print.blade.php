@php
    $hd = $report->header_data ?? [];

    // Get analysts from JSON arrays (use first entry)
    $monitoringUser = null;
    $readingUser = null;
    if (!empty($report->analyst_monitoring)) {
        $monitoringUser = \App\Models\User::find($report->analyst_monitoring[0]);
    }
    if (!empty($report->analyst_reading)) {
        $readingUser = \App\Models\User::find($report->analyst_reading[0]);
    }

    $monitoringSignedAt = !empty($hd['ttd_monitoring_signed_at'])
        ? \Illuminate\Support\Carbon::parse($hd['ttd_monitoring_signed_at'])
        : null;
    $dibacaSignedAt = !empty($hd['ttd_dibaca_signed_at'])
        ? \Illuminate\Support\Carbon::parse($hd['ttd_dibaca_signed_at'])
        : null;

    $supervisorApproval = $report->approvals->firstWhere('step', 2);
    $managerApproval = $report->approvals->firstWhere('step', 3);

    $cards = [
        [
            'label' => 'Dimonitoring oleh:',
            'sub' => '(Analis Lab. Mikrobiologi)',
            'user' => $monitoringUser,
            'signed_at' => $monitoringSignedAt,
        ],
        [
            'label' => 'Dibaca oleh:',
            'sub' => '(Analis Lab. Mikrobiologi)',
            'user' => $readingUser,
            'signed_at' => $dibacaSignedAt,
        ],
        [
            'label' => 'Direview oleh:',
            'sub' => '(Supervisor Mikrobiologi)',
            'user' => $supervisorApproval?->user,
            'signed_at' => $supervisorApproval?->signed_at,
        ],
        [
            'label' => 'Disetujui oleh:',
            'sub' => '(QC Manager)',
            'user' => $managerApproval?->user,
            'signed_at' => $managerApproval?->signed_at,
        ],
    ];
@endphp

<table class="dt sig-tbl" style="margin-top:10px">
    <tr>
        @foreach ($cards as $card)
        <td style="width:25%;font-weight:700">{{ $card['label'] }}</td>
        @endforeach
    </tr>
    <tr>
        @foreach ($cards as $card)
        <td style="height:24mm;vertical-align:top;text-align:center">
            <div style="padding-top:8mm">
                @if ($card['user'])
                    <div style="font-weight:700">{{ $card['user']->name }}</div>
                @endif
                @if ($card['signed_at'])
                    <div style="margin-top:3mm;font-size:16px;line-height:1">&#10003;</div>
                    <div style="margin-top:2mm;font-size:10px">{{ $card['signed_at']->isoFormat('D MMM Y') }}</div>
                    <div style="font-size:10px">{{ $card['signed_at']->isoFormat('HH:mm') }}</div>
                @endif
            </div>
        </td>
        @endforeach
    </tr>
    <tr>
        @foreach ($cards as $card)
        <td>{{ $card['sub'] }}</td>
        @endforeach
    </tr>
</table>
