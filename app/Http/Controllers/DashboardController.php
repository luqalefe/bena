<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardService $dashboard) {}

    public function index(Request $request): View
    {
        /** @var Estagiario $estagiario */
        $estagiario = $request->user();

        return view('dashboard', [
            'estagiario' => $estagiario,
            'resumo' => $this->dashboard->montar($estagiario),
        ]);
    }
}
