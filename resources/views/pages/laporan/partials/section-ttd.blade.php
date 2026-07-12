@include('partials.report-signature-grid', [
    'report' => $report,
    'sectionMarginClass' => 'mb-4',
    'notice' => $isEditable ? 'Tanda tangan analis terisi otomatis.' : null,
    'onlySupervisor' => true,
])
