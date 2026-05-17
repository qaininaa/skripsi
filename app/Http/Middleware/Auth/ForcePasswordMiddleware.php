<?php

namespace App\Http\Middleware\Auth;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForcePasswordMiddleware
{
    /**
     * @var array<int, string>
     */
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
