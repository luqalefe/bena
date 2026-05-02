<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Estagiario;
use App\Services\AssinaturaService;
use App\Services\FolhaMensalService;
use App\Services\PdfFolhaMensalService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response;

class FolhaMensalController extends Controller
{
    public function show(int $ano, int $mes, Request $request, FolhaMensalService $service, AssinaturaService $assinaturas): View
    {
        /** @var Estagiario $usuario */
        $usuario = auth()->user();

        $alvoUsername = $request->query('estagiario');
        $alvo = $this->resolverAlvo($usuario, $alvoUsername);

        $modoAdmin = $alvo->id !== $usuario->id;
        $folha = $service->montar($alvo, $ano, $mes);
        $verificacoes = $assinaturas->verificar($alvo, $ano, $mes);

        $grupo = session('grupodeacesso');
        $souProprioEstagiario = $grupo === 'E' && $alvo->id === $usuario->id;
        $souSupervisorResponsavel = $grupo === 'S' && $alvo->supervisor_username === $usuario->username;

        return view('frequencia.show', [
            'folha' => $folha,
            'estagiario' => $alvo,
            'modoAdmin' => $modoAdmin,
            'queryNavegacao' => $modoAdmin ? ['estagiario' => $alvo->username] : [],
            'verificacoes' => $verificacoes,
            'podeAssinarComoEstagiario' => $souProprioEstagiario && ! $this->jaAssinou($verificacoes, 'estagiario'),
            'podeContraAssinarComoSupervisor' => $souSupervisorResponsavel
                && $this->jaAssinou($verificacoes, 'estagiario')
                && ! $this->jaAssinou($verificacoes, 'supervisor'),
            'podeEditarObservacao' => $souProprioEstagiario && ! $this->jaAssinou($verificacoes, 'estagiario'),
        ]);
    }

    private function jaAssinou(array $verificacoes, string $papel): bool
    {
        foreach ($verificacoes as $v) {
            if ($v['papel'] === $papel) {
                return true;
            }
        }

        return false;
    }

    public function pdf(int $ano, int $mes, Request $request, PdfFolhaMensalService $pdfService): Response
    {
        /** @var Estagiario $usuario */
        $usuario = auth()->user();

        $alvoUsername = $request->query('estagiario');
        $alvo = $this->resolverAlvo($usuario, $alvoUsername);

        $pdf = $pdfService->gerar($alvo, $ano, $mes);
        $nome = $pdfService->nomeArquivo($alvo, $ano, $mes);

        return $pdf->download($nome);
    }

    public function redirectMesCorrente(): RedirectResponse
    {
        $hoje = CarbonImmutable::now();

        return redirect()->route('frequencia.show', [
            'ano' => $hoje->year,
            'mes' => $hoje->month,
        ]);
    }

    private function resolverAlvo(Estagiario $usuario, ?string $alvoUsername): Estagiario
    {
        if ($alvoUsername === null || $alvoUsername === $usuario->username) {
            return $usuario;
        }

        $grupo = session('grupodeacesso');
        $alvo = Estagiario::where('username', $alvoUsername)->first();

        if ($grupo === '0') {
            if ($alvo === null) {
                abort(404);
            }

            return $alvo;
        }

        if ($grupo === 'S' && $alvo !== null && $alvo->supervisor_username === $usuario->username) {
            return $alvo;
        }

        abort(403);
    }
}
