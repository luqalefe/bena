<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupervisorDashboardController extends Controller
{
    public function index(Request $request): View
    {
        if (session('grupodeacesso') !== 'S') {
            abort(403);
        }

        /** @var Estagiario $supervisor */
        $supervisor = auth()->user();
        $hoje = CarbonImmutable::now();

        $estagiarios = Estagiario::query()
            ->where('supervisor_username', $supervisor->username)
            ->where('ativo', true)
            ->orderBy('nome')
            ->get();

        return view('supervisor.dashboard', [
            'estagiarios' => $estagiarios,
            'ano' => $hoje->year,
            'mes' => $hoje->month,
        ]);
    }
}
