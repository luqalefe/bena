<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Estagiario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContratoDownloadTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return ['Remote-User' => 'rh.admin', 'Remote-Groups' => 'admin'];
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(string $username): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'estagiarios'];
    }

    /** @return array<string, string> */
    private function supervisorHeaders(string $username): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'supervisores'];
    }

    private function criarEstagiarioComContrato(array $atributos = []): Estagiario
    {
        Storage::fake('local');
        $caminho = Storage::disk('local')
            ->putFile('contratos', UploadedFile::fake()->create('original.pdf', 50, 'application/pdf'));

        return Estagiario::factory()->create(array_merge([
            'contrato_path' => $caminho,
        ], $atributos));
    }

    public function test_admin_baixa_contrato_de_qualquer_estagiario(): void
    {
        $alvo = $this->criarEstagiarioComContrato(['username' => 'ana.dev']);

        $response = $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.contrato', $alvo));

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/pdf');
        $response->assertDownload('contrato_ana.dev.pdf');
    }

    public function test_estagiario_baixa_o_proprio_contrato(): void
    {
        $alvo = $this->criarEstagiarioComContrato(['username' => 'lucas.dev']);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get(route('admin.estagiarios.contrato', $alvo))
            ->assertStatus(200)
            ->assertDownload('contrato_lucas.dev.pdf');
    }

    public function test_estagiario_nao_baixa_contrato_de_outro(): void
    {
        $alvo = $this->criarEstagiarioComContrato(['username' => 'ana.dev']);

        // Garante que o "outro" exista pra middleware criar/atualizar sem ruído
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $this->withHeaders($this->estagiarioHeaders('lucas.dev'))
            ->get(route('admin.estagiarios.contrato', $alvo))
            ->assertStatus(403);
    }

    public function test_supervisor_baixa_contrato_de_estagiario_sob_sua_responsabilidade(): void
    {
        $alvo = $this->criarEstagiarioComContrato([
            'username' => 'ana.dev',
            'supervisor_username' => 'marco.supervisor',
        ]);

        $this->withHeaders($this->supervisorHeaders('marco.supervisor'))
            ->get(route('admin.estagiarios.contrato', $alvo))
            ->assertStatus(200)
            ->assertDownload('contrato_ana.dev.pdf');
    }

    public function test_supervisor_nao_baixa_contrato_de_estagiario_de_outro_supervisor(): void
    {
        $alvo = $this->criarEstagiarioComContrato([
            'username' => 'ana.dev',
            'supervisor_username' => 'outro.supervisor',
        ]);

        $this->withHeaders($this->supervisorHeaders('marco.supervisor'))
            ->get(route('admin.estagiarios.contrato', $alvo))
            ->assertStatus(403);
    }

    public function test_baixar_contrato_inexistente_retorna_404(): void
    {
        $alvo = Estagiario::factory()->create(['contrato_path' => null]);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.contrato', $alvo))
            ->assertStatus(404);
    }
}
