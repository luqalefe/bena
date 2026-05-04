<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Models\RecessoEstagiario;
use App\Services\ConformidadeService;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConformidadeServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_estagiario_sem_alertas_retorna_array_vazio(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2026-03-01',
            'fim_estagio' => '2027-02-28',
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertSame([], $alertas);
    }

    public function test_alerta_tce_vencendo_quando_fim_estagio_em_30_dias(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-06-01',
            'fim_estagio' => '2026-05-15', // 11 dias
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertContains('tce_vencendo', $alertas);
    }

    public function test_alerta_tce_vencendo_inclui_dia_30_exato(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-06-01',
            'fim_estagio' => '2026-06-03', // exatamente 30 dias
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertContains('tce_vencendo', $alertas);
    }

    public function test_sem_alerta_tce_quando_fim_estagio_em_31_dias(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-06-01',
            'fim_estagio' => '2026-06-04', // 31 dias
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertNotContains('tce_vencendo', $alertas);
    }

    public function test_sem_alerta_tce_quando_fim_estagio_no_passado(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2024-06-01',
            'fim_estagio' => '2026-04-30', // já encerrado
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertNotContains('tce_vencendo', $alertas);
    }

    public function test_alerta_sem_recesso_apos_12_meses_sem_registro(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-04-01', // 13 meses atrás
            'fim_estagio' => '2027-03-31',
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertContains('sem_recesso', $alertas);
    }

    public function test_sem_alerta_recesso_quando_recesso_existe_nos_ultimos_12_meses(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-04-01',
            'fim_estagio' => '2027-03-31',
            'horas_diarias' => 5.00,
        ]);
        RecessoEstagiario::factory()->create([
            'estagiario_id' => $estagiario->id,
            'inicio' => '2025-12-01',
            'fim' => '2025-12-30',
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertNotContains('sem_recesso', $alertas);
    }

    public function test_recesso_futuro_nao_silencia_alerta_sem_recesso(): void
    {
        // Cenário: estagiário com 13 meses de casa, sem recesso já gozado.
        // RH cadastra um recesso para julho/2026 (futuro). O alerta deve
        // continuar disparando — o estagiário ainda não exerceu o direito.
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-04-01',
            'fim_estagio' => '2027-03-31',
            'horas_diarias' => 5.00,
        ]);
        RecessoEstagiario::factory()->create([
            'estagiario_id' => $estagiario->id,
            'inicio' => '2026-07-13', // futuro
            'fim' => '2026-07-31',
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertContains('sem_recesso', $alertas);
    }

    public function test_recesso_em_curso_silencia_alerta_sem_recesso(): void
    {
        // Recesso que começou ontem e termina daqui a 20 dias — já está
        // sendo exercido, não deveria disparar alerta.
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-04-01',
            'fim_estagio' => '2027-03-31',
            'horas_diarias' => 5.00,
        ]);
        RecessoEstagiario::factory()->create([
            'estagiario_id' => $estagiario->id,
            'inicio' => '2026-05-03', // ontem
            'fim' => '2026-05-23',
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertNotContains('sem_recesso', $alertas);
    }

    public function test_sem_alerta_recesso_para_estagio_recente(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-08-01', // 9 meses atrás
            'fim_estagio' => '2027-07-31',
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertNotContains('sem_recesso', $alertas);
    }

    public function test_alerta_jornada_excedida_quando_soma_semanal_passa_do_limite(): void
    {
        // Semana de 04/05/2026 (segunda) a 10/05/2026 (domingo).
        Carbon::setTestNow('2026-05-07 16:00:00'); // quinta
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2026-01-01',
            'fim_estagio' => '2027-12-31',
            'horas_diarias' => 5.00, // limite semanal: 25h
        ]);

        // 6h x 5 dias = 30h (acima de 25h)
        foreach (['2026-05-04', '2026-05-05', '2026-05-06', '2026-05-07', '2026-05-08'] as $dia) {
            Frequencia::create([
                'estagiario_id' => $estagiario->id,
                'data' => $dia,
                'entrada' => '09:00:00',
                'saida' => '15:00:00',
                'horas' => 6.0,
            ]);
        }

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertContains('jornada_excedida', $alertas);
    }

    public function test_sem_alerta_jornada_quando_soma_semanal_dentro_do_limite(): void
    {
        Carbon::setTestNow('2026-05-07 16:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2026-01-01',
            'fim_estagio' => '2027-12-31',
            'horas_diarias' => 5.00, // limite semanal: 25h
        ]);

        foreach (['2026-05-04', '2026-05-05', '2026-05-06', '2026-05-07'] as $dia) {
            Frequencia::create([
                'estagiario_id' => $estagiario->id,
                'data' => $dia,
                'entrada' => '09:00:00',
                'saida' => '14:00:00',
                'horas' => 5.0,
            ]);
        }

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertNotContains('jornada_excedida', $alertas);
    }

    public function test_jornada_excedida_ignora_horas_de_outras_semanas(): void
    {
        // Semana corrente: 04/05 a 10/05/2026.
        Carbon::setTestNow('2026-05-07 16:00:00');
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2026-01-01',
            'fim_estagio' => '2027-12-31',
            'horas_diarias' => 5.00,
        ]);

        // Semana anterior — não conta.
        foreach (['2026-04-27', '2026-04-28', '2026-04-29', '2026-04-30', '2026-05-01'] as $dia) {
            Frequencia::create([
                'estagiario_id' => $estagiario->id,
                'data' => $dia,
                'entrada' => '09:00:00',
                'saida' => '17:00:00',
                'horas' => 8.0,
            ]);
        }
        // Semana atual: só 4h.
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '13:00:00',
            'horas' => 4.0,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario($estagiario);

        $this->assertNotContains('jornada_excedida', $alertas);
    }

    public function test_descricao_amigavel_de_cada_alerta(): void
    {
        $svc = app(ConformidadeService::class);

        $this->assertStringContainsString('30 dias', $svc->descricao('tce_vencendo'));
        $this->assertStringContainsString('Recesso', $svc->descricao('sem_recesso'));
        $this->assertStringContainsString('Jornada', $svc->descricao('jornada_excedida'));
    }

    public function test_alertas_para_estagiario_com_data_fixa(): void
    {
        // Ao receber CarbonImmutable explícito, ignora Carbon::now().
        $estagiario = Estagiario::factory()->create([
            'inicio_estagio' => '2025-06-01',
            'fim_estagio' => '2026-05-15',
            'horas_diarias' => 5.00,
        ]);

        $alertas = app(ConformidadeService::class)->alertasParaEstagiario(
            $estagiario,
            CarbonImmutable::parse('2026-05-04')
        );

        $this->assertContains('tce_vencendo', $alertas);
    }
}
