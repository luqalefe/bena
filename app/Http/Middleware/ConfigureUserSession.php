<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\Estagiario;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ConfigureUserSession
{
    private const GRUPOS_VALIDOS = ['admin', 'supervisores', 'estagiarios'];

    /**
     * Rotas que apenas o grupo admin pode acessar.
     *
     * @var array<int, string>
     */
    protected array $adminOnlyRouteNames = [
        'admin.dashboard',
        'admin.feriados.create',
        'admin.feriados.store',
        'admin.feriados.edit',
        'admin.feriados.update',
        'admin.feriados.confirmDestroy',
        'admin.feriados.destroy',
        'admin.estagiarios.index',
        'admin.estagiarios.edit',
        'admin.estagiarios.update',
        'admin.supervisores.index',
        'admin.supervisores.create',
        'admin.supervisores.store',
        'admin.supervisores.edit',
        'admin.supervisores.update',
        'admin.supervisores.destroy',
        'admin.estagiarios.recessos.index',
        'admin.estagiarios.recessos.store',
        'admin.estagiarios.recessos.destroy',
        'admin.auditoria.index',
    ];

    public function handle(Request $request, Closure $next): Response
    {
        [$username, $grupos, $nome, $email] = $this->resolverIdentidade($request);

        if ($username === null) {
            abort(401);
        }

        $matchingGroups = array_values(array_intersect($grupos, self::GRUPOS_VALIDOS));

        if ($matchingGroups === []) {
            abort(403);
        }

        $grupoDeAcesso = $this->retornaAcesso($matchingGroups[0]);

        session([
            'user' => [
                'username' => $username,
                'groups' => $grupos,
            ],
            'matchingGroups' => $matchingGroups,
            'grupodeacesso' => $grupoDeAcesso,
        ]);

        $routeName = optional($request->route())->getName();

        if ($routeName !== null
            && in_array($routeName, $this->adminOnlyRouteNames, true)
            && $grupoDeAcesso !== '0') {
            abort(403);
        }

        $estagiario = Estagiario::firstOrNew(['username' => $username]);
        $estagiario->fill([
            'nome' => $nome,
            'email' => $email,
        ]);
        $estagiario->save();

        Auth::setUser($estagiario);

        return $next($request);
    }

    /**
     * @return array{0: ?string, 1: array<int, string>, 2: string, 3: string}
     */
    private function resolverIdentidade(Request $request): array
    {
        $username = $request->header('Remote-User');

        if ($username !== null) {
            return [
                $username,
                $this->extrairGrupos($request->header('Remote-Groups', '')),
                $request->header('Remote-Name', $username),
                $request->header('Remote-Email', $username.'@local'),
            ];
        }

        if ($this->bypassPermitido()) {
            return $this->identidadeSimulada($request);
        }

        return [null, [], '', ''];
    }

    /**
     * Em dev, prefere o override de sessão (gerenciado em /_dev/sessao)
     * sobre os defaults do .env. Permite trocar de usuário simulado em
     * runtime sem reiniciar o container.
     *
     * @return array{0: string, 1: array<int, string>, 2: string, 3: string}
     */
    private function identidadeSimulada(Request $request): array
    {
        $sessao = $request->hasSession()
            ? (array) $request->session()->get('dev_session', [])
            : [];

        $username = $sessao['username'] ?? config('authelia.dev_user');
        $grupos = $sessao['groups'] ?? config('authelia.dev_groups', '');
        $nome = $sessao['nome'] ?? config('authelia.dev_name', $username);
        $email = $sessao['email'] ?? config('authelia.dev_email', $username.'@local');

        return [
            (string) $username,
            $this->extrairGrupos((string) $grupos),
            (string) $nome,
            (string) $email,
        ];
    }

    private function bypassPermitido(): bool
    {
        return (bool) config('authelia.dev_bypass')
            && app()->environment() !== 'production';
    }

    /**
     * @return array<int, string>
     */
    private function extrairGrupos(string $header): array
    {
        return array_values(array_filter(array_map('trim', explode(',', $header))));
    }

    private function retornaAcesso(string $grupo): ?string
    {
        return match ($grupo) {
            'admin' => '0',
            'supervisores' => 'S',
            'estagiarios' => 'E',
            default => null,
        };
    }
}
