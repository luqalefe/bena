<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Support\BuddySprite;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_exige_autenticacao(): void
    {
        config(['authelia.dev_bypass' => false]);

        $this->get('/')->assertStatus(401);
    }

    public function test_dashboard_renderiza_cards_e_botao_de_entrada_quando_aguardando(): void
    {
        Carbon::setTestNow('2026-05-04 09:00:00');
        $estagiario = Estagiario::factory()->create();

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get('/');

        $response->assertStatus(200);
        $response->assertSee('Status hoje');
        $response->assertSee('Horas no mês');
        $response->assertSee('Dias batidos');
        $response->assertSee('Bater entrada');
        $response->assertDontSee('Bater saída');
    }

    public function test_dashboard_mostra_botao_de_saida_quando_em_andamento(): void
    {
        Carbon::setTestNow('2026-05-04 09:30:00');
        $estagiario = Estagiario::factory()->create();
        Frequencia::create([
            'estagiario_id' => $estagiario->id,
            'data' => '2026-05-04',
            'entrada' => '09:30:00',
        ]);
        Carbon::setTestNow('2026-05-04 11:00:00');

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get('/');

        $response->assertStatus(200);
        $response->assertSee('Em andamento desde 09:30');
        $response->assertSee('Bater saída');
        $response->assertDontSee('>Bater entrada<', false);
    }

    public function test_dashboard_tem_link_para_folha_mensal_do_mes_corrente(): void
    {
        $estagiario = Estagiario::factory()->create();

        $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get('/')
            ->assertSee(route('frequencia.atual'), false)
            ->assertSee('Ver folha mensal');
    }

    public function test_dashboard_renderiza_img_do_buddy_quando_sprite_existe(): void
    {
        $diretorio = sys_get_temp_dir().'/buddy-sprite-feature-'.uniqid();
        mkdir($diretorio, 0o755, true);
        file_put_contents($diretorio.'/coruja.png', 'fake');
        $this->app->instance(
            BuddySprite::class,
            new BuddySprite($diretorio, '/images/buddies'),
        );

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        try {
            $response = $this->withHeaders([
                'Remote-User' => $estagiario->username,
                'Remote-Groups' => 'estagiarios',
            ])->get('/');

            $response->assertStatus(200);
            $response->assertSee('src="/images/buddies/coruja.png"', false);
            $response->assertDontSee('aria-hidden="true">🦉<', false);
        } finally {
            unlink($diretorio.'/coruja.png');
            @rmdir($diretorio);
        }
    }

    public function test_dashboard_renderiza_emoji_quando_sprite_ausente(): void
    {
        $diretorio = sys_get_temp_dir().'/buddy-sprite-feature-'.uniqid();
        mkdir($diretorio, 0o755, true);
        $this->app->instance(
            BuddySprite::class,
            new BuddySprite($diretorio, '/images/buddies'),
        );

        $estagiario = Estagiario::factory()->comBuddy('coruja')->create();

        try {
            $response = $this->withHeaders([
                'Remote-User' => $estagiario->username,
                'Remote-Groups' => 'estagiarios',
            ])->get('/');

            $response->assertStatus(200);
            $response->assertSee('🦉');
            $response->assertDontSee('src="/images/buddies/coruja.png"', false);
        } finally {
            @rmdir($diretorio);
        }
    }

    public function test_dashboard_renderiza_horas_e_dias_do_mes(): void
    {
        Carbon::setTestNow('2026-05-15 09:00:00');
        $estagiario = Estagiario::factory()->create();
        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-05-04', 'entrada' => '09:00:00', 'saida' => '14:00:00', 'horas' => 5.00]);
        Frequencia::create(['estagiario_id' => $estagiario->id, 'data' => '2026-05-05', 'entrada' => '09:00:00', 'saida' => '14:30:00', 'horas' => 5.50]);

        $response = $this->withHeaders([
            'Remote-User' => $estagiario->username,
            'Remote-Groups' => 'estagiarios',
        ])->get('/');

        $response->assertSee('10,50 h');
        $response->assertSee('2', false); // dias batidos
    }
}
