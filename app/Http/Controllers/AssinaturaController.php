<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Services\AssinaturaService;
use Carbon\CarbonImmutable;
use DomainException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AssinaturaController extends Controller
{
    public function __construct(private readonly AssinaturaService $service) {}

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
            $this->service->assinar(
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
            $this->service->assinar(
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

        return redirect()
            ->route('frequencia.show', ['ano' => $ano, 'mes' => $mes, 'estagiario' => $alvo->username])
            ->with('sucesso', 'Folha contra-assinada como supervisor.');
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
