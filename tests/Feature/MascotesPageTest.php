<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estagiario;
use App\Support\BuddySprite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MascotesPageTest extends TestCase
{
    use RefreshDatabase;

    private string $diretorioSprites = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->diretorioSprites = sys_get_temp_dir().'/mascotes-sprite-'.uniqid();
        mkdir($this->diretorioSprites, 0o755, true);
        $this->app->instance(
            BuddySprite::class,
            new BuddySprite($this->diretorioSprites, '/images/buddies'),
        );
    }

    protected function tearDown(): void
    {
        foreach (glob($this->diretorioSprites.'/*') ?: [] as $f) {
            unlink($f);
        }
        @rmdir($this->diretorioSprites);
        parent::tearDown();
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(): array
    {
        return ['Remote-User' => 'lucas.dev', 'Remote-Groups' => 'estagiarios'];
    }

    /** @return array<string, string> */
    private function adminHeaders(): array
    {
        return ['Remote-User' => 'rh.admin', 'Remote-Groups' => 'admin'];
    }

    /** @return array<string, string> */
    private function supervisorHeaders(): array
    {
        return ['Remote-User' => 'super.dev', 'Remote-Groups' => 'supervisores'];
    }

    public function test_pagina_mascotes_acessivel_para_qualquer_grupo(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);
        Estagiario::factory()->create(['username' => 'rh.admin']);
        Estagiario::factory()->create(['username' => 'super.dev']);

        $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'))->assertStatus(200);

        $this->withHeaders($this->adminHeaders())
            ->get(route('mascotes.index'))->assertStatus(200);

        $this->withHeaders($this->supervisorHeaders())
            ->get(route('mascotes.index'))->assertStatus(200);
    }

    public function test_pagina_mascotes_exibe_pool_padrao(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'));

        $response->assertStatus(200);
        foreach (config('buddies.tipos') as $tipo) {
            $perfil = config("buddies.perfis.{$tipo}");
            $response->assertSee($perfil['emoji'], false);
            $response->assertSee($perfil['nome']);
        }
    }

    public function test_pagina_mascotes_exibe_pool_senior(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'));

        $response->assertStatus(200);
        foreach (config('buddies.tipos_supervisores') as $tipo) {
            $perfil = config("buddies.perfis.{$tipo}");
            $response->assertSee($perfil['emoji'], false);
            $response->assertSee($perfil['nome']);
        }
    }

    public function test_pagina_mascotes_exibe_historia_e_personalidade(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'));

        $response->assertStatus(200);
        $coruja = config('buddies.perfis.coruja');
        $response->assertSee($coruja['personalidade']);
        $response->assertSee(substr($coruja['historia'], 0, 30));
    }

    public function test_pagina_mascotes_exibe_pool_lendario(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'));

        $response->assertStatus(200);
        foreach (config('buddies.tipos_lendarios') as $tipo) {
            $perfil = config("buddies.perfis.{$tipo}");
            $response->assertSee($perfil['emoji'], false);
            $response->assertSee($perfil['nome']);
            $response->assertSee(substr($perfil['flavor'], 0, 20));
        }
    }

    public function test_pagina_mascotes_exibe_titulo_da_secao_lendaria(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'));

        $response->assertStatus(200);
        $response->assertSee('Lendárias');
    }

    public function test_pagina_mascotes_inclui_player_de_audio_com_trilha(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'));

        $response->assertStatus(200);
        $response->assertSee('<audio', false);
        $response->assertSee('/audio/bena-master.mp3', false);
    }

    public function test_link_para_mascotes_no_onboarding_aciona_autoplay(): void
    {
        $estagiario = Estagiario::factory()
            ->semOnboarding()
            ->comBuddy('coruja')
            ->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get(route('onboarding.show'));

        $response->assertStatus(200);
        $response->assertSee(route('mascotes.index').'?autoplay=1', false);
    }

    public function test_player_aparece_em_todas_as_views_autenticadas(): void
    {
        // O player vive no layout global (@auth), então qualquer view que
        // estende layouts.app e tem usuário logado renderiza o widget.
        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('<audio', false);
        $response->assertSee('/audio/bena-master.mp3', false);
        $response->assertSee('bena-player__cover', false);
        $response->assertSee('bena-player__close', false);
    }

    public function test_player_marcado_como_turbo_permanent_para_navegacao_fluida(): void
    {
        // O atributo data-turbo-permanent garante que o Turbo preserve o
        // widget (e seu <audio>) ao trocar de página — sem isso o áudio
        // cortaria a cada navegação. Não remover.
        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get(route('dashboard'));

        $response->assertStatus(200);
        $response->assertSee('data-turbo-permanent', false);
        $response->assertSee('@hotwired/turbo', false);
    }

    public function test_player_mostra_placeholder_quando_sem_buddy(): void
    {
        // Estagiário sem buddy_tipo ainda → player começa em modo "aguardando
        // sorteio", sem fallback hardcoded de Lucander.
        // Usamos /mascotes pois MascotesController não chama garantirBuddy
        // (a dashboard chamaria, atribuindo um buddy aleatório).
        Estagiario::factory()->create([
            'username' => 'novato.player',
            'buddy_tipo' => null,
        ]);

        $response = $this->withHeaders([
            'Remote-User' => 'novato.player',
            'Remote-Groups' => 'estagiarios',
        ])->get(route('mascotes.index'));

        $response->assertStatus(200);
        $response->assertSee('bena-player__cover-placeholder', false);
        $response->assertSee('Aguardando sorteio', false);
        // Lucander aparece como carta lendária na página, então não dá pra
        // assertDontSee no nome dele globalmente — em vez disso, garantimos
        // que o player NÃO tem um <img> com src apontando pro lucas.png.
        $response->assertDontSee('<img src="http://localhost/images/buddies/lucas.png"', false);
    }

    public function test_player_usa_buddy_do_usuario_como_capa(): void
    {
        // Player deve mostrar o mascote sorteado pro usuário, não Lucander
        // (fallback). Aqui o user tem buddy=waldirene → cover = waldirene.png.
        // setUp já injeta BuddySprite numa dir temp vazia; criamos o sprite
        // fake pra a resolução não cair no fallback.
        file_put_contents($this->diretorioSprites.'/waldirene.png', 'fake');

        Estagiario::factory()->comBuddy('waldirene')->create([
            'username' => 'lucas.dev',
        ]);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('dashboard'));

        $response->assertStatus(200);
        // Cover img do player carrega o sprite do buddy do usuário.
        $response->assertSee('/images/buddies/waldirene.png', false);
        // Artist label do player mostra o nome do buddy.
        $response->assertSee(config('buddies.perfis.waldirene.nome'));
    }

    public function test_player_tem_botao_de_fechar(): void
    {
        Estagiario::factory()->create(['username' => 'lucas.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders())
            ->get(route('mascotes.index'));

        $response->assertStatus(200);
        $response->assertSee('bena-player__close', false);
        $response->assertSee('Fechar player', false);
    }

    public function test_botao_mascotes_aparece_no_onboarding(): void
    {
        $estagiario = Estagiario::factory()
            ->semOnboarding()
            ->comBuddy('coruja')
            ->create();

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get(route('onboarding.show'))
            ->assertStatus(200)
            ->assertSee(route('mascotes.index'), false);
    }
}
