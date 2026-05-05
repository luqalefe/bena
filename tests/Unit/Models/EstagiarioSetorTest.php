<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Estagiario;
use App\Models\Setor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstagiarioSetorTest extends TestCase
{
    use RefreshDatabase;

    public function test_estagiario_belongs_to_setor(): void
    {
        $setor = Setor::create(['sigla' => 'STI', 'ativo' => true]);
        $estagiario = Estagiario::factory()->create(['setor_id' => $setor->id]);

        $this->assertSame('STI', $estagiario->setor->sigla);
    }

    public function test_setor_id_pode_ser_nulo(): void
    {
        $estagiario = Estagiario::factory()->create(['setor_id' => null]);

        $this->assertNull($estagiario->fresh()->setor);
    }

    public function test_factory_default_cria_estagiario_com_setor_existente(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => true]);
        Setor::create(['sigla' => 'SDBD', 'ativo' => true]);

        $estagiario = Estagiario::factory()->create();

        $this->assertNotNull($estagiario->setor);
    }
}
