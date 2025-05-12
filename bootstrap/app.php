<?php

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Foundation\Application;
use App\Console\Commands\CheckLatePayments;
use App\Http\Middleware\SecurityMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Facades\Schedule;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )

     ->withMiddleware(function (Middleware $middleware) {


         $middleware->alias([
             'jwt' => JwtMiddleware::class,

         ]);
         $middleware->append([

          'security' => SecurityMiddleware::class,

        ]);
     })

    ->withExceptions(function (Exceptions $exceptions) {
        // Exception handling configuration
        $exceptions->render(function (AuthorizationException $e) {
            return response()->json([
                'errors' => [
                    'errorDetails' => $e->getMessage(),
                ]
            ], $e->status ?? 403);
        });

        $exceptions->render(function (ModelNotFoundException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'حدث خطأ . يرجى إعادة المحاولة.',
                'errors' => [
                    'errorDetails' => __("السجل غيرموجود "),

                ]
            ], 404);
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return response()->json([
                'status' => 'eror',
                'message' => 'حدث خطأ . يرجى إعادة المحاولة.',
                'errors' => [
                    'errorDetails' => __("السجل غيرموجود "),

                ]
            ], 404);
        });
    })
    ->create();
