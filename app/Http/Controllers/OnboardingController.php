<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function show(): View
    {
        return view('onboarding.show');
    }

    public function concluir(Request $request): RedirectResponse
    {
        /** @var Estagiario $usuario */
        $usuario = $request->user();
        $usuario->tutorial_visto_em = CarbonImmutable::now();
        $usuario->save();

        return redirect()->route('dashboard');
    }
}
