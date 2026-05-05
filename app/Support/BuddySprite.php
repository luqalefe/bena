<?php

declare(strict_types=1);

namespace App\Support;

final readonly class BuddySprite
{
    public function __construct(
        private string $diretorio,
        private string $urlBase,
    ) {}

    public function caminho(string $tipo): ?string
    {
        $arquivo = $tipo.'.png';

        if (! is_file($this->diretorio.'/'.$arquivo)) {
            return null;
        }

        return rtrim($this->urlBase, '/').'/'.$arquivo;
    }
}
