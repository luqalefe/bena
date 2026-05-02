<?php

declare(strict_types=1);

namespace Tests\Feature\Http;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevSessionControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_form_renderiza_em_dev(): void
    {
        config(['authelia.dev_bypass' => true]);

        $response = $this->get('/_dev/sessao');

        $response->assertStatus(200);
        $response->assertSee('Configurar usuário simulado');
    }

    public function test_post_seta_usuario_simulado_na_sessao(): void
    {
        config(['authelia.dev_bypass' => true]);

        $response = $this->post('/_dev/sessao', [
            'username' => 'marco.admin',
            'groups' => 'admin',
            'nome' => 'Marco Admin',
            'email' => 'marco.admin@example.local',
        ]);

        $response->assertRedirect('/');
        $response->assertSessionHas('dev_session.username', 'marco.admin');
        $response->assertSessionHas('dev_session.groups', 'admin');
    }

    public function test_post_reset_limpa_a_sessao(): void
    {
        config(['authelia.dev_bypass' => true]);

        $response = $this->withSession([
            'dev_session' => ['username' => 'qualquer'],
        ])->post('/_dev/sessao/reset');

        $response->assertRedirect('/');
        $response->assertSessionMissing('dev_session');
    }

    public function test_get_em_producao_retorna_404(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['authelia.dev_bypass' => false]);

        $this->get('/_dev/sessao')->assertStatus(404);
    }

    public function test_post_em_producao_retorna_404(): void
    {
        $this->app->detectEnvironment(fn () => 'production');
        config(['authelia.dev_bypass' => false]);

        $this->withoutMiddleware(ValidateCsrfToken::class)
            ->post('/_dev/sessao', ['username' => 'x'])
            ->assertStatus(404);
    }
}
