<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Estagiario;
use App\Support\BuddySprite;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuddyDashboardTest extends TestCase
{
    use RefreshDatabase;

    private string $diretorioSprites = '';

    protected function setUp(): void
    {
        parent::setUp();
        $this->diretorioSprites = sys_get_temp_dir().'/buddy-dashboard-sprite-'.uniqid();
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

    public function test_dashboard_exibe_buddy_para_estagiario(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get('/');

        $response->assertStatus(200);
        $response->assertSee('Coruinha');
        $response->assertSee('🦉', false);
        $response->assertSee('<div class="bena-buddy-card"', false);
    }

    public function test_dashboard_nao_exibe_buddy_para_admin(): void
    {
        Estagiario::factory()->create(['username' => 'rh.admin']);

        $response = $this->withHeaders([
            'Remote-User' => 'rh.admin',
            'Remote-Groups' => 'admin',
        ])->get('/admin');

        $response->assertStatus(200);
        $response->assertDontSee('<div class="bena-buddy-card"', false);
    }

    public function test_buddy_atribuido_automaticamente_no_primeiro_acesso(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->create(['buddy_tipo' => null]);

        $this->assertNull($estagiario->buddy_tipo);

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get('/')->assertStatus(200);

        $this->assertNotNull($estagiario->fresh()->buddy_tipo);
        $this->assertContains(
            $estagiario->fresh()->buddy_tipo,
            config('buddies.tipos'),
        );
    }

    public function test_buddy_persiste_entre_acessos(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->comBuddy('cachorro')->create();

        $headers = [
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ];

        $this->withHeaders($headers)->get('/')->assertStatus(200);
        $this->assertSame('cachorro', $estagiario->fresh()->buddy_tipo);

        $this->withHeaders($headers)->get('/')->assertStatus(200);
        $this->assertSame('cachorro', $estagiario->fresh()->buddy_tipo);
    }

    public function test_onboarding_exibe_buddy_para_estagiario(): void
    {
        $estagiario = Estagiario::factory()
            ->semOnboarding()
            ->comBuddy('capivara')
            ->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get(route('onboarding.show'));

        $response->assertStatus(200);
        $response->assertSee('Capi');
        $response->assertSee('🦫', false);
        $response->assertSee('<div class="bena-buddy-card', false);
    }

    public function test_onboarding_exibe_buddy_para_admin_e_supervisor(): void
    {
        // O buddy aparece pra TODOS os grupos no /bem-vindo (tela de
        // apresentação do sistema). A restrição "admin não vê buddy"
        // se aplica só ao dashboard de cada grupo, não ao onboarding.
        Estagiario::factory()->semOnboarding()->create(['username' => 'rh.admin.novo']);

        $this->withHeaders([
            'Remote-User' => 'rh.admin.novo',
            'Remote-Groups' => 'admin',
        ])->get(route('onboarding.show'))
            ->assertStatus(200)
            ->assertSee('<div class="bena-buddy-card', false);

        Estagiario::factory()->semOnboarding()->create(['username' => 'super.novo']);

        $this->withHeaders([
            'Remote-User' => 'super.novo',
            'Remote-Groups' => 'supervisores',
        ])->get(route('onboarding.show'))
            ->assertStatus(200)
            ->assertSee('<div class="bena-buddy-card', false);
    }

    public function test_onboarding_atribui_buddy_no_primeiro_acesso(): void
    {
        $estagiario = Estagiario::factory()
            ->semOnboarding()
            ->create(['buddy_tipo' => null]);

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get(route('onboarding.show'))->assertStatus(200);

        $this->assertNotNull($estagiario->fresh()->buddy_tipo);
    }
}
