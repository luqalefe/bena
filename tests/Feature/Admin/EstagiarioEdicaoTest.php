<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Estagiario;
use App\Models\Supervisor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class EstagiarioEdicaoTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return [
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ];
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(): array
    {
        return [
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
        ];
    }

    public function test_estagiario_comum_em_form_de_edicao_recebe_403(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('admin.estagiarios.edit', $alvo))
            ->assertStatus(403);
    }

    public function test_admin_ve_form_pre_preenchido_com_campos_administrativos(): void
    {
        $alvo = Estagiario::factory()->create([
            'matricula' => 'EST12345',
            'lotacao' => 'Gabinete 1',
            'sei' => 'SEI-00123/2026',
            'inicio_estagio' => '2026-03-01',
            'fim_estagio' => '2026-12-01',
            'horas_diarias' => 5.00,
            'ativo' => true,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo));

        $response->assertStatus(200)
            ->assertSee('name="matricula"', false)
            ->assertSee('name="lotacao"', false)
            ->assertSee('name="sei"', false)
            ->assertSee('name="inicio_estagio"', false)
            ->assertSee('name="fim_estagio"', false)
            ->assertSee('name="horas_diarias"', false)
            ->assertSee('name="ativo"', false)
            ->assertSee('value="EST12345"', false)
            ->assertSee('value="Gabinete 1"', false);
    }

    public function test_form_nao_inclui_input_de_username(): void
    {
        // Username é fonte de verdade do Authelia — não pode ser editado.
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo))
            ->assertDontSee('name="username"', false);
    }

    public function test_form_inclui_inputs_editaveis_de_nome_e_email(): void
    {
        $alvo = Estagiario::factory()->create([
            'nome' => 'Lucas Alefe',
            'email' => 'lucas.alefe@tre-ac.jus.br',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo));

        $response->assertSee('name="nome"', false)
            ->assertSee('name="email"', false)
            ->assertSee('value="Lucas Alefe"', false)
            ->assertSee('value="lucas.alefe@tre-ac.jus.br"', false);
    }

    public function test_admin_atualiza_dados_administrativos_e_redireciona(): void
    {
        $alvo = Estagiario::factory()->create([
            'matricula' => null,
            'lotacao' => null,
            'horas_diarias' => 5.00,
            'ativo' => true,
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'matricula' => 'EST99999',
                'lotacao' => 'CTI',
                'sei' => 'SEI-77777/2026',
                'inicio_estagio' => '2026-04-01',
                'fim_estagio' => '2026-09-30',
                'horas_diarias' => '6',
                'ativo' => '1',
            ]);

        $response->assertRedirect(route('admin.estagiarios.index'));
        $response->assertSessionHas('sucesso');

        $alvo->refresh();
        $this->assertSame('EST99999', $alvo->matricula);
        $this->assertSame('CTI', $alvo->lotacao);
        $this->assertSame('SEI-77777/2026', $alvo->sei);
        $this->assertSame('2026-04-01', $alvo->inicio_estagio->format('Y-m-d'));
        $this->assertSame('2026-09-30', $alvo->fim_estagio->format('Y-m-d'));
        $this->assertSame('6.00', (string) $alvo->horas_diarias);
        $this->assertTrue($alvo->ativo);
    }

    public function test_admin_vincula_estagiario_a_supervisor_via_dropdown(): void
    {
        $supervisor = Supervisor::factory()->create([
            'nome' => 'Daniele Carlos de Oliveira Nunes',
            'username' => 'daniele.nunes',
        ]);
        $alvo = Estagiario::factory()->create([
            'supervisor_id' => null,
            'supervisor_nome' => null,
            'supervisor_username' => null,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'supervisor_id' => $supervisor->id,
                'horas_diarias' => '5',
                'ativo' => '1',
            ])
            ->assertRedirect(route('admin.estagiarios.index'));

        $alvo->refresh();
        $this->assertSame($supervisor->id, $alvo->supervisor_id);
        // Para preservar a autorização legada (middleware checa supervisor_username),
        // a vinculação por dropdown também deve sincronizar nome/username do supervisor.
        $this->assertSame('Daniele Carlos de Oliveira Nunes', $alvo->supervisor_nome);
        $this->assertSame('daniele.nunes', $alvo->supervisor_username);
    }

    public function test_form_inclui_dropdown_de_supervisores_ativos(): void
    {
        Supervisor::factory()->create(['nome' => 'Aieza Bandeira', 'ativo' => true]);
        Supervisor::factory()->create(['nome' => 'Inativo Antigo', 'ativo' => false]);
        $alvo = Estagiario::factory()->create();

        $response = $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo));

        $response->assertSee('name="supervisor_id"', false)
            ->assertSee('Aieza Bandeira');
        // Supervisor inativo NÃO aparece como opção do dropdown.
        $response->assertDontSee('Inativo Antigo');
    }

    public function test_form_de_edicao_aceita_upload_de_contrato_pdf(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo))
            ->assertSee('enctype="multipart/form-data"', false)
            ->assertSee('name="contrato"', false)
            ->assertSee('accept="application/pdf"', false);
    }

    public function test_admin_faz_upload_de_contrato_e_persiste_caminho(): void
    {
        Storage::fake('local');
        $alvo = Estagiario::factory()->create(['contrato_path' => null]);
        $pdf = UploadedFile::fake()->create('contrato-original.pdf', 100, 'application/pdf');

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'horas_diarias' => '5',
                'ativo' => '1',
                'contrato' => $pdf,
            ])
            ->assertRedirect(route('admin.estagiarios.index'));

        $alvo->refresh();
        $this->assertNotNull($alvo->contrato_path);
        $this->assertStringStartsWith('contratos/', $alvo->contrato_path);
        $this->assertStringNotContainsString('contrato-original', $alvo->contrato_path);
        Storage::disk('local')->assertExists($alvo->contrato_path);
    }

    public function test_upload_rejeita_arquivo_que_nao_eh_pdf(): void
    {
        Storage::fake('local');
        $alvo = Estagiario::factory()->create(['contrato_path' => null]);
        $jpeg = UploadedFile::fake()->image('foto.jpg');

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'horas_diarias' => '5',
                'ativo' => '1',
                'contrato' => $jpeg,
            ])
            ->assertSessionHasErrors(['contrato']);

        $this->assertNull($alvo->fresh()->contrato_path);
    }

    public function test_upload_rejeita_pdf_acima_de_5mb(): void
    {
        Storage::fake('local');
        $alvo = Estagiario::factory()->create(['contrato_path' => null]);
        // Tamanho em KB; 5121 KB > 5 MB (max:5120)
        $pdfGrande = UploadedFile::fake()->create('grande.pdf', 5121, 'application/pdf');

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'horas_diarias' => '5',
                'ativo' => '1',
                'contrato' => $pdfGrande,
            ])
            ->assertSessionHasErrors(['contrato']);

        $this->assertNull($alvo->fresh()->contrato_path);
    }

    public function test_novo_upload_deleta_contrato_anterior(): void
    {
        $disk = Storage::fake('local');
        $alvo = Estagiario::factory()->create(['contrato_path' => null]);

        // Primeiro upload
        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'horas_diarias' => '5',
                'ativo' => '1',
                'contrato' => UploadedFile::fake()->create('original.pdf', 100, 'application/pdf'),
            ]);
        $caminhoAntigo = $alvo->fresh()->contrato_path;
        $this->assertNotNull($caminhoAntigo);
        $disk->assertExists($caminhoAntigo);

        // Segundo upload — deve deletar o antigo
        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'horas_diarias' => '5',
                'ativo' => '1',
                'contrato' => UploadedFile::fake()->create('novo.pdf', 100, 'application/pdf'),
            ]);
        $caminhoNovo = $alvo->fresh()->contrato_path;

        $this->assertNotSame($caminhoAntigo, $caminhoNovo);
        $disk->assertMissing($caminhoAntigo);
        $disk->assertExists($caminhoNovo);
    }

    public function test_form_mostra_link_de_download_quando_contrato_existe(): void
    {
        $alvo = Estagiario::factory()->create(['contrato_path' => 'contratos/abc123.pdf']);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo))
            ->assertSee(route('admin.estagiarios.contrato', $alvo), false);
    }

    public function test_form_nao_mostra_link_quando_contrato_inexistente(): void
    {
        $alvo = Estagiario::factory()->create(['contrato_path' => null]);

        $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo))
            ->assertDontSee('Contrato atual:', false);
    }

    public function test_admin_pode_inativar_estagiario_omitindo_checkbox_ativo(): void
    {
        $alvo = Estagiario::factory()->create(['ativo' => true]);

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'lotacao' => 'CTI',
                'horas_diarias' => '5',
                // ativo não enviado = inativado
            ])
            ->assertRedirect(route('admin.estagiarios.index'));

        $this->assertFalse($alvo->fresh()->ativo);
    }

    public function test_update_nao_altera_username(): void
    {
        // Username é fonte de verdade do Authelia; payload mal-intencionado é descartado.
        $alvo = Estagiario::factory()->create([
            'username' => 'original.user',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'username' => 'tentativa.hack',
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'lotacao' => 'CTI',
                'horas_diarias' => '5',
                'ativo' => '1',
            ]);

        $this->assertSame('original.user', $alvo->fresh()->username);
    }

    public function test_admin_atualiza_nome_e_email(): void
    {
        $alvo = Estagiario::factory()->create([
            'nome' => 'Nome Antigo',
            'email' => 'antigo@example.local',
        ]);

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => 'Nome Corrigido',
                'email' => 'novo@tre-ac.jus.br',
                'horas_diarias' => '5',
                'ativo' => '1',
            ])
            ->assertRedirect(route('admin.estagiarios.index'));

        $alvo->refresh();
        $this->assertSame('Nome Corrigido', $alvo->nome);
        $this->assertSame('novo@tre-ac.jus.br', $alvo->email);
    }

    public function test_validacao_nome_obrigatorio_no_update(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => '',
                'email' => $alvo->email,
                'horas_diarias' => '5',
            ])
            ->assertSessionHasErrors(['nome']);
    }

    public function test_admin_persiste_instituicao_e_prorrogacao(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'instituicao_ensino' => 'IFAC',
                'prorrogacao_inicio' => '2025-07-21',
                'prorrogacao_fim' => '2026-07-21',
                'horas_diarias' => '5',
                'ativo' => '1',
            ])
            ->assertRedirect(route('admin.estagiarios.index'));

        $alvo->refresh();
        $this->assertSame('IFAC', $alvo->instituicao_ensino);
        $this->assertSame('2025-07-21', $alvo->prorrogacao_inicio->format('Y-m-d'));
        $this->assertSame('2026-07-21', $alvo->prorrogacao_fim->format('Y-m-d'));
    }

    public function test_validacao_prorrogacao_fim_posterior_a_inicio(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'prorrogacao_inicio' => '2025-12-01',
                'prorrogacao_fim' => '2025-06-01',
                'horas_diarias' => '5',
            ])
            ->assertSessionHasErrors(['prorrogacao_fim']);
    }

    public function test_form_inclui_inputs_de_instituicao_e_prorrogacao(): void
    {
        $alvo = Estagiario::factory()->create([
            'instituicao_ensino' => 'IFAC',
            'prorrogacao_inicio' => '2025-07-21',
            'prorrogacao_fim' => '2026-07-21',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo));

        $response->assertSee('name="instituicao_ensino"', false)
            ->assertSee('name="prorrogacao_inicio"', false)
            ->assertSee('name="prorrogacao_fim"', false)
            ->assertSee('value="IFAC"', false)
            ->assertSee('value="2025-07-21"', false)
            ->assertSee('value="2026-07-21"', false);
    }

    public function test_validacao_horas_diarias_obrigatorias_e_positivas(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'horas_diarias' => '0',
            ])
            ->assertSessionHasErrors(['horas_diarias']);
    }

    public function test_validacao_horas_diarias_acima_de_8_e_rejeitada(): void
    {
        // Lei 11.788, art. 10, III — jornada máxima 8h/dia.
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'horas_diarias' => '8.5',
            ])
            ->assertSessionHasErrors(['horas_diarias']);
    }

    public function test_validacao_horas_diarias_aceita_8_horas_exatas(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'inicio_estagio' => '2026-04-01',
                'fim_estagio' => '2026-09-30',
                'horas_diarias' => '8',
                'ativo' => '1',
            ])
            ->assertSessionDoesntHaveErrors(['horas_diarias']);
    }

    public function test_validacao_fim_estagio_posterior_a_inicio(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'inicio_estagio' => '2026-12-01',
                'fim_estagio' => '2026-06-01',
                'horas_diarias' => '5',
            ])
            ->assertSessionHasErrors(['fim_estagio']);
    }

    public function test_validacao_duracao_maxima_de_2_anos(): void
    {
        // Lei 11.788, art. 11 — duração máxima 2 anos na mesma parte concedente.
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'inicio_estagio' => '2026-01-01',
                'fim_estagio' => '2028-01-02', // 2 anos + 1 dia
                'horas_diarias' => '5',
            ])
            ->assertSessionHasErrors(['fim_estagio']);
    }

    public function test_validacao_duracao_exatamente_2_anos_e_aceita(): void
    {
        $alvo = Estagiario::factory()->create();

        $this->withHeaders($this->adminHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'inicio_estagio' => '2026-01-01',
                'fim_estagio' => '2028-01-01',
                'horas_diarias' => '5',
                'ativo' => '1',
            ])
            ->assertSessionDoesntHaveErrors(['fim_estagio']);
    }

    public function test_estagiario_comum_em_put_de_update_recebe_403(): void
    {
        $alvo = Estagiario::factory()->create(['matricula' => null]);

        $this->withHeaders($this->estagiarioHeaders())
            ->put(route('admin.estagiarios.update', $alvo), [
                'matricula' => 'HACK0001',
                'horas_diarias' => '5',
            ])
            ->assertStatus(403);

        $this->assertNull($alvo->fresh()->matricula);
    }
}
