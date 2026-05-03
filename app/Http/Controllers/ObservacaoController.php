<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Models\Assinatura;
use App\Models\Estagiario;
use App\Models\Frequencia;
use App\Services\AssinaturaService;
use App\Services\AuditoriaService;
use App\Services\CalendarioService;
use Carbon\CarbonImmutable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ObservacaoController extends Controller
{
    public function __construct(
        private readonly CalendarioService $calendario,
        private readonly AssinaturaService $assinaturas,
        private readonly AuditoriaService $auditoria,
    ) {}

    public function salvar(int $ano, int $mes, int $dia, Request $request): RedirectResponse
    {
        if (session('grupodeacesso') !== 'E') {
            abort(403);
        }

        $dados = $request->validate([
            'texto' => ['nullable', 'string', 'max:500'],
        ]);

        $data = CarbonImmutable::create($ano, $mes, $dia);

        if (! $this->calendario->ehDiaUtil($data)) {
            abort(422, 'Observação só pode ser registrada em dia útil.');
        }

        /** @var Estagiario $usuario */
        $usuario = $request->user();

        $jaAssinada = $this->assinaturas->assinaturaDoMes(
            $usuario, $ano, $mes, Assinatura::PAPEL_ESTAGIARIO
        );
        if ($jaAssinada !== null) {
            abort(422, 'Folha já assinada — observações não podem mais ser editadas.');
        }

        $texto = $dados['texto'] ?? null;
        if ($texto === '') {
            $texto = null;
        }

        $frequencia = Frequencia::query()
            ->where('estagiario_id', $usuario->id)
            ->whereDate('data', $data->format('Y-m-d'))
            ->first();

        if ($texto === null) {
            // Apaga a observação. Se a Frequencia só existia pra carregar
            // observação (sem ponto batido), some inteira.
            if ($frequencia !== null) {
                if ($frequencia->entrada === null && $frequencia->saida === null) {
                    $frequencia->delete();
                } else {
                    $frequencia->observacao = null;
                    $frequencia->save();
                }
            }
        } else {
            if ($frequencia === null) {
                $frequencia = new Frequencia([
                    'estagiario_id' => $usuario->id,
                    'data' => $data->format('Y-m-d'),
                ]);
            }
            $frequencia->observacao = $texto;
            $frequencia->save();
        }

        $this->auditoria->registrar(
            usuario: $usuario->username,
            acao: 'frequencia.observacao',
            entidade: 'frequencia',
            entidadeId: $frequencia?->id !== null ? (string) $frequencia->id : null,
            payload: [
                'data' => $data->format('Y-m-d'),
                'texto' => $texto,
            ],
            ip: $request->ip(),
        );

        return redirect()->route('frequencia.show', ['ano' => $ano, 'mes' => $mes]);
    }
}
