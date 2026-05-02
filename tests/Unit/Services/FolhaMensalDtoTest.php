<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\FolhaMensal;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class FolhaMensalDtoTest extends TestCase
{
    private function folha(int $ano, int $mes): FolhaMensal
    {
        return new FolhaMensal(
            ano: $ano,
            mes: $mes,
            dias: new Collection,
            totalHoras: 0.0,
        );
    }

    public function test_titulo_extenso_em_portugues(): void
    {
        $this->assertSame('Abril / 2026', $this->folha(2026, 4)->tituloExtenso());
    }

    public function test_mes_anterior_dentro_do_mesmo_ano(): void
    {
        $this->assertSame(['ano' => 2026, 'mes' => 3], $this->folha(2026, 4)->mesAnterior());
    }

    public function test_mes_anterior_em_janeiro_vira_dezembro_do_ano_anterior(): void
    {
        $this->assertSame(['ano' => 2025, 'mes' => 12], $this->folha(2026, 1)->mesAnterior());
    }

    public function test_proximo_mes_dentro_do_mesmo_ano(): void
    {
        $this->assertSame(['ano' => 2026, 'mes' => 5], $this->folha(2026, 4)->proximoMes());
    }

    public function test_proximo_mes_em_dezembro_vira_janeiro_do_proximo_ano(): void
    {
        $this->assertSame(['ano' => 2027, 'mes' => 1], $this->folha(2026, 12)->proximoMes());
    }

    /**
     * @return array<string, array{0: int, 1: int, 2: bool}>
     */
    public static function casosNavegacaoProximo(): array
    {
        // Hoje é 2026-05-15 (mês corrente: 2026/5)
        return [
            'mes anterior ao corrente' => [2026, 4,  true],   // próximo seria maio = corrente, OK
            'mes corrente' => [2026, 5,  false],  // próximo seria junho = futuro, bloqueia
            'mes futuro proximo' => [2026, 6,  false],  // próximo seria julho = futuro
            'janeiro do ano anterior' => [2025, 1,  true],
        ];
    }

    #[DataProvider('casosNavegacaoProximo')]
    public function test_pode_navegar_para_proximo_compara_com_mes_corrente(int $ano, int $mes, bool $esperado): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $this->assertSame(
            $esperado,
            $this->folha($ano, $mes)->podeNavegarParaProximo(),
        );
    }
}
