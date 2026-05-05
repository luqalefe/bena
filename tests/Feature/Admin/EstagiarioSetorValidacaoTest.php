<?php

declare(strict_types=1);

namespace Tests\Feature\Admin;

use App\Models\Estagiario;
use App\Models\Setor;
use Database\Seeders\EstagiariosCsvSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstagiarioSetorValidacaoTest extends TestCase
{
    use RefreshDatabase;

    private function adminHeaders(): array
    {
        return [
            'Remote-User' => 'admin.dev',
            'Remote-Groups' => 'admin',
            'Remote-Name' => 'Admin Dev',
            'Remote-Email' => 'admin@example.local',
        ];
    }

    public function test_form_de_edicao_lista_apenas_setores_ativos(): void
    {
        Setor::create(['sigla' => 'ATIVO', 'ativo' => true]);
        Setor::create(['sigla' => 'INATIVO', 'ativo' => false]);
        $alvo = Estagiario::factory()->create();

        $response = $this->withHeaders($this->adminHeaders())
            ->get(route('admin.estagiarios.edit', $alvo));

        $response->assertSee('ATIVO');
        $response->assertDontSee('>INATIVO<', false);
    }

    public function test_update_rejeita_setor_id_inexistente(): void
    {
        $alvo = Estagiario::factory()->create(['setor_id' => null]);

        $response = $this->withHeaders($this->adminHeaders())
            ->from(route('admin.estagiarios.edit', $alvo))
            ->put(route('admin.estagiarios.update', $alvo), [
                'nome' => $alvo->nome,
                'email' => $alvo->email,
                'setor_id' => 99999,
                'horas_diarias' => '5',
                'ativo' => '1',
            ]);

        $response->assertRedirect(route('admin.estagiarios.edit', $alvo));
        $response->assertSessionHasErrors('setor_id');
        $this->assertNull($alvo->fresh()->setor_id);
    }

    public function test_csv_seeder_loga_warning_e_deixa_setor_null_para_sigla_desconhecida(): void
    {
        // Sem nenhum setor populado, todas as siglas do CSV devem cair em null
        // (e gerar warnings no log) — comportamento defensivo, não bloqueia o seed.
        $this->seed(EstagiariosCsvSeeder::class);

        $semSetor = Estagiario::whereNull('setor_id')->count();
        $this->assertSame(28, $semSetor);
    }

    public function test_csv_seeder_resolve_setor_id_quando_setor_existe(): void
    {
        Setor::create(['sigla' => 'SSEC', 'ativo' => true]);

        $this->seed(EstagiariosCsvSeeder::class);

        $lucas = Estagiario::where('email', 'lucas.araujo@tre-ac.jus.br')->first();
        $this->assertSame('SSEC', $lucas?->setor?->sigla);
    }

    public function test_csv_seeder_normaliza_zona_para_ze(): void
    {
        // CSV tem "9ª ZONA", "9º ZONA" → API tem "9ª ZE".
        Setor::create(['sigla' => '9ª ZE', 'ativo' => true]);

        $this->seed(EstagiariosCsvSeeder::class);

        $awanna = Estagiario::where('username', 'awanna')->first();
        $this->assertSame('9ª ZE', $awanna?->setor?->sigla);
    }
}
