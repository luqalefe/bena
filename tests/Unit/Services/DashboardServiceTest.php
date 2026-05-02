<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Services\DashboardService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_status_aguardando_entrada_quando_sem_registro_hoje(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->create();

        $resumo = app(DashboardService::class)->montar($estagiario);

        $this->assertSame('aguardando_entrada', $resumo->statusHoje);
    }

    public function test_status_em_andamento_quando_so_entrada(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:30:00',
        ]);
        Carbon::setTestNow('2026-05-04 11:00:00');

        $resumo = app(DashboardService::class)->montar($estagiario);

        $this->assertSame('em_andamento', $resumo->statusHoje);
        $this->assertSame('Em andamento desde 09:30', $resumo->statusDescricao);
    }

    public function test_horas_no_mes_soma_apenas_mes_corrente(): void
    {
        Carbon::setTestNow('2026-05-15 09:00:00');
        $estagiario = Estagiario::factory()->create();

        // Mês corrente — somar
        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-05-04', 'entrada' => '09:00:00', 'saida' => '14:00:00', 'horas' => 5.00]);
        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-05-05', 'entrada' => '09:00:00', 'saida' => '14:30:00', 'horas' => 5.50]);
        // Mês anterior — ignorar
        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-04-30', 'entrada' => '09:00:00', 'saida' => '14:00:00', 'horas' => 5.00]);
        // Outro estagiário — ignorar
        $outro = Estagiario::factory()->create();
        Frequencia::create(['estagiario_id' => $outro->id, 'data' => '2026-05-04', 'entrada' => '09:00:00', 'saida' => '14:00:00', 'horas' => 5.00]);

        $resumo = app(DashboardService::class)->montar($estagiario);

        $this->assertSame(10.50, $resumo->horasMes);
    }

    public function test_dias_batidos_conta_apenas_com_saida_preenchida(): void
    {
        Carbon::setTestNow('2026-05-15 09:00:00');
        $estagiario = Estagiario::factory()->create();

        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-05-04', 'entrada' => '09:00:00', 'saida' => '14:00:00', 'horas' => 5.00]);
        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-05-05', 'entrada' => '09:00:00', 'saida' => '14:00:00', 'horas' => 5.00]);
        // Sem saída — não conta
        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-05-06', 'entrada' => '09:00:00']);

        $resumo = app(DashboardService::class)->montar($estagiario);

        $this->assertSame(2, $resumo->diasBatidos);
    }

    public function test_status_concluido_quando_entrada_e_saida(): void
    {
        Carbon::setTestNow('2026-05-04 14:30:00');
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:30:00',
            'saida' => '14:30:00',
            'horas' => 5.00,
        ]);

        $resumo = app(DashboardService::class)->montar($estagiario);

        $this->assertSame('concluido', $resumo->statusHoje);
        $this->assertSame('Concluído (5,00 h)', $resumo->statusDescricao);
    }
}
