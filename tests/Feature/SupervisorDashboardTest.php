<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estagiario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorDashboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cria os users que serão simulados nos headers, com
        // tutorial_visto_em preenchido pra evitar o redirect do
        // middleware EnsureOnboarded.
        Estagiario::factory()->create(['username' => 'lucas.supervisor']);
        Estagiario::factory()->create(['username' => 'rh.admin']);
        Estagiario::factory()->create(['username' => 'lucas.dev']);
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

    /** @return array<string, string> */
    private function estagiarioHeaders(): array
    {
        return ['Remote-User' => 'lucas.dev', 'Remote-Groups' => 'estagiarios'];
    }

    public function test_supervisor_acessa_supervisor_e_ve_seus_estagiarios(): void
    {
        Estagiario::factory()->create([
            'username' => 'ana.minha',
            'nome' => 'Ana Minha',
            'supervisor_username' => 'lucas.supervisor',
        ]);
        Estagiario::factory()->create([
            'username' => 'bruno.outro',
            'nome' => 'Bruno Outro',
            'supervisor_username' => 'outro.supervisor',
        ]);

        $response = $this->withHeaders($this->supervisorHeaders('lucas.supervisor'))
            ->get('/supervisor');

        $response->assertStatus(200);
        $response->assertSee('Ana Minha');
        $response->assertDontSee('Bruno Outro');
    }

    public function test_estagiario_em_supervisor_recebe_403(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->get('/supervisor')
            ->assertStatus(403);
    }

    public function test_admin_em_supervisor_recebe_403(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get('/supervisor')
            ->assertStatus(403);
    }

    public function test_supervisor_sem_estagiarios_ve_mensagem_amigavel(): void
    {
        Estagiario::factory()->create(['username' => 'orfão.supervisor']);

        $response = $this->withHeaders($this->supervisorHeaders('orfão.supervisor'))
            ->get('/supervisor');

        $response->assertStatus(200);
        $response->assertSee('Nenhum estagiário sob sua responsabilidade');
    }
}
