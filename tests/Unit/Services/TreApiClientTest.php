<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\TreApiClient;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use Tests\TestCase;

class TreApiClientTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.tre_ac.base_url' => 'https://visao.tre-ac.jus.br/painel/view/api',
            'services.tre_ac.timeout' => 8,
            'services.tre_ac.cache_ttl' => 3600,
        ]);

        Cache::flush();
    }

    public function test_unidades_retorna_lista_de_siglas_a_partir_da_api(): void
    {
        Http::fake([
            '*/unidades/' => Http::response([
                ['SIGLA_UNID_TSE' => 'STI'],
                ['SIGLA_UNID_TSE' => 'SDBD'],
                ['SIGLA_UNID_TSE' => 'AGECON'],
            ]),
        ]);

        $unidades = app(TreApiClient::class)->unidades();

        $this->assertSame(['STI', 'SDBD', 'AGECON'], $unidades);
    }

    public function test_lotacoes_retorna_sigla_e_quantidade(): void
    {
        Http::fake([
            '*/lotacao/' => Http::response([
                ['SIGLA_UNID_TSE' => 'STI', 'QUANTIDADE' => '1'],
                ['SIGLA_UNID_TSE' => 'SDBD', 'QUANTIDADE' => '3'],
            ]),
        ]);

        $lotacoes = app(TreApiClient::class)->lotacoes();

        $this->assertSame(['STI' => 1, 'SDBD' => 3], $lotacoes);
    }

    public function test_unidades_usa_cache_em_chamadas_subsequentes(): void
    {
        Http::fake([
            '*/unidades/' => Http::response([['SIGLA_UNID_TSE' => 'STI']]),
        ]);

        $client = app(TreApiClient::class);
        $client->unidades();
        $client->unidades();
        $client->unidades();

        Http::assertSentCount(1);
    }

    public function test_lotacoes_usa_cache_em_chamadas_subsequentes(): void
    {
        Http::fake([
            '*/lotacao/' => Http::response([['SIGLA_UNID_TSE' => 'STI', 'QUANTIDADE' => '1']]),
        ]);

        $client = app(TreApiClient::class);
        $client->lotacoes();
        $client->lotacoes();

        Http::assertSentCount(1);
    }

    public function test_http_status_nao_2xx_lanca_runtime_exception(): void
    {
        Http::fake([
            '*/unidades/' => Http::response(null, 500),
        ]);

        $this->expectException(RuntimeException::class);

        app(TreApiClient::class)->unidades();
    }

    public function test_json_invalido_lanca_runtime_exception(): void
    {
        Http::fake([
            '*/unidades/' => Http::response('isto nao eh json', 200),
        ]);

        $this->expectException(RuntimeException::class);

        app(TreApiClient::class)->unidades();
    }

    public function test_payload_que_nao_eh_array_lanca_runtime_exception(): void
    {
        Http::fake([
            '*/unidades/' => Http::response(['SIGLA_UNID_TSE' => 'STI'], 200),
        ]);

        $this->expectException(RuntimeException::class);

        app(TreApiClient::class)->unidades();
    }

    public function test_timeout_lanca_runtime_exception(): void
    {
        Http::fake([
            '*/unidades/' => fn () => throw new ConnectionException('timeout'),
        ]);

        $this->expectException(RuntimeException::class);

        app(TreApiClient::class)->unidades();
    }

    public function test_lotacoes_ignora_entradas_sem_sigla(): void
    {
        Http::fake([
            '*/lotacao/' => Http::response([
                ['SIGLA_UNID_TSE' => 'STI', 'QUANTIDADE' => '1'],
                ['QUANTIDADE' => '5'],
                ['SIGLA_UNID_TSE' => '', 'QUANTIDADE' => '2'],
                ['SIGLA_UNID_TSE' => 'SDBD', 'QUANTIDADE' => '3'],
            ]),
        ]);

        $lotacoes = app(TreApiClient::class)->lotacoes();

        $this->assertSame(['STI' => 1, 'SDBD' => 3], $lotacoes);
    }

    public function test_unidades_ignora_entradas_sem_sigla(): void
    {
        Http::fake([
            '*/unidades/' => Http::response([
                ['SIGLA_UNID_TSE' => 'STI'],
                [],
                ['SIGLA_UNID_TSE' => ''],
                ['SIGLA_UNID_TSE' => 'SDBD'],
            ]),
        ]);

        $unidades = app(TreApiClient::class)->unidades();

        $this->assertSame(['STI', 'SDBD'], $unidades);
    }
}
