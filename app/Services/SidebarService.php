<?php

namespace App\Services;

use Domain\Report\Models\Report;
use Domain\Report\Models\ReportApproval;
use Domain\User\Models\User;
use Illuminate\Support\Facades\Route;

/**
 * Builds the sidebar navigation structure for the authenticated user.
 *
 * Returns role-aware sections and items, filtered to only include
 * routes that are currently registered in the application. Also exposes
 * pending-approval and ongoing-report badge counts for supervisor and
 * manajer roles.
 */
class SidebarService
{
    /**
     * Build the complete sidebar data array for the given user.
     *
     * Returns an array with keys: roleLabel, sections, user.
     * Returns an empty structure if no user is provided.
     *
     * @param  User|null  $user  The authenticated user, or null for unauthenticated context.
     * @return array{
     *     roleLabel: string|null,
     *     sections: array<int, array{label: string, items: array<int, array{label: string, route: string, activePattern: string, icon: string, badge?: int|null, badgeColor?: string}>}>,
     *     user: array{name: string, email: string|null, role: string}|null
     * }
     */
    public function buildForUser(?User $user): array
    {
        if (! $user) {
            return [
                'roleLabel' => null,
                'sections' => [],
                'user' => null,
            ];
        }

        $role = (string) $user->role;
        [$incomingCount, $ongoingCount] = $this->resolveReportBadgeCounts($user, $role);

        return [
            'roleLabel' => $this->resolveRoleLabel($role),
            'sections' => $this->resolveSections($role, $incomingCount, $ongoingCount),
            'user' => [
                'name' => $user->name,
                'email' => $user->email ?? $user->username ?? null,
                'role' => $role,
            ],
        ];
    }

    /**
     * Resolve a human-readable label for the given role slug.
     */
    protected function resolveRoleLabel(string $role): string
    {
        return match ($role) {
            'super' => 'Super Admin',
            'admin' => 'Admin Quality Control',
            'analyst' => 'Analis Lab. Mikrobiologi',
            'supervisor' => 'Supervisor Mikrobiologi',
            'manager' => 'Manajer',
            default => 'Pengguna',
        };
    }

    /**
     * Build the ordered list of sidebar sections for the given role.
     *
     * Each section has a label and an array of navigation items.
     * Sections containing no registered routes are automatically excluded.
     *
     * @return array<int, array{label: string, items: array<int, array{label: string, route: string, activePattern: string, icon: string, badge?: int|null, badgeColor?: string}>}>
     */
    protected function resolveSections(string $role, int $incomingCount, int $ongoingCount): array
    {
        $sections = [
            [
                'label' => 'Dashboard',
                'items' => [
                    $this->item('Dashboard', 'dashboard', 'dashboard', 'icons/sidebar/dashboard.svg'),
                ],
            ],
        ];

        if ($role === 'super') {
            $sections[] = [
                'label' => 'Manajemen',
                'items' => [
                    $this->item('Manajemen Pengguna', 'users.index', 'users.*', 'icons/sidebar/users.svg'),
                    $this->item('Audit Trail', 'audit-logs.index', 'audit-logs.*', 'icons/sidebar/audit.svg'),
                    $this->item('Pengaturan Password', 'settings.index', 'settings.*', 'icons/sidebar/settings.svg'),
                ],
            ];
        }

        if ($role === 'admin') {
            $sections[] = [
                'label' => 'Master Data',
                'items' => [
                    $this->item('Ruangan', 'master.room.index', 'master.room.*', 'icons/sidebar/building.svg'),
                    $this->item('Lokasi', 'master.location.index', 'master.location.*', 'icons/sidebar/location.svg'),
                    $this->item('Manajemen Laporan', 'report-types.index', 'report-types.*', 'icons/sidebar/reports.svg'),
                ],
            ];

            $sections[] = [
                'label' => 'Laporan',
                'items' => [
                    $this->item('Tugas Pelaporan', 'report-assignment.index', 'report-assignment.*', 'icons/sidebar/reports.svg'),
                ],
            ];
        }

        if ($role === 'analyst') {
            $sections[] = [
                'label' => 'Laporan',
                'items' => [
                    $this->item('Laporan', 'reports.index', 'reports.*', 'icons/sidebar/reports.svg'),
                ],
            ];
        }

        if ($role === 'supervisor') {
            $sections[] = [
                'label' => 'Laporan',
                'items' => [
                    $this->item(
                        'Laporan Masuk',
                        'supervisor.incoming-reports',
                        'supervisor.incoming-reports',
                        'icons/sidebar/inbox.svg',
                        $incomingCount,
                        'red',
                    ),
                    $this->item(
                        'Sedang Dikerjakan',
                        'supervisor.ongoing-reports',
                        'supervisor.ongoing-reports',
                        'icons/sidebar/reports.svg',
                        $ongoingCount,
                        'emerald',
                    ),
                ],
            ];
        }

        if ($role === 'manager') {
            $sections[] = [
                'label' => 'Laporan',
                'items' => [
                    $this->item(
                        'Laporan Masuk',
                        'manager.incoming-reports',
                        'manager.incoming-reports',
                        'icons/sidebar/inbox.svg',
                        $incomingCount,
                        'red',
                    ),
                    $this->item(
                        'Sedang Dikerjakan',
                        'manager.ongoing-reports',
                        'manager.ongoing-reports',
                        'icons/sidebar/reports.svg',
                        $ongoingCount,
                        'emerald',
                    ),
                ],
            ];
        }

        if ($role !== 'super') {
            $sections[] = [
                'label' => 'Arsip',
                'items' => [
                    $this->item('Arsip Laporan', 'report-archive.index', 'report-archive.*', 'icons/sidebar/archive.svg'),
                ],
            ];
        }

        return $this->filterUnavailableRoutes($sections);
    }

    /**
     * Create a single navigation item array.
     *
     * @return array{label: string, route: string, activePattern: string, icon: string, badge?: int|null, badgeColor?: string}
     */
    protected function item(
        string $label,
        string $routeName,
        string $activePattern,
        string $icon,
        ?int $badge = null,
        string $badgeColor = 'red',
    ): array {
        return [
            'label' => $label,
            'route' => $routeName,
            'activePattern' => $activePattern,
            'icon' => $icon,
            'badge' => $badge,
            'badgeColor' => $badgeColor,
        ];
    }

    /**
     * Compute the [incoming, ongoing] report badge counts for the given user.
     *
     * Only meaningful for supervisor and manajer roles.
     *
     * @return array{0: int, 1: int}
     */
    protected function resolveReportBadgeCounts(User $user, string $role): array
    {
        if (! in_array($role, ['supervisor', 'manager'], true)) {
            return [0, 0];
        }

        $step = $role === 'supervisor' ? 2 : 3;

        $incoming = ReportApproval::query()
            ->where('step', $step)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->count();

        $ongoing = Report::query()
            ->whereDoesntHave('approvals', function ($query) {
                $query->where('step', 3)->where('status', 'approved');
            })
            ->where(function ($query) {
                $query->whereIn('status', ['pending', 'monitoring', 'reading'])
                    ->orWhereHas('approvals', function ($approvalQuery) {
                        $approvalQuery->where('step', 2)->where('status', 'pending');
                    })
                    ->orWhereHas('approvals', function ($approvalQuery) {
                        $approvalQuery->where('step', 3)->where('status', 'pending');
                    });
            })
            ->count();

        return [$incoming, $ongoing];
    }

    /**
     * Remove sections and items whose named routes are not registered.
     *
     * Sections that become empty after filtering are also removed.
     *
     * @param  array<int, array{label: string, items: array<int, array<string, mixed>>}>  $sections
     * @return array<int, array{label: string, items: array<int, array<string, mixed>>}>
     */
    protected function filterUnavailableRoutes(array $sections): array
    {
        $filtered = [];

        foreach ($sections as $section) {
            $items = array_values(array_filter(
                $section['items'],
                static fn (array $item): bool => Route::has($item['route'])
            ));

            if ($items === []) {
                continue;
            }

            $filtered[] = [
                'label' => $section['label'],
                'items' => $items,
            ];
        }

        return $filtered;
    }
}
