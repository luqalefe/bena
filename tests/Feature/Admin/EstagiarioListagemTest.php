<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Estagiario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstagiarioListagemTest extends TestCase
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

    public function test_estagiario_comum_em_lista_recebe_403(): void
    {
        $this->withHeaders($this->estagiarioHeaders())
            ->get('/admin/estagiarios')
            ->assertStatus(403);
    }

    public function test_admin_ve_lista_com_estagiarios_ativos_e_inativos(): void
    {
        Estagiario::factory()->create([
            'username' => 'ana.estagiaria',
            'nome' => 'Ana Estagiária',
            'lotacao' => 'Gabinete 1',
            'ativo' => true,
        ]);
        Estagiario::factory()->inativo()->create([
            'username' => 'bruno.afastado',
            'nome' => 'Bruno Afastado',
            'lotacao' => 'CTI',
        ]);

        $response = $this->withHeaders($this->adminHeaders())
            ->get('/admin/estagiarios');

        $response->assertStatus(200);
        $response->assertSee('Ana Estagiária');
        $response->assertSee('Bruno Afastado');
        $response->assertSee('Gabinete 1');
        $response->assertSee('CTI');
    }
}
