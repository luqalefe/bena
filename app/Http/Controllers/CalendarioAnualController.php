<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CalendarioService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CalendarioAnualController extends Controller
{
    public function __construct(private readonly CalendarioService $calendario) {}

    public function index(Request $request): View
    {
        $hoje = CarbonImmutable::now();
        $ano = (int) $request->query('ano', (string) $hoje->year);
        $mes = (int) $request->query('mes', (string) $hoje->month);

        return $this->renderMes($ano, $mes);
    }

    public function mes(int $ano, int $mes): View
    {
        return $this->renderMes($ano, $mes);
    }

    private function renderMes(int $ano, int $mes): View
    {
        abort_if($mes < 1 || $mes > 12, 404);

        $feriados = $this->calendario->feriadosDoAno($ano);

        $feriadosPorData = [];
        foreach ($feriados as $feriado) {
            if ($feriado->data->month !== $mes) {
                continue;
            }
            $chave = $feriado->data->format('Y-m-d');
            $feriadosPorData[$chave] = [
                'descricao' => $feriado->descricao,
                'tipo' => $feriado->tipo,
                'id' => $feriado->id,
            ];
        }

        return view('calendario.mes', [
            'ano' => $ano,
            'mes' => $mes,
            'feriadosPorData' => $feriadosPorData,
        ]);
    }
}
