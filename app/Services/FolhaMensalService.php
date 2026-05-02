<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;
use App\Models\Frequencia;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class FolhaMensalService
{
    public function __construct(private readonly CalendarioService $calendario) {}

    public function montar(Estagiario $estagiario, int $ano, int $mes): FolhaMensal
    {
        $inicio = CarbonImmutable::create($ano, $mes, 1);
        $diasNoMes = $inicio->daysInMonth;

        $frequenciasPorDia = Frequencia::query()
            ->where('estagiario_id', $estagiario->id)
            ->whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->get()
            ->keyBy(fn (Frequencia $f) => (int) $f->data->day);

        $feriadosPorDia = $this->calendario->feriadosDoAno($ano)
            ->filter(fn ($feriado) => (int) $feriado->data->month === $mes)
            ->keyBy(fn ($feriado) => (int) $feriado->data->day);

        $dias = new Collection;
        for ($d = 1; $d <= $diasNoMes; $d++) {
            $data = $inicio->setDay($d);
            $feriado = $feriadosPorDia->get($d);

            $dias->push(new DiaFolha(
                data: $data,
                tipo: $this->classificar($data, $feriado !== null),
                frequencia: $frequenciasPorDia->get($d),
                descricaoFeriado: $feriado?->descricao,
            ));
        }

        return new FolhaMensal(
            ano: $ano,
            mes: $mes,
            dias: $dias,
            totalHoras: (float) $frequenciasPorDia->sum('horas'),
        );
    }

    private function classificar(CarbonImmutable $data, bool $ehFeriado): string
    {
        if ($ehFeriado) {
            return 'feriado';
        }

        return match ($data->dayOfWeek) {
            CarbonImmutable::SATURDAY => 'sabado',
            CarbonImmutable::SUNDAY => 'domingo',
            default => 'util',
        };
    }
}
