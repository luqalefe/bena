<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Models\RecessoEstagiario;
use Carbon\CarbonImmutable;
use DomainException;

class PontoService
{
    public function __construct(private readonly CalendarioService $calendario) {}

    public function baterEntrada(Estagiario $estagiario, ?string $ip = null): Frequencia
    {
        $this->garantirAtivo($estagiario);

        $agora = CarbonImmutable::now();
        $hoje = $agora->toDateString();

        $this->garantirVigencia($estagiario, $agora);
        $this->garantirNaoEmRecesso($estagiario, $agora);

        if (! $this->calendario->ehDiaUtil($agora)) {
            throw new DomainException('Hoje não é dia útil.');
        }

        $existente = Frequencia::where('estagiario_id', $estagiario->id)
            ->whereDate('data', $hoje)
            ->first();

        if ($existente !== null && $existente->entrada !== null) {
            throw new DomainException(
                'Entrada já registrada hoje às '.$existente->entrada->format('H:i').'.'
            );
        }

        return Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => $hoje,
            'entrada' => $agora->format('H:i:s'),
            'ip_entrada' => $ip,
        ]);
    }

    public function baterSaida(Estagiario $estagiario, ?string $ip = null): Frequencia
    {
        $this->garantirAtivo($estagiario);

        $agora = CarbonImmutable::now();
        $hoje = $agora->toDateString();

        $this->garantirVigencia($estagiario, $agora);
        $this->garantirNaoEmRecesso($estagiario, $agora);

        $frequencia = Frequencia::where('estagiario_id', $estagiario->id)
            ->whereDate('data', $hoje)
            ->first();

        if ($frequencia === null || $frequencia->entrada === null) {
            throw new DomainException('Você precisa bater a entrada antes de bater a saída.');
        }

        if ($frequencia->saida !== null) {
            throw new DomainException(
                'Saída já registrada hoje às '.$frequencia->saida->format('H:i').'.'
            );
        }

        $entrada = CarbonImmutable::createFromFormat('H:i:s', $frequencia->entrada->format('H:i:s'));
        $saidaTime = CarbonImmutable::createFromFormat('H:i:s', $agora->format('H:i:s'));

        if ($saidaTime <= $entrada) {
            throw new DomainException('A saída precisa ser posterior à entrada.');
        }

        $horas = round($entrada->diffInMinutes($saidaTime) / 60, 2);

        $frequencia->saida = $agora->format('H:i:s');
        $frequencia->ip_saida = $ip;
        $frequencia->horas = $horas;
        $frequencia->save();

        return $frequencia;
    }

    private function garantirAtivo(Estagiario $estagiario): void
    {
        if (! $estagiario->ativo) {
            throw new DomainException('Estágio inativo. Procure a coordenação.');
        }
    }

    private function garantirNaoEmRecesso(Estagiario $estagiario, CarbonImmutable $agora): void
    {
        $hoje = $agora->toDateString();

        $recesso = RecessoEstagiario::query()
            ->where('estagiario_id', $estagiario->id)
            ->whereDate('inicio', '<=', $hoje)
            ->whereDate('fim', '>=', $hoje)
            ->first();

        if ($recesso !== null) {
            $fim = CarbonImmutable::parse($recesso->fim)->format('d/m/Y');
            throw new DomainException("Em recesso até {$fim}.");
        }
    }

    private function garantirVigencia(Estagiario $estagiario, CarbonImmutable $agora): void
    {
        $hoje = $agora->startOfDay();

        if ($estagiario->inicio_estagio !== null) {
            $inicio = CarbonImmutable::parse($estagiario->inicio_estagio)->startOfDay();
            if ($hoje->lt($inicio)) {
                throw new DomainException(
                    'Estágio ainda não começou (início em '.$inicio->format('d/m/Y').').'
                );
            }
        }

        if ($estagiario->fim_estagio !== null) {
            $fim = CarbonImmutable::parse($estagiario->fim_estagio)->startOfDay();
            if ($hoje->gt($fim)) {
                throw new DomainException(
                    'Estágio encerrado em '.$fim->format('d/m/Y').'.'
                );
            }
        }
    }

    /**
     * Fecha pontos esquecidos: registros com entrada e sem saida em dias
     * passados ganham saida = entrada + horas_diarias do estagiário, e
     * são marcados como saida_automatica=true. Idempotente.
     */
    public function fecharPontosAbertos(): int
    {
        $hoje = CarbonImmutable::now()->toDateString();

        $abertos = Frequencia::query()
            ->whereNotNull('entrada')
            ->whereNull('saida')
            ->whereDate('data', '<', $hoje)
            ->with('estagiario')
            ->get();

        foreach ($abertos as $frequencia) {
            $jornada = (float) $frequencia->estagiario->horas_diarias;
            $entrada = CarbonImmutable::createFromFormat('H:i:s', $frequencia->entrada->format('H:i:s'));
            $saida = $entrada->addMinutes((int) round($jornada * 60));

            $frequencia->saida = $saida->format('H:i:s');
            $frequencia->horas = $jornada;
            $frequencia->saida_automatica = true;
            $frequencia->save();
        }

        return $abertos->count();
    }
}
