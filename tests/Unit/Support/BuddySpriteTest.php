<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\BuddySprite;
use PHPUnit\Framework\TestCase;

class BuddySpriteTest extends TestCase
{
    private string $diretorio;

    private BuddySprite $sprite;

    protected function setUp(): void
    {
        $this->diretorio = sys_get_temp_dir().'/buddy-sprite-'.uniqid();
        mkdir($this->diretorio, 0o755, true);
        $this->sprite = new BuddySprite($this->diretorio, '/images/buddies');
    }

    protected function tearDown(): void
    {
        foreach (glob($this->diretorio.'/*') ?: [] as $arquivo) {
            unlink($arquivo);
        }
        @rmdir($this->diretorio);
    }

    public function test_retorna_null_quando_arquivo_nao_existe(): void
    {
        $this->assertNull($this->sprite->caminho('coruja'));
    }

    public function test_retorna_url_publica_quando_arquivo_existe(): void
    {
        file_put_contents($this->diretorio.'/coruja.png', 'fake');

        $this->assertSame(
            '/images/buddies/coruja.png',
            $this->sprite->caminho('coruja'),
        );
    }

    public function test_url_base_personalizada_e_respeitada(): void
    {
        $sprite = new BuddySprite($this->diretorio, '/assets/sprites');
        file_put_contents($this->diretorio.'/bortoli.png', 'fake');

        $this->assertSame(
            '/assets/sprites/bortoli.png',
            $sprite->caminho('bortoli'),
        );
    }
}
