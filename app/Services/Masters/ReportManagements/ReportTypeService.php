<?php

namespace App\Services\Masters\ReportManagements;

use App\Models\AuditLog;
use App\Models\ReportType;
use App\Models\ReportTypeIncubator;
use App\Models\ReportTypeMedium;
use Illuminate\Http\Request;

class ReportTypeService
{
    public function create(array $validated, array $meta): ReportType
    {
        $reportType = ReportType::create([
            'sop_code'     => $validated['sop_code'],
            'sop_version'  => $validated['sop_version'],
            'name'         => $validated['name'],
            'annex_number' => $validated['annex_number'],
        ]);

        $this->syncMedia($reportType, $validated['medium_labels'] ?? []);
        $this->syncIncubators($reportType, $validated['incubator_labels'] ?? [], $validated['incubator_min_days'] ?? []);
        $this->auditLog('create_report_type', "Membuat jenis laporan: {$reportType->name} ({$reportType->annex_number})", $meta);

        return $reportType;
    }

    public function update(ReportType $reportType, array $validated, array $meta): ReportType
    {
        $reportType->update([
            'sop_code'     => $validated['sop_code'],
            'sop_version'  => $validated['sop_version'],
            'name'         => $validated['name'],
            'annex_number' => $validated['annex_number'],
        ]);

        // hapus lama, sync baru
        $reportType->media()->delete();
        $reportType->incubatorConfigs()->delete();
        $this->syncMedia($reportType, $validated['medium_labels'] ?? []);
        $this->syncIncubators($reportType, $validated['incubator_labels'] ?? [], $validated['incubator_min_days'] ?? []);
        $this->auditLog('update_report_type', "Memperbarui jenis laporan: {$reportType->name} ({$reportType->annex_number})", $meta);

        return $reportType;
    }

    public function delete(ReportType $reportType, array $meta): void
    {
        $name  = $reportType->name;
        $annex = $reportType->annex_number;

        $reportType->delete();

        $this->auditLog('delete_report_type', "Menghapus jenis laporan: {$name} ({$annex})", $meta);
    }

    // ── Helpers ─────────────────────────────────────────────

    private function syncMedia(ReportType $reportType, array $labels): void
    {
        foreach ($labels as $label) {
            $label = trim($label);
            if ($label !== '') {
                ReportTypeMedium::create([
                    'report_type_id' => $reportType->id,
                    'name'           => $label,
                ]);
            }
        }
    }

    private function syncIncubators(ReportType $reportType, array $labels, array $minDays): void
    {
        foreach ($labels as $i => $label) {
            $label = trim($label);
            if ($label !== '') {
                ReportTypeIncubator::create([
                    'report_type_id'    => $reportType->id,
                    'temperature_label' => $label,
                    'min_days'          => (int) ($minDays[$i] ?? 3),
                ]);
            }
        }
    }

    private function auditLog(string $action, string $description, array $meta): void
    {
        AuditLog::create([
            'user_id'     => $meta['user_id'] ?? null,
            'action'      => $action,
            'description' => $description,
            'ip_address'  => $meta['ip_address'] ?? null,
            'user_agent'  => $meta['user_agent'] ?? null,
        ]);
    }
}