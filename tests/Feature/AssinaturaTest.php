<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Frequencia;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssinaturaTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function estagiarioHeaders(string $username = 'lucas.dev'): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'estagiarios'];
    }

    /** @return array<string, string> */
    private function supervisorHeaders(string $username = 'lucas.supervisor'): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'supervisores'];
    }

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return ['Remote-User' => 'rh.admin', 'Remote-Groups' => 'admin'];
    }

    private function frequenciaSimples(Estagiario $e, string $data = '2026-04-10'): Frequencia
    {
        return Frequencia::create([
            'estagiario_id' => $e->id,
            'data' => $data,
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);
    }

    public function test_estagiario_assina_propria_folha_de_mes_passado(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        $lucas = Estagiario::factory()->create(['username' => 'lucas.dev']);
        $this->frequenciaSimples($lucas);

        $response = $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/assinar');

        $response->assertRedirect(route('frequencia.show', ['ano' => 2026, 'mes' => 4]));
        $response->assertSessionHas('sucesso');

        $this->assertDatabaseHas('assinaturas', [
            'estagiario_id' => $lucas->id,
            'ano' => 2026,
            'mes' => 4,
            'papel' => Assinatura::PAPEL_ESTAGIARIO,
            'assinante_username' => 'lucas.dev',
        ]);
    }

    public function test_estagiario_nao_pode_assinar_mes_futuro(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/6/assinar')
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('assinaturas', 0);
    }

    public function test_estagiario_nao_assina_folha_de_outro(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        Estagiario::factory()->create(['username' => 'lucas.dev']);
        $outro = Estagiario::factory()->create(['username' => 'outra.pessoa']);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/assinar?estagiario=outra.pessoa')
            ->assertStatus(403);

        $this->assertDatabaseCount('assinaturas', 0);
    }

    public function test_estagiario_nao_assina_duas_vezes(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/assinar');
        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/assinar')
            ->assertSessionHasErrors();

        $this->assertDatabaseCount('assinaturas', 1);
    }

    public function test_supervisor_contra_assina_folha_do_seu_estagiario_apos_estagiario_ter_assinado(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        $lucas = Estagiario::factory()->create([
            'username' => 'lucas.dev',
            'supervisor_username' => 'lucas.supervisor',
        ]);
        $this->frequenciaSimples($lucas);

        // Estagiário assina primeiro
        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/assinar')
            ->assertRedirect();

        // Supervisor contra-assina
        $this->withHeaders($this->supervisorHeaders('lucas.supervisor'))
            ->post('/frequencia/2026/4/contra-assinar?estagiario=lucas.dev')
            ->assertRedirect();

        $this->assertDatabaseHas('assinaturas', [
            'papel' => Assinatura::PAPEL_SUPERVISOR,
            'assinante_username' => 'lucas.supervisor',
        ]);
    }

    public function test_supervisor_nao_pode_contra_assinar_antes_do_estagiario(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        Estagiario::factory()->create([
            'username' => 'lucas.dev',
            'supervisor_username' => 'lucas.supervisor',
        ]);

        $this->withHeaders($this->supervisorHeaders('lucas.supervisor'))
            ->post('/frequencia/2026/4/contra-assinar?estagiario=lucas.dev')
            ->assertSessionHasErrors();

        $this->assertDatabaseMissing('assinaturas', ['papel' => Assinatura::PAPEL_SUPERVISOR]);
    }

    public function test_supervisor_nao_contra_assina_de_outro_estagiario(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        Estagiario::factory()->create([
            'username' => 'ana.dev',
            'supervisor_username' => 'outro.supervisor',
        ]);

        $this->withHeaders($this->supervisorHeaders('lucas.supervisor'))
            ->post('/frequencia/2026/4/contra-assinar?estagiario=ana.dev')
            ->assertStatus(403);
    }

    public function test_admin_nao_pode_assinar_nem_contra_assinar(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $this->withHeaders($this->adminHeaders())
            ->post('/frequencia/2026/4/assinar?estagiario=lucas.dev')
            ->assertStatus(403);

        $this->withHeaders($this->adminHeaders())
            ->post('/frequencia/2026/4/contra-assinar?estagiario=lucas.dev')
            ->assertStatus(403);
    }

    public function test_supervisor_de_si_mesmo_consegue_contra_assinar(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        $lucas = Estagiario::factory()->create([
            'username' => 'lucas.dev',
            'supervisor_username' => 'lucas.dev', // auto-supervisor
        ]);
        $this->frequenciaSimples($lucas);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/assinar')
            ->assertRedirect();

        $response = $this->withHeaders($this->supervisorHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/contra-assinar?estagiario=lucas.dev');

        $response->assertRedirect();
        $this->assertDatabaseHas('assinaturas', [
            'estagiario_id' => $lucas->id,
            'papel' => Assinatura::PAPEL_SUPERVISOR,
            'assinante_username' => 'lucas.dev',
        ]);
    }

    public function test_view_mostra_hash_truncado_e_data_apos_assinatura(): void
    {
        Carbon::setTestNow('2026-05-10 10:00:00');

        $lucas = Estagiario::factory()->create(['username' => 'lucas.dev']);
        $this->frequenciaSimples($lucas);
        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->post('/frequencia/2026/4/assinar');

        $assinatura = Assinatura::firstOrFail();

        $response = $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/4');

        $response->assertSee(substr($assinatura->hash, 0, 12));
        $response->assertSee('Assinada como estagiário');
    }
}
