<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estagiario;
use App\Models\Feriado;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioAnualTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Pré-cria os users simulados pelos headers, com tutorial_visto_em
        // preenchido pelo factory, para evitar redirect de EnsureOnboarded.
        Estagiario::factory()->create(['username' => 'rh.admin']);
        Estagiario::factory()->create(['username' => 'lucas.supervisor']);
        Estagiario::factory()->create(['username' => 'lucas.dev']);
    }

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return ['Remote-User' => 'rh.admin', 'Remote-Groups' => 'admin'];
    }

    /** @return array<string, string> */
    private function supervisorHeaders(): array
    {
        return ['Remote-User' => 'lucas.supervisor', 'Remote-Groups' => 'supervisores'];
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(): array
    {
        return ['Remote-User' => 'lucas.dev', 'Remote-Groups' => 'estagiarios'];
    }

    public function test_calendario_retorna_200_para_usuario_autenticado(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get(route('calendario.index'))
            ->assertStatus(200);

        $this->withHeaders($this->supervisorHeaders())
            ->get(route('calendario.index'))
            ->assertStatus(200);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.index'))
            ->assertStatus(200);
    }

    public function test_calendario_index_renderiza_mes_atual_por_padrao(): void
    {
        Carbon::setTestNow('2026-08-15 10:00:00');

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.index'))
            ->assertStatus(200)
            ->assertSee('agosto 2026', false)
            ->assertSee('bena-mes__card', false)
            ->assertDontSee('bena-cal-grid', false);
    }

    public function test_calendario_index_aceita_ano_e_mes_via_query(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get(route('calendario.index', ['ano' => 2025, 'mes' => 9]))
            ->assertStatus(200)
            ->assertSee('setembro 2025', false)
            ->assertSee('bena-mes__card', false);
    }

    public function test_calendario_exibe_feriado_cadastrado(): void
    {
        Feriado::create([
            'data' => '2026-09-07',
            'descricao' => 'Independência do Brasil',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertSee('Independência do Brasil');
    }

    public function test_calendario_exibe_feriado_recorrente_no_ano_solicitado(): void
    {
        Feriado::create([
            'data' => '2020-12-25',
            'descricao' => 'Natal',
            'tipo' => 'nacional',
            'recorrente' => true,
        ]);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 12]))
            ->assertStatus(200)
            ->assertSee('Natal');
    }

    public function test_calendario_link_edicao_apenas_para_admin(): void
    {
        $feriado = Feriado::create([
            'data' => '2026-09-07',
            'descricao' => 'Independência do Brasil',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $rotaEdit = route('admin.feriados.edit', $feriado);

        $this->withHeaders($this->adminHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertSee($rotaEdit, false);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertDontSee($rotaEdit, false);
    }

    public function test_calendario_admin_ve_dialog_de_adicionar_feriado(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertSee('Adicionar feriado')
            ->assertSee('data-add-feriado', false);
    }

    public function test_calendario_estagiario_nao_ve_dialog_de_adicionar(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertDontSee('data-add-feriado', false);
    }

    public function test_calendario_mes_retorna_200_para_qualquer_grupo(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 5]))
            ->assertStatus(200);

        $this->withHeaders($this->supervisorHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 5]))
            ->assertStatus(200);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 5]))
            ->assertStatus(200);
    }

    public function test_calendario_mes_aborta_404_para_mes_invalido(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 13]))
            ->assertStatus(404);
    }

    public function test_calendario_mes_exibe_feriados_apenas_do_mes(): void
    {
        Feriado::create([
            'data' => '2026-09-07',
            'descricao' => 'Independência do Brasil',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);
        Feriado::create([
            'data' => '2026-11-15',
            'descricao' => 'Proclamação da República',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertSee('Independência do Brasil')
            ->assertDontSee('Proclamação da República');
    }

    public function test_calendario_mes_link_edicao_apenas_para_admin(): void
    {
        $feriado = Feriado::create([
            'data' => '2026-09-07',
            'descricao' => 'Independência do Brasil',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $rotaEdit = route('admin.feriados.edit', $feriado);

        $this->withHeaders($this->adminHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertSee($rotaEdit, false);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('calendario.mes', ['ano' => 2026, 'mes' => 9]))
            ->assertStatus(200)
            ->assertDontSee($rotaEdit, false);
    }
}
