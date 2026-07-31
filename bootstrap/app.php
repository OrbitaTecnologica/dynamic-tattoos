<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

use App\Support\ApiErrorResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'stripe/webhook',
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'admin.2fa' => \App\Http\Middleware\EnsureAdminTwoFactor::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $isApiRequest = static fn (Request $request): bool => $request->is('api/*') || $request->expectsJson();

        $exceptions->render(function (AuthenticationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiErrorResponse::fromStatus(401, 'Unauthenticated.');
        });

        $exceptions->render(function (AuthorizationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiErrorResponse::fromStatus(
                status: 403,
                message: $exception->getMessage() !== '' ? $exception->getMessage() : 'This action is unauthorized.',
            );
        });

        $exceptions->render(function (ValidationException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiErrorResponse::validation(
                errors: $exception->errors(),
                message: $exception->getMessage(),
            );
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            return ApiErrorResponse::fromStatus(404, 'Resource not found.');
        });

        $exceptions->render(function (HttpExceptionInterface $exception, Request $request) use ($isApiRequest) {
            if (! $isApiRequest($request)) {
                return null;
            }

            $status = $exception->getStatusCode();

            if (! in_array($status, [401, 403, 404, 422], true)) {
                return null;
            }

            return ApiErrorResponse::fromStatus(
                status: $status,
                message: $exception->getMessage() !== '' ? $exception->getMessage() : 'HTTP error.',
            );
        });
    })->create();
