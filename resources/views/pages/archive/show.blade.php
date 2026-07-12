@include('pages.supervisor.reports-print', [
    'report' => $report,
    'entryMap' => $entryMap,
    'needsAirSampler' => $needsAirSampler,
    'needsInkubator' => $needsInkubator,
    'needsMedium' => $needsMedium,
    'backUrl' => $backUrl ?? route('report-archive.index'),
    'showPrint' => true,
    'autoPrint' => false,
])
