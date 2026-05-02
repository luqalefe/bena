<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Estagiario;
use App\Models\Feriado;
use App\Models\Frequencia;
use App\Services\FolhaMensalService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FolhaMensalServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_montar_retorna_uma_linha_por_dia_do_mes(): void
    {
        $estagiario = Estagiario::factory()->create();

        $folha = app(FolhaMensalService::class)->montar($estagiario, 2026, 4);

        $this->assertCount(30, $folha->dias);
        $this->assertSame(2026, $folha->ano);
        $this->assertSame(4, $folha->mes);
    }

    public function test_dia_util_batido_traz_frequencia_com_entrada_saida_e_horas(): void
    {
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-06', // segunda-feira
            'entrada' => '09:00:00',
            'saida' => '13:00:00',
            'horas' => 4.0,
        ]);

        $folha = app(FolhaMensalService::class)->montar($estagiario, 2026, 4);

        $dia6 = $folha->dias->firstWhere(fn ($d) => $d->data->day === 6);
        $this->assertNotNull($dia6);
        $this->assertSame('util', $dia6->tipo);
        $this->assertNotNull($dia6->frequencia);
        $this->assertSame('09:00:00', $dia6->frequencia->entrada->format('H:i:s'));
        $this->assertSame('13:00:00', $dia6->frequencia->saida->format('H:i:s'));
        $this->assertSame('4.00', (string) $dia6->frequencia->horas);
    }

    public function test_dia_util_sem_registro_tem_frequencia_null(): void
    {
        $estagiario = Estagiario::factory()->create();

        $folha = app(FolhaMensalService::class)->montar($estagiario, 2026, 4);

        $dia7 = $folha->dias->firstWhere(fn ($d) => $d->data->day === 7); // terça
        $this->assertSame('util', $dia7->tipo);
        $this->assertNull($dia7->frequencia);
    }

    public function test_sabado_e_domingo_classificados_corretamente(): void
    {
        $estagiario = Estagiario::factory()->create();

        $folha = app(FolhaMensalService::class)->montar($estagiario, 2026, 4);

        // 2026-04-04 = sábado, 2026-04-05 = domingo
        $sabado = $folha->dias->firstWhere(fn ($d) => $d->data->day === 4);
        $domingo = $folha->dias->firstWhere(fn ($d) => $d->data->day === 5);

        $this->assertSame('sabado', $sabado->tipo);
        $this->assertSame('domingo', $domingo->tipo);
    }

    public function test_feriado_classificado_com_descricao(): void
    {
        $estagiario = Estagiario::factory()->create();
        Feriado::create([
            'data' => '2026-04-21',
            'descricao' => 'Tiradentes',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $folha = app(FolhaMensalService::class)->montar($estagiario, 2026, 4);

        $tiradentes = $folha->dias->firstWhere(fn ($d) => $d->data->day === 21);
        $this->assertSame('feriado', $tiradentes->tipo);
        $this->assertSame('Tiradentes', $tiradentes->descricaoFeriado);
    }

    public function test_total_horas_soma_apenas_horas_validas(): void
    {
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-06',
            'entrada' => '09:00:00',
            'saida' => '13:00:00',
            'horas' => 4.0,
        ]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-07',
            'entrada' => '09:00:00',
            'saida' => '12:30:00',
            'horas' => 3.5,
        ]);
        // Em andamento (sem saída/horas)
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-08',
            'entrada' => '09:00:00',
        ]);

        $folha = app(FolhaMensalService::class)->montar($estagiario, 2026, 4);

        $this->assertSame(7.5, $folha->totalHoras);
    }

    public function test_frequencia_de_outro_estagiario_nao_vaza(): void
    {
        $a = Estagiario::factory()->create();
        $b = Estagiario::factory()->create();

        Frequencia::create([
            'estagiario_id' => $a->id,
            'data' => '2026-04-06',
            'entrada' => '09:00:00',
            'saida' => '13:00:00',
            'horas' => 4.0,
        ]);

        $folhaB = app(FolhaMensalService::class)->montar($b, 2026, 4);

        $dia6 = $folhaB->dias->firstWhere(fn ($d) => $d->data->day === 6);
        $this->assertNull($dia6->frequencia);
        $this->assertSame(0.0, $folhaB->totalHoras);
    }
}
