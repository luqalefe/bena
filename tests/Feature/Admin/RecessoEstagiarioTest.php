<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Estagiario;
use App\Models\RecessoEstagiario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RecessoEstagiarioTest extends TestCase
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

    /** @return array<string, string> */
    private function supervisorHeaders(): array
    {
        return [
            'Remote-User' => 'super.visor',
            'Remote-Groups' => 'supervisores',
        ];
    }

    public function test_admin_ve_listagem_de_recessos_do_estagiario(): void
    {
        $alvo = Estagiario::factory()->create(['nome' => 'Lucas Dev']);
        RecessoEstagiario::factory()->create([
            'estagiario_id' => $alvo->id,
            'inicio' => '2026-07-13',
            'fim' => '2026-07-31',
            'observacao' => 'Recesso anual remunerado',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.recessos.index', $alvo))
            ->assertOk()
            ->assertSee('13/07/2026')
            ->assertSee('31/07/2026')
            ->assertSee('Recesso anual remunerado');
    }

    public function test_admin_cria_recesso_valido(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->post(route('admin.estagiarios.recessos.store', $alvo), [
                'inicio' => '2026-07-13',
                'fim' => '2026-07-31',
                'observacao' => 'Recesso anual',
            ])
            ->assertRedirect(route('admin.estagiarios.recessos.index', $alvo))
            ->assertSessionHas('sucesso');

        $this->assertDatabaseHas('recessos_estagiario', [
            'estagiario_id' => $alvo->id,
            'observacao' => 'Recesso anual',
        ]);
    }

    public function test_recesso_com_fim_antes_do_inicio_e_rejeitado(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->post(route('admin.estagiarios.recessos.store', $alvo), [
                'inicio' => '2026-07-31',
                'fim' => '2026-07-13',
            ])
            ->assertSessionHasErrors(['fim']);
    }

    public function test_recesso_com_periodo_sobreposto_e_rejeitado(): void
    {
        $alvo = Estagiario::factory()->create();
        RecessoEstagiario::factory()->create([
            'estagiario_id' => $alvo->id,
            'inicio' => '2026-07-13',
            'fim' => '2026-07-31',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->post(route('admin.estagiarios.recessos.store', $alvo), [
                'inicio' => '2026-07-25',
                'fim' => '2026-08-10',
            ])
            ->assertSessionHasErrors(['fim']);
    }

    public function test_recesso_em_periodo_de_outro_estagiario_nao_conflita(): void
    {
        $alvo = Estagiario::factory()->create();
        $outro = Estagiario::factory()->create();
        RecessoEstagiario::factory()->create([
            'estagiario_id' => $outro->id,
            'inicio' => '2026-07-13',
            'fim' => '2026-07-31',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->post(route('admin.estagiarios.recessos.store', $alvo), [
                'inicio' => '2026-07-20',
                'fim' => '2026-08-05',
            ])
            ->assertRedirect(route('admin.estagiarios.recessos.index', $alvo))
            ->assertSessionDoesntHaveErrors();
    }

    public function test_admin_remove_recesso(): void
    {
        $alvo = Estagiario::factory()->create();
        $recesso = RecessoEstagiario::factory()->create([
            'estagiario_id' => $alvo->id,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->delete(route('admin.estagiarios.recessos.destroy', [$alvo, $recesso]))
            ->assertRedirect(route('admin.estagiarios.recessos.index', $alvo))
            ->assertSessionHas('sucesso');

        $this->assertDatabaseMissing('recessos_estagiario', ['id' => $recesso->id]);
    }

    public function test_admin_nao_remove_recesso_de_outro_estagiario(): void
    {
        $alvo = Estagiario::factory()->create();
        $outro = Estagiario::factory()->create();
        $recesso = RecessoEstagiario::factory()->create([
            'estagiario_id' => $outro->id,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->delete(route('admin.estagiarios.recessos.destroy', [$alvo, $recesso]))
            ->assertNotFound();

        $this->assertDatabaseHas('recessos_estagiario', ['id' => $recesso->id]);
    }

    public function test_estagiario_comum_em_index_de_recesso_recebe_403(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('admin.estagiarios.recessos.index', $alvo))
            ->assertStatus(403);
    }

    public function test_supervisor_em_index_de_recesso_recebe_403(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->supervisorHeaders())
            ->get(route('admin.estagiarios.recessos.index', $alvo))
            ->assertStatus(403);
    }

    public function test_estagiario_comum_em_store_de_recesso_recebe_403(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->estagiarioHeaders())
            ->post(route('admin.estagiarios.recessos.store', $alvo), [
                'inicio' => '2026-07-13',
                'fim' => '2026-07-31',
            ])
            ->assertStatus(403);

        $this->assertDatabaseCount('recessos_estagiario', 0);
    }
}
