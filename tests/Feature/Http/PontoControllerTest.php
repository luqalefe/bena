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

    public function test_post_entrada_via_json_retorna_201(): void
    {
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->postJson('/ponto/entrada');

        $response->assertStatus(201);
        $this->assertDatabaseHas('frequencias', [
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04 00:00:00',
        ]);
    }

    public function test_post_entrada_via_form_redireciona_para_sucesso_com_horario(): void
    {
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->post('/ponto/entrada')
            ->assertRedirect(route('ponto.sucesso'))
            ->assertSessionHas('ponto_acao', 'entrada')
            ->assertSessionHas('ponto_horario', '10:30');
    }

    public function test_post_entrada_sem_autenticacao_retorna_401(): void
    {
        $this->post('/ponto/entrada')->assertStatus(401);
    }

    public function test_post_entrada_em_feriado_via_json_retorna_422(): void
    {
        Feriado::create([
            'data' => '2026-05-04 00:00:00',
            'descricao' => 'Feriado teste',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->postJson('/ponto/entrada')
            ->assertStatus(422)
            ->assertJsonPath('message', 'Hoje não é dia útil.');
    }

    public function test_post_entrada_em_feriado_via_form_volta_com_flash_erro(): void
    {
        Feriado::create([
            'data' => '2026-05-04 00:00:00',
            'descricao' => 'Feriado teste',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->post('/ponto/entrada')
            ->assertRedirect()
            ->assertSessionHas('erro', 'Hoje não é dia útil.');
    }

    public function test_post_saida_via_json_atualiza_frequencia(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        $headers = ['Remote-User' => $estagiario->username, 'Remote-Groups' => 'estagiarios'];
        $this->withHeaders($headers)->postJson('/ponto/entrada')->assertStatus(201);

        Carbon::setTestNow('2026-05-04 14:30:00');
        $response = $this->withHeaders($headers)->postJson('/ponto/saida');

        $response->assertStatus(200);
        $this->assertDatabaseHas('frequencias', [
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04 00:00:00',
            'horas' => '5.00',
        ]);
    }

    public function test_post_saida_via_form_redireciona_com_horario_e_horas(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        $headers = ['Remote-User' => $estagiario->username, 'Remote-Groups' => 'estagiarios'];
        $this->withHeaders($headers)->post('/ponto/entrada');

        Carbon::setTestNow('2026-05-04 14:30:00');
        $this->withHeaders($headers)->post('/ponto/saida')
            ->assertRedirect(route('ponto.sucesso'))
            ->assertSessionHas('ponto_acao', 'saida')
            ->assertSessionHas('ponto_horario', '14:30')
            ->assertSessionHas('ponto_horas', '5,00');
    }

    public function test_get_sucesso_sem_flash_redireciona_para_dashboard(): void
    {
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get(route('ponto.sucesso'))
            ->assertRedirect(route('dashboard'));
    }

    public function test_get_sucesso_com_flash_renderiza_view(): void
    {
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->withSession([
            'ponto_acao' => 'entrada',
            'ponto_horario' => '09:30',
        ])->get(route('ponto.sucesso'))
            ->assertStatus(200)
            ->assertSee('Entrada registrada')
            ->assertSee('09:30')
            ->assertSee('Voltar ao dashboard');
    }
}
