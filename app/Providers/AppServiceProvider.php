<?php

namespace App\Providers;

use App\Services\ReportSectionService;
use App\View\Composers\SectionTableComposer;
use App\View\Composers\SidebarComposer;
use Domain\AuditLog\Interfaces\AuditLogRepositoryInterface;
use Domain\Room\Interfaces\RoomRepositoryInterface;
use Domain\Room\Repositories\RoomRepository;
use Domain\AuditLog\Repositories\AuditLogRepository;
use Domain\AuditLog\Services\AuditLogService;
use Domain\PasswordPolicy\Interfaces\PasswordSettingRepositoryInterface;
use Domain\PasswordPolicy\Repositories\PasswordSettingRepository;
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
