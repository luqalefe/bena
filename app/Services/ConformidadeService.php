<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Models\RecessoEstagiario;
use Carbon\CarbonImmutable;

class ConformidadeService
{
    public const ALERTA_TCE_VENCENDO = 'tce_vencendo';

    public const ALERTA_SEM_RECESSO = 'sem_recesso';

    public const ALERTA_JORNADA_EXCEDIDA = 'jornada_excedida';

    /**
     * @return list<string>
     */
    public function alertasParaEstagiario(Estagiario $estagiario, ?CarbonImmutable $hoje = null): array
    {
        $hoje = ($hoje ?? CarbonImmutable::now())->startOfDay();
        $alertas = [];

        if ($this->tceVencendo($estagiario, $hoje)) {
            $alertas[] = self::ALERTA_TCE_VENCENDO;
        }

        if ($this->semRecesso($estagiario, $hoje)) {
            $alertas[] = self::ALERTA_SEM_RECESSO;
        }

        if ($this->jornadaExcedida($estagiario, $hoje)) {
            $alertas[] = self::ALERTA_JORNADA_EXCEDIDA;
        }

        return $alertas;
    }

    public function descricao(string $codigo): string
    {
        return match ($codigo) {
            self::ALERTA_TCE_VENCENDO => 'TCE vence em até 30 dias.',
            self::ALERTA_SEM_RECESSO => 'Recesso anual pendente: 12+ meses sem registro.',
            self::ALERTA_JORNADA_EXCEDIDA => 'Jornada semanal acima do limite contratado.',
            default => $codigo,
        };
    }

    private function tceVencendo(Estagiario $estagiario, CarbonImmutable $hoje): bool
    {
        if ($estagiario->fim_estagio === null) {
            return false;
        }

        $fim = CarbonImmutable::parse($estagiario->fim_estagio)->startOfDay();
        if ($fim->lt($hoje)) {
            return false;
        }

        return $fim->lte($hoje->addDays(30));
    }

    private function semRecesso(Estagiario $estagiario, CarbonImmutable $hoje): bool
    {
        if ($estagiario->inicio_estagio === null) {
            return false;
        }

        $inicio = CarbonImmutable::parse($estagiario->inicio_estagio)->startOfDay();
        if ($inicio->gt($hoje->subYear())) {
            return false;
        }

        $umAnoAtras = $hoje->subYear()->toDateString();
        $hojeStr = $hoje->toDateString();

        // Conta como recesso "exercido" apenas o que já começou — recesso
        // agendado pra data futura não silencia o alerta.
        return ! RecessoEstagiario::query()
            ->where('estagiario_id', $estagiario->id)
            ->whereDate('inicio', '>=', $umAnoAtras)
            ->whereDate('inicio', '<=', $hojeStr)
            ->exists();
    }

    private function jornadaExcedida(Estagiario $estagiario, CarbonImmutable $hoje): bool
    {
        $segunda = $hoje->startOfWeek(CarbonImmutable::MONDAY);
        $domingo = $segunda->endOfWeek(CarbonImmutable::SUNDAY);

        $somaHoras = (float) Frequencia::query()
            ->where('estagiario_id', $estagiario->id)
            ->whereBetween('data', [$segunda->toDateString(), $domingo->toDateString()])
            ->sum('horas');

        $limiteSemanal = (float) $estagiario->horas_diarias * 5;

        return $somaHoras > $limiteSemanal;
    }
}
