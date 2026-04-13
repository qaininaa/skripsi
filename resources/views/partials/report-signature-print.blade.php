@php
    // Per-section data passed from cetak view
    // $secMonIds, $secReadIds, $secMonTs, $secReadTs, $userMap, $supApproval, $mngrApproval
@endphp

<table class="dt sig-tbl" style="margin-top:10px">
    <tr>
        <td style="width:25%;font-weight:700">Dimonitoring oleh:</td>
        <td style="width:25%;font-weight:700">Dibaca oleh:</td>
        <td style="width:25%;font-weight:700">Direview oleh:</td>
        <td style="width:25%;font-weight:700">Disetujui oleh:</td>
    </tr>
    <tr>
        {{-- Dimonitoring --}}
        <td style="height:24mm;vertical-align:top;text-align:center">
            <div style="padding-top:4mm">
                @forelse ($secMonIds as $_uid)
                @php $_u = $userMap->get($_uid); $_ts = isset($secMonTs[$_uid]) ? \Illuminate\Support\Carbon::parse($secMonTs[$_uid]) : null; @endphp
                @if ($_u)
                @if ($_ts)
                <div style="font-size:16px;line-height:1">&#10003;</div>
                @endif
                <div style="font-weight:700">{{ $_u->name }}</div>
                @if ($_ts)
                <div style="font-size:10px">{{ $_ts->isoFormat('D MMM Y') }}</div>
                <div style="font-size:10px">{{ $_ts->isoFormat('HH:mm') }}</div>
                @endif
                @endif
                @empty
                @endforelse
            </div>
        </td>
        {{-- Dibaca --}}
        <td style="height:24mm;vertical-align:top;text-align:center">
            <div style="padding-top:4mm">
                @forelse ($secReadIds as $_uid)
                @php $_u = $userMap->get($_uid); $_ts = isset($secReadTs[$_uid]) ? \Illuminate\Support\Carbon::parse($secReadTs[$_uid]) : null; @endphp
                @if ($_u)
                @if ($_ts)
                <div style="font-size:16px;line-height:1">&#10003;</div>
                @endif
                <div style="font-weight:700">{{ $_u->name }}</div>
                @if ($_ts)
                <div style="font-size:10px">{{ $_ts->isoFormat('D MMM Y') }}</div>
                <div style="font-size:10px">{{ $_ts->isoFormat('HH:mm') }}</div>
                @endif
                @endif
                @empty
                @endforelse
            </div>
        </td>
        {{-- Direview --}}
        <td style="height:24mm;vertical-align:middle;text-align:center">
            <div>
                @if ($supApproval?->user)
                <div style="font-weight:700">{{ $supApproval->user->name }}</div>
                @if ($supApproval->signed_at)
                <div style="font-size:16px;line-height:1">&#10003;</div>
                <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($supApproval->signed_at)->isoFormat('D MMM Y') }}</div>
                <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($supApproval->signed_at)->isoFormat('HH:mm') }}</div>
                @endif
                @endif
            </div>
        </td>
        {{-- Disetujui --}}
        <td style="height:24mm;vertical-align:middle;text-align:center">
            <div>
                @if ($mngrApproval?->user)
                <div style="font-weight:700">{{ $mngrApproval->user->name }}</div>
                @if ($mngrApproval->signed_at)
                <div style="font-size:16px;line-height:1">&#10003;</div>
                <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($mngrApproval->signed_at)->isoFormat('D MMM Y') }}</div>
                <div style="font-size:10px">{{ \Illuminate\Support\Carbon::parse($mngrApproval->signed_at)->isoFormat('HH:mm') }}</div>
                @endif
                @endif
            </div>
        </td>
    </tr>
    <tr>
        <td>(Analis Lab. Mikrobiologi)</td>
        <td>(Analis Lab. Mikrobiologi)</td>
        <td>(Supervisor Mikrobiologi)</td>
        <td>(QC Manager)</td>
    </tr>
</table>
