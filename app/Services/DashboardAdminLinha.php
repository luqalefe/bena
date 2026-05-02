<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;

final readonly class DashboardAdminLinha
{
    public function __construct(
        public Estagiario $estagiario,
        public float $horasMes,
        public int $diasBatidos,
        public bool $assinadoEstagiario,
        public bool $assinadoSupervisor,
    ) {}

    public function liberadaParaRh(): bool
    {
        return $this->assinadoEstagiario && $this->assinadoSupervisor;
    }
}
