<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Estagiario;
use App\Models\Feriado;
use App\Models\Frequencia;
use App\Services\DashboardService;
use App\Services\PontoService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class PontoServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_bater_entrada_em_dia_util_cria_frequencia_no_horario_atual(): void
    {
        Carbon::setTestNow('2026-05-04 10:30:00'); // segunda-feira
        $estagiario = Estagiario::factory()->create();

        $frequencia = app(PontoService::class)->baterEntrada($estagiario);

        $this->assertSame('2026-05-04', $frequencia->data->format('Y-m-d'));
        $this->assertSame('10:30:00', $frequencia->entrada->format('H:i:s'));
        $this->assertSame($estagiario->id, $frequencia->estagiario_id);
        $this->assertNull($frequencia->saida);
    }

    public function test_bater_entrada_persiste_ip_da_requisicao(): void
    {
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();

        $frequencia = app(PontoService::class)->baterEntrada($estagiario, '192.168.1.42');

        $this->assertSame('192.168.1.42', $frequencia->ip_entrada);
    }

    public function test_bater_entrada_quando_ja_existe_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();
        app(PontoService::class)->baterEntrada($estagiario);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Entrada já registrada hoje às 10:30/');

        Carbon::setTestNow('2026-05-04 11:00:00');
        app(PontoService::class)->baterEntrada($estagiario);
    }

    public function test_bater_entrada_em_sabado_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-02 10:00:00'); // sábado
        $estagiario = Estagiario::factory()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/dia útil/i');

        app(PontoService::class)->baterEntrada($estagiario);
    }

    public function test_bater_entrada_em_domingo_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-03 10:00:00'); // domingo
        $estagiario = Estagiario::factory()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/dia útil/i');

        app(PontoService::class)->baterEntrada($estagiario);
    }

    public function test_bater_saida_atualiza_frequencia_com_horario_atual(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        app(PontoService::class)->baterEntrada($estagiario);

        Carbon::setTestNow('2026-05-04 14:30:00');
        $frequencia = app(PontoService::class)->baterSaida($estagiario);

        $this->assertSame('14:30:00', $frequencia->saida->format('H:i:s'));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function casosDeCalculo(): array
    {
        return [
            '5h cheias' => ['09:30:00', '14:30:00', '5.00'],
            '4h45 fracionado' => ['10:00:00', '14:45:00', '4.75'],
            '8h cheias' => ['08:00:00', '16:00:00', '8.00'],
        ];
    }

    #[DataProvider('casosDeCalculo')]
    public function test_bater_saida_calcula_horas_decimais(string $entrada, string $saida, string $horasEsperadas): void
    {
        $estagiario = Estagiario::factory()->create();
        Carbon::setTestNow('2026-05-04 '.$entrada);
        app(PontoService::class)->baterEntrada($estagiario);

        Carbon::setTestNow('2026-05-04 '.$saida);
        $frequencia = app(PontoService::class)->baterSaida($estagiario);

        $this->assertSame($horasEsperadas, (string) $frequencia->horas);
    }

    public function test_bater_saida_persiste_ip_da_requisicao(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        app(PontoService::class)->baterEntrada($estagiario);

        Carbon::setTestNow('2026-05-04 14:30:00');
        $frequencia = app(PontoService::class)->baterSaida($estagiario, '10.0.0.7');

        $this->assertSame('10.0.0.7', $frequencia->ip_saida);
    }

    public function test_bater_saida_sem_entrada_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-04 14:30:00');
        $estagiario = Estagiario::factory()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/precisa bater a entrada/i');

        app(PontoService::class)->baterSaida($estagiario);
    }

    public function test_bater_saida_no_mesmo_horario_da_entrada_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->create();
        app(PontoService::class)->baterEntrada($estagiario);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/saída.*posterior.*entrada/i');

        app(PontoService::class)->baterSaida($estagiario);
    }

    public function test_bater_saida_antes_da_entrada_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-04 14:00:00');
        $estagiario = Estagiario::factory()->create();
        // Frequência manualmente criada com entrada às 14:00
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '14:00:00',
        ]);
        // Simulamos saída sendo "anterior" (relógio voltou — improvável, mas cinto e suspensório)
        Carbon::setTestNow('2026-05-04 09:00:00');

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/saída.*posterior.*entrada/i');

        app(PontoService::class)->baterSaida($estagiario);
    }

    public function test_soma_mensal_de_20_dias_de_5h_da_exatamente_100(): void
    {
        $estagiario = Estagiario::factory()->create();
        // 20 dias úteis de maio/2026 (segunda 04 → sexta 29)
        $diasUteis = ['05-04', '05-05', '05-06', '05-07', '05-08',
            '05-11', '05-12', '05-13', '05-14', '05-15',
            '05-18', '05-19', '05-20', '05-21', '05-22',
            '05-25', '05-26', '05-27', '05-28', '05-29'];

        foreach ($diasUteis as $dia) {
            Carbon::setTestNow("2026-{$dia} 09:00:00");
            app(PontoService::class)->baterEntrada($estagiario);
            Carbon::setTestNow("2026-{$dia} 14:00:00");
            app(PontoService::class)->baterSaida($estagiario);
        }

        Carbon::setTestNow('2026-05-30 09:00:00');
        $resumo = app(DashboardService::class)->montar($estagiario);

        $this->assertSame(100.0, $resumo->horasMes);
        $this->assertSame(20, $resumo->diasBatidos);
    }

    public function test_bater_saida_quando_ja_existe_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        app(PontoService::class)->baterEntrada($estagiario);

        Carbon::setTestNow('2026-05-04 14:30:00');
        app(PontoService::class)->baterSaida($estagiario);

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Saída já registrada hoje às 14:30/');

        Carbon::setTestNow('2026-05-04 15:00:00');
        app(PontoService::class)->baterSaida($estagiario);
    }

    public function test_bater_entrada_de_estagiario_inativo_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->inativo()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Estágio inativo.*coordenação/');

        app(PontoService::class)->baterEntrada($estagiario);
    }

    public function test_bater_saida_de_estagiario_inativo_lanca_excecao(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->create();
        app(PontoService::class)->baterEntrada($estagiario);

        $estagiario->update(['ativo' => false]);

        Carbon::setTestNow('2026-05-04 14:00:00');
        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/Estágio inativo.*coordenação/');

        app(PontoService::class)->baterSaida($estagiario);
    }

    public function test_bater_entrada_em_feriado_lanca_excecao(): void
    {
        Feriado::create([
            'data' => '2026-05-04',
            'descricao' => 'Feriado de teste',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);
        Carbon::setTestNow('2026-05-04 10:00:00'); // segunda, mas com feriado cadastrado
        $estagiario = Estagiario::factory()->create();

        $this->expectException(\DomainException::class);
        $this->expectExceptionMessageMatches('/dia útil/i');

        app(PontoService::class)->baterEntrada($estagiario);
    }

    // ─────────────────────────────────────────────────────────────
    // Auto-fechamento de pontos abertos (cron diário)
    // ─────────────────────────────────────────────────────────────

    public function test_fecha_ponto_aberto_de_ontem_usando_horas_diarias_do_estagiario(): void
    {
        Carbon::setTestNow('2026-05-05 00:05:00'); // terça
        $estagiario = Estagiario::factory()->create(['horas_diarias' => 5.00]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            // sem saida — ponto aberto
        ]);

        $fechados = app(PontoService::class)->fecharPontosAbertos();

        $this->assertSame(1, $fechados);
        $f = Frequencia::first();
        $this->assertSame('14:00:00', $f->saida->format('H:i:s'));
        $this->assertSame('5.00', (string) $f->horas);
        $this->assertTrue($f->saida_automatica);
    }

    public function test_fechamento_respeita_horas_diarias_customizada(): void
    {
        Carbon::setTestNow('2026-05-05 00:05:00');
        $estagiario = Estagiario::factory()->create(['horas_diarias' => 6.50]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '08:00:00',
        ]);

        app(PontoService::class)->fecharPontosAbertos();

        $f = Frequencia::first();
        $this->assertSame('14:30:00', $f->saida->format('H:i:s'));
        $this->assertSame('6.50', (string) $f->horas);
    }

    public function test_nao_fecha_ponto_do_dia_atual(): void
    {
        Carbon::setTestNow('2026-05-04 23:50:00');
        $estagiario = Estagiario::factory()->create(['horas_diarias' => 5.00]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
        ]);

        $fechados = app(PontoService::class)->fecharPontosAbertos();

        $this->assertSame(0, $fechados);
        $this->assertNull(Frequencia::first()->saida);
    }

    public function test_nao_mexe_em_ponto_ja_com_saida(): void
    {
        Carbon::setTestNow('2026-05-05 00:05:00');
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '13:00:00',
            'horas' => 4.00,
        ]);

        $fechados = app(PontoService::class)->fecharPontosAbertos();

        $this->assertSame(0, $fechados);
        $f = Frequencia::first();
        $this->assertSame('13:00:00', $f->saida->format('H:i:s'));
        $this->assertFalse($f->saida_automatica);
    }

    public function test_fecha_pontos_abertos_de_estagiarios_diferentes(): void
    {
        Carbon::setTestNow('2026-05-05 00:05:00');
        $a = Estagiario::factory()->create(['horas_diarias' => 5.00]);
        $b = Estagiario::factory()->create(['horas_diarias' => 4.00]);
        Frequencia::create(['estagiario_id' => $a->id, 'data' => '2026-05-04', 'entrada' => '09:00:00']);
        Frequencia::create(['estagiario_id' => $b->id, 'data' => '2026-05-04', 'entrada' => '10:00:00']);

        $fechados = app(PontoService::class)->fecharPontosAbertos();

        $this->assertSame(2, $fechados);
        $this->assertSame('14:00:00', Frequencia::where('estagiario_id', $a->id)->first()->saida->format('H:i:s'));
        $this->assertSame('14:00:00', Frequencia::where('estagiario_id', $b->id)->first()->saida->format('H:i:s'));
    }

    public function test_ignora_frequencia_so_com_observacao_sem_entrada(): void
    {
        Carbon::setTestNow('2026-05-05 00:05:00');
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'observacao' => 'ausência justificada',
        ]);

        $fechados = app(PontoService::class)->fecharPontosAbertos();

        $this->assertSame(0, $fechados);
        $this->assertNull(Frequencia::first()->saida);
    }
}
