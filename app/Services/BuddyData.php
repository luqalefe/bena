<?php

declare(strict_types=1);

namespace App\Services;

final readonly class BuddyData
{
    public function __construct(
        public string $tipo,
        public string $emoji,
        public string $nome,
        public string $frase,
        public ?string $sprite = null,
    ) {}
}
