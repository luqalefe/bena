<?php

declare(strict_types=1);

namespace Tests\Feature\Database;

use App\Models\Estagiario;
use App\Models\Setor;
use App\Models\Supervisor;
use Database\Seeders\EstagiariosCsvSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstagiariosCsvSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Em prod a tabela setores é populada por setores:sincronizar antes
        // do seed. Aqui simulamos esse pré-requisito com as siglas da API
        // que aparecem no CSV (incluindo a forma normalizada "Nª ZE").
        $siglas = [
            '4ª ZE', '7ª ZE', '8ª ZE', '9ª ZE',
            'ASCOM', 'ASGIM', 'ASPLAN', 'COGEP', 'CRIP', 'EJE', 'GADG',
            'OUVIDORIA', 'SEADE', 'SECAP', 'SECEP', 'SECON', 'SEDES',
            'SEJUD', 'SSEC', 'SSU',
        ];
        foreach ($siglas as $sigla) {
            Setor::create(['sigla' => $sigla, 'ativo' => true]);
        }
    }

    public function test_seeder_cria_28_estagiarios_a_partir_do_csv(): void
    {
        $this->seed(EstagiariosCsvSeeder::class);

        $this->assertSame(28, Estagiario::count());
    }

    public function test_seeder_cria_supervisores_distintos(): void
    {
        $this->seed(EstagiariosCsvSeeder::class);

        // Supervisores únicos no CSV (column SUPERVISOR proper-case): 17.
        // Aieza, Edilson, Roberval, Ana Cátia, Adriana, João A., Daniele,
        // Maria Francisca, Ronaldo, Igor, Suellen, Rodolfo, Cristiane,
        // Helton, Cleiber, Janice, José Francisco, Japhnis.
        $this->assertGreaterThanOrEqual(15, Supervisor::count());
    }

    public function test_lucas_alefe_eh_persistido_com_dados_corretos(): void
    {
        $this->seed(EstagiariosCsvSeeder::class);

        $lucas = Estagiario::where('email', 'lucas.araujo@tre-ac.jus.br')->first();

        $this->assertNotNull($lucas);
        $this->assertSame('Lucas Álefe Estevo de Araújo', $lucas->nome);
        $this->assertSame('lucas.araujo', $lucas->username);
        $this->assertSame('SSEC', $lucas->setor?->sigla);
        $this->assertSame('IFAC', $lucas->instituicao_ensino);
        $this->assertSame('0001820-40.2024.6.01.8000', $lucas->sei);
        $this->assertSame('2024-07-22', $lucas->inicio_estagio->format('Y-m-d'));
        // Fim original é "21/07/2025 (PRORROGADO)" → 2025-07-21
        $this->assertSame('2025-07-21', $lucas->fim_estagio->format('Y-m-d'));
        // Prorrogação: "21/07/2025 à 21/07/2026"
        $this->assertSame('2025-07-21', $lucas->prorrogacao_inicio->format('Y-m-d'));
        $this->assertSame('2026-07-21', $lucas->prorrogacao_fim->format('Y-m-d'));
    }

    public function test_estagiario_sem_email_no_csv_fica_com_username_null(): void
    {
        $this->seed(EstagiariosCsvSeeder::class);

        // ALINE VITÓRIA não tem email no CSV.
        $aline = Estagiario::where('sei', '0000058-52.2025.6.01.8000')->first();

        $this->assertNotNull($aline);
        $this->assertNull($aline->email);
        $this->assertNull($aline->username);
        $this->assertSame('Aline Vitória Alves da Rocha', $aline->nome);
    }

    public function test_estagiarios_estao_vinculados_ao_supervisor_correto(): void
    {
        $this->seed(EstagiariosCsvSeeder::class);

        $daniele = Supervisor::where('nome', 'Daniele Carlos de Oliveira Nunes')->first();
        $this->assertNotNull($daniele);

        // 3 estagiárias têm Daniele como supervisora no CSV: Laylanne,
        // Andriele, Luísy.
        $this->assertSame(3, $daniele->estagiarios()->count());
    }

    public function test_estagiario_recebe_horas_diarias_padrao_5h(): void
    {
        $this->seed(EstagiariosCsvSeeder::class);

        $primeiro = Estagiario::orderBy('id')->first();

        $this->assertNotNull($primeiro);
        $this->assertSame('5.00', (string) $primeiro->horas_diarias);
        $this->assertTrue($primeiro->ativo);
    }

    public function test_seeder_eh_idempotente_ao_rodar_duas_vezes(): void
    {
        $this->seed(EstagiariosCsvSeeder::class);
        $primeiraContagem = Estagiario::count();
        $primeirosSupervisores = Supervisor::count();

        $this->seed(EstagiariosCsvSeeder::class);

        $this->assertSame($primeiraContagem, Estagiario::count());
        $this->assertSame($primeirosSupervisores, Supervisor::count());
    }
}
