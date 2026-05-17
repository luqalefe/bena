<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Estagiario;
use App\Models\Setor;
use App\Services\BuddyData;
use App\Services\BuddyService;
use App\Support\BuddySprite;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuddyServiceTest extends TestCase
{
    use RefreshDatabase;

    private string $diretorioSprites = '';

    protected function tearDown(): void
    {
        if ($this->diretorioSprites !== '') {
            foreach (glob($this->diretorioSprites.'/*') ?: [] as $arquivo) {
                unlink($arquivo);
            }
            @rmdir($this->diretorioSprites);
        }
        parent::tearDown();
    }

    private function bindSpriteFake(): string
    {
        $this->diretorioSprites = sys_get_temp_dir().'/buddy-sprite-test-'.uniqid();
        mkdir($this->diretorioSprites, 0o755, true);
        $this->app->instance(
            BuddySprite::class,
            new BuddySprite($this->diretorioSprites, '/images/buddies'),
        );

        return $this->diretorioSprites;
    }

    public function test_garantir_buddy_atribui_tipo_quando_null(): void
    {
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        app(BuddyService::class)->garantirBuddy($estagiario);

        $this->assertNotNull($estagiario->fresh()->buddy_tipo);
        // Sem grupo → cai no pool comum (estagiário de setor não-lendário).
        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos'),
        );
    }

    public function test_garantir_buddy_nao_altera_tipo_existente(): void
    {
        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        app(BuddyService::class)->garantirBuddy($estagiario);

        $this->assertSame('coruja', $estagiario->fresh()->buddy_tipo);
    }

    public function test_montar_retorna_buddy_data_com_frase(): void
    {
        Carbon::setTestNow('2026-05-04 10:00:00'); // segunda

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->montar($estagiario, 'aguardando_entrada');

        $this->assertInstanceOf(BuddyData::class, $data);
        $this->assertSame('coruja', $data->tipo);
        $this->assertSame('Coruinha', $data->nome);
        $this->assertSame('🦉', $data->emoji);
        $this->assertNotEmpty($data->frase);
    }

    public function test_montar_usa_frase_deterministica_por_dia(): void
    {
        Carbon::setTestNow('2026-05-05 09:00:00'); // terça

        $estagiario = Estagiario::factory()->comBuddy('cachorro')->create();
        $service = app(BuddyService::class);

        $primeira = $service->montar($estagiario, 'em_andamento');

        // Mesmo dia, mesmo status, mesma hora-do-dia → mesma frase.
        Carbon::setTestNow('2026-05-05 11:00:00');
        $segunda = $service->montar($estagiario, 'em_andamento');

        $this->assertSame($primeira->frase, $segunda->frase);
    }

    public function test_montar_retorna_frase_generica_quando_contexto_vazio(): void
    {
        // Configura buddy 'coruja' com frases.tipo.dia.status vazio,
        // mas frases.coruja.generica preenchido.
        config([
            'buddies.frases.coruja.segunda.aguardando_entrada' => [],
            'buddies.frases.coruja.generica' => ['Frase de fallback da coruja.'],
        ]);

        Carbon::setTestNow('2026-05-04 10:00:00'); // segunda

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->montar($estagiario, 'aguardando_entrada');

        $this->assertSame('Frase de fallback da coruja.', $data->frase);
    }

    public function test_boas_vindas_retorna_buddy_com_frase_de_apresentacao(): void
    {
        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->boasVindas($estagiario);

        $this->assertInstanceOf(BuddyData::class, $data);
        $this->assertSame('coruja', $data->tipo);
        $this->assertSame('Coruinha', $data->nome);
        $this->assertSame('🦉', $data->emoji);
        $this->assertNotEmpty($data->frase);
        $this->assertContains(
            $data->frase,
            config('buddies.frases.coruja.boas_vindas'),
        );
    }

    public function test_garantir_buddy_de_supervisor_usa_pool_senior(): void
    {
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        app(BuddyService::class)->garantirBuddy($estagiario, 'S');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_supervisores'),
        );
    }

    public function test_garantir_buddy_de_admin_usa_pool_senior(): void
    {
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        app(BuddyService::class)->garantirBuddy($estagiario, '0');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_supervisores'),
        );
    }

    public function test_garantir_buddy_estagiario_sti_sorteia_lendaria(): void
    {
        // Estagiários lotados em STI (ou SSEC) recebem pool lendário exclusivo.
        $estagiario = Estagiario::factory()->create([
            'username' => 'novo.sti',
            'setor_id' => Setor::firstOrCreate(['sigla' => 'STI'], ['ativo' => true])->id,
            'buddy_tipo' => null,
        ]);

        app(BuddyService::class)->garantirBuddy($estagiario, 'E');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_lendarios'),
        );
    }

    public function test_garantir_buddy_estagiario_ssec_sorteia_lendaria(): void
    {
        // SSEC faz parte do mesmo grupo institucional da STI.
        $estagiario = Estagiario::factory()->create([
            'username' => 'novo.ssec',
            'setor_id' => Setor::firstOrCreate(['sigla' => 'SSEC'], ['ativo' => true])->id,
            'buddy_tipo' => null,
        ]);

        app(BuddyService::class)->garantirBuddy($estagiario, 'E');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_lendarios'),
        );
    }

    public function test_garantir_buddy_estagiario_fora_da_sti_usa_pool_comum(): void
    {
        // Estagiários de outros setores recebem o pool comum, não o lendário.
        $estagiario = Estagiario::factory()->create([
            'username' => 'estagiario.cogep',
            'setor_id' => Setor::firstOrCreate(['sigla' => 'COGEP'], ['ativo' => true])->id,
            'buddy_tipo' => null,
        ]);

        app(BuddyService::class)->garantirBuddy($estagiario, 'E');

        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos'),
        );
        $this->assertNotContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_lendarios'),
        );
    }

    public function test_garantir_buddy_de_supervisor_nao_recebe_lendaria(): void
    {
        // Servidores e admin continuam no pool sênior — lendárias são
        // exclusivas dos estagiários, independente do setor.
        $estagiario = Estagiario::factory()->create([
            'username' => 'servidor.qualquer',
            'setor_id' => Setor::firstOrCreate(['sigla' => 'STI'], ['ativo' => true])->id,
            'buddy_tipo' => null,
        ]);

        app(BuddyService::class)->garantirBuddy($estagiario, 'S');

        $this->assertNotContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_lendarios'),
        );
        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos_supervisores'),
        );
    }

    public function test_carta_lendaria_waldirene_existe_no_pool(): void
    {
        $this->assertContains('waldirene', config('buddies.tipos_lendarios'));
    }

    public function test_carta_lendaria_waldirene_tem_perfil_completo(): void
    {
        $perfil = config('buddies.perfis.waldirene');

        $this->assertIsArray($perfil);
        foreach (['emoji', 'nome', 'personalidade', 'raridade', 'classe', 'habilidade', 'flavor', 'historia'] as $campo) {
            $this->assertArrayHasKey($campo, $perfil, "campo {$campo} ausente no perfil da waldirene");
            $this->assertNotEmpty($perfil[$campo], "campo {$campo} vazio no perfil da waldirene");
        }
        $this->assertSame('lendaria', $perfil['raridade']);
    }

    public function test_carta_lendaria_waldirene_tem_frases_para_todos_dias_e_status(): void
    {
        $frases = config('buddies.frases.waldirene');

        $this->assertIsArray($frases);
        foreach (['segunda', 'terca', 'quarta', 'quinta', 'sexta'] as $dia) {
            foreach (['aguardando_entrada', 'em_andamento', 'concluido'] as $status) {
                $this->assertNotEmpty(
                    $frases[$dia][$status] ?? [],
                    "frases.waldirene.{$dia}.{$status} vazio",
                );
            }
        }
        $this->assertNotEmpty($frases['generica'] ?? [], 'frases.waldirene.generica vazio');
        $this->assertNotEmpty($frases['boas_vindas'] ?? [], 'frases.waldirene.boas_vindas vazio');
    }

    public function test_montar_popula_sprite_quando_png_existe(): void
    {
        $diretorio = $this->bindSpriteFake();
        file_put_contents($diretorio.'/coruja.png', 'fake');

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->montar($estagiario, 'aguardando_entrada');

        $this->assertSame('/images/buddies/coruja.png', $data->sprite);
    }

    public function test_montar_deixa_sprite_null_quando_png_ausente(): void
    {
        $this->bindSpriteFake();

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->montar($estagiario, 'aguardando_entrada');

        $this->assertNull($data->sprite);
    }

    public function test_boas_vindas_popula_sprite_quando_png_existe(): void
    {
        $diretorio = $this->bindSpriteFake();
        file_put_contents($diretorio.'/coruja.png', 'fake');

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $data = app(BuddyService::class)->boasVindas($estagiario);

        $this->assertSame('/images/buddies/coruja.png', $data->sprite);
    }
}
