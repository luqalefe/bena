<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Auditoria;
use App\Models\Estagiario;
use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_estagiario_em_auditoria_recebe_403(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('admin.auditoria.index'))
            ->assertStatus(403);
    }

    public function test_admin_acessa_auditoria_com_200(): void
    {
        Estagiario::factory()->create(['username' => 'rh.admin']);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.auditoria.index'))
            ->assertStatus(200)
            ->assertSee('Auditoria de ações');
    }

    public function test_bater_entrada_gera_linha_em_auditoria(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00'); // segunda
        $lucas = Estagiario::factory()->create(['username' => 'lucas.dev']);

        $this->withHeaders($this->estagiarioHeaders())
            ->post('/ponto/entrada')
            ->assertRedirect();

        $this->assertDatabaseHas('auditoria', [
            'usuario_username' => 'lucas.dev',
            'acao' => 'frequencia.entrada',
            'entidade' => 'frequencia',
        ]);
    }

    public function test_criar_feriado_gera_linha_em_auditoria(): void
    {
        Estagiario::factory()->create(['username' => 'rh.admin']);

        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'Independência',
                'tipo' => 'nacional',
            ]);

        $this->assertDatabaseHas('auditoria', [
            'usuario_username' => 'rh.admin',
            'acao' => 'feriado.criar',
            'entidade' => 'feriado',
        ]);
    }

    public function test_remover_feriado_gera_linha_em_auditoria(): void
    {
        Estagiario::factory()->create(['username' => 'rh.admin']);
        $feriado = Feriado::create([
            'data' => '2026-09-07', 'descricao' => 'Independência',
            'tipo' => 'nacional', 'recorrente' => false,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->delete(route('admin.feriados.destroy', $feriado));

        $this->assertDatabaseHas('auditoria', [
            'usuario_username' => 'rh.admin',
            'acao' => 'feriado.remover',
            'entidade' => 'feriado',
            'entidade_id' => (string) $feriado->id,
        ]);
    }

    public function test_filtro_por_usuario_restringe_resultado(): void
    {
        Estagiario::factory()->create(['username' => 'rh.admin']);
        Auditoria::create([
            'usuario_username' => 'lucas.dev', 'acao' => 'frequencia.entrada',
            'entidade' => 'frequencia', 'created_at' => now(),
        ]);
        Auditoria::create([
            'usuario_username' => 'paula.dev', 'acao' => 'frequencia.entrada',
            'entidade' => 'frequencia', 'created_at' => now(),
        ]);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.auditoria.index', ['usuario' => 'lucas']))
            ->assertSee('lucas.dev')
            ->assertDontSee('paula.dev');
    }

    public function test_filtro_por_acao_restringe_resultado(): void
    {
        Estagiario::factory()->create(['username' => 'rh.admin']);
        Auditoria::create([
            'usuario_username' => 'lucas.dev', 'acao' => 'frequencia.entrada',
            'entidade' => 'frequencia', 'created_at' => now(),
        ]);
        Auditoria::create([
            'usuario_username' => 'lucas.dev', 'acao' => 'feriado.criar',
            'entidade' => 'feriado', 'created_at' => now(),
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get(route('admin.auditoria.index', ['acao' => 'feriado.criar']));

        $response->assertSee('feriado.criar');
        // 'frequencia.entrada' não deve aparecer como entrada da tabela
        $response->assertDontSee('frequencia.entrada</td>', false);
    }
}
