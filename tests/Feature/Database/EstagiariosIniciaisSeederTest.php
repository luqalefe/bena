<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Estagiario;
use Database\Seeders\EstagiariosIniciaisSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstagiariosIniciaisSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_seeder_cria_25_estagiarios_a_partir_do_csv(): void
    {
        $this->seed(EstagiariosIniciaisSeeder::class);

        $this->assertSame(25, Estagiario::count());
    }

    public function test_seeder_persiste_lotacao_supervisor_e_sei_da_aline(): void
    {
        $this->seed(EstagiariosIniciaisSeeder::class);

        $aline = Estagiario::where('sei', '0000058-52.2025.6.01.8000')->firstOrFail();

        $this->assertSame('ALINE VITÓRIA ALVES DA ROCHA', $aline->nome);
        $this->assertSame('SECEP', $aline->lotacao);
        $this->assertSame('MARIA FRANCISCA DA CONCEIÇÃO FERREIRA', $aline->supervisor_nome);
    }

    public function test_seeder_eh_idempotente_quando_executado_duas_vezes(): void
    {
        $this->seed(EstagiariosIniciaisSeeder::class);
        $this->seed(EstagiariosIniciaisSeeder::class);

        $this->assertSame(25, Estagiario::count());
    }

    public function test_seeder_atualiza_lotacao_se_csv_mudar_para_o_mesmo_sei(): void
    {
        $this->seed(EstagiariosIniciaisSeeder::class);

        Estagiario::where('sei', '0000058-52.2025.6.01.8000')
            ->update(['lotacao' => 'LOTACAO_ANTIGA']);

        $this->seed(EstagiariosIniciaisSeeder::class);

        $aline = Estagiario::where('sei', '0000058-52.2025.6.01.8000')->firstOrFail();
        $this->assertSame('SECEP', $aline->lotacao);
    }

    public function test_estagiario_importado_eh_ativo_e_tem_horas_diarias_default(): void
    {
        $this->seed(EstagiariosIniciaisSeeder::class);

        $exemplo = Estagiario::where('sei', '0000058-52.2025.6.01.8000')->firstOrFail();
        $this->assertTrue($exemplo->ativo);
        $this->assertSame('5.00', (string) $exemplo->horas_diarias);
    }

    public function test_seeder_gera_username_unico_por_estagiario(): void
    {
        $this->seed(EstagiariosIniciaisSeeder::class);

        $usernames = Estagiario::pluck('username');
        $this->assertCount(25, $usernames);
        $this->assertSame(25, $usernames->unique()->count());
    }
}
