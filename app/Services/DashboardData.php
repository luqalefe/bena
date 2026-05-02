<?php

declare(strict_types=1);

namespace App\Services;

/**
 * DTO retornado pelo DashboardService. Tudo readonly — view só lê.
 */
final readonly class DashboardData
{
    public function __construct(
        public string $statusHoje,
        public ?string $statusDescricao = null,
        public float $horasMes = 0.0,
        public int $diasBatidos = 0,
        public string $mesAnoExtenso = '',
    ) {}
}
