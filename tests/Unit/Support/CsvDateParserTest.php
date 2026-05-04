<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\CsvDateParser;
use PHPUnit\Framework\TestCase;

class CsvDateParserTest extends TestCase
{
    private CsvDateParser $parser;

    protected function setUp(): void
    {
        $this->parser = new CsvDateParser;
    }

    public function test_parse_us_format_mes_baixo_dia_baixo(): void
    {
        $this->assertSame('2025-01-13', $this->parser->parse('1/13/2025')?->format('Y-m-d'));
    }

    public function test_parse_us_format_mes_alto(): void
    {
        $this->assertSame('2025-12-22', $this->parser->parse('12/22/2025')?->format('Y-m-d'));
    }

    public function test_parse_us_format_padrao_majoritario_do_csv(): void
    {
        $this->assertSame('2024-07-22', $this->parser->parse('7/22/2024')?->format('Y-m-d'));
    }

    public function test_parse_br_format_dia_alto_indica_dmy(): void
    {
        // Dia 21, mês 07 — só faz sentido em D/M/Y porque mês 21 não existe.
        $this->assertSame('2025-07-21', $this->parser->parse('21/07/2025')?->format('Y-m-d'));
    }

    public function test_parse_br_format_30_de_junho(): void
    {
        $this->assertSame('2026-06-30', $this->parser->parse('30/06/2026')?->format('Y-m-d'));
    }

    public function test_parse_strip_texto_parentetico(): void
    {
        $this->assertSame('2025-07-21', $this->parser->parse('21/07/2025 (PRORROGADO)')?->format('Y-m-d'));
    }

    public function test_parse_aceita_espacos_em_volta(): void
    {
        // Valor inequívoco (dia 30 só faz sentido em BR).
        $this->assertSame('2026-06-30', $this->parser->parse(' 30/06/2026 ')?->format('Y-m-d'));
    }

    public function test_parse_caso_ambiguo_prefere_us_seguindo_padrao_majoritario_do_csv(): void
    {
        // " 05/11/2025" — válido como US (May 11) ou BR (Nov 5). O parser
        // prefere US porque é o formato dominante no CSV exportado do Excel.
        $this->assertSame('2025-05-11', $this->parser->parse('05/11/2025')?->format('Y-m-d'));
    }

    public function test_parse_iso_format_passa_direto(): void
    {
        // Migração futura: se alguém limpar o CSV pra ISO, ainda funciona.
        $this->assertSame('2026-04-13', $this->parser->parse('2026-04-13')?->format('Y-m-d'));
    }

    public function test_parse_string_vazia_retorna_null(): void
    {
        $this->assertNull($this->parser->parse(''));
        $this->assertNull($this->parser->parse('   '));
    }

    public function test_parse_lixo_retorna_null(): void
    {
        $this->assertNull($this->parser->parse('garbage'));
        $this->assertNull($this->parser->parse('99/99/9999'));
    }

    public function test_parse_intervalo_extrai_inicio_e_fim(): void
    {
        // Formato visto em PRORROGAÇÃO: "21/07/2025 à 21/07/2026"
        $intervalo = $this->parser->parseIntervalo('21/07/2025 à 21/07/2026');

        $this->assertNotNull($intervalo);
        $this->assertSame('2025-07-21', $intervalo['inicio']->format('Y-m-d'));
        $this->assertSame('2026-07-21', $intervalo['fim']->format('Y-m-d'));
    }

    public function test_parse_intervalo_aceita_separador_a_sem_acento(): void
    {
        $intervalo = $this->parser->parseIntervalo('21/07/2025 a 21/07/2026');

        $this->assertNotNull($intervalo);
        $this->assertSame('2025-07-21', $intervalo['inicio']->format('Y-m-d'));
    }

    public function test_parse_intervalo_vazio_retorna_null(): void
    {
        $this->assertNull($this->parser->parseIntervalo(''));
        $this->assertNull($this->parser->parseIntervalo('garbage'));
    }
}
