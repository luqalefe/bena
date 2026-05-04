<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Estagiario;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorTest extends TestCase
{
    use RefreshDatabase;

    public function test_cria_supervisor_com_nome_obrigatorio(): void
    {
        $supervisor = Supervisor::create([
            'nome' => 'Maria Francisca da Conceição Ferreira',
        ]);

        $this->assertNotNull($supervisor->id);
        $this->assertSame('Maria Francisca da Conceição Ferreira', $supervisor->fresh()->nome);
        $this->assertNull($supervisor->fresh()->email);
        $this->assertNull($supervisor->fresh()->username);
        $this->assertTrue((bool) $supervisor->fresh()->ativo);
    }

    public function test_supervisor_aceita_email_e_username_opcionais(): void
    {
        $supervisor = Supervisor::create([
            'nome' => 'Edilson Duarte Lima Júnior',
            'email' => 'edilson.junior@tre-ac.jus.br',
            'username' => 'edilson.junior',
            'lotacao' => '9ª ZONA',
        ]);

        $this->assertSame('edilson.junior', $supervisor->fresh()->username);
        $this->assertSame('edilson.junior@tre-ac.jus.br', $supervisor->fresh()->email);
        $this->assertSame('9ª ZONA', $supervisor->fresh()->lotacao);
    }

    public function test_supervisor_tem_muitos_estagiarios(): void
    {
        $supervisor = Supervisor::create(['nome' => 'Daniele Carlos de Oliveira Nunes']);

        Estagiario::factory()->count(3)->create(['supervisor_id' => $supervisor->id]);

        $this->assertCount(3, $supervisor->fresh()->estagiarios);
    }
}
