<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use App\Http\Middleware\IsAdmin;
use App\Http\Middleware\HasRole;
use App\Http\Middleware\HasPermission;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'rider'           => \App\Http\Middleware\RiderMiddleware::class,
            'verified.rider'  => \App\Http\Middleware\VerifiedRiderMiddleware::class,
            'admin'           => IsAdmin::class,
            'role'            => HasRole::class,
            'permission'      => HasPermission::class,
            'seller.verified' => \App\Http\Middleware\SellerVerified::class,
            'seller'          => \App\Http\Middleware\EnsureSeller::class,
            'deactivated' => \App\Http\Middleware\PreventDeactivatedLogin::class,
            'redirect.by.type' => \App\Http\Middleware\RedirectByUserType::class,
        ]);
        $middleware->validateCsrfTokens(except: [
        'telegram/webhook',
        'payment/webhook',
        'telegram/seller/webhook',
        'telegram/admin/webhook',
    ]);

        // Redirect authenticated users away from 'guest' routes to the
        // correct home for their guard, instead of the default '/'.
        $middleware->redirectGuestsTo(function (Request $request) {
            if (auth()->guard('seller')->check()) {
                return route('seller.dashboard');
            }

            return route('home'); // replace 'home' with your customer home route name
        });
    })
    ->withCommands([
    \App\Console\Commands\AlgoliaSetupCommand::class,
     \App\Console\Commands\SendTelegramDigest::class,
    \App\Console\Commands\RunTelegramProactiveChecks::class,
    \App\Console\Commands\SyncDeliveredSellerWallets::class,
    ])
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
