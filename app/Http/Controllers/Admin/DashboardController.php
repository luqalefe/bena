<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estagiario;
use App\Services\ConformidadeService;
use App\Services\DashboardAdminService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardAdminService $service,
        private readonly ConformidadeService $conformidade,
    ) {}

    public function index(Request $request): View
    {
        $hoje = CarbonImmutable::now();
        $ano = (int) $request->query('ano', (string) $hoje->year);
        $mes = (int) $request->query('mes', (string) $hoje->month);
        $lotacao = $request->query('lotacao');
        $apenasLiberadas = $request->boolean('liberadas');
        $alerta = $request->query('alerta');
        $alerta = is_string($alerta) && $alerta !== '' ? $alerta : null;

        $linhas = $this->service->montar($ano, $mes, $lotacao, $apenasLiberadas, $alerta);

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
            'alerta' => $alerta,
            'descricaoAlerta' => fn (string $codigo) => $this->conformidade->descricao($codigo),
            'mesAnoExtenso' => CarbonImmutable::create($ano, $mes, 1)->translatedFormat('F / Y'),
        ]);
    }
}
