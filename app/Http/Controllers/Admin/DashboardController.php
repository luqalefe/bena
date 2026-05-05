<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Estagiario;
use App\Models\Setor;
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
        $setor = $request->query('setor');
        $setor = is_string($setor) && $setor !== '' ? $setor : null;
        $apenasLiberadas = $request->boolean('liberadas');
        $alerta = $request->query('alerta');
        $alerta = is_string($alerta) && $alerta !== '' ? $alerta : null;

        $linhas = $this->service->montar($ano, $mes, $setor, $apenasLiberadas, $alerta);

        $setores = Setor::query()
            ->whereIn('id', Estagiario::query()->where('ativo', true)->whereNotNull('setor_id')->pluck('setor_id'))
            ->orderBy('sigla')
            ->pluck('sigla');

        return view('admin.dashboard', [
            'linhas' => $linhas,
            'setores' => $setores,
            'setor' => $setor,
            'ano' => $ano,
            'mes' => $mes,
            'apenasLiberadas' => $apenasLiberadas,
            'alerta' => $alerta,
            'descricaoAlerta' => fn (string $codigo) => $this->conformidade->descricao($codigo),
            'mesAnoExtenso' => CarbonImmutable::create($ano, $mes, 1)->translatedFormat('F / Y'),
        ]);
    }
}
