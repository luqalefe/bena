<?php

declare(strict_types=1);

namespace Tests\Feature\Middleware;

use App\Http\Middleware\ConfigureUserSession;
use App\Models\Estagiario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class ConfigureUserSessionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Route::middleware([StartSession::class, ConfigureUserSession::class])->get('/_test/me', function () {
            return [
                'username' => auth()->user()?->username,
                'session_user' => session('user'),
                'matching_groups' => session('matchingGroups'),
                'grupo_de_acesso' => session('grupodeacesso'),
            ];
        });

        Route::middleware([StartSession::class, ConfigureUserSession::class])
            ->get('/_test/admin-only', fn () => ['ok' => true])
            ->name('admin.feriados.index');
    }

    public function test_sem_remote_user_retorna_401(): void
    {
        $response = $this->get('/_test/me');

        $response->assertStatus(401);
    }

    public function test_dev_bypass_autentica_usuario_simulado_quando_nao_em_producao(): void
    {
        config([
            'authelia.dev_bypass' => true,
            'authelia.dev_user' => 'lucas.dev',
            'authelia.dev_groups' => 'estagiarios',
            'authelia.dev_name' => 'Lucas Dev',
            'authelia.dev_email' => 'lucas.dev@example.local',
        ]);

        $response = $this->get('/_test/me');

        $response->assertStatus(200);
        $response->assertJson(['username' => 'lucas.dev']);
        $this->assertDatabaseHas('estagiarios', [
            'username' => 'lucas.dev',
            'nome' => 'Lucas Dev',
        ]);
    }

    public function test_dev_bypass_usa_session_override_quando_presente(): void
    {
        config([
            'authelia.dev_bypass' => true,
            'authelia.dev_user' => 'lucas.dev',
            'authelia.dev_groups' => 'estagiarios',
        ]);

        $response = $this->withSession([
            'dev_session' => [
                'username' => 'marco.admin',
                'groups' => 'admin',
                'nome' => 'Marco Admin',
                'email' => 'marco.admin@example.local',
            ],
        ])->get('/_test/me');

        $response->assertStatus(200);
        $response->assertJson(['username' => 'marco.admin']);
        $this->assertDatabaseHas('estagiarios', [
            'username' => 'marco.admin',
            'nome' => 'Marco Admin',
        ]);
    }

    public function test_dev_bypass_ignorado_em_producao(): void
    {
        config([
            'authelia.dev_bypass' => true,
            'authelia.dev_user' => 'lucas.dev',
            'authelia.dev_groups' => 'estagiarios',
        ]);
        $this->app->detectEnvironment(fn () => 'production');

        $response = $this->get('/_test/me');

        $response->assertStatus(401);
    }

    public function test_grupo_invalido_retorna_403(): void
    {
        $response = $this->withHeaders([
            'Remote-User' => 'externo.dev',
            'Remote-Groups' => 'visitantes,outros',
            'Remote-Name' => 'Externo',
            'Remote-Email' => 'externo@example.local',
        ])->get('/_test/me');

        $response->assertStatus(403);
        $this->assertDatabaseMissing('estagiarios', ['username' => 'externo.dev']);
    }

    public function test_remote_user_existente_atualiza_dados(): void
    {
        Estagiario::factory()->create([
            'username' => 'lucas.dev',
            'nome' => 'Nome Antigo',
            'email' => 'antigo@example.local',
        ]);

        $this->withHeaders([
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
            'Remote-Name' => 'Lucas Atualizado',
            'Remote-Email' => 'lucas.novo@example.local',
        ])->get('/_test/me')->assertStatus(200);

        $this->assertDatabaseHas('estagiarios', [
            'username' => 'lucas.dev',
            'nome' => 'Lucas Atualizado',
            'email' => 'lucas.novo@example.local',
        ]);
        $this->assertDatabaseCount('estagiarios', 1);
    }

    public function test_remote_user_valido_cria_estagiario_e_autentica(): void
    {
        $response = $this->withHeaders([
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
            'Remote-Name' => 'Lucas Estagiario',
            'Remote-Email' => 'lucas.dev@example.local',
        ])->get('/_test/me');

        $response->assertStatus(200);
        $response->assertJson(['username' => 'lucas.dev']);

        $this->assertDatabaseHas('estagiarios', [
            'username' => 'lucas.dev',
            'nome' => 'Lucas Estagiario',
            'email' => 'lucas.dev@example.local',
        ]);
    }

    public function test_handle_persiste_username_e_groups_em_session_user(): void
    {
        $this->withHeaders([
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios,outros',
            'Remote-Name' => 'Lucas',
            'Remote-Email' => 'lucas@example.local',
        ])->get('/_test/me')->assertJson([
            'session_user' => [
                'username' => 'lucas.dev',
                'groups' => ['estagiarios', 'outros'],
            ],
        ]);
    }

    public function test_handle_persiste_apenas_grupos_reconhecidos_em_matching_groups(): void
    {
        $this->withHeaders([
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios,outros,visitantes',
        ])->get('/_test/me')->assertJson([
            'matching_groups' => ['estagiarios'],
        ]);
    }

    public function test_grupodeacesso_admin_eh_zero(): void
    {
        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/_test/me')->assertJson([
            'grupo_de_acesso' => '0',
        ]);
    }

    public function test_grupodeacesso_estagiarios_eh_e(): void
    {
        $this->withHeaders([
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
        ])->get('/_test/me')->assertJson([
            'grupo_de_acesso' => 'E',
        ]);
    }

    public function test_grupodeacesso_supervisores_eh_s(): void
    {
        $this->withHeaders([
            'Remote-User' => 'lucas.supervisor',
            'Remote-Groups' => 'supervisores',
        ])->get('/_test/me')->assertJson([
            'grupo_de_acesso' => 'S',
            'matching_groups' => ['supervisores'],
        ]);
    }

    public function test_supervisor_em_rota_admin_only_recebe_403(): void
    {
        $this->withHeaders([
            'Remote-User' => 'lucas.supervisor',
            'Remote-Groups' => 'supervisores',
        ])->get('/_test/admin-only')->assertStatus(403);
    }

    public function test_estagiario_em_rota_admin_only_recebe_403(): void
    {
        $this->withHeaders([
            'Remote-User' => 'lucas.dev',
            'Remote-Groups' => 'estagiarios',
        ])->get('/_test/admin-only')->assertStatus(403);
    }

    public function test_admin_em_rota_admin_only_passa(): void
    {
        $this->withHeaders([
            'Remote-User' => 'marco.admin',
            'Remote-Groups' => 'admin',
        ])->get('/_test/admin-only')->assertStatus(200)->assertJson(['ok' => true]);
    }
}
