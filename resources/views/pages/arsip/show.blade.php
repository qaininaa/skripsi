@include('pages.supervisor.laporan-cetak', [
    'report' => $report,
    'entryMap' => $entryMap,
    'needsAirSampler' => $needsAirSampler,
    'needsInkubator' => $needsInkubator,
    'needsMedium' => $needsMedium,
    'backUrl' => $backUrl ?? route('arsip-laporan.index'),
    'showPrint' => true,
    'autoPrint' => true,
])
