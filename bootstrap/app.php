<?php

use App\Console\Commands\FecharPontosAbertosCommand;
use App\Http\Middleware\ConfigureUserSession;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withCommands([
        FecharPontosAbertosCommand::class,
    ])
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'configure.session' => ConfigureUserSession::class,
        ]);

        $middleware->trustProxies(at: env('TRUSTED_PROXIES', '127.0.0.1'));
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Regras de negócio violadas:
        //  - request JSON → 422 com {message: ...}
        //  - form POST do navegador → volta pra página anterior com flash 'erro'
        $exceptions->render(function (DomainException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage()], 422);
            }

            if ($request->isMethod('post')) {
                return back()->with('erro', $e->getMessage());
            }

            return null;
        });
    })->create();
