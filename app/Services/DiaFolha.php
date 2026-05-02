<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Frequencia;
use Carbon\CarbonImmutable;

final readonly class DiaFolha
{
    public function __construct(
        public CarbonImmutable $data,
        public string $tipo,
        public ?Frequencia $frequencia = null,
        public ?string $descricaoFeriado = null,
    ) {}
}
