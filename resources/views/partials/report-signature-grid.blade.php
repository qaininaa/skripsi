@php
    $sectionClass = $sectionClass ?? 'bg-white rounded-xl border border-gray-100 shadow-sm';
    $sectionMarginClass = $sectionMarginClass ?? '';
    $notice = $notice ?? null;
    $onlySupervisor = $onlySupervisor ?? false;  // when true, skip monitoring/reading columns

    // Ensure required relations are available in this shared partial.
    $report->loadMissing(['analysts.user', 'approvals.user', 'sectionSignatures']);

    $monitoringUsersSorted = $report->analysts->where('type', 'monitoring')->map->user->filter()->values();
    $readingUsersSorted    = $report->analysts->where('type', 'reading')->map->user->filter()->values();

    // Per-analyst timestamps from section signatures (latest per user + role).
    $monTimestamps = [];
    $readTimestamps = [];
    foreach ($report->sectionSignatures as $sig) {
        if (! $sig->user_id || ! $sig->signed_at) {
            continue;
        }

        $uid = (string) $sig->user_id;

        if ($sig->role === 'monitoring') {
            if (! isset($monTimestamps[$uid]) || $sig->signed_at->gt($monTimestamps[$uid])) {
                $monTimestamps[$uid] = $sig->signed_at;
            }
        }

        if ($sig->role === 'reading') {
            if (! isset($readTimestamps[$uid]) || $sig->signed_at->gt($readTimestamps[$uid])) {
                $readTimestamps[$uid] = $sig->signed_at;
            }
        }
    }

    $supervisorApproval = $report->approvals->firstWhere('step', 2);
    $managerApproval    = $report->approvals->firstWhere('step', 3);

    $cards = [
        [
            'label'   => 'Dimonitoring oleh:',
            'sub'     => '(Analis Lab. Mikrobiologi)',
            'entries' => $monitoringUsersSorted->map(fn($u) => [
                'user'      => $u,
                'signed_at' => $monTimestamps[(string) $u->id] ?? null,
            ]),
            'status_label' => 'Tersimpan',
        ],
        [
            'label'   => 'Dibaca oleh:',
            'sub'     => '(Analis Lab. Mikrobiologi)',
            'entries' => $readingUsersSorted->map(fn($u) => [
                'user'      => $u,
                'signed_at' => $readTimestamps[(string) $u->id] ?? null,
            ]),
            'status_label' => 'Tersimpan',
        ],
        [
            'label'   => 'Direview oleh:',
            'sub'     => '(Supervisor Mikrobiologi)',
            'entries' => collect($supervisorApproval?->user ? [[
                'user'      => $supervisorApproval->user,
                'signed_at' => $supervisorApproval->signed_at
                    ? \Illuminate\Support\Carbon::parse($supervisorApproval->signed_at) : null,
            ]] : []),
            'status_label' => 'Disetujui',
        ],
        [
            'label'   => 'Disetujui oleh:',
            'sub'     => '(QC Manager)',
            'entries' => collect($managerApproval?->user ? [[
                'user'      => $managerApproval->user,
                'signed_at' => $managerApproval->signed_at
                    ? \Illuminate\Support\Carbon::parse($managerApproval->signed_at) : null,
            ]] : []),
            'status_label' => 'Disetujui',
        ],
    ];

    if ($onlySupervisor) {
        $cards = array_slice($cards, 2); // keep only Direview + Disetujui
    }
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
            <p class="text-xs font-semibold text-gray-600 mb-3">{{ $card['label'] }}</p>

            <div class="flex-1 flex flex-col gap-3 justify-center">
                @forelse ($card['entries'] as $entry)
                <div class="text-center">
                    @if ($entry['signed_at'])
                        <div class="mb-1 inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-[11px] font-semibold text-emerald-700">
                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $card['status_label'] }}
                        </div>
                    @endif
                    <p class="text-sm font-semibold text-gray-700">{{ $entry['user']->name }}</p>
                    @if ($entry['signed_at'])
                        <p class="mt-1 text-[11px] text-gray-500">{{ $entry['signed_at']->isoFormat('D MMM Y, HH:mm') }}</p>
                    @endif
                </div>
                @empty
                <div class="text-center">
                    <div class="h-px w-16 border-b border-dashed border-gray-300 mx-auto"></div>
                </div>
                @endforelse
            </div>

            <p class="text-[11px] text-gray-400 text-center mt-3">{{ $card['sub'] }}</p>
        </div>
        @endforeach
    </div>
</div>
