<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Feriado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeriadoEdicaoTest extends TestCase
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

    private function criarFeriado(array $atributos = []): Feriado
    {
        return Feriado::create(array_merge([
            'data' => '2026-09-07',
            'descricao' => 'Independência',
            'tipo' => 'nacional',
            'recorrente' => false,
        ], $atributos));
    }

    public function test_estagiario_em_form_de_edicao_recebe_403(): void
    {
        $f = $this->criarFeriado();

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('admin.feriados.edit', $f))
            ->assertStatus(403);
    }

    public function test_admin_ve_form_de_edicao_pre_preenchido(): void
    {
        $f = $this->criarFeriado(['descricao' => 'Independência do Brasil']);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.feriados.edit', $f))
            ->assertStatus(200)
            ->assertSee('value="Independência do Brasil"', false)
            ->assertSee('value="2026-09-07"', false);
    }

    public function test_admin_atualiza_feriado_e_redireciona(): void
    {
        $f = $this->criarFeriado(['descricao' => 'Errado']);

        $response = $this->withHeaders($this->adminHeaders())
            ->put(route('admin.feriados.update', $f), [
                'data' => '2026-09-07',
                'descricao' => 'Independência do Brasil',
                'tipo' => 'nacional',
                'recorrente' => '1',
            ]);

        $response->assertRedirect(route('admin.feriados.index'));
        $response->assertSessionHas('sucesso');
        $this->assertSame('Independência do Brasil', $f->fresh()->descricao);
        $this->assertTrue($f->fresh()->recorrente);
    }

    public function test_admin_remove_feriado_e_redireciona(): void
    {
        $f = $this->criarFeriado();

        $response = $this->withHeaders($this->adminHeaders())
            ->delete(route('admin.feriados.destroy', $f));

        $response->assertRedirect(route('admin.feriados.index'));
        $response->assertSessionHas('sucesso');
        $this->assertNull(Feriado::find($f->id));
    }

    public function test_estagiario_nao_pode_remover_feriado(): void
    {
        $f = $this->criarFeriado();

        $this->withHeaders($this->estagiarioHeaders())
            ->delete(route('admin.feriados.destroy', $f))
            ->assertStatus(403);

        $this->assertNotNull(Feriado::find($f->id));
    }

    public function test_get_form_de_remocao_exibe_aviso_quando_ha_assinatura_no_mes(): void
    {
        $f = $this->criarFeriado(['data' => '2026-04-21', 'descricao' => 'Tiradentes']);
        $estagiario = Estagiario::factory()->create();
        Assinatura::create([
            'estagiario_id' => $estagiario->id,
            'ano' => 2026,
            'mes' => 4,
            'papel' => Assinatura::PAPEL_ESTAGIARIO,
            'assinante_username' => $estagiario->username,
            'snapshot' => '{}',
            'hash' => str_repeat('a', 64),
            'assinado_em' => now(),
        ]);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.feriados.confirmDestroy', $f))
            ->assertStatus(200)
            ->assertSeeInOrder(['1 folhas', 'hash', 'invalidad'], false);
    }
}
