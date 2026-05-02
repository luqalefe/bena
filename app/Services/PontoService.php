<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;
use App\Models\Frequencia;
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
}
