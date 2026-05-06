<?php

namespace App\Domains\Auth\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordChangeMiddleware
{
    protected array $except = [
        'password.change',
        'password.change.update',
        'logout',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && $user->mustChangePassword()) {
            foreach ($this->except as $routeName) {
                if ($request->routeIs($routeName)) {
                    return $next($request);
                }
            }

            return redirect()->route('password.change')
                ->with('warning', 'Anda harus mengganti password sebelum melanjutkan.');
        }

        return $next($request);
    }
}
