<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Estagiario;
use App\Models\Frequencia;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VerFolhaDeOutroTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return [
            'Remote-User' => 'rh.admin',
            'Remote-Groups' => 'admin',
        ];
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(string $username = 'lucas.dev'): array
    {
        return [
            'Remote-User' => $username,
            'Remote-Groups' => 'estagiarios',
        ];
    }

    public function test_admin_passando_username_ve_folha_do_estagiario_alvo(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $alvo = Estagiario::factory()->create([
            'username' => 'ana.alvo',
            'nome' => 'Ana Alvo',
        ]);
        Frequencia::create([
            'estagiario_id' => $alvo->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/frequencia/2026/5?estagiario=ana.alvo');

        $response->assertStatus(200)
            ->assertSee('Ana Alvo')
            ->assertSee('09:00')
            ->assertSee('14:00');
    }

    public function test_admin_sem_username_ve_propria_folha(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/frequencia/2026/5');

        $response->assertStatus(200);
    }

    public function test_admin_passando_username_inexistente_recebe_404(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get('/frequencia/2026/5?estagiario=nao.existe')
            ->assertStatus(404);
    }

    public function test_estagiario_comum_passando_username_de_outro_recebe_403(): void
    {
        Estagiario::factory()->create(['username' => 'outra.pessoa']);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/5?estagiario=outra.pessoa')
            ->assertStatus(403);
    }

    public function test_estagiario_comum_passando_proprio_username_funciona(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/5?estagiario=lucas.dev')
            ->assertStatus(200);
    }

    public function test_view_em_modo_admin_indica_que_esta_vendo_folha_de_outro(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        Estagiario::factory()->create([
            'username' => 'ana.alvo',
            'nome' => 'Ana Alvo',
            'lotacao' => 'SECEP',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/frequencia/2026/5?estagiario=ana.alvo');

        $response->assertSee('Visualizando folha de Ana Alvo');
        $response->assertSee('SECEP');
    }

    public function test_navegacao_anterior_e_proximo_preservam_query_estagiario(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        Estagiario::factory()->create(['username' => 'ana.alvo', 'nome' => 'Ana Alvo']);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/frequencia/2026/5?estagiario=ana.alvo');

        // Anterior aponta pra abril/2026 com a query preservada
        $response->assertSee('/frequencia/2026/4?estagiario=ana.alvo', false);
    }
}
