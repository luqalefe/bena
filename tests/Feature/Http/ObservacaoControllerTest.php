<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Feriado;
use App\Models\Frequencia;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ObservacaoControllerTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function headers(string $username): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'estagiarios'];
    }

    public function test_estagiario_cria_observacao_em_dia_util_com_frequencia_existente(): void
    {
        Carbon::setTestNow('2026-05-04 10:30:00');
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.0,
        ]);

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', [
                'texto' => 'Saí mais cedo para consulta médica',
            ])
            ->assertRedirect();

        $this->assertSame(
            'Saí mais cedo para consulta médica',
            Frequencia::where('estagiario_id', $estagiario->id)
                ->whereDate('data', '2026-05-04')
                ->value('observacao'),
        );
    }

    public function test_estagiario_cria_observacao_em_dia_util_sem_frequencia(): void
    {
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', [
                'texto' => 'Ausência justificada — atestado médico',
            ])
            ->assertRedirect();

        $frequencia = Frequencia::where('estagiario_id', $estagiario->id)
            ->whereDate('data', '2026-05-04')
            ->first();

        $this->assertNotNull($frequencia);
        $this->assertSame('Ausência justificada — atestado médico', $frequencia->observacao);
        $this->assertNull($frequencia->entrada);
        $this->assertNull($frequencia->saida);
        $this->assertNull($frequencia->horas);
    }

    public function test_estagiario_atualiza_observacao_existente(): void
    {
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'observacao' => 'rascunho antigo',
        ]);

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', [
                'texto' => 'versão final',
            ])
            ->assertRedirect();

        $this->assertSame(
            'versão final',
            Frequencia::where('estagiario_id', $estagiario->id)
                ->whereDate('data', '2026-05-04')
                ->value('observacao'),
        );
    }

    public function test_observacao_acima_de_500_chars_retorna_422(): void
    {
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', [
                'texto' => str_repeat('a', 501),
            ])
            ->assertStatus(302) // redirect back com erros (form padrão Laravel)
            ->assertSessionHasErrors(['texto']);

        $this->assertDatabaseMissing('frequencias', [
            'estagiario_id' => $estagiario->id,
        ]);
    }

    public function test_observacao_em_sabado_retorna_422(): void
    {
        // 2026-05-02 é sábado
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/2/observacao', [
                'texto' => 'tentativa em sábado',
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('frequencias', [
            'estagiario_id' => $estagiario->id,
        ]);
    }

    public function test_observacao_em_domingo_retorna_422(): void
    {
        // 2026-05-03 é domingo
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/3/observacao', [
                'texto' => 'tentativa em domingo',
            ])
            ->assertStatus(422);
    }

    public function test_observacao_em_feriado_retorna_422(): void
    {
        Feriado::create([
            'data' => '2026-05-04 00:00:00',
            'descricao' => 'Feriado teste',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', [
                'texto' => 'tentativa em feriado',
            ])
            ->assertStatus(422);

        $this->assertDatabaseMissing('frequencias', [
            'estagiario_id' => $estagiario->id,
        ]);
    }

    public function test_admin_recebe_403(): void
    {
        $estagiario = Estagiario::factory()->create(['username' => 'rh.admin']);

        $this->withHeaders(['Remote-User' => 'rh.admin', 'Remote-Groups' => 'admin'])
            ->post('/frequencia/2026/5/4/observacao', ['texto' => 'tentativa'])
            ->assertStatus(403);
    }

    public function test_supervisor_recebe_403(): void
    {
        Estagiario::factory()->create(['username' => 'marco.supervisor']);

        $this->withHeaders(['Remote-User' => 'marco.supervisor', 'Remote-Groups' => 'supervisores'])
            ->post('/frequencia/2026/5/4/observacao', ['texto' => 'tentativa'])
            ->assertStatus(403);
    }

    public function test_texto_vazio_remove_observacao_mas_mantem_frequencia_com_ponto(): void
    {
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.0,
            'observacao' => 'a remover',
        ]);

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', ['texto' => ''])
            ->assertRedirect();

        $f = Frequencia::where('estagiario_id', $estagiario->id)
            ->whereDate('data', '2026-05-04')
            ->first();

        $this->assertNotNull($f, 'Frequencia com entrada/saida deve permanecer');
        $this->assertNull($f->observacao);
        $this->assertSame('5.00', (string) $f->horas);
    }

    public function test_texto_vazio_em_dia_so_com_observacao_deleta_frequencia(): void
    {
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'observacao' => 'a remover',
        ]);

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', ['texto' => ''])
            ->assertRedirect();

        $this->assertDatabaseMissing('frequencias', [
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04 00:00:00',
        ]);
    }

    public function test_observacao_imutavel_apos_assinatura_do_estagiario(): void
    {
        $estagiario = Estagiario::factory()->create();
        Assinatura::create([
            'estagiario_id' => $estagiario->id,
            'ano' => 2026,
            'mes' => 5,
            'papel' => Assinatura::PAPEL_ESTAGIARIO,
            'assinante_username' => $estagiario->username,
            'snapshot' => '{}',
            'hash' => str_repeat('a', 64),
            'assinado_em' => '2026-06-01 10:00:00',
        ]);

        $this->withHeaders($this->headers($estagiario->username))
            ->post('/frequencia/2026/5/4/observacao', ['texto' => 'tentativa pós-assinatura'])
            ->assertStatus(422);

        $this->assertDatabaseMissing('frequencias', [
            'estagiario_id' => $estagiario->id,
            'observacao' => 'tentativa pós-assinatura',
        ]);
    }
}
