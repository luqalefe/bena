<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Feriado;
use App\Services\CalendarioService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CalendarioServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_feriados_do_ano_retorna_apenas_do_ano_solicitado_para_nao_recorrentes(): void
    {
        Feriado::create(['data' => '2025-09-07', 'descricao' => 'Independência 2025', 'tipo' => 'nacional', 'recorrente' => false]);
        Feriado::create(['data' => '2026-09-07', 'descricao' => 'Independência 2026', 'tipo' => 'nacional', 'recorrente' => false]);
        Feriado::create(['data' => '2027-09-07', 'descricao' => 'Independência 2027', 'tipo' => 'nacional', 'recorrente' => false]);

        $feriados = app(CalendarioService::class)->feriadosDoAno(2026);

        $this->assertCount(1, $feriados);
        $this->assertSame('Independência 2026', $feriados->first()->descricao);
    }

    public function test_feriados_do_ano_inclui_recorrentes_com_data_remapeada_para_o_ano_solicitado(): void
    {
        Feriado::create(['data' => '2020-12-25', 'descricao' => 'Natal', 'tipo' => 'nacional', 'recorrente' => true]);

        $feriados = app(CalendarioService::class)->feriadosDoAno(2026);

        $this->assertCount(1, $feriados);
        $natal = $feriados->first();
        $this->assertSame('Natal', $natal->descricao);
        $this->assertSame('2026-12-25', $natal->data->format('Y-m-d'));
    }

    public function test_feriados_do_ano_ordena_por_mes_e_dia_crescente(): void
    {
        Feriado::create(['data' => '2020-12-25', 'descricao' => 'Natal', 'tipo' => 'nacional', 'recorrente' => true]);
        Feriado::create(['data' => '2026-09-07', 'descricao' => 'Independência', 'tipo' => 'nacional', 'recorrente' => false]);
        Feriado::create(['data' => '2020-04-21', 'descricao' => 'Tiradentes', 'tipo' => 'nacional', 'recorrente' => true]);

        $feriados = app(CalendarioService::class)->feriadosDoAno(2026);

        $this->assertSame(
            ['Tiradentes', 'Independência', 'Natal'],
            $feriados->pluck('descricao')->all(),
        );
    }

    public function test_feriados_do_ano_filtra_por_tipo_quando_fornecido(): void
    {
        Feriado::create(['data' => '2026-09-07', 'descricao' => 'Independência', 'tipo' => 'nacional', 'recorrente' => false]);
        Feriado::create(['data' => '2026-06-13', 'descricao' => 'Santo Antônio', 'tipo' => 'municipal', 'recorrente' => false]);
        Feriado::create(['data' => '2026-11-15', 'descricao' => 'Aniversário do AC', 'tipo' => 'estadual', 'uf' => 'AC', 'recorrente' => false]);

        $feriados = app(CalendarioService::class)->feriadosDoAno(2026, 'estadual');

        $this->assertCount(1, $feriados);
        $this->assertSame('Aniversário do AC', $feriados->first()->descricao);
    }
}
