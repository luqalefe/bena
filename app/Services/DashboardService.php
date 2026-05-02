<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;
use App\Models\Frequencia;
use Carbon\CarbonImmutable;

class DashboardService
{
    public function montar(Estagiario $estagiario): DashboardData
    {
        $hoje = CarbonImmutable::now();

        $frequenciaHoje = Frequencia::where('estagiario_id', $estagiario->id)
            ->whereDate('data', $hoje->toDateString())
            ->first();

        [$status, $descricao] = $this->resolverStatus($frequenciaHoje);

        $doMes = Frequencia::where('estagiario_id', $estagiario->id)
            ->whereYear('data', $hoje->year)
            ->whereMonth('data', $hoje->month);

        return new DashboardData(
            statusHoje: $status,
            statusDescricao: $descricao,
            horasMes: round((float) $doMes->clone()->sum('horas'), 2),
            diasBatidos: $doMes->clone()->whereNotNull('saida')->count(),
            mesAnoExtenso: $hoje->translatedFormat('F / Y'),
        );
    }

    /**
     * @return array{0: string, 1: ?string}
     */
    private function resolverStatus(?Frequencia $frequencia): array
    {
        if ($frequencia === null || $frequencia->entrada === null) {
            return ['aguardando_entrada', null];
        }

        if ($frequencia->saida === null) {
            return [
                'em_andamento',
                'Em andamento desde '.$frequencia->entrada->format('H:i'),
            ];
        }

        $horas = number_format((float) $frequencia->horas, 2, ',', '');

        return ['concluido', "Concluído ({$horas} h)"];
    }
}
