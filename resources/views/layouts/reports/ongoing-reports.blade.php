@php
    $tabs = $tabs ?? [
        'all' => ['label' => 'Semua'],
        'pending' => ['label' => 'Belum Dikerjakan'],
        'monitoring' => ['label' => 'Monitoring'],
        'reading' => ['label' => 'Pembacaan'],
        'review_supervisor' => ['label' => 'Direview Supervisor'],
        'waiting_manager' => ['label' => 'Menunggu Persetujuan Manajer'],
    ];

    $accent = $accent ?? 'emerald';
    $title = $title ?? 'Laporan Sedang Dikerjakan';
    $description = $description ?? 'Daftar laporan yang sedang diproses oleh analis.';
    $tabRouteName = $tabRouteName ?? '';
    $previewRouteName = $previewRouteName ?? '';

    $tabActiveClass = $accent === 'blue'
        ? 'border-blue-500 text-blue-600'
        : 'border-emerald-500 text-emerald-600';
    $tabBadgeActiveClass = $accent === 'blue'
        ? 'bg-blue-100 text-blue-700'
        : 'bg-emerald-100 text-emerald-700';
    $previewButtonClass = $accent === 'blue'
        ? 'bg-blue-50 text-blue-700 hover:bg-blue-100'
        : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100';
@endphp

<div class="space-y-5">
    <div>
        <h2 class="text-lg font-semibold text-gray-800">{{ $title }}</h2>
        <p class="text-sm text-gray-500 mt-0.5">{{ $description }}</p>
    </div>

    <div class="bg-white rounded-xl border border-gray-100 shadow-sm">
        <div class="flex overflow-x-auto border-b border-gray-100 scrollbar-none">
            @foreach ($tabs as $key => $tabDef)
                @php
                    $isActive = $status === $key;
                    $count = $counts[$key] ?? 0;
                @endphp
                <a href="{{ route($tabRouteName, ['status' => $key]) }}"
                   class="flex items-center gap-2 px-4 py-3.5 text-sm font-medium whitespace-nowrap border-b-2 transition-colors
                          {{ $isActive ? $tabActiveClass : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-200' }}">
                    {{ $tabDef['label'] }}
                    @if ($count > 0)
                        <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 rounded-full text-xs font-semibold
                                     {{ $isActive ? $tabBadgeActiveClass : 'bg-gray-100 text-gray-600' }}">
                            {{ $count }}
                        </span>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="overflow-x-auto">
            @if ($reports->isEmpty())
                <div class="flex flex-col items-center justify-center py-14 text-center">
                    <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="text-sm font-medium text-gray-400">Tidak ada laporan yang sedang dikerjakan.</p>
                </div>
            @else
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-gray-100">
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tanggal</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nama Produk</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Nomor Batch</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tahap</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Sedang Dikerjakan Oleh</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Tim Analis</th>
                            <th class="px-5 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wider">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        @foreach ($reports as $report)
                            @php
                                $monitoringNames = $report->analysts
                                    ->where('type', 'monitoring')
                                    ->map(fn ($analyst) => optional($analyst->user)->name)
                                    ->filter()
                                    ->unique()
                                    ->values();
                                $readingNames = $report->analysts
                                    ->where('type', 'reading')
                                    ->map(fn ($analyst) => optional($analyst->user)->name)
                                    ->filter()
                                    ->unique()
                                    ->values();

                                $teamParts = [];
                                if ($monitoringNames->isNotEmpty()) {
                                    $teamParts[] = 'Monitoring: '.$monitoringNames->join(', ');
                                }
                                if ($readingNames->isNotEmpty()) {
                                    $teamParts[] = 'Pembacaan: '.$readingNames->join(', ');
                                }

                                $pendingSupervisorApproval = $report->approvals
                                    ->first(fn ($approval) => (int) $approval->step === 2 && $approval->status === 'pending');
                                $pendingManagerApproval = $report->approvals
                                    ->first(fn ($approval) => (int) $approval->step === 3 && $approval->status === 'pending');

                                if ($report->status === 'pending') {
                                    $activeWorker = '-';
                                } elseif (in_array($report->status, ['monitoring', 'reading'], true)) {
                                    $activeWorker = optional($report->lockedByUser)->name ?: '-';
                                } elseif ($pendingSupervisorApproval) {
                                    $activeWorker = optional($pendingSupervisorApproval->user)->name ?: 'Menunggu penetapan supervisor';
                                } elseif ($pendingManagerApproval) {
                                    $activeWorker = optional($pendingManagerApproval->user)->name ?: 'Menunggu penetapan manajer';
                                } else {
                                    $activeWorker = '-';
                                }
                            @endphp
                            <tr class="hover:bg-gray-50 transition-colors">
                                <td class="px-5 py-3.5 text-gray-700 whitespace-nowrap">
                                    {{ $report->created_at->isoFormat('D MMM Y') }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ $report->product_name }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ $report->batch_number ?: '-' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    @if ($report->status === 'pending')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            Belum Dikerjakan
                                        </span>
                                    @elseif ($report->status === 'monitoring')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-amber-100 text-amber-700">
                                            Monitoring
                                        </span>
                                    @elseif ($report->status === 'reading')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-indigo-100 text-indigo-700">
                                            Pembacaan
                                        </span>
                                    @elseif ($pendingSupervisorApproval)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-sky-100 text-sky-700">
                                            Direview Supervisor
                                        </span>
                                    @elseif ($pendingManagerApproval)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-100 text-blue-700">
                                            Menunggu Persetujuan Manajer
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-100 text-gray-700">
                                            {{ $report->status }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ $activeWorker }}
                                </td>
                                <td class="px-5 py-3.5 text-gray-700">
                                    {{ ! empty($teamParts) ? implode(' | ', $teamParts) : '-' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <a href="{{ route($previewRouteName, $report->id) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg {{ $previewButtonClass }} text-xs font-medium transition-colors">
                                        Lihat
                                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @if ($reports->hasPages())
                    <div class="px-5 py-4 border-t border-gray-100">
                        {{ $reports->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>
