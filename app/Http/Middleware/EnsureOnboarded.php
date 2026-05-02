<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Estagiario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureOnboarded
{
    public function handle(Request $request, Closure $next): Response
    {
        $usuario = Auth::user();

        if ($usuario instanceof Estagiario && $usuario->tutorial_visto_em === null) {
            return redirect()->route('onboarding.show');
        }

        return $next($request);
    }
}
