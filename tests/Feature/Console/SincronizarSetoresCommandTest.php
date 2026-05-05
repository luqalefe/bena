<?php

declare(strict_types=1);

namespace Tests\Feature\Console;

use App\Models\Setor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SincronizarSetoresCommandTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.tre_ac.base_url' => 'https://visao.tre-ac.jus.br/painel/view/api',
            'services.tre_ac.cache_ttl' => 3600,
        ]);

        Cache::flush();
    }

    private function fakeApi(array $unidades, array $lotacoes): void
    {
        Http::fake([
            '*/unidades/' => Http::response(array_map(fn ($s) => ['SIGLA_UNID_TSE' => $s], $unidades)),
            '*/lotacao/' => Http::response(array_map(
                fn ($q, $s) => ['SIGLA_UNID_TSE' => $s, 'QUANTIDADE' => (string) $q],
                $lotacoes,
                array_keys($lotacoes),
            )),
        ]);
    }

    public function test_cria_setores_a_partir_da_api(): void
    {
        $this->fakeApi(
            unidades: ['STI', 'SDBD', 'AGECON'],
            lotacoes: ['STI' => 1, 'SDBD' => 3, 'AGECON' => 2],
        );

        $this->artisan('setores:sincronizar')->assertSuccessful();

        $this->assertDatabaseCount('setores', 3);
        $this->assertSame(1, Setor::firstWhere('sigla', 'STI')->quantidade_servidores);
        $this->assertSame(3, Setor::firstWhere('sigla', 'SDBD')->quantidade_servidores);
    }

    public function test_atualiza_quantidade_de_setor_existente(): void
    {
        Setor::create(['sigla' => 'STI', 'quantidade_servidores' => 1, 'ativo' => true]);

        $this->fakeApi(
            unidades: ['STI'],
            lotacoes: ['STI' => 5],
        );

        $this->artisan('setores:sincronizar')->assertSuccessful();

        $this->assertDatabaseCount('setores', 1);
        $this->assertSame(5, Setor::firstWhere('sigla', 'STI')->quantidade_servidores);
    }

    public function test_marca_como_inativo_setor_que_sumiu_da_api(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => true]);
        Setor::create(['sigla' => 'OBSOLETO', 'ativo' => true]);

        $this->fakeApi(
            unidades: ['STI'],
            lotacoes: ['STI' => 1],
        );

        $this->artisan('setores:sincronizar')->assertSuccessful();

        $this->assertTrue(Setor::firstWhere('sigla', 'STI')->ativo);
        $this->assertFalse(Setor::firstWhere('sigla', 'OBSOLETO')->ativo);
    }

    public function test_reativa_setor_que_voltou_a_aparecer(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => false]);

        $this->fakeApi(
            unidades: ['STI'],
            lotacoes: ['STI' => 2],
        );

        $this->artisan('setores:sincronizar')->assertSuccessful();

        $this->assertTrue(Setor::firstWhere('sigla', 'STI')->ativo);
    }

    public function test_setor_so_em_unidades_sem_lotacao_fica_com_quantidade_nula(): void
    {
        $this->fakeApi(
            unidades: ['STI', 'VAZIO'],
            lotacoes: ['STI' => 1],
        );

        $this->artisan('setores:sincronizar')->assertSuccessful();

        $vazio = Setor::firstWhere('sigla', 'VAZIO');
        $this->assertNotNull($vazio);
        $this->assertNull($vazio->quantidade_servidores);
        $this->assertTrue($vazio->ativo);
    }

    public function test_setor_so_em_lotacao_sem_unidades_tambem_eh_persistido(): void
    {
        $this->fakeApi(
            unidades: ['STI'],
            lotacoes: ['STI' => 1, 'GHOST' => 4],
        );

        $this->artisan('setores:sincronizar')->assertSuccessful();

        $this->assertSame(4, Setor::firstWhere('sigla', 'GHOST')->quantidade_servidores);
    }

    public function test_sincronizado_em_eh_atualizado(): void
    {
        $this->fakeApi(unidades: ['STI'], lotacoes: ['STI' => 1]);

        $this->artisan('setores:sincronizar')->assertSuccessful();

        $this->assertNotNull(Setor::firstWhere('sigla', 'STI')->sincronizado_em);
    }

    public function test_output_resume_criados_atualizados_e_inativados(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => true, 'quantidade_servidores' => 1]);
        Setor::create(['sigla' => 'OBSOLETO', 'ativo' => true]);

        $this->fakeApi(
            unidades: ['STI', 'NOVO'],
            lotacoes: ['STI' => 2, 'NOVO' => 1],
        );

        Artisan::call('setores:sincronizar');
        $output = Artisan::output();

        $this->assertStringContainsString('1 criados, 1 atualizados, 1 inativados', $output);
    }

    public function test_falha_de_api_retorna_codigo_de_erro_e_imprime_mensagem(): void
    {
        Http::fake([
            '*/unidades/' => Http::response(null, 500),
        ]);

        $this->artisan('setores:sincronizar')
            ->assertExitCode(1);
    }

    public function test_setor_em_que_nada_mudou_nao_eh_contado_como_atualizado(): void
    {
        Setor::create(['sigla' => 'STI', 'ativo' => true, 'quantidade_servidores' => 5]);

        $this->fakeApi(
            unidades: ['STI'],
            lotacoes: ['STI' => 5],
        );

        Artisan::call('setores:sincronizar');
        $output = Artisan::output();

        $this->assertStringContainsString('0 criados, 0 atualizados, 0 inativados', $output);
    }
}
