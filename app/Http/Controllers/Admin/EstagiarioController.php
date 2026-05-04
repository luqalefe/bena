<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEstagiarioRequest;
use App\Models\Estagiario;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class EstagiarioController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function index(Request $request): View
    {
        $lotacao = $request->query('lotacao');

        $estagiarios = Estagiario::query()
            ->when($lotacao, fn ($q) => $q->where('lotacao', $lotacao))
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        $lotacoes = Estagiario::query()
            ->whereNotNull('lotacao')
            ->distinct()
            ->orderBy('lotacao')
            ->pluck('lotacao');

        return view('admin.estagiarios.index', [
            'estagiarios' => $estagiarios,
            'lotacoes' => $lotacoes,
            'lotacao' => $lotacao,
        ]);
    }

    public function edit(Estagiario $estagiario): View
    {
        return view('admin.estagiarios.edit', ['estagiario' => $estagiario]);
    }

    public function update(UpdateEstagiarioRequest $request, Estagiario $estagiario): RedirectResponse
    {
        $dados = $request->validated();

        $estagiario->fill([
            'matricula' => $dados['matricula'] ?? null,
            'lotacao' => $dados['lotacao'] ?? null,
            'supervisor_nome' => $dados['supervisor_nome'] ?? null,
            'supervisor_username' => $dados['supervisor_username'] ?? null,
            'sei' => $dados['sei'] ?? null,
            'inicio_estagio' => $dados['inicio_estagio'] ?? null,
            'fim_estagio' => $dados['fim_estagio'] ?? null,
            'horas_diarias' => $dados['horas_diarias'],
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        if ($request->hasFile('contrato')) {
            if ($estagiario->contrato_path) {
                Storage::disk('local')->delete($estagiario->contrato_path);
            }
            $estagiario->contrato_path = Storage::disk('local')
                ->putFile('contratos', $request->file('contrato'));
        }

        $estagiario->save();

        $this->auditoria->registrar(
            usuario: (string) $request->session()->get('user.username', ''),
            acao: 'estagiario.editar',
            entidade: 'estagiario',
            entidadeId: (string) $estagiario->id,
            payload: [
                'username' => $estagiario->username,
                'campos_atualizados' => array_keys($dados),
            ],
            ip: $request->ip(),
        );

        return redirect()
            ->route('admin.estagiarios.index')
            ->with('sucesso', "Dados de {$estagiario->nome} atualizados.");
    }

    public function contrato(Estagiario $estagiario): StreamedResponse
    {
        if (! $this->podeBaixarContratoDe($estagiario)) {
            abort(403);
        }

        if (! $estagiario->contrato_path) {
            abort(404);
        }

        return Storage::disk('local')->download(
            $estagiario->contrato_path,
            "contrato_{$estagiario->username}.pdf",
            ['Content-Type' => 'application/pdf'],
        );
    }

    private function podeBaixarContratoDe(Estagiario $estagiario): bool
    {
        $usernameLogado = Auth::user()?->getAuthIdentifier();
        $grupoDeAcesso = session('grupodeacesso');

        return match ($grupoDeAcesso) {
            '0' => true,
            'S' => $estagiario->supervisor_username === $usernameLogado,
            'E' => $estagiario->username === $usernameLogado,
            default => false,
        };
    }
}
