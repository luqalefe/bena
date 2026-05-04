<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Estagiario;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupervisorCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return [
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ];
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(): array
    {
        return [
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
        ];
    }

    public function test_estagiario_em_index_recebe_403(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->get('/admin/supervisores')
            ->assertStatus(403);
    }

    public function test_estagiario_em_post_create_recebe_403(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->post('/admin/supervisores', ['nome' => 'X'])
            ->assertStatus(403);

        $this->assertDatabaseCount('supervisores', 0);
    }

    public function test_admin_lista_supervisores_ordenados_por_nome(): void
    {
        Supervisor::factory()->create(['nome' => 'Zilma Rocha', 'ativo' => true]);
        Supervisor::factory()->create(['nome' => 'Aieza Bandeira', 'ativo' => true]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/admin/supervisores')
            ->assertStatus(200);

        $body = (string) $response->getContent();
        $posAieza = strpos($body, 'Aieza Bandeira');
        $posZilma = strpos($body, 'Zilma Rocha');

        $this->assertNotFalse($posAieza);
        $this->assertNotFalse($posZilma);
        $this->assertLessThan($posZilma, $posAieza, 'Aieza deveria aparecer antes de Zilma (ordem alfabética).');
    }

    public function test_admin_acessa_form_de_criar(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get('/admin/supervisores/criar')
            ->assertStatus(200)
            ->assertSee('name="nome"', false)
            ->assertSee('name="email"', false)
            ->assertSee('name="username"', false)
            ->assertSee('name="lotacao"', false);
    }

    public function test_admin_cria_supervisor_apenas_com_nome(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->post('/admin/supervisores', [
                'nome' => 'Daniele Carlos de Oliveira Nunes',
            ]);

        $response->assertRedirect(route('admin.supervisores.index'));
        $response->assertSessionHas('sucesso');

        $this->assertDatabaseHas('supervisores', [
            'nome' => 'Daniele Carlos de Oliveira Nunes',
            'ativo' => 1,
        ]);
    }

    public function test_validacao_nome_obrigatorio(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->post('/admin/supervisores', ['nome' => ''])
            ->assertSessionHasErrors('nome');

        $this->assertDatabaseCount('supervisores', 0);
    }

    public function test_validacao_username_unico(): void
    {
        Supervisor::factory()->create(['username' => 'aieza.bandeira']);

        $this->withHeaders($this->adminHeaders())
            ->post('/admin/supervisores', [
                'nome' => 'Aieza Outra',
                'username' => 'aieza.bandeira',
            ])
            ->assertSessionHasErrors('username');

        $this->assertDatabaseCount('supervisores', 1);
    }

    public function test_admin_edita_supervisor(): void
    {
        $supervisor = Supervisor::factory()->create([
            'nome' => 'Nome Original',
            'username' => null,
            'email' => null,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->put("/admin/supervisores/{$supervisor->id}", [
                'nome' => 'Nome Atualizado',
                'email' => 'nome.atualizado@tre-ac.jus.br',
                'username' => 'nome.atualizado',
                'lotacao' => 'SECEP',
                'ativo' => '1',
            ]);

        $response->assertRedirect(route('admin.supervisores.index'));

        $this->assertDatabaseHas('supervisores', [
            'id' => $supervisor->id,
            'nome' => 'Nome Atualizado',
            'username' => 'nome.atualizado',
            'lotacao' => 'SECEP',
        ]);
    }

    public function test_edicao_username_unico_ignora_o_proprio(): void
    {
        $supervisor = Supervisor::factory()->create([
            'username' => 'mesmo.user',
            'nome' => 'Mesmo User',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->put("/admin/supervisores/{$supervisor->id}", [
                'nome' => 'Mesmo User Renomeado',
                'username' => 'mesmo.user',
            ])
            ->assertSessionHasNoErrors();
    }

    public function test_admin_apaga_supervisor_sem_estagiarios_vinculados(): void
    {
        $supervisor = Supervisor::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->delete("/admin/supervisores/{$supervisor->id}")
            ->assertRedirect(route('admin.supervisores.index'))
            ->assertSessionHas('sucesso');

        $this->assertDatabaseMissing('supervisores', ['id' => $supervisor->id]);
    }

    public function test_apagar_supervisor_com_estagiarios_vinculados_eh_bloqueado(): void
    {
        $supervisor = Supervisor::factory()->create(['nome' => 'Tem Estagiários']);
        Estagiario::factory()->create(['supervisor_id' => $supervisor->id]);

        $this->withHeaders($this->adminHeaders())
            ->delete("/admin/supervisores/{$supervisor->id}")
            ->assertRedirect(route('admin.supervisores.index'))
            ->assertSessionHasErrors('supervisor');

        $this->assertDatabaseHas('supervisores', ['id' => $supervisor->id]);
    }
}
