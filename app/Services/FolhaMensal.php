<?php

declare(strict_types=1);

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

final readonly class FolhaMensal
{
    /** @param Collection<int, DiaFolha> $dias */
    public function __construct(
        public int $ano,
        public int $mes,
        public Collection $dias,
        public float $totalHoras,
    ) {}

    public function tituloExtenso(): string
    {
        return ucfirst(CarbonImmutable::create($this->ano, $this->mes, 1)
            ->translatedFormat('F / Y'));
    }

    /** @return array{ano: int, mes: int} */
    public function mesAnterior(): array
    {
        $anterior = CarbonImmutable::create($this->ano, $this->mes, 1)->subMonth();

        return ['ano' => $anterior->year, 'mes' => $anterior->month];
    }

    /** @return array{ano: int, mes: int} */
    public function proximoMes(): array
    {
        $proximo = CarbonImmutable::create($this->ano, $this->mes, 1)->addMonth();

        return ['ano' => $proximo->year, 'mes' => $proximo->month];
    }

    public function podeNavegarParaProximo(): bool
    {
        $atual = CarbonImmutable::create($this->ano, $this->mes, 1);
        $corrente = CarbonImmutable::now()->startOfMonth();

        return $atual->lessThan($corrente);
    }
}
