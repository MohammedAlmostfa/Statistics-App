<?php

use App\Http\Middleware\JwtMiddleware;
use Illuminate\Foundation\Application;

use App\Http\Middleware\SecurityMiddleware;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;


use Illuminate\Database\QueryException;

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



     $exceptions->render(function (AuthorizationException $e) {
         return response()->json([
             'status' => 'error',
             'message' => 'ليس لديك الصلاحية للقيام بهذا الإجراء.',
         ], 403);
     });


     $exceptions->render(function (Throwable $e) {
         if ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException) {
             return response()->json([
                 'status' => 'error',
                 'message' => 'عذرًا، لم يتم العثور على البيانات المطلوبة.',
             ], 404);
         }

         return null;
     });

     $exceptions->render(function (AuthenticationException $e) {
         return response()->json([
             'status' => 'error',
             'message' => 'الرجاء تسجيل الدخول للوصول إلى هذه الخدمة.',
         ], 401);
     });

     $exceptions->render(function (QueryException $e) {
         return response()->json([
             'status' => 'error',
             'message' => 'حدث خطأ في قاعدة البيانات. الرجاء المحاولة لاحقًا.',
         ], 500);
     });

     $exceptions->render(function (Throwable $e) {
         return response()->json([
             'status' => 'error',
             'message' => 'حدث خطأ غير متوقع. الرجاء المحاولة لاحقًا.',
         ], 500);
     });
 })

    ->create();
