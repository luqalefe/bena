<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Auditoria;
use App\Services\AuditoriaService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditoriaServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registrar_persiste_entrada_com_campos_obrigatorios(): void
    {
        Carbon::setTestNow('2026-05-03 12:00:00');

        $log = app(AuditoriaService::class)->registrar(
            usuario: 'lucas.dev',
            acao: 'feriado.criar',
            entidade: 'feriado',
            entidadeId: '42',
            payload: ['descricao' => 'Tiradentes', 'data' => '2026-04-21'],
            ip: '10.0.0.1',
        );

        $this->assertDatabaseHas('auditoria', [
            'usuario_username' => 'lucas.dev',
            'acao' => 'feriado.criar',
            'entidade' => 'feriado',
            'entidade_id' => '42',
            'ip' => '10.0.0.1',
        ]);
        $this->assertSame(2026, $log->created_at->year);
    }

    public function test_registrar_aceita_payload_nulo(): void
    {
        $log = app(AuditoriaService::class)->registrar(
            usuario: 'rh.admin',
            acao: 'estagiario.editar',
            entidade: 'estagiario',
            entidadeId: '7',
        );

        $this->assertNull($log->payload);
        $this->assertNull($log->ip);
    }

    public function test_registrar_serializa_payload_em_json(): void
    {
        app(AuditoriaService::class)->registrar(
            usuario: 'lucas.dev',
            acao: 'frequencia.entrada',
            entidade: 'frequencia',
            entidadeId: null,
            payload: ['data' => '2026-05-03', 'entrada' => '09:15:00'],
        );

        $linha = Auditoria::query()->first();
        $payload = json_decode((string) $linha->payload, true);

        $this->assertSame('2026-05-03', $payload['data']);
        $this->assertSame('09:15:00', $payload['entrada']);
    }
}
