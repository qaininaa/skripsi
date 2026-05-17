<?php

namespace App\Providers;

use App\Services\ReportSectionService;
use App\View\Composers\SectionTableComposer;
use App\View\Composers\SidebarComposer;
use Domain\AuditLog\Interfaces\AuditLogRepositoryInterface;
use Domain\Location\Interfaces\LocationRepositoryInterface;
use Domain\Location\Repositories\LocationRepository;
use Domain\ReportType\Interfaces\ReportSectionRepositoryInterface;
use Domain\ReportType\Interfaces\ReportTypeRepositoryInterface;
use Domain\ReportType\Interfaces\SectionLocationRepositoryInterface;
use Domain\ReportType\Repositories\ReportSectionRepository;
use Domain\ReportType\Repositories\ReportTypeRepository;
use Domain\ReportType\Repositories\SectionLocationRepository;
use Domain\Room\Interfaces\RoomRepositoryInterface;
use Domain\Room\Repositories\RoomRepository;
use Domain\AuditLog\Repositories\AuditLogRepository;
use Domain\AuditLog\Services\AuditLogService;
use Domain\PasswordPolicy\Interfaces\PasswordSettingRepositoryInterface;
use Domain\PasswordPolicy\Repositories\PasswordSettingRepository;
use Domain\Report\Interfaces\FieldLockRepositoryInterface;
use Domain\Report\Interfaces\IncubatorEntryRepositoryInterface;
use Domain\Report\Interfaces\InstrumentIdentityEntryRepositoryInterface;
use Domain\Report\Interfaces\MediumEntryRepositoryInterface;
use Domain\Report\Interfaces\ReportClaimingRepositoryInterface;
use Domain\Report\Interfaces\ReportDraftingRepositoryInterface;
use Domain\Report\Interfaces\ReportEntryRepositoryInterface;
use Domain\Report\Interfaces\ReportSubmissionRepositoryInterface;
use Domain\Report\Repositories\FieldLockRepository;
use Domain\Report\Repositories\IncubatorEntryRepository;
use Domain\Report\Repositories\InstrumentIdentityEntryRepository;
use Domain\Report\Repositories\MediumEntryRepository;
use Domain\Report\Repositories\ReportClaimingRepository;
use Domain\Report\Repositories\ReportDraftingRepository;
use Domain\Report\Repositories\ReportEntryRepository;
use Domain\Report\Repositories\ReportSubmissionRepository;
use Domain\ReportAssignment\Interfaces\ReportAssignmentRepositoryInterface;
use Domain\ReportAssignment\Repositories\ReportAssignmentRepository;
use Domain\User\Interfaces\AuthRepositoryInterface;
use Domain\User\Interfaces\PasswordRepositoryInterface;
use Domain\User\Interfaces\UserRepositoryInterface;
use Domain\User\Repositories\AuthRepository;
use Domain\User\Repositories\PasswordRepository;
use Domain\User\Repositories\UserRepository;
use Illuminate\Auth\Events\Login;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ReportSectionService::class);

        // Domain repository bindings
        $this->app->bind(AuthRepositoryInterface::class, AuthRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(PasswordRepositoryInterface::class, PasswordRepository::class);
        $this->app->bind(PasswordSettingRepositoryInterface::class, PasswordSettingRepository::class);
        $this->app->bind(AuditLogRepositoryInterface::class, AuditLogRepository::class);
        $this->app->bind(RoomRepositoryInterface::class, RoomRepository::class);
        $this->app->bind(LocationRepositoryInterface::class, LocationRepository::class);
        $this->app->bind(ReportTypeRepositoryInterface::class, ReportTypeRepository::class);
        $this->app->bind(ReportSectionRepositoryInterface::class, ReportSectionRepository::class);
        $this->app->bind(SectionLocationRepositoryInterface::class, SectionLocationRepository::class);
        $this->app->bind(ReportAssignmentRepositoryInterface::class, ReportAssignmentRepository::class);

        // Report domain repository bindings
        $this->app->bind(FieldLockRepositoryInterface::class, FieldLockRepository::class);
        $this->app->bind(IncubatorEntryRepositoryInterface::class, IncubatorEntryRepository::class);
        $this->app->bind(InstrumentIdentityEntryRepositoryInterface::class, InstrumentIdentityEntryRepository::class);
        $this->app->bind(MediumEntryRepositoryInterface::class, MediumEntryRepository::class);
        $this->app->bind(ReportClaimingRepositoryInterface::class, ReportClaimingRepository::class);
        $this->app->bind(ReportDraftingRepositoryInterface::class, ReportDraftingRepository::class);
        $this->app->bind(ReportEntryRepositoryInterface::class, ReportEntryRepository::class);
        $this->app->bind(ReportSubmissionRepositoryInterface::class, ReportSubmissionRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer(
            'pages.laporan.partials.section-tabel',
            SectionTableComposer::class
        );

        View::composer('components.sidebar.sidebar', SidebarComposer::class);

        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user;

            app(AuditLogService::class)->log(
                'login',
                'User login: ' . $user->name . ' (' . $user->username . ')',
                [
                    'user_id' => $user->id,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                ],
            );
        });
    }
}
