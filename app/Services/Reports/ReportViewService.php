<?php

namespace App\Services\Reports;

use App\Domains\ReportEntry\InstrumentIdentityEntry\Services\InstrumentIdentityEntryService;
use App\Domains\ReportEntry\MediumEntry\Services\MediumEntryService;
use App\Models\Report;
use App\Models\User;
use App\Services\ReportSectionService;
use App\Services\SectionInstanceService;

/**
 * ReportViewService
 *
 * Menyiapkan data tampilan (view data) untuk halaman isi laporan.
 * Menghilangkan duplikasi kode antara isi() dan lihat() di controller.
 */
class ReportViewService
{
    /**
     * Relasi yang di-eager-load untuk setiap halaman tampilan laporan.
     */
    public const RELATIONS = [
        'reportType.sections.locations.room',
        'reportType.incubatorTypes',
        'reportType.mediumTypes',
        'environmentalEntries',
        'approvals.user',
        'lockedByUser',
        'instrumentEntries',
        'mediumIdentities',
        'incubators.entries.incubatedBy',
        'incubators.entries.removedBy',
        'analysts.user',
        'sectionColumnNames',
        'sectionSignatures.user',
        'reportType.personnelMethods.activities',
        'reportType.personnelMethods.samplingPoints',
        'reportType.personnelMethods.limits',
        'personnelInstances.rows.samplingEntries',
    ];

    public function __construct(
        private InstrumentIdentityEntryService $instrumentIdentityEntryService,
        private MediumEntryService $mediumEntryService,
        private ReportSectionService $sectionService,
        private SectionInstanceService $instanceService,
    ) {}

    /**
     * Muat semua relasi yang dibutuhkan halaman tampilan laporan.
     */
    public function loadRelations(Report $report): void
    {
        $report->load(self::RELATIONS);
    }

    /**
     * Bangun array data view yang digunakan oleh blade isi.blade.php.
     *
     * @param  Report $report      Laporan yang sudah di-load relasinya
     * @param  bool   $isEditable  true = mode edit (analis pemegang kunci), false = read-only
     * @return array               Semua variabel view yang siap di-compact / di-merge
     */
    public function buildViewData(Report $report, bool $isEditable): array
    {
        // Pastikan setiap section punya minimal 1 instance original.
        $this->instanceService->ensureInstancesInitialized($report);

        $entryMap        = $this->sectionService->buildEntryMap($report);
        $sectionNeeds    = $this->sectionService->computeSectionNeeds($report);
        $sectionInstances = $this->sectionService->buildSectionInstances($report);

        // Kelompokkan tanda tangan by compound key 'section_id|instance_number'.
        $sectionSignatures = $report->sectionSignatures->groupBy(
            fn ($sig) => $sig->section_id . '|' . $sig->instance_number
        );

        $instrument       = $report->instrumentEntries->first();
        $instrumentFieldLocks = $this->instrumentIdentityEntryService->getFieldLocksForRowId($instrument?->id);
        $incubators       = $report->incubators->keyBy('report_type_incubator_id');
        $incubatorTypes = $report->reportType->incubatorTypes;
        $mediums          = $report->mediumIdentities->keyBy('name');
        $mediumFieldLocks = $this->mediumEntryService->getFieldLocksByMediumName($report->mediumIdentities);

        $personnelMethods    = $report->reportType->personnelMethods;
        $personnelInstances  = $report->personnelInstances;

        $monitoringAnalysts = $report->analysts->where('type', 'monitoring');
        $readingAnalysts    = $report->analysts->where('type', 'reading');

        $analis      = User::where('role', 'analis')->orderBy('name')->get();
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();

        $personnelSignatures = $report->personnelSignatures()
            ->with('user')
            ->orderBy('signed_at')
            ->orderBy('created_at')
            ->get()
            ->groupBy('role'); // ['monitoring' => Collection<Signature>, 'reading' => Collection<Signature>]

        // Approval untuk supervisor & manager (sama dengan env section)
        $supApproval  = $report->approvals->firstWhere('step', 2);
        $mngrApproval = $report->approvals->firstWhere('step', 3);

        return [
            'entryMap'          => $entryMap,
            'sectionInstances'  => $sectionInstances,
            'sectionSignatures' => $sectionSignatures,
            'needsAirSampler'   => $sectionNeeds['needsAirSampler'],
            'needsInkubator'    => $sectionNeeds['needsInkubator'],
            'needsMedium'       => $sectionNeeds['needsMedium'],
            'isEditable'        => $isEditable,
            'isMonitoringPhase' => $report->status === 'monitoring',
            'myShift'           => 1,
            'instrument'        => $instrument,
            'instrumentFieldLocks' => $instrumentFieldLocks,
            'incubators'        => $incubators,
            'incubatorTypes'  => $incubatorTypes,
            'mediums'           => $mediums,
            'mediumFieldLocks'  => $mediumFieldLocks,
            'monitoringAnalysts' => $monitoringAnalysts,
            'readingAnalysts'   => $readingAnalysts,
            'analis'            => $analis,
            'otherAnalis'       => $otherAnalis,
            'personnelMethods'   => $personnelMethods,
            'personnelInstances' => $personnelInstances,
            'personnelSignatures' => $personnelSignatures,
            'supApproval'       => $supApproval,
            'mngrApproval'      => $mngrApproval,
        ];
    }
}
