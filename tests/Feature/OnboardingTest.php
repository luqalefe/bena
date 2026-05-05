<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estagiario;
use App\Support\BuddySprite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    private string $diretorioSprites = '';

    protected function tearDown(): void
    {
        if ($this->diretorioSprites !== '') {
            foreach (glob($this->diretorioSprites.'/*') ?: [] as $f) {
                unlink($f);
            }
            @rmdir($this->diretorioSprites);
        }
        parent::tearDown();
    }

    private function bindSpriteFake(): string
    {
        $this->diretorioSprites = sys_get_temp_dir().'/onboarding-sprite-'.uniqid();
        mkdir($this->diretorioSprites, 0o755, true);
        $this->app->instance(
            BuddySprite::class,
            new BuddySprite($this->diretorioSprites, '/images/buddies'),
        );

        return $this->diretorioSprites;
    }

    /** @return array<string, string> */
    private function estagiarioHeaders(string $username): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'estagiarios'];
    }

    /** @return array<string, string> */
    private function adminHeaders(string $username): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'admin'];
    }

    /** @return array<string, string> */
    private function supervisorHeaders(string $username): array
    {
        return ['Remote-User' => $username, 'Remote-Groups' => 'supervisores'];
    }

    public function test_estagiario_sem_tutorial_visto_redireciona_para_bem_vindo(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get('/')
            ->assertRedirect(route('onboarding.show'));
    }

    public function test_estagiario_com_tutorial_visto_segue_para_dashboard(): void
    {
        Estagiario::factory()->create(['username' => 'veterano.dev']);

        $this->withHeaders($this->estagiarioHeaders('veterano.dev'))
            ->get('/')
            ->assertStatus(200);
    }

    public function test_admin_sem_tutorial_visto_redireciona(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'rh.novato']);

        $this->withHeaders($this->adminHeaders('rh.novato'))
            ->get('/admin')
            ->assertRedirect(route('onboarding.show'));
    }

    public function test_supervisor_sem_tutorial_visto_redireciona(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'super.novato']);

        $this->withHeaders($this->supervisorHeaders('super.novato'))
            ->get('/supervisor')
            ->assertRedirect(route('onboarding.show'));
    }

    public function test_get_bem_vindo_renderiza_view_explicativa(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'))
            ->assertStatus(200)
            ->assertSee('Bem-vindo ao Bena')
            ->assertSee('Bater ponto')
            ->assertSee('Folha mensal')
            ->assertSee('Assinatura digital')
            ->assertSee('Entendi');
    }

    public function test_bem_vindo_apresenta_lucander_como_narrador(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'))
            ->assertSee('Lucander')
            ->assertSee('criador do Bena');
    }

    public function test_hero_renderiza_sprite_do_lucander_quando_existe(): void
    {
        $diretorio = $this->bindSpriteFake();
        file_put_contents($diretorio.'/lucas.png', 'fake');

        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'));

        $response->assertSee('src="/images/buddies/lucas.png"', false);
        $response->assertSee('bena-onboarding-hero__narrator-card', false);
    }

    public function test_hero_renderiza_emoji_quando_sprite_do_lucander_ausente(): void
    {
        $this->bindSpriteFake();

        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $response = $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'));

        $response->assertSee('🧙‍♂️');
        $response->assertDontSee('src="/images/buddies/lucas.png"', false);
    }

    public function test_estagiario_ve_fluxo_de_estagiario(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'))
            ->assertSee('Bater ponto')
            ->assertSee('Folha mensal')
            ->assertSee('Assinatura')
            ->assertDontSee('contra-assina');
    }

    public function test_supervisor_ve_fluxo_de_supervisor(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'super.novato']);

        $this->withHeaders($this->supervisorHeaders('super.novato'))
            ->get(route('onboarding.show'))
            ->assertSee('Revisar')
            ->assertSee('Contra-assinar')
            ->assertDontSee('Bater ponto');
    }

    public function test_admin_ve_fluxo_de_admin(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'rh.novato']);

        $this->withHeaders($this->adminHeaders('rh.novato'))
            ->get(route('onboarding.show'))
            ->assertSee('Auditoria')
            ->assertSee('Feriados')
            ->assertSee('Cadastrar')
            ->assertDontSee('Bater ponto')
            ->assertDontSee('Contra-assinar');
    }

    public function test_mascote_aparece_apos_explicacao_nao_no_topo(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'))
            ->assertSeeInOrder([
                'criador do Bena',
                'Por que Bena',
                'Bater ponto',
                'Conheça seu mascote',
            ]);
    }

    public function test_card_mascote_inicia_oculto_e_botao_descobrir_existe(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'))
            ->assertSee('Descobrir meu mascote')
            ->assertSee('data-buddy-reveal="false"', false);
    }

    public function test_view_inclui_origem_do_nome_bena(): void
    {
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get(route('onboarding.show'))
            ->assertSee('Por que Bena')
            ->assertSee('Hãtxa Kuĩ')
            ->assertSee('Huni Kuin')
            ->assertSee('Xinã Bena');
    }

    public function test_get_bem_vindo_acessivel_mesmo_apos_visto(): void
    {
        // user já viu — pode revisitar livremente
        Estagiario::factory()->create(['username' => 'veterano.dev']);

        $this->withHeaders($this->estagiarioHeaders('veterano.dev'))
            ->get(route('onboarding.show'))
            ->assertStatus(200);
    }

    public function test_post_concluir_seta_timestamp_e_redireciona_para_dashboard(): void
    {
        $estagiario = Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->post(route('onboarding.concluir'))
            ->assertRedirect(route('dashboard'));

        $this->assertNotNull($estagiario->fresh()->tutorial_visto_em);
    }

    public function test_middleware_nao_redireciona_em_rotas_nao_home(): void
    {
        // Estagiário ainda não viu tutorial, mas pode acessar a folha mensal
        // (link salvo, retorno do supervisor, etc) sem ser barrado.
        Estagiario::factory()->semOnboarding()->create(['username' => 'novato.dev']);

        $this->withHeaders($this->estagiarioHeaders('novato.dev'))
            ->get('/frequencia/2026/4')
            ->assertStatus(200);
    }
}
