<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeriadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_estagiario_sem_grupo_admin_recebe_403(): void
    {
        $this->withHeaders([
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
        ])->get('/admin/feriados')->assertStatus(403);
    }

    public function test_admin_acessa_listagem_com_200(): void
    {
        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin/feriados?ano=2026')->assertStatus(200);
    }

    public function test_quando_ano_omitido_usa_ano_corrente(): void
    {
        Carbon::setTestNow('2027-03-15 10:00:00');

        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin/feriados')
            ->assertStatus(200)
            ->assertSee('Feriados 2027');
    }

    public function test_view_exibe_data_descricao_e_tipo_de_cada_feriado(): void
    {
        Feriado::create([
            'data' => '2026-09-07',
            'descricao' => 'Independência do Brasil',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin/feriados?ano=2026')
            ->assertSee('07/09/2026')
            ->assertSee('Independência do Brasil')
            ->assertSee('nacional');
    }

    public function test_view_mostra_uf_para_feriados_estaduais(): void
    {
        Feriado::create([
            'data' => '2026-11-15',
            'descricao' => 'Aniversário do Acre',
            'tipo' => 'estadual',
            'uf' => 'AC',
            'recorrente' => false,
        ]);

        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin/feriados?ano=2026')
            ->assertSee('Aniversário do Acre')
            ->assertSee('AC');
    }

    public function test_view_mostra_badge_recorrente_quando_aplicavel(): void
    {
        Feriado::create([
            'data' => '2020-12-25',
            'descricao' => 'Natal',
            'tipo' => 'nacional',
            'recorrente' => true,
        ]);

        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin/feriados?ano=2026')
            ->assertSee('Natal')
            ->assertSee('recorrente');
    }

    public function test_filtro_por_tipo_restringe_resultado(): void
    {
        Feriado::create(['data' => '2026-09-07', 'descricao' => 'Independência', 'tipo' => 'nacional', 'recorrente' => false]);
        Feriado::create(['data' => '2026-11-15', 'descricao' => 'Aniversário do Acre', 'tipo' => 'estadual', 'uf' => 'AC', 'recorrente' => false]);

        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin/feriados?ano=2026&tipo=estadual')
            ->assertSee('Aniversário do Acre')
            ->assertDontSee('Independência');
    }

    public function test_view_renderiza_filtro_de_tipo_no_topo(): void
    {
        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin/feriados?ano=2026')
            ->assertSee('name="tipo"', false)
            ->assertSee('nacional', false)
            ->assertSee('estadual', false)
            ->assertSee('municipal', false)
            ->assertSee('recesso', false);
    }
}
