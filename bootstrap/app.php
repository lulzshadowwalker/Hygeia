<?php

use App\Http\Middleware\DefaultAcceptJsonHeader;
use App\Http\Middleware\LanguageMiddleware;
use App\Http\Response\JsonResponseBuilder;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Exceptions\PostTooLargeException;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->group('api', [
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            // 'throttle:api',
            LanguageMiddleware::class,
            DefaultAcceptJsonHeader::class,
            // SandboxMiddleware::class,
            SubstituteBindings::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        if (!request()->is('api/*')) return;
        if (config('app.debug')) return;

        $exceptions->render(function (HttpResponseException $exception, Request $request) {
            return $exception->getResponse();
        });

        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            $builder->error(
                title: 'Unauthenticated',
                detail: 'The user is not authenticated.',
                code: Response::HTTP_UNAUTHORIZED,
            );
            return $builder->build();
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            foreach ($exception->errors() as $field => $messages) {
                $builder->error(
                    title: 'Invalid Attribute',
                    detail: $messages[0],
                    code: Response::HTTP_UNPROCESSABLE_ENTITY,
                    meta: ['info' => 'Ensure that the attribute meets the required validation rules.'],
                    pointer: "/data/attributes/{$field}"
                );
            }
            return $builder->build();
        });

        $exceptions->render(function (ModelNotFoundException $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            $builder->error(
                title: 'Resource Not Found',
                detail: 'The requested resource could not be found.',
                code: Response::HTTP_NOT_FOUND,
            );
            return $builder->build();
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            $builder->error(
                title: 'Not Found',
                detail: 'The requested resource could not be found.',
                code: Response::HTTP_NOT_FOUND,
            );
            return $builder->build();
        });

        $exceptions->render(function (AccessDeniedHttpException $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            $builder->error(
                title: 'Forbidden',
                detail: $exception->getMessage() ?: 'Access to this resource is forbidden.',
                code: Response::HTTP_FORBIDDEN,
            );
            return $builder->build();
        });

        $exceptions->render(function (HttpException $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            $builder->error(
                title: $exception->getMessage() ?: 'HTTP Error',
                detail: $exception->getMessage(),
                code: $exception->getStatusCode()
            );
            return $builder->build();
        });

        $exceptions->render(function (PostTooLargeException $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            $builder->error(
                title: 'Request Entity Too Large',
                detail: 'The request entity is too large.',
                code: Response::HTTP_REQUEST_ENTITY_TOO_LARGE
            );
            return $builder->build();
        });

        $exceptions->render(function (Exception $exception, Request $request) {
            $builder = new JsonResponseBuilder();
            $builder->error(
                title: 'Internal Server Error',
                detail: 'An unexpected error occurred on the server.',
                code: Response::HTTP_INTERNAL_SERVER_ERROR
            );
            return $builder->build();
        });
    })->create();
