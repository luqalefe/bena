<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Feriado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FeriadoCadastroTest extends TestCase
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

    public function test_estagiario_em_form_de_criar_recebe_403(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->get('/admin/feriados/criar')
            ->assertStatus(403);
    }

    public function test_admin_acessa_form_com_200_e_campos(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->get('/admin/feriados/criar')
            ->assertStatus(200)
            ->assertSee('name="data"', false)
            ->assertSee('name="descricao"', false)
            ->assertSee('name="tipo"', false)
            ->assertSee('name="uf"', false)
            ->assertSee('name="recorrente"', false);
    }

    public function test_estagiario_em_post_criar_recebe_403(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'Independência',
                'tipo' => 'nacional',
            ])
            ->assertStatus(403);

        $this->assertDatabaseCount('feriados', 0);
    }

    public function test_post_valido_persiste_e_redireciona_com_flash(): void
    {
        $response = $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'Independência do Brasil',
                'tipo' => 'nacional',
                'recorrente' => '1',
            ]);

        $response->assertRedirect(route('admin.feriados.index'));
        $response->assertSessionHas('sucesso');

        $feriado = Feriado::firstWhere('descricao', 'Independência do Brasil');
        $this->assertNotNull($feriado);
        $this->assertSame('2026-09-07', $feriado->data->format('Y-m-d'));
        $this->assertSame('nacional', $feriado->tipo);
        $this->assertTrue($feriado->recorrente);
    }

    public function test_validacao_data_obrigatoria(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'descricao' => 'Sem data',
                'tipo' => 'nacional',
            ])
            ->assertSessionHasErrors(['data']);

        $this->assertDatabaseCount('feriados', 0);
    }

    public function test_validacao_descricao_max_200(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => str_repeat('a', 201),
                'tipo' => 'nacional',
            ])
            ->assertSessionHasErrors(['descricao']);

        $this->assertDatabaseCount('feriados', 0);
    }

    public function test_validacao_tipo_dentro_do_enum(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'X',
                'tipo' => 'inexistente',
            ])
            ->assertSessionHasErrors(['tipo']);

        $this->assertDatabaseCount('feriados', 0);
    }

    public function test_validacao_uf_size_2_quando_preenchida(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'X',
                'tipo' => 'estadual',
                'uf' => 'ABC',
            ])
            ->assertSessionHasErrors(['uf']);

        $this->assertDatabaseCount('feriados', 0);
    }

    public function test_uf_vazia_eh_aceita(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'Independência',
                'tipo' => 'nacional',
                'uf' => '',
            ])
            ->assertRedirect(route('admin.feriados.index'));

        $this->assertDatabaseCount('feriados', 1);
    }

    public function test_data_duplicada_retorna_erro_especifico(): void
    {
        Feriado::create([
            'data' => '2026-09-07',
            'descricao' => 'Independência',
            'tipo' => 'nacional',
            'recorrente' => false,
        ]);

        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'Outra coisa',
                'tipo' => 'nacional',
            ])
            ->assertSessionHasErrors([
                'data' => 'Já existe feriado nesta data.',
            ]);

        $this->assertDatabaseCount('feriados', 1);
    }

    public function test_feriado_recem_criado_aparece_imediatamente_na_listagem(): void
    {
        $this->withHeaders($this->adminHeaders())
            ->post('/admin/feriados', [
                'data' => '2026-09-07',
                'descricao' => 'Independência',
                'tipo' => 'nacional',
            ]);

        $this->withHeaders($this->adminHeaders())
            ->get('/admin/feriados?ano=2026')
            ->assertSee('Independência')
            ->assertSee('07/09/2026');
    }
}
