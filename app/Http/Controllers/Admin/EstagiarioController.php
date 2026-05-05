<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateEstagiarioRequest;
use App\Models\Estagiario;
use App\Models\Setor;
use App\Models\Supervisor;
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
        $setor = $request->query('setor');
        $setor = is_string($setor) && $setor !== '' ? $setor : null;

        $estagiarios = Estagiario::query()
            ->with('setor')
            ->when($setor, fn ($q) => $q->whereHas('setor', fn ($s) => $s->where('sigla', $setor)))
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        $setores = Setor::query()
            ->whereIn('id', Estagiario::query()->whereNotNull('setor_id')->pluck('setor_id'))
            ->orderBy('sigla')
            ->pluck('sigla');

        return view('admin.estagiarios.index', [
            'estagiarios' => $estagiarios,
            'setores' => $setores,
            'setor' => $setor,
        ]);
    }

    public function edit(Estagiario $estagiario): View
    {
        $supervisores = Supervisor::query()
            ->where('ativo', true)
            ->orderBy('nome')
            ->get(['id', 'nome', 'lotacao']);

        $setores = Setor::ativos()->orderBy('sigla')->get(['id', 'sigla']);

        return view('admin.estagiarios.edit', [
            'estagiario' => $estagiario,
            'supervisores' => $supervisores,
            'setores' => $setores,
        ]);
    }

    public function update(UpdateEstagiarioRequest $request, Estagiario $estagiario): RedirectResponse
    {
        $dados = $request->validated();

        $estagiario->fill([
            'nome' => $dados['nome'],
            'email' => $dados['email'] ?? null,
            'matricula' => $dados['matricula'] ?? null,
            'setor_id' => $dados['setor_id'] ?? null,
            'sei' => $dados['sei'] ?? null,
            'instituicao_ensino' => $dados['instituicao_ensino'] ?? null,
            'inicio_estagio' => $dados['inicio_estagio'] ?? null,
            'fim_estagio' => $dados['fim_estagio'] ?? null,
            'prorrogacao_inicio' => $dados['prorrogacao_inicio'] ?? null,
            'prorrogacao_fim' => $dados['prorrogacao_fim'] ?? null,
            'horas_diarias' => $dados['horas_diarias'],
            'ativo' => (bool) ($dados['ativo'] ?? false),
        ]);

        $this->vincularSupervisor($estagiario, $dados['supervisor_id'] ?? null);

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

    /**
     * Sincroniza supervisor_id e os campos legados (supervisor_nome /
     * supervisor_username) a partir do dropdown. Mantém a autorização
     * existente — que ainda lê supervisor_username — funcionando.
     */
    private function vincularSupervisor(Estagiario $estagiario, ?int $supervisorId): void
    {
        if ($supervisorId === null) {
            $estagiario->supervisor_id = null;
            $estagiario->supervisor_nome = null;
            $estagiario->supervisor_username = null;

            return;
        }

        $supervisor = Supervisor::find($supervisorId);
        if ($supervisor === null) {
            return;
        }

        $estagiario->supervisor_id = $supervisor->id;
        $estagiario->supervisor_nome = $supervisor->nome;
        $estagiario->supervisor_username = $supervisor->username;
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
