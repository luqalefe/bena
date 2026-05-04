<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Frequencia;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardAdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Cria o user-admin com tutorial_visto_em preenchido pra evitar
        // o redirect do middleware EnsureOnboarded.
        Estagiario::factory()->create(['username' => 'rh.admin']);
    }

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return [
            'Remote-User' => 'rh.admin',
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

    public function test_estagiario_comum_em_admin_recebe_403(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->get('/admin')
            ->assertStatus(403);
    }

    public function test_admin_ve_lista_de_estagiarios_ativos(): void
    {
        Estagiario::factory()->create([
            'username' => 'ana.ativa',
            'nome' => 'Ana Ativa',
            'lotacao' => 'SECEP',
            'ativo' => true,
        ]);

        $response = $this->withHeaders($this->adminHeaders())->get('/admin');

        $response->assertStatus(200)
            ->assertSee('Ana Ativa')
            ->assertSee('SECEP');
    }

    public function test_dashboard_omite_estagiarios_inativos(): void
    {
        Estagiario::factory()->inativo()->create([
            'username' => 'inativo.bruno',
            'nome' => 'Bruno Inativo',
            'lotacao' => 'CTI',
        ]);

        $response = $this->withHeaders($this->adminHeaders())->get('/admin');

        $response->assertStatus(200);
        $response->assertDontSee('Bruno Inativo');
    }

    public function test_dashboard_mostra_horas_e_dias_do_mes_corrente_por_estagiario(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $ana = Estagiario::factory()->create([
            'nome' => 'Ana Horas',
            'lotacao' => 'SECEP',
        ]);

        Frequencia::create([
            'estagiario_id' => $ana->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);
        Frequencia::create([
            'estagiario_id' => $ana->id,
            'data' => '2026-05-05',
            'entrada' => '09:00:00',
            'saida' => '14:30:00',
            'horas' => 5.50,
        ]);

        $response = $this->withHeaders($this->adminHeaders())->get('/admin');

        $response->assertSee('Ana Horas')
            ->assertSee('10,50')   // total de horas no mês
            ->assertSee('2 dias'); // dias batidos
    }

    public function test_dashboard_filtra_por_lotacao(): void
    {
        Estagiario::factory()->create([
            'nome' => 'Carla SECEP',
            'lotacao' => 'SECEP',
        ]);
        Estagiario::factory()->create([
            'nome' => 'Diego CTI',
            'lotacao' => 'CTI',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/admin?lotacao=SECEP');

        $response->assertStatus(200);
        $response->assertSee('Carla SECEP');
        $response->assertDontSee('Diego CTI');
    }

    public function test_dashboard_aceita_seletor_de_mes_ano_para_visualizacao_historica(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $ana = Estagiario::factory()->create(['nome' => 'Ana Histórica']);

        // Frequência de abril/2026 (mês passado)
        Frequencia::create([
            'estagiario_id' => $ana->id,
            'data' => '2026-04-10',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);

        // Acessando o mês corrente (maio) → 0 horas
        $atual = $this->withHeaders($this->adminHeaders())->get('/admin');
        $atual->assertSee('0,00');

        // Mudando pra abril → vê as 5h
        $abril = $this->withHeaders($this->adminHeaders())->get('/admin?ano=2026&mes=4');
        $abril->assertSee('5,00');
    }

    public function test_dashboard_mostra_badges_de_assinatura_estagiario_e_supervisor(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $ana = Estagiario::factory()->create([
            'username' => 'ana.test',
            'nome' => 'Ana Test',
            'lotacao' => 'SECEP',
        ]);

        Assinatura::create([
            'estagiario_id' => $ana->id,
            'ano' => 2026,
            'mes' => 5,
            'papel' => Assinatura::PAPEL_ESTAGIARIO,
            'assinante_username' => 'ana.test',
            'snapshot' => '{}',
            'hash' => str_repeat('a', 64),
            'assinado_em' => now(),
        ]);

        $response = $this->withHeaders($this->adminHeaders())->get('/admin');

        $response->assertStatus(200);
        $response->assertSeeInOrder(['estagiário', '✓', 'supervisor', '✗'], false);
    }

    public function test_dashboard_mostra_alerta_tce_vencendo(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        Estagiario::factory()->create([
            'username' => 'tce.proximo',
            'nome' => 'TCE Proximo',
            'inicio_estagio' => '2025-06-01',
            'fim_estagio' => '2026-05-25', // 10 dias
        ]);

        $response = $this->withHeaders($this->adminHeaders())->get('/admin');

        $response->assertSee('TCE Proximo');
        $response->assertSee('TCE vence em até 30 dias.', false);
    }

    public function test_dashboard_filtra_por_alerta_tce_vencendo(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        Estagiario::factory()->create([
            'username' => 'tce.proximo',
            'nome' => 'TCE Proximo',
            'inicio_estagio' => '2025-06-01',
            'fim_estagio' => '2026-05-25',
        ]);
        Estagiario::factory()->create([
            'username' => 'tce.tranquilo',
            'nome' => 'TCE Tranquilo',
            'inicio_estagio' => '2026-01-01',
            'fim_estagio' => '2027-12-31',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/admin?alerta=tce_vencendo');

        $response->assertSee('TCE Proximo');
        $response->assertDontSee('TCE Tranquilo');
    }

    public function test_dashboard_estagiario_sem_alertas_nao_mostra_badge(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        Estagiario::factory()->create([
            'username' => 'tudo.certo',
            'nome' => 'Tudo Certo',
            'inicio_estagio' => '2026-01-01',
            'fim_estagio' => '2027-12-31',
            'horas_diarias' => 5.00,
        ]);

        $response = $this->withHeaders($this->adminHeaders())->get('/admin');

        $response->assertSee('Tudo Certo');
        $response->assertDontSee('TCE vence em até 30 dias.', false);
        $response->assertDontSee('Recesso anual pendente', false);
    }

    public function test_dashboard_filtra_apenas_liberadas_para_rh(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $liberada = Estagiario::factory()->create([
            'username' => 'lib.test',
            'nome' => 'Liberada Teste',
        ]);
        $aberta = Estagiario::factory()->create([
            'username' => 'aberta.test',
            'nome' => 'Aberta Teste',
        ]);

        foreach ([Assinatura::PAPEL_ESTAGIARIO, Assinatura::PAPEL_SUPERVISOR] as $papel) {
            Assinatura::create([
                'estagiario_id' => $liberada->id,
                'ano' => 2026,
                'mes' => 5,
                'papel' => $papel,
                'assinante_username' => 'qualquer',
                'snapshot' => '{}',
                'hash' => str_repeat('b', 64),
                'assinado_em' => now(),
            ]);
        }

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/admin?liberadas=1');

        $response->assertSee('Liberada Teste');
        $response->assertDontSee('Aberta Teste');
    }
}
