<?php

use App\Http\Middleware\AddSecurityHeaders;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Sentry\Laravel\Integration;
use Symfony\Component\HttpFoundation\Response;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->append(AddSecurityHeaders::class);
        // Mail providers post RFC 8058 one-click unsubscribes without a session; the signed URL is the credential.
        $middleware->preventRequestForgery(except: ['newsletter/unsubscribe/*']);
        // Forge's Nginx resolves trusted proxy addresses before setting REMOTE_ADDR.
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        Integration::handles($exceptions);

        $exceptions->respond(
            fn (Response $response, Throwable $_exception, Request $request): Response => AddSecurityHeaders::apply($response, $request)
        );
    })->create();
