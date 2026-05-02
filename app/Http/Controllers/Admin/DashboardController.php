<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estagiario;
use App\Services\DashboardAdminService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(private readonly DashboardAdminService $service) {}

    public function index(Request $request): View
    {
        $hoje = CarbonImmutable::now();
        $ano = (int) $request->query('ano', (string) $hoje->year);
        $mes = (int) $request->query('mes', (string) $hoje->month);
        $lotacao = $request->query('lotacao');
        $apenasLiberadas = $request->boolean('liberadas');

        $linhas = $this->service->montar($ano, $mes, $lotacao, $apenasLiberadas);

        $lotacoes = Estagiario::query()
            ->where('ativo', true)
            ->whereNotNull('lotacao')
            ->distinct()
            ->orderBy('lotacao')
            ->pluck('lotacao');

        return view('admin.dashboard', [
            'linhas' => $linhas,
            'lotacoes' => $lotacoes,
            'lotacao' => $lotacao,
            'ano' => $ano,
            'mes' => $mes,
            'apenasLiberadas' => $apenasLiberadas,
            'mesAnoExtenso' => CarbonImmutable::create($ano, $mes, 1)->translatedFormat('F / Y'),
        ]);
    }
}
