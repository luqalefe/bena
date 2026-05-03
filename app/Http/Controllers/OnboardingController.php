<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use App\Services\BuddyService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OnboardingController extends Controller
{
    public function __construct(private readonly BuddyService $buddy) {}

    public function show(Request $request): View
    {
        $usuario = $request->user();
        $buddyData = null;

        // A apresentação do mascote aparece pra todos os grupos no /bem-vindo,
        // independente de admin/supervisor/estagiário. No dashboard de cada
        // grupo é que a regra muda — buddy fica visível só no dashboard do
        // estagiário (grupo 'E'). Supervisores e admin recebem buddy do pool
        // sênior (águia, leão, elefante, urso).
        if ($usuario instanceof Estagiario) {
            $grupo = (string) session('grupodeacesso');
            $this->buddy->garantirBuddy($usuario, $grupo);
            $buddyData = $this->buddy->boasVindas($usuario);
        }

        return view('onboarding.show', ['buddy' => $buddyData]);
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
