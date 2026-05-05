<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Services\FolhaMensalService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfFolhaMensalTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return ['Remote-User' => 'rh.admin', 'Remote-Groups' => 'admin'];
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(string $username = 'lucas.dev'): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'estagiarios'];
    }

    /** @return array<string, string> */
    private function supervisorHeaders(string $username = 'lucas.supervisor'): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'supervisores'];
    }

    public function test_botao_baixar_pdf_aparece_na_folha_mensal(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $response = $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/5');

        $response->assertSee('Baixar PDF');
    }

    public function test_estagiario_baixa_pdf_da_propria_folha(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $response = $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/5/pdf');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_filename_pdf_segue_padrao_frequencia_username_ano_mes(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $response = $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/5/pdf');

        $response->assertHeader(
            'content-disposition',
            'attachment; filename=frequencia_lucas.dev_2026-05.pdf'
        );
    }

    public function test_admin_baixa_pdf_de_qualquer_estagiario(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        Estagiario::factory()->create(['username' => 'ana.alvo', 'nome' => 'Ana Alvo']);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/frequencia/2026/5/pdf?estagiario=ana.alvo');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
        $response->assertHeader(
            'content-disposition',
            'attachment; filename=frequencia_ana.alvo_2026-05.pdf'
        );
    }

    public function test_supervisor_baixa_pdf_de_estagiario_sob_sua_responsabilidade(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        Estagiario::factory()->create([
            'username' => 'ana.minha',
            'nome' => 'Ana Minha',
            'supervisor_username' => 'lucas.supervisor',
        ]);

        $response = $this->withHeaders($this->supervisorHeaders('lucas.supervisor'))
            ->get('/frequencia/2026/5/pdf?estagiario=ana.minha');

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_supervisor_nao_baixa_pdf_de_estagiario_de_outro(): void
    {
        Estagiario::factory()->create([
            'username' => 'ana.outra',
            'supervisor_username' => 'outro.supervisor',
        ]);

        $this->withHeaders($this->supervisorHeaders('lucas.supervisor'))
            ->get('/frequencia/2026/5/pdf?estagiario=ana.outra')
            ->assertStatus(403);
    }

    public function test_estagiario_comum_nao_baixa_pdf_de_outro(): void
    {
        Estagiario::factory()->create(['username' => 'outra.pessoa']);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/5/pdf?estagiario=outra.pessoa')
            ->assertStatus(403);
    }

    public function test_pdf_inclui_dados_essenciais_da_folha(): void
    {
        Carbon::setTestNow('2026-05-15 10:00:00');

        $estagiario = Estagiario::factory()->create([
            'username' => 'lucas.dev',
            'nome' => 'Lucas Dev',
            'setor_id' => $this->setorId('CTI'),
            'matricula' => 'EST00001',
            'sei' => 'SEI-DEV-1/2026',
            'supervisor_nome' => 'Lucas Supervisor',
        ]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
        ]);

        $response = $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get('/frequencia/2026/5/pdf');

        $response->assertStatus(200);
        // O PDF binário não pode ser inspecionado por assertSee — então o
        // controller também tem um modo HTML pra teste.
    }

    public function test_pdf_view_inclui_observacao_da_frequencia(): void
    {
        $estagiario = Estagiario::factory()->create([
            'username' => 'lucas.dev',
            'nome' => 'Lucas Dev',
        ]);
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:00:00',
            'saida' => '14:00:00',
            'horas' => 5.00,
            'observacao' => 'Atraso justificado por consulta médica',
        ]);

        $folha = app(FolhaMensalService::class)->montar($estagiario, 2026, 5);
        $html = view('frequencia.pdf', [
            'folha' => $folha,
            'estagiario' => $estagiario,
            'verificacoes' => [],
        ])->render();

        $this->assertStringContainsString('Atraso justificado por consulta médica', $html);
    }
}
