<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Frequencia;
use Carbon\CarbonImmutable;
use DomainException;

class AssinaturaService
{
    /**
     * Snapshot canônico do mês: campos significativos, ordem fixa por
     * data ASC, sem timestamps internos. Hash idempotente desde que os
     * dados das frequências não mudem.
     *
     * @return array{estagiario_id: int, ano: int, mes: int, dias: list<array{data: string, entrada: ?string, saida: ?string, horas: ?string, observacao: ?string}>}
     */
    public function canonicalSnapshot(Estagiario $estagiario, int $ano, int $mes): array
    {
        $frequencias = Frequencia::query()
            ->where('estagiario_id', $estagiario->id)
            ->whereYear('data', $ano)
            ->whereMonth('data', $mes)
            ->orderBy('data')
            ->get();

        $dias = $frequencias->map(fn (Frequencia $f) => [
            'data' => $f->data->format('Y-m-d'),
            'entrada' => $f->entrada?->format('H:i:s'),
            'saida' => $f->saida?->format('H:i:s'),
            'horas' => $f->horas !== null ? (string) $f->horas : null,
            'observacao' => $f->observacao,
        ])->values()->all();

        return [
            'estagiario_id' => (int) $estagiario->id,
            'ano' => $ano,
            'mes' => $mes,
            'dias' => $dias,
        ];
    }

    public function hash(array $snapshot): string
    {
        $json = json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);

        return hash('sha256', $json);
    }

    public function assinar(
        Estagiario $estagiario,
        int $ano,
        int $mes,
        string $papel,
        string $assinante,
        ?string $ip = null,
    ): Assinatura {
        $this->garantirPapelValido($papel);
        $this->garantirNaoAssinadoAinda($estagiario, $ano, $mes, $papel);

        if ($papel === Assinatura::PAPEL_SUPERVISOR) {
            $this->garantirEstagiarioJaAssinou($estagiario, $ano, $mes);
        }

        $snapshot = $this->canonicalSnapshot($estagiario, $ano, $mes);

        return Assinatura::create([
            'estagiario_id' => $estagiario->id,
            'ano' => $ano,
            'mes' => $mes,
            'papel' => $papel,
            'assinante_username' => $assinante,
            'snapshot' => json_encode($snapshot, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            'hash' => $this->hash($snapshot),
            'assinado_em' => CarbonImmutable::now(),
            'ip' => $ip,
        ]);
    }

    /**
     * @return list<array{papel: string, integro: bool, assinatura: Assinatura}>
     */
    public function verificar(Estagiario $estagiario, int $ano, int $mes): array
    {
        $assinaturas = Assinatura::query()
            ->where('estagiario_id', $estagiario->id)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->orderBy('assinado_em')
            ->get();

        $hashAtual = $this->hash($this->canonicalSnapshot($estagiario, $ano, $mes));

        return $assinaturas->map(fn (Assinatura $a) => [
            'papel' => $a->papel,
            'integro' => $a->hash === $hashAtual,
            'assinatura' => $a,
        ])->values()->all();
    }

    public function assinaturaDoMes(Estagiario $estagiario, int $ano, int $mes, string $papel): ?Assinatura
    {
        return Assinatura::query()
            ->where('estagiario_id', $estagiario->id)
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->where('papel', $papel)
            ->first();
    }

    private function garantirPapelValido(string $papel): void
    {
        if (! in_array($papel, [Assinatura::PAPEL_ESTAGIARIO, Assinatura::PAPEL_SUPERVISOR], true)) {
            throw new DomainException("Papel inválido: {$papel}");
        }
    }

    private function garantirNaoAssinadoAinda(Estagiario $estagiario, int $ano, int $mes, string $papel): void
    {
        if ($this->assinaturaDoMes($estagiario, $ano, $mes, $papel) !== null) {
            $rotulo = $papel === Assinatura::PAPEL_ESTAGIARIO ? 'estagiário' : 'supervisor';
            throw new DomainException("Esta folha já foi assinada como {$rotulo}.");
        }
    }

    private function garantirEstagiarioJaAssinou(Estagiario $estagiario, int $ano, int $mes): void
    {
        if ($this->assinaturaDoMes($estagiario, $ano, $mes, Assinatura::PAPEL_ESTAGIARIO) === null) {
            throw new DomainException('O estagiário precisa assinar antes do supervisor.');
        }
    }
}
