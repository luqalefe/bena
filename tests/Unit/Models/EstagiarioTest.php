<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Estagiario;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstagiarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_estagiario_pertence_a_um_supervisor(): void
    {
        $supervisor = Supervisor::create(['nome' => 'Daniele Carlos de Oliveira Nunes']);
        $estagiario = Estagiario::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->assertSame($supervisor->id, $estagiario->supervisor->id);
        $this->assertSame('Daniele Carlos de Oliveira Nunes', $estagiario->supervisor->nome);
    }

    public function test_estagiario_pode_nao_ter_supervisor_vinculado(): void
    {
        $estagiario = Estagiario::factory()->create(['supervisor_id' => null]);

        $this->assertNull($estagiario->supervisor);
    }

    public function test_estagiario_persiste_instituicao_e_prorrogacao(): void
    {
        $estagiario = Estagiario::factory()->create([
            'instituicao_ensino' => 'IFAC',
            'prorrogacao_inicio' => '2025-07-21',
            'prorrogacao_fim' => '2026-07-21',
        ]);

        $fresh = $estagiario->fresh();

        $this->assertSame('IFAC', $fresh->instituicao_ensino);
        $this->assertSame('2025-07-21', $fresh->prorrogacao_inicio->format('Y-m-d'));
        $this->assertSame('2026-07-21', $fresh->prorrogacao_fim->format('Y-m-d'));
    }
}
