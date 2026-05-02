<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Estagiario;
use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PontoControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_entrada_autenticado_cria_frequencia_e_retorna_201(): void
    {
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->post('/ponto/entrada');

        $response->assertStatus(201);
        $this->assertDatabaseHas('frequencias', [
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04 00:00:00',
        ]);
    }

    public function test_post_entrada_sem_autenticacao_retorna_401(): void
    {
        $this->post('/ponto/entrada')->assertStatus(401);
    }

    public function test_post_entrada_em_feriado_retorna_422_com_mensagem(): void
    {
        Feriado::create([
            'data' => '2026-05-04 00:00:00',
            'descricao' => 'Feriado teste',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->post('/ponto/entrada');

        $response->assertStatus(422);
        $response->assertJsonPath('message', 'Hoje não é dia útil.');
    }

    public function test_post_saida_autenticado_atualiza_frequencia(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        $headers = ['Remote-User' => $estagiario->username, 'Remote-Groups' => 'estagiarios'];
        $this->withHeaders($headers)->post('/ponto/entrada')->assertStatus(201);

        Carbon::setTestNow('2026-05-04 14:30:00');
        $response = $this->withHeaders($headers)->post('/ponto/saida');

        $response->assertStatus(200);
        $this->assertDatabaseHas('frequencias', [
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04 00:00:00',
            'horas' => '5.00',
        ]);
    }
}
