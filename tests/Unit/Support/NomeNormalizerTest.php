<?php

declare(strict_types=1);

namespace Tests\Unit\Support;

use App\Support\NomeNormalizer;
use PHPUnit\Framework\TestCase;

class NomeNormalizerTest extends TestCase
{
    private NomeNormalizer $normalizer;

    protected function setUp(): void
    {
        $this->normalizer = new NomeNormalizer;
    }

    public function test_converte_uppercase_para_title_case(): void
    {
        $this->assertSame(
            'Lucas Álefe Estevo de Araújo',
            $this->normalizer->normalizar('LUCAS ÁLEFE ESTEVO DE ARAÚJO'),
        );
    }

    public function test_preserva_particulas_em_lowercase(): void
    {
        $this->assertSame(
            'Maria Francisca da Conceição Ferreira',
            $this->normalizer->normalizar('MARIA FRANCISCA DA CONCEIÇÃO FERREIRA'),
        );
    }

    public function test_remove_espacos_extras_em_volta(): void
    {
        $this->assertSame(
            'Ana Paula Albuquerque Campos',
            $this->normalizer->normalizar('  ANA PAULA ALBUQUERQUE CAMPOS  '),
        );
    }

    public function test_colapsa_multiplos_espacos_internos(): void
    {
        $this->assertSame(
            'Pedro Augusto Brito de Lima',
            $this->normalizer->normalizar('PEDRO AUGUSTO BRITO DE  LIMA'),
        );
    }

    public function test_preserva_titulo_quando_ja_esta_em_caso_misto(): void
    {
        $this->assertSame(
            'Lucas Álefe Estevo de Araújo',
            $this->normalizer->normalizar('Lucas Álefe Estevo de Araújo'),
        );
    }

    public function test_string_vazia_retorna_string_vazia(): void
    {
        $this->assertSame('', $this->normalizer->normalizar(''));
        $this->assertSame('', $this->normalizer->normalizar('   '));
    }

    public function test_particulas_no_inicio_capitalizam(): void
    {
        // Quando uma partícula é a primeira palavra, capitaliza
        // (ninguém escreve "de Souza Cordeiro" no campo Nome).
        $this->assertSame(
            'Da Silva',
            $this->normalizer->normalizar('DA SILVA'),
        );
    }
}
