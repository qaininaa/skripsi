<?php

namespace App\Services\Dashboard;

use Domain\Report\Models\Report;
use Domain\User\Models\User;
use InvalidArgumentException;

/**
 * Dashboard service.
 *
 * Resolves the right dashboard blade view per user role and exposes the
 * data each dashboard needs.
 */
class DashboardService
{
    /**
     * Map application role keys to dashboard view names.
     *
     * @var array<string, string>
     */
    private const ROLE_VIEW_MAP = [
        'super' => 'dashboard.super-admin',
        'admin' => 'dashboard.admin-qc',
        'analis' => 'dashboard.analyst',
        'supervisor' => 'dashboard.supervisor',
        'manajer' => 'dashboard.manager',
    ];

    /**
     * Resolve the dashboard view name for a given role.
     *
     * @throws InvalidArgumentException When no dashboard exists for the given role.
     */
    public function resolveViewByRole(?string $role): string
    {
        if ($role === null || ! array_key_exists($role, self::ROLE_VIEW_MAP)) {
            throw new InvalidArgumentException('Role tidak memiliki halaman dashboard.');
        }

        return self::ROLE_VIEW_MAP[$role];
    }

    /**
     * Build view data dictionary for the given user.
     *
     * @return array<string, mixed>
     */
    public function buildViewData(?User $user): array
    {
        $base = [
            'userName' => $this->resolveUserName($user),
        ];

        if ($user === null) {
            return $base;
        }

        return match ($user->role) {
            'analis' => array_merge($base, $this->analystData()),
            'supervisor' => array_merge($base, $this->approverData($user, 2)),
            'manajer' => array_merge($base, $this->approverData($user, 3)),
            default => $base,
        };
    }

    /**
     * Resolve display name for the given user.
     */
    public function resolveUserName(?User $user): string
    {
        return $user?->name ?? 'Pengguna';
    }

    /**
     * Build dashboard data for analyst role.
     *
     * @return array<string, mixed>
     */
    private function analystData(): array
    {
        $counts = Report::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        return [
            'counts' => $counts,
            'pendingReports' => $this->latestReportsByStatus('pending'),
            'monitoringReports' => $this->latestReportsByStatus('monitoring'),
            'readingReports' => $this->latestReportsByStatus('reading'),
        ];
    }

    /**
     * Build dashboard data for supervisor (step 2) or manager (step 3).
     *
     * @return array<string, mixed>
     */
    private function approverData(User $user, int $approvalStep): array
    {
        $userId = $user->id;

        $base = fn () => Report::join('report_approvals', 'reports.id', '=', 'report_approvals.report_id')
            ->where('report_approvals.step', $approvalStep)
            ->where('report_approvals.user_id', $userId);

        $pending = (clone $base())->where('report_approvals.status', 'pending')->count();
        $approved = (clone $base())->where('report_approvals.status', 'approved')->count();
        $returned = (clone $base())->whereIn('report_approvals.status', ['returned', 'rejected'])->count();

        $data = [
            'pending' => $pending,
            'approved' => $approved,
            'returned' => $returned,
        ];

        // Supervisor view shows additional report breakdowns.
        if ($approvalStep === 2) {
            $counts = Report::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status');

            $data['counts'] = $counts;
            $data['pendingReports'] = $this->latestReportsByStatus('pending');
            $data['monitoringReports'] = $this->latestReportsByStatus('monitoring');
            $data['readingReports'] = $this->latestReportsByStatus('reading');
        }

        return $data;
    }

    private function latestReportsByStatus(string $status, int $limit = 5)
    {
        return Report::with('reportType', 'lockedByUser')
            ->where('status', $status)
            ->latest()
            ->take($limit)
            ->get();
    }
}
