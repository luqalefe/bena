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
