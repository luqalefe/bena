<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Services\AssinaturaService;
use App\Services\AuditoriaService;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AssinaturaController extends Controller
{
    public function __construct(
        private readonly AssinaturaService $service,
        private readonly AuditoriaService $auditoria,
    ) {}

    public function assinarComoEstagiario(int $ano, int $mes, Request $request): RedirectResponse
    {
        $alvoUsername = $request->query('estagiario');

        /** @var Estagiario $usuario */
        $usuario = auth()->user();
        $grupo = session('grupodeacesso');

        if ($grupo !== 'E') {
            abort(403);
        }

        if ($alvoUsername !== null && $alvoUsername !== $usuario->username) {
            abort(403);
        }

        $this->validarMesNaoFuturo($ano, $mes);

        try {
            $assinatura = $this->service->assinar(
                $usuario,
                $ano,
                $mes,
                Assinatura::PAPEL_ESTAGIARIO,
                $usuario->username,
                $request->ip()
            );
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['assinatura' => $e->getMessage()]);
        }

        $this->auditoria->registrar(
            usuario: $usuario->username,
            acao: 'assinatura.assinar',
            entidade: 'assinatura',
            entidadeId: (string) $assinatura->id,
            payload: ['papel' => 'estagiario', 'ano' => $ano, 'mes' => $mes, 'estagiario_id' => $usuario->id],
            ip: $request->ip(),
        );

        return redirect()
            ->route('frequencia.show', ['ano' => $ano, 'mes' => $mes])
            ->with('sucesso', 'Folha assinada como estagiário.');
    }

    public function contraAssinarComoSupervisor(int $ano, int $mes, Request $request): RedirectResponse
    {
        $alvoUsername = $request->query('estagiario');
        if ($alvoUsername === null) {
            abort(404);
        }

        /** @var Estagiario $usuario */
        $usuario = auth()->user();
        $grupo = session('grupodeacesso');

        if ($grupo !== 'S') {
            abort(403);
        }

        $alvo = Estagiario::where('username', $alvoUsername)->first();
        if ($alvo === null) {
            abort(404);
        }

        if ($alvo->supervisor_username !== $usuario->username) {
            abort(403);
        }

        $this->validarMesNaoFuturo($ano, $mes);

        try {
            $assinatura = $this->service->assinar(
                $alvo,
                $ano,
                $mes,
                Assinatura::PAPEL_SUPERVISOR,
                $usuario->username,
                $request->ip()
            );
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['assinatura' => $e->getMessage()]);
        }

        $this->auditoria->registrar(
            usuario: $usuario->username,
            acao: 'assinatura.contra-assinar',
            entidade: 'assinatura',
            entidadeId: (string) $assinatura->id,
            payload: ['papel' => 'supervisor', 'ano' => $ano, 'mes' => $mes, 'estagiario_id' => $alvo->id],
            ip: $request->ip(),
        );

        return redirect()
            ->route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $alvo->username])
            ->with('sucesso', 'Folha contra-assinada como supervisor.');
    }

    public function reassinarComoEstagiario(int $ano, int $mes, Request $request): RedirectResponse
    {
        $alvoUsername = $request->query('estagiario');

        /** @var Estagiario $usuario */
        $usuario = auth()->user();
        $grupo = session('grupodeacesso');

        if ($grupo !== 'E') {
            abort(403);
        }

        if ($alvoUsername !== null && $alvoUsername !== $usuario->username) {
            abort(403);
        }

        try {
            $assinatura = $this->service->reassinar(
                $usuario,
                $ano,
                $mes,
                Assinatura::PAPEL_ESTAGIARIO,
                $usuario->username,
                $request->ip()
            );
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['assinatura' => $e->getMessage()]);
        }

        $this->auditoria->registrar(
            usuario: $usuario->username,
            acao: 'assinatura.reassinar',
            entidade: 'assinatura',
            entidadeId: (string) $assinatura->id,
            payload: ['papel' => 'estagiario', 'ano' => $ano, 'mes' => $mes, 'estagiario_id' => $usuario->id],
            ip: $request->ip(),
        );

        return redirect()
            ->route('frequencia.show', ['ano' => $ano, 'mes' => $mes])
            ->with('sucesso', 'Folha re-assinada na versão atual. Assinatura anterior preservada como histórico.');
    }

    public function reContraAssinarComoSupervisor(int $ano, int $mes, Request $request): RedirectResponse
    {
        $alvoUsername = $request->query('estagiario');
        if ($alvoUsername === null) {
            abort(404);
        }

        /** @var Estagiario $usuario */
        $usuario = auth()->user();
        $grupo = session('grupodeacesso');

        if ($grupo !== 'S') {
            abort(403);
        }

        $alvo = Estagiario::where('username', $alvoUsername)->first();
        if ($alvo === null) {
            abort(404);
        }

        if ($alvo->supervisor_username !== $usuario->username) {
            abort(403);
        }

        try {
            $assinatura = $this->service->reassinar(
                $alvo,
                $ano,
                $mes,
                Assinatura::PAPEL_SUPERVISOR,
                $usuario->username,
                $request->ip()
            );
        } catch (DomainException $e) {
            throw ValidationException::withMessages(['assinatura' => $e->getMessage()]);
        }

        $this->auditoria->registrar(
            usuario: $usuario->username,
            acao: 'assinatura.re-contra-assinar',
            entidade: 'assinatura',
            entidadeId: (string) $assinatura->id,
            payload: ['papel' => 'supervisor', 'ano' => $ano, 'mes' => $mes, 'estagiario_id' => $alvo->id],
            ip: $request->ip(),
        );

        return redirect()
            ->route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $alvo->username])
            ->with('sucesso', 'Contra-assinatura refeita na versão atual.');
    }

    private function validarMesNaoFuturo(int $ano, int $mes): void
    {
        $alvo = CarbonImmutable::create($ano, $mes, 1);
        $corrente = CarbonImmutable::now()->startOfMonth();

        if ($alvo->greaterThan($corrente)) {
            throw ValidationException::withMessages([
                'periodo' => 'Mês ainda não fechou. Não é possível assinar.',
            ]);
        }
    }
}
