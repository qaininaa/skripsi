<?php

namespace App\Providers;

use App\Domains\AuditLog\Models\AuditLog;
use App\Services\ReportSectionService;
use App\View\Composers\SectionTableComposer;
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

        Event::listen(Login::class, function (Login $event): void {
            $user = $event->user;

            AuditLog::create([
                'user_id'    => $user->id,
                'action'     => 'login',
                'description' => 'User login: ' . $user->name . ' (' . $user->email . ')',
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);
        });
    }
}