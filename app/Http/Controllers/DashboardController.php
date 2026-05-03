<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use App\Services\BuddyService;
use App\Services\DashboardService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
        private readonly BuddyService $buddy,
    ) {}

    public function index(Request $request): View
    {
        /** @var Estagiario $estagiario */
        $estagiario = $request->user();

        $resumo = $this->dashboard->montar($estagiario);

        $this->buddy->garantirBuddy($estagiario, 'E');
        $buddyData = $this->buddy->montar($estagiario, $resumo->statusHoje);

        return view('dashboard', [
            'estagiario' => $estagiario,
            'resumo' => $resumo,
            'buddy' => $buddyData,
        ]);
    }
}
