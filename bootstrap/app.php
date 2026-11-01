<?php

use App\Http\Middleware\EnsureCustomerNotBanned;
use App\Http\Middleware\EnsureStaffActive;
use App\Http\Middleware\EnsureStaffRole;
use App\Http\Middleware\RedirectIfCustomerAuthenticated;
use App\Http\Middleware\RedirectIfStaffAuthenticated;
use App\Http\Middleware\SecurityHeaders;
use App\Http\Middleware\TrackVisit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

$app = Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(SecurityHeaders::class);

        $middleware->redirectGuestsTo(function (Request $request) {
            if ($request->is('staff*')) {
                return route('staff.login');
            }

            return route('login');
        });

        $middleware->alias([
            'customer.not_banned' => EnsureCustomerNotBanned::class,
            'customer.guest' => RedirectIfCustomerAuthenticated::class,
            'staff.guest' => RedirectIfStaffAuthenticated::class,
            'staff.active' => EnsureStaffActive::class,
            'staff.role' => EnsureStaffRole::class,
            'track.visit' => TrackVisit::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();

$app->useLangPath(resource_path('lang'));

return $app;
