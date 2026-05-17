<?php

namespace App\Services\Reports;

use App\Domains\Report\Services\IncubatorEntryService;
use App\Domains\Report\Services\InstrumentIdentityEntryService;
use App\Domains\Report\Services\MediumEntryService;
use App\Domains\Report\Models\Report;
use App\Domains\User\Models\User;
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
        'sectionNotes',
        'sectionSignatures.user',
    ];

    public function __construct(
        private IncubatorEntryService $incubatorEntryService,
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

        return array_merge(
            $this->buildSectionViewData($report),
            $this->buildIdentityAndEquipmentViewData($report),
            $this->buildAnalystAndApprovalViewData($report),
            [
                'isEditable' => $isEditable,
                'isMonitoringPhase' => $report->status === 'monitoring',
                'myShift' => 1,
            ]
        );
    }

    /**
     * Build data untuk blok section environment.
     */
    private function buildSectionViewData(Report $report): array
    {
        $entryMap = $this->sectionService->buildEntryMap($report);
        $sectionNeeds = $this->sectionService->computeSectionNeeds($report);
        $sectionInstances = $this->sectionService->buildSectionInstances($report);

        // Kelompokkan tanda tangan by compound key 'section_id|instance_number'.
        $sectionSignatures = $report->sectionSignatures->groupBy(
            fn ($sig) => $sig->section_id . '|' . $sig->instance_number
        );

        return [
            'entryMap' => $entryMap,
            'sectionInstances' => $sectionInstances,
            'sectionSignatures' => $sectionSignatures,
            'needsAirSampler' => $sectionNeeds['needsAirSampler'],
            'needsInkubator' => $sectionNeeds['needsInkubator'],
            'needsMedium' => $sectionNeeds['needsMedium'],
        ];
    }

    /**
     * Build data untuk identity instrument, medium, dan inkubator.
     */
    private function buildIdentityAndEquipmentViewData(Report $report): array
    {
        $instrument = $report->instrumentEntries->first();
        $instrumentFieldLocks = $this->instrumentIdentityEntryService->getFieldLocksForRowId($instrument?->id);

        $incubators = $report->incubators->keyBy('report_type_incubator_id');
        $incubatorFieldLocks = $this->incubatorEntryService->getFieldLocksByIncubatorConfigId($report->incubators);
        $incubatorTypes = $report->reportType->incubatorTypes;

        $mediums = $report->mediumIdentities->keyBy('name');
        $mediumFieldLocks = $this->mediumEntryService->getFieldLocksByMediumName($report->mediumIdentities);

        return [
            'instrument' => $instrument,
            'instrumentFieldLocks' => $instrumentFieldLocks,
            'incubators' => $incubators,
            'incubatorFieldLocks' => $incubatorFieldLocks,
            'incubatorTypes' => $incubatorTypes,
            'mediums' => $mediums,
            'mediumFieldLocks' => $mediumFieldLocks,
        ];
    }

    /**
     * Build data analis dan approval.
     */
    private function buildAnalystAndApprovalViewData(Report $report): array
    {
        $monitoringAnalysts = $report->analysts->where('type', 'monitoring');
        $readingAnalysts = $report->analysts->where('type', 'reading');

        $analis = User::where('role', 'analis')->orderBy('name')->get();
        $otherAnalis = $analis->where('id', '!=', auth()->id())->values();

        // Approval untuk supervisor & manager (sama dengan env section).
        $supApproval = $report->approvals->firstWhere('step', 2);
        $mngrApproval = $report->approvals->firstWhere('step', 3);

        return [
            'monitoringAnalysts' => $monitoringAnalysts,
            'readingAnalysts' => $readingAnalysts,
            'analis' => $analis,
            'otherAnalis' => $otherAnalis,
            'supApproval' => $supApproval,
            'mngrApproval' => $mngrApproval,
        ];
    }
}
