<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Setor;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetorTest extends TestCase
{
    use RefreshDatabase;

    public function test_pode_persistir_setor_com_sigla_e_quantidade(): void
    {
        $setor = Setor::create([
            'sigla' => 'STI',
            'quantidade_servidores' => 1,
            'ativo' => true,
            'sincronizado_em' => now(),
        ]);

        $this->assertSame('STI', $setor->fresh()->sigla);
        $this->assertSame(1, $setor->fresh()->quantidade_servidores);
        $this->assertTrue($setor->fresh()->ativo);
    }

    public function test_sigla_eh_unica(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => true]);

        $this->expectException(QueryException::class);

        Setor::create(['sigla' => 'STI', 'ativo' => true]);
    }

    public function test_scope_ativo_filtra_inativos(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => true]);
        Setor::create(['sigla' => 'OBSOLETO', 'ativo' => false]);
        Setor::create(['sigla' => 'SDBD', 'ativo' => true]);

        $ativos = Setor::ativos()->orderBy('sigla')->pluck('sigla')->all();

        $this->assertSame(['SDBD', 'STI'], $ativos);
    }

    public function test_quantidade_servidores_pode_ser_nula_para_setor_sem_lotacao_na_api(): void
    {
        $setor = Setor::create([
            'sigla' => 'VAZIO',
            'ativo' => true,
        ]);

        $this->assertNull($setor->fresh()->quantidade_servidores);
    }

    public function test_ativo_eh_castado_para_boolean(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => 1]);

        $this->assertSame(true, Setor::firstWhere('sigla', 'STI')->ativo);
    }
}
