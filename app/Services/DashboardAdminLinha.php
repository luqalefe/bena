<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Estagiario;

final readonly class DashboardAdminLinha
{
    /**
     * @param  list<string>  $alertas  Códigos de alerta de conformidade (ConformidadeService::ALERTA_*)
     */
    public function __construct(
        public Estagiario $estagiario,
        public float $horasMes,
        public int $diasBatidos,
        public bool $assinadoEstagiario,
        public bool $assinadoSupervisor,
        public array $alertas = [],
    ) {}

    public function liberadaParaRh(): bool
    {
        return $this->assinadoEstagiario && $this->assinadoSupervisor;
    }
}
