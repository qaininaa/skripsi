<?php

namespace Domain\Report\Services;

use Domain\AuditLog\Services\AuditLogService;
use Domain\Report\Models\Report;
use Illuminate\Support\Facades\Auth;

/**
 * Service for granular audit logging on analyst report claim and field updates.
 */
class ReportFieldAuditService
{
    public function __construct(
        private AuditLogService $auditLogService,
    ) {}

    public function logClaimStateChange(
        Report $report,
        ?string $beforeStatus,
        ?string $afterStatus,
        ?string $beforeLockedBy,
        ?string $afterLockedBy
    ): void {
        if (! $this->isAnalystActor()) {
            return;
        }

        if (
            $this->normalizeComparable($beforeStatus) === $this->normalizeComparable($afterStatus)
            && $this->normalizeComparable($beforeLockedBy) === $this->normalizeComparable($afterLockedBy)
        ) {
            return;
        }

        $actor = $this->actorLabel();
        $reportLabel = $this->reportLabel($report);

        $this->auditLogService->log(
            'claim_report',
            "{$actor} melakukan klaim laporan {$reportLabel}: status {$this->display($beforeStatus)} -> {$this->display($afterStatus)}, lock {$this->display($beforeLockedBy)} -> {$this->display($afterLockedBy)}",
            $this->meta()
        );
    }

    public function logFieldChange(Report $report, string $fieldPath, mixed $before, mixed $after): void
    {
        if (! $this->isAnalystActor()) {
            return;
        }

        $beforeNormalized = $this->normalizeComparable($before);
        $afterNormalized = $this->normalizeComparable($after);

        if ($beforeNormalized === $afterNormalized) {
            return;
        }

        $actor = $this->actorLabel();
        $reportLabel = $this->reportLabel($report);

        $this->auditLogService->log(
            'update_report_field',
            "{$actor} mengubah field {$fieldPath} pada laporan {$reportLabel}: {$this->display($before)} -> {$this->display($after)}",
            $this->meta()
        );
    }

    /**
     * @return array{user_id: string|null, ip_address: string|null, user_agent: string|null}
     */
    private function meta(): array
    {
        return [
            'user_id' => Auth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ];
    }

    private function isAnalystActor(): bool
    {
        return (Auth::user()?->role ?? null) === 'analyst';
    }

    private function actorLabel(): string
    {
        $user = Auth::user();

        if (! $user) {
            return 'Analis';
        }

        $username = trim((string) ($user->username ?? ''));

        return $username !== ''
            ? "Analis {$user->name} ({$username})"
            : "Analis {$user->name}";
    }

    private function reportLabel(Report $report): string
    {
        $report->loadMissing('reportType');

        $annexNumber = $report->reportType?->annex_number ?? 'Annex tidak diketahui';
        $reportTypeName = $report->reportType?->name ?? 'Jenis laporan tidak diketahui';

        return "{$report->product_name} (Batch {$report->batch_number}) - {$annexNumber} {$reportTypeName}";
    }

    private function normalizeComparable(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof \DateTimeInterface) {
            return $value->format('Y-m-d H:i:s');
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            $trimmed = trim((string) $value);

            return $trimmed !== '' ? $trimmed : null;
        }

        $encoded = json_encode($value);

        return $encoded !== false ? $encoded : null;
    }

    private function display(mixed $value): string
    {
        $normalized = $this->normalizeComparable($value);

        return $normalized === null ? '[kosong]' : $normalized;
    }
}
