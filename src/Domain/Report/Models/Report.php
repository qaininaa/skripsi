<?php

namespace Domain\Report\Models;

use Domain\ReportType\Models\ReportType;
use Domain\User\Models\User;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Aggregate root for the Report domain.
 *
 * Represents one work-in-progress laporan along with its normalised
 * relations (instruments, mediums, incubators, analysts, signatures).
 */
class Report extends Model
{
    use HasUuids;

    protected $fillable = [
        'report_type_id', 'product_name', 'batch_number',
        'status', 'created_by', 'locked_by', 'header_data',
        'printed_at', 'printed_by',
    ];

    protected $casts = [
        'header_data' => 'array',
        'printed_at' => 'datetime',
    ];

    public function reportType()
    {
        return $this->belongsTo(ReportType::class);
    }

    public function lockedByUser()
    {
        return $this->belongsTo(User::class, 'locked_by');
    }

    public function printedByUser()
    {
        return $this->belongsTo(User::class, 'printed_by');
    }

    public function environmentalEntries()
    {
        return $this->hasMany(ReportEnvironmentalEntry::class);
    }

    public function approvals()
    {
        return $this->hasMany(ReportApproval::class)->orderBy('step');
    }

    public function currentApprovalStep()
    {
        return $this->approvals()->where('status', 'pending')->orderBy('step')->first();
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Daftar medium (agar) yang digunakan dalam laporan ini.
     */
    public function mediumIdentities()
    {
        return $this->hasMany(MediumEntry::class);
    }

    /**
     * Daftar incubator yang digunakan dalam laporan ini.
     */
    public function incubators()
    {
        return $this->hasMany(Incubator::class);
    }

    /**
     * Daftar instrumen (Air Sampler) yang digunakan dalam laporan ini.
     */
    public function instrumentEntries()
    {
        return $this->hasMany(InstrumentIdentityEntry::class);
    }

    public function instrumentIdentities()
    {
        return $this->instrumentEntries();
    }

    /**
     * Daftar analis yang mengerjakan laporan ini (monitoring dan reading).
     */
    public function analysts()
    {
        return $this->hasMany(Analyst::class);
    }

    /**
     * Daftar TTD (tanda tangan) per section dalam laporan ini.
     */
    public function sectionSignatures()
    {
        return $this->hasMany(SectionSignature::class);
    }

    public function signatures()
    {
        return $this->sectionSignatures();
    }

    /**
     * Label kolom per section/instance/period (SP/Shift) untuk laporan ini.
     */
    public function sectionColumnNames()
    {
        return $this->hasMany(ReportSectionColumn::class)
            ->orderBy('section_id')
            ->orderBy('instance_number')
            ->orderBy('period_number');
    }

    /**
     * Catatan dan kesimpulan per section untuk laporan ini.
     */
    public function sectionNotes()
    {
        return $this->hasMany(ReportSectionNote::class)
            ->orderBy('section_id')
            ->orderBy('instance_number');
    }

    /**
     * Apply the snapshot saved at manager approval time. This prevents template
     * changes (new sections, renamed annex, etc.) from altering archived reports.
     *
     * No-op when no snapshot exists (backward compat) or relations not loaded.
     */
    public function applyReportTypeSnapshot(): void
    {
        $hd = $this->header_data ?? [];
        $snapshotRt = $hd['_snapshot_report_type'] ?? null;
        $snapshotIds = $hd['_snapshot_section_ids'] ?? null;

        if (! $this->relationLoaded('reportType') || ! $this->reportType) {
            return;
        }

        if ($snapshotRt) {
            $rt = $this->reportType;
            if (array_key_exists('annex_number', $snapshotRt)) {
                $rt->annex_number = $snapshotRt['annex_number'];
            }
            if (array_key_exists('name', $snapshotRt)) {
                $rt->name = $snapshotRt['name'];
            }
        }

        if ($snapshotIds !== null && $this->reportType->relationLoaded('sections')) {
            $filtered = $this->reportType->sections
                ->filter(fn ($s) => in_array($s->id, $snapshotIds))
                ->values();
            $this->reportType->setRelation('sections', $filtered);
        }
    }
}
