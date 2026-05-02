<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Bloqueia rotas que só fazem sentido em desenvolvimento (ex: /_dev/sessao).
 * Em produção retorna 404 — não é só pra esconder; é pra evitar acidentes
 * de roteamento se essas rotas vazarem em alguma branch.
 */
class EnsureNotProduction
{
    public function handle(Request $request, Closure $next): Response
    {
        if (app()->environment('production')) {
            abort(404);
        }

        return $next($request);
    }
}
