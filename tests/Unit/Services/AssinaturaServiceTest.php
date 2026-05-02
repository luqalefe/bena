<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Services\AssinaturaService;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AssinaturaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_snapshot_canonico_eh_deterministico(): void
    {
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-15',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-10',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);

        $svc = app(AssinaturaService::class);

        $snap1 = $svc->canonicalSnapshot($estagiario, 2026, 4);
        $snap2 = $svc->canonicalSnapshot($estagiario, 2026, 4);

        $this->assertSame($snap1, $snap2);
        $this->assertSame(
            ['2026-04-10', '2026-04-15'],
            array_column($snap1['dias'], 'data')
        );
    }

    public function test_hash_muda_quando_uma_frequencia_muda(): void
    {
        $estagiario = Estagiario::factory()->create();
        $freq = Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-10',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);

        $svc = app(AssinaturaService::class);
        $hashAntes = $svc->hash($svc->canonicalSnapshot($estagiario, 2026, 4));

        $freq->update(['saida' => '15:00:00', 'horas' => 6.00]);
        $hashDepois = $svc->hash($svc->canonicalSnapshot($estagiario, 2026, 4));

        $this->assertNotSame($hashAntes, $hashDepois);
    }

    public function test_estagiario_assina_propria_folha(): void
    {
        $estagiario = Estagiario::factory()->create(['username' => 'lucas.dev']);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-10',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);

        $assinatura = app(AssinaturaService::class)->assinar(
            $estagiario,
            2026,
            4,
            Assinatura::PAPEL_ESTAGIARIO,
            'lucas.dev',
            '10.0.0.1'
        );

        $this->assertSame('estagiario', $assinatura->papel);
        $this->assertSame('lucas.dev', $assinatura->assinante_username);
        $this->assertSame(64, strlen((string) $assinatura->hash));
        $this->assertNotNull($assinatura->assinado_em);
        $this->assertSame('10.0.0.1', $assinatura->ip);
    }

    public function test_assinar_duas_vezes_no_mesmo_papel_falha(): void
    {
        $estagiario = Estagiario::factory()->create();
        $svc = app(AssinaturaService::class);

        $svc->assinar($estagiario, 2026, 4, Assinatura::PAPEL_ESTAGIARIO, 'lucas.dev');

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/já foi assinada como estagi/i');

        $svc->assinar($estagiario, 2026, 4, Assinatura::PAPEL_ESTAGIARIO, 'lucas.dev');
    }

    public function test_supervisor_nao_assina_antes_do_estagiario(): void
    {
        $estagiario = Estagiario::factory()->create();
        $svc = app(AssinaturaService::class);

        $this->expectException(DomainException::class);
        $this->expectExceptionMessageMatches('/estagi.*precisa assinar antes/i');

        $svc->assinar($estagiario, 2026, 4, Assinatura::PAPEL_SUPERVISOR, 'lucas.supervisor');
    }

    public function test_supervisor_pode_assinar_apos_estagiario(): void
    {
        $estagiario = Estagiario::factory()->create(['username' => 'lucas.dev']);
        $svc = app(AssinaturaService::class);

        $svc->assinar($estagiario, 2026, 4, Assinatura::PAPEL_ESTAGIARIO, 'lucas.dev');
        $supSign = $svc->assinar($estagiario, 2026, 4, Assinatura::PAPEL_SUPERVISOR, 'lucas.supervisor');

        $this->assertSame('supervisor', $supSign->papel);
        $this->assertCount(2, Assinatura::where('estagiario_id', $estagiario->id)->get());
    }

    public function test_verificar_retorna_integro_quando_dados_nao_mudaram(): void
    {
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-10',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);
        $svc = app(AssinaturaService::class);
        $svc->assinar($estagiario, 2026, 4, Assinatura::PAPEL_ESTAGIARIO, 'lucas.dev');

        $resultado = $svc->verificar($estagiario, 2026, 4);

        $this->assertCount(1, $resultado);
        $this->assertTrue($resultado[0]['integro']);
    }

    public function test_verificar_detecta_alteracao_pos_assinatura(): void
    {
        $estagiario = Estagiario::factory()->create();
        $freq = Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-04-10',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);
        $svc = app(AssinaturaService::class);
        $svc->assinar($estagiario, 2026, 4, Assinatura::PAPEL_ESTAGIARIO, 'lucas.dev');

        $freq->update(['saida' => '15:00:00', 'horas' => 6.00]);

        $resultado = $svc->verificar($estagiario, 2026, 4);

        $this->assertFalse($resultado[0]['integro']);
    }
}
