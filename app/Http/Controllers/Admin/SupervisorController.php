<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreSupervisorRequest;
use App\Http\Requests\Admin\UpdateSupervisorRequest;
use App\Models\Supervisor;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SupervisorController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria) {}

    public function index(): View
    {
        $supervisores = Supervisor::query()
            ->withCount('estagiarios')
            ->orderByDesc('ativo')
            ->orderBy('nome')
            ->get();

        return view('admin.supervisores.index', ['supervisores' => $supervisores]);
    }

    public function create(): View
    {
        return view('admin.supervisores.form', ['supervisor' => null]);
    }

    public function store(StoreSupervisorRequest $request): RedirectResponse
    {
        $dados = $request->validated();

        $supervisor = Supervisor::create($this->payload($dados));

        $this->auditoria->registrar(
            usuario: (string) $request->session()->get('user.username', ''),
            acao: 'supervisor.criar',
            entidade: 'supervisor',
            entidadeId: (string) $supervisor->id,
            payload: $this->payload($dados),
            ip: $request->ip(),
        );

        return redirect()
            ->route('admin.supervisores.index')
            ->with('sucesso', "Supervisor {$supervisor->nome} cadastrado.");
    }

    public function edit(Supervisor $supervisor): View
    {
        return view('admin.supervisores.form', ['supervisor' => $supervisor]);
    }

    public function update(UpdateSupervisorRequest $request, Supervisor $supervisor): RedirectResponse
    {
        $dados = $request->validated();
        $antes = $supervisor->only(['nome', 'username', 'email', 'lotacao', 'ativo']);

        $supervisor->update($this->payload($dados));

        $this->auditoria->registrar(
            usuario: (string) $request->session()->get('user.username', ''),
            acao: 'supervisor.editar',
            entidade: 'supervisor',
            entidadeId: (string) $supervisor->id,
            payload: ['antes' => $antes, 'depois' => $this->payload($dados)],
            ip: $request->ip(),
        );

        return redirect()
            ->route('admin.supervisores.index')
            ->with('sucesso', "Dados de {$supervisor->nome} atualizados.");
    }

    public function destroy(Request $request, Supervisor $supervisor): RedirectResponse
    {
        if ($supervisor->estagiarios()->exists()) {
            return redirect()
                ->route('admin.supervisores.index')
                ->withErrors([
                    'supervisor' => "Não é possível remover {$supervisor->nome}: há estagiários vinculados. Reatribua-os antes ou desative o supervisor.",
                ]);
        }

        $snapshot = $supervisor->only(['nome', 'username', 'email', 'lotacao', 'ativo']);
        $id = $supervisor->id;
        $supervisor->delete();

        $this->auditoria->registrar(
            usuario: (string) $request->session()->get('user.username', ''),
            acao: 'supervisor.remover',
            entidade: 'supervisor',
            entidadeId: (string) $id,
            payload: $snapshot,
            ip: $request->ip(),
        );

        return redirect()
            ->route('admin.supervisores.index')
            ->with('sucesso', 'Supervisor removido.');
    }

    /**
     * @param  array<string, mixed>  $dados
     * @return array<string, mixed>
     */
    private function payload(array $dados): array
    {
        return [
            'nome' => $dados['nome'],
            'username' => $dados['username'] ?? null,
            'email' => $dados['email'] ?? null,
            'lotacao' => $dados['lotacao'] ?? null,
            'ativo' => array_key_exists('ativo', $dados) ? (bool) $dados['ativo'] : true,
        ];
    }
}
